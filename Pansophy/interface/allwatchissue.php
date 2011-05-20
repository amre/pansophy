<?php

/**
 * Sets all user users to watch an issue.
 */

include('../include/header.inc'); include_once('../DataAccessManager.inc.php');
$dam=new DataAccessManager();
if($_GET['all_watch']){
	$dam->setAllWatch($_GET['issue']);
}
echo '<meta http-equiv="Refresh" content="0; URL=./viewissue.php?id='.$_GET['issue'].'">';

?>
