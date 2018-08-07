<?php

namespace App\Http\Controllers\api\backend;
use Auth;
use DB;
use Validator;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Core\APIBackend;
class MusicsController extends APIBackend
{
    public function create ()
    {
        $artists = \App\Models\Artists::get()->toArray();
        $this->_DATA["total"]   = 0;
        $this->_DATA["page"]    = 0;
        $this->_DATA["limit"]   = $this->_PAGINGNUMBER;
        $this->_DATA["response"] = $artists;
        $this->_DATA["status"]  = 1;
        $this->_DATA["public_url"] = asset("public/");
        return response()->json($this->_DATA,200);

    }
    public function gets (Request $request){
        $page   = $request->page ? $request->page : 0;
        $where  = []; 
        $offset = $page * $this->_PAGINGNUMBER;
        $this->_MODEL = new \App\Models\Musics();
        $this->_MODEL = $this->_MODEL->leftJoin('artists', 'artists.id', '=', 'musics.artist_id')
        ->select('musics.*', 'artists.name as artist_name');
        if($where != null){
            $this->_MODEL->where($where);
        }
        $count  = $this->_MODEL->count();
        $results = $this->_MODEL->offset($offset)->limit($this->_PAGINGNUMBER)->get();
        $this->_DATA["total"]   = $count;
        $this->_DATA["page"]    = $page;
        $this->_DATA["limit"]   = $this->_PAGINGNUMBER;
        $this->_DATA["response"] = $results;
        $this->_DATA["status"]  = 1;
        $this->_DATA["public_url"] = asset("public/");
        return response()->json($this->_DATA,200);
    }
    public function edit ( Request $request){
        $id = $request->id;
        if($id){
            $this->_DATA["response"] = \App\Models\Musics::find($id);
            $this->_DATA["status"]  = 1;
        }
        return response()->json($this->_DATA,200);
    }
    public function store(Request $request){
        $a = new \App\Models\Musics();
        $a->name = $request->name;
        $a->artist_id = $request->artist_id;
        $a->description = $request->description;
        $a->status = $request->status;
        $a->size = 0;
        $a->type = $request->type;
        $a->price =  0;
        $a->version = "1.0.0";
        $ls = \App\Models\Artists::find($a->artist_id)->musics()->orderBy('sort','ASC')->first();
        if(@$ls->id){
            $a->sort = $ls->sort + 1;
        }else{
            $a->sort = 0;
        }
        if($request->day_of_creation)
            $a->day_of_creation = date('Y-m-d',strtotime($request->day_of_creation)); 
        $a->slug = $this->slug("musics","slug",$a->name);
        $a->save();
        if($request->hasFile('thumbFile')){
            if ($request->file('thumbFile')->isValid()) {
                $path = $request->thumbFile->move('uploads/images/'.$a->artist_id.'/', uniqid (). '-' .$request->thumbFile->getClientOriginalName());
                $a->thumb = $path;  
                $a->save();      
            }
        }
        if($request->hasFile('pathFile')){
            if ($request->file('pathFile')->isValid()) {
                $path = $request->pathFile->move('uploads/audios/'.$a->artist_id.'/', uniqid (). '-' .$request->pathFile->getClientOriginalName());
                $extension = $request->pathFile->getClientOriginalExtension();
                $a->size   = $request->pathFile->getClientSize();
                $a->extension = $extension;
                $a->path = $path;    
                $a->save();     
            }
        }
        $this->_DATA["response"] = $a;
        $this->_DATA["status"]  = 1;
        return response()->json($this->_DATA ,200);
    }
    public function update(Request $request){
        $id = $request->id;
        if($id){
            $a = \App\Models\Musics::find($id);
            if($a->id){
                $a->name = $request->name;
                $a->artist_id = $request->artist_id;
                $a->description = $request->description;
                $a->status = $request->status;
                $a->size = 0;
                $a->type = $request->type;
                $a->price =  0;
                $a->version = "1.0.0";
                if($request->day_of_creation)
                    $a->day_of_creation = date('Y-m-d',strtotime($request->day_of_creation)); 
                $a->slug = $this->slug("musics","slug",$a->name,[["id","!=",$id]]);
                $a->save();
                if($request->hasFile('thumbFile')){
                    if ($request->file('thumbFile')->isValid()) {
                        $path = $request->thumbFile->move('uploads/images/'.$a->artist_id.'/', uniqid (). '-' .$request->thumbFile->getClientOriginalName());
                        $a->thumb = $path;
                        $a->save();
                    }
                }     
                if($request->hasFile('pathFile')){
                    if ($request->file('pathFile')->isValid()) {
                        $extension = $request->pathFile->getClientOriginalExtension();
                        $path = $request->pathFile->move('uploads/audios/'.$a->artist_id.'/', uniqid (). '-' .$request->pathFile->getClientOriginalName());
                        $a->size   = $request->pathFile->getClientSize();
                        $a->extension = $extension;
                        $a->path = $path;  
                        $a->save() ;
                    }
                }
                $this->_DATA["response"] = \App\Models\Musics::find($a->id);
                $this->_DATA["status"]  = 1;
                return response()->json($this->_DATA ,200);
            }
        } 
    }
    public function destroy (Request $request){
        $id = $request->id;
        if($id){
            $m = \App\Models\Musics::find($id);
            $m->status = 0;
            $m->save();
            $this->_DATA["response"] = $m;
            $this->_DATA["status"]  = 1;
        }
        return response()->json($this->_DATA,200);
    }
    public function sort(Request $request){
        $id = $request->id;     
        if($id){
            $m = \App\Models\Musics::find($id);
            if($m->id){
                
                $old = $m->sort;
                $new = $request->index;
                if($old != $new){
                    $update_list;
                    if($old > $new){
                        $l =\App\Models\Musics::where ([["sort","<",$old],["sort",">=",$new]])->get();
                        $update_list = 1;
                    }else{
                        $l =\App\Models\Musics::where ([["sort",">",$old],["sort","<=",$new]])->get();
                        $update_list = -1;
                    }
                    foreach ($l as $key => $value) {
                        $value->sort = $value->sort + ($update_list);
                        $value->save();
                    }
                    $m->sort = $new;
                    $m->save();
                } 
                $this->_DATA["status"]  = 1;
                return response()->json($this->_DATA,200);
            } 
        }
        return response()->json($this->_DATA,200);
    }
}