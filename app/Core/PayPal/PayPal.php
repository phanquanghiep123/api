<?php
namespace App\Core\PayPal;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use App\Core\PayPal\ResultPrinter;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Illuminate\Http\Request;
use PayPal\Api\PaymentExecution;
class PayPal {
    public $_SANBOX;
    public $_APP_KEY;
    public $_CLIENT_ID;
    public $_SECRET;
    public $_ACCESS_TOKEN;
    public $_USERNAME;
    public $_PASSWORD;
    public $_SIGNATURE;
    private  $apiContext;
    public function __construct(){
        $this->_SANBOX          = env("PAYPAL_SANBOX", "sandbox");
        $this->_APP_KEY         = env("PAYPAL_APP_KEY", null);
        $this->_CLIENT_ID       = env("PAYPAL_CLIENT_ID", "AS2qnbO8JuqVytKRs_6YNtbWwmwjg-vJTefOLCutzxYJL9zRRM99WEkZgy81xtzuirJaJr3dGcnhEGjv");
        $this->_SECRET          = env("PAYPAL_SECRET", "EHMCtmkmddj7XU58z-B9e80zT0uI8hpEBTSIUy_Flhs3NBC5XqCxpFfpAEjPaXdel5y6IINjW7LVdY0r");
        $this->_ACCESS_TOKEN    = env("PAYPAL_ACCESS_TOKEN", 'access_token$sandbox$khbj3mczs52hv24d$227f3855afccc7d4ae0cde34e683fa');
        $this->_USERNAME        = env("PAYPAL_USERNAME", "hiep_api1.gmail.com");
        $this->_PASSWORD        = env("PAYPAL_PASSWORD", "MHGRJ2H4T8YU6HP6");
        $this->_SIGNATURE       = env("PAYPAL_SIGNATURE", "AFcWxV21C7fd0v3bYYYRCpSSRl31AE8jd9Bb-DwMPn0bm2N91SNpc.aO");
        $composerAutoload = __DIR__. '/autoload.php';
        if (!file_exists($composerAutoload)) {
            $composerAutoload = __DIR__ . '/vendor/autoload.php';
            if (!file_exists($composerAutoload)) {
                echo "The 'vendor' folder is missing. You must run 'composer update' to resolve application dependencies.\nPlease see the README for more information.\n";
                exit(1);
            }
        }
        require $composerAutoload;
        
        date_default_timezone_set(@date_default_timezone_get());
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        $clientId =  $this->_CLIENT_ID;
        $clientSecret = $this->_SECRET ;
        $this->apiContext = $this->getApiContext($clientId, $clientSecret);        
    }

    private function getApiContext($clientId, $clientSecret)
    {

        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $clientId,
                $clientSecret
            )
        );

        // Comment this line out and uncomment the PP_CONFIG_PATH
        // 'define' block if you want to use static file
        // based configuration

        $apiContext->setConfig(
            array(
                'mode' => $this->_SANBOX,
                'log.LogEnabled' => true,
                'log.FileName' => __DIR__.'/log/PayPal.log',
                'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => true,
                //'cache.FileName' => '/PaypalCache' // for determining paypal cache directory
                // 'http.CURLOPT_CONNECTTIMEOUT' => 30
                // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
                //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
            )
        );

        // Partner Attribution Id
        // Use this header if you are a PayPal partner. Specify a unique BN Code to receive revenue attribution.
        // To learn more or to request a BN Code, contact your Partner Manager or visit the PayPal Partner Portal
        // $apiContext->addRequestHeader('PayPal-Partner-Attribution-Id', '123123123');

        return $apiContext;
    }
    public function CreatePaymentUsingPayPal(Request $request){
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");
        $item1 = new Item();
        $item1->setName($request->name)
            ->setCurrency($request->currency)
            ->setQuantity(1)
            ->setSku("123123") // Similar to `item_number` in Classic API
            ->setPrice($request->price);
       
        $itemList = new ItemList();
        $itemList->setItems(array($item1));

        
        $details = new Details();
        $details->setSubtotal($request->price);

        $amount = new Amount();
        $amount->setCurrency($request->currency)
            ->setTotal($request->price)
            ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Payment description")
            ->setInvoiceNumber(uniqid());
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($request->success_url)
            ->setCancelUrl($request->cancel_url);
        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));
        $request = clone $payment;
        try {
            $payment->create($this->apiContext);
        } catch (Exception $ex) {
            // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
            //ResultPrinter::printError("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", null, $request, $ex);
            exit(1);
        }
        $approvalUrl = $payment->getApprovalLink();
        //ResultPrinter::printResult("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", "<a href='$approvalUrl' >$approvalUrl</a>", $request, $payment);
        return $approvalUrl;
    }
    public function ExecutePayment ($request){
        if (isset($request['success']) && $request['success'] == 'true') {
            try {
                $paymentId = $_GET['paymentId'];
                $payment = Payment::get($paymentId, $this->apiContext);
                $execution = new PaymentExecution();
                $execution->setPayerId($_GET['PayerID']);
                $transaction = new Transaction();
                $amount = new Amount();
                $details = new Details();
                $details->setSubtotal($request['price']);
                $amount->setCurrency($request['currency']);
                $amount->setTotal($request['price']);
                $amount->setDetails($details);
                $transaction->setAmount($amount);
                $execution->addTransaction($transaction);
                try {
                    $result = $payment->execute($execution, $this->apiContext);
                    try {

                        $payment = Payment::get($paymentId, $this->apiContext);
                    } catch (Exception $ex) {
                        echo $ex->getCode(); 
                        echo $ex->getData();
                        die($ex);
                    }
                } catch (Exception $ex) {
                    echo $ex->getCode(); 
                    echo $ex->getData();
                    die($ex);
                }
            }
            catch (Exception $ex) {
                echo $ex->getCode(); 
                echo $ex->getData();
                die($ex);
            }
            return $payment;
        }  
    }

}