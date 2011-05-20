<?php

/**
 * it lists whatever tasks the current user can do and gives
 * links to them. Replaces admin.php.
 */
 
//+-----------------------------------------------------------------------------------------+ 
 
include("../include/header.inc"); 
include('../DataAccessManager.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

echo '<div align="center"><h1>User Tasks</h1>';

// Michael Thompson * 12/16/2005 * changed front end interface for user entry

echo '<hr style="width: 75%" /><br /><table width="30%" border="0" align="center"><tr align="center"><td align="center"><div align="center">';
if( $dam->userCanViewFW('') ) {
	echo '<p><div align="left"><a href="./viewfirstwatch.php">[Students Currently on First Watch]</a></div></p>';
}
if( $dam->userCanModifyFW('') ){
	echo '<p><div align="left"><a href="./addfw.php">[Place Students on First Watch]</a></div></p>';
}
if( $dam->userCanCreateInterim('') ) {
	echo '<p><div align="left"><a href="./addinterim.php">[Interim Creator]</a></div></p>';
}
if( $dam->userCanCreateUser('') ) {
	echo '<p><div align="left"><a href="./getuser.php">[Add a New User]</a></div></p>
	<p><div align="left"><a href="./editflags.php">[Change Student Flags]</a></div></p>
	<p><div align="left"><a href="./clearcounter.php">[Clear Interim Counter]</a></div></p>
	<p><div align="left"><a href="./changeemails.php">[Change Writing/Learning/Math Center e-mails]</a></div></p>';
}
// Not used anymore.
//if( $dam->userCanCreateOrReplaceStudent( '' ) ) {
//	echo '<p><div align="left"><a href="../import.php">[Import SRN Student Records]</a></div></p>';
//}
echo '</div></td></tr></table></div></body></html>';




?>
