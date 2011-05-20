<?php

/**
 * Admin page that allows student flags to be customizable.
 */

include('../include/header.inc');
include('../DataAccessManager.inc.php');
$dam=new DataAccessManager();

//check if user has access to page
if($dam->userCanCreateUser('')){
	if(strcmp($_POST['submitted'], '1') == 0){
		$F1 = $_POST['Field1'];
		$F2 = $_POST['Field2'];
		$F3 = $_POST['Field3'];
		
		// Removes spaces from entered text to prevent problems with flags
		$res = strpos($F1, " ");
		if($res !== false)
		{
			$F1 = str_replace(" ", "", $F1);
		}
		$res = strpos($F2, " ");
		if($res !== false)
		{
			$F2 = str_replace(" ", "", $F2);
		}
		$res = strpos($F3, " ");
		if($res !== false)
		{
			$F3 = str_replace(" ", "", $F3);
		}
		
		// Array to remove all special characters
		$chars = array("!","@","`","~","#","\$","%","^","&","*","(",")","_","-","+","=",",","<",
		">",".","/","?","[","{","]","}","\'","\"",";",":","|","\\");
		
		$F1 = str_replace($chars, "", $F1);
		$F2 = str_replace($chars, "", $F2);
		$F3 = str_replace($chars, "", $F3);
		
		//Update flags where text is entered
		
		$dam->updateFlags( $F1, $F2, $F3 );
		
		// Javascript command to refresh the Menu frame to include the new options
		echo "<script languague=javascript>parent.Menu.window.location.reload();</script>";

		echo '<meta http-equiv="Refresh" content="0; URL=./editflags.php">';
	}
	
	if(!$_POST['submitted']){
		//Display current editable flags
		$results = $dam->extractFlags();
		extract($results);
		
		if($Option1 == ''){
			$Option1a = "No Flag Set";
		}
		else{
			$Option1a = $Option1;
		}
		if($Option2 == ''){
			$Option2a = "No Flag Set";
		}
		else{
			$Option2a = $Option2;
		}
		if($Option3 == ''){
			$Option3a = "No Flag Set";
		}
		else{
			$Option3a = $Option3;
		}
		
		//Print the page
		echo '<h1>Change User Flags</h1>
		<p class="mediumhead">Type new flags in the text fields or leave them blank to keep the flag the same. (Type "remove" (without quotes) to drop the flag entirely.)<br /><br />Flags that have spaces or special characters in them will have them removed.</p>
		<form action="./editflags.php" method="POST">
		<table width="80%" >
		<tr><td align="left"><b>Current Flags</b></td><td>&nbsp;</td><td align="left"><b>New Flags</b></td></tr>
		<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
		<tr><td align="left">'.$Option1a.'</td><td>&nbsp;</td><td align="left"><input type="text" name="Field1" maxlength="40" size=40"></td></tr>
		<tr><td align="left">'.$Option2a.'</td><td>&nbsp;</td><td align="left"><input type="text" name="Field2" maxlength="40" size=40"></td></tr>
		<tr><td align="left">'.$Option3a.'</td><td>&nbsp;</td><td align="left"><input type="text" name="Field3" maxlength="40" size=40"></td></tr>
		</table><br /><input type="hidden" name="submitted" value="1"><button type="submit">Submit Changes</button></form>';
		
		echo '<p><br><br><a href="./tasks.php">[Return to User Tasks]</a></p></body></html>';
	}
}
else{
	echo '<script language=javascript>alert("You don\'t have access to view this page. Redirecting to the main page.");</script>
	<meta http-equiv="Refresh" content="0; URL=../index.php">';
}
?>