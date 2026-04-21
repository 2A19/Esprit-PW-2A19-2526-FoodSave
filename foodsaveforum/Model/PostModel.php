<?php
if (!class_exists('PostModel')) {
class PostModel {
    private ?int $id_post;
    private ?string $titre;
    private ?string $contenu;
    private ?DateTime $date_creation;
    private ?int $id_utilisateur;
    private ?string $categorie;
    private ?string $statue;

    // Constructor
    public function __construct(?int $id_post, ?string $titre, ?string $contenu, ?DateTime $date_creation, ?int $id_utilisateur, ?string $categorie, ?string $statue) {
        $this->id_post = $id_post;
        $this->titre = $titre;
        $this->contenu = $contenu;
        $this->date_creation = $date_creation;
        $this->id_utilisateur = $id_utilisateur;
        $this->categorie = $categorie;
        $this->statue = $statue;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID Post</th><th>Titre</th><th>Contenu</th><th>Date Creation</th><th>ID Utilisateur</th><th>Categorie</th><th>Statue</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id_post}</td>";
        echo "<td>{$this->titre}</td>";
        echo "<td>{$this->contenu}</td>";
        echo "<td>" . ($this->date_creation ? $this->date_creation->format('Y-m-d H:i:s') : '') . "</td>";
        echo "<td>{$this->id_utilisateur}</td>";
        echo "<td>{$this->categorie}</td>";
        echo "<td>{$this->statue}</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters and Setters
    public function getIdPost(): ?int {
        return $this->id_post;
    }

    public function setIdPost(?int $id_post): void {
        $this->id_post = $id_post;
    }

    public function getTitre(): ?string {
        return $this->titre;
    }

    public function setTitre(?string $titre): void {
        $this->titre = $titre;
    }

    public function getContenu(): ?string {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): void {
        $this->contenu = $contenu;
    }

    public function getDateCreation(): ?DateTime {
        return $this->date_creation;
    }

    public function setDateCreation(?DateTime $date_creation): void {
        $this->date_creation = $date_creation;
    }

    public function getIdUtilisateur(): ?int {
        return $this->id_utilisateur;
    }

    public function setIdUtilisateur(?int $id_utilisateur): void {
        $this->id_utilisateur = $id_utilisateur;
    }

    public function getCategorie(): ?string {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): void {
        $this->categorie = $categorie;
    }

    public function getStatue(): ?string {
        return $this->statue;
    }

    public function setStatue(?string $statue): void {
        $this->statue = $statue;
    }
}
}
?>
