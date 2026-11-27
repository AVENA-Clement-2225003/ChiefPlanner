# Déploiement par pull d'images via le Watchtower existant, migrations au démarrage, sans sauvegarde

GitHub Actions construit `chiefplanner-api` et `chiefplanner-front` vers GHCR. `master` donne le tag `:latest` (prod, `chiefplanner.michaelkineticsystems.fr`) et la branche `dev` donne `:dev` (`dev-chiefplanner.michaelkineticsystems.fr`) ; `v2` est mergée dans `dev` avant `master`. Le Watchtower d'Extrudo sur `origae` (`WATCHTOWER_LABEL_ENABLE=true`, poll 300 s, via docker-socket-proxy) est réutilisé : chaque conteneur ChiefPlanner porte le label `com.centurylinklabs.watchtower.enable=true`. Chaque environnement a son MySQL 8 et son `.env` hors dépôt sur `origae`.

Le conteneur API lance `php artisan migrate --force` au démarrage, donc chaque nouvelle image appliquée par Watchtower migre la base automatiquement.

## Consequences

- **Il n'y a aucune sauvegarde de la base**, par choix explicite. Une migration destructrice poussée sur `master`, ou la perte du volume MySQL, n'est pas récupérable. Les migrations doivent donc être écrites en tenant compte de cela.
- Il y a une fenêtre de redémarrage de l'API à chaque déploiement.
