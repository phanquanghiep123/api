<?php
namespace App\Http\Controllers\api\backend;
use Auth;
use Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
class AuthController extends Controller
{
    public function Login(Request $request){  
       
        $_DATA = ["status" => "error","response" => null ,"messege" => null];
        $check = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);
        if ($check->fails()){
            $_DATA["messege"] = $check->errors();
        }else{
            $_HEADERS = $this->Customgetallheaders();
            $doamin   = @$_HEADERS["Host"];
            $email    = strtolower($request->email);
            $password = $request->password;
            $remember = $request->remember;
            if( Auth::attempt(['email' => $email, 'password' => $password, "is_sys" => 1]) ){
                $record = Auth::user();  
                $user = \App\Models\Users::find($record->id);
                $user->token = md5($record->id.$doamin);
                $user->save();
                $user->role  = md5($record->id . $record->role_id . $doamin);
                \App\Models\TrackingLogin::where('user_id','=',$user->id)->update(["status" => 0]);
                $t = new \App\Models\TrackingLogin();
                $t->user_id = $user->id;
                $t->token = $user->token;
                $t->day_woking = $remember ? 6 : 2 ;
                $t->save();
                $user->tracking = $t->id;
                $_DATA["status"] = "success";
                $_DATA["response"] = $user->toArray();
            }else{
                $_DATA["messege"] = '<p>User/password not incorrect</p>';
            }       
        }
        return response()->json($_DATA,200);
    }
    public function make_token ($record,$doamin){
        return md5($record->id.$doamin);
    }
    public function make_role_token ($record,$doamin){
        return md5($record->id . $record->role_id . $doamin);
    }
    private function Customgetallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
