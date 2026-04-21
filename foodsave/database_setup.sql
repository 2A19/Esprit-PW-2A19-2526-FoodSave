-- =============================================
-- FoodSave Database Initialization Script avec Jointures
-- =============================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS foodsave_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE foodsave_db;

-- =============================================
-- Table Categories (Références pour aliments)
-- =============================================
CREATE TABLE IF NOT EXISTS `categories` (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nom (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table Users (Utilisateurs)
-- =============================================
CREATE TABLE IF NOT EXISTS `user` (
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_statut (statut),
    INDEX idx_date_inscription (date_inscription)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table Aliments (Produits alimentaires)
-- =============================================
CREATE TABLE IF NOT EXISTS `aliments` (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    categorie_id INT NOT NULL,
    description TEXT NULL,
    calories_100g INT NULL,
    conservation_jours INT DEFAULT 7,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_categorie (categorie_id),
    INDEX idx_nom (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table Listes (Listes de courses/stocks utilisateur)
-- =============================================
CREATE TABLE IF NOT EXISTS `listes` (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    titre VARCHAR(150) NOT NULL,
    type ENUM('courses', 'stock', 'recette') DEFAULT 'courses',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    statut ENUM('active', 'archivee', 'supprimee') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table Articles Listes (JOIN user - aliments - listes)
-- =============================================
CREATE TABLE IF NOT EXISTS `articles_liste` (
    id INT PRIMARY KEY AUTO_INCREMENT,
    liste_id INT NOT NULL,
    aliment_id INT NOT NULL,
    quantite DECIMAL(10,2) NOT NULL DEFAULT 1,
    unite VARCHAR(50) DEFAULT 'piece',
    statut ENUM('a_acheter', 'achete', 'consomme') DEFAULT 'a_acheter',
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (liste_id) REFERENCES listes(id) ON DELETE CASCADE,
    FOREIGN KEY (aliment_id) REFERENCES aliments(id) ON DELETE RESTRICT,
    INDEX idx_liste (liste_id),
    INDEX idx_aliment (aliment_id),
    INDEX idx_statut (statut),
    UNIQUE KEY unique_article (liste_id, aliment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table Recettes
-- =============================================
CREATE TABLE IF NOT EXISTS `recettes` (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(200) NOT NULL,
    description TEXT NULL,
    temps_preparation INT NULL COMMENT 'en minutes',
    temps_cuisson INT NULL COMMENT 'en minutes',
    portions INT DEFAULT 4,
    difficulte ENUM('facile', 'moyen', 'difficile') DEFAULT 'moyen',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_titre (titre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table Ingredients Recette (JOIN recettes - aliments)
-- =============================================
CREATE TABLE IF NOT EXISTS `ingredients_recette` (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recette_id INT NOT NULL,
    aliment_id INT NOT NULL,
    quantite DECIMAL(10,2) NOT NULL,
    unite VARCHAR(50) DEFAULT 'piece',
    FOREIGN KEY (recette_id) REFERENCES recettes(id) ON DELETE CASCADE,
    FOREIGN KEY (aliment_id) REFERENCES aliments(id) ON DELETE RESTRICT,
    INDEX idx_recette (recette_id),
    INDEX idx_aliment (aliment_id),
    UNIQUE KEY unique_ingredient (recette_id, aliment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Insérer les catégories
-- =============================================
INSERT INTO `categories` (nom, description) VALUES 
('Fruits', 'Fruits frais et secs'),
('Légumes', 'Légumes frais et surgelés'),
('Produits Laitiers', 'Lait, fromage, yaourt, beurre'),
('Viandes', 'Viande rouge, volaille, poisson'),
('Féculents', 'Riz, pâtes, pain, pommes de terre'),
('Épices & Condiments', 'Herbes, épices, condiments'),
('Surgelés', 'Produits surgelés'),
('Conserves', 'Conserves et produits en boîte')
ON DUPLICATE KEY UPDATE id=id;

-- =============================================
-- Insérer un utilisateur administrateur par défaut
-- =============================================
-- Identifiant: admin@foodsave.com
-- Mot de passe: Admin123456
INSERT INTO `user` (nom, prenom, email, password, role, statut, telephone, date_naissance, date_inscription) 
VALUES ('FoodSave', 'Admin', 'admin@foodsave.com', '$2y$10$YIjlrKxP8KUl.UmNVn8U.OjSx4Qp3YKazfAG0BjFIv9V9Y2Dq5A2e', 'admin', 'actif', NULL, NULL, NOW())
ON DUPLICATE KEY UPDATE id=id;

-- =============================================
-- Utilisateurs de test
-- =============================================
-- Utilisateur particulier: user@foodsave.com / User@12345
INSERT INTO `user` (nom, prenom, email, password, role, statut, telephone, date_naissance, date_inscription) 
VALUES ('Dupont', 'Jean', 'user@foodsave.com', '$2y$10$1qxZMUJ3B8ZwQb8g.Kz6XeJa8fJd8KqXjKfYvUwQ9Yl8hK5YO2Wry', 'user', 'actif', '0621345678', '1990-05-15', NOW())
ON DUPLICATE KEY UPDATE id=id;

-- Autre utilisateur: test@foodsave.com / Test@12345
INSERT INTO `user` (nom, prenom, email, password, role, statut, telephone, date_naissance, date_inscription) 
VALUES ('Martin', 'Marie', 'test@foodsave.com', '$2y$10$7Qk9DqX2PvL4Ym8Nj3Rq.eOqZ5eY0wfAb6Cp1Ds2Et9Gh4Lk2VYe', 'user', 'actif', '0687654321', '1995-08-20', NOW())
ON DUPLICATE KEY UPDATE id=id;

-- =============================================
-- Afficher les structures des tables
-- =============================================
DESCRIBE `user`;
DESCRIBE `categories`;
DESCRIBE `aliments`;
DESCRIBE `listes`;
DESCRIBE `articles_liste`;
DESCRIBE `recettes`;
DESCRIBE `ingredients_recette`;
