# liv4 — Cahier de recette et tests

**Type :** validation · **Responsable :** moi · **Validation :** enseignant référent

Ce livrable regroupe :
- les cas positifs, négatifs et limites des règles ;
- la non-régression ;
- la sécurité ;
- la sauvegarde et la restauration.

Le cahier lui-même est dans [RECETTE.md](RECETTE.md).

## Critères de validation

| ✔ | Critère | Vérification | Résultat obtenu | Date | Preuve |
|---|---|---|---|---|---|
| ☐ | Chaque règle contractuelle a un cas positif, un cas négatif et un cas limite | Relire RECETTE.md avec les critères de réussite | | | |
| ☐ | Les tests de non-régression passent après l'évolution | Rejouer les tests E5, E7 et E9 | | | |
| ☐ | La restauration de la base est vérifiée | Restaurer un dump sur une base neuve | | | |

## Preuves attendues par tâche

| Tâche | Preuve |
|---|---|
| Q2 Évolution + non-régression | Commit de l'évolution + résultats des tests rejoués (section « Non-régression après évolution ») |
| V1 Recette E5, E7, E9 | RECETTE.md rempli |
| V2 Sécurité, sauvegarde et restauration | Section « Sécurité et sauvegarde » de RECETTE.md + captures |
