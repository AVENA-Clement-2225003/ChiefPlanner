# Quantités stockées pour une personne ; paquets et Restes calculés à la demande

La quantité d'un ingrédient dans un plat est stockée pour une personne (`DECIMAL(10,4)`). À la création, l'utilisateur saisit pour N personnes et le serveur divise par N ; un avertissement non bloquant apparaît si le résultat n'est pas entier pour une unité de comptage. Le nombre de Portions est porté par chaque Repas, et la Liste de courses est calculée à la demande à partir des Repas.

Le Conditionnement (quantités achetables et prix optionnel) est un réglage par utilisateur et par ingrédient. La Liste de courses en déduit la combinaison de paquets la plus juste et le Reste. L'algorithme de sélection peut répéter un plat, au plus 2 fois dans la Semaine, s'il réduit le Reste total.

## Consequences

- Comme 1/3 ne se représente pas exactement, le calcul des courses arrondit avec une petite tolérance pour éviter d'acheter un paquet de trop à cause d'un 0,9999.
- Les Unités se cumulent seulement dans une même famille (masse, volume, comptage) ; il n'y a pas de conversion entre familles.
