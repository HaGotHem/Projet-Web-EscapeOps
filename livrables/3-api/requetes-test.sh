#!/usr/bin/env bash
# Requêtes de test de l'API JSON (tâche A1, contrat docs/api.md).
# Prérequis : serveur lancé (composer serve) et base chargée avec le seed.
# Usage : bash livrables/3-api/requetes-test.sh > livrables/3-api/captures/AAAA-MM-JJ_A1_journal-requetes.txt
#
# Le cas E5 a besoin d'une session ($SESSION_CONFLIT) qui chevauche une session déjà animée
# par le game master $GM_CONFLIT. Valeurs par défaut : données du 25/11 (session 31, Cabinet
# 18 h 30, contre Nora sur la Crypte 18 h). Avec un seed rechargé le jour même :
#   SESSION_CONFLIT=26 GM_CONFLIT=3 bash livrables/3-api/requetes-test.sh   (Léa déjà sur la 25 à 18 h)
set -u

BASE="${BASE:-http://127.0.0.1:8080}"
SESSION_CONFLIT="${SESSION_CONFLIT:-31}"
GM_CONFLIT="${GM_CONFLIT:-5}"
RESPONSABLE="camille.roux@archives-obscures.example:demo-escapeops-2026"   # compte fictif du seed
GAME_MASTER="lea.martin@archives-obscures.example:demo-escapeops-2026"     # compte fictif du seed

n=0
requete() {
    local titre="$1"; shift
    n=$((n + 1))
    echo "=================================================================="
    echo "#$n  $titre"
    echo "\$ curl $*" | sed -E 's/(-u [^:]+:)[^ ]+/\1********/'
    echo "------------------------------------------------------------------"
    curl -s -i "$@" | tr -d '\r' | grep -v -E '^(Host|Date|Connection|X-Powered-By):'
    echo
    echo
}

echo "Journal des requêtes de test — API EscapeOps — $(date '+%d/%m/%Y %H:%M')"
echo

requete "GET collection : 200 + tableau" "$BASE/api/salles"
requete "GET détail inconnu : 404" "$BASE/api/salles/99"
requete "GET collection filtrée : 200" "$BASE/api/sessions?salle=2"
requete "GET filtre invalide : 422" "$BASE/api/sessions?salle=abc"
requete "GET détail session : 200" "$BASE/api/sessions/$SESSION_CONFLIT"
requete "POST valide (game master) : 201 + Location" \
    -u "$GAME_MASTER" -H "Content-Type: application/json" \
    -d '{"id_salle":2,"titre":"L'"'"'orgue ne joue plus la s\u00e9quence finale","gravite":"BLOQUANT","id_equipement":3}' \
    "$BASE/api/incidents"
requete "POST JSON malformé : 400" \
    -u "$GAME_MASTER" -H "Content-Type: application/json" -d '{"id_salle":2,' "$BASE/api/incidents"
requete "POST mauvais Content-Type : 415" \
    -u "$GAME_MASTER" -H "Content-Type: text/plain" -d 'orgue en panne' "$BASE/api/incidents"
requete "POST champs invalides : 422 avec une erreur par champ" \
    -u "$GAME_MASTER" -H "Content-Type: application/json" \
    -d '{"id_salle":1,"titre":"ab","gravite":"CRITIQUE","id_equipement":3}' "$BASE/api/incidents"
requete "POST sans authentification : 401" \
    -H "Content-Type: application/json" -d '{"id_salle":2,"titre":"Test","gravite":"MINEUR"}' "$BASE/api/incidents"
requete "POST affectation par un game master : 403" \
    -u "$GAME_MASTER" -H "Content-Type: application/json" -d "{\"id_game_master\":$GM_CONFLIT}" \
    "$BASE/api/sessions/$SESSION_CONFLIT/game-master"
requete "POST affectation en conflit (règle E5) : 422" \
    -u "$RESPONSABLE" -H "Content-Type: application/json" -d "{\"id_game_master\":$GM_CONFLIT}" \
    "$BASE/api/sessions/$SESSION_CONFLIT/game-master"
requete "POST affectation, session inconnue : 404" \
    -u "$RESPONSABLE" -H "Content-Type: application/json" -d '{"id_game_master":4}' \
    "$BASE/api/sessions/9999/game-master"
