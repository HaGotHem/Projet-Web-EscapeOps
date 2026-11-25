<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\RegleMetierException;
use App\Domain\SaisieInvalide;
use App\Http\HttpInputException;
use App\Http\JsonInput;
use App\Http\JsonResponder;
use App\Repository\SalleRepository;
use App\Service\IncidentService;
use App\Service\SessionService;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * API JSON (contrat : docs/api.md).
 *
 * Lecture publique : salles et créneaux publiés, sans donnée personnelle.
 * Écriture authentifiée (ApiAuthMiddleware) : affectation d'un game master (E5)
 * et déclaration d'un incident. Les règles sont celles du site HTML : mêmes services.
 * Erreurs : { "erreur": "...", "champs": { ... } } avec 400, 401, 403, 404, 415 ou 422.
 */
final class ApiController
{
    public function __construct(
        private SalleRepository $salles,
        private SessionService $sessions,
        private IncidentService $incidents
    ) {
    }

    // --- Salles et créneaux (public) -----------------------------------------------

    /** GET /api/salles */
    public function salles(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return JsonResponder::respond($response, array_map($this->salle(...), $this->salles->all()));
    }

    /** GET /api/salles/{id} : la salle et ses créneaux publiés */
    public function detailSalle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $salle = $this->salles->trouver((int) $args['id']);
        if ($salle === null) {
            return $this->erreur($response, 404, 'Salle introuvable.');
        }

        return JsonResponder::respond($response, $this->salle($salle) + [
            'creneaux' => array_map($this->creneau(...), $this->salles->creneauxPublies((int) $salle['id_salle'])),
        ]);
    }

    /** GET /api/sessions[?salle=ID] : créneaux publiés (prévus ou prêts, pas encore commencés) */
    public function creneaux(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $filtre = $request->getQueryParams()['salle'] ?? null;
        if ($filtre !== null && (!is_string($filtre) || preg_match('/^[1-9][0-9]{0,8}$/', $filtre) !== 1)) {
            return $this->erreur($response, 422, 'Paramètre invalide.', ['salle' => 'Identifiant de salle entier attendu.']);
        }

        $lignes = $this->salles->creneauxPublies($filtre === null ? null : (int) $filtre);

        return JsonResponder::respond($response, array_map($this->creneau(...), $lignes));
    }

    /** GET /api/sessions/{id} : horaire et état d'une session (ni contact, ni game master) */
    public function detailSession(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $s = $this->sessions->trouver((int) $args['id']);
        if ($s === null) {
            return $this->erreur($response, 404, 'Session introuvable.');
        }

        return JsonResponder::respond($response, [
            'id' => (int) $s['id_session'],
            'salle' => ['id' => (int) $s['id_salle'], 'nom' => $s['nom_salle']],
            'debut' => $this->date($s['debut_session']),
            'fin' => $this->date($s['fin_session']),
            'etat' => $s['etat_session'],
            'libre' => $s['code_reservation'] === null,
        ]);
    }

    // --- Game master (responsable, règle E5) ----------------------------------------

    /** POST /api/sessions/{id}/game-master  { "id_game_master": 5 } */
    public function affecterGameMaster(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];

        try {
            $corps = JsonInput::readObject($request);
        } catch (HttpInputException $e) {
            return $this->erreur($response, $e->status, $e->getMessage());
        }

        if (!is_int($corps['id_game_master'] ?? null)) {
            return $this->erreur($response, 422, 'Requête invalide.', ['id_game_master' => 'Identifiant entier du game master attendu.']);
        }
        if ($this->sessions->trouver($id) === null) {
            return $this->erreur($response, 404, 'Session introuvable.');
        }

        try {
            $nom = $this->sessions->affecterGameMaster($id, $corps['id_game_master']);
        } catch (RegleMetierException $e) {
            // ex. E5 : « Affectation refusée (règle E5) : Nora Benali anime déjà … »
            return $this->erreur($response, 422, $e->getMessage());
        }

        return JsonResponder::respond($response, [
            'id_session' => $id,
            'game_master' => ['id' => $corps['id_game_master'], 'nom' => $nom],
        ]);
    }

    // --- Incidents (game master ou responsable) -------------------------------------

    /** GET /api/incidents */
    public function incidents(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return JsonResponder::respond($response, array_map($this->incident(...), $this->incidents->liste()));
    }

    /** GET /api/incidents/{id} */
    public function detailIncident(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $incident = $this->incidents->detail((int) $args['id']);
        if ($incident === null) {
            return $this->erreur($response, 404, 'Incident introuvable.');
        }

        return JsonResponder::respond($response, $this->incident($incident));
    }

    /** POST /api/incidents : 201 + Location */
    public function declarerIncident(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $corps = JsonInput::readObject($request);
            $incident = $this->incidents->declarer($corps, (int) $request->getAttribute('utilisateur')['id']);
        } catch (HttpInputException $e) {
            return $this->erreur($response, $e->status, $e->getMessage());
        } catch (SaisieInvalide $e) {
            return $this->erreur($response, 422, 'Incident refusé : corrigez les champs indiqués.', $e->erreurs);
        }

        return JsonResponder::respond($response, $this->incident($incident), 201)
            ->withHeader('Location', '/api/incidents/' . $incident['id_incident']);
    }

    // --- Représentations JSON -------------------------------------------------------

    private function salle(array $s): array
    {
        return [
            'id' => (int) $s['id_salle'],
            'nom' => $s['nom_salle'],
            'theme' => $s['theme_salle'],
            'description' => $s['description_salle'],
            'duree_minutes' => (int) $s['duree_jeu_min'],
            'joueurs_min' => (int) $s['capacite_min'],
            'joueurs_max' => (int) $s['capacite_max'],
        ];
    }

    private function creneau(array $c): array
    {
        return [
            'id' => (int) $c['id_session'],
            'id_salle' => (int) $c['id_salle'],
            'debut' => $this->date($c['debut_session']),
            'fin' => $this->date($c['fin_session']),
            'libre' => (int) $c['nb_confirmees'] === 0,
        ];
    }

    private function incident(array $i): array
    {
        return [
            'id' => (int) $i['id_incident'],
            'titre' => $i['titre_incident'],
            'description' => $i['description_incident'],
            'gravite' => $i['gravite_incident'],
            'statut' => $i['statut_incident'],
            'declare_le' => $this->date($i['declare_le']),
            'resolu_le' => $i['resolu_le'] === null ? null : $this->date($i['resolu_le']),
            'salle' => ['id' => (int) $i['id_salle'], 'nom' => $i['nom_salle']],
            'equipement' => $i['id_equipement'] === null ? null : ['id' => (int) $i['id_equipement'], 'nom' => $i['nom_equipement']],
            'declarant' => ['id' => (int) $i['id_declarant'], 'nom' => $i['prenom_utilisateur'] . ' ' . $i['nom_utilisateur']],
        ];
    }

    /** Date SQL (heure de Paris) → ISO 8601 avec décalage, ex. « 2026-11-25T18:00:00+01:00 ». */
    private function date(string $sql): string
    {
        return (new DateTimeImmutable($sql))->format(DATE_ATOM);
    }

    private function erreur(ResponseInterface $response, int $statut, string $message, array $champs = []): ResponseInterface
    {
        return JsonResponder::respond($response, ['erreur' => $message] + ($champs === [] ? [] : ['champs' => $champs]), $statut);
    }
}
