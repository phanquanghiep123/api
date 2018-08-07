<?php
namespace App\Http\Controllers\api\frontend;
use Auth;
use Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Core\APIFrontend;
class AuthController extends APIFrontend
{
    
    public function make_token ($record,$doamin){
        return md5($record->id.$doamin);
    }
    public function make_role_token ($record,$doamin){
        return md5($record->id . $record->role_id . $doamin);
    }
    public function add(Request $request){
        if($request->id == 0){
            $t = new \App\Models\Tracking_in_site();
            
        }else{
            $t =\App\Models\Tracking_in_site::find($request->id);
        }
        $t->token =  $request->token;
        $t->loc =  $request->loc;
        $t->region =  $request->region;
        $t->city =  $request->city;
        $t->country =  $request->country;
        $t->ip =  $request->ip;
        $t->save();
        $this->_DATA["response"] = $t->toArray();
        $this->_DATA["status"]   = 1;
        return response()->json($this->_DATA,200);
    }
}
