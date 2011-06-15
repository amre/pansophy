<?php

/**
 * Displays list of issues in student page.
 * In other news, Josh Thomas is a member of the National Association of Black Journalists
 * and Alpha Phi Alpha Fraternity. (Again, Wiki it - I'm not making this stuff up)
 */

//+-----------------------------------------------------------------------------------------+

include_once('../DataAccessManager.inc.php');
include_once('../include/header.inc');
include_once('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

$ID = $_GET['id'];

$issues=$dam->getStudentIssues($ID);

$issuesToView = 8;
if(isset($_GET['viewallissues']) && $_GET['viewallissues']) $issuesToView = sizeof($issues);
else if(sizeof($issues) < $issuesToView) $issuesToView = sizeof($issues);

echo '<table><dl>';
if($issues){
	for($i=0; $i < $issuesToView; $i++){
		$staff=array();
// Michael Thompson * 12/07/2005 * Fixed bug in link
		if($issues[$i]['ID'] == ''){
		}
		else{
			if($dam->userCanViewIssue('', $issues[$i]['ID'])){
				echo '<tr><td><dt><a href="./viewissue.php?id='.$issues[$i]['ID'].'" TARGET="Main">
					'.$issues[$i]['ID'][0].'-'.substr($issues[$i]['ID'],1).'</a> ('.$issues[$i]['Status'].'): 
					'.stripslashes($issues[$i]['Header']);
				$contacts=$dam->issueContacts($issues[$i]['ID']);
				echo '<dd><i>Staff contacting this student:</i> ';
				for($j=0; $j<sizeof($contacts); $j++){
					if(strcmp($contacts[$j]['Issue'], $issues[$i]['ID'])==0 && !in_array($contacts[$j]['Creator'], $staff)){
						$Creator = $dam->viewUser('', $contacts[$j]['Creator']);
						if (sizeof($staff) == 0){
							echo '<a href="mailto:'.$Creator['Email'].'">'.$Creator['FirstName'].'
							'.$Creator['LastName'].'</a>';
						}
						else{
							echo ', <a href="mailto:'.$Creator['Email'].'">'.$Creator['FirstName'].'
							'.$Creator['LastName'].'</a>';
						}
						array_push($staff, $contacts[$j]['Creator']);
					}
				}
				echo '</td></tr>';
			}
		}
	}
}
else {
	echo 'There are no issues for this student.';
}
echo '</dl></table>';
?>
