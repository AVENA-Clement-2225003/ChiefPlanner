# Les rôles viennent des groupes Authentik, avec un token de service limité

Il n'existe que deux rôles : Utilisateur et Administrateur. Ils sont portés par quatre groupes Authentik, `chiefplanner-{prod,dev}-{user,admin}`, lus dans le claim `groups` à la connexion. L'app n'a plus de table de rôles. Le rôle Créateur est supprimé : chacun crée ses propres plats et ingrédients. Le premier administrateur de chaque environnement est ajouté à la main dans Authentik.

Le panel admin de l'app peut promouvoir ou retirer un administrateur en appelant l'API Authentik avec un token de service. Ce token n'a le droit de modifier que le groupe `-admin` de son propre environnement, jamais un token superuser, car l'app peut sinon s'octroyer des droits.

## Consequences

- Un rôle modifié dans Authentik ne se voit qu'à la prochaine connexion de la personne. Une promotion ou un retrait fait depuis le panel met aussi à jour l'application immédiatement.
- Une base utilisateur dans l'app subsiste, liée à Authentik par le claim `sub`, pour porter plats, Préférences, Semaine, etc.
