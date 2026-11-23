# Livrables — EscapeOps · Les Archives Obscures (E5 + E7 + E9)

Un dossier par livrable du projet. Les numéros sont ceux de l'outil de suivi Jalon (liv1 à liv6).
Chaque dossier contient un `README.md` avec :

- les **critères de validation**, à cocher quand ils sont vérifiés ;
- les **preuves attendues**, tâche par tâche.

Release visée : **v1.0.0 le vendredi 27 novembre 2026**.

| # | Livrable | Type | Tâches | Dossier | Validé |
|---|---|---|---|---|---|
| liv1 | Application EscapeOps | Technique | S3 S4 S5 S6 R1 R2 R3 V4 | [1-application](1-application/) | ☐ |
| liv2 | Base de données et objets SQL avancés | Technique | S2 Q1 | [2-base-de-donnees](2-base-de-donnees/) | ☐ |
| liv3 | API REST JSON | Technique | A1 | [3-api](3-api/) | ☐ |
| liv4 | Cahier de recette et tests | Validation | Q2 V1 V2 | [4-recette-tests](4-recette-tests/) | ☐ |
| liv5 | Dossier de conception | Documentaire | C1 C2 C3 C4 | [5-conception](5-conception/) | ☐ |
| liv6 | README, analyse d'évolution, rapport d'incident | Documentaire | A2 Q3 V3 | [6-documentation](6-documentation/) | ☐ |

## Règles de rangement

- **Captures** : nommer `AAAA-MM-JJ_TACHE_description.png`, par exemple `2026-11-25_R1_refus-gm-18h.png`.
  On retrouve ainsi la tâche Jalon et la date du constat.
- **Une preuve = un constat daté** : ce qui a été testé, le résultat attendu et le résultat obtenu.
  Une capture seule ne suffit pas.
- **Le code reste dans le code** : `database/`, `src/`, `tests/`.
  Ici, on met la preuve et un lien vers le fichier ou le commit.
- **Aucun secret** : pas de mot de passe réel ni de fichier `.env`. Les comptes de démonstration sont fictifs.

## Rituel du soir (5 minutes)

1. Cocher les critères vérifiés dans le `README.md` du livrable concerné.
2. Déposer les captures du jour.
3. Dans Jalon :
   - mettre à jour le statut et la charge réelle de chaque tâche ;
   - renseigner le champ **preuve** (chemin du fichier) ;
   - cocher le jalon du jour s'il est atteint.
4. Si un critère est *Non conforme* : noter la cause et l'action prévue dans Jalon, pas seulement dans ce dossier.

## Correspondance avec les preuves BTS ([docs/10-preuves-bts.md](../docs/10-preuves-bts.md))

| Catégorie BTS | Où la trouver |
|---|---|
| 01 Besoin | [../docs/CHOIX_REGLES.md](../docs/CHOIX_REGLES.md), 5-conception |
| 02 Maquettes | 5-conception/maquettes |
| 03 Modélisation | 5-conception/modelisation, 2-base-de-donnees |
| 04 Règles métier | 5-conception/regles, 1-application |
| 05 Tests | 4-recette-tests |
| 06 API | 3-api |
| 07 Sécurité | 4-recette-tests (section sécurité), 1-application (droits) |
| 08 Maintenance | 6-documentation (évolution, incident) |
| 09 Déploiement | 6-documentation (README, installation) |

> Quand le dépôt du projet sera créé (tâche S1), déplace ce dossier dans son `docs/`.
> Il sera alors versionné avec le code. Pense à mettre à jour les liens vers `../docs/`.
