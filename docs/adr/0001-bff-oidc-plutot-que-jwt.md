# Authentification OIDC côté serveur (BFF) plutôt que JWT dans la SPA

Laravel est un client OIDC confidentiel d'Authentik (`auth.domaine.fr`) : il fait le flux Authorization Code côté serveur et pose un cookie de session `HttpOnly`, `Secure`, `SameSite`. La SPA ne manipule jamais de token. La SPA et l'API partagent le même hôte (`/` vers `front`, `/api` vers `api`), donc pas de CORS. Chaque environnement a sa propre application Authentik, avec son `client_id` et son `client_secret`.

## Considered Options

- **Authorization Code + PKCE dans la SPA, JWT en Bearer validé par l'API (JWKS)** : API sans état, mais un token d'accès vit dans le navigateur (exposé au XSS). Écarté : le critère retenu est la sécurité maximale.

## Consequences

- Les sessions sont stockées en base (MySQL de l'environnement), l'API n'est donc pas totalement sans état.
- L'inscription locale, les mots de passe et Google via Socialite sont supprimés. Google n'est pas fédéré dans Authentik.
- Le hors ligne (ADR 0004) dépend de la validité du cookie de session.
