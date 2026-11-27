# PWA : hors ligne en lecture seule

L'application est une PWA. Hors ligne, elle permet seulement de consulter la Semaine, les recettes (Étapes) et la Liste de courses, avec une alerte explicite pour que l'utilisateur ne croie pas pouvoir modifier. Toute écriture (plats, ingrédients, MealFlows, Repas, courses cochées) exige d'être en ligne.

## Considered Options

- **Lecture et écritures hors ligne avec synchronisation** : écarté, il aurait fallu gérer les conflits de synchronisation entre appareils pour un gain limité.
