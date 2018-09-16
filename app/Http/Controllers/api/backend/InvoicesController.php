<?php
namespace App\Http\Controllers\api\backend;
use Auth;
use Validator;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Core\APIBackend;
class InvoicesController extends APIBackend
{
    public function gets (Request $request){
        $this->_DATA["page"] = $request->page;
        $page   = $request->page  - 1;
        $where  = [];
        $offset = $page * $this->_PAGINGNUMBER;
        $this->_MODEL = new \App\Models\Invoices();
        if($request->model != null){
            foreach ($request->model as $key => $value) {
                $where[] = [$key,"=",$value];
            }   
        }
        
        if($where){
            $this->_MODE = $this->_MODEL->where($where);
        }    
        $count  = $this->_MODEL->count();
        $results = $this->_MODEL->select(["payments.*","checkouts.full_name","checkouts.email","checkouts.price","currencys.currency","checkouts.payment_option","artists.name as artist_name"])
        ->join("checkouts","checkouts.id","=","payments.ckeckout_id")
        ->join("currencys","currencys.id","checkouts.currency")
        ->join("artists","artists.id","checkouts.artist_id")
        ->where($where)->offset($offset)->limit($this->_PAGINGNUMBER)->get();
        $this->_DATA["total"]   = $count;
        $this->_DATA["limit"]   = $this->_PAGINGNUMBER;
        $this->_DATA["response"] = $results;
        $this->_DATA["status"]  = 1;
        return response()->json($this->_DATA,200);
    }
    public function create (){
        $this->_MODEL = new \App\Models\Artists();
        $currencys = \App\Models\Currencys::leftJoin('currency_artists', 
            function($leftJoin)
            {
                $leftJoin->on('currencys.id', '=', 'currency_artists.currency_id')
                ->where('currency_artists.artist_id', '=', 0 );
            }
        )->select(['currencys.*','currency_artists.price'])->get()->toArray();
        $this->_DATA["response"] = $currencys;
        $this->_DATA["status"]  = 1;
        return response()->json($this->_DATA,200);
    }
    public function edit ( Request $request){
        $id = $request->id;
        if($id){
            $this->_DATA["response"] = \App\Models\Artists::find($id);
            $currencys = \App\Models\Currencys::leftJoin('currency_artists',
            function($leftJoin) use($id)
            {
                $leftJoin->on('currencys.id', '=', 'currency_artists.currency_id')
                ->where('currency_artists.artist_id', '=', $id );
            })
            ->select(['currencys.*','currency_artists.price'])->get()->toArray();
            $this->_DATA["response"]["musics"] = \App\Models\Artists::find($id)->musics()->orderBy('sort','ASC')->get();
            $this->_DATA["response"]['prices'] = $currencys;
            $this->_DATA["status"]  = 1;
        }
        return response()->json($this->_DATA,200);
    }
    public function store(Request $request){
        $a = new \App\Models\Artists();
        $a->name = $request->name;
        $a->sort_name = $request->sort_name;
        $a->gender = $request->gender;
        $a->bio = $request->bio;
        $a->area = $request->area;
        $a->type = $request->type;
        if($request->date_of_birth)
            $a->date_of_birth = date('Y-m-d',strtotime($request->date_of_birth)); 
        if($request->begin_date)
            $a->begin_date =  date('Y-m-d',strtotime($request->begin_date)); 
        if($request->end_date)
            $a->end_date = date('Y-m-d',strtotime($request->end_date));   
        $a->slug = $this->slug("artists","slug",$a->name);
        $a->save();
        if($request->prices ){
            $prices = json_decode($request->prices);
            foreach ($prices as $key => $value) {
               if($value->price !== null){
                    $ca = new \App\Models\Currency_artists();
                    $ca->artist_id = $a->id;
                    $ca->currency_id = $value->id;
                    $ca->price = $value->price;
                    $ca->save();  
               } 
            }
        }
        if($request->hasFile('avatarFile')){
            if ($request->file('avatarFile')->isValid()) {
                $path = $request->avatarFile->move('uploads/images/'.$a->id.'/', uniqid (). '-' .$request->avatarFile->getClientOriginalName());
                $a->avatar = $path;    
                $a->save();    
            }
        }
        $this->_DATA["response"] = \App\Models\Artists::find($a->id);
        $this->_DATA["status"]  = 1;
        return response()->json($this->_DATA ,200);
    }
    public function update(Request $request){
        $id = $request->id;
        if($id){
            $a = \App\Models\Artists::find($id);
            if($a->id){
                $a->name = $request->name;
                $a->sort_name = $request->sort_name;
                $a->gender = $request->gender;
                $a->bio = $request->bio;
                $a->area = $request->area;
                $a->type = $request->type;
                if($request->prices ){
                    $prices = json_decode($request->prices);
                    foreach ($prices as $key => $value) {
                       if($value->price !== null){
                            $cak = \App\Models\Currency_artists::where([["currency_id","=",$value->id],["artist_id","=",$a->id]])->first();
                            if($cak){
                                $cak->price = $value->price;
                                $ca->save();
                            }else {
                                $ca = new \App\Models\Currency_artists();
                                $ca->artist_id = $a->id;
                                $ca->currency_id = $value->id;
                                $ca->price = $value->price;
                                $ca->save();
                            }   
                       } 
                    }
                }
                if($request->date_of_birth)
                    $a->date_of_birth = date('Y-m-d',strtotime($request->date_of_birth)); 
                if($request->begin_date)
                    $a->begin_date =  date('Y-m-d',strtotime($request->begin_date)); 
                if($request->end_date)
                    $a->end_date = date('Y-m-d',strtotime($request->end_date));   
                $a->slug = $this->slug("artists","slug",$a->name,[["id","!=",$id]]);
                $a->save();
                if($request->hasFile('avatarFile')){
                    if ($request->file('avatarFile')->isValid()) {
                        $path = $request->avatarFile->move('uploads/images/'.$a->id.'/', uniqid (). '-' .$request->avatarFile->getClientOriginalName());
                        $a->avatar = $path;    
                        $a->save();    
                    }
                }
                $this->_DATA["response"] = \App\Models\Artists::find($a->id);
                $this->_DATA["status"]  = 1;
                $currencys = \App\Models\Currencys::leftJoin('currency_artists',
                function($leftJoin) use($id)
                {
                    $leftJoin->on('currencys.id', '=', 'currency_artists.currency_id')
                    ->where('currency_artists.artist_id', '=', $id );
                })
                ->select(['currencys.*','currency_artists.price'])->get()->toArray();
                $this->_DATA["response"]["musics"] = \App\Models\Artists::find($id)->musics()->orderBy('sort','ASC')->get();
                $this->_DATA["response"]['prices'] = $currencys;
                return response()->json($this->_DATA ,200);
            }
        } 
    }
    public function destroy (Request $request){
        $id = $request->id;
        if($id){
            $a = \App\Models\Artists::find($id);
            $a->status = 0;
            $a->save();
            $this->_DATA["response"] = $a;
            $this->_DATA["status"]  = 1;
        }
        return response()->json($this->_DATA,200);
    }
}
