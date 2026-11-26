# Base de données EscapeOps

Moteur utilisé et testé : **MariaDB 13.0** (Windows). Compatible MariaDB ≥ 10.2.1 et MySQL ≥ 8.0.16, les versions qui appliquent les `CHECK`.
Le modèle vient de [livrables/5-conception/modelisation](../../livrables/5-conception/modelisation/MLD.md).

## Scripts, dans l'ordre

| Ordre | Fichier | Contenu | Tâche |
|---|---|---|---|
| 1 | `migrations/001_schema.sql` | 9 tables, clés étrangères, `CHECK` (E5, E7), index | S2 |
| 2 | `migrations/002_vues.sql` | vues `v_planning` et `v_tableau_bord` | Q1 |
| 3 | `migrations/003_procedures.sql` | procédure `sp_bilan_salle` : compteur E9 d'une salle | Q1 |
| 4 | `migrations/004_triggers.sql` | trigger `trg_session_historique` : historique des changements d'état | Q1 |
| 5 | `seed.sql` | données fictives de démonstration | S2 |

`001_schema.sql` supprime puis recrée les tables : on peut le rejouer à tout moment pour repartir d'une base propre.

## Reconstruire la base

Depuis le dossier `starter-kit`, dans PowerShell. Le mot de passe root est demandé une seule fois.

```powershell
& "C:\Program Files\MariaDB 13.0\bin\mariadb.exe" -u root -p --default-character-set=utf8mb4 -e "DROP DATABASE IF EXISTS escapeops; CREATE DATABASE escapeops CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; USE escapeops; SOURCE database/migrations/001_schema.sql; SOURCE database/migrations/002_vues.sql; SOURCE database/migrations/003_procedures.sql; SOURCE database/migrations/004_triggers.sql; SOURCE database/seed.sql;"
```

002 à 004 se rejouent seuls sur une base existante, sans perdre les données (`DROP … IF EXISTS` puis `CREATE`) : remplacez la liste des `SOURCE` par les trois fichiers.

Ne passez pas les fichiers par un pipe PowerShell (`Get-Content … | mariadb`) : PowerShell 5.1 abîme les accents. Le client MariaDB doit lire lui-même les fichiers avec `SOURCE`.

## Objets SQL avancés (Q1) et où le code les utilise

| Objet | Rôle | Utilisé par |
|---|---|---|
| `v_planning` | une ligne par session avec sa salle, son game master et sa réservation confirmée | `SessionRepository::planning()`, `trouver()`, `parGameMaster()` |
| `v_tableau_bord` | synthèse du jour par salle : sessions, annulées, sans game master, prochaine session, incidents ouverts, bloquants, critiques | `SalleRepository::tableauDeBord()`, pour le cockpit (S6) |
| `sp_bilan_salle(id)` | dernier contrôle et compteur E9 (sessions `TERMINEE` finies après ce contrôle) | `SalleRepository::bilan()`, puis `MaintenanceService` (badge E9) |
| `trg_session_historique` | `AFTER UPDATE` sur `session` : une ligne dans `historique_session` quand l'état change, rien sinon | se déclenche à chaque `SessionRepository::changerEtat()` |

Test sur la vraie base (dans une transaction annulée, la base n'est pas modifiée) :

```powershell
composer test-bdd
```

## Données de démonstration

Les dates du seed sont **relatives au moment de l'import**. Il faut rejouer le seed le matin de la démonstration.

Mot de passe de tous les comptes (fictif) : `demo-escapeops-2026`

| Compte | Rôle |
|---|---|
| admin@archives-obscures.example | ADMIN |
| camille.roux@archives-obscures.example | RESPONSABLE |
| lea.martin@archives-obscures.example | GAME_MASTER |
| hugo.bernard@archives-obscures.example | GAME_MASTER |
| nora.benali@archives-obscures.example | GAME_MASTER |
| maxime.lefevre@archives-obscures.example | GAME_MASTER |
| jules.petit@archives-obscures.example | GAME_MASTER, compte désactivé |

| Règle | Situation préparée | Résultat attendu |
|---|---|---|
| E5 | Session 25 : Cabinet, aujourd'hui 18 h–19 h, animée par Léa Martin. Session 26 : Crypte, même créneau, sans game master | Affecter Léa à la 26 : **refusé**. Démarrer la 26 : **refusé** |
| E5 | Session 27 : Crypte, aujourd'hui 19 h–20 h, sans game master | Affecter Léa : **accepté** (créneaux jointifs) |
| E7 | Réservation `AO-INSOMNIA` : session dans 1 à 2 h | Annulation classée **TARDIVE** |
| E7 | Réservation `AO-ARCHIMED` : session dans 2 jours | Annulation classée **SANS_FRAIS** |
| E9 | Laboratoire Interdit : 9 sessions terminées et 1 annulée depuis le dernier contrôle. Session 24 en cours | Terminer la 24 donne la **10e** partie, et le badge `CONTROLE_A_PREVOIR` apparaît |
| E9 | Cabinet : 4 sessions terminées depuis le contrôle. Crypte : 6 | Pas de badge |

## Vérifications faites sur une base neuve (23/11/2026)

- Le schéma et le seed s'importent sans erreur, deux fois de suite.
- Contenu : 7 utilisateurs, 3 salles, 37 sessions, 30 réservations, 7 équipements, 8 affectations, 3 incidents, 3 contrôles.
- Les compteurs E9 donnent 4, 6 et 9. Aucun game master ni aucune salle n'a deux sessions qui se chevauchent. Les deux annulations du seed respectent la règle des 24 h.
- Les contraintes ont refusé les 14 cas invalides testés, par exemple :
  - une session `EN_COURS` ou `TERMINEE` sans game master ;
  - une fin avant le début ;
  - une réservation `ANNULEE` sans classement, ou un classement sans annulation ;
  - 0 joueur, ou un code de réservation en double ;
  - une capacité maximale inférieure à la minimale ;
  - un incident `RESOLU` sans date ;
  - la suppression d'un game master qui anime encore des sessions.
- `password_verify('demo-escapeops-2026', mdp_hash)` renvoie vrai.

## Vérifications Q1 sur une base neuve (26/11/2026)

- 001 à 004 puis le seed s'importent sans erreur sur une base vide (`escapeops_q1`).
- `CALL sp_bilan_salle(1)`, `(2)`, `(3)` donnent 4, 6 et 9, comme le calcul PHP.
- `composer test-bdd` : 50 vérifications vertes (vues, procédure, trigger, scénario PREVUE → PRETE → EN_COURS → TERMINEE, annulation ignorée, contrôle qui remet à zéro).
