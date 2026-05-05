<?php
/**
 * FoodSave — API : categories.php
 */
declare(strict_types=1);
require_once __DIR__ . '/../models/Category.php';
header('Content-Type: application/json; charset=utf-8');

function respond(int $s, array $p): void { http_response_code($s); echo json_encode($p, JSON_UNESCAPED_UNICODE); exit; }
function body(): array { $r=json_decode(file_get_contents('php://input')?:'{}',true); return is_array($r)?$r:respond(400,['success'=>false,'message'=>'JSON invalide']); }
function c(string $v): string { return htmlspecialchars(trim($v),ENT_QUOTES,'UTF-8'); }

$m = new Category();
try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['id'])) { $cat=$m->findById((int)$_GET['id']); $cat?respond(200,['success'=>true,'data'=>$cat->toArray()]):respond(404,['success'=>false,'message'=>'Introuvable']); }
            if (isset($_GET['simple'])) respond(200,['success'=>true,'data'=>$m->findAllSimple()]);
            if (isset($_GET['stats']))  respond(200,['success'=>true,'data'=>$m->getStats()]);
            respond(200,['success'=>true,'data'=>$m->findAll()]);

        case 'POST':
            $b=body();
            if(empty($b['nom'])) respond(422,['success'=>false,'message'=>'Nom obligatoire.']);
            $cat=new Category();
            $cat->setNom(c($b['nom']))->setDescription(c($b['description']??''))
                ->setCouleur(c($b['couleur']??'#4caf50'))->setIcone(c($b['icone']??'tag'));
            $cat->save()?respond(201,['success'=>true,'message'=>'Catégorie créée.']):respond(500,['success'=>false,'message'=>'Erreur.']);

        case 'PUT':
            $b=body(); $id=(int)($b['id']??0);
            if(!$id) respond(422,['success'=>false,'message'=>'ID manquant.']);
            $cat=$m->findById($id); if(!$cat) respond(404,['success'=>false,'message'=>'Introuvable.']);
            if(empty($b['nom'])) respond(422,['success'=>false,'message'=>'Nom obligatoire.']);
            $cat->setNom(c($b['nom']))->setDescription(c($b['description']??''))
                ->setCouleur(c($b['couleur']??'#4caf50'))->setIcone(c($b['icone']??'tag'));
            $cat->save()?respond(200,['success'=>true,'message'=>'Catégorie modifiée.']):respond(500,['success'=>false,'message'=>'Erreur.']);

        case 'DELETE':
            $b=body(); $id=(int)($b['id']??$_GET['id']??0);
            if(!$id) respond(422,['success'=>false,'message'=>'ID manquant.']);
            $cat=$m->findById($id); if(!$cat) respond(404,['success'=>false,'message'=>'Introuvable.']);
            $cat->delete()?respond(200,['success'=>true,'message'=>'Catégorie supprimée.']):respond(500,['success'=>false,'message'=>'Erreur.']);

        default: respond(405,['success'=>false,'message'=>'Méthode non autorisée.']);
    }
} catch(Throwable $e) { respond(500,['success'=>false,'message'=>'Erreur serveur: '.$e->getMessage()]); }
