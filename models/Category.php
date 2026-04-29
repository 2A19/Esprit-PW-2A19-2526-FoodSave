<?php
/**
 * FoodSave — Model : Category
 * Getters / Setters + accès PDO
 */

require_once __DIR__ . '/../config/Database.php';

class Category {

    /* ── Propriétés ── */
    private ?int    $id          = null;
    private string  $nom         = '';
    private string  $description = '';
    private string  $couleur     = '#4caf50';
    private string  $icone       = 'tag';
    private ?string $createdAt   = null;

    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /* ── Getters ── */
    public function getId():          ?int    { return $this->id; }
    public function getNom():         string  { return $this->nom; }
    public function getDescription(): string  { return $this->description; }
    public function getCouleur():     string  { return $this->couleur; }
    public function getIcone():       string  { return $this->icone; }
    public function getCreatedAt():   ?string { return $this->createdAt; }

    /* ── Setters ── */
    public function setId(int $id):            self { $this->id          = $id;     return $this; }
    public function setNom(string $v):         self { $this->nom         = trim($v); return $this; }
    public function setDescription(string $v): self { $this->description = trim($v); return $this; }
    public function setCouleur(string $v):     self { $this->couleur     = trim($v); return $this; }
    public function setIcone(string $v):       self { $this->icone       = trim($v); return $this; }

    /* ── Hydratation ── */
    public function hydrate(array $row): self {
        if (isset($row['id']))           $this->id          = (int)   $row['id'];
        if (isset($row['nom']))          $this->nom         = (string)$row['nom'];
        if (isset($row['description'])) $this->description = (string)$row['description'];
        if (isset($row['couleur']))      $this->couleur     = (string)$row['couleur'];
        if (isset($row['icone']))        $this->icone       = (string)$row['icone'];
        if (isset($row['created_at']))   $this->createdAt   = (string)$row['created_at'];
        return $this;
    }

    /* ── toArray() ── */
    public function toArray(): array {
        return [
            'id'          => $this->id,
            'nom'         => $this->nom,
            'description' => $this->description,
            'couleur'     => $this->couleur,
            'icone'       => $this->icone,
            'created_at'  => $this->createdAt,
        ];
    }

    /* ══════════════════════════════
       REQUÊTES
    ══════════════════════════════ */

    public function findAll(): array {
        $sql = "SELECT c.*, COUNT(d.id) AS nombre_dechets
                FROM categories c
                LEFT JOIN dechets d ON d.categorie_id = c.id
                GROUP BY c.id ORDER BY c.nom ASC";
        return array_map(
            fn($r) => (new self())->hydrate($r)->toArray() + ['nombre_dechets' => $r['nombre_dechets']],
            $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function findAllSimple(): array {
        return $this->pdo->query("SELECT id,nom,couleur,icone FROM categories ORDER BY nom ASC")
                         ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?self {
        $st = $this->pdo->prepare("SELECT * FROM categories WHERE id=:id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? (new self())->hydrate($row) : null;
    }

    public function existsById(int $id): bool {
        $st = $this->pdo->prepare("SELECT 1 FROM categories WHERE id=:id LIMIT 1");
        $st->execute([':id' => $id]);
        return (bool)$st->fetchColumn();
    }

    public function save(): bool {
        if ($this->id) {
            $sql = "UPDATE categories SET nom=:n,description=:d,couleur=:c,icone=:i WHERE id=:id";
        } else {
            $sql = "INSERT INTO categories (nom,description,couleur,icone) VALUES (:n,:d,:c,:i)";
        }
        $st = $this->pdo->prepare($sql);
        $ok = $st->execute([
            ':n'  => $this->nom,
            ':d'  => $this->description,
            ':c'  => $this->couleur,
            ':i'  => $this->icone,
            ...$this->id ? [':id' => $this->id] : [],
        ]);
        if ($ok && !$this->id) $this->id = (int)$this->pdo->lastInsertId();
        return $ok;
    }

    public function delete(): bool {
        if (!$this->id) return false;
        return $this->pdo->prepare("DELETE FROM categories WHERE id=:id")->execute([':id' => $this->id]);
    }

    public function getStats(): array {
        return $this->pdo->query(
            "SELECT c.nom, c.couleur, COUNT(d.id) total_dechets, SUM(d.quantite) total_kg
             FROM categories c LEFT JOIN dechets d ON d.categorie_id=c.id
             GROUP BY c.id ORDER BY total_kg DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
