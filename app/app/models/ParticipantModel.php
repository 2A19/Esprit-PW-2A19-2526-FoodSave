<?php

class Participant
{
    private ?int $id;
    private ?string $nom;
    private ?string $prenom;
    private ?string $email;
    private ?string $telephone;
    private ?int $evenementId;
    private ?string $statut;
    private ?DateTime $dateInscription;

    public function __construct(
        ?int $id,
        ?string $nom,
        ?string $prenom,
        ?string $email,
        ?string $telephone,
        ?int $evenementId,
        ?string $statut,
        ?DateTime $dateInscription
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->evenementId = $evenementId;
        $this->statut = $statut;
        $this->dateInscription = $dateInscription;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): void { $this->nom = $nom; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $prenom): void { $this->prenom = $prenom; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): void { $this->email = $email; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): void { $this->telephone = $telephone; }

    public function getEvenementId(): ?int { return $this->evenementId; }
    public function setEvenementId(?int $evenementId): void { $this->evenementId = $evenementId; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): void { $this->statut = $statut; }

    public function getDateInscription(): ?DateTime { return $this->dateInscription; }
    public function setDateInscription(?DateTime $dateInscription): void { $this->dateInscription = $dateInscription; }
}
?>
