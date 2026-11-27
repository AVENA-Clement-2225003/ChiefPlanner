# ChiefPlanner

Application qui génère en un clic le planning de repas de la semaine d'un utilisateur, à partir de ses plats et de ses préférences, et en déduit la liste de courses.

## Language

**Utilisateur**:
Personne authentifiée via Authentik qui planifie ses propres repas.
_Avoid_: Compte, membre, client

**Administrateur**:
Utilisateur du groupe admin de l'environnement, qui peut en plus promouvoir d'autres utilisateurs administrateurs et modérer les ingrédients (fusion des doublons).
_Avoid_: Admin système, Créateur (rôle supprimé : chacun crée ses propres plats et ingrédients)

**Plat**:
Recette qu'un utilisateur possède et peut planifier. Un plat appartient à son créateur, qui décide s'il le partage avec la communauté.
_Avoid_: Dish, recette

**Ingrédient**:
Produit composant un plat. Un ingrédient n'est que son nom, le même pour tous : il n'a ni propriétaire ni visibilité, et n'est créé que s'il n'existe pas déjà. Ce qui est personnel (Conditionnement, prix, Rayon) est rattaché à l'utilisateur, pas à l'ingrédient.
_Avoid_: Produit

**Communauté**:
Ensemble des utilisateurs de l'environnement, avec qui un propriétaire peut partager un plat.

**MealFlow**:
Regroupement nommé de plats (« Semaine végétarienne », « Repas du dimanche »), qui sert de réservoir à la génération. C'est le nom produit de ce que le code historique appelle « playlist ».
_Avoid_: Playlist, liste de plats

**Semaine**:
Planning des repas du midi et du soir sur les jours de la semaine d'un utilisateur.
_Avoid_: Planning (sauf en langage courant)

**Portions**:
Nombre de personnes pour lesquelles un repas de la Semaine est prévu. Les quantités d'un plat sont définies pour une personne et sont ajustées selon les portions du repas.
_Avoid_: Convives, couverts

**Étape**:
Instruction ordonnée de la recette d'un plat.
_Avoid_: Instruction, procédure

**Moment**:
Créneau d'une journée où l'on mange (midi ou soir). Un plat est adapté à un ou plusieurs moments, et l'utilisateur choisit pour chaque moment de la Semaine s'il y planifie un repas ou non.
_Avoid_: Service, créneau

**Repas**:
Occupation d'un Moment d'un jour de la Semaine : un Plat et un nombre de Portions. Les Repas alimentent la Liste de courses.
_Avoid_: Planif, entrée de semaine

**Rayon**:
Catégorie de magasin (légumes, frais, viande…) choisie dans une liste fixe, qui regroupe les articles de la Liste de courses. Elle est propre à chaque utilisateur, selon son magasin.
_Avoid_: Label, catégorie d'ingrédient

**Préférences**:
Choix persistants de l'utilisateur sur les Moments de la semaine où il veut un repas planifié. Seuls ces Moments sont remplis à chaque Proposition.
_Avoid_: Paramètres, sélection du jour

**Conditionnement**:
Quantité dans laquelle un utilisateur peut acheter un ingrédient dans son magasin (ex. 2 ou 3 portions de gnocchis, 10 ou 20 nuggets), avec son prix. Il est propre à chaque utilisateur, car les magasins diffèrent.
_Avoid_: Format, lot, pack

**Reste**:
Quantité d'un ingrédient achetée en Conditionnement mais non consommée par les repas de la Semaine.
_Avoid_: Surplus, gaspillage

**Étiquette**:
Caractéristique d'un plat issue d'une liste fixe (ex. asiatique, viande, végé), utilisée par la sélection pour composer la Semaine.
_Avoid_: Tag, catégorie

**Unité**:
Mesure d'une quantité d'ingrédient, choisie dans une liste fixe. Seules des quantités de même unité se cumulent dans la Liste de courses.
_Avoid_: Type de quantité

**Liste de courses**:
Cumul des quantités d'ingrédients des repas de la Semaine, additionné aux Extras.
_Avoid_: Panier

**Proposition**:
Semaine générée à la demande de l'utilisateur, que celui-ci peut annuler pour revenir à la précédente ou ajuster jour par jour.
_Avoid_: Génération automatique

**Extra**:
Article de la liste de courses sans lien avec un plat (lait, mouchoirs, chips).
_Avoid_: Divers, hors-plat
