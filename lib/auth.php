<?php
include_once('db.inc.php'); 

function ierg4210_auth(){
	if(!empty($_SESSION['t4210'])){		
		$db = ierg4210_DB();
		$q=$db->prepare('SELECT * FROM accounts WHERE email = ?');	
		$q->execute(array($_SESSION['t4210']['em']));
		if($r=$q->fetch()){
			return array($_SESSION['t4210']['em'],$r['admin']);
		}
	}

	if(!empty($_COOKIE['t4210'])){
		if($t=json_decode(stripslashes($_COOKIE['t4210']),true)){		
			if(time()>$t['exp']) return false;
			$db = ierg4210_DB();
			$q=$db->prepare('SELECT * FROM accounts WHERE email = ?');	
			$q->execute(array($t['em']));
			if($r=$q->fetch()){
				$realk=hash_hmac('sha1',$t['exp'].$r['password'],$r['salt']);
				if($realk==$t['k']){
					$_SESSION['t4210']=$t;
					return array($t['em'],$r['admin']);
				}
			}
		}
	}

	return false;
}

?>