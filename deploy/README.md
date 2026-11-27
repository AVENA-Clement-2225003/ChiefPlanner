# Déploiement

Chaîne automatique : **push sur GitHub → CI (tests) → images sur GHCR → Watchtower redémarre les conteneurs** ([ADR 0003](../docs/adr/0003-deploiement-watchtower-sans-sauvegarde.md)).

| Branche | Tag des images | Instance | Adresse |
|---|---|---|---|
| `master` | `latest` | prod | `https://chiefplanner.michaelkineticsystems.fr` |
| `dev` | `dev` | dev | `https://dev-chiefplanner.michaelkineticsystems.fr` |

Le serveur `origae` héberge déjà le Watchtower d'Extrudo (`/home/origae/projects/Extrudo/watchtower.yaml`, `WATCHTOWER_LABEL_ENABLE=true`, relevé toutes les 5 minutes). **Il est réutilisé** : les conteneurs `chiefplanner-api` et `chiefplanner-front` portent le label `com.centurylinklabs.watchtower.enable=true`, rien d'autre à lancer. Ne pas démarrer un second Watchtower.

## 1. Accès du serveur à GHCR

Les images sont privées. Le `docker login ghcr.io` fait pour Extrudo (`/root/.docker/config.json`) suffit si le même compte GitHub possède les deux dépôts. Après la première exécution de la CI :
`docker pull ghcr.io/avena-clement-2225003/chiefplanner-api:dev`.

## 2. Authentik (une application par instance)

Dans Authentik, pour chaque instance (`dev`, puis `prod`) :

1. **Groupes** : `chiefplanner-<instance>-user` et `chiefplanner-<instance>-admin`.
2. **Provider OAuth2/OpenID** `chiefplanner-<instance>` : client confidentiel, redirect URI `https://<hôte>/api/auth/callback`, scopes `openid profile email groups`, signature RS256. Le claim `groups` doit être renvoyé.
3. **Application** `chiefplanner-<instance>`, slug `chiefplanner-<instance>` (l'émetteur est donc `https://auth.michaelkineticsystems.fr/application/o/chiefplanner-<instance>/`), liée au provider. Restreindre l'accès aux deux groupes.
4. **Compte de service** avec un jeton d'API limité à la modification du groupe `chiefplanner-<instance>-admin` (rôle avec la seule permission « modifier l'utilisateur dans ce groupe », jamais superutilisateur, [ADR 0002](../docs/adr/0002-roles-pilotes-par-authentik.md)). Noter l'identifiant (UUID) du groupe admin pour `AUTHENTIK_ADMIN_GROUP_ID`.
5. Le premier administrateur : ajouter à la main un compte au groupe `chiefplanner-<instance>-admin`.

## 3. Lancer une instance

Dans un dossier du serveur distinct de l'ancienne installation (par exemple `/home/origae/projects/ChiefPlanner-v2/`) :

```sh
cp compose.yaml dev.env.example .   # puis renommer dev.env.example en dev.env et le remplir
docker compose -p chiefplanner-dev --env-file dev.env up -d
```

`APP_KEY` se génère avec `php artisan key:generate --show` (ou `echo "base64:$(openssl rand -base64 32)"`). Pour la prod : `prod.env`, `-p chiefplanner-prod`. Chaque projet Compose a sa propre base MySQL.

## 4. Nginx Proxy Manager

*Hosts → Proxy Hosts → Add Proxy Host* :

| Champ | Valeur |
|---|---|
| Domain Names | `dev-chiefplanner.michaelkineticsystems.fr` |
| Scheme / Forward Hostname / Port | `http` / `front-dev-chiefplanner` / `80` |
| SSL | Let's Encrypt, **Force SSL** (le cookie de session est `Secure`) |

Onglet **Custom locations** → *Add location* :

| Champ | Valeur |
|---|---|
| Define location | `/api` |
| Scheme / Forward Hostname / Port | `http` / `api-dev-chiefplanner` / `8080` |

Ne pas cocher « Cache Assets » : le front envoie ses propres en-têtes de cache (le service worker doit rester revalidé).

## Vérifications après un déploiement

- `https://<hôte>/api/health` répond `{"status":"ok"}`.
- La connexion passe par Authentik et le cookie de session est `Secure` et `HttpOnly`.
- Un push sur `dev` met l'instance à jour en une dizaine de minutes (CI puis une période de Watchtower).

## Pas de sauvegarde

Tout l'état est dans le volume `db` (MySQL). Il n'y a aucune sauvegarde automatique, par choix (ADR 0003). Les migrations s'appliquent au démarrage de l'API : ne jamais en pousser une destructrice sans l'avoir signalée dans « Merge Danger » de la PR.

## Passage en production (lot L9)

L'ancienne installation (`chiefplanner-app`, `chiefplanner-mysql`, réseau `chiefplanner_chiefplanner`, dossier `/home/origae/projects/ChiefPlanner`) reste en place jusqu'à la bascule : l'arrêter (`docker compose down`, sans `-v` tant qu'on veut pouvoir revenir), libérer l'hôte `chiefplanner.michaelkineticsystems.fr` dans NPM, puis lancer l'instance prod.
