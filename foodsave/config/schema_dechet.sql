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

-- ---- Table dechets ----
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

-- ---- Table collectes ----
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

-- ---- Table metiers ----
CREATE TABLE IF NOT EXISTS metiers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(100)  NOT NULL,
    description TEXT,
    icone       VARCHAR(10)   DEFAULT '💼',
    actif       TINYINT(1)    DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Table pivot collecte <-> dechets ----
-- Jointure 3 entités : collectes JOIN collecte_dechets JOIN dechets JOIN categories
CREATE TABLE IF NOT EXISTS collecte_dechets (
    collecte_id INT NOT NULL,
    dechet_id   INT NOT NULL,
    PRIMARY KEY (collecte_id, dechet_id),
    FOREIGN KEY (collecte_id) REFERENCES collectes(id) ON DELETE CASCADE,
    FOREIGN KEY (dechet_id)   REFERENCES dechets(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
--  DONNÉES – CATÉGORIES (10 catégories intelligentes)
-- ============================================================
INSERT INTO categories (nom, description, couleur, icone) VALUES
('Légumes & Herbes',         'Légumes frais, racines, herbes aromatiques et plantes potagères',           '#2e7d32', '🥦'),
('Fruits de Saison',         'Fruits frais locaux et exotiques, jus naturels et confitures artisanales',  '#f57c00', '🍊'),
('Produits Laitiers',        'Lait, yaourts, fromages artisanaux et crèmes fraîches à courte DLC',        '#1565c0', '🥛'),
('Viande & Poisson',         'Viandes fraîches, volailles, poissons et fruits de mer invendus du jour',   '#c62828', '🥩'),
('Boulangerie & Pâtisserie', 'Pains artisanaux, viennoiseries, gâteaux et biscuits du soir',             '#4e342e', '🥐'),
('Épicerie Sèche',           'Légumineuses, céréales, pâtes, riz, huiles et conserves proches péremption','#6a1b9a', '🫙'),
('Plats Cuisinés',           'Repas préparés non servis, soupes, tajines et couscous invendus',           '#00838f', '🍲'),
('Produits Sucrés',          'Confiseries, chocolats, miel, dattes et pâtisseries orientales',            '#ad1457', '🍯'),
('Boissons',                 'Jus de fruits, laits végétaux, smoothies et sodas proches péremption',      '#0277bd', '🧃'),
('Épices & Condiments',      'Harissa, ras-el-hanout, cumin, coriandre et mélanges épices tunisiennes',   '#558b2f', '🌶️');


-- ============================================================
--  DONNÉES – DÉCHETS (20 enregistrements réalistes Tunisie)
-- ============================================================
INSERT INTO dechets (type_aliment, quantite, unite, date_dechet, raison, notes, categorie_id) VALUES
('Courgettes bio',              3.200, 'kg', '2026-04-01', 'Esthétique (calibre irrégulier)', 'Marché Bir El Bey, parfaitement comestibles',          1),
('Persil et menthe frais',      0.800, 'kg', '2026-04-02', 'Surproduction hebdomadaire',      'Idéales pour taboulé et thé',                          1),
('Carottes déclassées',         5.000, 'kg', '2026-04-04', 'Esthétique (formes atypiques)',   'Goût identique aux calibre A',                         1),
('Oranges Maltaises de Nabeul', 4.500, 'kg', '2026-04-03', 'Surproduction saisonnière',       'Parfum exceptionnel, idéales pour jus',                2),
('Figues de Barbarie Kasserine',2.100, 'kg', '2026-04-05', 'Transport (chocs légers)',        'Très mûres, à consommer rapidement',                   2),
('Grenades de Kairouan',        1.700, 'kg', '2026-04-07', 'Date proche',                     'Arilles encore fermes et savoureux',                   2),
('Yaourts artisanaux fermiers', 1.200, 'kg', '2026-04-06', 'DLC à 48h',                       'Yaourts de Béja, non ouverts',                         3),
('Lait frais pasteurisé',       6.000, 'L',  '2026-04-08', 'DLC dépassée de 1 jour',          'Encore consommable cuit (béchamel, crêpes)',            3),
('Poulet fermier entier',       2.800, 'kg', '2026-04-06', 'Mévente du weekend',              'Volaille de Zaghouan, abattue le jour même',           4),
('Merlan frais de Bizerte',     1.500, 'kg', '2026-04-09', 'Fin de marché matinal',           'Port de Bizerte, invendu après 13h',                   4),
('Pain de seigle artisanal',    0.900, 'kg', '2026-04-05', 'Invendu en fin de journée',       'Boulangerie El Baraka, Ariana',                        5),
('Cornes de gazelle',           0.600, 'kg', '2026-04-10', 'Présentation abîmée',             'Gâteaux aux amandes, cassés mais savoureux',           5),
('Makroudh aux dattes',         1.400, 'kg', '2026-04-11', 'Surproduction Ramadan',           'Gâteaux semoule dattes, invendus après fête',          5),
('Lentilles corail bio',        2.000, 'kg', '2026-04-03', 'Emballage endommagé',             'Grains intacts, sachets percés',                       6),
('Huile d''olive vierge Sfax',  3.000, 'L',  '2026-04-10', 'DLC à 30 jours',                  'Première pression à froid, qualité premium',           6),
('Couscous agneau du vendredi', 4.200, 'kg', '2026-04-08', 'Réservation annulée',             'Restaurant La Médina, Tunis',                          7),
('Chorba frik cantine',         3.500, 'L',  '2026-04-09', 'Fermeture anticipée',             'Cantine scolaire Manouba, soupe chaude',                7),
('Tajine kefta tomates',        2.000, 'kg', '2026-04-11', 'Fin de service soir',             'Traiteur Sidi Bou Saïd',                               7),
('Jus de grenade pressé',       4.000, 'L',  '2026-04-07', 'Péremption à 24h',                'Pressé le matin, sans conservateurs',                  9),
('Smoothie mangue-gingembre',   2.000, 'L',  '2026-04-10', 'Surproduction du jour',           'Café Green & Fresh, La Marsa',                         9);


-- ============================================================
--  DONNÉES – COLLECTES (8 scénarios intelligents)
-- ============================================================
INSERT INTO collectes (titre, description, date_collecte, lieu, quantite_totale, unite, statut) VALUES
('Récupération Invendus Marché Central',
 'Collecte hebdomadaire des fruits et légumes déclassés. Partenariat avec 12 marchands volontaires. Redistribution aux familles du quartier Mellassine.',
 '2026-04-01', 'Marché Central de Tunis, Bab Souika', 28.500, 'kg', 'terminee'),

('Pain du Soir – Réseau Boulangeries Ariana',
 'Tournée quotidienne de collecte des invendus de 6 boulangeries artisanales. Redistribution le soir même via les épiceries sociales partenaires.',
 '2026-04-05', 'Réseau 6 boulangeries, Ariana Ville', 14.300, 'kg', 'terminee'),

('Repas Non Servis – Restaurant Universitaire Manouba',
 'Collecte des plats cuisinés non distribués. Partenariat FoodSave x CROUS Manouba. Plats refroidis rapidement et redistribués sous 2h aux étudiants boursiers.',
 '2026-04-08', 'Restaurant Universitaire, Campus Manouba', 22.700, 'kg', 'terminee'),

('Produits DLC Courte – Épicerie Solidaire Menzah',
 'Collecte mensuelle des produits à DLC courte (moins de 72h). Yaourts, lait, jus frais collectés et redistribués le jour même aux adhérents.',
 '2026-04-10', 'Épicerie Solidaire FoodSave, Menzah 6', 19.200, 'kg', 'terminee'),

('Opération Zéro Gaspillage – Carrefour La Marsa',
 'Partenariat pilote avec grande surface pour collecte quotidienne de fruits, légumes et produits frais déclassés. Objectif : zéro déchet alimentaire en rayon.',
 '2026-04-22', 'Carrefour La Marsa, Avenue Habib Bourguiba', 35.000, 'kg', 'en_cours'),

('Collecte Post-Festival Gastronomique Carthage',
 'Récupération des surplus du Festival Gastronomique de Carthage. Estimation 150 kg de plats cuisinés, fruits et pâtisseries à redistribuer aux banques alimentaires.',
 '2026-05-03', 'Site Archéologique de Carthage, Amphithéâtre', 0.000, 'kg', 'planifiee'),

('Buffet Excédentaire – Hôtels Corniche Bizerte',
 'Collecte des excédents de buffets petit-déjeuner et dîner de 3 hôtels. Coordination chef exécutif. Redistribution via association Baraka Bizerte.',
 '2026-05-10', 'Hôtel El Feth, Corniche de Bizerte', 0.000, 'kg', 'planifiee'),

('Collecte Cantine Scolaire – Annulée Grève',
 'Collecte annulée suite à la grève du personnel de restauration. Report prévu la semaine suivante après reprise du service normal.',
 '2026-04-15', 'Cantine École Primaire Ibn Khaldoun, Tunis', 0.000, 'kg', 'annulee');


-- ============================================================
--  DONNÉES – MÉTIERS
-- ============================================================
INSERT INTO metiers (nom, description, icone, actif) VALUES
('Restaurateur',   'Gérant ou employé de restaurant, brasserie ou snack',  '🍽️', 1),
('Épicier',        'Commerce alimentaire de détail, épicerie de quartier',  '🛒', 1),
('Boulanger',      'Artisan boulanger, pâtissier ou viennoiserie',          '🥖', 1),
('Maraîcher',      'Producteur et vendeur de fruits et légumes',            '🥦', 1),
('Grande surface', 'Supermarché ou hypermarché (GMS)',                      '🏪', 1),
('Association',    'Organisation à but non lucratif, banque alimentaire',   '🤝', 1),
('Particulier',    'Citoyen individuel souhaitant réduire son gaspillage',  '👤', 1),
('Traiteur',       'Service de repas à emporter ou livraison',              '🥡', 0);


-- ============================================================
--  JOINTURE 3 ENTITÉS : collectes ⟶ collecte_dechets ⟶ dechets ⟶ categories
-- ============================================================

-- Collecte 1 (Marché Central) → légumes déclassés + oranges + figues
INSERT INTO collecte_dechets (collecte_id, dechet_id) VALUES
(1, 1), (1, 3), (1, 4), (1, 5);

-- Collecte 2 (Boulangeries Ariana) → pains + pâtisseries
INSERT INTO collecte_dechets (collecte_id, dechet_id) VALUES
(2, 11), (2, 12), (2, 13);

-- Collecte 3 (Restaurant Universitaire) → plats cuisinés
INSERT INTO collecte_dechets (collecte_id, dechet_id) VALUES
(3, 16), (3, 17), (3, 18);

-- Collecte 4 (Épicerie Menzah) → laitiers + épicerie sèche + boissons
INSERT INTO collecte_dechets (collecte_id, dechet_id) VALUES
(4, 7), (4, 8), (4, 14), (4, 15), (4, 19), (4, 20);

-- Collecte 5 (Carrefour La Marsa) → légumes + fruits + viande/poisson
INSERT INTO collecte_dechets (collecte_id, dechet_id) VALUES
(5, 2), (5, 6), (5, 9), (5, 10);


-- ============================================================
--  VUE – Jointure 3 entités lisible
--  Usage : SELECT * FROM v_collectes_detail;
-- ============================================================
CREATE OR REPLACE VIEW v_collectes_detail AS
    SELECT
        co.id                 AS collecte_id,
        co.titre              AS collecte_titre,
        co.statut             AS collecte_statut,
        co.date_collecte,
        co.lieu,
        co.quantite_totale,
        d.id                  AS dechet_id,
        d.type_aliment,
        d.quantite            AS dechet_quantite,
        d.unite               AS dechet_unite,
        d.raison,
        cat.id                AS categorie_id,
        cat.nom               AS categorie_nom,
        cat.couleur           AS categorie_couleur,
        cat.icone             AS categorie_icone
    FROM  collectes         co
    JOIN  collecte_dechets  cd  ON cd.collecte_id = co.id
    JOIN  dechets           d   ON d.id           = cd.dechet_id
    LEFT JOIN categories    cat ON cat.id          = d.categorie_id
    ORDER BY co.date_collecte DESC, co.id, cat.nom, d.id;
