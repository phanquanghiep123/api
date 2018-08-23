<?php

namespace App\Http\Controllers\api\frontend;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Core\APIFrontend;
use DateTime;
use ZipArchive;
class DownloadsController extends APIFrontend
{
    public function add (Request $request){
        $public_key = $request->public_key;
        $checkout_id  = $request->checkout_id;
        $c = \App\Models\Checkouts::find( $checkout_id );
        if($c && $c->status == 2){
            $p = \App\Models\Payments::where("ckeckout_id","=" ,$c->id )->first();
            $a = \App\Models\Artists::find( $c->artist_id );
            if($a && $p && $p->status == 1){
                $d = new \App\Models\Downloads();
                $d->artist_id = $a->id;
                $d->checkout_id = $c->id;
                $d->payment_id = $p->id;
                $d->public_key = $public_key;
                $d->start = date("Y-m-d H:i:s");
                $d->end = date('Y-m-d H:i:s', strtotime($d->start.' +2 day'));
                $d->status = 1;
                $d->number_dowload = 0;
                $d->save();
                $this->_DATA["response"] = $d->toArray();
                $this->_DATA["status"]   = 1;
                $c->status = 3;
                $c->save();
                $p->status = 2;
                $p->save();        
            }else{
                $this->_DATA["redirect"]   = true;
                $this->_DATA["response"] = '/';
                
            }
        }else{
            $this->_DATA["redirect"]   = true;
            $this->_DATA["response"] = '/';
            
        }
        return response()->json($this->_DATA,200);
    }
    public function file(Request $request){
        ini_set('memory_limit', '512M');
        ini_set('memory_limit', '1G');
        $public_key  = $request->public_key;
        $track_id    = $request->track_id;
        $artist_id   = $request->artist_id;
        $today = date("Y-m-d H:i:s");
        $d = \App\Models\Downloads::file($today , $track_id, $artist_id, $public_key);
        if($d){
            $size = filesize (base_path($d->path));
            $content = file_get_contents (base_path($d->path));
            $type = pathinfo(base_path($d->path), PATHINFO_EXTENSION);
            $base64content = 'data:audio/' . $d->extension . ';base64,' . base64_encode($content);
            $dw = \App\Models\Downloads::find($d->dowload_id);
            $dw->number_dowload = $dw->number_dowload + 1;
            $dw->save();
            $response = \Response::make($content);
            $response->header('Content-Type', 'audio/'.$d->extension);
            $response->header("Content-Disposition","attachment; filename=$d->name.$d->extension");
            $response->header('Content-Length',  $size);
            return  $response;
        } 
    }
    public function check(Request $request){
        $public_key = $request->public_key;
        $today = date("Y-m-d H:i:s");
        $ds = \App\Models\Downloads::where("public_key","=",$public_key)->where([
            ["start","<=",$today],
            ["end",">=",$today]
        ])->first();
        $artist_ids = [];
        if($ds){
            $artist_ids[] = $ds->artist_id;
            $as = \App\Models\Artists::whereIn("id",$artist_ids)->get();
            $artists = [];
            if(!$as->isEmpty()){
                foreach ($as as $key => $value) {
                    $value->tracks = \App\Models\Artists::find($value->id)->musics()->select("artist_id","id","name","description","type","size")->orderBy('sort','ASC')->get()->toArray();
                    $artists[] = $value;
                }
                $tracks = \App\Models\Musics::whereIn("artist_id",$artist_ids)->orderBy('sort','ASC')->get()->toArray();
                $datetime1 = new DateTime($today);
                $datetime2 = new DateTime($ds->end);
                $interval = $datetime1->diff($datetime2);
                $d = $interval->d ;
                $h = $interval->h ;
                $i = $interval->i ;
                $s = $interval->s ;
                $h = $d > 0 ? $d * 24 + $h : $h;
                $ds->diffTime = $h . ":" . $i . ":" . $s ;
                $this->_DATA["response"] = $artists;
                $this->_DATA["response"]["tracks"] = $tracks;
                $this->_DATA["response"]["download"] = $ds;
                $this->_DATA["status"]   = 1;
            }else{
                $this->_DATA["redirect"]   = true;
                $this->_DATA["response"] = '/';
                
            }
        }else{
            $this->_DATA["redirect"]   = true;
            $this->_DATA["response"] = '/';
            
        }
        return response()->json($this->_DATA,200);
    }

    public function zipfiles(Request $request,Response $response)
    {
       
        ini_set('memory_limit', '512M');
        ini_set('memory_limit', '1G');
        //phpinfo();
       // return;
        $public_key  = $request->public_key;
        $track_id    = $request->track_id;
        $artist_id   = $request->artist_id;
        $today = date("Y-m-d H:i:s");
        $a = \App\Models\Artists::find($artist_id);
        if($a)
        {
            $d = \App\Models\Downloads::file($today , $track_id, $artist_id, $public_key);
            if($d){
                $t = \App\Models\Musics::find($track_id);
                $public_dir = public_path();
                $zipFileName = $t->name.'.zip';
                $zip = new ZipArchive();
                if ($zip->open($public_dir . '/' . $zipFileName,ZipArchive::CREATE) === TRUE) {
                    $zip->addFile(base_path($t->path));
                    $zip->addFile(base_path($t->pathextended));
                    $zip->addFile(base_path($t->pathpdf)); 
                    $zip->close();
                }
                $filetopath = $public_dir.'/'.$zipFileName;
                if(file_exists($filetopath)){
                    header('Content-Type: application/octet-stream');
                    header("Content-Transfer-Encoding: Binary"); 
                    header("Content-disposition: attachment; filename=\"" .$zipFileName. "\""); 
                    readfile($filetopath);
                    unlink ($filetopath);
                }
            }
        }
    }
}
