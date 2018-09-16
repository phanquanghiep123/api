<?php
namespace App\Http\Controllers\api\frontend;
use Auth;
use Validator;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Core\APIFrontend;
class ArtistsController extends APIFrontend
{
    public function first ( Request $request){
        if($request->slug) $artists = \App\Models\Artists::where("slug","=",$request->slug)->first();
        else $artists = \App\Models\Artists::first();
        if($artists){
            $id = $artists->id;
            if($id){
                $currencys = \App\Models\Currencys::leftJoin('currency_artists',
                function($leftJoin) use($id)
                {
                    $leftJoin->on('currencys.id', '=', 'currency_artists.currency_id')
                    ->where('currency_artists.artist_id', '=', $id );
                })
                ->select(['currencys.*','currency_artists.price'])->get()->toArray();
                $artists->prices = $currencys;
                $this->_DATA["response"] = $artists;
                $this->_DATA["response"]["tracks"] = \App\Models\Artists::find($id)->musics()->where([["musics.status","=",1]])->orderBy('sort','ASC')->get();
                $this->_DATA["status"]  = 1;
            }   
        } 
        return response()->json($this->_DATA,200);     
    }   
}
