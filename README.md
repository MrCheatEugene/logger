# logger
PHP simple logger with asymetric encryption. 
# Mechanics
1. Initialize logger object
2. Append to log.
3. Save log. Saving DOES NOT clear public log object variable.

# How to use

```php
$logger = new logger();
if($logger){
$logger->appendDebugInfo();// append $_POST, $_SESSION, $_GET, $_SERVER variables to log
$logger->appendToLog("text") // Append "text" with mark "INFO(AUTO)"(auto means that no mark was set) and no force-save enabled
$logger->appendToLog("text","ERROR") // Append "text" with mark "ERROR" and no force-save enabled
$logger->appendToLog("text","ERROR",true) // Append "text" with mark "EROR" and force-save enabled
$logger->saveLog(); // Save encrypted log

// $logger->filename is ENCRYPTED LOG's Filename

var_dump($logger->decryptLogFile("filename","private key"));//If success, returns string.
}
```

# Can I store personal information here without any worries?
**Yes.** All data is encrypted, and can be decrypted only with matching with public, private key.

# Can logs be decrypted without private key? 
No.
# How to get public&private keys? 
You need to generate a self-signed pkcs7 certificate using OpenSSL. You can enter random data, it won't affect the encryption. Do not use the passphrase, as it's not supported at the moment.
Certificate is your publickey, must be stored in /var/log/logger/publickey.pem
Private key must be stored anywhere, but not on the encryption server. Otherwise, if server will get hacked, the logs can be decrypted, because the private key is on the same server with logger. It's like storing your backup keys under floor mat. Store it on device that you trust and that less likely will be hacked.

# Need more info?
Open an issue, or read the library itself. It's not that hard.
