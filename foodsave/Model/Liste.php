<?php
class Liste {
    private ?int $id;
    private ?int $user_id;
    private ?string $user_nom;
    private ?string $user_prenom;
    private ?string $titre;
    private ?string $type;
    private ?string $statut;
    private ?DateTime $date_creation;
    private ?DateTime $date_modification;
    private ?array $articles;
    
    public $errors = [];

    public function __construct(
        ?int $id = null,
        ?int $user_id = null,
        ?string $user_nom = null,
        ?string $user_prenom = null,
        ?string $titre = null,
        ?string $type = 'courses',
        ?string $statut = 'active',
        ?DateTime $date_creation = null,
        ?DateTime $date_modification = null,
        ?array $articles = null
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->user_nom = $user_nom;
        $this->user_prenom = $user_prenom;
        $this->titre = $titre;
        $this->type = $type;
        $this->statut = $statut;
        $this->date_creation = $date_creation;
        $this->date_modification = $date_modification;
        $this->articles = $articles ?? [];
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getUserNom() { return $this->user_nom; }
    public function getUserPrenom() { return $this->user_prenom; }
    public function getTitre() { return $this->titre; }
    public function getType() { return $this->type; }
    public function getStatut() { return $this->statut; }
    public function getDateCreation() { return $this->date_creation; }
    public function getDateModification() { return $this->date_modification; }
    public function getArticles() { return $this->articles; }

    // Setters
    public function setTitre($titre) { $this->titre = $titre; return $this; }
    public function setType($type) { $this->type = $type; return $this; }
    public function setStatut($statut) { $this->statut = $statut; return $this; }
    public function setArticles($articles) { $this->articles = $articles; return $this; }

    public function validate() {
        $this->errors = [];
        
        if (empty($this->titre)) {
            $this->errors['titre'] = 'Le titre est requis';
        }
        
        if (empty($this->user_id)) {
            $this->errors['user_id'] = 'L\'utilisateur est requis';
        }
        
        return count($this->errors) === 0;
    }
}
?>
