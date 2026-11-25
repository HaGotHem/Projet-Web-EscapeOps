# Contrat de l'API JSON — EscapeOps

Tâche C4. Implémentation : `starter-kit/routes/api.php` et `starter-kit/src/Controller/ApiController.php`.
Requêtes de test rejouables : `livrables/3-api/requetes-test.sh` (journal dans `livrables/3-api/captures/`).

## Principes

- Base : `http://127.0.0.1:8080/api` en développement.
- Corps et réponses en JSON UTF-8 (`Content-Type: application/json; charset=utf-8`), `Cache-Control: no-store`.
- Dates en ISO 8601 avec le décalage de Paris, par exemple `2026-11-25T18:00:00+01:00`.
- **Lecture publique** : uniquement ce que montre déjà la vitrine (salles, créneaux, état). Pas de nom de client, pas de contact, pas de game master.
- **Écriture et données internes** : authentification HTTP Basic à chaque requête (`Authorization: Basic base64(email:mot_de_passe)`) avec un compte actif. L'API ne lit pas la session PHP du site : elle est sans état côté serveur. Les identifiants passent en clair dans l'en-tête, donc **HTTPS obligatoire en production**.
- Les règles sont celles du site HTML : l'API appelle les mêmes services (`SessionService`, `IncidentService`). Elle ne contourne ni la validation ni les droits.
- Niveau visé : Richardson 2 (ressources, verbes et statuts). Pas d'hypermédia.

## Format des erreurs

```json
{ "erreur": "Message lisible.", "champs": { "titre": "Le titre est un texte de 3 à 150 caractères." } }
```

`champs` n'est présent que pour une erreur de saisie (422).

| Statut | Quand |
|---|---|
| 400 | JSON mal formé, ou corps qui n'est pas un objet JSON |
| 401 | Pas d'en-tête `Authorization`, identifiants faux ou compte désactivé (en-tête `WWW-Authenticate: Basic`) |
| 403 | Compte authentifié, mais rôle non autorisé |
| 404 | Ressource inconnue |
| 415 | `Content-Type` différent de `application/json` sur un POST |
| 422 | Entrée compréhensible mais invalide, ou règle métier refusée (ex. E5) |

## Routes

| Méthode | URI | Auth | Corps | Succès | Erreurs |
|---|---|---|---|---|---|
| GET | `/api/health` | — | — | 200 | — |
| GET | `/api/salles` | — | — | 200 + tableau | — |
| GET | `/api/salles/{id}` | — | — | 200 + objet (avec `creneaux`) | 404 |
| GET | `/api/sessions?salle={id}` | — | — | 200 + tableau (même vide) | 422 (`salle` non entier) |
| GET | `/api/sessions/{id}` | — | — | 200 + objet | 404 |
| POST | `/api/sessions/{id}/game-master` | RESPONSABLE | `{ "id_game_master": 5 }` | 200 + objet | 400, 401, 403, 404, 415, 422 |
| GET | `/api/incidents` | RESPONSABLE ou GAME_MASTER | — | 200 + tableau | 401, 403 |
| GET | `/api/incidents/{id}` | RESPONSABLE ou GAME_MASTER | — | 200 + objet | 401, 403, 404 |
| POST | `/api/incidents` | RESPONSABLE ou GAME_MASTER | voir ci-dessous | 201 + `Location` + objet | 400, 401, 403, 415, 422 |

### GET /api/salles

```json
[
  {
    "id": 1,
    "nom": "Le Cabinet des Ombres",
    "theme": "Cabinet de curiosités",
    "description": "Le cabinet de curiosités d'un collectionneur disparu. …",
    "duree_minutes": 60,
    "joueurs_min": 2,
    "joueurs_max": 6
  }
]
```

### GET /api/salles/{id}

La salle (mêmes champs) et ses créneaux publiés, c'est-à-dire ses sessions prévues ou prêtes qui n'ont pas commencé :

```json
{
  "id": 1, "nom": "Le Cabinet des Ombres", "…": "…",
  "creneaux": [
    { "id": 31, "id_salle": 1, "debut": "2026-11-25T18:30:00+01:00", "fin": "2026-11-25T19:30:00+01:00", "libre": true }
  ]
}
```

`404 { "erreur": "Salle introuvable." }` si la salle n'existe pas.

### GET /api/sessions?salle={id}

Les créneaux publiés, de toutes les salles ou d'une seule, triés par salle puis par heure. Mêmes objets que `creneaux` ci-dessus. Une salle inconnue donne un tableau vide. `salle=abc` donne `422`.

### GET /api/sessions/{id}

```json
{
  "id": 31,
  "salle": { "id": 1, "nom": "Le Cabinet des Ombres" },
  "debut": "2026-11-25T18:30:00+01:00",
  "fin": "2026-11-25T19:30:00+01:00",
  "etat": "PREVUE",
  "libre": true
}
```

`etat` vaut `PREVUE`, `PRETE`, `EN_COURS`, `TERMINEE` ou `ANNULEE`. `libre` vaut `false` quand un groupe a une réservation confirmée.

### POST /api/sessions/{id}/game-master — règle E5

Réservé au responsable. Affecte un game master à une session prévue ou prête.

```http
POST /api/sessions/31/game-master
Authorization: Basic …
Content-Type: application/json

{ "id_game_master": 4 }
```

- `200` : `{ "id_session": 31, "game_master": { "id": 4, "nom": "Hugo Bernard" } }`. Réaffecter le même game master ne change rien et renvoie aussi 200.
- `422` si `id_game_master` n'est pas un entier : `{ "erreur": "Requête invalide.", "champs": { "id_game_master": "…" } }`.
- `404` si la session n'existe pas.
- `422` si une règle refuse, avec le message du site :
  - **E5, chevauchement** : `{ "erreur": "Affectation refusée (règle E5) : Nora Benali anime déjà « La Crypte de l'Archiviste » de 18 h 00 à 19 h 00." }`. Deux créneaux jointifs (fin de l'un = début de l'autre) sont acceptés.
  - session déjà commencée, terminée ou annulée ;
  - compte qui n'est pas un game master actif.

### POST /api/incidents

Un game master ou le responsable déclare un incident sur une salle. L'incident est créé `OUVERT`, à l'heure du serveur, au nom du compte authentifié.

| Champ | Type | Obligatoire | Contrôle |
|---|---|---|---|
| `id_salle` | entier | oui | salle existante |
| `titre` | texte | oui | 3 à 150 caractères (espaces de bord retirés) |
| `gravite` | texte | oui | `MINEUR` ou `BLOQUANT` |
| `description` | texte ou `null` | non | 2 000 caractères au plus |
| `id_equipement` | entier ou `null` | non | équipement affecté aujourd'hui à cette salle |

```http
POST /api/incidents
Authorization: Basic …
Content-Type: application/json

{ "id_salle": 2, "titre": "L'orgue ne joue plus la séquence finale", "gravite": "BLOQUANT", "id_equipement": 3 }
```

`201 Created`, avec `Location: /api/incidents/5` :

```json
{
  "id": 5,
  "titre": "L'orgue ne joue plus la séquence finale",
  "description": null,
  "gravite": "BLOQUANT",
  "statut": "OUVERT",
  "declare_le": "2026-11-25T15:10:00+01:00",
  "resolu_le": null,
  "salle": { "id": 2, "nom": "La Crypte de l'Archiviste" },
  "equipement": { "id": 3, "nom": "Orgue à pédales électronique" },
  "declarant": { "id": 3, "nom": "Léa Martin" }
}
```

`422` avec une erreur par champ fautif, par exemple :

```json
{
  "erreur": "Incident refusé : corrigez les champs indiqués.",
  "champs": {
    "titre": "Le titre est un texte de 3 à 150 caractères.",
    "gravite": "La gravité vaut MINEUR ou BLOQUANT.",
    "id_equipement": "Cet équipement n'est pas affecté à cette salle."
  }
}
```

### GET /api/incidents et /api/incidents/{id}

Les incidents (mêmes objets que ci-dessus) : les ouverts d'abord, puis du plus récent au plus ancien. `404 { "erreur": "Incident introuvable." }` pour un identifiant inconnu.

## Exemples curl

Les comptes sont ceux, fictifs, du seed (`starter-kit/database/seed.sql`).

```bash
curl -i http://127.0.0.1:8080/api/salles
curl -i "http://127.0.0.1:8080/api/sessions?salle=2"
curl -i -u camille.roux@archives-obscures.example:demo-escapeops-2026 \
     -H "Content-Type: application/json" -d '{"id_game_master":5}' \
     http://127.0.0.1:8080/api/sessions/31/game-master
```
