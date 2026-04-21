<?php
include(__DIR__ . '/../config/config.php');
include(__DIR__ . '/../Model/Liste.php');

class ListeController {

    /**
     * Récupère toutes les listes d'un utilisateur avec JOIN
     */
    public function getListesByUser($user_id) {
        $sql = "SELECT 
                    l.id,
                    l.user_id,
                    u.nom as user_nom,
                    u.prenom as user_prenom,
                    l.titre,
                    l.type,
                    l.statut,
                    l.date_creation,
                    l.date_modification,
                    COUNT(al.id) as nombre_articles
                FROM listes l
                INNER JOIN user u ON l.user_id = u.id
                LEFT JOIN articles_liste al ON l.id = al.liste_id
                WHERE l.user_id = :user_id AND l.statut = 'active'
                GROUP BY l.id
                ORDER BY l.date_modification DESC";
        
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les détails d'une liste avec ses articles (JOIN 3 tables)
     */
    public function getListeDetailsWithArticles($liste_id) {
        $sql = "SELECT 
                    l.id,
                    l.user_id,
                    u.nom as user_nom,
                    u.prenom as user_prenom,
                    u.email as user_email,
                    l.titre,
                    l.type,
                    l.statut,
                    l.date_creation,
                    l.date_modification,
                    al.id as article_id,
                    al.aliment_id,
                    a.nom as aliment_nom,
                    c.nom as categorie_nom,
                    al.quantite,
                    al.unite,
                    al.statut as article_statut,
                    a.conservation_jours
                FROM listes l
                INNER JOIN user u ON l.user_id = u.id
                LEFT JOIN articles_liste al ON l.id = al.liste_id
                LEFT JOIN aliments a ON al.aliment_id = a.id
                LEFT JOIN categories c ON a.categorie_id = c.id
                WHERE l.id = :liste_id
                ORDER BY c.nom, a.nom";
        
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':liste_id', $liste_id, PDO::PARAM_INT);
            $req->execute();
            $results = $req->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($results)) {
                return null;
            }
            
            // Organiser les données
            $liste = [
                'id' => $results[0]['id'],
                'user_id' => $results[0]['user_id'],
                'user_nom' => $results[0]['user_nom'],
                'user_prenom' => $results[0]['user_prenom'],
                'user_email' => $results[0]['user_email'],
                'titre' => $results[0]['titre'],
                'type' => $results[0]['type'],
                'statut' => $results[0]['statut'],
                'date_creation' => $results[0]['date_creation'],
                'date_modification' => $results[0]['date_modification'],
                'articles' => array_filter($results, function($item) {
                    return !is_null($item['article_id']);
                })
            ];
            
            return $liste;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Crée une nouvelle liste
     */
    public function createListe(Liste $liste) {
        $sql = "INSERT INTO listes (user_id, titre, type, statut) 
                VALUES (:user_id, :titre, :type, :statut)";
        
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'user_id' => $liste->getUserId(),
                'titre' => $liste->getTitre(),
                'type' => $liste->getType(),
                'statut' => $liste->getStatut()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Ajoute un article à une liste
     */
    public function addArticleToListe($liste_id, $aliment_id, $quantite, $unite = 'piece') {
        $sql = "INSERT INTO articles_liste (liste_id, aliment_id, quantite, unite) 
                VALUES (:liste_id, :aliment_id, :quantite, :unite)
                ON DUPLICATE KEY UPDATE quantite = :quantite, unite = :unite";
        
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'liste_id' => $liste_id,
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
     * Supprime une liste
     */
    public function deleteListe($liste_id) {
        $sql = "DELETE FROM listes WHERE id = :id";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':id', $liste_id, PDO::PARAM_INT);
            $req->execute();
            return true;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>
