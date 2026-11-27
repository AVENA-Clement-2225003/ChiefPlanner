# Provisions one ChiefPlanner instance in Authentik: groups, service account, OIDC provider and application.
# Idempotent: running it again changes nothing that already exists.
#
# `INSTANCE` ("dev" or "prod") and `HOST` (public host name of the instance) are set by the caller, which
# prepends two lines to this script. It runs inside the Authentik server container:
#
#   { printf 'INSTANCE = "dev"\nHOST = "dev-chiefplanner.michaelkineticsystems.fr"\n'; cat provision.py; } \
#     | docker exec -i authentik-server-1 ak shell
#
# Secrets are NOT printed in clear for a human: the lines starting with `ENV ` are meant to be piped
# straight into the instance's .env file on the server (see README.md).
import os

from authentik.core.models import Application, Group, Token, TokenIntents, User, UserTypes
from authentik.crypto.models import CertificateKeyPair
from authentik.flows.models import Flow
from authentik.policies.models import PolicyBinding
from authentik.providers.oauth2.models import (
    OAuth2Provider,
    RedirectURI,
    RedirectURIMatchingMode,
    RedirectURIType,
    ScopeMapping,
)
from authentik.rbac.models import Role

PUBLIC_URL = f"https://{HOST}"
AUTHENTIK_URL = "https://auth.michaelkineticsystems.fr"

# --- Groups: who may enter, and who administers ---------------------------------------------------
user_group, _ = Group.objects.get_or_create(name=f"chiefplanner-{INSTANCE}-user")
admin_group, _ = Group.objects.get_or_create(name=f"chiefplanner-{INSTANCE}-admin")

# --- Service account used by the app to promote administrators (ADR 0002) -------------------------
# It may only read users and add/remove members of the admin group of THIS instance: nothing else.
role, _ = Role.objects.get_or_create(name=f"chiefplanner-{INSTANCE}-svc")
role.assign_perms("authentik_core.view_user")
for codename in ["add_user_to_group", "remove_user_from_group", "view_group"]:
    role.assign_perms(f"authentik_core.{codename}", obj=admin_group)

service_user, _ = User.objects.get_or_create(
    username=f"chiefplanner-{INSTANCE}-svc",
    defaults={"name": f"ChiefPlanner ({INSTANCE}) service", "type": UserTypes.SERVICE_ACCOUNT, "path": "goauthentik.io/service-accounts"},
)
service_user.set_unusable_password()
service_user.save()
role.users.add(service_user)

token, _ = Token.objects.get_or_create(
    identifier=f"chiefplanner-{INSTANCE}-svc-token",
    defaults={"user": service_user, "intent": TokenIntents.INTENT_API, "expiring": False},
)

# --- OIDC provider and application ----------------------------------------------------------------
mappings = ScopeMapping.objects.filter(
    managed__in=[
        "goauthentik.io/providers/oauth2/scope-openid",
        "goauthentik.io/providers/oauth2/scope-email",
        "goauthentik.io/providers/oauth2/scope-profile",
    ]
)
provider, created = OAuth2Provider.objects.get_or_create(
    name=f"Provider for ChiefPlanner ({INSTANCE})",
    defaults={
        "authorization_flow": Flow.objects.get(slug="default-provider-authorization-implicit-consent"),
        "invalidation_flow": Flow.objects.get(slug="default-provider-invalidation-flow"),
        "client_type": "confidential",
        "signing_key": CertificateKeyPair.objects.get(name="authentik Self-signed Certificate"),
        "sub_mode": "hashed_user_id",
    },
)
provider.redirect_uris = [
    RedirectURI(RedirectURIMatchingMode.STRICT, f"{PUBLIC_URL}/api/auth/callback", RedirectURIType.AUTHORIZATION)
]
# Since Authentik 2026.8 a provider only accepts the grant types it lists: with none, every authorize request
# is refused with a bare `invalid_request` ("The request is otherwise malformed").
provider.grant_types = ["authorization_code", "refresh_token"]
provider.property_mappings.set(mappings)
provider.save()

application, _ = Application.objects.get_or_create(
    slug=f"chiefplanner-{INSTANCE}",
    defaults={"name": f"ChiefPlanner ({INSTANCE})", "provider": provider, "meta_launch_url": PUBLIC_URL},
)
if application.provider_id != provider.pk:
    application.provider = provider
    application.save()

# Only members of the two groups may open the application.
for order, group in enumerate([admin_group, user_group]):
    PolicyBinding.objects.get_or_create(target=application, group=group, defaults={"order": order, "enabled": True})

# --- What the app needs in its .env ----------------------------------------------------------------
env = {
    "OIDC_ISSUER": f"{AUTHENTIK_URL}/application/o/{application.slug}/",
    "OIDC_CLIENT_ID": provider.client_id,
    "OIDC_CLIENT_SECRET": provider.client_secret,
    "OIDC_GROUP_USER": user_group.name,
    "OIDC_GROUP_ADMIN": admin_group.name,
    "AUTHENTIK_API_URL": f"{AUTHENTIK_URL}/api/v3",
    "AUTHENTIK_API_TOKEN": token.key,
    "AUTHENTIK_ADMIN_GROUP_ID": str(admin_group.pk),
}
for key, value in env.items():
    print(f"ENV {key}={value}")
print(f"DONE {INSTANCE} provider_created={created}")
