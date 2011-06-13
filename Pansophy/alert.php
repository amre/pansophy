<?php

/**
 * Controls the alert box at the bottom of the page.
 */
include_once('./include/mainheader.inc'); 
include_once('./DataAccessManager.inc.php');
$dam=new DataAccessManager();

include('./include/header.inc');

function printPage() {
	global $dam;
	echo '<table width="100%" cellpadding="5" class="darkbg" cellspacing="6">';
	/* getUserAlerts() checks for any issues, students, users, student interims that have been
	 * added and shows that you have alerts.
	 */
	if( $dam->getUserAlerts() )
		printWithAlerts();
	else 
		printWithoutAlerts();
	echo '</table>';
}

function printWithoutAlerts() {
	echo ' <meta http-equiv="Refresh" content="300; URL=./alert.php">';
	echo '<table  width="100%" cellpadding="5" class="darkbg" cellspacing="6">
	<td align="left" height="25" width="100%" class="lightbg">You currently have no alerts.</td>';
	echo '<td align="right" height="25" nowrap><p class="mediumcolorheading">Phronesis&reg Contact Manager</td></table>';
}

function printWithAlerts() {
	echo ' <meta http-equiv="Refresh" content="30; URL=./alert.php">';
	echo '<table  width="100%" cellpadding="5" class="redbg" cellspacing="6">
	<td align="left" height="25" width="100%" class="lightbg"><a href="./interface/alerts.php" target="Main" class="alert">[You have new alerts]</a></td>';
	echo '<td align="right" height="25" nowrap><p class="mediumlightcolorheading">Phronesis&reg Contact Manager</td></table>';
}

printPage();



?>
