<?php

/**
 * Displays all the contacts of an issue in the viewissue page.
 */

include_once('../include/header.inc'); 
include_once('../DataAccessManager.inc.php');
include_once('../include/miscfunctions.inc.php');

$dam = new DataAccessManager();

include_once('../include/filescript.inc');

$ID = $_GET['id'];
$contacts=$dam->getIssuesContacts($ID);
if ( sizeof( $contacts ) == 0 ) {
	echo 'ERROR: There are no contacts for this issue.  Please contact your system administrator.';
}
for($i=0; $i<sizeof($contacts); $i++){
	$contact=$dam->viewContact('', $contacts[$i]);
	$Creator = $dam->viewUser('', $contact['Creator']);
	//echo $Students; exit;
	$Students = explode(",", $contact['Students']);
	echo '<table class="contact"><td class="left">'.readableDateAndTime($contact['DateCreated']).'</td>
	<td class="right" nowrap>
	<a href="mailto:'.$Creator['Email'].'">'.$Creator['FirstName'].' '.$Creator['LastName'].'</a>';
	if($dam->userCanModifyContact('', $contact['ID'])){
		echo ' <a href="./editcontact.php?id='.$contact['ID'].'"><b>[Edit]</b></a>';
	}
	if($dam->userCanDeleteContact('', $contact['ID'])){
		echo ' <a href="./deletecontact.php?ID='.$contact['ID'].'"><b>[Delete]</b></a>';
	}
	echo '</td></tr>
		<tr><td class="body" colspan="2">'.nl2br(scanTextForLinks(stripslashes($contact['Description']))).'</td></tr>';
		
	// show attached files
	$files = $dam->viewAttachedFiles('',$contact['ID']);
	if(!empty($files)){
		echo '<tr><td><b>Attached Files</b></td></tr>';
		for($j=0; $j<count($files); $j++){
			
			// get and print filename
			$filename = $dam->getAttachedFileName($files[$j]);
			echo '<tr><td>'.$filename.'</td>';
			echo '<td class="right">';
			
			// if user has permission, print download and delete links
			if($dam->userCanDownloadFile('',$files[$j])){
				echo '<a href="./viewissue.php?id='.$ID.'&fileop=download&fileid='.$files[$j].'" target="_blank">[Open]</a>';
			}
			if($dam->userCanDeleteFile('',$files[$j])){
				echo '<a href="./viewissue.php?id='.$ID.'&fileop=delete&fileid='.$files[$j].'">[Delete]</a>';
			}
			echo '</td></tr>';
		}
		echo '<tr><td height="5"></td></tr>';
	}
	// done showing files
		
		
	echo '<tr><td class="left"><i>Students involved:</i> ';
	$emailStudents = "";
	for($j=0; $j<count($Students); $j++){
		if(!empty($Students[$j])){
			$Student=$dam->viewStudent('',$Students[$j]);
			$FirstName=$Student['FIRST_NAME'];
			$LastName=$Student['LAST_NAME'];
			echo '<a href="./viewstudent.php?id='.$Students[$j].'" TARGET="Main">'.$FirstName.' '.$LastName.'</a>';
			$emailStudents .= "".$FirstName." ".$LastName;
			if( !($j == count( $Students ) -1 ) ) {
				echo ', ';
				$emailStudents .= ", ";
			}
		}
		else echo ' ERROR';	
	}
	echo "</td>
	<td class='right'><a href='mailto:?subject=Pansophy contact email&body=Contact: ".rawurlencode(html_entity_decode($contact['ID']))."
	%0AStaff Involved: ".html_entity_decode(rawurlencode($Creator['FirstName']))." ".rawurlencode(html_entity_decode($Creator['LastName']))."
	%0AStudents Involved: ".rawurlencode(html_entity_decode($emailStudents))."
	%0ADescription: ".rawurlencode(html_entity_decode(stripslashes($contact['Description'])))."'><strong>[Email this contact]</strong></a></td>";
	echo '</tr></table><p>';
}
return $contacts;	//this gets passed to viewissue.php to fill the email body
?>
