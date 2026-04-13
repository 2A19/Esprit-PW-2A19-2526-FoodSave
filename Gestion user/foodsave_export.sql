-- =============================================
-- FoodSave export SQL - réinitialise la table user et ajoute les comptes de test
-- =============================================

CREATE DATABASE IF NOT EXISTS foodsave_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE foodsave_db;

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(20) NULL,
    date_naissance DATE NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    statut ENUM('actif', 'inactif', 'banni') DEFAULT 'actif',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_statut (statut),
    INDEX idx_date_inscription (date_inscription)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compte administrateur
-- Email : admin@foodsave.com
-- Mot de passe : Admin123456
INSERT INTO `user` (nom, prenom, email, password, role, statut, telephone, date_naissance, date_inscription)
VALUES
    ('FoodSave', 'Admin', 'admin@foodsave.com',
     '$2y$10$YIjlrKxP8KUl.UmNVn8U.OjSx4Qp3YKazfAG0BjFIv9V9Y2Dq5A2e',
     'admin', 'actif', NULL, NULL, NOW());

-- Comptes de test
INSERT INTO `user` (nom, prenom, email, password, role, statut, telephone, date_naissance, date_inscription)
VALUES
    ('Dupont', 'Jean', 'user@foodsave.com',
     '$2y$10$1qxZMUJ3B8ZwQb8g.Kz6XeJa8fJd8KqXjKfYvUwQ9Yl8hK5YO2Wry',
     'user', 'actif', '0621345678', '1990-05-15', NOW()),
    ('Martin', 'Marie', 'test@foodsave.com',
     '$2y$10$7Qk9DqX2PvL4Ym8Nj3Rq.eOqZ5eY0wfAb6Cp1Ds2Et9Gh4Lk2VYe',
     'user', 'actif', '0687654321', '1995-08-20', NOW());
