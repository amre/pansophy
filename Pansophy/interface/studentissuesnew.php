<?php

/**
 * Displays list of issues in student page.
 */

//+-----------------------------------------------------------------------------------------+

include_once('../DataAccessManager.inc.php');
include_once('../include/header.inc');
include_once('../include/miscfunctions.inc.php');
include_once('./issuecontactsnew.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

$ID = $_GET['id'];
$expand = $_GET['expand'];

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
				if (strpos($expand,$issues[$i]['ID']) !== FALSE )
				{// fix below
					$urlstring = preg_replace('/'.$issues[$i]['ID'].'/','',$expand);
					$expanded=TRUE;
				}
				else
				{
					if (strcmp($expand,"")==0) $urlstring = $issues[$i]['ID'];
					else{
					$urlstring = $expand.$issues[$i]['ID'];
					}
					$expanded=FALSE;
				}
				if ($expanded)
				{
					echo '<tr><td><center><a href="./viewstudent.php?id='.$ID.'&expand='.$urlstring.'" style="font-size:16px;border: solid 3px #660000;width:20px;height: 16px;padding-bottom:4px;display:block;" TARGET="Main"><b>-</b></a></center></td><td><dt><a href="./viewissue.php?id='.$issues[$i]['ID'].'" >';
				}
				else
				{
					echo '<tr><td><center><a href="./viewstudent.php?id='.$ID.'&expand='.$urlstring.'" style="font-size:16px;border: solid 3px #660000;width:20px;height:16px;padding-bottom:4px;display:block;" >+</a></center></td><td><dt><a href="./viewissue.php?id='.$issues[$i]['ID'].'" TARGET="Main">';
				}
				
					echo $issues[$i]['ID'][0].'-'.substr($issues[$i]['ID'],1).'</a> ('.$issues[$i]['Status'].'): 
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
				echo '</dd></td></tr>';
				if($expanded)
				{	
					echo '<tr><td align="right" valign="top"><a style="border: solid 1px #660000;display:block;right:0%;top:0%;height:50%;width:50%;" href="./addcontact.php?issueid='.$issues[$i]['ID'].'&isnewissue=0">+&nbsp<title="Add a contact to this issue" border="0" style="margin:0"/></a></right></td><td>';
					$contactstring=getIssuedContacts($issues[$i]['ID']);
					echo $contactstring;
					echo '</td></tr>';
				}
			}
		}
	}
}
else {
	echo 'There are no issues for this student.';
}
echo '</dl></table>';
?>
