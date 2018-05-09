<?php 
include_once('lib/db.inc.php');    //declare DB setting and connecting fuction: ierg4210_DB


if($_SERVER['REQUEST_METHOD']=='POST')
{
	try{
		$priceArray = array();
		$totalprice=0;
		$salt = mt_rand();
		$plaintext="HKD471794027-facilitator@qq.com";
		date_default_timezone_set("Asia/Hong_Kong");
		$createdtime=date('Y-m-d')." ".date('h:i:sa');
		$user="guest";
		$user_record="";

		foreach ($_POST as $param_name => $param_val) {
			if(strcmp($param_name, "user") == 0){
    			$user=$param_val;
    		}else{
				$param_val["pid"]=(int)$param_val["pid"];
				$param_val["quantity"]=(int)$param_val["quantity"];
				$price=product_getprice($param_val["pid"]);
				$name=product_getname($param_val["pid"]);
    			$totalprice=$totalprice+$price["price"]*$param_val["quantity"];
    			$plaintext=$plaintext.$param_val["pid"].$param_val["quantity"].$price["price"];
    			$priceArray[$param_name]=$price["price"];
				$user_record=$user_record."name:".$name["title"]." quantity:".$param_val["quantity"]." price:".$price["price"]."; ";
			}   		
		}
		$plaintext=$plaintext.$totalprice;
		$user_record=$user_record."date:".$createdtime." total:".$totalprice;
		$digest = hash_hmac('sha1',$plaintext, $salt);
		$lastInsertId=insert_order($digest,$salt,$createdtime);

		if(strcmp($user, "guest") != 0){
			insert_history($user,$digest,$user_record);
		}
		echo 'while(1);' . json_encode(array('success' => array("invoice" => $lastInsertId,"custom" => $digest,"price"=>$priceArray)));
	}catch(PDOException $e) {
		error_log($e->getMessage());
		echo 'while(1);' . json_encode(array('failed'=>'error-db'));
	} catch(Exception $e) {
		echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
	}
}


function product_getprice($pid){

	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT price FROM products where pid = ?");
	$q->bindParam(1,$pid);  
	$q->execute();
	$q=$q->fetch();
	return $q;
}

function product_getname($pid){

	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT title FROM products where pid = ?");
	$q->bindParam(1,$pid);  
	$q->execute();
	$q=$q->fetch();
	return $q;
}

function insert_order($digest,$salt,$createdtime){

	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("INSERT INTO orders (digest,salt,createdtime) VALUES (?,?,?)");
	$q=$q->execute(array($digest,$salt,$createdtime));
	return $db->lastInsertId(); 
}

function insert_history($user,$digest,$user_record){

	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("INSERT INTO history (email,digest,data) VALUES (?,?,?)");
	$q->execute(array($user,$digest,$user_record)); 
}

?>
