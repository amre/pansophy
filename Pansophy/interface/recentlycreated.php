<?php

/**
 * Displays recently created issues.
 */
 

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
include('../include/miscfunctions.inc.php');

$dam=new DataAccessManager();

if(isset($_GET['limit'])){
	$limit = $_GET['limit'];
}
else {
	$limit = 30;
}

$issues = $dam->powerSearch('issues', "ORDER BY DateCreated DESC LIMIT $limit");
//print_r($issues);
echo '<h1>Recently Created Issues</h1>
<table width="100%" border="1" cellspacing="5" cellpadding="3" class="darkbg">
<tr><td valign="top" align="left" width="50%" class="lightbg">
<h2>Open/Special Case</h2>';
for($i=0; $i<sizeof($issues); $i++){
	if(strcmp($issues[$i]['Status'], 'Open') == 0 || strcmp($issues[$i]['Status'], 'Special Case') == 0){
		echo '<br><a href="./viewissue.php?id='.$issues[$i]['ID'].'">'.$issues[$i]['ID'].' ('.$issues[$i]['Status'].')</a> - '.stripslashes_all($issues[$i]['Header']);
	}
}
echo '</td><td valign="top" align="left" width="50%" class="lightbg">
<h2>Closed</h2>';
for($i=0; $i<sizeof($issues); $i++){
	if(strcmp($issues[$i]['Status'], 'Closed') == 0){
		echo '<br><a href="./viewissue.php?id='.$issues[$i]['ID'].'">'.$issues[$i]['ID'].'</a> - '.stripslashes_all($issues[$i]['Header']);
	}
}
echo '</td>
</table>';
?>
