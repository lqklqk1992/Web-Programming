<?php 
include_once('lib/db.inc.php');    //declare DB setting and connecting fuction: ierg4210_DB


if($_SERVER['REQUEST_METHOD']=='GET')
{
	if(isset($_GET['catid'])){
		if(isset($_GET['title'])){
			echo 'while(1);' . json_encode(array('success' => product_fetchone()));
		}
		else{
			echo 'while(1);' . json_encode(array('success' => product_fetchall()));
		}
	}
	else if(isset($_GET['pid'])){
		echo 'while(1);' . json_encode(array('success' => product_fetchone()));
	}
	else if(isset($_GET['em'])){
		$result=user_history();
		if($result=="authfailed")
			echo 'while(1);' . json_encode(array('failed' => $result));
		else
			echo 'while(1);' . json_encode(array('success' => $result));
	}else{
		echo 'while(1);' . json_encode(array('success' => cat_fetchall()));
	}
}


function cat_fetchall(){
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM categories");
	$q->execute();
	$q=$q->fetchAll();
	return $q;
}


function product_fetchall(){
	$_GET['catid'] = (int) $_GET['catid'];

	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM products where catid = ?");
	$q->bindParam(1,$_GET['catid']);  
	$q->execute();
	$q=$q->fetchAll();
	return $q;
}

function product_fetchone(){
	global $db;
	$db = ierg4210_DB();

	if(isset($_GET['pid'])){
		$_GET['pid'] = (int) $_GET['pid'];

		$q = $db->prepare("SELECT * FROM products where pid = ?");
		$q->bindParam(1,$_GET['pid']);  
	}
	else{
		$_GET['catid'] = (int) $_GET['catid'];

		if (!preg_match('/^[\w\-,&\' ]+$/', $_GET['title']))
			throw new Exception("invalid-title");

		$q = $db->prepare("SELECT * FROM products where catid = ? and title = ?");
		$q->bindParam(1,$_GET['catid']);  
		$q->bindParam(2,$_GET['title']);
	}
	
	$q->execute();
	$q=$q->fetchAll();
	return $q;
}

function user_history(){
	if(!empty($_COOKIE['t4210'])){
		if($t=json_decode(stripslashes($_COOKIE['t4210']),true)){		
			if(time()>$t['exp']) return "authfailed";
			if(strcmp($t['em'], $_GET['em']) != 0) return "authfailed";

			$db = ierg4210_DB();
			$q=$db->prepare('SELECT * FROM history WHERE email = ?');	
			$q->execute(array($t['em']));
			$q=$q->fetchAll();
			return $q;
		}
	}
	return "authfailed";
}
?>
