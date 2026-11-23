# Scénario de démonstration personnelle

**Sujet :** EscapeOps — Les Archives Obscures  
**Étudiant :**  
**Trois règles contractuelles :** E5 + E7 + E9

> « Vendredi, je montrerai à un responsable d'exploitation qui tente d'affecter le game master déjà prévu à 18 h à une seconde salle au même horaire, et l'application refuse parce que la règle E5 interdit qu'un game master se dédouble ; puis un groupe annule à 2 h du début et l'annulation est classée `TARDIVE` (E7), et la salle qui vient de terminer sa 10e partie affiche `CONTROLE_A_PREVOIR` (E9). »

## Démo de 5 à 7 minutes

### 1. Client / besoin (40 secondes)
### 2. Maquette / architecture / MCD-MLD (50 secondes)
### 3. Parcours fonctionnel (1 minute)
### 4. E5 — affectation refusée à 18 h, créneau jointif accepté (30 secondes)
### 5. E7 — annulation à 26 h `SANS_FRAIS`, à 2 h `TARDIVE` (30 secondes)
### 6. E9 — badge à la 10e session, remise à zéro par un contrôle (30 secondes)
### 7. API / HTTP / sécurité (1 minute)
### 8. Évolution / incident et tests (1 minute)
### 9. Déploiement / limites (30 secondes)

## Préparation des données

| Étape | Compte de démonstration | Données nécessaires | Résultat attendu |
|---|---|---|---|
| E5 | responsable | 2 game masters, 2 sessions à 18 h dans 2 salles, 2 sessions jointives | refus puis acceptation |
| E7 | responsable / client | une session à J+2, une session dans 2 h | `SANS_FRAIS` puis `TARDIVE` |
| E9 | responsable | une salle à 9 sessions terminées + 1 annulée | badge à la 10e, puis remise à zéro |

## Preuves
- Maquettes : `../5-conception/maquettes/`
- Modélisation : `../5-conception/modelisation/`
- API : `../3-api/`
- Tests : `../4-recette-tests/RECETTE.md`
- Git/issue :
