<?php 

/**
 * This is the login page.  It handles login and authentication.  Joel gave it an ugly background,
 * but that should go away soon.
 */

@session_start();

include_once('../include/loginheader.inc');
include('../DataAccessManager.inc.php');
$dam=new DataAccessManager();

//If there is no username set, they must not have logged in yet  Print out the login form.
if(!isset($_POST['username'])){
	//print login form
	echo '<center>
		<div style="display: table; height: 300px; overflow: hidden; margin: auto; text-align: center;">
		<div style="_top:  50%; display: table-cell; vertical-align: middle;">
		<div style="_top: -50%; width:500px; height: 200px" class="blackwithborder">
		<form action="./login.php" method="post">
		<h1  align="center">Welcome to Phronesis&reg; Contact Manager</h1>
		<p  class="mediumcolorheading" align="center">Please log in <a href="../help/index.html" class="lightcolor" target="_blank">[Help]</a></p>
		<p  class="lightcolortext" align="center">Username: <input type="text"  name="username" tabindex="1" /></p>
		<p class="lightcolortext"  align="center">Password: <input type="password" name="pass"  tabindex="2" /></p>
		<center><input type="submit" value="Login"  tabindex="3" /></center></form>
		</div>
		</div>
		</div>
	</center>';
}
else{
	if($_POST['username'] && $_POST['pass']){
		/*This part of the scripts exists to authenticate against an LDAP server and then set up 
		a PHP session assuming the authentication is successful.*/
		$username=$_POST['username'];
		$context=$dam->getUserContext($username);
		$username = explode('.', $username);
		$userid=$username[0];
		$username = 'cn='.$username[0].',ou='.$context[0].',ou='.$context[1].',o=wooster';
		$pass=$_POST['pass'];
		$ldapaddress='ldap.wooster.edu';
		$link=ldap_connect($ldapaddress);
		$islockedout = $dam->isLockedOut($userid);
		if(@ldap_bind($link, $username, $pass) && !$islockedout){
			//it worked, so start this session
			session_set_cookie_params(0);
			$dam->successfulLoginAttempt($userid);
			$_SESSION['userid']=$userid;
			if($dam->getAccessLevel('') > 0){
				echo '<meta http-equiv="Refresh" content="0; URL=../index.php">';
			}
			else{
				echo 'Access denied.';
            			echo '<meta http-equiv="Refresh" content="3; URL=./login.php">';
			}
		}
		else{
			//The authentication did not work.  Print an error message.
			if($islockedout){
			echo 'This username has been temporarily locked out of the system for too many incorrect login attempts. Please wait a few minutes and try again';
			}
			else{
			echo 'Username or password incorrect.  Please try again or check with your system administrator.';
			$dam->failedLoginAttempt($userid);
			}
		   echo '<meta http-equiv="Refresh" content="3; URL=./login.php">';
		}
	}
	else{
		echo 'You must fill in both the user name and the password.';
		//if(!$index){
			echo '<meta http-equiv="Refresh" content="3; URL=./login.php">';
		//}
		/*if($index){
			echo '<meta http-equiv="Refresh" content="3; URL=./login/login.php">';
		}*/
	}
}
echo '
</body>
</html>';
?>
