-- ========================================
-- FoodSave Forum - Export Base de Données
-- ========================================
-- Date: 2026-04-11
-- Description: Schéma complet pour le module Forum

-- ========================================
-- Créer la base de données
-- ========================================
CREATE DATABASE IF NOT EXISTS foodsave_forum;
USE foodsave_forum;

-- ========================================
-- Table: utilisateurs (si elle n'existe pas)
-- ========================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    statue VARCHAR(20) DEFAULT 'actif',
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: posts
-- ========================================
CREATE TABLE IF NOT EXISTS posts (
    id_post INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_utilisateur INT NOT NULL,
    categorie VARCHAR(100),
    statue VARCHAR(20) DEFAULT 'actif' COMMENT 'actif ou banni',
    
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE,
    
    INDEX idx_categorie (categorie),
    INDEX idx_statue (statue),
    INDEX idx_date_creation (date_creation),
    INDEX idx_id_utilisateur (id_utilisateur),
    FULLTEXT INDEX ft_titre_contenu (titre, contenu)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: commentaires
-- ========================================
CREATE TABLE IF NOT EXISTS commentaires (
    id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_post INT NOT NULL,
    id_utilisateur INT NOT NULL,
    statue VARCHAR(20) DEFAULT 'actif' COMMENT 'actif ou banni',
    
    FOREIGN KEY (id_post) REFERENCES posts(id_post) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE,
    
    INDEX idx_id_post (id_post),
    INDEX idx_statue (statue),
    INDEX idx_date_publication (date_publication),
    INDEX idx_id_utilisateur (id_utilisateur)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Données d'exemple (optionnel)
-- ========================================

-- Insérer des utilisateurs de test
INSERT INTO utilisateurs (username, email, password, statue) VALUES
('utilisateur1', 'user1@foodsave.com', 'password_hashe_1', 'actif'),
('utilisateur2', 'user2@foodsave.com', 'password_hashe_2', 'actif'),
('admin', 'admin@foodsave.com', 'password_hashe_admin', 'actif');

-- Insérer des posts de test
INSERT INTO posts (titre, contenu, id_utilisateur, categorie, statue) VALUES
('Comment conserver les légumes frais ?', 'Bonjour à tous ! Je cherche des astuces pour garder mes légumes plus longtemps au réfrigérateur. Notamment les salades et les champignons. Merci pour vos conseils !', 1, 'Astuces', 'actif'),
('Recette : Soupe de légumes anti-gaspi', 'Voici ma recette préférée pour utiliser les restes de légumes. C\'est simple, rapide et délicieux ! Commencez par couper les légumes en petits morceaux, puis faites-les revenir dans l\'huile d\'olive avec un oignon...', 2, 'Recettes', 'actif'),
('Congélation des fruits : mode d\'emploi', 'Beaucoup de gens pensent que congeler des fruits c\'est compliqué, mais c\'est en fait très simple ! Voici les étapes à suivre pour bien congeler vos fruits sans les abîmer...', 1, 'Conseils', 'actif'),
('Questions sur le compostage', 'Bonjour ! J\'aimerais démarrer un compost chez moi mais j\'ai plusieurs questions. Est-ce que je peux y mettre tous les restes de cuisine ? Y a-t-il des mauvaises odeurs ? Combien de temps faut-il ?', 2, 'Questions', 'actif');

-- Insérer des commentaires de test
INSERT INTO commentaires (contenu, id_post, id_utilisateur, statue) VALUES
('Super conseil ! J\'utilise des boîtes hermétiques et ça marche très bien.', 1, 2, 'actif'),
('Merci beaucoup pour cette astuce ! Je vais l\'essayer dès demain.', 1, 1, 'actif'),
('Délicieuse cette recette ! J\'en ai fait une hier et ma famille a adoré !', 2, 1, 'actif'),
('Est-ce qu\'on peut aussi congeler les tomates ? Les miennes vont avant qu\'on les utilise...', 3, 2, 'actif'),
('Très utile ! J\'attendais juste ce genre de guide.', 3, 1, 'actif'),
('Je pense que je vais commencer mon compost ce week-end !', 4, 1, 'actif');

-- ========================================
-- Fin de l'export
-- ========================================
