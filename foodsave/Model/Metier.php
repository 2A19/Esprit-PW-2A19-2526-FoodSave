<?php
/**
 * FoodSave — Model : Metier
 */

require_once __DIR__ . '/../config/Database.php';

class Metier {

    /* ══════════════════════════════
       PROPRIÉTÉS
    ══════════════════════════════ */

    private ?int    $id          = null;
    private string  $nom         = '';
    private string  $description = '';
    private string  $icone       = '💼';
    private bool    $actif       = true;
    private ?string $createdAt   = null;

    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /* ══════════════════════════════
       GETTERS
    ══════════════════════════════ */

    public function getId():          ?int    { return $this->id; }
    public function getNom():         string  { return $this->nom; }
    public function getDescription(): string  { return $this->description; }
    public function getIcone():       string  { return $this->icone; }
    public function isActif():        bool    { return $this->actif; }
    public function getCreatedAt():   ?string { return $this->createdAt; }

    /* ══════════════════════════════
       SETTERS
    ══════════════════════════════ */

    public function setId(int $id):            self { $this->id          = $id;      return $this; }
    public function setNom(string $v):         self { $this->nom         = trim($v); return $this; }
    public function setDescription(string $v): self { $this->description = trim($v); return $this; }
    public function setIcone(string $v):       self { $this->icone       = trim($v); return $this; }
    public function setActif(bool $v):         self { $this->actif       = $v;       return $this; }

    /* ══════════════════════════════
       HYDRATATION & SÉRIALISATION
    ══════════════════════════════ */

    public function hydrate(array $row): self {
        if (isset($row['id']))          $this->id          = (int)   $row['id'];
        if (isset($row['nom']))         $this->nom         = (string)$row['nom'];
        if (isset($row['description'])) $this->description = (string)$row['description'];
        if (isset($row['icone']))       $this->icone       = (string)$row['icone'];
        if (isset($row['actif']))       $this->actif       = (bool)  $row['actif'];
        if (isset($row['created_at']))  $this->createdAt   = (string)$row['created_at'];
        return $this;
    }

    public function toArray(): array {
        return [
            'id'          => $this->id,
            'nom'         => $this->nom,
            'description' => $this->description,
            'icone'       => $this->icone,
            'actif'       => $this->actif,
            'created_at'  => $this->createdAt,
        ];
    }

    /* ══════════════════════════════
       REQUÊTES SQL
    ══════════════════════════════ */

    public function findAll(bool $actifOnly = false): array {
        $where = $actifOnly ? 'WHERE actif = 1' : '';
        $sql   = "SELECT * FROM metiers $where ORDER BY nom ASC";
        return array_map(
            fn($r) => (new self())->hydrate($r),
            $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function findById(int $id): ?self {
        $st = $this->pdo->prepare("SELECT * FROM metiers WHERE id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? (new self())->hydrate($row) : null;
    }

    public function save(): bool {
        if ($this->id) {
            $sql = "UPDATE metiers SET nom=:n, description=:d, icone=:i, actif=:a WHERE id=:id";
        } else {
            $sql = "INSERT INTO metiers (nom, description, icone, actif) VALUES (:n, :d, :i, :a)";
        }
        $st = $this->pdo->prepare($sql);
        $ok = $st->execute([
            ':n'  => $this->nom,
            ':d'  => $this->description,
            ':i'  => $this->icone,
            ':a'  => (int)$this->actif,
            ...$this->id ? [':id' => $this->id] : [],
        ]);
        if ($ok && !$this->id) $this->id = (int)$this->pdo->lastInsertId();
        return $ok;
    }

    public function delete(): bool {
        if (!$this->id) return false;
        return $this->pdo->prepare("DELETE FROM metiers WHERE id=:id")->execute([':id' => $this->id]);
    }

    public function getStats(): array {
        return $this->pdo->query(
            "SELECT COUNT(*) total, SUM(actif) total_actifs FROM metiers"
        )->fetch(PDO::FETCH_ASSOC);
    }
}
