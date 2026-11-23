# liv2 — Base de données et objets SQL avancés

**Type :** technique · **Responsable :** moi · **Validation :** enseignant référent

Le livrable comprend :
- le schéma et les données fictives ;
- 2 vues, 1 procédure stockée et 1 trigger ;
- le tout versionné dans Git et reconstructible ([docs/27-sql-avance-dans-le-projet.md](../../docs/27-sql-avance-dans-le-projet.md)).

## Critères de validation

| ✔ | Critère | Vérification | Résultat obtenu | Date | Preuve |
|---|---|---|---|---|---|
| ☐ | La base se reconstruit sur une base neuve | Exécuter les scripts versionnés sur une base vide | | | |
| ☐ | Deux vues sont lues depuis PHP | Afficher le planning et le tableau de bord | | | |
| ☐ | La procédure stockée est appelée depuis un repository | Afficher le bilan par salle (compteur E9) | | | |
| ☐ | Le trigger métier se déclenche | Changer l'état d'une session et lire l'historique | | | |

## Objets SQL prévus

| Objet | Rôle | Script | Utilisé dans (repository / page) |
|---|---|---|---|
| Vue planning | Sessions à venir par salle | `database/advanced.sql` | |
| Vue tableau de bord | Prochaines sessions, incidents ouverts, disponibilité | `database/advanced.sql` | |
| Procédure bilan par salle | Compteur E9 depuis le dernier contrôle | `database/advanced.sql` | |
| Trigger historique | Trace chaque transition d'état de session | `database/advanced.sql` | |

## Ordre de reconstruction

1. `database/schema.sql`
2. `database/seed.sql`
3. `database/advanced.sql`

## Preuves attendues par tâche

| Tâche | Preuve |
|---|---|
| S2 Schéma et données fictives | `database/schema.sql`, `database/seed.sql` + capture de la base reconstruite |
| Q1 Vues, procédure, trigger | `database/advanced.sql` + capture du test de reconstruction + captures d'utilisation depuis PHP |
