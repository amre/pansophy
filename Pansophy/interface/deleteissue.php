<?php

/**
 * This script deletes issues.  Apparently PHP doesn't return anything on a mysql_query() if the query is a delete statement, so we
 * just always assume that the deletion is successful.  Also, the page calling this script my provide the ID of the issue to be deleted
 * through the get line.
 */

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');

$dam = new DataAccessManager();
$ID=$_GET['ID'];
if(strcmp($_POST['submit'], 'Yes')  == 0){
	$dam->deleteIssue('', $ID);
	echo '<meta http-equiv="Refresh" content="0; URL=../main.php?">';
}

else if(strcmp($_POST['submit'], 'No') == 0){
	echo '<meta http-equiv="Refresh" content="0; URL=./viewissue.php?id='.$ID.'">';
}

else{
	echo "Do you really want to delete Issue $ID from the system?";
	echo '<form action="./deleteissue.php?ID='.$ID.'" method="POST" target="_self">
		<input type="submit" name="submit" value="Yes"> <input type="submit" name="submit" value="No">
	</form>';
}
?>
