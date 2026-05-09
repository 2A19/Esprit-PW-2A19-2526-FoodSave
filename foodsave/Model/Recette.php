<?php
class Recette {
    private ?int $id;
    private ?string $titre;
    private ?string $description;
    private ?int $temps_preparation;
    private ?int $temps_cuisson;
    private ?int $portions;
    private ?string $difficulte;
    private ?DateTime $date_creation;
    private ?DateTime $date_modification;
    private ?array $ingredients;
    
    public $errors = [];

    public function __construct(
        ?int $id = null,
        ?string $titre = null,
        ?string $description = null,
        ?int $temps_preparation = null,
        ?int $temps_cuisson = null,
        ?int $portions = 4,
        ?string $difficulte = 'moyen',
        ?DateTime $date_creation = null,
        ?DateTime $date_modification = null,
        ?array $ingredients = null
    ) {
        $this->id = $id;
        $this->titre = $titre;
        $this->description = $description;
        $this->temps_preparation = $temps_preparation;
        $this->temps_cuisson = $temps_cuisson;
        $this->portions = $portions;
        $this->difficulte = $difficulte;
        $this->date_creation = $date_creation;
        $this->date_modification = $date_modification;
        $this->ingredients = $ingredients ?? [];
    }

    // Getters
    public function getId() { return $this->id; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getTempsPreparation() { return $this->temps_preparation; }
    public function getTempsCuisson() { return $this->temps_cuisson; }
    public function getPortions() { return $this->portions; }
    public function getDifficulte() { return $this->difficulte; }
    public function getDateCreation() { return $this->date_creation; }
    public function getDateModification() { return $this->date_modification; }
    public function getIngredients() { return $this->ingredients; }

    // Setters
    public function setTitre($titre) { $this->titre = $titre; return $this; }
    public function setDescription($description) { $this->description = $description; return $this; }
    public function setTempsPreparation($temps) { $this->temps_preparation = $temps; return $this; }
    public function setTempsCuisson($temps) { $this->temps_cuisson = $temps; return $this; }
    public function setPortions($portions) { $this->portions = $portions; return $this; }
    public function setDifficulte($difficulte) { $this->difficulte = $difficulte; return $this; }
    public function setIngredients($ingredients) { $this->ingredients = $ingredients; return $this; }

    public function validate() {
        $this->errors = [];
        
        if (empty($this->titre)) {
            $this->errors['titre'] = 'Le titre est requis';
        }
        
        if (count($this->ingredients) === 0) {
            $this->errors['ingredients'] = 'Au moins un ingrédient est requis';
        }
        
        return count($this->errors) === 0;
    }
}
?>
