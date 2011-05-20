<?php

/**
 * Sets a user to watch a student.
 */

//+-----------------------------------------------------------------------------------------+

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
$dam=new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

$id = $_GET['id'];
if($_GET['watch']){
	$dam->watchStudent('', $id);
}
else{
	$dam->stopWatchingStudent('', $id);	
}
echo '<meta http-equiv="Refresh" content="0; URL=./viewstudent.php?id='.$id.'">';

?>
