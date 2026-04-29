-- ============================================================
--  FoodSave – Base de données
--  Compatible MySQL 5.7+ / MariaDB 10+
-- ============================================================

CREATE DATABASE IF NOT EXISTS foodsave_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE foodsave_db;

-- ---- Table categories ----
CREATE TABLE IF NOT EXISTS categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(100)  NOT NULL,
    description TEXT,
    couleur     VARCHAR(7)    DEFAULT '#4caf50' COMMENT 'Code couleur HEX',
    icone       VARCHAR(50)   DEFAULT 'tag',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Table dechets (sans user_id) ----
CREATE TABLE IF NOT EXISTS dechets (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    type_aliment  VARCHAR(100)  NOT NULL,
    quantite      DECIMAL(8,3)  NOT NULL,
    unite         VARCHAR(20)   NOT NULL,
    date_dechet   DATE          NOT NULL,
    raison        VARCHAR(150)  NOT NULL,
    notes         TEXT,
    categorie_id  INT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dechets_categorie
        FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Table collectes (sans user_id) ----
CREATE TABLE IF NOT EXISTS collectes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titre           VARCHAR(150)   NOT NULL,
    description     TEXT,
    date_collecte   DATE           NOT NULL,
    lieu            VARCHAR(200)   NOT NULL,
    quantite_totale DECIMAL(8,3)   DEFAULT 0.000,
    unite           VARCHAR(20)    NOT NULL DEFAULT 'kg',
    statut          ENUM('planifiee','en_cours','terminee','annulee') DEFAULT 'planifiee',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Table pivot collecte <-> dechets ----
CREATE TABLE IF NOT EXISTS collecte_dechets (
    collecte_id INT NOT NULL,
    dechet_id   INT NOT NULL,
    PRIMARY KEY (collecte_id, dechet_id),
    FOREIGN KEY (collecte_id) REFERENCES collectes(id) ON DELETE CASCADE,
    FOREIGN KEY (dechet_id)   REFERENCES dechets(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Données de test – catégories ----
INSERT INTO categories (nom, description, couleur, icone) VALUES
('Légumes',           'Légumes frais et surgelés',      '#4caf50', '🥦'),
('Fruits',            'Fruits frais et exotiques',      '#ff9800', '🍎'),
('Produits laitiers', 'Lait, fromages, yaourts',        '#2196f3', '🥛'),
('Viande & Poisson',  'Viandes, volailles et poissons', '#f44336', '🥩'),
('Pain & Céréales',   'Pain, pâtes, riz et céréales',   '#795548', '🍞'),
('Autres',            'Catégorie générique',             '#9e9e9e', '📦');

-- ---- Données de test – déchets ----
INSERT INTO dechets (type_aliment, quantite, unite, date_dechet, raison, notes, categorie_id) VALUES
('Légumes',           2.500, 'kg', '2026-04-01', 'Mauvais stockage',   'Carottes et poireaux', 1),
('Fruits',            1.200, 'kg', '2026-04-03', 'Surproduction',       'Pommes abîmées',       2),
('Pain',              0.400, 'kg', '2026-04-05', 'Portions excessives', '',                     5),
('Produits laitiers', 0.800, 'L',  '2026-04-06', 'Date expirée',        'Yaourts',              3),
('Viande',            0.300, 'kg', '2026-04-08', 'Esthétique',          '',                     4);

-- ---- Données de test – collectes ----
INSERT INTO collectes (titre, description, date_collecte, lieu, quantite_totale, unite, statut) VALUES
('Collecte Marché El Aouina', 'Récupération des invendus du marché',   '2026-04-10', 'Marché El Aouina, Tunis',     15.500, 'kg', 'terminee'),
('Collecte Épicerie Menzah',  'Produits proches péremption',           '2026-04-18', 'Épicerie Menzah 5',            8.200, 'kg', 'terminee'),
('Collecte Boulangerie',      'Pain et viennoiseries du soir',         '2026-04-22', 'Boulangerie Centrale, Ariana', 5.000, 'kg', 'en_cours'),
('Collecte Restaurant',       'Restes de midi non servis',             '2026-04-25', 'Restaurant La Médina, Tunis', 12.000, 'kg', 'planifiee');
