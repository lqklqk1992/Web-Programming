<?php
session_start();
include_once('lib/csrf.php');
include_once('lib/auth.php');

$em=ierg4210_auth();
if($em==false){
	header('Location: login.php', true, 302);
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>IERG4210 Change Password Panel</title>
	<style>
		fieldset{width:30%;}
	</style>
</head>
<body>
<h1>IERG4210 Change Password Panel</h1>
<fieldset>
	<legend><?php echo ($em[0]); ?></legend>
	<form id="changepwForm" method="POST" action="auth-process.php?action=<?php echo ($action = 'changepw'); ?>">
		<label for="oldpw">Old PW:</label>
		<div><input type="password" name="oldpw" required="true" pattern="^[\w@#$%^&*-]+$"/></div>
		<label for="newpw">New PW:</label>
		<div><input type="password" name="newpw" required="true" pattern="^[\w@#$%^&*-]+$"/></div>
		<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
		<input type="hidden" name="email" value="<?php echo ($em[0]); ?>"/>
		<input type="submit" value="Change" />
	</form>
</fieldset>
</body>
</html>