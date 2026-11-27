# Lots de travail

Découpage de la refonte en **tranches verticales** : après les fondations et un déploiement de bout en bout, chaque lot livre une fonction complète (API Laravel et écrans de la SPA), pas une couche technique. Chaque lot se découpe en issues puis en PR empilées vers `dev` (voir [CONTRIBUTING.md](../CONTRIBUTING.md)). Le vocabulaire est dans [GLOSSARY.md](../GLOSSARY.md), les décisions dans [docs/adr/](adr/).

```text
L0 Fondations ─▶ L1 Déploiement bout en bout ─▶ L2 Authentification ─▶ L3 Catalogue ─┬▶ L4 MealFlows ──┐
                                                                                   └▶ L5 Semaine ─────┼▶ L6 Courses ─▶ L7 Mon magasin ─▶ L8 PWA ─▶ L9 RGPD et production
```

L4 et L5 sont indépendants l'un de l'autre. L6 a besoin de L5 (Repas). L8 peut démarrer dès que L6 existe.

## État d'avancement

Le code des lots L0 à L9 est écrit, sur des branches empilées (voir plus bas), mais **rien n'a été poussé ni déployé**. Reste à faire côté serveur : Authentik, DNS, NPM et `.env` (voir [deploy/README.md](../deploy/README.md)), puis le premier passage de la chaîne GitHub Actions → GHCR → Watchtower. Les Dockerfiles n'ont jamais été construits (pas de moteur Docker sur le poste de développement), et les tests ont tourné sur SQLite en local ; la CI rejoue la suite sur MySQL 8.

| Branche (empilées dans cet ordre) | Contenu |
|---|---|
| `feat/foundations` | L0 : dépôt `api/` + `web/`, nettoyage, règles, CI |
| `feat/deploy-skeleton` | L1 : Dockerfiles, compose, images GHCR, guide de déploiement |
| `feat/auth-authentik` | L2 : connexion OIDC côté serveur, rôles, panneau admin |
| `feat/catalog` | L3 (API) : plats, ingrédients, étiquettes, unités, partage |
| `feat/mealflows` | L4 (API) |
| `feat/week` | L5 (API) : Préférences, Semaine, Proposition, annulation |
| `feat/shopping` | L6 et L7 (API) : courses, paquets, rayons, extras, dépenses |
| `feat/leftovers` | L7 : sélection qui réutilise les Restes |
| `feat/rgpd-moderation` | L9 (API) : suppression de compte, modération des ingrédients |
| `feat/web-integration` | L2 à L8 (SPA) : tous les écrans branchés sur l'API, PWA hors ligne en lecture seule |

## Décisions prises en route

Elles n'étaient pas dans l'entretien et sont faciles à changer :

- **Structure** `api/` et `web/`, noms **en anglais** dans le code (table de correspondance dans `CONTRIBUTING.md`).
- **Laravel 13** (Laravel 11 avait des avis de sécurité ouverts), PHP 8.3 minimum, image `serversideup/php:8.4-fpm-nginx`.
- Un ingrédient créé est **partagé par défaut** (ses noms ne portent pas de donnée personnelle, et cela limite les doublons) ; un plat est **privé par défaut**.
- Les étiquettes sont des listes fixes en trois familles (cuisine, régime, contrainte). Elles servent au filtre de recherche et aux fiches, pas encore à la sélection : « compatible avec le contexte du jour » n'est pas défini, la `SelectionStrategy` est prête à l'accueillir.
- Les Préférences par défaut : les 14 Moments activés, 1 personne, aucun MealFlow.
- Un MealFlow qui n'a aucun plat pour un Moment laisse la sélection retomber sur tous les plats du Moment.
- Un plat est répété (au plus 2 fois) pour réduire les Restes seulement si l'utilisateur a déclaré des conditionnements ; sans eux, la Proposition est un tirage aléatoire sans répétition.
- La promotion d'un administrateur retrouve l'utilisateur Authentik par son **email** (correspondance exacte), pas par son `sub`.
- La SPA n'a plus de données simulées : pour la voir, il faut l'API (ou l'environnement dev déployé).

## L0. Fondations

Remettre le dépôt dans un état sain avant tout développement.

- Créer `dev` à partir de `v2` ; `master` reste la production actuelle.
- Supprimer l'ancien code : `chief-planner/` (CRA), `frontend/dist`, vues Blade, contrôleurs et middlewares d'auth maison, jQuery, `DB_scheme.*`, `prompt.txt`, `SetUpCommand.txt`, anciennes docs Playlist (contenu reporté dans le glossaire).
- Structure du dépôt : `api/` (Laravel) et `web/` (SPA), noms en anglais dans le code (voir `CONTRIBUTING.md`).
- CI GitHub Actions : `phpunit`, `typecheck`, `build`, plus lint et tests du front.
- Créer le projet GitHub « ChiefPlanner » et les issues des lots suivants.

**Fini quand** : `dev` existe, la CI passe sur une PR vide, plus aucune trace de l'ancien code d'auth ni de Blade.

## L1. Déploiement de bout en bout (squelette)

Valider tout le chemin « push » → « conteneur à jour sur `origae` » avant d'écrire du métier, parce que c'est ce qui comporte le plus d'inconnues.

- Dockerfiles `api` (PHP-FPM, migrations au démarrage) et `front` (nginx qui sert la SPA).
- GitHub Actions : build et push vers GHCR (`:dev` depuis `dev`, `:latest` depuis `master`).
- Stacks compose `dev` et `prod` dans `/home/origae/projects/ChiefPlanner`, MySQL 8 par environnement, `.env` par environnement, label `com.centurylinklabs.watchtower.enable=true`.
- nginx_proxy_manager : `dev-chiefplanner.michaelkineticsystems.fr` (`/` vers `front`, `/api` vers `api`).
- Étapes manuelles (DNS, proxy, `.env`) sous forme de script guidé (skill `wizard`).
- Un endpoint `/api/health` et une page d'accueil minimale pour prouver la chaîne.

**Fini quand** : un merge dans `dev` met à jour l'environnement dev sans intervention, en moins de quelques minutes.

## L2. Authentification (Authentik, BFF)

[ADR 0001](adr/0001-bff-oidc-plutot-que-jwt.md) et [ADR 0002](adr/0002-roles-pilotes-par-authentik.md).

- Dans Authentik : application et provider OIDC `chiefplanner-dev` (puis `-prod`), quatre groupes, token de service limité au groupe `-admin`. Script guidé pour les étapes manuelles.
- Laravel : flux Authorization Code côté serveur, session en base, cookie `HttpOnly`/`Secure`/`SameSite`, déconnexion, lecture des groupes dans le claim, création de l'Utilisateur au premier login (lien par `sub`).
- Middlewares `user` et `admin`, route `/api/me`.
- SPA : garde de routes, écran « non connecté », menu compte.
- Panel admin : liste des utilisateurs en lecture, promotion ou retrait d'administrateur via l'API Authentik.

**Fini quand** : on se connecte sur dev avec un compte du groupe `chiefplanner-dev-user`, un `-admin` voit le panel et peut promouvoir quelqu'un, un inconnu est refusé.

## L3. Catalogue (Plats, Ingrédients, Étiquettes, Unités)

[ADR 0005](adr/0005-quantites-par-personne-et-conditionnements.md).

- Données de référence en seed : Unités (par famille), Étiquettes (cuisine, régime, contrainte), Moments `midi` et `soir`.
- Ingrédients : création avec réutilisation par libellé normalisé, propriétaire, partage.
- Plats : création avec nombre de personnes de saisie et division par N, quantités stockées par personne, Moments et Étiquettes, Étapes ordonnées, partage, avertissement non bloquant sur les quantités non entières.
- Adoption d'un plat partagé de la Communauté (le lien d'abonnement sert de dépendance, ce qui permet le retrait RGPD).
- Écrans : Plats, Tous les plats, Ingrédients, fiche et édition d'un plat avec recette pas à pas.
- Modération admin des ingrédients partagés (simple suppression ou masquage).

**Fini quand** : un utilisateur crée un plat de A à Z, le partage, et un autre le trouve et l'adopte.

## L4. MealFlows

- CRUD des MealFlows (regroupement nommé de plats), ajout et retrait de plats.
- Écran MealFlows de la maquette adapté.

**Fini quand** : un utilisateur crée « Repas du dimanche » avec trois plats.

## L5. Semaine, Préférences et Proposition

- Préférences : grille des Moments retenus, nombre de Portions par défaut par Moment, affectation de 0, 1 ou 2 MealFlows aux Moments.
- Proposition : régénération de toute la Semaine à la demande (filtre par Moment, Étiquettes et MealFlow, tirage sans répétition), derrière une interface `SelectionStrategy` remplaçable.
- Annulation : l'avant-dernière Proposition en base, l'historique plus ancien dans la session du front.
- Édition d'un Repas : changer le plat, changer les Portions, sauvegarde en base.
- Plat retiré (RGPD) : Repas conservé sans plat, signalé « à remplacer ».

**Fini quand** : un utilisateur règle ses Préférences, lance une Proposition, change un Repas, puis annule la dernière Proposition.

## L6. Liste de courses

- Cumul des quantités des Repas par ingrédient (ajustées aux Portions), avec conversion dans une même famille d'Unités et tolérance d'arrondi.
- Extras : ajout, modification, suppression.
- Saisie du total réellement dépensé (optionnel).
- Écran Liste de courses.

**Fini quand** : la liste d'une Semaine fusionne correctement « 500 g + 1 kg » et affiche les Extras.

## L7. Mon magasin (Conditionnements, Rayons, prix, Restes)

- Écran « Mon magasin » : par ingrédient et par utilisateur, Conditionnements, prix optionnel, Rayon.
- Liste de courses : combinaison de paquets la plus juste, Reste affiché, regroupement par Rayon, total estimé.
- Algorithme de Proposition : répéter un plat (au plus 2 fois) quand cela réduit le Reste total.

**Fini quand** : avec gnocchis 2 portions et nuggets 10 pièces, la Semaine propose le plat deux fois et la liste affiche un Reste minimal.

## L8. PWA, hors ligne en lecture seule

[ADR 0004](adr/0004-hors-ligne-lecture-seule.md).

- Manifest, icônes, service worker, installation sur téléphone.
- Mise en cache de la Semaine, des recettes et de la Liste de courses pour la lecture.
- Détection du hors ligne et alerte claire ; actions d'écriture désactivées.

**Fini quand** : en mode avion, on lit sa Semaine, ses recettes et sa Liste de courses, et l'écran dit qu'on est hors ligne.

## L9. RGPD et passage en production

- Suppression d'un utilisateur : tout ce qu'il possède, sauf les ingrédients partagés utilisés par d'autres qui passent à la Communauté.
- Stack `prod` : application Authentik `chiefplanner-prod`, `chiefplanner.michaelkineticsystems.fr`, `.env` de prod.
- Bascule : arrêt de l'ancienne stack (`chiefplanner-app`, `chiefplanner-mysql`), base vide, premier administrateur ajouté dans Authentik.
- Revue finale : tests de bout en bout, vérification du glossaire et des ADR.

**Fini quand** : la prod tourne sur les nouvelles images et l'ancienne installation est arrêtée.

