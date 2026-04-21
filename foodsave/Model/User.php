<?php
class User {
    private ?int $id;
    private ?string $prenom;
    private ?string $nom;
    private ?string $email;
    private ?string $password;
    private ?string $telephone;
    private ?DateTime $date_naissance;
    private ?string $role;
    private ?string $statut;
    private ?DateTime $date_inscription;

    // Erreurs de validation
    public $errors = [];

    // Constructor
    public function __construct(?int $id = null, ?string $prenom = null, ?string $nom = null, ?string $email = null, ?string $password = null, ?string $telephone = null, ?DateTime $date_naissance = null, ?string $role = null, ?string $statut = null, ?DateTime $date_inscription = null) {
        $this->id = $id;
        $this->prenom = $prenom;
        $this->nom = $nom;
        $this->email = $email;
        $this->password = $password;
        $this->telephone = $telephone;
        $this->date_naissance = $date_naissance;
        $this->role = $role;
        $this->statut = $statut;
        $this->date_inscription = $date_inscription;
    }

    public function show() {
        echo '<table border="1" cellpadding="5">';
        echo '<tr><th>ID</th><th>Prénom</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Date de naissance</th><th>Rôle</th><th>Statut</th><th>Date d\'inscription</th></tr>';
        echo '<tr>';
        echo '<td>' . $this->id . '</td>';
        echo '<td>' . $this->prenom . '</td>';
        echo '<td>' . $this->nom . '</td>';
        echo '<td>' . $this->email . '</td>';
        echo '<td>' . $this->telephone . '</td>';
        echo '<td>' . ($this->date_naissance ? $this->date_naissance->format('Y-m-d') : '') . '</td>';
        echo '<td>' . $this->role . '</td>';
        echo '<td>' . $this->statut . '</td>';
        echo '<td>' . ($this->date_inscription ? $this->date_inscription->format('Y-m-d H:i:s') : '') . '</td>';
        echo '</tr>';
        echo '</table>';
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
        } else if ($this->emailExists($this->email, $this->id)) {
            $this->errors['email'] = 'Cet email est déjà utilisé';
        }

        // Validation du mot de passe
        if (empty($this->password)) {
            if (!$this->id) {
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
        if (!empty($this->date_naissance) && $this->date_naissance instanceof DateTime) {
            // Already DateTime, no need to validate format
        } elseif (!empty($this->date_naissance)) {
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
        $db = config::getConnexion();
        $query = 'SELECT id FROM user WHERE email = :email';
        if ($excludeId) {
            $query .= ' AND id != :id';
        }
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        if ($excludeId) {
            $stmt->bindParam(':id', $excludeId);
        }
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Getters and Setters
    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getPrenom(): ?string {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): void {
        $this->prenom = $prenom;
    }

    public function getNom(): ?string {
        return $this->nom;
    }

    public function setNom(?string $nom): void {
        $this->nom = $nom;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(?string $email): void {
        $this->email = $email;
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(?string $password): void {
        $this->password = $password;
    }

    public function getTelephone(): ?string {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): void {
        $this->telephone = $telephone;
    }

    public function getDateNaissance(): ?DateTime {
        return $this->date_naissance;
    }

    public function setDateNaissance(?DateTime $date_naissance): void {
        $this->date_naissance = $date_naissance;
    }

    public function getRole(): ?string {
        return $this->role;
    }

    public function setRole(?string $role): void {
        $this->role = $role;
    }

    public function getStatut(): ?string {
        return $this->statut;
    }

    public function setStatut(?string $statut): void {
        $this->statut = $statut;
    }

    public function getDateInscription(): ?DateTime {
        return $this->date_inscription;
    }

    public function setDateInscription(?DateTime $date_inscription): void {
        $this->date_inscription = $date_inscription;
    }
}
?>
