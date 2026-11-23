# liv3 — API REST JSON

**Type :** technique · **Responsable :** moi · **Validation :** enseignant référent

Liste, détail et création d'une réservation ou d'un incident, conformes au contrat d'API (`docs/api.md` du dépôt du projet, tâche C4).

## Critères de validation

| ✔ | Critère | Vérification | Résultat obtenu | Date | Preuve |
|---|---|---|---|---|---|
| ☐ | GET collection et détail : 200, identifiant inconnu : 404 | Requêtes HTTP de test | | | |
| ☐ | POST valide : 201 ; corps malformé : 400 ; mauvais Content-Type : 415 | Requêtes HTTP de test | | | |
| ☐ | Une règle violée en API renvoie 422 avec un message clair | POST d'une affectation de GM en conflit (E5) | | | |

## Journal des requêtes de test

| # | Méthode | Route | Corps / en-têtes | Statut attendu | Statut obtenu | Capture |
|---|---|---|---|---|---|---|
| 1 | GET | | | 200 | | |
| 2 | GET | (id inconnu) | | 404 | | |
| 3 | POST | | corps valide | 201 | | |
| 4 | POST | | JSON malformé | 400 | | |
| 5 | POST | | `Content-Type: text/plain` | 415 | | |
| 6 | POST | | affectation GM en conflit | 422 | | |

## Preuves attendues

| Tâche | Preuve |
|---|---|
| C4 Contrat d'API | `docs/api.md` (voir aussi 5-conception) |
| A1 API JSON | Le tableau ci-dessus rempli + captures des réponses dans `captures/` |
