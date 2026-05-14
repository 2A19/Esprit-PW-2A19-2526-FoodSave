-- ============================================================
--  Migration SQL — Ajout des tables evenements et participants
--  Base de données: foodsave_db
-- ============================================================

CREATE TABLE IF NOT EXISTS evenements (
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

CREATE TABLE IF NOT EXISTS participants (
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

-- Données de test
INSERT IGNORE INTO evenements (id, titre, categorie, statut, date_event, heure, lieu, organisateur, capacite, description) VALUES
(1, 'Atelier Anti-Gaspillage',    'Atelier',    'upcoming', '2026-04-20', '10:00:00', 'Salle B2, Tunis',      'Faten Karoui',    30,  'Apprenez a mieux gerer vos restes alimentaires.'),
(2, 'Conference Nutrition',       'Conference', 'ongoing',  '2026-04-13', '14:00:00', 'Auditorium Central',   'Wadhah Laaribi',  100, 'Experts partagent leurs connaissances sur l alimentation durable.'),
(3, 'Food Swap Communautaire',    'Social',     'upcoming', '2026-04-25', '09:00:00', 'Place Republique',     'Nermine Achour',  50,  'Echangez vos surplus alimentaires avec la communaute.'),
(4, 'Hackathon FoodSave 2026',    'Hackathon',  'upcoming', '2026-05-03', '08:00:00', 'Hub Numerique, Lac',   'Fares Chihaoui',  80,  '48h pour developper des solutions contre le gaspillage.'),
(5, 'Marche Bio Zero Dechet',     'Social',     'past',     '2026-03-15', '08:30:00', 'Jardin El Mechtel',    'Cyrine Mahouachi',200, 'Marche mensuel promouvant le zero dechet.');

INSERT IGNORE INTO participants (nom, prenom, email, telephone, evenement_id, statut) VALUES
('Ben Salem',  'Amine',   'amine@email.com',   '55123456', 1, 'confirmed'),
('Mansouri',   'Sara',    'sara@email.com',    '55234567', 1, 'pending'),
('Trabelsi',   'Khalil',  'khalil@email.com',  '55345678', 2, 'confirmed'),
('Hamdi',      'Lina',    'lina@email.com',    '55456789', 2, 'pending'),
('Gara',       'Youssef', 'youssef@email.com', '55567890', 3, 'confirmed');
