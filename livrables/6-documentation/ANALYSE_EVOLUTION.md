# Analyse d'impact — ticket d'évolution du mercredi

Démarche du cours 2 : enregistrer → clarifier → évaluer l'impact → décider → actualiser → communiquer.

**Projet :** EscapeOps — Les Archives Obscures  
**Ticket :** EV-THO-01 « Le contrôle périodique devient bloquant pour l'ouverture » — [AstrowareConception/BTS2-projets-web#9](https://github.com/AstrowareConception/BTS2-projets-web/issues/9)  
**Date de réception :** mardi 24/11/2026 (publié), à traiter à partir du mercredi 25/11/2026  
**Demandé par :** le directeur de l'escape game (client), ticket transmis par l'enseignant référent

## 1. Demande

> L'alerte *Contrôle à prévoir* est utile, mais elle ne suffit pas : si une salle vient d'atteindre dix parties depuis sa dernière vérification, nous ne devons pas lancer une onzième partie avant qu'un responsable ait confirmé son contrôle. Les réservations futures peuvent rester enregistrées.

Travail demandé : une salle dont l'indicateur E9 est **CONTRÔLE À PRÉVOIR** ne peut pas démarrer une nouvelle session (transition `PRÉVUE/PRÊTE → EN_COURS`) tant qu'un contrôle de maintenance n'a pas été enregistré par un rôle autorisé. Les réservations existantes restent enregistrées. Pas de paiement ni d'annulation automatique.

Critères d'acceptation du ticket :
1. 9 sessions terminées depuis le dernier contrôle → la 10e peut démarrer ;
2. 10 sessions terminées → démarrage de la suivante refusé avec un motif précis ;
3. un responsable enregistre un contrôle → le démarrage redevient possible ;
4. une session annulée ne compte pas dans le seuil E9.

## 2. Besoin clarifié

Aujourd'hui, E9 **informe** : à 10 sessions terminées depuis le dernier contrôle, la salle affiche le badge, mais rien n'empêche de jouer. Le client veut qu'E9 **bloque** une seule action : **démarrer** une partie dans cette salle.

Ce qui change :
- Le passage `PRETE → EN_COURS` est refusé quand le compteur E9 de la salle est ≥ 10. Le motif nomme la salle, le compteur et l'action à faire, par exemple : « Démarrage impossible (règle E9) : Le Laboratoire Interdit a 10 sessions terminées depuis son dernier contrôle. Un responsable doit enregistrer un contrôle de maintenance. »
- Le refus est vérifié **côté serveur**, au moment du POST. Le bouton « Démarrer » est aussi désactivé avec ce motif sur la fiche session, comme pour E5.

Ce qui ne change pas :
- On peut toujours créer des sessions, les réserver, les marquer prêtes, affecter un game master et annuler une réservation dans une salle à 10/10. Les réservations futures restent enregistrées.
- La 10e partie elle-même démarre : au moment de son démarrage, le compteur vaut 9.
- Le calcul du compteur reste celui d'E9 : sessions `TERMINEE` finies après le dernier contrôle, annulées exclues.
- « Rôle autorisé » : le **responsable d'exploitation**. C'est déjà le seul rôle qui enregistre un contrôle (`/responsable/salles/{id}/controles`) et le seul qui fait avancer une session. Le game master et l'administrateur n'ont pas accès à ces actions. Je n'ajoute pas de rôle.

Point vérifié, pas de question ouverte : il n'existe pas d'autre chemin vers `EN_COURS`. Ni l'API JSON ni l'espace game master ne démarrent une session. Seul `SessionService::changerEtat()` le fait.

## 3. Impact

| Domaine | Impact | Détail |
|---|---|---|
| Code (services, contrôleurs, vues) | Faible | **Domaine :** `Maintenance::raisonRefusDemarrage(int $compteur, string $salle): ?string`, une règle pure et testable. **Service :** `SessionService::raisonDeRefus()` ajoute le cas `EN_COURS` + compteur ≥ 10, après le contrôle E5 (pas de game master), ce qui évite d'afficher deux refus à la fois. `SessionService` reçoit le calcul du compteur (aujourd'hui dans `MaintenanceService::bilan()`) : je le déplace dans une méthode partagée pour garder un seul calcul. **Contrôleur et vue :** rien à ajouter, `changerEtat()` relaie déjà le motif et `responsable/session.php` désactive déjà « Démarrer » quand `raison` est rempli. **Fiche salle du responsable :** une ligne sous le badge, « Le démarrage des sessions est bloqué jusqu'au contrôle ». |
| Base de données (schéma, vues, procédure, trigger) | Aucun changement de schéma | Les tables `session` et `controle_maintenance` suffisent. La procédure « bilan par salle » prévue en Q1 calculera ce même compteur : le blocage devra l'utiliser dès qu'elle existera, pour garder une seule source. Je n'ajoute pas de trigger bloquant en base : la règle dépend d'un comptage, et le message doit rester celui du site. |
| API | Aucun | Aucune route de l'API ne change l'état d'une session. Le contrat `docs/api.md` ne bouge pas. |
| Règles E5 / E7 / E9 | Risque faible, à rejouer | **E5 :** le refus « sans game master » reste prioritaire, et le CHECK `ck_session_gm` n'est pas touché. **E7 :** pas d'impact, les réservations ne sont ni annulées ni bloquées. **E9 :** même compteur et même badge. Le blocage s'appuie dessus : une erreur de comptage aurait désormais un effet bloquant, d'où les tests de seuil. **Socle :** les autres transitions (`PRETE`, `ANNULEE`, `TERMINEE`) ne sont pas concernées. Une partie déjà `EN_COURS` peut toujours se terminer. |
| Tests à ajouter / à rejouer | Moyen | **À ajouter** dans `tests/regles-e9.php` : 9 → démarrage autorisé, 10 → refus avec le nom de la salle et le compteur, 11 → refus, contrôle (0) → autorisé, 9 terminées + 1 annulée → autorisé. **À rejouer :** `composer test` (E5, E7, E9, S4/S5, saisie d'incident) et le journal de l'API. **Recette :** ajouter T12 (refus à 10/10) et T13 (démarrage possible après contrôle) à `RECETTE.md`. |
| Charge estimée | 2 h | 0 h 30 de tests, 0 h 45 de code (règle, service, ligne de la fiche salle), 0 h 30 de captures (refus puis retour à la normale) et de recette, 0 h 15 de commit et de communication. |
| Planning (jalons du 26 et du 27) | Tient dans Q2 (3 h, jeudi 26) | Ordre : Q1 (la procédure « bilan par salle ») puis Q2, pour que le blocage lise le compteur de la procédure. Si Q1 prend du retard, Q2 utilise le calcul PHP actuel, et Q1 le remplace ensuite sans changer le comportement. Aucun impact sur le vendredi 27 (gel des fonctionnalités, V1 à V4). La version réduite du ticket n'est pas nécessaire. |

## 4. Décision

**Accepté**, en version complète, à réaliser jeudi 26 dans Q2.

Pourquoi :
- l'évolution ne crée pas de quatrième règle : elle renforce E9, une règle déjà livrée et testée ;
- elle touche un seul point d'entrée (la transition vers `EN_COURS`), qui est déjà contrôlé côté serveur ;
- pas de migration, pas d'impact API ;
- la charge (2 h) tient dans le créneau Q2 sans toucher aux engagements E5, E7 et E9.

Qui décide :
- le développeur (moi) propose ;
- le client a formulé le besoin ;
- l'enseignant référent valide le périmètre. La version réduite n'est à demander qu'en cas de retard jeudi soir.

## 5. Actualisation

- Tâche Q2 mise à jour dans Jalon : ☐
- Changement enregistré dans Jalon (vue Changements) : ☐
- Branche Git : `feature/ev-tho-01-maintenance-bloquante`, fusionnée dans `projet/escapeops` après les tests
- `roadmap.md` : Q2 précisé (EV-THO-01), ☑

## 6. Communication

- Commentaire de décision à publier sur le ticket [AstrowareConception/BTS2-projets-web#9](https://github.com/AstrowareConception/BTS2-projets-web/issues/9) : ☐

  > Analyse faite (livrables/6-documentation/ANALYSE_EVOLUTION.md). Demande acceptée en version complète, prévue jeudi 26 (Q2, environ 2 h). Le démarrage d'une session (PRÉVUE/PRÊTE → EN_COURS) sera refusé côté serveur, avec le motif, dès que la salle a 10 sessions terminées depuis son dernier contrôle. Il redeviendra possible quand un responsable aura enregistré un contrôle. Les réservations restent enregistrées, l'API n'est pas concernée, et E5, E7 et E9 seront rejouées en non-régression.

- Point oral avec l'enseignant au rituel du soir : ☐
