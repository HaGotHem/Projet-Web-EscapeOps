# Règle métier — E9 « Maintenance préventive après dix parties »

## Formulation
Après 10 sessions terminées depuis son dernier contrôle, une salle affiche `CONTROLE_A_PREVOIR`, jusqu'à ce que le responsable enregistre un contrôle.

## Pourquoi existe-t-elle ?
Les décors et mécanismes s'usent à chaque partie. Un contrôle régulier évite qu'une énigme tombe en panne pendant une session.

## Entités concernées
- salle
- session (état `TERMINEE` / `ANNULEE`)
- contrôle de maintenance (salle, date, auteur)

## Préconditions
- La salle existe.
- Pour enregistrer un contrôle, l'utilisateur a le rôle responsable.

## Comportement attendu
- Le compteur ne prend en compte que les sessions `TERMINEE` postérieures au dernier contrôle.
- Les sessions annulées ne comptent pas.
- Le compteur est calculé par la procédure stockée « bilan par salle » (voir liv2).

## Cas de refus
- 9 sessions terminées → pas de badge.

## Implémentation
- classe : `App\Domain\Maintenance::compter()` et `controleAPrevoir()` (seuil 10)
- service : `MaintenanceService` (compteur par salle, enregistrement d'un contrôle par le responsable connecté)
- repository : `SalleRepository::controles()`, `sessionsFinies()`, `enregistrerControle()`
- contrôleur/route : `EquipeController`, `GET /responsable/salles`, `GET /responsable/salles/{id}`, `POST /responsable/salles/{id}/controles`
- une partie terminée en avance prend sa vraie heure de fin (`SessionRepository::changerEtat`), sinon elle compterait encore après un contrôle
- BDD : table `controle_maintenance` ; la procédure stockée « bilan par salle » remplacera le calcul PHP en Q1

## Tests

| Cas | Entrée / situation | Attendu |
|---|---|---|
| nominal | 10e session terminée | badge `CONTROLE_A_PREVOIR` |
| refus | 9 sessions terminées | pas de badge |
| limite | contrôle enregistré | compteur à 0, badge retiré |
| limite | 9 terminées + 1 annulée | pas de badge |

## Preuves
- tests : `starter-kit/tests/regles-e9.php` (9/10, 10/10, annulée ignorée, remise à zéro)
- captures : `livrables/1-application/captures/2026-11-25_R3_*.png`
