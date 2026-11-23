# liv5 — Dossier de conception

**Type :** documentaire · **Responsable :** moi · **Validation :** enseignant référent

Il rassemble :
- le choix des règles ;
- les maquettes ;
- le modèle de données (MCD et MLD) ;
- le contrat d'API ;
- les fiches des règles métier.

## Critères de validation

| ✔ | Critère | Vérification | Résultat obtenu | Date | Preuve |
|---|---|---|---|---|---|
| ☐ | Le triplet E5 + E7 + E9 et les extensions sont réservés et validés | Validation de l'enseignant sur CHOIX_REGLES.md | | | |
| ☑ | Les maquettes couvrent les écrans des 3 règles | Relecture des maquettes | E5 : Session + SessionConflit · E7 : AnnulationSansFrais + AnnulationTardive · E9 : SalleResponsable | 23/11/2026 | [maquettes/](maquettes/) |

## Contenu

| Élément | Emplacement | Tâche | État |
|---|---|---|---|
| Choix des règles | [../../docs/CHOIX_REGLES.md](../../docs/CHOIX_REGLES.md) | C1 | ☑ rédigé · ☐ validé par l'enseignant |
| Maquettes des 8 écrans (13 artboards) | [maquettes/](maquettes/) | C2 | ☑ rédigé · ☐ validé par l'enseignant |
| MCD / MLD | [modelisation/](modelisation/) | C3 | ☑ rédigé · ☐ validé par l'enseignant |
| Contrat d'API | `docs/api.md` du dépôt | C4 | ☐ |
| Fiche E5 | [regles/FICHE_E5.md](regles/FICHE_E5.md) | R1 | ☐ |
| Fiche E7 | [regles/FICHE_E7.md](regles/FICHE_E7.md) | R2 | ☐ |
| Fiche E9 | [regles/FICHE_E9.md](regles/FICHE_E9.md) | R3 | ☐ |

## Maquettes : écrans à couvrir

Cochez chaque écran quand sa maquette est faite :

- ☑ vitrine publique (salles, créneaux publiés) : `Main`, `FicheSalle`
- ☑ connexion : `Connexion`
- ☑ tableau de bord du responsable : `Cockpit`, `CockpitTablette`
- ☑ planning / sessions (affectation du game master → **E5**) : `Session`, `SessionConflit`
- ☑ détail d'une session (passage `EN_COURS` → **E5**) : `Session`
- ☑ réservation d'un groupe et annulation (statut → **E7**) : `Reservation`, `AnnulationSansFrais`, `AnnulationTardive`
- ☑ liste des salles avec badge `CONTROLE_A_PREVOIR` et enregistrement d'un contrôle (**E9**) : `SalleResponsable`
- ☑ incidents : `Incident`
- en plus : `Etats` (catalogue des badges d'état)

**Pour ouvrir les maquettes :** elles ont besoin du dossier `_runtime/`. Lance un petit serveur local dans `maquettes/` (`python -m http.server`), puis ouvre un fichier `.dc.html` dans le navigateur. Les ouvrir directement en double-cliquant peut ne pas marcher.
