# liv1 — Application EscapeOps

**Type :** technique · **Responsable :** moi · **Validation :** enseignant référent

Application web de planning et d'exploitation. Elle couvre :
- la vitrine publique ;
- les réservations de groupe, les sessions et les game masters ;
- les incidents et le tableau de bord ;
- les règles contractuelles E5, E7 et E9.

## Critères de validation

| ✔ | Critère | Vérification | Résultat obtenu | Date | Preuve |
|---|---|---|---|---|---|
| ☑ | E5 : l'affectation d'un GM sur une session qui chevauche est refusée | Affecter le même GM à deux salles à 18 h | Refusé : « Affectation refusée (règle E5) : Nora Benali anime déjà « La Crypte de l'Archiviste » de 18 h 00 à 19 h 00. » (session 31, Cabinet 18 h 30–19 h 30) | 25/11/2026 | `captures/2026-11-25_R1_refus-chevauchement.png`, `starter-kit/tests/regles-e5.php` |
| ☑ | E5 : des créneaux jointifs sont acceptés | Affecter le GM à 18 h–19 h, puis à 19 h–20 h | Accepté : Nora affectée au Cabinet 18 h–19 h (session 39) puis à la Crypte 19 h–20 h (session 40) | 25/11/2026 | `captures/2026-11-25_R1_jointif-accepte.png`, `starter-kit/tests/regles-e5.php` |
| ☑ | E5 : le démarrage est refusé sans game master | Passer `EN_COURS` une session sans affectation | Bouton « Démarrer » désactivé sur la session 41 (« Prête », sans GM) avec la raison « Démarrage impossible : aucun game master n'est affecté. » | 25/11/2026 | `captures/2026-11-25_R1_demarrage-sans-gm.png` |
| ☑ | E7 : une annulation est `SANS_FRAIS` à ≥ 24 h, `TARDIVE` sinon | Annuler à 26 h, à 24 h pile et à 2 h du début | Dans l'application : session 42 annulée 26 h avant → « classée SANS FRAIS » ; session 34 annulée 3 h avant → « classée TARDIVE » ; le badge reste affiché dans « Réservations annulées ». 24 h pile, 23 h 59 min 59 s et changement d'heure : vérifiés par le test (24 h pile → `SANS_FRAIS`) | 25/11/2026 | `captures/2026-11-25_R2_*.png` (4), `starter-kit/tests/regles-e7.php` |
| ☑ | E9 : le badge `CONTROLE_A_PREVOIR` apparaît à 10 sessions terminées, puis se remet à zéro | Terminer la 10e session, puis enregistrer un contrôle | Laboratoire Interdit : 9/10 sans badge (9 terminées + 1 annulée non comptée) ; session 24 terminée → 10/10 et badge « Contrôle à prévoir » ; contrôle enregistré → 0/10, badge retiré | 25/11/2026 | `captures/2026-11-25_R3_*.png` (4), `starter-kit/tests/regles-e9.php` |
| ☐ | Les droits sont vérifiés côté serveur | Tenter une action de responsable avec un compte game master (attendu : 403) | | | |

## Preuves attendues par tâche

| Tâche | Preuve | Fichier(s) |
|---|---|---|
| S3 Vitrine | Capture de la vitrine alimentée par la base | `captures/…_S3_vitrine.png` |
| S4 Connexion et rôles | Capture de la connexion + capture d'un refus 403 | `captures/…_S4_*.png` |
| S5 Salles, sessions, réservations | Captures du parcours du responsable | `captures/…_S5_*.png` |
| S6 Tableau de bord et recherche | Capture du tableau de bord | `captures/…_S6_tableau-de-bord.png` |
| R1 E5 | Tests `starter-kit/tests/regles-e5.php` + captures des refus HTML et API | `captures/2026-11-25_R1_*.png`, requête n° 12 de `../3-api/captures/2026-11-25_A1_journal-requetes.txt` |
| R2 E7 | Tests `starter-kit/tests/regles-e7.php` + captures à 26 h et à 3 h (la session de démo « dans 2 h » datait du seed de lundi) | `captures/2026-11-25_R2_*.png` |
| R3 E9 | Tests `starter-kit/tests/regles-e9.php` + captures 9/10, 10/10 avec badge, remise à zéro | `captures/2026-11-25_R3_*.png` |
| V4 Release | Lien de la release GitHub v1.0.0 | (lien ci-dessous) |

**Release v1.0.0 :** _lien à compléter_

> Definition of Done : la fonctionnalité marche depuis le navigateur avec des données en base, la règle est contrôlée côté serveur, les cas accepté, refusé et limite sont testés, le message est clair, le code est commité et poussé.
