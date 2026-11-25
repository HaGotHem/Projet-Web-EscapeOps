<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Creneau;
use App\Domain\EtatSession;
use App\Domain\RegleMetierException;
use App\Domain\SaisieInvalide;
use App\Domain\SaisieSession;
use App\Repository\SalleRepository;
use App\Repository\SessionRepository;
use DateTimeImmutable;

/**
 * Gestion des sessions par le responsable (S5) : planning, création, états.
 */
final class SessionService
{
    /** Le planning montre aujourd'hui et les 6 jours suivants. */
    public const JOURS_PLANNING = 7;

    /** E5 : on ne choisit le game master que d'une session qui n'a pas commencé. */
    private const ETATS_AFFECTABLES = ['PREVUE', 'PRETE'];

    public function __construct(
        private SessionRepository $sessions,
        private SalleRepository $salles
    ) {
    }

    public function planning(): array
    {
        return $this->sessions->planning(self::JOURS_PLANNING);
    }

    public function sessionsDuGameMaster(int $idGameMaster): array
    {
        return $this->sessions->parGameMaster($idGameMaster, self::JOURS_PLANNING);
    }

    public function salles(): array
    {
        return $this->salles->all();
    }

    /**
     * Une session et les changements d'état proposés au responsable.
     * Chaque action indique si elle est possible maintenant et, sinon, pourquoi.
     */
    public function detail(int $idSession): ?array
    {
        $session = $this->sessions->trouver($idSession);
        if ($session === null) {
            return null;
        }

        $session['actions'] = [];
        foreach (EtatSession::transitionsDepuis($session['etat_session']) as $vers) {
            $raison = $this->raisonDeRefus($session, $vers);
            $session['actions'][] = [
                'vers' => $vers,
                'libelle' => EtatSession::action($vers),
                'possible' => $raison === null,
                'raison' => $raison,
            ];
        }

        // E5 : les game masters proposés, chacun avec sa session en conflit (ou null).
        // Seulement pour l'affichage : affecterGameMaster() revérifie au moment du POST.
        $session['gm_modifiable'] = in_array($session['etat_session'], self::ETATS_AFFECTABLES, true);
        $session['game_masters'] = [];
        if ($session['gm_modifiable']) {
            foreach ($this->sessions->gameMastersActifs() as $gm) {
                $id = (int) $gm['id_utilisateur'];
                $session['game_masters'][] = [
                    'id' => $id,
                    'nom' => $gm['prenom_utilisateur'] . ' ' . $gm['nom_utilisateur'],
                    'affecte' => (int) $session['id_game_master'] === $id,
                    'conflit' => $this->conflitGameMaster($session, $id),
                ];
            }
        }

        return $session;
    }

    /**
     * E5 : affecte un game master à une session PREVUE ou PRETE.
     * @throws RegleMetierException si la session, le game master ou le créneau ne le permet pas
     */
    public function affecterGameMaster(int $idSession, int $idGameMaster): string
    {
        $session = $this->sessions->trouver($idSession);
        if ($session === null) {
            throw new RegleMetierException('Session introuvable.');
        }

        if (!in_array($session['etat_session'], self::ETATS_AFFECTABLES, true)) {
            throw new RegleMetierException('On ne choisit le game master que d\'une session prévue ou prête.');
        }

        $gm = null;
        foreach ($this->sessions->gameMastersActifs() as $candidat) {
            if ((int) $candidat['id_utilisateur'] === $idGameMaster) {
                $gm = $candidat;
            }
        }
        if ($gm === null) {
            throw new RegleMetierException('Ce compte n\'est pas un game master actif.');
        }
        $nom = $gm['prenom_utilisateur'] . ' ' . $gm['nom_utilisateur'];

        if ((int) $session['id_game_master'] === $idGameMaster) {
            return $nom; // déjà affecté : rien à changer
        }

        // Règle E5 : un game master ne peut pas se dédoubler
        $conflit = $this->conflitGameMaster($session, $idGameMaster);
        if ($conflit !== null) {
            throw new RegleMetierException(sprintf(
                'Affectation refusée (règle E5) : %s anime déjà « %s » de %s à %s.',
                $nom,
                $conflit['nom_salle'],
                (new DateTimeImmutable($conflit['debut_session']))->format('G \h i'),
                (new DateTimeImmutable($conflit['fin_session']))->format('G \h i')
            ));
        }

        if (!$this->sessions->affecterGameMaster($idSession, $idGameMaster, $session['etat_session'])) {
            throw new RegleMetierException('La session a été modifiée entre-temps. Rechargez la page.');
        }

        return $nom;
    }

    /** La première session du game master qui chevauche celle-ci, ou null (E5). */
    private function conflitGameMaster(array $session, int $idGameMaster): ?array
    {
        $debut = new DateTimeImmutable($session['debut_session']);
        $fin = new DateTimeImmutable($session['fin_session']);

        $autres = $this->sessions->autresSessionsDuGameMaster(
            $idGameMaster,
            (int) $session['id_session'],
            $session['debut_session'],
            $session['fin_session']
        );

        foreach ($autres as $autre) {
            if (Creneau::chevauche($debut, $fin, new DateTimeImmutable($autre['debut_session']), new DateTimeImmutable($autre['fin_session']))) {
                return $autre;
            }
        }

        return null;
    }

    /**
     * Crée une session PREVUE. Renvoie son identifiant.
     * @throws SaisieInvalide si la saisie est incorrecte ou la salle déjà prise
     */
    public function creer(array $saisie): int
    {
        $salles = $this->salles->all();
        $resultat = SaisieSession::valider($saisie, array_map('intval', array_column($salles, 'id_salle')));
        if ($resultat['erreurs'] !== []) {
            throw new SaisieInvalide($resultat['erreurs']);
        }

        $idSalle = $resultat['donnees']['id_salle'];
        $salle = array_values(array_filter($salles, fn (array $s) => (int) $s['id_salle'] === $idSalle))[0];
        $debut = $resultat['donnees']['debut'];
        $fin = SaisieSession::fin($debut, (int) $salle['duree_jeu_min']);

        if ($debut <= new DateTimeImmutable($this->sessions->maintenant())) {
            throw new SaisieInvalide(['debut' => 'Le début doit être dans le futur.']);
        }

        $debutSql = $debut->format('Y-m-d H:i:s');
        $finSql = $fin->format('Y-m-d H:i:s');

        if ($this->sessions->salleOccupee($idSalle, $debutSql, $finSql, (int) ($salle['remise_etat_min'] ?? 0))) {
            throw new SaisieInvalide([
                'debut' => 'La salle est déjà occupée (ou en remise en état) sur ce créneau.',
            ]);
        }

        return $this->sessions->creer($idSalle, $debutSql, $finSql);
    }

    /**
     * Fait passer une session dans un nouvel état.
     * @throws RegleMetierException si la transition est refusée
     */
    public function changerEtat(int $idSession, string $vers): void
    {
        $session = $this->sessions->trouver($idSession);
        if ($session === null) {
            throw new RegleMetierException('Session introuvable.');
        }

        if (!EtatSession::peutPasser($session['etat_session'], $vers)) {
            throw new RegleMetierException(sprintf(
                'Une session « %s » ne peut pas passer à « %s ».',
                EtatSession::libelle($session['etat_session']),
                EtatSession::libelle($vers)
            ));
        }

        $raison = $this->raisonDeRefus($session, $vers);
        if ($raison !== null) {
            throw new RegleMetierException($raison);
        }

        if (!$this->sessions->changerEtat($idSession, $session['etat_session'], $vers)) {
            throw new RegleMetierException('La session a été modifiée entre-temps. Rechargez la page.');
        }
    }

    /** Conditions en plus du cycle de vie. null = rien ne s'y oppose. */
    private function raisonDeRefus(array $session, string $vers): ?string
    {
        // E5 : pas de démarrage sans game master (doublé par le CHECK ck_session_gm en base)
        if ($vers === 'EN_COURS' && $session['id_game_master'] === null) {
            return 'Démarrage impossible : aucun game master n\'est affecté.';
        }

        // Une session réservée ne s'annule pas directement : on annule d'abord la
        // réservation du groupe, pour qu'elle soit classée sans frais ou tardive (E7).
        if ($vers === 'ANNULEE' && $session['code_reservation'] !== null) {
            return 'Un groupe a réservé cette session : annulez d\'abord sa réservation.';
        }

        return null;
    }
}
