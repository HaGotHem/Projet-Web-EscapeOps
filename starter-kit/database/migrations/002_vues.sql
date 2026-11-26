-- =====================================================================
-- EscapeOps · Les Archives Obscures
-- 002_vues.sql : vues SQL consommées par l'application (tâche Q1)
--
-- À appliquer après 001_schema.sql. Rejouable (DROP VIEW IF EXISTS).
-- =====================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- v_planning : une ligne par session, avec sa salle, son game master
-- et la réservation confirmée du groupe (s'il y en a une).
-- Lue par SessionRepository : planning du responsable, fiche session,
-- sessions d'un game master.
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_planning;

CREATE VIEW v_planning AS
SELECT s.id_session,
       s.debut_session,
       s.fin_session,
       s.etat_session,
       s.id_salle,
       sa.nom_salle,
       sa.duree_jeu_min,
       sa.remise_etat_min,
       sa.capacite_min,
       sa.capacite_max,
       s.id_game_master,
       gm.prenom_utilisateur AS gm_prenom,
       gm.nom_utilisateur    AS gm_nom,
       r.code_reservation,
       r.nom_groupe,
       r.nb_joueurs,
       r.contact_nom,
       r.contact_email,
       r.contact_tel,
       r.reservee_le
  FROM session s
  JOIN salle sa            ON sa.id_salle = s.id_salle
  LEFT JOIN utilisateur gm ON gm.id_utilisateur = s.id_game_master
  LEFT JOIN reservation r  ON r.id_session = s.id_session
                          AND r.statut_reservation = 'CONFIRMEE';

-- ---------------------------------------------------------------------
-- v_tableau_bord : une ligne par salle, la synthèse du jour pour le
-- cockpit du responsable (maquette 05, tâche S6).
--   - sessions du jour (hors annulées), annulées du jour,
--     sessions du jour non annulées encore sans game master (E5) ;
--   - prochaine session à venir (hors annulées) ;
--   - incidents ouverts, dont bloquants, dont sur un équipement critique.
-- Lue par SalleRepository::tableauDeBord().
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_tableau_bord;

CREATE VIEW v_tableau_bord AS
SELECT sa.id_salle,
       sa.nom_salle,
       (SELECT COUNT(*)
          FROM session s
         WHERE s.id_salle = sa.id_salle
           AND s.etat_session <> 'ANNULEE'
           AND s.debut_session >= CURDATE()
           AND s.debut_session <  CURDATE() + INTERVAL 1 DAY)      AS sessions_jour,
       (SELECT COUNT(*)
          FROM session s
         WHERE s.id_salle = sa.id_salle
           AND s.etat_session = 'ANNULEE'
           AND s.debut_session >= CURDATE()
           AND s.debut_session <  CURDATE() + INTERVAL 1 DAY)      AS sessions_annulees_jour,
       (SELECT COUNT(*)
          FROM session s
         WHERE s.id_salle = sa.id_salle
           AND s.etat_session IN ('PREVUE', 'PRETE')
           AND s.id_game_master IS NULL
           AND s.debut_session >= CURDATE()
           AND s.debut_session <  CURDATE() + INTERVAL 1 DAY)      AS sessions_sans_gm_jour,
       (SELECT MIN(s.debut_session)
          FROM session s
         WHERE s.id_salle = sa.id_salle
           AND s.etat_session IN ('PREVUE', 'PRETE')
           AND s.debut_session > NOW())                            AS prochaine_session,
       (SELECT COUNT(*)
          FROM incident i
         WHERE i.id_salle = sa.id_salle
           AND i.statut_incident = 'OUVERT')                       AS incidents_ouverts,
       (SELECT COUNT(*)
          FROM incident i
         WHERE i.id_salle = sa.id_salle
           AND i.statut_incident = 'OUVERT'
           AND i.gravite_incident = 'BLOQUANT')                    AS incidents_bloquants,
       (SELECT COUNT(*)
          FROM incident i
          JOIN equipement e ON e.id_equipement = i.id_equipement
         WHERE i.id_salle = sa.id_salle
           AND i.statut_incident = 'OUVERT'
           AND e.critique = TRUE)                                  AS incidents_critiques
  FROM salle sa;
