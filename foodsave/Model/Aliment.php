<?php
class Aliment {
    private ?int $id;
    private ?string $nom;
    private ?int $categorie_id;
    private ?string $categorie_nom;
    private ?string $description;
    private ?int $calories_100g;
    private ?int $conservation_jours;
    private ?DateTime $date_creation;
    private ?DateTime $date_modification;
    
    public $errors = [];

    public function __construct(
        ?int $id = null,
        ?string $nom = null,
        ?int $categorie_id = null,
        ?string $categorie_nom = null,
        ?string $description = null,
        ?int $calories_100g = null,
        ?int $conservation_jours = 7,
        ?DateTime $date_creation = null,
        ?DateTime $date_modification = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->categorie_id = $categorie_id;
        $this->categorie_nom = $categorie_nom;
        $this->description = $description;
        $this->calories_100g = $calories_100g;
        $this->conservation_jours = $conservation_jours;
        $this->date_creation = $date_creation;
        $this->date_modification = $date_modification;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getCategorieId() { return $this->categorie_id; }
    public function getCategorieNom() { return $this->categorie_nom; }
    public function getDescription() { return $this->description; }
    public function getCalories() { return $this->calories_100g; }
    public function getConservationJours() { return $this->conservation_jours; }
    public function getDateCreation() { return $this->date_creation; }
    public function getDateModification() { return $this->date_modification; }

    // Setters
    public function setNom($nom) { $this->nom = $nom; return $this; }
    public function setCategorieId($categorie_id) { $this->categorie_id = $categorie_id; return $this; }
    public function setDescription($description) { $this->description = $description; return $this; }
    public function setCalories($calories) { $this->calories_100g = $calories; return $this; }
    public function setConservationJours($jours) { $this->conservation_jours = $jours; return $this; }

    public function validate() {
        $this->errors = [];
        
        if (empty($this->nom)) {
            $this->errors['nom'] = 'Le nom est requis';
        }
        
        if (empty($this->categorie_id)) {
            $this->errors['categorie_id'] = 'La catégorie est requise';
        }
        
        return count($this->errors) === 0;
    }
}
?>
