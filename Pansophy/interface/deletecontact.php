<?php
/**
 * This script deletes contacts.  Apparently PHP doesn't return anything on a mysql_query() if the query is a delete statement, so we
 * just always assume that the deletion is successful.  Also, the page calling this script my provide the ID of the contact to be deleted
 * through the get line.
 */

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');

$dam = new DataAccessManager();
$ID=$_REQUEST['ID'];
$contact=$dam->viewContact('', $ID);
$Issue=$contact['Issue'];
if(strcmp($_POST['submit'], 'Yes')  == 0){
	$dam->deleteContact('', $ID);
	if(!$dam->powerSearch('issue', "WHERE `Issue` = '$Issue'")){
		echo '<meta http-equiv="Refresh" content="0; URL=../main.php?">';
	}
	else{
		echo '<meta http-equiv="Refresh" content="0; URL=./viewissue.php?id='.$Issue.'">';
	}
}

else if(strcmp($_POST['submit'], 'No') == 0){
	echo '<meta http-equiv="Refresh" content="0; URL=./viewissue.php?id='.$Issue.'">';
}

else{
	echo "Do you really want to delete contact $ID from the system?";
	if(!$dam->powerSearch('contacts', "WHERE `Issue` = '$Issue' AND `ID` != '$ID'")){
		echo '<br>This is the last contact in the issue and will delete the issue as well.';
	}
	echo '<form action="./deletecontact.php" method="POST" target="_self">
                <input type="hidden" name="ID" value="'.$ID.'">
		<input type="submit" name="submit" value="Yes"> <input type="submit" name="submit" value="No">
	</form>';
}
?>
