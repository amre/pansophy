<?php

/**
 * Handles the editing of contacts, both the interface and the processing.
 */
 
include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
include('../include/miscfunctions.inc.php');

$dam = new DataAccessManager();

// get contact info
//   Michael Thompson * 12/14/2005 * Changed id field to request instead of get
$contact = $dam->viewContact('', $_REQUEST['id']);
$keys = array_keys($contact);
for($i=0; $i<sizeof($keys); $i++){
	$$keys[$i] = $contact[$keys[$i]];
}

// display buttons
if(strcmp($_POST['submit'], 'Cancel') == 0){
	echo ' <meta http-equiv="Refresh" content="0; URL=./viewissue.php?id='.$Issue.'">';
}
if(strcmp($_POST['submit'], 'Submit Changes') == 0){
	//echo 'Form submitted<br>';
	$dam->modifyContact('', $ID, $_POST);
	echo '<meta http-equiv="Refresh" content="0; URL=./viewissue.php?id='.$Issue.'">';
}
else{
	echo '<center><p class="largecolorheading">Edit Contact '.$ID.' from Issue '.$Issue.'</p></center>';
	echo '<form action="./editcontact.php" method="POST" target="_self">
	<input type="hidden" name="id" value="'.$ID.'">
	<textarea name="Description" cols="60" rows="5">'.stripslashes($Description).'</textarea><br>
	<!--input type="hidden" name="page" value="'.$_SESSION['HTTP_REFERER'].'"-->
	<input type="submit" name="submit" value="Cancel"> <input type="submit" name="submit" value="Submit Changes">
	</form>';
}

?>
