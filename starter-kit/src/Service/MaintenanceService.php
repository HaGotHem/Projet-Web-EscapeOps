<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Maintenance;
use App\Domain\RegleMetierException;
use App\Domain\SaisieInvalide;
use App\Repository\SalleRepository;
use DateTimeImmutable;

/**
 * Salles et maintenance préventive côté responsable (règle E9).
 */
final class MaintenanceService
{
    private const COMMENTAIRE_MAX = 1000;

    public function __construct(private SalleRepository $salles)
    {
    }

    /** Toutes les salles, chacune avec son compteur E9. */
    public function salles(): array
    {
        return array_map(fn (array $salle): array => $this->bilan($salle), $this->salles->all());
    }

    /** Une salle avec son compteur, les sessions comptées et l'historique des contrôles. */
    public function fiche(int $idSalle): ?array
    {
        $salle = $this->salles->trouver($idSalle);

        return $salle === null ? null : $this->bilan($salle);
    }

    /**
     * Enregistre un contrôle fait par le responsable connecté : le compteur repart à 0.
     * @throws RegleMetierException salle inconnue
     * @throws SaisieInvalide       commentaire trop long
     */
    public function enregistrerControle(int $idSalle, int $idResponsable, array $saisie): void
    {
        if ($this->salles->trouver($idSalle) === null) {
            throw new RegleMetierException('Salle introuvable.');
        }

        $commentaire = trim(is_string($saisie['commentaire'] ?? null) ? $saisie['commentaire'] : '');
        if (mb_strlen($commentaire) > self::COMMENTAIRE_MAX) {
            throw new SaisieInvalide(['commentaire' => 'Le commentaire est limité à ' . self::COMMENTAIRE_MAX . ' caractères.']);
        }

        $this->salles->enregistrerControle($idSalle, $idResponsable, $commentaire === '' ? null : $commentaire);
    }

    private function bilan(array $salle): array
    {
        $idSalle = (int) $salle['id_salle'];
        $controles = $this->salles->controles($idSalle);
        $dernier = $controles[0] ?? null;
        $depuis = $dernier['controle_le'] ?? null;

        $sessions = $this->salles->sessionsFinies($idSalle, $depuis);
        $compteur = Maintenance::compter($sessions, $depuis === null ? null : new DateTimeImmutable($depuis));

        return $salle + [
            'compteur' => $compteur,
            'seuil' => Maintenance::SEUIL,
            'controle_a_prevoir' => Maintenance::controleAPrevoir($compteur),
            'dernier_controle' => $dernier,
            'controles' => $controles,
            'sessions_depuis_controle' => $sessions,
        ];
    }
}
