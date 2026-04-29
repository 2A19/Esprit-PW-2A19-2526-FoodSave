<?php
/**
 * FoodSave — API : dechets.php
 */
declare(strict_types=1);
require_once __DIR__ . '/../models/Dechet.php';
header('Content-Type: application/json; charset=utf-8');

function respond(int $s, array $p): void { http_response_code($s); echo json_encode($p, JSON_UNESCAPED_UNICODE); exit; }
function body(): array { $r=json_decode(file_get_contents('php://input')?:'{}',true); return is_array($r)?$r:respond(400,['success'=>false,'message'=>'JSON invalide']); }
function c(string $v): string { return htmlspecialchars(trim($v),ENT_QUOTES,'UTF-8'); }

$m = new Dechet();
try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['id'])) { $d=$m->findById((int)$_GET['id']); $d?respond(200,['success'=>true,'data'=>$d->toArray()]):respond(404,['success'=>false,'message'=>'Introuvable']); }
            if (isset($_GET['stats'])) respond(200,['success'=>true,'data'=>$m->getStats()]);
            respond(200,['success'=>true,'data'=>$m->findAll()]);

        case 'POST':
            $b=body();
            foreach(['type_aliment','quantite','unite','date_dechet','raison'] as $f)
                if(empty($b[$f])) respond(422,['success'=>false,'message'=>"Champ $f obligatoire."]);
            if((float)$b['quantite']<=0) respond(422,['success'=>false,'message'=>'Quantité doit être > 0.']);
            $d=new Dechet();
            $d->setTypeAliment(c($b['type_aliment']))->setQuantite((float)$b['quantite'])
              ->setUnite(c($b['unite']))->setDateDechet(c($b['date_dechet']))
              ->setRaison(c($b['raison']))->setNotes(c($b['notes']??''))
              ->setCategorieId(!empty($b['categorie_id'])?(int)$b['categorie_id']:null);
            $d->save()?respond(201,['success'=>true,'message'=>'Déchet créé.']):respond(500,['success'=>false,'message'=>'Erreur.']);

        case 'PUT':
            $b=body(); $id=(int)($b['id']??0);
            if(!$id) respond(422,['success'=>false,'message'=>'ID manquant.']);
            $d=$m->findById($id); if(!$d) respond(404,['success'=>false,'message'=>'Introuvable.']);
            $d->setTypeAliment(c($b['type_aliment']??$d->getTypeAliment()))
              ->setQuantite((float)($b['quantite']??$d->getQuantite()))
              ->setUnite(c($b['unite']??$d->getUnite()))
              ->setDateDechet(c($b['date_dechet']??$d->getDateDechet()))
              ->setRaison(c($b['raison']??$d->getRaison()))
              ->setNotes(c($b['notes']??$d->getNotes()))
              ->setCategorieId(isset($b['categorie_id'])&&$b['categorie_id']?(int)$b['categorie_id']:null);
            $d->save()?respond(200,['success'=>true,'message'=>'Déchet modifié.']):respond(500,['success'=>false,'message'=>'Erreur.']);

        case 'DELETE':
            $b=body(); $id=(int)($b['id']??$_GET['id']??0);
            if(!$id) respond(422,['success'=>false,'message'=>'ID manquant.']);
            $d=$m->findById($id); if(!$d) respond(404,['success'=>false,'message'=>'Introuvable.']);
            $d->delete()?respond(200,['success'=>true,'message'=>'Déchet supprimé.']):respond(500,['success'=>false,'message'=>'Erreur.']);

        default: respond(405,['success'=>false,'message'=>'Méthode non autorisée.']);
    }
} catch(Throwable $e) { respond(500,['success'=>false,'message'=>'Erreur serveur: '.$e->getMessage()]); }
