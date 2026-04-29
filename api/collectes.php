<?php
/**
 * FoodSave — API : collectes.php
 */
declare(strict_types=1);
require_once __DIR__ . '/../models/Collecte.php';
header('Content-Type: application/json; charset=utf-8');

function respond(int $s, array $p): void { http_response_code($s); echo json_encode($p, JSON_UNESCAPED_UNICODE); exit; }
function body(): array { $r=json_decode(file_get_contents('php://input')?:'{}',true); return is_array($r)?$r:respond(400,['success'=>false,'message'=>'JSON invalide']); }
function c(string $v): string { return htmlspecialchars(trim($v),ENT_QUOTES,'UTF-8'); }

$m = new Collecte();
try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['stats'])) respond(200,['success'=>true,'data'=>$m->getStats()]);
            if (isset($_GET['id'])) { $col=$m->findById((int)$_GET['id']); $col?respond(200,['success'=>true,'data'=>$col->toArray()]):respond(404,['success'=>false,'message'=>'Introuvable']); }
            respond(200,['success'=>true,'data'=>$m->findAll()]);

        case 'POST':
            $b=body();
            $action=trim($b['action']??'');
            if($action==='add_dechet'){
                $col=$m->findById((int)($b['collecte_id']??0)); if(!$col) respond(404,['success'=>false,'message'=>'Collecte introuvable.']);
                $ok=$col->addDechet((int)($b['dechet_id']??0)); respond($ok?200:500,['success'=>$ok,'message'=>$ok?'Déchet rattaché.':'Erreur.']);
            }
            if($action==='remove_dechet'){
                $col=$m->findById((int)($b['collecte_id']??0)); if(!$col) respond(404,['success'=>false,'message'=>'Collecte introuvable.']);
                $ok=$col->removeDechet((int)($b['dechet_id']??0)); respond($ok?200:500,['success'=>$ok,'message'=>$ok?'Déchet retiré.':'Erreur.']);
            }
            foreach(['titre','date_collecte','lieu'] as $f)
                if(empty($b[$f])) respond(422,['success'=>false,'message'=>"Champ $f obligatoire."]);
            $col=new Collecte();
            $col->setTitre(c($b['titre']))->setDescription(c($b['description']??''))
                ->setDateCollecte(c($b['date_collecte']))->setLieu(c($b['lieu']))
                ->setQuantiteTotale((float)($b['quantite_totale']??0))
                ->setUnite(c($b['unite']??'kg'))->setStatut(c($b['statut']??'planifiee'));
            $col->save()?respond(201,['success'=>true,'message'=>'Collecte créée.','id'=>$col->getId()]):respond(500,['success'=>false,'message'=>'Erreur.']);

        case 'PUT':
            $b=body(); $id=(int)($b['id']??0);
            if(!$id) respond(422,['success'=>false,'message'=>'ID manquant.']);
            $col=$m->findById($id); if(!$col) respond(404,['success'=>false,'message'=>'Introuvable.']);
            $col->setTitre(c($b['titre']??$col->getTitre()))
                ->setDescription(c($b['description']??$col->getDescription()))
                ->setDateCollecte(c($b['date_collecte']??$col->getDateCollecte()))
                ->setLieu(c($b['lieu']??$col->getLieu()))
                ->setQuantiteTotale((float)($b['quantite_totale']??$col->getQuantiteTotale()))
                ->setUnite(c($b['unite']??$col->getUnite()))
                ->setStatut(c($b['statut']??$col->getStatut()));
            $col->save()?respond(200,['success'=>true,'message'=>'Collecte modifiée.']):respond(500,['success'=>false,'message'=>'Erreur.']);

        case 'DELETE':
            $b=body(); $id=(int)($b['id']??$_GET['id']??0);
            if(!$id) respond(422,['success'=>false,'message'=>'ID manquant.']);
            $col=$m->findById($id); if(!$col) respond(404,['success'=>false,'message'=>'Introuvable.']);
            $col->delete()?respond(200,['success'=>true,'message'=>'Collecte supprimée.']):respond(500,['success'=>false,'message'=>'Erreur.']);

        default: respond(405,['success'=>false,'message'=>'Méthode non autorisée.']);
    }
} catch(Throwable $e) { respond(500,['success'=>false,'message'=>'Erreur serveur: '.$e->getMessage()]); }
