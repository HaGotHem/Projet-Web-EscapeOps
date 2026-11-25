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

    private const JOURS_LONGS = [
        1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi',
        5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche',
    ];

    private const MOIS_LONGS = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
        5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
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
     * Fiche salle publique : la salle et tous ses créneaux publiés, rangés par jour.
     * null si la salle n'existe pas (le contrôleur répond 404).
     */
    public function salle(int $idSalle): ?array
    {
        $salle = $this->repository->trouver($idSalle);
        if ($salle === null) {
            return null;
        }

        // Ranger les créneaux par jour : ['2026-11-25' => ['jour' => …, 'creneaux' => […]]]
        $jours = [];
        foreach ($this->repository->creneauxPublies($idSalle) as $ligne) {
            $debut = new DateTimeImmutable($ligne['debut_session']);
            $cle = $debut->format('Y-m-d');
            $jours[$cle]['jour'] ??= $this->jourLong($debut);
            $jours[$cle]['creneaux'][] = $this->creneau($ligne);
        }

        $salle['jours'] = array_values($jours);

        return $salle;
    }

    /**
     * Transforme une ligne SQL de session en créneau prêt à afficher.
     */
    private function creneau(array $ligne): array
    {
        $debut = new DateTimeImmutable($ligne['debut_session']);
        $fin = new DateTimeImmutable($ligne['fin_session']);

        return [
            'id' => (int) $ligne['id_session'],
            // Règle métier : un créneau sans réservation confirmée est libre.
            'libre' => (int) $ligne['nb_confirmees'] === 0,
            'jour' => $this->jourCourt($debut),
            'heure' => $this->heure($debut),
            'plage' => $this->heure($debut) . ' – ' . $this->heure($fin),
        ];
    }

    /** « 18 h 30 » */
    private function heure(DateTimeImmutable $date): string
    {
        return $date->format('G') . ' h ' . $date->format('i');
    }

    /**
     * « Mercredi 25 novembre »
     */
    private function jourLong(DateTimeImmutable $date): string
    {
        return self::JOURS_LONGS[(int) $date->format('N')] . ' '
            . $date->format('j') . ' '
            . self::MOIS_LONGS[(int) $date->format('n')];
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
