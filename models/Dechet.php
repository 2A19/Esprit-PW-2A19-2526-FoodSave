<?php
/**
 * FoodSave — Model : Dechet
 * Getters / Setters + accès PDO
 */

require_once __DIR__ . '/../config/Database.php';

class Dechet {

    /* ── Propriétés ── */
    private ?int    $id           = null;
    private string  $typeAliment  = '';
    private float   $quantite     = 0.0;
    private string  $unite        = '';
    private string  $dateDechet   = '';
    private string  $raison       = '';
    private string  $notes        = '';
    private ?int    $categorieId  = null;
    private ?string $createdAt    = null;

    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /* ── Getters ── */
    public function getId():          ?int    { return $this->id; }
    public function getTypeAliment(): string  { return $this->typeAliment; }
    public function getQuantite():    float   { return $this->quantite; }
    public function getUnite():       string  { return $this->unite; }
    public function getDateDechet():  string  { return $this->dateDechet; }
    public function getRaison():      string  { return $this->raison; }
    public function getNotes():       string  { return $this->notes; }
    public function getCategorieId(): ?int    { return $this->categorieId; }
    public function getCreatedAt():   ?string { return $this->createdAt; }

    /* ── Setters ── */
    public function setId(int $id):                   self { $this->id          = $id;          return $this; }
    public function setTypeAliment(string $v):        self { $this->typeAliment = trim($v);      return $this; }
    public function setQuantite(float $v):            self { $this->quantite    = $v;            return $this; }
    public function setUnite(string $v):              self { $this->unite       = trim($v);      return $this; }
    public function setDateDechet(string $v):         self { $this->dateDechet  = $v;            return $this; }
    public function setRaison(string $v):             self { $this->raison      = trim($v);      return $this; }
    public function setNotes(string $v):              self { $this->notes       = trim($v);      return $this; }
    public function setCategorieId(?int $v):          self { $this->categorieId = $v;            return $this; }

    /* ── Hydratation depuis tableau PDO ── */
    public function hydrate(array $row): self {
        if (isset($row['id']))           $this->id          = (int)   $row['id'];
        if (isset($row['type_aliment'])) $this->typeAliment = (string)$row['type_aliment'];
        if (isset($row['quantite']))     $this->quantite    = (float) $row['quantite'];
        if (isset($row['unite']))        $this->unite       = (string)$row['unite'];
        if (isset($row['date_dechet'])) $this->dateDechet  = (string)$row['date_dechet'];
        if (isset($row['raison']))       $this->raison      = (string)$row['raison'];
        if (isset($row['notes']))        $this->notes       = (string)$row['notes'];
        if (array_key_exists('categorie_id', $row))
            $this->categorieId = $row['categorie_id'] !== null ? (int)$row['categorie_id'] : null;
        if (isset($row['created_at']))   $this->createdAt   = (string)$row['created_at'];
        return $this;
    }

    /* ── toArray() — pour l'API ── */
    public function toArray(): array {
        return [
            'id'           => $this->id,
            'type_aliment' => $this->typeAliment,
            'quantite'     => $this->quantite,
            'unite'        => $this->unite,
            'date_dechet'  => $this->dateDechet,
            'raison'       => $this->raison,
            'notes'        => $this->notes,
            'categorie_id' => $this->categorieId,
            'created_at'   => $this->createdAt,
        ];
    }

    /* ══════════════════════════════
       REQUÊTES (logique SQL propre)
    ══════════════════════════════ */

    public function findAll(int $limit = 500, int $offset = 0): array {
        $sql = "SELECT d.*, c.nom AS categorie_nom, c.couleur AS categorie_couleur
                FROM dechets d
                LEFT JOIN categories c ON c.id = d.categorie_id
                ORDER BY d.date_dechet DESC
                LIMIT :lim OFFSET :off";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit,  PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map(fn($r) => (new self())->hydrate($r)->toArray() + $r, $st->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?self {
        $st = $this->pdo->prepare(
            "SELECT d.*, c.nom AS categorie_nom FROM dechets d
             LEFT JOIN categories c ON c.id = d.categorie_id
             WHERE d.id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? (new self())->hydrate($row) : null;
    }

    public function save(): bool {
        if ($this->id) {
            $sql = "UPDATE dechets SET type_aliment=:t,quantite=:q,unite=:u,
                    date_dechet=:d,raison=:r,notes=:n,categorie_id=:c WHERE id=:id";
        } else {
            $sql = "INSERT INTO dechets (type_aliment,quantite,unite,date_dechet,raison,notes,categorie_id)
                    VALUES (:t,:q,:u,:d,:r,:n,:c)";
        }
        $st = $this->pdo->prepare($sql);
        $ok = $st->execute([
            ':t'  => $this->typeAliment,
            ':q'  => $this->quantite,
            ':u'  => $this->unite,
            ':d'  => $this->dateDechet,
            ':r'  => $this->raison,
            ':n'  => $this->notes,
            ':c'  => $this->categorieId,
            ...$this->id ? [':id' => $this->id] : [],
        ]);
        if ($ok && !$this->id) $this->id = (int)$this->pdo->lastInsertId();
        return $ok;
    }

    public function delete(): bool {
        if (!$this->id) return false;
        return $this->pdo->prepare("DELETE FROM dechets WHERE id=:id")->execute([':id' => $this->id]);
    }

    public function getStats(): array {
        return $this->pdo->query(
            "SELECT COUNT(*) total, SUM(quantite) total_kg,
                    AVG(quantite) avg_kg, MAX(date_dechet) last_date FROM dechets"
        )->fetch(PDO::FETCH_ASSOC);
    }
}
