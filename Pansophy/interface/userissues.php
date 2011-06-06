<?php

/**
 * Displays list of issues created by a particular user on user profile page.
 */

//+-----------------------------------------------------------------------------------------+

include_once('../DataAccessManager.inc.php');
include_once('../include/header.inc');
include_once('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

// Get user ID
$ID = $_GET['id'];


// A flag is passed through GET indicating that all issues related to
// the user should be displayed. There can be a ton of issues so it
// can take a long time to query.
if(isset($_GET['viewallissues']) && $_GET['viewallissues']){
   $issues=$dam->getUserIssues($ID,0); // retrieve all issues
}
else{
   $issues=$dam->getUserIssues($ID,1); // retrieve only most recent
}
   

// Display table.
echo '<table><dl>';
if($issues){
	for($i=0; $i < sizeof($issues); $i++){
		$Students = explode(',', $issues[$i]['Students']);
		echo '<tr><td><dt><a href="./viewissue?id='.$issues[$i]['ID'].'" TARGET="Main">'.$issues[$i]['ID'][0].'-'.substr($issues[$i]['ID'],1).'</a> ('.$issues[$i]['Status'].'): '.stripslashes($issues[$i]['Header']);
		echo '<dd><i>Students involved:</i> ';
		for($j=0; $j<count($Students); $j++){
			$Student=$dam->ViewStudent('',$Students[$j]);
			$FirstName=$Student['FIRST_NAME'];
			$LastName=$Student['LAST_NAME'];
			echo '<a href="./viewstudent.php?id='.$Students[$j].'">'.$FirstName.' '.$LastName.'</a>';
			if(!($j == count($Students)-1)){
				echo ', ';
			}
		}
		echo '</td></tr>';
	}
}
else {
	echo 'There are no issues for this user.';
}
echo '</dl></table>';
?>
