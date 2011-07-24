<?php

/**
 * Added a starting php tag to this file. However, seeing as the file came without the tag,
 * I'm led to believe that this file isn't used currently by Pansophy to authenticate against
 * the LDAP server. - Josh Thomas
 */

$username=$_POST['username'];
$username = explode('.', $username);
$userid=$username[0];
$username = 'cn='.$username[0].',ou='.$username[1].',ou='.$username[2].',o=wooster';
$pass=$_POST['pass'];
$ldapaddress='ldap.wooster.edu';
$ldap=ldap_connect($ldapaddress);
if(@ldap_bind($ldap, $username, $pass)){
	//it worked, so start this session	
	session_id();
	session_set_cookie_params(900);
	ini_set('session.use_only_cookies', false);
	session_start();
	$_SESSION['userid']=$userid;
	echo 'Holy crap, it works.';
	echo $_SESSION['userid'];
	
}
else
{
	/*This page exists to authenticate against an LDAP server and then set up 
	a PHP session assuming the authentication is successful.*/
	$username=$_POST['username'];
	$username = explode('.', $username);
	$userid=$username[0];
	$username = 'cn='.$username[0].',ou='.$username[1].',ou='.$username[2].',o=wooster';
	$pass=$_POST['pass'];
	$ldapaddress='ldap.wooster.edu';
	$ldap=ldap_connect($ldapaddress);
	if(@ldap_bind($ldap, $username, $pass)){
		//it worked, so start this session	
		session_id();
		session_set_cookie_params(900);
		ini_set('session.use_only_cookies', false);
		session_start();
		$_SESSION['userid']=$userid;
		echo 'Holy crap, it works.';
		echo $_SESSION['userid'];
	
	}
	else
	{
		//The authentication did not work.  Print an error message.
		echo 'Unable to autheniticate with LDAP server.  Please try again or check with your system administrator.';
	}
}

?>
