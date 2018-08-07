<?php
namespace App\Core\Ccavenue;
class Ccavenue {
    public $_SANBOX;
    public $_MERCHANT_ID;
    public $_WORKING_KEY;
    public $_ASSESS_CODE;
    public $_URL;
    public function __construct(){
        error_reporting(0);
        $this->_SANBOX          = env("CCAVENUE_SANBOX", "live");
        $this->_URL             = $this->_SANBOX == "live" ? "https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction" : "https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction";
        $this->_MERCHANT_ID     = env("CCAVENUE_MERCHANT_ID", "74771");
        $this->_ASSESS_CODE     = env("CCAVENUE_ASSESS_CODE", "AVJU06CH10BL68UJLB");
        $this->_WORKING_KEY     = env("CCAVENUE_WORKING_KEY", "FD8BB95A9934B1E5BD660FF6BB1F3AE2");
    }
    public function create ($request){
        $request["merchant_id"] = $this->_MERCHANT_ID;
        $merchant_data = "";
        foreach ($request as $key => $value){
            $merchant_data.= $key.'='.$value.'&';
        }
        $encrypted_data = $this->encrypt($merchant_data,$this->_WORKING_KEY);
        $request["encrypted_data"] = $encrypted_data;
        $request["access_code"] = $this->_ASSESS_CODE;
        $request["action"] = $this->_URL;
        return $request;
    }
    public function status ($encResp){
        $encResponse = $encResp;	
        $rcvdString  = $this->decrypt($encResponse,$this->_WORKING_KEY);		 
        $order_status = "";
        $decryptValues = explode('&', $rcvdString);
        $dataSize= sizeof($decryptValues);
        $return = [];
        if($decryptValues){
            for($i = 0; $i < $dataSize; $i++) 
            {   $information =  explode('=',$decryptValues[$i]); 
                $return['info'][] = $information ;
                if($i==3) $order_status=$information[1];
            }
        }
        if($order_status==="Success")
        {
            $message = "Thank you for shopping with us. Your credit card has been charged and your transaction is successful. We will be shipping your order to you soon.";
            
        }
        else if($order_status==="Aborted")
        {
            $message = "Thank you for shopping with us.We will keep you posted regarding the status of your order through e-mail";
        
        }
        else if($order_status==="Failure")
        {
            $message = "Thank you for shopping with us.However,the transaction has been declined.";
        }
        else
        {
            $message =  "Security Error. Illegal access detected";
        
        }
        
        $return['order_status'] = $order_status;
        $return['message'] = $message;
        return $return;
    }
    public function cancel ($request){
         
    }
    private function encrypt($plainText,$key)
	{
		$secretKey = $this->hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
	  	$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
	  	$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
		$plainPad = $this->pkcs5_pad($plainText, $blockSize);
	  	if (mcrypt_generic_init($openMode, $secretKey, $initVector) != -1) 
		{
		      $encryptedText = mcrypt_generic($openMode, $plainPad);
	      	      mcrypt_generic_deinit($openMode);
		      			
		} 
		return bin2hex($encryptedText);
	}

	private function decrypt($encryptedText,$key)
	{
		$secretKey = $this->hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$encryptedText=$this->hextobin($encryptedText);
	  	$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
		mcrypt_generic_init($openMode, $secretKey, $initVector);
		$decryptedText = mdecrypt_generic($openMode, $encryptedText);
		$decryptedText = rtrim($decryptedText, "\0");
	 	mcrypt_generic_deinit($openMode);
		return $decryptedText;
		
	}
	//*********** Padding Function *********************

	private function pkcs5_pad ($plainText, $blockSize)
	{
	    $pad = $blockSize - (strlen($plainText) % $blockSize);
	    return $plainText . str_repeat(chr($pad), $pad);
	}

	//********** Hexadecimal to Binary function for php 4.0 version ********

	private function hextobin($hexString) 
    { 
        $length = strlen($hexString); 
        $binString="";   
        $count=0; 
        while($count<$length) 
        {       
            $subString =substr($hexString,$count,2);           
            $packedString = pack("H*",$subString); 
            if ($count==0)
        {
            $binString=$packedString;
        } 
            
        else 
        {
            $binString.=$packedString;
        } 
            
        $count+=2; 
        } 
        return $binString; 
    }
}

