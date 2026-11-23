# Règle métier — E5 « Un game master ne peut pas se dédoubler »

## Formulation
Un game master ne peut pas être affecté à deux sessions dont les créneaux se chevauchent. Une session sans game master ne peut pas démarrer.

## Pourquoi existe-t-elle ?
Un game master anime une seule salle à la fois. Une double affectation laisserait un groupe sans animateur au moment où les joueurs arrivent.

## Entités concernées
- game master
- session (début, fin, état)
- salle

## Préconditions
- La session a une heure de début et une heure de fin.
- L'utilisateur a le rôle responsable.

## Comportement attendu
- Deux créneaux se chevauchent si `début < autre_fin ET fin > autre_début`.
- Deux créneaux jointifs (fin de l'un = début de l'autre) sont **acceptés**.
- Le contrôle est fait côté serveur, dans un service, pour le HTML comme pour l'API.

## Cas de refus
- Affecter le GM déjà prévu à 18 h–19 h en salle A à une session à 18 h–19 h en salle B → refus avec un message clair (HTML) ou une réponse 422 (API).
- Passer `EN_COURS` une session sans game master → refus.

## Implémentation
- service/classe :
- repository :
- contrôleur/route :
- BDD : `game_master_id` sur la session (ou table d'affectation)

## Tests

| Cas | Entrée / situation | Attendu |
|---|---|---|
| nominal | GM à 18 h–19 h, puis 19 h–20 h | accepté |
| refus | GM à 18 h–19 h en salle A, puis 18 h–19 h en salle B | refusé (422 en API) |
| refus | session sans GM → `EN_COURS` | refusé |
| limite | créneaux jointifs | accepté |
| contrôle | autre GM sur le même créneau | accepté |

## Preuves
- issue :
- commits :
- capture :
