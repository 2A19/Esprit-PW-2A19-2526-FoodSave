<?php
require_once __DIR__ . '/Model/Database.php';
require_once __DIR__ . '/config.php';

$db = config::getConnexion();

try {
    // Définir les catégories et les posts à ajouter
    $postsToAdd = [
        // Catégorie Recettes
        [
            'titre' => 'Recette : Soupe de courges anti-gaspi',
            'contenu' => 'Utilisez vos restes de courges pour une délicieuse soupe. Idéal pour valoriser les légumes de fin de saison. Ajouter du bouillon, des herbes et mixer pour obtenir une texture lisse.',
            'categorie' => 'Recettes',
            'id_utilisateur' => 1
        ],
        [
            'titre' => 'Pâtes au pesto maison avec restes d\'herbes',
            'contenu' => 'Au lieu de jeter vos herbes fanées, transformez-les en pesto délicieux. Parfait pour accompagner les pâtes ou le riz. Simple et savoureux !',
            'categorie' => 'Recettes',
            'id_utilisateur' => 2
        ],
        [
            'titre' => 'Smoothie vitaminé avec fruits abîmés',
            'contenu' => 'Les fruits légèrement abîmés sont parfaits pour les smoothies ! Banane, pomme, baie : mixez avec du yaourt et du miel. Délicieux et sain.',
            'categorie' => 'Recettes',
            'id_utilisateur' => 3
        ],
        [
            'titre' => 'Bouillon maison avec épluchures de légumes',
            'contenu' => 'Conservez les épluchures de carottes, navets et oignons. Faites bouillir pour créer un bouillon savoureux à utiliser dans vos recettes.',
            'categorie' => 'Recettes',
            'id_utilisateur' => 1
        ],
        
        // Catégorie Astuces
        [
            'titre' => 'Comment conserver les herbes fraîches plus longtemps',
            'contenu' => 'Conseil : mettez vos herbes dans un verre d\'eau recouverts d\'un sac plastique au réfrigérateur. Elles resteront frais pendant 2 semaines !',
            'categorie' => 'Astuces',
            'id_utilisateur' => 2
        ],
        [
            'titre' => 'Astuce pour raviver le pain rassis',
            'contenu' => 'Passez rapidement votre pain sous l\'eau puis enfournez à 180°C pendant 5 minutes. Il redeviendra croustillant comme au premier jour !',
            'categorie' => 'Astuces',
            'id_utilisateur' => 3
        ],
        [
            'titre' => 'Nettoyer les légumes racines sans les peler',
            'contenu' => 'Brossez simplement vos carottes et pommes de terre sous l\'eau froide. La peau contient des nutriments importants !',
            'categorie' => 'Astuces',
            'id_utilisateur' => 1
        ],
        [
            'titre' => 'Prolonger la vie des fruits avec du vinaigre blanc',
            'contenu' => 'Lavez vos fruits dans un mélange eau-vinaigre blanc (1:3). Cela élimine les bactéries et les garde frais 2 à 3 fois plus longtemps.',
            'categorie' => 'Astuces',
            'id_utilisateur' => 2
        ],
        
        // Catégorie Questions
        [
            'titre' => 'Comment bien stocker les oignons et l\'ail ?',
            'contenu' => 'J\'aimerais garder mes oignons et ail plus longtemps. Quel est le meilleur endroit et la meilleure méthode pour les conserver ?',
            'categorie' => 'Questions',
            'id_utilisateur' => 3
        ],
        [
            'titre' => 'Les restes de riz cuit peuvent-ils se congeler ?',
            'contenu' => 'Je fais toujours trop de riz. Puis-je le congeler pour le manger plus tard ? Combien de temps ça se conserve ?',
            'categorie' => 'Questions',
            'id_utilisateur' => 1
        ],
        [
            'titre' => 'Que faire des blancs d\'œufs non utilisés ?',
            'contenu' => 'J\'utilise régulièrement les jaunes mais je jette les blancs. Comment puis-je les réutiliser ? Peuvent-ils se congeler ?',
            'categorie' => 'Questions',
            'id_utilisateur' => 2
        ],
        [
            'titre' => 'Comment identifier un fruit ou légume encore bon ?',
            'contenu' => 'Parfois je ne suis pas sûr si un fruit est encore bon à manger. Comment reconnaître les signes d\'une vraie détérioration ?',
            'categorie' => 'Questions',
            'id_utilisateur' => 3
        ],
        
        // Catégorie Conseils
        [
            'titre' => 'Les meilleures pratiques pour réduire le gaspillage alimentaire',
            'contenu' => 'Conseils pratiques : faites un inventaire du frigo, planifiez vos repas, utilisez le système FIFO. Chaque petit geste compte !',
            'categorie' => 'Conseils',
            'id_utilisateur' => 1
        ],
        [
            'titre' => 'Comprendre les dates de péremption et de consommation',
            'contenu' => 'La date limite de consommation (DLC) n\'est qu\'une indication. Apprenez à utiliser vos sens pour juger la qualité d\'un produit.',
            'categorie' => 'Conseils',
            'id_utilisateur' => 2
        ],
        [
            'titre' => 'Organisation du frigo pour minimiser les pertes',
            'contenu' => 'Organisez vos étagères intelligemment : fruits et légumes en bas, produits laitiers au froid, restes en avant. Une bonne organisation = moins de gaspillage.',
            'categorie' => 'Conseils',
            'id_utilisateur' => 3
        ],
        [
            'titre' => 'Équipements essentiels pour réduire le gaspillage',
            'contenu' => 'Un bon couteau, des contenants hermétiques, une balance de cuisine... Ces outils simples vous aideront à valoriser chaque aliment.',
            'categorie' => 'Conseils',
            'id_utilisateur' => 1
        ],

        // Catégorie Autre
        [
            'titre' => 'Applications utiles pour suivre ses stocks alimentaires',
            'contenu' => 'Quelles applications utilisez-vous pour noter les dates, les restes et les produits à consommer rapidement ? Je cherche des outils simples et gratuits.',
            'categorie' => 'Autre',
            'id_utilisateur' => 2
        ],
        [
            'titre' => 'Défi anti-gaspi de la semaine : qui participe ?',
            'contenu' => 'L’idée : cuisiner 3 repas avec uniquement ce qu’on a déjà à la maison. Partagez vos menus et vos astuces pour relever le défi.',
            'categorie' => 'Autre',
            'id_utilisateur' => 1
        ],
        [
            'titre' => 'Où donner ses surplus alimentaires localement ?',
            'contenu' => 'Connaissez-vous des associations, frigos solidaires ou points de collecte pour donner des denrées encore consommables ?',
            'categorie' => 'Autre',
            'id_utilisateur' => 3
        ],
        [
            'titre' => 'Vos meilleures habitudes zéro gaspillage au quotidien',
            'contenu' => 'Je lance une discussion libre : quelles petites habitudes ont eu le plus d’impact chez vous pour réduire le gaspillage à la maison ?',
            'categorie' => 'Autre',
            'id_utilisateur' => 2
        ]
    ];
    
    // Insérer les posts
    $count = 0;
    foreach ($postsToAdd as $post) {
        $sql = "INSERT INTO posts (titre, contenu, date_creation, id_utilisateur, categorie, statue) 
                VALUES (:titre, :contenu, NOW(), :id_utilisateur, :categorie, 'actif')";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':titre' => $post['titre'],
            ':contenu' => $post['contenu'],
            ':id_utilisateur' => $post['id_utilisateur'],
            ':categorie' => $post['categorie']
        ]);
        $count++;
    }
    
    echo "✓ " . $count . " nouveaux posts ajoutés avec succès!<br>";
    echo "- 4 posts en Recettes<br>";
    echo "- 4 posts en Astuces<br>";
    echo "- 4 posts en Questions<br>";
    echo "- 4 posts en Conseils<br>";
    echo "- 4 posts en Autre<br>";
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
