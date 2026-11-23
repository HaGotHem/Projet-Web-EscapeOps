-- =====================================================================
-- EscapeOps · Les Archives Obscures
-- seed.sql : données fictives de démonstration
--
-- À rejouer après 001_schema.sql, sur une base vide.
-- Les dates sont RELATIVES au moment de l'import (CURDATE / NOW) :
-- rejouez le seed le matin de la démonstration pour que les scénarios
-- « aujourd'hui 18 h », « dans 2 h » et « dans 2 jours » restent vrais.
--
-- Comptes de démonstration (tous fictifs) — mot de passe : demo-escapeops-2026
--   admin@archives-obscures.example          ADMIN
--   camille.roux@archives-obscures.example   RESPONSABLE
--   lea.martin@archives-obscures.example     GAME_MASTER
--   hugo.bernard@archives-obscures.example   GAME_MASTER
--   nora.benali@archives-obscures.example    GAME_MASTER
--   maxime.lefevre@archives-obscures.example GAME_MASTER
--   jules.petit@archives-obscures.example    GAME_MASTER (compte désactivé)
--
-- Scénarios préparés :
--   E5  session 25 (Cabinet, aujourd'hui 18 h-19 h, Léa Martin)
--       session 26 (Crypte, aujourd'hui 18 h-19 h, sans GM, PRETE)
--         -> affecter Léa : REFUSÉ (chevauchement) ; démarrer : REFUSÉ (sans GM)
--       session 27 (Crypte, aujourd'hui 19 h-20 h, sans GM)
--         -> affecter Léa : ACCEPTÉ (créneaux jointifs)
--   E7  réservation AO-INSOMNIA (session 28, dans 1 à 2 h) -> annulation TARDIVE
--       réservation AO-ARCHIMED (session 34, dans 2 jours) -> annulation SANS_FRAIS
--   E9  Laboratoire Interdit : 9 sessions terminées + 1 annulée depuis le
--       dernier contrôle ; la session 24 est EN_COURS : la terminer = 10e
--       -> badge CONTROLE_A_PREVOIR. Cabinet : 4, Crypte : 6.
-- =====================================================================

SET NAMES utf8mb4;

SET @j   = CURDATE();
SET @now = NOW();
SET @mdp = '$2y$10$.rLInW1XN4yPUXQuUWdZiOBpTx2J0quLIxzNEnY5/s42Qmn.ohefW';

-- ---------------------------------------------------------------------
-- Utilisateurs
-- ---------------------------------------------------------------------
INSERT INTO utilisateur (id_utilisateur, nom_utilisateur, prenom_utilisateur, email_utilisateur, mdp_hash, role_utilisateur, actif_utilisateur) VALUES
(1, 'Garnier',  'Alex',    'admin@archives-obscures.example',          @mdp, 'ADMIN',       TRUE),
(2, 'Roux',     'Camille', 'camille.roux@archives-obscures.example',   @mdp, 'RESPONSABLE', TRUE),
(3, 'Martin',   'Léa',     'lea.martin@archives-obscures.example',     @mdp, 'GAME_MASTER', TRUE),
(4, 'Bernard',  'Hugo',    'hugo.bernard@archives-obscures.example',   @mdp, 'GAME_MASTER', TRUE),
(5, 'Benali',   'Nora',    'nora.benali@archives-obscures.example',    @mdp, 'GAME_MASTER', TRUE),
(6, 'Lefèvre',  'Maxime',  'maxime.lefevre@archives-obscures.example', @mdp, 'GAME_MASTER', TRUE),
(7, 'Petit',    'Jules',   'jules.petit@archives-obscures.example',    @mdp, 'GAME_MASTER', FALSE);

-- ---------------------------------------------------------------------
-- Salles (textes repris de la maquette de la vitrine)
-- ---------------------------------------------------------------------
INSERT INTO salle (id_salle, nom_salle, theme_salle, description_salle, duree_jeu_min, capacite_min, capacite_max, remise_etat_min) VALUES
(1, 'Le Cabinet des Ombres', 'Cabinet de curiosités',
    'Le cabinet de curiosités d''un collectionneur disparu. Retrouvez le registre qu''il a caché avant l''aube.',
    60, 2, 6, 20),
(2, 'La Crypte de l''Archiviste', 'Crypte et bibliothèque secrète',
    'Sous la bibliothèque, l''archiviste a scellé un secret. Percez son système de classement avant que la crypte ne se referme.',
    60, 2, 5, 15),
(3, 'Le Laboratoire Interdit', 'Laboratoire de 1923',
    'Une expérience interrompue en 1923. Reprenez les notes du professeur et terminez la formule avant qu''elle ne vous échappe.',
    75, 3, 6, 15);

-- ---------------------------------------------------------------------
-- Contrôles de maintenance (E9) : point de départ des compteurs
-- ---------------------------------------------------------------------
INSERT INTO controle_maintenance (id_controle, controle_le, commentaire_controle, id_salle, id_responsable) VALUES
(1, @j - INTERVAL 6 DAY  + INTERVAL 10 HOUR, 'Serrures et coffre vérifiés, registre recollé.', 1, 2),
(2, @j - INTERVAL 8 DAY  + INTERVAL 10 HOUR, 'Éclairage UV remplacé, orgue réaccordé.',          2, 2),
(3, @j - INTERVAL 10 DAY + INTERVAL 10 HOUR, 'Fioles et circuit lumineux testés.',               3, 2);

-- ---------------------------------------------------------------------
-- Sessions passées
-- ---------------------------------------------------------------------
INSERT INTO session (id_session, debut_session, fin_session, etat_session, id_salle, id_game_master) VALUES
-- Cabinet des Ombres (17 h-18 h, Hugo) : 2 avant le contrôle, 4 après
( 1, @j - INTERVAL 8 DAY + INTERVAL 17 HOUR, @j - INTERVAL 8 DAY + INTERVAL 18 HOUR, 'TERMINEE', 1, 4),
( 2, @j - INTERVAL 7 DAY + INTERVAL 17 HOUR, @j - INTERVAL 7 DAY + INTERVAL 18 HOUR, 'TERMINEE', 1, 4),
( 3, @j - INTERVAL 5 DAY + INTERVAL 17 HOUR, @j - INTERVAL 5 DAY + INTERVAL 18 HOUR, 'TERMINEE', 1, 4),
( 4, @j - INTERVAL 4 DAY + INTERVAL 17 HOUR, @j - INTERVAL 4 DAY + INTERVAL 18 HOUR, 'TERMINEE', 1, 4),
( 5, @j - INTERVAL 2 DAY + INTERVAL 17 HOUR, @j - INTERVAL 2 DAY + INTERVAL 18 HOUR, 'TERMINEE', 1, 4),
( 6, @j - INTERVAL 1 DAY + INTERVAL 17 HOUR, @j - INTERVAL 1 DAY + INTERVAL 18 HOUR, 'TERMINEE', 1, 4),
-- Crypte de l'Archiviste (20 h 30-21 h 30, Nora) : 6 après le contrôle + 1 annulée
( 7, @j - INTERVAL 7 DAY + INTERVAL '20:30' HOUR_MINUTE, @j - INTERVAL 7 DAY + INTERVAL '21:30' HOUR_MINUTE, 'TERMINEE', 2, 5),
( 8, @j - INTERVAL 6 DAY + INTERVAL '20:30' HOUR_MINUTE, @j - INTERVAL 6 DAY + INTERVAL '21:30' HOUR_MINUTE, 'TERMINEE', 2, 5),
( 9, @j - INTERVAL 5 DAY + INTERVAL '20:30' HOUR_MINUTE, @j - INTERVAL 5 DAY + INTERVAL '21:30' HOUR_MINUTE, 'TERMINEE', 2, 5),
(10, @j - INTERVAL 4 DAY + INTERVAL '20:30' HOUR_MINUTE, @j - INTERVAL 4 DAY + INTERVAL '21:30' HOUR_MINUTE, 'TERMINEE', 2, 5),
(11, @j - INTERVAL 3 DAY + INTERVAL '20:30' HOUR_MINUTE, @j - INTERVAL 3 DAY + INTERVAL '21:30' HOUR_MINUTE, 'TERMINEE', 2, 5),
(12, @j - INTERVAL 1 DAY + INTERVAL '20:30' HOUR_MINUTE, @j - INTERVAL 1 DAY + INTERVAL '21:30' HOUR_MINUTE, 'TERMINEE', 2, 5),
(13, @j - INTERVAL 2 DAY + INTERVAL 21 HOUR,            @j - INTERVAL 2 DAY + INTERVAL 22 HOUR,            'ANNULEE',  2, NULL),
-- Laboratoire Interdit (19 h-20 h 15, Léa / Maxime) : 9 terminées + 1 annulée
(14, @j - INTERVAL 9 DAY + INTERVAL 19 HOUR, @j - INTERVAL 9 DAY + INTERVAL '20:15' HOUR_MINUTE, 'TERMINEE', 3, 3),
(15, @j - INTERVAL 8 DAY + INTERVAL 19 HOUR, @j - INTERVAL 8 DAY + INTERVAL '20:15' HOUR_MINUTE, 'TERMINEE', 3, 6),
(16, @j - INTERVAL 7 DAY + INTERVAL 19 HOUR, @j - INTERVAL 7 DAY + INTERVAL '20:15' HOUR_MINUTE, 'TERMINEE', 3, 3),
(17, @j - INTERVAL 6 DAY + INTERVAL 19 HOUR, @j - INTERVAL 6 DAY + INTERVAL '20:15' HOUR_MINUTE, 'TERMINEE', 3, 6),
(18, @j - INTERVAL 5 DAY + INTERVAL 19 HOUR, @j - INTERVAL 5 DAY + INTERVAL '20:15' HOUR_MINUTE, 'TERMINEE', 3, 3),
(19, @j - INTERVAL 4 DAY + INTERVAL 19 HOUR, @j - INTERVAL 4 DAY + INTERVAL '20:15' HOUR_MINUTE, 'TERMINEE', 3, 6),
(20, @j - INTERVAL 3 DAY + INTERVAL 19 HOUR, @j - INTERVAL 3 DAY + INTERVAL '20:15' HOUR_MINUTE, 'TERMINEE', 3, 3),
(21, @j - INTERVAL 2 DAY + INTERVAL 19 HOUR, @j - INTERVAL 2 DAY + INTERVAL '20:15' HOUR_MINUTE, 'TERMINEE', 3, 6),
(22, @j - INTERVAL 1 DAY + INTERVAL 19 HOUR, @j - INTERVAL 1 DAY + INTERVAL '20:15' HOUR_MINUTE, 'TERMINEE', 3, 3),
(23, @j - INTERVAL 5 DAY + INTERVAL 21 HOUR, @j - INTERVAL 5 DAY + INTERVAL '22:15' HOUR_MINUTE, 'ANNULEE',  3, NULL);

-- ---------------------------------------------------------------------
-- Sessions du jour (scénarios E5, E7, E9)
-- ---------------------------------------------------------------------
SET @en_cours = DATE_FORMAT(@now - INTERVAL 30 MINUTE, '%Y-%m-%d %H:%i:00');
SET @dans_2h  = DATE_FORMAT(@now + INTERVAL 2 HOUR,    '%Y-%m-%d %H:00:00');

INSERT INTO session (id_session, debut_session, fin_session, etat_session, id_salle, id_game_master) VALUES
(24, @en_cours, @en_cours + INTERVAL 75 MINUTE, 'EN_COURS', 3, 6),                  -- E9 : la 10e partie
(25, @j + INTERVAL 18 HOUR, @j + INTERVAL 19 HOUR, 'PRETE',  1, 3),                -- E5 : Léa à 18 h
(26, @j + INTERVAL 18 HOUR, @j + INTERVAL 19 HOUR, 'PRETE',  2, NULL),             -- E5 : conflit / démarrage refusé
(27, @j + INTERVAL 19 HOUR, @j + INTERVAL 20 HOUR, 'PREVUE', 2, NULL),             -- E5 : créneau jointif
(28, @dans_2h, @dans_2h + INTERVAL 75 MINUTE,      'PREVUE', 3, NULL);              -- E7 : annulation tardive

-- ---------------------------------------------------------------------
-- Créneaux publiés des prochains jours (vitrine)
-- ---------------------------------------------------------------------
INSERT INTO session (id_session, debut_session, fin_session, etat_session, id_salle, id_game_master) VALUES
(29, @j + INTERVAL 1 DAY + INTERVAL 18 HOUR,            @j + INTERVAL 1 DAY + INTERVAL 19 HOUR,            'PREVUE', 1, 4),
(30, @j + INTERVAL 1 DAY + INTERVAL 20 HOUR,            @j + INTERVAL 1 DAY + INTERVAL 21 HOUR,            'PREVUE', 1, NULL),
(31, @j + INTERVAL 2 DAY + INTERVAL '18:30' HOUR_MINUTE, @j + INTERVAL 2 DAY + INTERVAL '19:30' HOUR_MINUTE, 'PREVUE', 1, NULL),
(32, @j + INTERVAL 1 DAY + INTERVAL 17 HOUR,            @j + INTERVAL 1 DAY + INTERVAL 18 HOUR,            'PREVUE', 2, NULL),
(33, @j + INTERVAL 1 DAY + INTERVAL 19 HOUR,            @j + INTERVAL 1 DAY + INTERVAL 20 HOUR,            'PREVUE', 2, NULL),
(34, @j + INTERVAL 2 DAY + INTERVAL 18 HOUR,            @j + INTERVAL 2 DAY + INTERVAL 19 HOUR,            'PREVUE', 2, 5),
(35, @j + INTERVAL 1 DAY + INTERVAL 19 HOUR,            @j + INTERVAL 1 DAY + INTERVAL '20:15' HOUR_MINUTE, 'PREVUE', 3, NULL),
(36, @j + INTERVAL 2 DAY + INTERVAL '17:15' HOUR_MINUTE, @j + INTERVAL 2 DAY + INTERVAL '18:30' HOUR_MINUTE, 'PREVUE', 3, NULL),
(37, @j + INTERVAL 2 DAY + INTERVAL '20:30' HOUR_MINUTE, @j + INTERVAL 2 DAY + INTERVAL '21:45' HOUR_MINUTE, 'PREVUE', 3, 6);

-- ---------------------------------------------------------------------
-- Réservations des sessions terminées (une par session, groupes fictifs)
-- ---------------------------------------------------------------------
INSERT INTO reservation (code_reservation, nom_groupe, nb_joueurs, contact_nom, contact_email, contact_tel, statut_reservation, reservee_le, id_session)
SELECT
    CONCAT('AO-', LPAD(s.id_session, 5, '0')),
    ELT(1 + s.id_session % 8, 'Les Lanternes', 'Les Insolites', 'Les Hiboux', 'Les Curieux',
        'Les Sphinx', 'Les Rêveurs', 'Les Zigotos', 'Les Cartographes'),
    sa.capacite_min + s.id_session % (sa.capacite_max - sa.capacite_min + 1),
    ELT(1 + s.id_session % 6, 'Inès Moreau', 'Paul Girard', 'Chloé Lambert', 'Yanis Fontaine',
        'Manon Chevalier', 'Théo Robin'),
    CONCAT('groupe', s.id_session, '@exemple.test'),
    NULL,
    'CONFIRMEE',
    s.debut_session - INTERVAL 5 DAY,
    s.id_session
FROM session s
JOIN salle sa ON sa.id_salle = s.id_salle
WHERE s.etat_session = 'TERMINEE';

-- ---------------------------------------------------------------------
-- Réservations des scénarios et des prochains jours
-- ---------------------------------------------------------------------
INSERT INTO reservation (code_reservation, nom_groupe, nb_joueurs, contact_nom, contact_email, contact_tel, statut_reservation, reservee_le, annulee_le, statut_annulation, id_session) VALUES
-- en cours (E9)
('AO-VAGABOND', 'Les Vagabonds',     4, 'Lucas Perrin',   'vagabonds@exemple.test',  '06 00 00 00 01', 'CONFIRMEE', @j - INTERVAL 3 DAY + INTERVAL 11 HOUR, NULL, NULL, 24),
-- ce soir 18 h (E5)
('AO-PIPELETT', 'Les Pipelettes',    5, 'Sarah Lemoine',  'pipelettes@exemple.test', '06 00 00 00 02', 'CONFIRMEE', @j - INTERVAL 4 DAY + INTERVAL 15 HOUR, NULL, NULL, 25),
('AO-DUVAL001', 'Famille Duval',     4, 'Marc Duval',     'duval@exemple.test',      NULL,             'CONFIRMEE', @j - INTERVAL 6 DAY + INTERVAL 9 HOUR,  NULL, NULL, 26),
-- dans 1 à 2 h : à annuler pendant la démo -> TARDIVE (E7)
('AO-INSOMNIA', 'Les Insomniaques',  3, 'Emma Caron',     'insomniaques@exemple.test', '06 00 00 00 03', 'CONFIRMEE', @j - INTERVAL 2 DAY + INTERVAL 20 HOUR, NULL, NULL, 28),
-- demain 18 h
('AO-LANTERNE', 'Les Lanternes',     6, 'Hélène Faure',   'lanternes@exemple.test',  NULL,             'CONFIRMEE', @j - INTERVAL 1 DAY + INTERVAL 14 HOUR, NULL, NULL, 29),
-- demain 20 h : annulée plus de 24 h avant -> SANS_FRAIS, le créneau redevient libre
('AO-FEUFOLLE', 'Les Feux Follets',  4, 'Karim Haddad',   'feuxfollets@exemple.test', NULL,            'ANNULEE',   @j - INTERVAL 5 DAY + INTERVAL 10 HOUR,
    @j - INTERVAL 2 DAY + INTERVAL 9 HOUR, 'SANS_FRAIS', 30),
-- dans 2 jours 18 h : à annuler pendant la démo -> SANS_FRAIS (E7)
('AO-ARCHIMED', 'Team Archimède',    5, 'Julie Marchand', 'archimede@exemple.test',  '06 00 00 00 04', 'CONFIRMEE', @j - INTERVAL 1 DAY + INTERVAL 18 HOUR, NULL, NULL, 34),
-- dans 2 jours 20 h 30
('AO-MINUIT01', 'Collectif Minuit',  6, 'Bastien Roy',    'minuit@exemple.test',     NULL,             'CONFIRMEE', @j - INTERVAL 1 DAY + INTERVAL 21 HOUR, NULL, NULL, 37),
-- avant-hier 21 h, Crypte : annulée 6 h avant -> TARDIVE ; la session a été annulée
('AO-OMBRESV1', 'Les Ombres Vives',  3, 'Anaïs Brunet',   'ombresvives@exemple.test', NULL,            'ANNULEE',   @j - INTERVAL 9 DAY + INTERVAL 16 HOUR,
    @j - INTERVAL 2 DAY + INTERVAL 15 HOUR, 'TARDIVE', 13);

-- ---------------------------------------------------------------------
-- Équipements et affectations (AFFECTER)
-- ---------------------------------------------------------------------
INSERT INTO equipement (id_equipement, nom_equipement, description_equipement, critique, etat_equipement) VALUES
(1, 'Mécanisme de la porte secrète', 'Vérin et aimant qui libèrent la bibliothèque pivotante.', TRUE,  'OPERATIONNEL'),
(2, 'Coffre à combinaison',          'Coffre à 4 molettes contenant le registre.',            TRUE,  'OPERATIONNEL'),
(3, 'Orgue à pédales électronique',  'Joue la séquence finale quand les 5 notes sont trouvées.', TRUE, 'OPERATIONNEL'),
(4, 'Lampe UV portative',            'Révèle les annotations invisibles. Circule entre les salles.', FALSE, 'EN_PANNE'),
(5, 'Paillasse à fioles lumineuses', 'Les fioles s''allument quand la formule est correcte.',  TRUE,  'OPERATIONNEL'),
(6, 'Talkie-walkie n° 1',            'Liaison entre le game master et les joueurs.',            FALSE, 'OPERATIONNEL'),
(7, 'Caméra de surveillance',        'Retour vidéo vers le poste du game master.',              FALSE, 'OPERATIONNEL');

INSERT INTO affecter (id_salle, id_equipement, date_debut_affectation, date_fin_affectation) VALUES
(2, 1, @j - INTERVAL 60 DAY, NULL),
(1, 2, @j - INTERVAL 60 DAY, NULL),
(2, 3, @j - INTERVAL 60 DAY, NULL),
(1, 4, @j - INTERVAL 20 DAY, @j - INTERVAL 12 DAY),   -- d'abord au Cabinet...
(2, 4, @j - INTERVAL 12 DAY, NULL),                   -- ...puis à la Crypte
(3, 5, @j - INTERVAL 60 DAY, NULL),
(3, 6, @j - INTERVAL 30 DAY, NULL),
(3, 7, @j - INTERVAL 30 DAY, NULL);

-- ---------------------------------------------------------------------
-- Incidents
-- ---------------------------------------------------------------------
INSERT INTO incident (titre_incident, description_incident, gravite_incident, statut_incident, declare_le, resolu_le, id_salle, id_equipement, id_declarant) VALUES
('La lampe UV clignote',
    'La lampe s''éteint au bout de quelques secondes ; les annotations restent illisibles.',
    'MINEUR', 'OUVERT', @now - INTERVAL 2 HOUR, NULL, 2, 4, 5),
('Coffre bloqué en position ouverte',
    'La molette n° 3 ne s''enclenche plus, le coffre ne se referme pas.',
    'BLOQUANT', 'RESOLU', @j - INTERVAL 3 DAY + INTERVAL '17:40' HOUR_MINUTE,
    @j - INTERVAL 3 DAY + INTERVAL '18:30' HOUR_MINUTE, 1, 2, 4),
('Fuite d''eau sous l''évier du décor',
    'Flaque près de la paillasse après la partie de 19 h ; aucun équipement touché.',
    'MINEUR', 'OUVERT', @j - INTERVAL 1 DAY + INTERVAL '20:20' HOUR_MINUTE, NULL, 3, NULL, 3);
