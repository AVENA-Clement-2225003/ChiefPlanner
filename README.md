# ChiefPlanner

Planificateur de repas de la semaine : on génère en un clic une Proposition de repas pour le midi et le soir, ajustée au nombre de personnes, et l'application en déduit la liste de courses.

- Vocabulaire : [GLOSSARY.md](GLOSSARY.md)
- Décisions d'architecture : [docs/adr/](docs/adr/)
- Plan de travail : [docs/lots-de-travail.md](docs/lots-de-travail.md)
- Règles de travail : [CONTRIBUTING.md](CONTRIBUTING.md)

## Structure

| Dossier | Contenu |
|---|---|
| `api/` | API Laravel (JSON), authentification OIDC avec Authentik |
| `web/` | SPA React, TypeScript et Ant Design (PWA) |
| `design/` | Maquettes et design system |
| `docs/` | ADR, plan de travail, documentation agents |

## Développer en local

```bash
# API (PHP 8.3 ou plus, Composer)
cd api
composer install
cp .env.example .env && php artisan key:generate
php artisan serve --port=8080

# SPA (Node 22) ; /api est proxifié vers http://127.0.0.1:8080
cd web
npm install
npm run dev        # avec l'API sur le port 8080
```

## Voir l'interface en local, sans Authentik

Avec `APP_ENV=local`, `GET /api/dev/login` ouvre une session de test (compte administrateur « Chef Local », quelques plats et MealFlows de démonstration) puis renvoie vers la SPA. Cette route n'existe pas hors de `local`.

```bash
# api/.env.local (ignoré par git) : APP_ENV=local, APP_KEY, DB_CONNECTION=sqlite, DB_DATABASE=<chemin absolu>
cd api && APP_ENV=local php artisan migrate && APP_ENV=local php artisan serve --port=8080
cd web && npm run dev
# puis ouvrir http://localhost:8080/api/dev/login
```

## Contrôles

```bash
cd api && vendor/bin/pint --test && vendor/bin/phpunit
cd web && npm run typecheck && npm run lint && npm test && npm run build
```
