<?php

/**
 * Page displays a text box where a user can copy and paste an interim. The text is then sent
 * to addinterim.php to be parsed.
 */

include('../include/header.inc');
include('../DataAccessManager.inc.php');
include('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();

if($dam->getAccessLevel() > 8){
	// Takes pasted interim and parses the text (in addinterim.php).
	echo '<h1>Import Interim Report</h1>
	<p class="mediumhead">Copy and paste the text from the interim report e-mail into the textbox below.<br />Start at the ID and copy down to the end of Other Recommended Action.<br />(Leave the textbox empty to manually fill out an interim.)</p>
	<form action="./addinterim.php" method="POST">
	<table width="80%" >
	<tr><td align="left"><textarea name="interim" cols="100" rows="20"></textarea></td></tr>
	</table><input type="hidden" name="submitted" value="1"><button type="submit">Process Text</button></form></body></html>';
}
else{
	echo '<script language=javascript>alert("You don\'t have access to view this page. Redirecting to the main page.");</script>
	<meta http-equiv="Refresh" content="0; URL=../index.php">';
}

?>