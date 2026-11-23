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
| ☐ | E5 : l'affectation d'un GM sur une session qui chevauche est refusée | Affecter le même GM à deux salles à 18 h | | | |
| ☐ | E5 : des créneaux jointifs sont acceptés | Affecter le GM à 18 h–19 h, puis à 19 h–20 h | | | |
| ☐ | E5 : le démarrage est refusé sans game master | Passer `EN_COURS` une session sans affectation | | | |
| ☐ | E7 : une annulation est `SANS_FRAIS` à ≥ 24 h, `TARDIVE` sinon | Annuler à 26 h, à 24 h pile et à 2 h du début | | | |
| ☐ | E9 : le badge `CONTROLE_A_PREVOIR` apparaît à 10 sessions terminées, puis se remet à zéro | Terminer la 10e session, puis enregistrer un contrôle | | | |
| ☐ | Les droits sont vérifiés côté serveur | Tenter une action de responsable avec un compte game master (attendu : 403) | | | |

## Preuves attendues par tâche

| Tâche | Preuve | Fichier(s) |
|---|---|---|
| S3 Vitrine | Capture de la vitrine alimentée par la base | `captures/…_S3_vitrine.png` |
| S4 Connexion et rôles | Capture de la connexion + capture d'un refus 403 | `captures/…_S4_*.png` |
| S5 Salles, sessions, réservations | Captures du parcours du responsable | `captures/…_S5_*.png` |
| S6 Tableau de bord et recherche | Capture du tableau de bord | `captures/…_S6_tableau-de-bord.png` |
| R1 E5 | Tests `tests/regle-e5` + captures des refus (HTML et API) | `captures/…_R1_*.png` |
| R2 E7 | Tests `tests/regle-e7` + captures à 26 h et à 2 h | `captures/…_R2_*.png` |
| R3 E9 | Tests `tests/regle-e9` + capture du badge | `captures/…_R3_*.png` |
| V4 Release | Lien de la release GitHub v1.0.0 | (lien ci-dessous) |

**Release v1.0.0 :** _lien à compléter_

> Definition of Done : la fonctionnalité marche depuis le navigateur avec des données en base, la règle est contrôlée côté serveur, les cas accepté, refusé et limite sont testés, le message est clair, le code est commité et poussé.
