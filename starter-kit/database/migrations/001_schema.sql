-- =====================================================================
-- EscapeOps · Les Archives Obscures
-- 001_schema.sql : schéma relationnel issu du MLD
-- (livrables/5-conception/modelisation/MLD.md)
--
-- Cible : MariaDB >= 10.2.1 ou MySQL >= 8.0.16 (CHECK appliqués).
-- Rejouable : les tables sont supprimées puis recréées.
-- Les vues, la procédure et le trigger viendront dans 002 à 004 (tâche Q1).
-- =====================================================================

SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS historique_session;
DROP TABLE IF EXISTS reservation;
DROP TABLE IF EXISTS controle_maintenance;
DROP TABLE IF EXISTS incident;
DROP TABLE IF EXISTS affecter;
DROP TABLE IF EXISTS session;
DROP TABLE IF EXISTS equipement;
DROP TABLE IF EXISTS salle;
DROP TABLE IF EXISTS utilisateur;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- UTILISATEUR : administrateur, responsable d'exploitation, game master
-- ---------------------------------------------------------------------
CREATE TABLE utilisateur (
    id_utilisateur      INT          NOT NULL AUTO_INCREMENT,
    nom_utilisateur     VARCHAR(80)  NOT NULL,
    prenom_utilisateur  VARCHAR(80)  NOT NULL,
    email_utilisateur   VARCHAR(180) NOT NULL,
    mdp_hash            VARCHAR(255) NOT NULL,
    role_utilisateur    ENUM('ADMIN', 'RESPONSABLE', 'GAME_MASTER') NOT NULL,
    actif_utilisateur   BOOLEAN      NOT NULL DEFAULT TRUE,
    CONSTRAINT pk_utilisateur PRIMARY KEY (id_utilisateur),
    CONSTRAINT uq_utilisateur_email UNIQUE (email_utilisateur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- SALLE
-- ---------------------------------------------------------------------
CREATE TABLE salle (
    id_salle            INT          NOT NULL AUTO_INCREMENT,
    nom_salle           VARCHAR(100) NOT NULL,
    theme_salle         VARCHAR(100) NOT NULL,
    description_salle   TEXT         NULL,
    duree_jeu_min       INT          NOT NULL,
    capacite_min        INT          NOT NULL,
    capacite_max        INT          NOT NULL,
    remise_etat_min     INT          NOT NULL DEFAULT 0,
    CONSTRAINT pk_salle PRIMARY KEY (id_salle),
    CONSTRAINT uq_salle_nom UNIQUE (nom_salle),
    CONSTRAINT ck_salle_duree     CHECK (duree_jeu_min > 0),
    CONSTRAINT ck_salle_cap_min   CHECK (capacite_min >= 1),
    CONSTRAINT ck_salle_cap_max   CHECK (capacite_max >= capacite_min),
    CONSTRAINT ck_salle_remise    CHECK (remise_etat_min >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- SESSION : ACCUEILLIR (salle, 1,1) et ANIMER (game master, 0,1)
-- ---------------------------------------------------------------------
CREATE TABLE session (
    id_session          INT          NOT NULL AUTO_INCREMENT,
    debut_session       DATETIME     NOT NULL,
    fin_session         DATETIME     NOT NULL,
    etat_session        ENUM('PREVUE', 'PRETE', 'EN_COURS', 'TERMINEE', 'ANNULEE') NOT NULL DEFAULT 'PREVUE',
    id_salle            INT          NOT NULL,
    id_game_master      INT          NULL,
    CONSTRAINT pk_session PRIMARY KEY (id_session),
    CONSTRAINT fk_session_salle FOREIGN KEY (id_salle)
        REFERENCES salle (id_salle) ON DELETE RESTRICT ON UPDATE CASCADE,
    -- RESTRICT dans les deux sens (et non SET NULL / CASCADE) : MariaDB et
    -- MySQL interdisent une action référentielle sur une colonne utilisée
    -- dans un CHECK (ck_session_gm ci-dessous). On désactive un utilisateur
    -- au lieu de le supprimer, et un identifiant ne change jamais.
    CONSTRAINT fk_session_game_master FOREIGN KEY (id_game_master)
        REFERENCES utilisateur (id_utilisateur) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT ck_session_creneau CHECK (fin_session > debut_session),
    -- E5 : double protection du contrôle applicatif (démarrage sans GM refusé)
    CONSTRAINT ck_session_gm CHECK (
        etat_session NOT IN ('EN_COURS', 'TERMINEE') OR id_game_master IS NOT NULL
    ),
    -- E5 : recherche des sessions d'un game master qui chevauchent un créneau
    INDEX ix_session_gm_creneau (id_game_master, debut_session, fin_session),
    -- E9 : sessions d'une salle par état et par date
    INDEX ix_session_salle_etat (id_salle, etat_session, fin_session)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- RESERVATION : CONCERNER (session, 1,1) ; client sans compte (code)
-- ---------------------------------------------------------------------
CREATE TABLE reservation (
    id_reservation      INT          NOT NULL AUTO_INCREMENT,
    code_reservation    VARCHAR(12)  NOT NULL,
    nom_groupe          VARCHAR(100) NOT NULL,
    nb_joueurs          INT          NOT NULL,
    contact_nom         VARCHAR(100) NOT NULL,
    contact_email       VARCHAR(180) NOT NULL,
    contact_tel         VARCHAR(20)  NULL,
    statut_reservation  ENUM('CONFIRMEE', 'ANNULEE') NOT NULL DEFAULT 'CONFIRMEE',
    reservee_le         DATETIME     NOT NULL,
    annulee_le          DATETIME     NULL,
    statut_annulation   ENUM('SANS_FRAIS', 'TARDIVE') NULL,
    id_session          INT          NOT NULL,
    CONSTRAINT pk_reservation PRIMARY KEY (id_reservation),
    CONSTRAINT uq_reservation_code UNIQUE (code_reservation),
    CONSTRAINT fk_reservation_session FOREIGN KEY (id_session)
        REFERENCES session (id_session) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT ck_reservation_joueurs CHECK (nb_joueurs > 0),
    -- E7 : date et classement d'annulation présents si et seulement si ANNULEE
    CONSTRAINT ck_reservation_annulation CHECK (
        (statut_reservation = 'ANNULEE'
            AND annulee_le IS NOT NULL AND statut_annulation IS NOT NULL)
        OR
        (statut_reservation <> 'ANNULEE'
            AND annulee_le IS NULL AND statut_annulation IS NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- HISTORIQUE_SESSION : TRACER (session, 1,1), alimentée par le trigger (Q1)
-- ---------------------------------------------------------------------
CREATE TABLE historique_session (
    id_historique       INT          NOT NULL AUTO_INCREMENT,
    ancien_etat         ENUM('PREVUE', 'PRETE', 'EN_COURS', 'TERMINEE', 'ANNULEE') NOT NULL,
    nouvel_etat         ENUM('PREVUE', 'PRETE', 'EN_COURS', 'TERMINEE', 'ANNULEE') NOT NULL,
    change_le           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_session          INT          NOT NULL,
    CONSTRAINT pk_historique_session PRIMARY KEY (id_historique),
    CONSTRAINT fk_historique_session FOREIGN KEY (id_session)
        REFERENCES session (id_session) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- EQUIPEMENT
-- ---------------------------------------------------------------------
CREATE TABLE equipement (
    id_equipement           INT          NOT NULL AUTO_INCREMENT,
    nom_equipement          VARCHAR(100) NOT NULL,
    description_equipement  TEXT         NULL,
    critique                BOOLEAN      NOT NULL DEFAULT FALSE,
    etat_equipement         ENUM('OPERATIONNEL', 'EN_PANNE', 'HORS_SERVICE') NOT NULL DEFAULT 'OPERATIONNEL',
    CONSTRAINT pk_equipement PRIMARY KEY (id_equipement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- AFFECTER : association N-N salle <-> équipement, porteuse de dates
-- ---------------------------------------------------------------------
CREATE TABLE affecter (
    id_salle                INT          NOT NULL,
    id_equipement           INT          NOT NULL,
    date_debut_affectation  DATETIME     NOT NULL,
    date_fin_affectation    DATETIME     NULL,
    CONSTRAINT pk_affecter PRIMARY KEY (id_salle, id_equipement),
    CONSTRAINT fk_affecter_salle FOREIGN KEY (id_salle)
        REFERENCES salle (id_salle) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_affecter_equipement FOREIGN KEY (id_equipement)
        REFERENCES equipement (id_equipement) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT ck_affecter_dates CHECK (
        date_fin_affectation IS NULL OR date_fin_affectation > date_debut_affectation
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- INCIDENT : SURVENIR (salle), IMPLIQUER (équipement, 0,1), DECLARER
-- ---------------------------------------------------------------------
CREATE TABLE incident (
    id_incident             INT          NOT NULL AUTO_INCREMENT,
    titre_incident          VARCHAR(150) NOT NULL,
    description_incident    TEXT         NULL,
    gravite_incident        ENUM('MINEUR', 'BLOQUANT') NOT NULL,
    statut_incident         ENUM('OUVERT', 'RESOLU') NOT NULL DEFAULT 'OUVERT',
    declare_le              DATETIME     NOT NULL,
    resolu_le               DATETIME     NULL,
    id_salle                INT          NOT NULL,
    id_equipement           INT          NULL,
    id_declarant            INT          NOT NULL,
    CONSTRAINT pk_incident PRIMARY KEY (id_incident),
    CONSTRAINT fk_incident_salle FOREIGN KEY (id_salle)
        REFERENCES salle (id_salle) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_incident_equipement FOREIGN KEY (id_equipement)
        REFERENCES equipement (id_equipement) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_incident_declarant FOREIGN KEY (id_declarant)
        REFERENCES utilisateur (id_utilisateur) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT ck_incident_resolution CHECK (
        (statut_incident = 'RESOLU' AND resolu_le IS NOT NULL)
        OR (statut_incident <> 'RESOLU' AND resolu_le IS NULL)
    ),
    INDEX ix_incident_statut (statut_incident, declare_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- CONTROLE_MAINTENANCE (E9) : CONTROLER (salle), EFFECTUER (responsable)
-- ---------------------------------------------------------------------
CREATE TABLE controle_maintenance (
    id_controle             INT          NOT NULL AUTO_INCREMENT,
    controle_le             DATETIME     NOT NULL,
    commentaire_controle    TEXT         NULL,
    id_salle                INT          NOT NULL,
    id_responsable          INT          NOT NULL,
    CONSTRAINT pk_controle_maintenance PRIMARY KEY (id_controle),
    CONSTRAINT fk_controle_salle FOREIGN KEY (id_salle)
        REFERENCES salle (id_salle) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_controle_responsable FOREIGN KEY (id_responsable)
        REFERENCES utilisateur (id_utilisateur) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX ix_controle_salle_date (id_salle, controle_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
