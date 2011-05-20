<?php 

/**
 * Form to add a new user
 */

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');

$dam=new DataAccessManager();

/*This script handles the input and processing of the fields required for user creation.*/

if($_POST['submitted']){ //Check if there is an existing form submission
	if($_POST['submitted']){	//Check to be sure nothing was null earlier.
		//Create the user
		$result = $dam->createUser( '', $_POST );
		echo '<meta http-equiv="Refresh" content="0; URL=./viewuser.php?id='.$_POST['ID'].'">';
	}
}
if(!$_POST['submitted']){
// Michael Thompson * 12/16/2005 * Made it look for variables off the GET / POST, Added search for ldap context
	$ID       = $_REQUEST['ID'];
	$Context1 = $_REQUEST['Context1'];
	$Context2 = $_REQUEST['Context2'];
        if (!$Context1) {
		include ('../include/LDAPLookup.php');
        }
	echo '<h1>Create a New User</h1>
	<p class="mediumhead">NOTE: This feature is still highly vulnerable to user error.  Please choose inputs carefully.</p>
	<form action="./adduser.php" method="POST">
	<table width="80%" >
	<tr><td align="left">Username:</td><td align="left" colspan="2"><input type="text" name="ID" maxlength="20" size=20" value="'.$ID.'"></td></tr>
	<tr><td align="left">User context 1:</td><td align="left"><input type="text" name="Context1" maxlength="30" size=30" value="'.$Context1.'"></td></tr>
	<tr><td align="left">User context 2:</td><td align="left"><input type="text" name="Context2" maxlength="30" size=30" value="'.$Context2.'"></td></tr>
	<tr><td align="left">Last name:</td><td align="left"><input type="text" name="LastName" maxlength="30" size=20"></td></tr>
	<tr><td align="left">First name:</td><td align="left"><input type="text" name="FirstName" maxlength="20" size=20"></td></tr>
	<tr><td align="left">Middle initial(s):</td><td align="left"><input type="text" name="MiddleIn" maxlength="3" size=3"></td></tr>
	<tr><td align="left">Email address:</td><td align="left"><input type="text" name="Email" maxlength="30" size=20"></td></tr>
	<tr><td align="left">Phone extension:</td><td align="left"><input type="text" name="Extension" maxlength="4" size=4"></td></tr>
	<tr><td align="left">Security access level:</td><td align="left">
		<select size="1" name="AccessLevel">
			<option value="0">No Access</option>
			<option value="4">Read Only User (Normal)</option>
			<option value="5">Read Only User (Full)</option>
			<option value="7" SELECTED>Normal</option>
			<option value="8">First Watch</option>
			<option value="9">Privileged</option>
			<option value="10">Administrative User</option>
		</select>
	</td></tr>
	</table><input type="hidden" name="submitted" value="1"><button type="submit">Create User</button></form>';
	
	mysql_close();
}
