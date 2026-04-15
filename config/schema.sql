-- ============================================================
--  FoodSave – Base de données : Gestion des Déchets
--  Compatible MySQL 5.7+ / MariaDB 10+
-- ============================================================

CREATE DATABASE IF NOT EXISTS foodsave_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE foodsave_db;

-- ---- Table users ----
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(80)  NOT NULL,
    prenom     VARCHAR(80)  NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Table dechets ----
CREATE TABLE IF NOT EXISTS dechets (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT           NOT NULL,
    type_aliment  VARCHAR(100)  NOT NULL,
    quantite      DECIMAL(8,3)  NOT NULL,
    unite         VARCHAR(20)   NOT NULL,
    date_dechet   DATE          NOT NULL,
    raison        VARCHAR(150)  NOT NULL,
    notes         TEXT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Table historique ----
CREATE TABLE IF NOT EXISTS historique (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    dechet_id  INT          NOT NULL,
    user_id    INT          NOT NULL,
    action     ENUM('CREATE','UPDATE','DELETE') NOT NULL,
    detail     TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dechet_id) REFERENCES dechets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Données de test ----
INSERT INTO users (nom, prenom, email, password, role) VALUES
('Karoui',    'Faten',   'faten@esprit.tn',   SHA2('password123',256), 'user'),
('Laaribi',   'Wadhah',  'wadhah@esprit.tn',  SHA2('password123',256), 'user'),
('Achour',    'Nermine', 'nermine@esprit.tn',  SHA2('password123',256), 'user'),
('Chihaoui',  'Fares',   'fares@esprit.tn',   SHA2('password123',256), 'user'),
('Mahouachi', 'Cyrine',  'cyrine@esprit.tn',  SHA2('password123',256), 'user'),
('Admin',     'FoodSave','admin@foodsave.tn',  SHA2('admin2026',256),   'admin');

INSERT INTO dechets (user_id, type_aliment, quantite, unite, date_dechet, raison, notes) VALUES
(1, 'Légumes',          2.500, 'kg', '2026-04-01', 'Mauvais stockage',   'Carottes et poireaux'),
(2, 'Fruits',           1.200, 'kg', '2026-04-03', 'Surproduction',       'Pommes abîmées'),
(3, 'Pain',             0.400, 'kg', '2026-04-05', 'Portions excessives', ''),
(4, 'Produits laitiers',0.800, 'L',  '2026-04-06', 'Date expirée',        'Yaourts'),
(5, 'Viande',           0.300, 'kg', '2026-04-08', 'Esthétique',          '');
