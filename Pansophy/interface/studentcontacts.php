<?php
/**
 * This script is for use in the inline Contact frame on view student
 */

//+-----------------------------------------------------------------------------------------+

include_once('../include/header.inc'); 
include_once('../DataAccessManager.inc.php');
include_once('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

include('../include/filescript.inc');

$ID = $_GET['id'];
if(empty($ID)) echo "ERROR in usercontacts.php.  Please contact your system administrator.";

$contacts=$dam->getStudentsContacts($ID);
if ( sizeof( $contacts ) == 0 ) echo 'There are no contacts for this student.';

$contactsToView = 8;
if(isset($_GET['viewallcontacts']) && $_GET['viewallcontacts']) $contactsToView = sizeof($contacts);
else if(sizeof($contacts) < $contactsToView) $contactsToView = sizeof($contacts);

for($i=0; $i < $contactsToView; $i++){
	if($contacts[$i]['ID'] == ''){
	 echo 'no id';
	}
	else{
		$contact=$dam->viewContact('', $contacts[$i]);
		//print_r($contact);
		$Creator = $dam->viewUser('', $contact['Creator']);
		if($dam->userCanViewContact('', $contact['ID'])){
		
			echo '<table class="contact"><tr><td class="left">'.readableDateAndTime($contact['DateCreated']).'</td><td class="right"> <a href="mailto:'.$Creator['Email'].'">'.$Creator['FirstName'].' '.$Creator['LastName'].'</a>';
			if($dam->userCanModifyContact('', $contact['ID'])){
				echo ' <a href="./editcontact.php?id='.$contact['ID'].'"><b>[Edit]</b></a>';
			}	
			if($dam->userCanDeleteContact('', $contact['ID'])){
				echo ' <a href="./deletecontact.php?ID='.$contact['ID'].'"><b>[Delete]</b></a>';
			}
			echo '</td></tr>
				<tr>
					<td class="body" colspan="2">'.nl2br(scanTextForLinks(stripslashes($contact['Description']))).'</td></tr>';
			
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
						echo '<a href="./viewstudent.php?id='.$ID.'&fileop=download&fileid='.$files[$j].'">[Open]</a>';
					}
					if($dam->userCanDeleteFile('',$files[$j])){
						echo '<a href="./viewstudent.php?id='.$ID.'&fileop=delete&fileid='.$files[$j].'">[Delete]</a>';
					}
					echo '</td></tr>';
				}
				echo '<tr><td height="5"></td></tr>';
			}
			// done showing files
			
			echo '<tr>
					<td class="left"><strong><a href="mailto:?subject=Phronesis contact email&body='.rawurlencode(html_entity_decode(stripslashes($contact['Description']))).'"><strong>[Email this contact]</strong></a></td>
					<td class="right"><a href="./viewissue.php?id='.$contact['Issue'].'" TARGET="Main">Issue '.$contact['Issue'][0].'-'.substr($contact['Issue'],1).'
				</table><p>';	
		}
	}
}
?>
