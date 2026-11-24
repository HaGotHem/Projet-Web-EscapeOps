<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\SalleRepository;
use DateTimeImmutable;

/**
 * Prépare les données de la vitrine publique.
 * Le repository donne les lignes brutes ; ici on applique la règle
 * « libre / réservé » et on met les dates au format français.
 */
final class VitrineService
{
    /** Nombre de créneaux montrés par salle sur la page d'accueil (maquette Main). */
    private const CRENEAUX_PAR_SALLE = 12;

    private const JOURS = [
        1 => 'Lun.', 2 => 'Mar.', 3 => 'Mer.', 4 => 'Jeu.',
        5 => 'Ven.', 6 => 'Sam.', 7 => 'Dim.',
    ];

    private const MOIS = [
        1 => 'janv.', 2 => 'févr.', 3 => 'mars', 4 => 'avr.',
        5 => 'mai', 6 => 'juin', 7 => 'juil.', 8 => 'août',
        9 => 'sept.', 10 => 'oct.', 11 => 'nov.', 12 => 'déc.',
    ];

    public function __construct(private SalleRepository $repository)
    {
    }

    /**
     * Toutes les salles, chacune avec ses prochains créneaux publiés.
     */
    public function salles(): array
    {
        // a. Ranger les créneaux par salle : [id_salle => [créneau, créneau, ...]]
        $parSalle = [];
        foreach ($this->repository->creneauxPublies() as $ligne) {
            $parSalle[(int) $ligne['id_salle']][] = $this->creneau($ligne);
        }

        // b. Ajouter à chaque salle ses 4 premiers créneaux (déjà triés par heure)
        $salles = [];
        foreach ($this->repository->all() as $salle) {
            $id = (int) $salle['id_salle'];
            $salle['creneaux'] = array_slice($parSalle[$id] ?? [], 0, self::CRENEAUX_PAR_SALLE);
            $salles[] = $salle;
        }

        return $salles;
    }

    /**
     * Transforme une ligne SQL de session en créneau prêt à afficher.
     */
    private function creneau(array $ligne): array
    {
        $debut = new DateTimeImmutable($ligne['debut_session']);

        return [
            'id' => (int) $ligne['id_session'],
            // Règle métier : un créneau sans réservation confirmée est libre.
            'libre' => (int) $ligne['nb_confirmees'] === 0,
            'jour' => $this->jourCourt($debut),
            'heure' => $debut->format('G') . ' h ' . $debut->format('i'),
        ];
    }

    /**
     * « Lun. 23 nov. » (sans l'extension intl, absente de ce PHP).
     */
    private function jourCourt(DateTimeImmutable $date): string
    {
        return self::JOURS[(int) $date->format('N')] . ' '
            . $date->format('j') . ' '
            . self::MOIS[(int) $date->format('n')];
    }
}
