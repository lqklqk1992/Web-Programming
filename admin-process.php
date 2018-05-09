<?php 
include_once('lib/db.inc.php');    //declare DB setting and connecting fuction: ierg4210_DB
include_once('lib/auth.php');

//cookie validation
$em=ierg4210_auth();
if(!($em==true&&$em[1]==1)){
	header('Location: login.php', true, 302); //It does not work because ajax will not return 302. Instead, it returns the response after redirection.
	//echo '{"status":302, "location":"login.php"}';     
	exit();
}


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
	else{
		echo 'while(1);' . json_encode(array('success' => cat_fetchall()));
	}
}

else{
	try {
	if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
		if ($db && $db->errorCode()) 
			error_log(print_r($db->errorInfo(), true));
		echo 'while(1);' . json_encode(array('failed'=>'1'));
	}
	else
		echo 'while(1);' . json_encode(array('success' => $returnVal));
} catch(PDOException $e) {
	error_log($e->getMessage());
	echo 'while(1);' . json_encode(array('failed'=>'error-db'));
} catch(Exception $e) {
	echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
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

function ierg4210_user_fetchall() {
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM accounts LIMIT 100;");
	if ($q->execute())
		return $q->fetchAll();
}

function ierg4210_user_insert() {
	// input validation or sanitization
	if (!preg_match('/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/', $_POST['name']))
		throw new Exception("invalid-email");
	if (!preg_match('/^.{4,8}$/', $_POST['password']))
		throw new Exception("invalid-password");
	if (!preg_match('/^(0|1)$/', $_POST['admin']))
		throw new Exception("invalid-admin value");

	$salt = mt_rand();
	$saltpassword=hash_hmac('sha1', $_POST['password'], $salt);
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("INSERT INTO accounts (email,salt,password,admin) VALUES (?,?,?,?)");
	$q=$q->execute(array($_POST['name'],$salt,$saltpassword,$_POST['admin']));
	header('Location: admin.php');
	exit();
}

function ierg4210_user_delete() {

	// input validation or sanitization
	$_POST['userid'] = (int) $_POST['userid'];

	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT admin FROM accounts WHERE userid=".$_POST['userid']);
	$q->execute();
	$admin=$q->fetchAll();
	if($admin[0]['admin']==1)
		throw new Exception("Cannot delete admin account");
	$q = $db->prepare("DELETE FROM accounts WHERE userid = ?");
	return $q->execute(array($_POST['userid']));
}

function ierg4210_cat_fetchall() {
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM categories LIMIT 100;");
	if ($q->execute())
		return $q->fetchAll();
}

function ierg4210_cat_insert() {
	// input validation or sanitization
	if (!preg_match('/^[\w\-,&\' ]+$/', $_POST['name']))
		throw new Exception("invalid-name");

	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("INSERT INTO categories (name) VALUES (?)");
	return $q->execute(array($_POST['name']));
}

function ierg4210_cat_edit() {
	// TODO: complete the rest of this function; it's now always says "successful" without doing 
	$_POST['catid'] = (int) $_POST['catid'];
	if (!preg_match('/^[\w\-,&\' ]+$/', $_POST['name']))
		throw new Exception("invalid-name");

	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("UPDATE categories SET name=? WHERE catid = ?");
	return $q->execute(array($_POST['name'],$_POST['catid']));
}

function ierg4210_cat_delete() {

	// input validation or sanitization
	$_POST['catid'] = (int) $_POST['catid'];

	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("DELETE FROM categories WHERE catid = ?");
	return $q->execute(array($_POST['catid']));
}

// Since this form will take file upload, we use the tranditional (simpler) rather than AJAX form submission.
// Therefore, after handling the request (DB insert and file copy), this function then redirects back to admin.php
function ierg4210_prod_insert() {
	// input validation or sanitization
	$_POST['catid'] = (int) $_POST['catid'];
	if (!preg_match('/^[\w\-,&\' ]+$/', $_POST['name']))
		throw new Exception("invalid-name");
	if (!preg_match('/^[\w\-,&\' ]+$/', $_POST['title']))
		throw new Exception("invalid-title");
	if (!preg_match('/^[\d]+[\.]?[\d]*$/', $_POST['price']))
		throw new Exception("invalid-price");
	if (!preg_match('/^[\w\-,\'!. ]*$/', $_POST['description']))
		throw new Exception("invalid-description");
	if (!($_FILES["file"]["error"] == 0 && $_FILES["file"]["type"] == "image/jpeg" && $_FILES["file"]["size"] < 5000000)){
		// Only an invalid file will result in the execution below
		// To replace the content-type header which was json and output an error message
		header('Content-Type: text/html; charset=utf-8');
		echo 'Invalid file detected. <br/><a href="javascript:history.back();">Back to admin panel.</a>';
		exit();
	}

	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	//have to set this attribute if you want to use lastInsertId()
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,TRUE);
	// TODO: complete the rest of the INSERT command
	$q = $db->prepare("INSERT INTO products (catid, title, name, price, description) VALUES (?,?,?,?,?)");
	$q->execute(array($_POST['catid'],$_POST['title'],$_POST['name'],$_POST['price'],$_POST['description']));
	$lastId = $db->lastInsertId();
	
	
	// The lastInsertId() function returns the pid (primary key) resulted by the last INSERT command
	

	// Copy the uploaded file to a folder which can be publicly accessible at incl/img/[pid].jpg
		// Note: Take care of the permission of destination folder (hints: current user is apache)
	if (move_uploaded_file($_FILES["file"]["tmp_name"], "incl/img/" . $lastId . ".jpg")) {
		// redirect back to original page; you may comment it during debug
		header('Location: admin.php');
		exit();
	}
}	

function ierg4210_prod_delete() {

	// input validation or sanitization
	$_POST['pid'] = (int) $_POST['pid'];
	unlink('incl/img/' . $_POST['pid'] . ".jpg");
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("DELETE FROM products WHERE pid = ?");
	return $q->execute(array($_POST['pid']));
}

function ierg4210_prod_edit() {

	// input validation or sanitization
	$_POST['pid'] = (int) $_POST['pid'];
	$_POST['catid'] = (int) $_POST['catid'];
	
	if (!preg_match('/^[\w\-,&\' ]+$/', $_POST['name']))
		throw new Exception("invalid-name");
	if (!preg_match('/^[\w\-,&\' ]+$/', $_POST['title']))
		throw new Exception("invalid-title");
	if (!preg_match('/^[\d]+[\.]?[\d]*$/', $_POST['price']))
		throw new Exception("invalid-price");
	if (!preg_match('/^[\w\-,\'!. ]*$/', $_POST['description']))
		throw new Exception("invalid-description");
	
	if(file_exists($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])){
		if (!($_FILES["file"]["error"] == 0 && $_FILES["file"]["type"] == "image/jpeg" && $_FILES["file"]["size"] < 5000000)){
		// Only an invalid file will result in the execution below
		// To replace the content-type header which was json and output an error message
			header('Content-Type: text/html; charset=utf-8');
			echo 'Invalid file detected. <br/><a href="javascript:history.back();">Back to admin panel.</a>';
			exit();
		}
		move_uploaded_file($_FILES["file"]["tmp_name"], "incl/img/" . $_POST['pid'] . ".jpg");
	}

	global $db;
	$db = ierg4210_DB();
	//have to set this attribute if you want to use lastInsertId()
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,TRUE);
	// TODO: complete the rest of the INSERT command
	$q = $db->prepare("UPDATE products SET catid= ? , title= ? , name= ? , price= ? , description= ? WHERE pid=?");
	$q->execute(array($_POST['catid'],$_POST['title'],$_POST['name'],$_POST['price'],$_POST['description'],$_POST['pid']));
	header('Location: admin.php');
	exit();
}

function ierg4210_order_fetchall() {
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM orders LIMIT 100;");
	if ($q->execute())
		return $q->fetchAll();
}

function ierg4210_order_delete() {

	// input validation or sanitization
	$_POST['oid'] = (int) $_POST['oid'];

	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("DELETE FROM orders WHERE oid = ?");
	return $q->execute(array($_POST['oid']));
}

?>
