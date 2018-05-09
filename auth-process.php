<?php
// init $_SESSION
session_start();

function ierg4210_signup(){
	if (!preg_match('/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/', $_POST['email']))
		throw new Exception("invalid-email");
	if (!preg_match('/^.{4,8}$/', $_POST['pw']))
		throw new Exception("invalid-password");
	// Implement the login logic here
	global $db;
	$db = ierg4210_DB();
	$q=$db->prepare('SELECT * FROM accounts WHERE email = ?');
	$q->execute(array($_POST['email']));
	
	if($r=$q->fetch()){
		throw new Exception('Email has already been registered');
	}
	
	$salt = mt_rand();
	$saltpassword=hash_hmac('sha1', $_POST['pw'], $salt);
	$q = $db->prepare("INSERT INTO accounts (email,salt,password,admin) VALUES (?,?,?,?)");
	$q=$q->execute(array($_POST['email'],$salt,$saltpassword,0));
	header('Refresh: 5; url=login.php');
	echo '<strong>Registration done!</strong><br/>Redirecting to login page in 5 seconds...';
	exit();
}

function ierg4210_login(){
	if (empty($_POST['email']) || empty($_POST['pw']) 
		|| !preg_match("/^[\w=+\-\/][\w='+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST['email'])
		|| !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['pw']))
		throw new Exception('Wrong Credentials');
	// Implement the login logic here
	global $db;
	$db = ierg4210_DB();
	$q=$db->prepare('SELECT * FROM accounts WHERE email = ?');
	$q->execute(array($_POST['email']));
	$login_success=False;
	if($r=$q->fetch()){
		$saltPassword=hash_hmac('sha1',$_POST['pw'],$r['salt']);
		if($saltPassword==$r['password']){
			$exp=time()+3600*24*3;
			$token=array('em'=>$r['email'],'exp'=>$exp,'k'=>hash_hmac('sha1',$exp.$r['password'],$r['salt']));
			setcookie('t4210',json_encode($token),$exp,'','',false,true);
			$_SESSION['t4210']=$token;
			$login_success=True;
		}
	}
	
	
	if ($login_success){
		if($r['admin']==1)
			header('Location: admin.php', true, 302);
		else
			header('Location: index.php', true, 302);
		exit();
	} else
		throw new Exception('Wrong Credentials');
}

function ierg4210_logout(){
	// clear the cookies and session
	setcookie("PHPSESSID","",time()-300);
	setcookie("t4210","",time()-300);
	session_destroy();
	// redirect to login page after logout
	header('Location: login.php', true, 302);
	exit();
}

function ierg4210_changepw(){
	if (empty($_POST['oldpw']) || empty($_POST['newpw']) 
		|| !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['oldpw'])
		|| !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['newpw']))
		throw new Exception('invalid-input');	
	if (!preg_match('/^.{4,8}$/', $_POST['oldpw'])||!preg_match('/^.{4,8}$/', $_POST['newpw']))
		throw new Exception('invalid-input');
	if($_SESSION['t4210']['em']!=$_POST['email'])
		throw new Exception('tampered-request');

	global $db;
	$db = ierg4210_DB();
	$q=$db->prepare('SELECT * FROM accounts WHERE email = ?');
	$q->execute(array($_POST['email']));
	$login_success=False;
	if($r=$q->fetch()){
		$saltPassword=hash_hmac('sha1',$_POST['oldpw'],$r['salt']);
		if($saltPassword==$r['password']){
			$newsaltPassword=hash_hmac('sha1', $_POST['newpw'], $r['salt']);
			$q = $db->prepare("UPDATE accounts SET password=? WHERE email=?");
			$q=$q->execute(array($newsaltPassword,$_POST['email']));
			
		}else{
			throw new Exception('wrong-oldpw');
		}
	}

	setcookie("PHPSESSID","",time()-300);
	setcookie("t4210","",time()-300);
	session_destroy();

	header('Refresh: 5; url=login.php');
	echo '<strong>Password has been changed successfully!</strong><br/>Redirecting to login page in 5 seconds...';
	exit();
}







header("Content-type: text/html; charset=utf-8");

try {
	// input validation
	if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action']))
		throw new Exception('Undefined Action');
	
	// check if the form request can present a valid nonce
	if($_REQUEST['action']=='login' || $_REQUEST['action']=='signup' || $_REQUEST['action']=='changepw'){
		include_once('lib/csrf.php');
		csrf_verifyNonce($_REQUEST['action'], $_POST['nonce']);
	}
	// run the corresponding function according to action
	include_once('lib/db.inc.php');    
	if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
		if ($db && $db->errorCode()) 
			error_log(print_r($db->errorInfo(), true));
		throw new Exception('Failed');
	} else {
		// no functions are supposed to return anything
		// echo $returnVal;
	}

} catch(PDOException $e) {
	error_log($e->getMessage());
	header('Refresh: 10; url=login.php?error=db');
	echo '<strong>Error Occurred:</strong> DB <br/>Redirecting to login page in 10 seconds...';
} catch(Exception $e) {
	$re=$e->getMessage();
	if($re=='invalid-input'||$re=='wrong-oldpw'||$re=='tampered-request'){
		header('Refresh: 10; url=change-password.php?error=' . $e->getMessage());
		echo '<strong>Error Occurred:</strong> ' . $e->getMessage() . '<br/>Redirecting to change-password page in 10 seconds...';
	}
	else{
		header('Refresh: 10; url=login.php?error=' . $e->getMessage());
		echo '<strong>Error Occurred:</strong> ' . $e->getMessage() . '<br/>Redirecting to login page in 10 seconds...';
	}
}
?>