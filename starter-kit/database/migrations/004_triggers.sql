-- =====================================================================
-- EscapeOps · Les Archives Obscures
-- 004_triggers.sql : trigger d'historique des sessions (tâche Q1)
--
-- À appliquer après 003_procedures.sql, avec le client mariadb / mysql
-- (DELIMITER est une commande du client, pas du serveur).
-- Rejouable (DROP TRIGGER IF EXISTS).
-- =====================================================================

SET NAMES utf8mb4;

DROP TRIGGER IF EXISTS trg_session_historique;

DELIMITER //

-- ---------------------------------------------------------------------
-- trg_session_historique : chaque changement d'état d'une session
-- (PREVUE -> PRETE -> EN_COURS -> TERMINEE, ou ANNULEE) ajoute une ligne
-- dans historique_session. Une mise à jour qui ne change pas l'état
-- (affectation d'un game master, heure de fin) n'écrit rien.
-- Déclenché par SessionRepository::changerEtat().
-- ---------------------------------------------------------------------
CREATE TRIGGER trg_session_historique
AFTER UPDATE ON session
FOR EACH ROW
BEGIN
    IF NEW.etat_session <> OLD.etat_session THEN
        INSERT INTO historique_session (ancien_etat, nouvel_etat, change_le, id_session)
        VALUES (OLD.etat_session, NEW.etat_session, NOW(), NEW.id_session);
    END IF;
END //

DELIMITER ;
