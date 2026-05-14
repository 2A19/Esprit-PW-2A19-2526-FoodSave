<?php
/**
 * FoodSave — API : metiers.php
 */
declare(strict_types=1);
require_once __DIR__ . '/../Model/Metier.php';
header('Content-Type: application/json; charset=utf-8');

function respond(int $s, array $p): void { http_response_code($s); echo json_encode($p, JSON_UNESCAPED_UNICODE); exit; }
function body(): array { $r=json_decode(file_get_contents('php://input')?:'{}',true); return is_array($r)?$r:respond(400,['success'=>false,'message'=>'JSON invalide']); }
function c(string $v): string { return htmlspecialchars(trim($v),ENT_QUOTES,'UTF-8'); }

$m = new Metier();
try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['id'])) {
                $met = $m->findById((int)$_GET['id']);
                $met ? respond(200,['success'=>true,'data'=>$met->toArray()]) : respond(404,['success'=>false,'message'=>'Introuvable']);
            }
            if (isset($_GET['stats']))   respond(200,['success'=>true,'data'=>$m->getStats()]);
            if (isset($_GET['actifs']))  respond(200,['success'=>true,'data'=>array_map(fn($o)=>$o->toArray(), $m->findAll(true))]);
            respond(200,['success'=>true,'data'=>array_map(fn($o)=>$o->toArray(), $m->findAll())]);

        case 'POST':
            $b = body();
            if (empty($b['nom'])) respond(422,['success'=>false,'message'=>'Nom obligatoire.']);
            $met = new Metier();
            $met->setNom(c($b['nom']))
                ->setDescription(c($b['description'] ?? ''))
                ->setIcone(c($b['icone'] ?? '💼'))
                ->setActif((bool)($b['actif'] ?? true));
            $met->save()
                ? respond(201,['success'=>true,'message'=>'Métier créé.','id'=>$met->getId()])
                : respond(500,['success'=>false,'message'=>'Erreur serveur.']);

        case 'PUT':
            $b = body(); $id = (int)($b['id'] ?? 0);
            if (!$id) respond(422,['success'=>false,'message'=>'ID manquant.']);
            $met = $m->findById($id);
            if (!$met) respond(404,['success'=>false,'message'=>'Introuvable.']);
            if (empty($b['nom'])) respond(422,['success'=>false,'message'=>'Nom obligatoire.']);
            $met->setNom(c($b['nom']))
                ->setDescription(c($b['description'] ?? ''))
                ->setIcone(c($b['icone'] ?? '💼'))
                ->setActif((bool)($b['actif'] ?? true));
            $met->save()
                ? respond(200,['success'=>true,'message'=>'Métier modifié.'])
                : respond(500,['success'=>false,'message'=>'Erreur serveur.']);

        case 'DELETE':
            $b = body(); $id = (int)($b['id'] ?? $_GET['id'] ?? 0);
            if (!$id) respond(422,['success'=>false,'message'=>'ID manquant.']);
            $met = $m->findById($id);
            if (!$met) respond(404,['success'=>false,'message'=>'Introuvable.']);
            $met->delete()
                ? respond(200,['success'=>true,'message'=>'Métier supprimé.'])
                : respond(500,['success'=>false,'message'=>'Erreur serveur.']);

        default: respond(405,['success'=>false,'message'=>'Méthode non autorisée.']);
    }
} catch(Throwable $e) { respond(500,['success'=>false,'message'=>'Erreur serveur: '.$e->getMessage()]); }
