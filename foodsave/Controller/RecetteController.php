<?php
include(__DIR__ . '/../config/config.php');
include(__DIR__ . '/../Model/Recette.php');

class RecetteController {

    /**
     * Récupère toutes les recettes
     */
    public function getAllRecettes() {
        $sql = "SELECT 
                    r.id,
                    r.titre,
                    r.description,
                    r.temps_preparation,
                    r.temps_cuisson,
                    r.portions,
                    r.difficulte,
                    r.date_creation,
                    r.date_modification,
                    COUNT(ir.id) as nombre_ingredients
                FROM recettes r
                LEFT JOIN ingredients_recette ir ON r.id = ir.recette_id
                GROUP BY r.id
                ORDER BY r.titre";
        
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les détails d'une recette avec ses ingrédients (JOIN 3 tables)
     */
    public function getRecetteWithIngredients($recette_id) {
        $sql = "SELECT 
                    r.id,
                    r.titre,
                    r.description,
                    r.temps_preparation,
                    r.temps_cuisson,
                    r.portions,
                    r.difficulte,
                    r.date_creation,
                    r.date_modification,
                    ir.id as ingredient_id,
                    ir.aliment_id,
                    a.nom as aliment_nom,
                    c.nom as categorie_nom,
                    ir.quantite,
                    ir.unite,
                    a.calories_100g
                FROM recettes r
                LEFT JOIN ingredients_recette ir ON r.id = ir.recette_id
                LEFT JOIN aliments a ON ir.aliment_id = a.id
                LEFT JOIN categories c ON a.categorie_id = c.id
                WHERE r.id = :recette_id
                ORDER BY c.nom, a.nom";
        
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':recette_id', $recette_id, PDO::PARAM_INT);
            $req->execute();
            $results = $req->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($results)) {
                return null;
            }
            
            // Organiser les données
            $recette = [
                'id' => $results[0]['id'],
                'titre' => $results[0]['titre'],
                'description' => $results[0]['description'],
                'temps_preparation' => $results[0]['temps_preparation'],
                'temps_cuisson' => $results[0]['temps_cuisson'],
                'portions' => $results[0]['portions'],
                'difficulte' => $results[0]['difficulte'],
                'date_creation' => $results[0]['date_creation'],
                'date_modification' => $results[0]['date_modification'],
                'ingredients' => array_filter($results, function($item) {
                    return !is_null($item['ingredient_id']);
                })
            ];
            
            return $recette;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les recettes filtrées par difficulté
     */
    public function getRecetteByDifficulte($difficulte) {
        $sql = "SELECT 
                    r.id,
                    r.titre,
                    r.description,
                    r.temps_preparation,
                    r.temps_cuisson,
                    r.portions,
                    r.difficulte,
                    COUNT(ir.id) as nombre_ingredients
                FROM recettes r
                LEFT JOIN ingredients_recette ir ON r.id = ir.recette_id
                WHERE r.difficulte = :difficulte
                GROUP BY r.id
                ORDER BY r.titre";
        
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':difficulte', $difficulte);
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Ajoute une nouvelle recette
     */
    public function addRecette(Recette $recette) {
        $sql = "INSERT INTO recettes (titre, description, temps_preparation, temps_cuisson, portions, difficulte) 
                VALUES (:titre, :description, :temps_preparation, :temps_cuisson, :portions, :difficulte)";
        
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $recette->getTitre(),
                'description' => $recette->getDescription(),
                'temps_preparation' => $recette->getTempsPreparation(),
                'temps_cuisson' => $recette->getTempsCuisson(),
                'portions' => $recette->getPortions(),
                'difficulte' => $recette->getDifficulte()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Ajoute un ingrédient à une recette
     */
    public function addIngredientToRecette($recette_id, $aliment_id, $quantite, $unite = 'piece') {
        $sql = "INSERT INTO ingredients_recette (recette_id, aliment_id, quantite, unite) 
                VALUES (:recette_id, :aliment_id, :quantite, :unite)";
        
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'recette_id' => $recette_id,
                'aliment_id' => $aliment_id,
                'quantite' => $quantite,
                'unite' => $unite
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Supprime une recette
     */
    public function deleteRecette($recette_id) {
        $sql = "DELETE FROM recettes WHERE id = :id";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $recette_id, PDO::PARAM_INT);
            $req->execute();
            return true;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>
