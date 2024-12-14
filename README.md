# ChiefPlanner v2.0
## Sommaire
- [Résumé](#résumé)
- [Fonctionalitées ajoutées lors de la dernière mise à jour](#fonctionalitées-ajoutées-lors-de-la-dernière-mise-à-jour)
- [Correction de bugs](#correction-de-bugs)
  - [Bugs corrigés](#bugs-corrigés)
  - [Bugs connus](#bugs-connus) 
- [Mises à jour futures](#mises-à-jour-futures)

## Résumé
ChiefPlanner est une application web qui permet en un simple clic de générer son planning de repas de la semaine.

Les plats sont sélectionnés de manière aléatoire pour le midi et le soir en fonction des préférences de générations que vous avez renseignés

## Fonctionalitées ajoutées lors de la dernière mise à jour
À partir de cette version 2.0 ChiefPlanner est désormais doté :

- Système de compte
- Authentification par tier :
  - Google
- Ajout d'un système de rôles :
    - Administrateur
    - Créateur de plats/ingrédients
    - Utilisateur
- Possibilité d'ajouter des extras dans la liste de courses (Qui ne sont pas en relation avec les plats). Par exemple : une bouteille de lait, des mouchoirs, des chips, etc.)

## Correction de bugs
### Bugs corrigés
- Une fois déconnecté et reconnecté, le script responsable de l'affichage des informations d'un plat pour un jour de la semaine ne s'exécutait plus.
- Une erreur survenait lorsque les préférences étaient modifiée et que l'on essayait de retourner sur la page principale (tentative de lecture sur une valeur de type null)

### Bugs connus
Aucun

## Mises à jour futures
 - Authentification par tier :
   - Microsoft
   - Apple
