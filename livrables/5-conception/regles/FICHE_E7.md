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
- service/classe :
- repository :
- contrôleur/route :
- BDD : colonnes `annulee_le` et `statut_annulation` sur la réservation

## Tests

| Cas | Entrée / situation | Attendu |
|---|---|---|
| nominal | annulation à 26 h du début | `SANS_FRAIS` |
| « refus » | annulation à 2 h du début | `TARDIVE` |
| limite | annulation à 24 h pile | `SANS_FRAIS` |
| persistance | relire la réservation | statut conservé |

## Preuves
- issue :
- commits :
- capture :
