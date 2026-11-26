-- =====================================================================
-- EscapeOps · Les Archives Obscures
-- 003_procedures.sql : procédure stockée (tâche Q1)
--
-- À appliquer après 002_vues.sql, avec le client mariadb / mysql
-- (DELIMITER est une commande du client, pas du serveur).
-- Rejouable (DROP PROCEDURE IF EXISTS).
-- =====================================================================

SET NAMES utf8mb4;

DROP PROCEDURE IF EXISTS sp_bilan_salle;

DELIMITER //

-- ---------------------------------------------------------------------
-- sp_bilan_salle(id) : bilan d'exploitation d'une salle pour la règle E9.
--   - dernier_controle : date du dernier contrôle de maintenance (NULL si aucun) ;
--   - compteur : sessions TERMINEE finies strictement après ce contrôle
--     (toutes les terminées s'il n'y en a jamais eu). Les ANNULEE ne comptent pas.
-- Même règle que App\Domain\Maintenance::compter(). Le seuil (10) et le
-- badge CONTROLE_A_PREVOIR restent dans le PHP (Maintenance::controleAPrevoir).
-- Appelée par SalleRepository::bilan() ; une salle inconnue renvoie 0 ligne.
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_bilan_salle(IN p_id_salle INT)
    READS SQL DATA
BEGIN
    DECLARE v_dernier_controle DATETIME DEFAULT NULL;

    SELECT MAX(c.controle_le)
      INTO v_dernier_controle
      FROM controle_maintenance c
     WHERE c.id_salle = p_id_salle;

    SELECT sa.id_salle,
           v_dernier_controle AS dernier_controle,
           (SELECT COUNT(*)
              FROM session s
             WHERE s.id_salle = sa.id_salle
               AND s.etat_session = 'TERMINEE'
               AND (v_dernier_controle IS NULL OR s.fin_session > v_dernier_controle)
           ) AS compteur
      FROM salle sa
     WHERE sa.id_salle = p_id_salle;
END //

DELIMITER ;
