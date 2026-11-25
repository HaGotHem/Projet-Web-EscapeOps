# liv3 — API REST JSON

**Type :** technique · **Responsable :** moi · **Validation :** enseignant référent

Liste, détail et création d'une réservation ou d'un incident, conformes au contrat d'API (`docs/api.md` du dépôt du projet, tâche C4).

## Critères de validation

| ✔ | Critère | Vérification | Résultat obtenu | Date | Preuve |
|---|---|---|---|---|---|
| ☑ | GET collection et détail : 200, identifiant inconnu : 404 | Requêtes HTTP de test | `GET /api/salles` → 200 + tableau ; `GET /api/sessions/31` → 200 ; `GET /api/salles/99` → 404 `{"erreur":"Salle introuvable."}` | 25/11/2026 | `captures/2026-11-25_A1_journal-requetes.txt` (n° 1, 2, 3, 5) |
| ☑ | POST valide : 201 ; corps malformé : 400 ; mauvais Content-Type : 415 | Requêtes HTTP de test | `POST /api/incidents` → 201 + `Location: /api/incidents/6` ; JSON tronqué → 400 ; `text/plain` → 415 ; champs invalides → 422 avec une erreur par champ ; sans authentification → 401 ; mauvais rôle → 403 | 25/11/2026 | `captures/2026-11-25_A1_journal-requetes.txt` (n° 6 à 11) |
| ☑ | Une règle violée en API renvoie 422 avec un message clair | POST d'une affectation de GM en conflit (E5) | 422 `{"erreur":"Affectation refusée (règle E5) : Nora Benali anime déjà « La Crypte de l'Archiviste » de 18 h 00 à 19 h 00."}` | 25/11/2026 | `captures/2026-11-25_A1_journal-requetes.txt` (n° 12) |

## Journal des requêtes de test

Rejouable avec `bash livrables/3-api/requetes-test.sh` (serveur lancé, base chargée). Sortie complète (en-têtes, statut, corps ; mots de passe masqués) : `captures/2026-11-25_A1_journal-requetes.txt`.

| # | Méthode | Route | Corps / en-têtes | Statut attendu | Statut obtenu | Capture |
|---|---|---|---|---|---|---|
| 1 | GET | `/api/salles` | — | 200 | 200 | journal n° 1 |
| 2 | GET | `/api/salles/99` (id inconnu) | — | 404 | 404 | journal n° 2 |
| 3 | GET | `/api/sessions?salle=2` | — | 200 | 200 | journal n° 3 |
| 4 | GET | `/api/sessions?salle=abc` | filtre non entier | 422 | 422 | journal n° 4 |
| 5 | GET | `/api/sessions/31` | — | 200 | 200 | journal n° 5 |
| 6 | POST | `/api/incidents` | corps valide, Basic game master | 201 + `Location` | 201 + `Location: /api/incidents/6` | journal n° 6 |
| 7 | POST | `/api/incidents` | JSON malformé | 400 | 400 | journal n° 7 |
| 8 | POST | `/api/incidents` | `Content-Type: text/plain` | 415 | 415 | journal n° 8 |
| 9 | POST | `/api/incidents` | titre trop court, gravité inconnue, équipement d'une autre salle | 422 | 422 (3 champs) | journal n° 9 |
| 10 | POST | `/api/incidents` | sans `Authorization` | 401 | 401 | journal n° 10 |
| 11 | POST | `/api/sessions/31/game-master` | Basic game master (rôle insuffisant) | 403 | 403 | journal n° 11 |
| 12 | POST | `/api/sessions/31/game-master` | affectation GM en conflit (E5) | 422 | 422 | journal n° 12 |
| 13 | POST | `/api/sessions/9999/game-master` | session inconnue | 404 | 404 | journal n° 13 |

## Preuves attendues

| Tâche | Preuve |
|---|---|
| C4 Contrat d'API | `docs/api.md` (voir aussi 5-conception) |
| A1 API JSON | Le tableau ci-dessus rempli + captures des réponses dans `captures/` |
