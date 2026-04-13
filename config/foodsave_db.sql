-- ============================================================
--  foodsave_db.sql
--  ETAPE 1 : Copier tout ce fichier dans phpMyAdmin > SQL > Executer
-- ============================================================

CREATE DATABASE IF NOT EXISTS foodsave_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE foodsave_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS participants;
DROP TABLE IF EXISTS evenements;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE evenements (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    titre        VARCHAR(150) NOT NULL,
    categorie    VARCHAR(80)  NOT NULL,
    statut       ENUM('upcoming','ongoing','past') NOT NULL DEFAULT 'upcoming',
    date_event   DATE         NOT NULL,
    heure        TIME         NOT NULL,
    lieu         VARCHAR(200) NOT NULL,
    organisateur VARCHAR(100) NOT NULL,
    capacite     INT          NOT NULL DEFAULT 50,
    description  TEXT,
    created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE participants (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    nom              VARCHAR(100) NOT NULL,
    prenom           VARCHAR(100) NOT NULL,
    email            VARCHAR(150) NOT NULL,
    telephone        VARCHAR(20)  DEFAULT NULL,
    evenement_id     INT          NOT NULL,
    statut           ENUM('confirmed','pending','cancelled') NOT NULL DEFAULT 'pending',
    date_inscription DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evenement_id) REFERENCES evenements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO evenements (titre, categorie, statut, date_event, heure, lieu, organisateur, capacite, description) VALUES
('Atelier Anti-Gaspillage',    'Atelier',    'upcoming', '2026-04-20', '10:00:00', 'Salle B2, Tunis',      'Faten Karoui',    30,  'Apprenez a mieux gerer vos restes alimentaires.'),
('Conference Nutrition',       'Conference', 'ongoing',  '2026-04-13', '14:00:00', 'Auditorium Central',   'Wadhah Laaribi',  100, 'Experts partagent leurs connaissances sur l alimentation durable.'),
('Food Swap Communautaire',    'Social',     'upcoming', '2026-04-25', '09:00:00', 'Place Republique',     'Nermine Achour',  50,  'Echangez vos surplus alimentaires avec la communaute.'),
('Hackathon FoodSave 2026',    'Hackathon',  'upcoming', '2026-05-03', '08:00:00', 'Hub Numerique, Lac',   'Fares Chihaoui',  80,  '48h pour developper des solutions contre le gaspillage.'),
('Marche Bio Zero Dechet',     'Social',     'past',     '2026-03-15', '08:30:00', 'Jardin El Mechtel',    'Cyrine Mahouachi',200, 'Marche mensuel promouvant le zero dechet.');

INSERT INTO participants (nom, prenom, email, telephone, evenement_id, statut) VALUES
('Ben Salem',  'Amine',   'amine@email.com',   '55123456', 1, 'confirmed'),
('Mansouri',   'Sara',    'sara@email.com',    '55234567', 1, 'pending'),
('Trabelsi',   'Khalil',  'khalil@email.com',  '55345678', 2, 'confirmed'),
('Hamdi',      'Lina',    'lina@email.com',    '55456789', 2, 'pending'),
('Gara',       'Youssef', 'youssef@email.com', '55567890', 3, 'confirmed');
