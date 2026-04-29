<?php
/**
 * FoodSave — Model : Collecte
 * Getters / Setters + accès PDO
 */

require_once __DIR__ . '/../config/Database.php';

class Collecte {

    /* ── Propriétés ── */
    private ?int    $id             = null;
    private string  $titre          = '';
    private string  $description    = '';
    private string  $dateCollecte   = '';
    private string  $lieu           = '';
    private float   $quantiteTotale = 0.0;
    private string  $unite          = 'kg';
    private string  $statut         = 'planifiee';
    private ?string $createdAt      = null;

    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /* ── Getters ── */
    public function getId():             ?int    { return $this->id; }
    public function getTitre():          string  { return $this->titre; }
    public function getDescription():   string  { return $this->description; }
    public function getDateCollecte():  string  { return $this->dateCollecte; }
    public function getLieu():          string  { return $this->lieu; }
    public function getQuantiteTotale():float   { return $this->quantiteTotale; }
    public function getUnite():         string  { return $this->unite; }
    public function getStatut():        string  { return $this->statut; }
    public function getCreatedAt():     ?string { return $this->createdAt; }

    /* ── Setters ── */
    public function setId(int $v):             self { $this->id             = $v;       return $this; }
    public function setTitre(string $v):       self { $this->titre          = trim($v); return $this; }
    public function setDescription(string $v): self { $this->description    = trim($v); return $this; }
    public function setDateCollecte(string $v):self { $this->dateCollecte   = $v;       return $this; }
    public function setLieu(string $v):        self { $this->lieu           = trim($v); return $this; }
    public function setQuantiteTotale(float $v):self{ $this->quantiteTotale = $v;       return $this; }
    public function setUnite(string $v):       self { $this->unite          = trim($v); return $this; }
    public function setStatut(string $v):      self {
        $allowed = ['planifiee','en_cours','terminee','annulee'];
        $this->statut = in_array($v, $allowed, true) ? $v : 'planifiee';
        return $this;
    }

    /* ── Hydratation ── */
    public function hydrate(array $row): self {
        if (isset($row['id']))              $this->id             = (int)   $row['id'];
        if (isset($row['titre']))           $this->titre          = (string)$row['titre'];
        if (isset($row['description']))     $this->description    = (string)$row['description'];
        if (isset($row['date_collecte']))   $this->dateCollecte   = (string)$row['date_collecte'];
        if (isset($row['lieu']))            $this->lieu           = (string)$row['lieu'];
        if (isset($row['quantite_totale'])) $this->quantiteTotale = (float) $row['quantite_totale'];
        if (isset($row['unite']))           $this->unite          = (string)$row['unite'];
        if (isset($row['statut']))          $this->setStatut((string)$row['statut']);
        if (isset($row['created_at']))      $this->createdAt      = (string)$row['created_at'];
        return $this;
    }

    /* ── toArray() ── */
    public function toArray(): array {
        return [
            'id'             => $this->id,
            'titre'          => $this->titre,
            'description'    => $this->description,
            'date_collecte'  => $this->dateCollecte,
            'lieu'           => $this->lieu,
            'quantite_totale'=> $this->quantiteTotale,
            'unite'          => $this->unite,
            'statut'         => $this->statut,
            'created_at'     => $this->createdAt,
        ];
    }

    /* ══════════════════════════════
       REQUÊTES
    ══════════════════════════════ */

    public function findAll(int $limit = 500, int $offset = 0): array {
        $sql = "SELECT * FROM collectes ORDER BY date_collecte DESC LIMIT :lim OFFSET :off";
        $st  = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit,  PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map(fn($r) => (new self())->hydrate($r)->toArray(), $st->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?self {
        $st = $this->pdo->prepare("SELECT * FROM collectes WHERE id=:id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? (new self())->hydrate($row) : null;
    }

    public function save(): bool {
        if ($this->id) {
            $sql = "UPDATE collectes SET titre=:ti,description=:de,date_collecte=:dc,
                    lieu=:li,quantite_totale=:qt,unite=:un,statut=:st WHERE id=:id";
        } else {
            $sql = "INSERT INTO collectes (titre,description,date_collecte,lieu,quantite_totale,unite,statut)
                    VALUES (:ti,:de,:dc,:li,:qt,:un,:st)";
        }
        $st = $this->pdo->prepare($sql);
        $ok = $st->execute([
            ':ti' => $this->titre,       ':de' => $this->description,
            ':dc' => $this->dateCollecte,':li' => $this->lieu,
            ':qt' => $this->quantiteTotale, ':un' => $this->unite,
            ':st' => $this->statut,
            ...$this->id ? [':id' => $this->id] : [],
        ]);
        if ($ok && !$this->id) $this->id = (int)$this->pdo->lastInsertId();
        return $ok;
    }

    public function delete(): bool {
        if (!$this->id) return false;
        return $this->pdo->prepare("DELETE FROM collectes WHERE id=:id")->execute([':id' => $this->id]);
    }

    public function addDechet(int $dechetId): bool {
        return $this->pdo->prepare(
            "INSERT IGNORE INTO collecte_dechets (collecte_id,dechet_id) VALUES (:c,:d)"
        )->execute([':c' => $this->id, ':d' => $dechetId]);
    }

    public function removeDechet(int $dechetId): bool {
        return $this->pdo->prepare(
            "DELETE FROM collecte_dechets WHERE collecte_id=:c AND dechet_id=:d"
        )->execute([':c' => $this->id, ':d' => $dechetId]);
    }

    public function getStats(): array {
        return $this->pdo->query(
            "SELECT COUNT(*) total,SUM(quantite_totale) total_kg,
             SUM(statut='terminee') terminees,SUM(statut='en_cours') en_cours,
             SUM(statut='planifiee') planifiees,SUM(statut='annulee') annulees
             FROM collectes"
        )->fetch(PDO::FETCH_ASSOC);
    }
}
