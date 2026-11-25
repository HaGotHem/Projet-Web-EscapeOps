# Règle métier — E7 « Annuler au bon moment »

## Formulation
Une annulation faite au moins 24 h avant le début de la session est classée `SANS_FRAIS`. En dessous de 24 h, elle est classée `TARDIVE`.

## Pourquoi existe-t-elle ?
Une annulation tardive laisse un créneau vide qu'on ne peut plus revendre. Le responsable doit voir quelles annulations relèvent de la politique commerciale. Aucun paiement n'est traité : la règle informe seulement.

## Entités concernées
- réservation (`annulee_le`, `statut_annulation`)
- session (début)

## Préconditions
- La réservation est active.
- La session n'a pas encore commencé.

## Comportement attendu
- Le délai est calculé entre l'heure d'annulation et le début de la session.
- Dates en `DateTimeImmutable`, fuseau Europe/Paris, avec une horloge injectable pour tester 24 h pile.
- Le statut est enregistré et visible du responsable et du client.

## Cas de refus
- Ce n'est pas un refus : à moins de 24 h, l'annulation est acceptée mais classée `TARDIVE`.

## Implémentation
- classe : `App\Domain\Annulation::classer($maintenant, $debut)`, règle pure, horloge passée en paramètre (24 h réelles, comparaison de timestamps)
- service : `SessionService::apercuAnnulation()` (classement annoncé avant de confirmer) et `annulerReservation()` (recalculé au POST, avec l'heure de la base)
- repository : `ReservationRepository::annuler()` (seulement si encore `CONFIRMEE`), `annulees()`
- contrôleur/route : `EquipeController`, `GET` et `POST /responsable/sessions/{id}/annulation` ; bouton « Annuler la réservation » sur la fiche session
- BDD : colonnes `annulee_le` et `statut_annulation` sur la réservation, CHECK `ck_reservation_annulation`

## Tests

| Cas | Entrée / situation | Attendu |
|---|---|---|
| nominal | annulation à 26 h du début | `SANS_FRAIS` |
| « refus » | annulation à 2 h du début | `TARDIVE` |
| limite | annulation à 24 h pile | `SANS_FRAIS` |
| persistance | relire la réservation | statut conservé |

## Preuves
- tests : `starter-kit/tests/regles-e7.php` (26 h, 24 h pile, 23 h 59 min 59 s, 2 h, changement d'heure)
- captures : `livrables/1-application/captures/2026-11-25_R2_*.png`
