<?php
session_start();
include_once('lib/csrf.php');
include_once('lib/auth.php');

$em=ierg4210_auth();
if($em!=false){
	if($em[1]==0)
		header('Location: index.php', true, 302);
	else
		header('Location: admin.php', true, 302);
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>IERG4210 Login Panel</title>
	<style>
		fieldset{width:30%;}
	</style>
</head>
<body>
<h1>IERG4210 Login Panel</h1>
<fieldset>
	<legend>Login Form</legend>
	<form id="loginForm" method="POST" action="auth-process.php?action=<?php echo ($action = 'login'); ?>">
		<label for="email">Email:</label>
		<div><input type="Email" name="email" required="true"/></div>
		<label for="pw">Password:</label>
		<div><input type="password" name="pw" required="true" pattern="^[\w@#$%^&*-]+$"/></div>
		<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
		<input type="submit" value="Login" />
	</form>
</fieldset>

<fieldset>
	<legend>SignUp Form</legend>
	<form id="signupForm" method="POST" action="auth-process.php?action=<?php echo ($action = 'signup'); ?>">
		<label for="email">Email:</label>
		<div><input type="Email" name="email" required="true"/></div>
		<label for="pw">Password(4-8):</label>
		<div><input type="password" name="pw" required="true" pattern="^.{4,8}$"/></div>
		<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
		<input type="submit" value="SignUp" />
	</form>
</fieldset>
</body>
</html>