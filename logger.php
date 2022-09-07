<?php 
if(class_exists("logger") == false){
# LOGGER
class logger
{
	public $filename;
	public $log;
	private $timesAppend = 0;
	public function __construct()
	{
		$logFileName=date("H:i:s d-m-Y").'.log';
		$FullLogFileName="/var/log/logger/".date("H:i:s d-m-Y").'_'.strval(random_int(1, 999999)).'.log';
		if (file_exists($FullLogFileName) == False and touch($FullLogFileName) == true) {
			$this->filename = $FullLogFileName;
			$this->log = '';
			return true;
		}else{
			return false;
		}
	}
	private function encrypt($key, $data) {
		$encrypted;
		$tmpEncFile = '/tmp/'.str_replace(array("/","\\"),"",base64_encode(random_bytes(9)));
		$tmpFile = '/tmp/'.str_replace(array("/","\\"),"",base64_encode(random_bytes(9)));
		file_put_contents($tmpFile, $data);
		if(openssl_pkcs7_encrypt($tmpFile,$tmpEncFile,openssl_x509_read($key),array())){
		#if(openssl_public_encrypt($data,$encrypted,openssl_x509_read($key))){
			unlink($tmpFile);
			$encrypted = file_get_contents($tmpEncFile);
			unlink($tmpEncFile);
			return $encrypted;
		}else{
			unlink($tmpEncFile);
			unlink($tmpFile);
		//	var_dump(openssl_error_string());
			return false;
		}
	}

	private function decrypt($key,$public, $data) {
		$decrypted;
		$tmpEncFile = '/tmp/'.str_replace(array("/","\\"),"",base64_encode(random_bytes(9)));
		$tmpFile = '/tmp/'.str_replace(array("/","\\"),"",base64_encode(random_bytes(9)));
		file_put_contents($tmpFile, $data);
		if(openssl_pkcs7_decrypt($tmpFile,$tmpEncFile,openssl_x509_read($public),openssl_pkey_get_private($key))){
		#if(openssl_private_decrypt($data,$decrypted,openssl_pkey_get_private($key))){
			unlink($tmpFile);
			$decrypted = file_get_contents($tmpEncFile);
			unlink($tmpEncFile);
			return $decrypted;
		}else{
			unlink($tmpEncFile);
			unlink($tmpFile);
			//var_dump(openssl_error_string());
			return false;
		}
	}
	public function appendToLog($text,$type="INFO(AUTO)",$save = false)
	{
		if ($this->timesAppend ==3) {
			$this->saveLog();
			$this->timesAppend = 0;

		#	echo "saved";
		}
		if (isset($this->log)) {
			$this->log.=date("d.m.Y H:i:s").": [".$type."]: ".$text."\n";
			if ($save) {
				$this->saveLog();
				$this->timesAppend = 0;
			}
			$this->timesAppend +=1;
			return true;
		}else{
			return false;
		}
	}
	public function appendDebugInfo(){
		$this->appendToLog("I am - ".$_SERVER['SCRIPT_NAME'].".","INFO");
		$this->appendToLog("DEBUG SESSION INFO: ","DEBUG");
		$this->appendToLog(json_encode($_SESSION),"DEBUG");
		$this->appendToLog("DEGUG SERVER INFO: ","DEBUG");
		$this->appendToLog(json_encode($_SERVER),"DEBUG");
		$this->appendToLog("DEBUG GET INFO: ","DEBUG");
		$this->appendToLog(json_encode($_GET),"DEBUG");
		$this->appendToLog("DEBUG POST INFO: ","DEBUG");
		$this->appendToLog(json_encode($_POST),"DEBUG",true);
	}
	public function saveLog()
	{
		if(file_exists($this->filename)){
			if(file_get_contents("/var/log/logger/publickey.pem") == true){
				$key = file_get_contents("/var/log/logger/publickey.pem");
			}else{
				return "key error";
			}
			return file_put_contents($this->filename, $this->encrypt(strval($key),$this->log));
		}else{
			return "file not exists";
		}
	}
	public function decryptLogFile($file,$key)
	{
		if(file_exists($file)){
			if(file_get_contents("/var/log/logger/publickey.pem") == true){
				$pubkey = file_get_contents("/var/log/logger/publickey.pem");
			}else{
				return "key error";
			}
			return $this->decrypt($key,$pubkey,file_get_contents($file));
		}else{
			return "file not exists";
		}
	}
}
/*
UNIT-TESTS
*/

/*
$logger = new logger();
if ($logger) {
	echo "TEST 1 PASS\n";
}else{
	echo "TEST 1 FAIL\n";
}
//var_dump($logger);
if (isset($logger->log)) {
	echo "TEST 2 PASS\n";
}else{
	echo "TEST 2 FAIL\n";
}
$logger->log = "Append to log by hand\n";
$logger->appendToLog("Append to log by function");
$logger->appendToLog("Info!","Info");
$logger->appendToLog("Error!","ERROR");
$logger->appendToLog("Warning!","WARN");
$testData = $logger->log;
//var_dump(isset($logger->log));
$saveLog = $logger->saveLog();
if ($saveLog) {
	echo "TEST 3 PASS\n";
}else{
	echo "TEST 3 FAIL\n";
}
//var_dump($saveLog);
$filename = $logger->filename;
$logger = new logger();
if ($logger) {
	echo "TEST 4 PASS\n";
}else{
	echo "TEST 4 FAIL\n";
}
$private_key = "private key here";
$decrypted = $logger->decryptLogFile($filename,$private_key);
if($decrypted == $testData){
	echo "TEST 5 PASS\n";
}else{
	echo "TEST 5 FAIL\n";
}
#//var_dump($saveLog);
//*/
}
