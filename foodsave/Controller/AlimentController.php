<?php
include(__DIR__ . '/../config/config.php');
include(__DIR__ . '/../Model/Aliment.php');

class AlimentController {

    /**
     * Récupère tous les aliments avec leur catégorie (JOIN)
     */
    public function getAllAliments() {
        $sql = "SELECT 
                    a.id,
                    a.nom,
                    a.categorie_id,
                    c.nom as categorie_nom,
                    a.description,
                    a.calories_100g,
                    a.conservation_jours,
                    a.date_creation,
                    a.date_modification
                FROM aliments a
                LEFT JOIN categories c ON a.categorie_id = c.id
                ORDER BY c.nom, a.nom";
        
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les aliments par catégorie
     */
    public function getAlimentsByCategory($categorie_id) {
        $sql = "SELECT 
                    a.id,
                    a.nom,
                    a.categorie_id,
                    c.nom as categorie_nom,
                    a.description,
                    a.calories_100g,
                    a.conservation_jours,
                    a.date_creation
                FROM aliments a
                LEFT JOIN categories c ON a.categorie_id = c.id
                WHERE a.categorie_id = :categorie_id
                ORDER BY a.nom";
        
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':categorie_id', $categorie_id, PDO::PARAM_INT);
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Récupère un aliment avec sa catégorie
     */
    public function getAlimentById($id) {
        $sql = "SELECT 
                    a.id,
                    a.nom,
                    a.categorie_id,
                    c.nom as categorie_nom,
                    a.description,
                    a.calories_100g,
                    a.conservation_jours,
                    a.date_creation,
                    a.date_modification
                FROM aliments a
                LEFT JOIN categories c ON a.categorie_id = c.id
                WHERE a.id = :id";
        
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id, PDO::PARAM_INT);
            $req->execute();
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Ajoute un nouvel aliment
     */
    public function addAliment(Aliment $aliment) {
        $sql = "INSERT INTO aliments (nom, categorie_id, description, calories_100g, conservation_jours) 
                VALUES (:nom, :categorie_id, :description, :calories_100g, :conservation_jours)";
        
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $aliment->getNom(),
                'categorie_id' => $aliment->getCategorieId(),
                'description' => $aliment->getDescription(),
                'calories_100g' => $aliment->getCalories(),
                'conservation_jours' => $aliment->getConservationJours()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Modifie un aliment
     */
    public function updateAliment(Aliment $aliment, $id) {
        $sql = "UPDATE aliments SET 
                nom = :nom,
                categorie_id = :categorie_id,
                description = :description,
                calories_100g = :calories_100g,
                conservation_jours = :conservation_jours
                WHERE id = :id";
        
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id' => $id,
                'nom' => $aliment->getNom(),
                'categorie_id' => $aliment->getCategorieId(),
                'description' => $aliment->getDescription(),
                'calories_100g' => $aliment->getCalories(),
                'conservation_jours' => $aliment->getConservationJours()
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Supprime un aliment
     */
    public function deleteAliment($id) {
        $sql = "DELETE FROM aliments WHERE id = :id";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $id, PDO::PARAM_INT);
            $req->execute();
            return true;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les catégories
     */
    public function getCategories() {
        $sql = "SELECT id, nom, description FROM categories ORDER BY nom";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>
