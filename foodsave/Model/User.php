<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $db;
    private $table = 'user';
    
    // Propriétés de l'utilisateur
    public $id;
    public $prenom;
    public $nom;
    public $email;
    public $password;
    public $telephone;
    public $date_naissance;
    public $role;
    public $statut;
    public $date_inscription;
    
    // Erreurs de validation
    public $errors = [];

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    /**
     * Validation des données utilisateur
     */
    public function validate() {
        $this->errors = [];

        // Validation du prénom
        if (empty($this->prenom)) {
            $this->errors['prenom'] = 'Le prénom est requis';
        } else if (strlen($this->prenom) < 2) {
            $this->errors['prenom'] = 'Le prénom doit contenir au moins 2 caractères';
        } else if (!preg_match('/^[a-zA-ZÀ-ÿ\s\'-]+$/', $this->prenom)) {
            $this->errors['prenom'] = 'Le prénom contient des caractères invalides';
        }

        // Validation du nom
        if (empty($this->nom)) {
            $this->errors['nom'] = 'Le nom est requis';
        } else if (strlen($this->nom) < 2) {
            $this->errors['nom'] = 'Le nom doit contenir au moins 2 caractères';
        } else if (!preg_match('/^[a-zA-ZÀ-ÿ\s\'-]+$/', $this->nom)) {
            $this->errors['nom'] = 'Le nom contient des caractères invalides';
        }

        // Validation de l'email
        if (empty($this->email)) {
            $this->errors['email'] = 'L\'email est requis';
        } else if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'L\'email n\'est pas valide';
        } else if ($this->emailExists($this->email, isset($this->id) ? $this->id : null)) {
            $this->errors['email'] = 'Cet email est déjà utilisé';
        }

        // Validation du mot de passe
        if (empty($this->password)) {
            if (!isset($this->id)) {
                $this->errors['password'] = 'Le mot de passe est requis';
            }
        } else if (strlen($this->password) < 8) {
            $this->errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
        } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $this->password)) {
            $this->errors['password'] = 'Le mot de passe doit contenir une majuscule, une minuscule et un chiffre';
        }

        // Validation du statut (admin edit)
        if (isset($this->statut) && $this->statut === '') {
            $this->errors['statut'] = 'Le statut est requis';
        }

        // Validation du téléphone (optionnel)
        if (!empty($this->telephone) && !preg_match('/^[\d\s\+\-\(\)]{10,}$/', $this->telephone)) {
            $this->errors['telephone'] = 'Le numéro de téléphone n\'est pas valide';
        }

        // Validation de la date de naissance (optionnel)
        if (!empty($this->date_naissance)) {
            $date = DateTime::createFromFormat('Y-m-d', $this->date_naissance);
            if (!$date || $date->format('Y-m-d') !== $this->date_naissance) {
                $this->errors['date_naissance'] = 'La date de naissance n\'est pas valide';
            }
        }

        return count($this->errors) === 0;
    }

    /**
     * Vérifie si un email existe déjà
     */
    private function emailExists($email, $excludeId = null) {
        $query = 'SELECT id FROM ' . $this->table . ' WHERE email = :email';
        if ($excludeId) {
            $query .= ' AND id != :id';
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        if ($excludeId) {
            $stmt->bindParam(':id', $excludeId);
        }
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Crée un nouvel utilisateur
     */
    public function create() {
        if (!$this->validate()) {
            return false;
        }

        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->role = 'user';
        $this->statut = 'actif';
        $this->date_inscription = date('Y-m-d H:i:s');

        $query = 'INSERT INTO ' . $this->table . ' 
                  (prenom, nom, email, password, telephone, date_naissance, role, statut, date_inscription)
                  VALUES (:prenom, :nom, :email, :password, :telephone, :date_naissance, :role, :statut, :date_inscription)';

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':prenom', $this->prenom);
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':telephone', $this->telephone);
        $stmt->bindParam(':date_naissance', $this->date_naissance);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':date_inscription', $this->date_inscription);

        return $stmt->execute();
    }

    /**
     * Authentifie un utilisateur
     */
    public function login($email, $password) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE email = :email LIMIT 1';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            $this->errors['login'] = 'Email ou mot de passe incorrect';
            return false;
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($password, $user['password'])) {
            $this->errors['login'] = 'Email ou mot de passe incorrect';
            return false;
        }

        return $user;
    }

    /**
     * Récupère tous les utilisateurs
     */
    public function getAll() {
        $query = 'SELECT id, prenom, nom, email, role, statut, telephone, date_inscription FROM ' . $this->table . ' ORDER BY date_inscription DESC';
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par ID
     */
    public function getById($id) {
        $query = 'SELECT id, prenom, nom, email, role, statut, telephone, date_naissance, date_inscription FROM ' . $this->table . ' WHERE id = :id LIMIT 1';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour les informations d'un utilisateur
     */
    public function update($id) {
        if (!$this->validate()) {
            return false;
        }

        $query = 'UPDATE ' . $this->table . ' 
                  SET prenom = :prenom, nom = :nom, email = :email, 
                      telephone = :telephone, date_naissance = :date_naissance';

        if (isset($this->statut)) {
            $query .= ', statut = :statut';
        }

        $query .= ' WHERE id = :id';

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':prenom', $this->prenom);
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':telephone', $this->telephone);
        $stmt->bindParam(':date_naissance', $this->date_naissance);

        if (isset($this->statut)) {
            $stmt->bindParam(':statut', $this->statut);
        }

        return $stmt->execute();
    }

    /**
     * Supprime un utilisateur
     */
    public function delete($id) {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Change le rôle d'un utilisateur
     */
    public function changeRole($id, $role) {
        if (!in_array($role, ['user', 'admin'])) {
            $this->errors['role'] = 'Le rôle est invalide';
            return false;
        }

        $query = 'UPDATE ' . $this->table . ' SET role = :role WHERE id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':role', $role);
        return $stmt->execute();
    }
}
?>
