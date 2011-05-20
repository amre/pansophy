<?php

/**
 * Sets a user to watch an issue.
 */

//+-----------------------------------------------------------------------------------------+

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
$dam=new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

if($_GET['watch']){
	$dam->watchIssue('', $_GET['issue']);
}
else{
	$dam->stopWatchingIssue('', $_GET['issue']);	
}
echo '<meta http-equiv="Refresh" content="0; URL=./viewissue.php?id='.$_GET['issue'].'">';

?>
