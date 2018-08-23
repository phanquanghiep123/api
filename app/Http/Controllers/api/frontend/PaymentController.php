<?php

namespace App\Http\Controllers\api\frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Core\APIFrontend;
use App\Core\PayPal\PayPal;
use App\Core\Ccavenue\Ccavenue;

/*
# checkout status.
0  -> init
1  -> purchase
2  -> payment successfuly
3  -> on remove checkout

# payment status .
1  -> init payment.
2  -> payment successfuly
3  -> cancel payment
4  -> payment return error

*/
class PaymentController extends APIFrontend
{
    public function checkout (Request $request){
        $artist = \App\Models\Artists::find($request->artist_id);
        if($artist){
            $checkout = new \App\Models\Checkouts();
            $checkout->full_name = $request->full_name;
            $checkout->email = $request->email;
            $checkout->artist_id = $artist->id;
            $checkout->payment_option = $request->payment_option;
            $currency = null;
            if($request->price){
                $currency_artist = \App\Models\Currency_artists::where([['currency_id',"=",$request->price["id"]],['artist_id',"=", $artist->id]])->first();
                if($currency_artist){
                    $checkout->price = $currency_artist->price;
                }
                $currency =\App\Models\Currencys::find($currency_artist->currency_id);
                $checkout->currency = $currency->id;
            }
            $checkout->status = 0;
            $checkout->key = md5(uniqid());
            $checkout->success_url = $request->success_url;
            $checkout->cancel_url = $request->cancel_url;
            $checkout->save();
            if($checkout->id){
                $this->_DATA["response"] = $checkout->toArray();
                $this->_DATA["status"]   = 1;
            }  
            if(@$currency->currency == "INR"){
                $request["redirect_url"] = route("api.payment.ccavenue_success",["success"=> "true","key" => $checkout->key]);
                $request["cancel_url"]  = route("api.payment.ccavenue_cancel",["success"=> "flase","key" => $checkout->key]);
                $data = [
                    "redirect_url"  => route("api.payment.ccavenue_success",["key" => $checkout->key]),
                    "cancel_url"    => route("api.payment.ccavenue_cancel",["key" => $checkout->key]),
                    "billing_name"  => $checkout->full_name,
                    "billing_email" => $checkout->email,
                    "currency"      => $currency->currency,
                    "amount"        => $checkout->price,
                    "order_id"      => $checkout->id
                ];
                $ccavenue = $this->ccavenue($data);
                $checkout->encrypted_data = @$ccavenue['encrypted_data'];
                $checkout->access_code    = @$ccavenue['access_code'];
                $checkout->host           = @$ccavenue['action'];
                $checkout->payment_option = "ccavenue";
                $checkout->status = 1;
                $checkout->save();
                $this->_DATA["response"]['url'] = route('api.payment.ccavenue_submit',["key" =>  $checkout->key]);
                $this->_DATA["response"]['checkout'] =  $checkout->toArray();
                $this->_DATA["redirect"] = 1;
                $this->_DATA["status"]   = 1;
            } 
        }
        return response()->json($this->_DATA,200);
        
    }
    public function purchase (Request $request){
        if($request->artist_id){
            $artist = \App\Models\Artists::find($request->artist_id);   
            $checkout = \App\Models\Checkouts::find($request->id); 
            $request->name = $artist->full_name;
            $request->email = $artist->email;
            if($request->price){
                $currency_artist = \App\Models\Currency_artists::where([['currency_id',"=",$request->price["id"]],['artist_id',"=", $artist->id]])->first();
                if($currency_artist){
                    $request->price = $currency_artist->price;
                    $currency =  \App\Models\Currencys::find($currency_artist->currency_id);
                    $request->currency = $currency->currency;
                }   
            }
            $checkout->status = 1;
            $checkout->save();
            if( $request->currency == "USD"){
                $request->success_url = route("api.payment.paypal_success",["success"=> "true","key" => $checkout->key]);
                $request->cancel_url = route("api.payment.paypal_cancel",["success"=> "flase","key" => $checkout->key]); 
                $paypal = $this->paypal($request);
                $checkout->host =$paypal;
                $checkout->save();
                $this->_DATA["response"] = $paypal;
                $this->_DATA["redirect"] = 1;
                $this->_DATA["status"]   = 1; 
            } else if($request->currency == "INR"){
                $request["redirect_url"] = route("api.payment.ccavenue_success",["success"=> "true","key" => $checkout->key]);
                $request["cancel_url"]  = route("api.payment.ccavenue_cancel",["success"=> "flase","key" => $checkout->key]);
                $data = [
                    "redirect_url"  => route("api.payment.ccavenue_success",["key" => $checkout->key]),
                    "cancel_url"    => route("api.payment.ccavenue_cancel",["key" => $checkout->key]),
                    "billing_name"  => $checkout['full_name'],
                    "billing_email" => $checkout['email'],
                    "currency"      => $request->currency,
                    "amount"        => $request->price,
                    "order_id"      => $checkout->id
                ];
                $ccavenue = $this->ccavenue($data);
                $checkout->encrypted_data = @$ccavenue['encrypted_data'];
                $checkout->access_code    = @$ccavenue['access_code'];
                $checkout->host           = @$ccavenue['action'];
                $checkout->save();
                $this->_DATA["response"] = route('api.payment.ccavenue_submit',["key" =>  $checkout->key]);
                $this->_DATA["redirect"] = 1;
                $this->_DATA["status"]   = 1;
            } 
            return response()->json($this->_DATA,200);  
        }
        
    }
    public function ccavenue_submit(Request $request){
        $checkout = \App\Models\Checkouts::where([["key","=",$request->key]])->first(); 
        if($checkout  == null ) die("1");
        return '<div class="none" style="display:none">
        <form id="formCcavenue" method="POST" name="redirect" action="'.$checkout->host.'">
          <input type=hidden name=encRequest value="'.$checkout->encrypted_data.'">
          <input type=hidden name=access_code value='.$checkout->access_code.'> 
        </form>
        <script language=\'javascript\'>document.redirect.submit();</script>
      </div>';
    }
    public function paypal_success(Request $request){
        if($request->key){
            $checkout = \App\Models\Checkouts::where("key","=",$request->key)->first(); 
            if(@$checkout->status == 1 ){
                $artist = \App\Models\Artists::find($checkout->artist_id);  
                $currency =  \App\Models\Currencys::find($checkout->currency);
                if($artist){
                    $request['name'] = $artist->name;
                    $request['currency'] = $currency->currency;
                    $currency_artist = \App\Models\Currency_artists::where([['currency_id',"=",$checkout->currency],['artist_id',"=", $artist->id]])->first();
                    if($currency_artist){
                        $request['price'] = $currency_artist->price;
                    }else{
                        return redirect(assert('/'));
                    }
                    $p = new PayPal();
                    $return = $p->ExecutePayment($request->all());
                    $payment = new \App\Models\Payments();
                    $payment->ckeckout_id = $checkout->id;
                    $payment->info = $return->toJSON();
                    $checkout->status = 2;
                    $checkout->save();
                    try {
                        $payment->info = $return->toJSON();
                        $payment->invoice_id = $this->generate_invoice($artist->name). '-' . $return->id;
                        $payment->status = 1;
                    } catch (Exception $e) {
                        $payment->info = '[]';
                        $payment->status = 4;
                    }
                    $payment->save();
                    return redirect($checkout->success_url . "?status=true&key=$checkout->key");
                } 
            }else{
                return redirect(assert('/'));
            }
        }else{
            return redirect(assert('/'));
        }
    }
    public function paypal_cancel(Request $request){
        if($request->key){
            $checkout = \App\Models\Checkouts::where("key","=",$request->key)->first(); 
            if(@$checkout->status == 1 ){
                $artist = \App\Models\Artists::find($checkout->artist_id);  
                if($artist){
                    $request->name = $artist->name;
                    $request->price = $artist->price;
                    $p = new PayPal();
                    $checkout->status = 3;
                    $checkout->save();
                    $payment = new \App\Models\Payments();
                    $payment->ckeckout_id = $checkout->id;
                    $payment->info = '[]';
                    $payment->status = 3;
                    $payment->save();
                    return redirect($checkout->cancel_url . "?status=false&key=$checkout->key");
                }  
            }else{
                return redirect(assert('/'));
            }
        }else{
            return redirect(assert('/'));
        }
    }
    public function paypal(Request $request){
        $p = new PayPal();
        $return = $p->CreatePaymentUsingPayPal($request);
        return  $return ;
    }
    public function ccavenue ($request){
        $c = new Ccavenue();
        $return = $c->create($request);
        return  $return ;
    }
    public function ccavenue_success (Request $request){
        if($request->key && $request->encResp){
            $encResp = $request->encResp;
            $checkout = \App\Models\Checkouts::where("key","=",$request->key)->first();         
            if(@$checkout->status == 1 ){
                $artist = \App\Models\Artists::find($checkout->artist_id);  
                if($artist){
                    $request->name = $artist->name;
                    $request->price = $artist->price;
                    $c = new Ccavenue();
                    $return = $c->status($encResp);
                    $checkout->status = 2;
                    $checkout->save();
                    $payment = new \App\Models\Payments();
                    $payment->ckeckout_id = $checkout->id;
                    try {
                        $payment->info = json_encode(@$return['info']);
                        $payment->invoice_id = $this->generate_invoice($artist->name). '-' . @$return['info'][0][1];
                        $payment->status_custom = @$return['order_status'];
                        $payment->message = @$return['message'];
                        if($payment->status_custom == "Success" || $payment->status_custom == "Aborted"){
                            $payment->status = 1;
                        }else{
                            $payment->status = 4;
                        }
                    } catch (Exception $e) {
                        $payment->info = '[]';
                        $payment->status = 4;
                    }
                    $payment->save();
                    return redirect($checkout->success_url . "?status=true&key=$checkout->key");
                } 
            }else{
                return redirect(assert('/'));
            }          
        }else{
           return redirect(assert('/'));
        }
    }
    public function ccavenue_cancel (Request $request){
        if($request->key){
            $encResp = $request->encResp;
            $checkout = \App\Models\Checkouts::where("key","=",$request->key)->first(); 
            if(@$checkout->status == 1 ){
                $artist = \App\Models\Artists::find($checkout->artist_id);  
                if($artist){
                    $request->name = $artist->name;
                    $request->price = $artist->price;
                    $checkout->status = 3;
                    $checkout->save();
                    $payment = new \App\Models\Payments();
                    $payment->ckeckout_id = $checkout->id;
                    try {
                        $payment->info = json_encode([]);
                        $payment->status = 3;
                    } catch (Exception $e) {
                        $payment->info = '[]';
                        $payment->status = 4;
                    }
                    $payment->save();
                    return redirect($checkout->cancel_url . "?status=false&key=$checkout->key");
                } 
            }else{
                return redirect(assert('/'));
            }         
        }else{
            return redirect(assert('/'));
        }
    }
    public function generate_invoice($string){
        $argstring = explode(" ",$string);
        $count = count($argstring);
        $return = "";
        for ($i = 0 ; $i < $count; $i++){
            if(strlen($return) < 3){
                $return .= substr($argstring[$i],0,1);
                $step = $i + 1;
                if(strlen($return) < 3 && @$argstring[$step] == null){
                    $return .= substr($argstring[$i],1,(3 - strlen($return)));
                }
            }else{
                break;
            }
        }
        return $return;
    }
}
