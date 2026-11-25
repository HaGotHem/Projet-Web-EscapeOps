# Roadmap EscapeOps — tâches restantes

Point au **mercredi 25 novembre 2026**. Release **v1.0.0 le vendredi 27 novembre**.
Les statuts officiels restent dans Jalon. Cette liste sert à suivre la fin de semaine.

Déjà fait : C1, C2, C3 (choix des règles, maquettes, MCD/MLD), S1, S2, S3, S4, S5 (dépôt, base, vitrine, connexion et rôles, sessions et réservations), et la fiche salle publique.

## Mercredi 25 — règles E5, E7, E9 et API JSON

Jalon du soir : 🟢 3 règles + API · 🟠 2 règles + socle stable · 🔴 moins de 2 règles.

- [x] **R1 · E5 : refuser le dédoublement d'un game master** — 4 h
  - Affectation refusée si le game master a déjà une session qui chevauche ; créneaux jointifs acceptés ; démarrage refusé sans game master.
  - Preuves : `tests/regle-e5.php`, captures `…_R1_refus-gm-18h.png`, `…_R1_jointif-accepte.png`, `…_R1_demarrage-sans-gm.png` (maquettes 06 et 07).
- [x] **R2 · E7 : classer les annulations `SANS_FRAIS` / `TARDIVE`** — 2 h
  - Bouton « Annuler la réservation » sur la fiche session ; au moins 24 h avant le début → `SANS_FRAIS`, sinon `TARDIVE`.
  - Preuves : `tests/regle-e7.php` (26 h, 24 h pile, 2 h), captures 26 h et 2 h (maquettes 08a et 08b).
- [x] **R3 · E9 : badge `CONTROLE_A_PREVOIR` après 10 sessions** — 3 h
  - Fiche salle du responsable : compteur depuis le dernier contrôle, badge à 10, « Enregistrer un contrôle » qui remet à zéro ; sessions annulées ignorées.
  - Preuves : `tests/regle-e9.php` (9/10, 10/10, remise à zéro), capture du badge (maquette 09).
- [x] **C4 · Rédiger le contrat d'API** — 1 h
  - `docs/api.md` : routes, codes 200 / 201 / 404 / 422, exemples de requêtes et de réponses.
- [x] **A1 · Exposer l'API JSON des salles, sessions et incidents** — 2 h
  - Liste, détail et une écriture (incident ou réservation) ; erreurs 404 et 422.
  - Preuves : requêtes HTTP de test, captures dans `livrables/3-api/captures/`.
- [x] **A2 · Analyser l'impact du ticket d'évolution** — 1 h
  - Remplir `livrables/6-documentation/ANALYSE_EVOLUTION.md` **avant** de toucher au code.
  - Ticket EV-THO-01 (AstrowareConception/BTS2-projets-web#9) : accepté en version complète, à faire en Q2.

## Jeudi 26 — évolution et incident

Jalon du soir : l'évolution et l'incident sont traçables, les trois règles restent stables.

- [ ] **Q1 · Créer les 2 vues, la procédure stockée et le trigger** — 4 h
  - Vue planning, vue tableau de bord, procédure de bilan par salle (compteur E9), trigger d'historique des sessions.
  - Preuves : `database/` dans Git + test de reconstruction sur base neuve.
- [ ] **S6 · Cockpit du responsable et saisie d'incident** — 2 h (+ 1 h 30 pour les incidents)
  - Tableau de bord sur la vue SQL (maquette 05) ; formulaire d'incident du game master (maquette 10).
  - La saisie d'incident n'a pas encore de tâche dans Jalon : à y ajouter.
  - Preuve : capture du tableau de bord.
- [ ] **Q2 · Implémenter l'évolution et rejouer la non-régression** — 3 h (estimé 2 h)
  - EV-THO-01 : refuser `PRETE → EN_COURS` quand la salle est à 10/10 (E9) ; un contrôle du responsable débloque.
  - Tests 9 / 10 / 11 / remise à zéro / annulée ignorée ; recette T12-T13 ; capture du refus puis du retour à la normale.
  - Après Q1 si possible, pour lire le compteur de la procédure « bilan par salle ».
  - Preuves : commit + résultats des tests.
- [ ] **Q3 · Corriger l'incident et rédiger le rapport** — 2 h
  - Preuve : `livrables/6-documentation/RAPPORT_INCIDENT.md`.

## Vendredi 27 — livrer, ne plus ajouter

- [ ] **V1 · Dérouler la recette des règles E5, E7 et E9** — 2 h
  - Preuve : `livrables/4-recette-tests/RECETTE.md` (T01 à T11).
- [ ] **V2 · Vérifier la sécurité et la sauvegarde / restauration** — 1 h
  - Preuve : section sécurité de `RECETTE.md`.
  - Mettre `APP_ENV=prod` et `APP_DEBUG=false` sur la démo : le menu debug disparaît.
- [ ] **V3 · Rédiger le README et l'installation** — 2 h
- [ ] **V4 · Publier la release v1.0.0 et préparer la démo** — 1 h
  - Preuves : release GitHub + `livrables/6-documentation/DEMONSTRATION.md` (démo de 5 à 7 minutes).

## Extensions (seulement si tout le reste est stable)

- [ ] X1 · E10 : checklist avant ouverture — 2 h
- [ ] X2 · E4 : 20 minutes entre deux sessions — 2 h
- [ ] X3 · E3 : groupe de 2 à 6 joueurs — 1 h

## Chemin critique et plan B

- Chemin critique : `R3 → Q1 → V2 → V4` et `A2 → Q2 → V1 → V4`.
- Si un jalon est manqué, on coupe dans cet ordre : extensions X1 à X3 (présentées à l'oral comme pistes), puis cockpit et recherche (S6) réduits au minimum.
- On ne coupe jamais E5, E7, E9, l'API JSON ni le SQL avancé contractuel (2 vues, 1 procédure, 1 trigger).

## Chaque soir

1. Captures dans `livrables/` au format `AAAA-MM-JJ_TACHE_description.png`, critères cochés dans les README des livrables.
2. Jalon : statut, charge réelle, chemin de la preuve, jalon du jour coché s'il est atteint.
3. Commit et push du travail du jour.
