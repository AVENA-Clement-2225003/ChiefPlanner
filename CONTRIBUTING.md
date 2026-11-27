# Règles de travail

Ces règles s'appliquent à tout le monde, agents compris. Le vocabulaire du projet est dans [GLOSSARY.md](GLOSSARY.md), les décisions d'architecture dans [docs/adr/](docs/adr/).

## Branches

| Branche | Rôle |
|---|---|
| `master` | Ce qui est en production. Mise à jour **uniquement par l'utilisateur**, jamais par un agent. |
| `dev` | Branche d'intégration. Toutes les PR de feature pointent ici. |
| `feat/<nom-court>[-<issue>]` | Une branche par feature. |

Nom d'une branche de feature : `feat/<nom-court>` en minuscules séparées par des tirets, suivi de `-<numéro d'issue>` quand l'issue existe.

```text
feat/mealflow-activation-12
feat/shopping-list-packs
```

Les autres types suivent le même schéma : `fix/<nom-court>[-<issue>]`, `refactor/…`, `docs/…`, `chore/…`.

## Du code à `master`

```text
feat/x  ──PR──▶  dev  ──(l'utilisateur)──▶  master
```

1. Créer la branche depuis `dev`.
2. Ouvrir une PR **vers `dev`**, jamais vers `master`.
3. Une fois `dev` validée, c'est l'utilisateur qui met `master` à jour. Un agent ne merge pas dans `master` et ne pousse pas dessus.

Les merges dans `dev` et dans `master` déclenchent un déploiement automatique (images `:dev` et `:latest`, puis Watchtower, voir [ADR 0003](docs/adr/0003-deploiement-watchtower-sans-sauvegarde.md)). La base n'étant pas sauvegardée, une migration destructrice est traitée comme un changement à haut risque : elle est signalée dans « Merge Danger » de la PR.

### Features à la suite : PR empilées

Quand une feature dépend de la précédente, on empile les branches au lieu d'attendre le merge.

```text
dev ◀── feat/a ◀── feat/b ◀── feat/c
```

- `feat/b` part de `feat/a`, et sa PR pointe vers `feat/a` ; `feat/c` pointe vers `feat/b`.
- On merge de bas en haut : `feat/a` dans `dev`, puis on repointe la PR de `feat/b` vers `dev`, et ainsi de suite.
- Chaque PR ne montre que sa propre feature. Si `feat/a` change, rebaser les branches au-dessus.

## Commits et PR

- Un commit = un changement qui se comprend seul.
- Message en anglais, préfixé par le type : `Feature:`, `Fix:`, `Refactor:`, `Docs:`, `Chore:`, `Test:`.
- Pas de ligne `Co-Authored-By` ni d'attribution d'outil dans les commits ni dans les PR.
- La description de la PR suit le modèle du skill `pr` : Summary, Evidence, Merge Danger.
- Avant d'ouvrir la PR, lancer les mêmes contrôles que la CI (`.github/workflows/`) :
  - `cd api && vendor/bin/pint --test && vendor/bin/phpunit`
  - `cd web && npm run typecheck && npm run lint && npm test && npm run build`
- La CI refuse une PR dont l'un de ces contrôles échoue.
- Toute interface suit le design system du projet (`design/chiefplanner-design-system.html` et les jetons de `src/theme/`), thèmes clair et sombre vérifiés.
- Une décision d'architecture dure à défaire ou surprenante devient un ADR dans `docs/adr/`. Un nouveau terme du domaine entre dans `GLOSSARY.md` avant d'être utilisé dans le code.

## Issues et tableau du projet

Le tableau est le projet GitHub « ChiefPlanner ». Chaque issue y est dans une colonne, et on la déplace **au fur et à mesure**, sans attendre la fin du lot.

| Colonne | Quand une issue y va |
|---|---|
| `Backlog` | Elle existe, personne ne la travaille. |
| `Ready` | Dès qu'on décide de travailler un lot d'issues (par exemple 4), toutes passent de `Backlog` à `Ready`. |
| `In progress` | Dès qu'on commence celle-là, et seulement celle-là. |
| `In review` | Dès que sa PR est ouverte et attend la revue. La PR passe aussi en `In review`. |
| `Done` | Une fois la PR mergée dans `dev`. |

- **Citer l'issue dans la PR** (`Refs #12` dans la description) pour que le lien se voie des deux côtés.
- **Fermer l'issue quand la PR est mergée dans `dev`**, pas dans `master` : `dev` est la branche d'intégration, et ce qui y est mergé est considéré comme fait. GitHub ne ferme automatiquement que les merges vers la branche par défaut (`master`), donc il faut la fermer à la main, avec un commentaire qui cite la PR.
- **Passer l'issue en `Done`** au moment de la fermeture, si le projet ne l'a pas déjà fait seul.
- Une issue qui ne sert plus (obsolète, doublon) est fermée avec la raison dans un commentaire.

## Code propre

### Imbrication

- Maximum **2 niveaux** de blocs imbriqués (`if`, `for`, `try`, callback) dans une fonction.
- Sortir tôt : traiter d'abord les cas d'erreur et les cas vides avec un `return`, garder le cas normal à plat.
- Quand un troisième niveau apparaît, extraire le bloc interne dans une fonction nommée.

```ts
// Non
function addToShoppingList(meal) {
  if (meal) {
    if (meal.dish) {
      if (meal.dish.hasIngredients) {
        return collect(meal);
      }
    }
  }
}

// Oui
function addToShoppingList(meal) {
  if (!meal?.dish?.hasIngredients) return;

  return collect(meal);
}
```

### Branchement

- Pas de `else` après un `return`, un `throw` ou un `continue`.
- Pas de `if`/`else if` en cascade sur la même valeur : utiliser `match` (PHP), `switch` ou une table de correspondance.
- Un `if` qui a plus de deux conditions combinées se nomme : `$canShare = $dish->isOwnedBy($user) && $dish->hasIngredients()`.
- Pas de paramètre booléen qui change le comportement d'une fonction : écrire deux fonctions.

### Noms

- Un nom dit **ce que c'est** ou **ce que ça fait**, sans commentaire pour l'expliquer.
- Les fonctions sont des verbes (`fetchMeals`, `markAsShared`), les variables et classes des noms, les booléens des questions (`isShared`, `hasLeftover`).
- Pas d'abréviation obscure ni de nom d'une lettre, sauf un indice de boucle trivial.
- Utiliser les termes du [glossaire](GLOSSARY.md), pas un synonyme : pas de « playlist », de « planif » ni de « créateur » dans le code neuf. Le code, les migrations et les clés d'API sont **en anglais**, avec cette correspondance (les textes affichés à l'utilisateur restent en français) :

  | Glossaire | Code | Glossaire | Code |
  |---|---|---|---|
  | Utilisateur | `User` | Moment (`midi`, `soir`) | `Slot` (`lunch`, `dinner`) |
  | Administrateur | `Admin` | Portions | `Servings` |
  | Plat | `Dish` | Étape | `Step` |
  | Ingrédient | `Ingredient` | Étiquette | `Tag` |
  | Communauté | `Community` | Unité | `Unit` |
  | MealFlow | `MealFlow` | Conditionnement | `Pack` |
  | Semaine | `Week` | Reste | `Leftover` |
  | Repas | `Meal` | Rayon | `Aisle` |
  | Préférences | `Preferences` | Proposition | `Proposal` |
  | Liste de courses | `ShoppingList` | Extra | `Extra` |
- Une fonction fait une seule chose et tient sur un écran. Si son nom contient « et » ou « and », la couper.

### Le reste

- Pas de nombre ou de chaîne magique : une constante nommée (`MAX_REPEATS_PER_WEEK`).
- Pas de code mort, de code commenté, d'import ou de variable inutilisés. Le lint le refuse.
- Pas de `any` en TypeScript : typer, ou utiliser `unknown` et le réduire.
- Un commentaire explique **pourquoi**, jamais **quoi**.
- Un bug corrigé reçoit un test qui échouait avant le correctif.
