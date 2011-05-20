<?php 
 
/**
 * Does a lookup for a wooster username when an admin wants to add a user.
 */
 
include('../include/header.inc'); 
include('../DataAccessManager.inc.php');

$dam=new DataAccessManager();

// Michael Thompson * 12/16/2005 * Created to alter the flow of the user entry / edit process

// Check to see if the user is already on file
if($_REQUEST['ID']){
	$ID = $_REQUEST['ID'];
	$result = $dam->getUserLookup($ID);
	if ($result) $test = mysql_fetch_assoc($result);
	if ($test) {
		echo '<meta http-equiv="Refresh" content="0; URL=./viewuser.php?id='.$ID.'">';
	} else {
		echo '<meta http-equiv="Refresh" content="0; URL=./adduser.php?ID='.$ID.'">';
	}
} else {

// print the form, there is no name submitted
	echo '<h1>Edit / Create User</h1>
	<form action="./getuser.php" method="POST">
	<table width="80%" >
	<tr><td align="left">Username:</td><td align="left" colspan="2"><input type="text" name="ID" maxlength="20" size=20"></td></tr>
	</table><input type="submit" name="submitted" value="Lookup User"></form>';
	
	echo '<p><br><br><a href="./tasks.php">[Return to User Tasks]</a></p>';
}
mysql_close();