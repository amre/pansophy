<?php

/* This file deprecated and no longer used. It has been replaced by tasks.php.
 */
echo $html_top;
include("../include/header.inc"); include('../DataAccessManager.inc.php');
$dam = new DataAccessManager();
echo "<center><h1>Administrative Tasks</h1>";
if( $dam->userCanCreateUser( '' ) ) {
// Michael Thompson * 12/16/2005 * changed front end interface for user entry
	echo '<hr style="width: 75%"><br /><p>
	<a href="./getuser.php">Add a New User</a></p>
	<p>
	<a href="./editflags.php">Change Student Flags</a></p>';
}
if( $dam->userCanCreateOrReplaceStudent( '' ) ) {
	echo '<p>
	<a href="../import.php">Import SRN Student Records</a>';
}
echo '</p></center>';

?>
