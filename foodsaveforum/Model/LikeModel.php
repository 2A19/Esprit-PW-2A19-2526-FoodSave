<?php
if (!class_exists('LikeModel')) {
    class LikeModel {
        private ?int $id_like;
        private ?int $id_post;
        private ?int $id_utilisateur;
        private ?string $type_reaction;
        private ?DateTime $date_creation;

        public function __construct(?int $id_like, ?int $id_post, ?int $id_utilisateur, ?string $type_reaction, ?DateTime $date_creation) {
            $this->id_like = $id_like;
            $this->id_post = $id_post;
            $this->id_utilisateur = $id_utilisateur;
            $this->type_reaction = $type_reaction;
            $this->date_creation = $date_creation;
        }

        // Getters and Setters
        public function getIdLike(): ?int {
            return $this->id_like;
        }

        public function setIdLike(?int $id_like): void {
            $this->id_like = $id_like;
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

        public function getTypeReaction(): ?string {
            return $this->type_reaction;
        }

        public function setTypeReaction(?string $type_reaction): void {
            $this->type_reaction = $type_reaction;
        }

        public function getDateCreation(): ?DateTime {
            return $this->date_creation;
        }

        public function setDateCreation(?DateTime $date_creation): void {
            $this->date_creation = $date_creation;
        }
    }
}
?>
