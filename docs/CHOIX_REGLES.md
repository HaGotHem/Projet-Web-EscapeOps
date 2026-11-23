# Réservation de la personnalisation

**Étudiant :**  
**Projet :** EscapeOps — « Les Archives Obscures »  
**Date :** 23/11/2026  
**Validation enseignant :**

## 3 règles contractuelles à livrer

| ID | Famille | Difficulté | Entités concernées | Démo / cas de refus |
|---|---|---|---|---|
| E5 — « Un game master ne peut pas se dédoubler » | personnel / temps | intermédiaire | game master, session, salle | Le responsable affecte le même game master à deux salles à 18 h : la seconde affectation est **refusée**. 18 h–19 h puis 19 h–20 h (créneaux jointifs) : **accepté**. Une session sans game master ne peut pas passer `EN_COURS`. |
| E7 — « Annuler au bon moment » | temps / politique commerciale | accessible | réservation, session | Annulation à 26 h du début : `SANS_FRAIS` ; à 2 h : `TARDIVE`. Cas limite : exactement 24 h → `SANS_FRAIS`. Le statut reste enregistré et visible du responsable et du client. Aucun paiement traité. |
| E9 — « Maintenance préventive après dix parties » | historique / seuil | intermédiaire | salle, session, contrôle de maintenance | À la 9e session terminée : pas de badge ; à la 10e : la salle affiche `CONTROLE_A_PREVOIR`. Le responsable enregistre un contrôle : le compteur repart à 0. Les sessions annulées ne comptent pas. |

**Triplet trié (sans ordre) :** `E5 + E7 + E9`

## 3 extensions réservées

| Priorité | ID | Intérêt pour le client | Quand l'envisager ? |
|---:|---|---|---|
| 1 | E10 — Checklist avant ouverture | Trace que la salle est rangée, le matériel testé et les consignes de sécurité vérifiées avant de passer une session à `PRETE`. | après socle et 3 contractuelles |
| 2 | E4 — Vingt minutes entre deux missions | Garantit le temps de remise en état entre deux sessions de la même salle (cas du « Cabinet des Ombres »). | si temps restant |
| 3 | E3 — Groupe de deux à six aventuriers | Refuse une réservation hors de la taille de groupe autorisée par la salle. | piste d'évolution |

## Mon scénario de démonstration en une phrase

> « Vendredi, je montrerai à un responsable d'exploitation qui tente d'affecter le game master déjà prévu à 18 h à une seconde salle au même horaire, et l'application refuse parce que la règle E5 interdit qu'un game master se dédouble ; puis un groupe annule à 2 h du début et l'annulation est classée `TARDIVE` (E7), et la salle qui vient de terminer sa 10e partie affiche `CONTROLE_A_PREVOIR` (E9). »

## Mon risque technique le plus important

> Le calcul du chevauchement pour E5 : il faut refuser un vrai conflit mais accepter deux créneaux jointifs (fin de l'un = début de l'autre). Je l'isole dans un service (`début < autre_fin ET fin > autre_début`) et j'écris les tests conflit / jointif / démarrage sans game master **avant** de le brancher sur les formulaires et l'API. Second risque : la frontière des 24 h d'E7 (fuseau horaire) → `DateTimeImmutable` en Europe/Paris et horloge injectable pour tester 24 h pile.

## Tâches / issues

- [ ] E5 — refuser l'affectation d'un game master sur une session qui chevauche (HTML + API, réponse 422 en API)
- [ ] E5 — refuser le passage `EN_COURS` d'une session sans game master
- [ ] E5 — tests : conflit, créneaux jointifs, autre game master, démarrage sans affectation
- [ ] E7 — calculer et enregistrer `SANS_FRAIS` / `TARDIVE` à l'annulation
- [ ] E7 — tests : 26 h, 24 h pile, 2 h, statut conservé
- [ ] E9 — compter les sessions `TERMINEE` depuis le dernier contrôle et afficher le badge
- [ ] E9 — enregistrement d'un contrôle par le responsable (remise à zéro)
- [ ] E9 — tests : 9/10, 10/10, remise à zéro, sessions annulées ignorées
- [ ] Backlog : E10, puis E4, puis E3 (seulement si le socle et les 3 règles sont stables)

## Dépendances de données

- **E5** : table d'affectation session ↔ game master (ou colonne `game_master_id` sur la session), heures de début et de fin de session ; données de démo : 2 game masters, 2 sessions qui se chevauchent dans 2 salles différentes, 2 sessions jointives.
- **E7** : colonnes `annulee_le` et `statut_annulation` (`SANS_FRAIS` / `TARDIVE`) sur la réservation ; données de démo : une session à J+2 et une session dans 2 h.
- **E9** : table des contrôles de maintenance (salle, date, auteur) ; données de démo : une salle avec 9 sessions terminées depuis son dernier contrôle, et 1 session annulée.
- **SQL avancé** (sans ajouter de règle) : vue planning, vue tableau de bord, procédure de bilan par salle (compteur E9), trigger d'historique des transitions de session.

## Vérifications avant validation

- [ ] même triplet interdit pour les étudiants ayant le même sujet ;
- [x] deux familles différentes ou plus ; *(personnel/temps, temps/politique commerciale, historique/seuil)*
- [x] au moins une règle multi-entités ; *(E5 : game master + session + salle ; E9 : salle + sessions + contrôle)*
- [x] au moins une règle intermédiaire ou avancée ; *(E5 et E9 sont intermédiaires)*
- [x] trois règles réellement démontrables, et non trois champs `required` ;
- [x] extensions séparées de l'engagement contractuel ;
- [ ] date et validation enseignant.

Copiez la [fiche de règle métier](../templates/FICHE_REGLE_METIER.md) pour documenter les règles contractuelles.
