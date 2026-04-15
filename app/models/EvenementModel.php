<?php

class Evenement
{
    private ?int $id;
    private ?string $titre;
    private ?string $categorie;
    private ?string $statut;
    private ?DateTime $dateEvent;
    private ?string $heure;
    private ?string $lieu;
    private ?string $organisateur;
    private ?int $capacite;
    private ?string $description;

    public function __construct(
        ?int $id,
        ?string $titre,
        ?string $categorie,
        ?string $statut,
        ?DateTime $dateEvent,
        ?string $heure,
        ?string $lieu,
        ?string $organisateur,
        ?int $capacite,
        ?string $description
    ) {
        $this->id = $id;
        $this->titre = $titre;
        $this->categorie = $categorie;
        $this->statut = $statut;
        $this->dateEvent = $dateEvent;
        $this->heure = $heure;
        $this->lieu = $lieu;
        $this->organisateur = $organisateur;
        $this->capacite = $capacite;
        $this->description = $description;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(?string $titre): void { $this->titre = $titre; }

    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(?string $categorie): void { $this->categorie = $categorie; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): void { $this->statut = $statut; }

    public function getDateEvent(): ?DateTime { return $this->dateEvent; }
    public function setDateEvent(?DateTime $dateEvent): void { $this->dateEvent = $dateEvent; }

    public function getHeure(): ?string { return $this->heure; }
    public function setHeure(?string $heure): void { $this->heure = $heure; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $lieu): void { $this->lieu = $lieu; }

    public function getOrganisateur(): ?string { return $this->organisateur; }
    public function setOrganisateur(?string $organisateur): void { $this->organisateur = $organisateur; }

    public function getCapacite(): ?int { return $this->capacite; }
    public function setCapacite(?int $capacite): void { $this->capacite = $capacite; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }
}
?>
