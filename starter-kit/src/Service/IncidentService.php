<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\SaisieIncident;
use App\Domain\SaisieInvalide;
use App\Repository\IncidentRepository;
use App\Repository\SalleRepository;

/**
 * Déclaration et consultation des incidents (API JSON ; formulaire du game master en S6).
 */
final class IncidentService
{
    public function __construct(
        private IncidentRepository $incidents,
        private SalleRepository $salles
    ) {
    }

    public function liste(): array
    {
        return $this->incidents->liste();
    }

    public function detail(int $idIncident): ?array
    {
        return $this->incidents->trouver($idIncident);
    }

    /**
     * Déclare un incident OUVERT et le renvoie tel qu'il est enregistré.
     * @throws SaisieInvalide une erreur par champ (HTTP 422)
     */
    public function declarer(array $saisie, int $idDeclarant): array
    {
        $idsSalles = array_map('intval', array_column($this->salles->all(), 'id_salle'));
        $resultat = SaisieIncident::valider($saisie, $idsSalles);
        $erreurs = $resultat['erreurs'];
        $donnees = $resultat['donnees'];

        // Salle et équipement bien formés : l'équipement doit être dans cette salle
        if (isset($donnees['id_salle'])
            && ($donnees['id_equipement'] ?? null) !== null
            && !$this->incidents->equipementDansSalle($donnees['id_equipement'], $donnees['id_salle'])) {
            $erreurs['id_equipement'] = 'Cet équipement n\'est pas affecté à cette salle.';
        }

        if ($erreurs !== []) {
            throw new SaisieInvalide($erreurs);
        }

        $id = $this->incidents->creer($donnees, $idDeclarant);

        return $this->incidents->trouver($id);
    }
}
