<?php
if (!class_exists('CommentaireModel')) {
class CommentaireModel {
    private ?int $id_commentaire;
    private ?string $contenu;
    private ?DateTime $date_publication;
    private ?int $id_post;
    private ?int $id_utilisateur;
    private ?string $statue;

    // Constructor
    public function __construct(?int $id_commentaire, ?string $contenu, ?DateTime $date_publication, ?int $id_post, ?int $id_utilisateur, ?string $statue) {
        $this->id_commentaire = $id_commentaire;
        $this->contenu = $contenu;
        $this->date_publication = $date_publication;
        $this->id_post = $id_post;
        $this->id_utilisateur = $id_utilisateur;
        $this->statue = $statue;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID Commentaire</th><th>Contenu</th><th>Date Publication</th><th>ID Post</th><th>ID Utilisateur</th><th>Statue</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id_commentaire}</td>";
        echo "<td>{$this->contenu}</td>";
        echo "<td>" . ($this->date_publication ? $this->date_publication->format('Y-m-d H:i:s') : '') . "</td>";
        echo "<td>{$this->id_post}</td>";
        echo "<td>{$this->id_utilisateur}</td>";
        echo "<td>{$this->statue}</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters and Setters
    public function getIdCommentaire(): ?int {
        return $this->id_commentaire;
    }

    public function setIdCommentaire(?int $id_commentaire): void {
        $this->id_commentaire = $id_commentaire;
    }

    public function getContenu(): ?string {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): void {
        $this->contenu = $contenu;
    }

    public function getDatePublication(): ?DateTime {
        return $this->date_publication;
    }

    public function setDatePublication(?DateTime $date_publication): void {
        $this->date_publication = $date_publication;
    }

    public function getIdPost(): ?int {
        return $this->id_post;
    }

    public function setIdPost(?int $id_post): void {
        $this->id_post = $id_post;
    }

    public function getIdUtilisateur(): ?int {
        return $this->id_utilisateur;
    }

    public function setIdUtilisateur(?int $id_utilisateur): void {
        $this->id_utilisateur = $id_utilisateur;
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
