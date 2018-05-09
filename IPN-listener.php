<?php 
include_once('lib/db.inc.php');    //declare DB setting and connecting fuction: ierg4210_DB


header('HTTP/1.1 200 OK');

// read the IPN notification from PayPal and add the 'cmd' parameter to the beginning of the acknowledgement you will send back
$req = 'cmd=_notify-validate';

// Loop through the notification name-value pairs
foreach ($_POST as $key => $value) {
	// Encode the values
	$value = urlencode(stripslashes($value));
    // Add the name-value pairs to the acknowledgement
	$req .= "&$key=$value";
}
// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.1\r\n";

// Set up other acknowledgement request headers
$header .= "Host: www.sandbox.paypal.com:443\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";


// Open a socket for the acknowledgement request
// If testing on Sandbox use:
$fp = fsockopen('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
// For live servers use $fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

// Send the HTTP POST request back to PayPal for validation
fputs($fp, $header . $req);
//error_log($header . $req);
while (!feof($fp)) {
		// While not EOF
	$res = trim(fgets($fp, 1024));
		// Get the acknowledgement response
	if (strcmp($res, "VERIFIED") == 0) {
		// Authentication protocol is complete - OK to process notification contents
		// Possible processing steps for a payment include the following:
		$plaintext="HKD471794027-facilitator@qq.com"; 
		$invoice="invoice";
		$custom="custom";
		$tid="tid";

		foreach ($_POST as $key => $value) {
			// Check that the payment_status is Completed
			//error_log($key);
			//error_log($value);
			if(strcmp($key, "payment_status") == 0){
				if(strcmp($value, "Completed") != 0){
				 	error_log("payment_status is not completed!");
				 	exit();
				 }
			}
			// Check that txn_id has not been previously processed
			if(strcmp($key, "txn_id") == 0){
				global $db;
				$db = ierg4210_DB();
				$q = $db->prepare("SELECT COUNT(*) FROM orders WHERE tid = ?");
				$q->execute(array($value));
				$number_of_rows = $q->fetchColumn();
				error_log($number_of_rows);
				
				if($number_of_rows>=1){
					error_log("duplicate transactions!");
				 	exit();
				 }
				
				$tid=$value;
			}
			// Check the txn_type
			if(strcmp($key, "txn_type") == 0){
				if(strcmp($value, "cart") != 0){
					error_log("wrong txn_type!");
				 	exit();
				 }
			}
			// Check that the receiver_email is your Primary PayPal email
			if(strcmp($key, "receiver_email") == 0){
				if(strcmp($value, "471794027-facilitator@qq.com") != 0){
					error_log("wrong receiver_email!");
				 	exit();
				 }
			}
			// Check that payment_amount/payment_currency are correct
			if(strcmp($key, "invoice") == 0){
				$invoice=$value;
			}
			if(strcmp($key, "custom") == 0){
				$custom=$value;
			}		
		}

		// Process payment
		global $db;
		$db = ierg4210_DB();
		$q = $db->prepare("SELECT digest FROM orders WHERE oid= ?");
		$q->execute(array($invoice));
		$q=$q->fetch();
		$digest=$q['digest'];
		if(strcmp ($digest, $custom) == 0){
			$q = $db->prepare("UPDATE orders SET tid=? WHERE oid = ?");
			$q->execute(array($tid,$invoice));	
			$q = $db->prepare("UPDATE history SET complete='Y' WHERE digest = ?");
			$q->execute(array($digest));
		}else{
			error_log("digests not consistent!");
			exit();
		}
	} else if (strcmp ($res, "INVALID") == 0) { 
		//Response contains INVALID - reject notification
		// Authentication protocol is complete - begin error handling
		error_log("notification rejected by paypal!");
		exit();	
	}	
}
fclose ($fp);
?>