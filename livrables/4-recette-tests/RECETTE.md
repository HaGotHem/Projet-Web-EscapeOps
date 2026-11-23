# Rapport de recette

**Projet :** EscapeOps — Les Archives Obscures  
**Version :** v1.0.0  
**Date :**

| ID | Fonction / règle | Précondition | Action | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|---|
| T01 | E5 conflit | GM affecté à la salle A à 18 h–19 h | Affecter le même GM à la salle B à 18 h–19 h | Refus avec message clair (HTML) / 422 (API) | | ✅/❌ |
| T02 | E5 jointif | GM affecté à 18 h–19 h | Affecter le même GM à 19 h–20 h | Accepté | | ✅/❌ |
| T03 | E5 démarrage | Session `PRETE` sans game master | Passer la session `EN_COURS` | Refus | | ✅/❌ |
| T04 | E7 sans frais | Session dans 26 h | Annuler la réservation | `SANS_FRAIS` enregistré et visible | | ✅/❌ |
| T05 | E7 limite | Session dans exactement 24 h | Annuler la réservation | `SANS_FRAIS` | | ✅/❌ |
| T06 | E7 tardive | Session dans 2 h | Annuler la réservation | `TARDIVE` enregistré et visible | | ✅/❌ |
| T07 | E9 seuil −1 | Salle à 9 sessions terminées depuis le dernier contrôle | Afficher la salle | Pas de badge | | ✅/❌ |
| T08 | E9 seuil | Salle à 9 sessions terminées | Terminer la 10e session | Badge `CONTROLE_A_PREVOIR` | | ✅/❌ |
| T09 | E9 remise à zéro | Badge affiché | Enregistrer un contrôle | Compteur à 0, badge retiré | | ✅/❌ |
| T10 | E9 annulées | Salle à 9 terminées + 1 annulée | Afficher la salle | Pas de badge | | ✅/❌ |
| T11 | Droits | Connecté en game master | Appeler une action de responsable | 403 | | ✅/❌ |

## Tests des règles contractuelles

### Règle 1 — E5 « Un game master ne peut pas se dédoubler »
- cas nominal : T02 (créneaux jointifs acceptés)
- cas de refus : T01 (chevauchement), T03 (démarrage sans GM)
- cas limite : T02 (fin de l'un = début de l'autre)

### Règle 2 — E7 « Annuler au bon moment »
- cas nominal : T04 (26 h → `SANS_FRAIS`)
- cas de refus : T06 (2 h → `TARDIVE`)
- cas limite : T05 (24 h pile → `SANS_FRAIS`)

### Règle 3 — E9 « Maintenance préventive après dix parties »
- cas nominal : T08 (10e session → badge)
- cas de refus : T07 (9 sessions → pas de badge)
- cas limite : T09 (remise à zéro), T10 (annulées ignorées)

## Sécurité et sauvegarde (V2)

| Contrôle | Méthode | Résultat obtenu | Statut |
|---|---|---|---|
| Mots de passe hachés | Lire la table des utilisateurs | | ✅/❌ |
| Jeton CSRF sur les formulaires | Envoyer un formulaire sans jeton | | ✅/❌ |
| Droits côté serveur | T11 | | ✅/❌ |
| Aucun secret dans le dépôt | Vérifier `.gitignore` et l'historique | | ✅/❌ |
| Sauvegarde | Faire un dump de la base | | ✅/❌ |
| Restauration | Restaurer le dump sur une base neuve, puis rejouer T01 | | ✅/❌ |

## Non-régression après évolution
Décrire l'évolution, puis les tests rejoués (au minimum T01 à T10).

## Non-régression après incident
Décrire le test ajouté et les tests rejoués.

## Anomalies connues
Lister les anomalies restantes et leur impact.
