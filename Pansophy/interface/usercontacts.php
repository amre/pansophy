<?php
/**
 * This script is for use in the inline Contact frame on view user.
 */
 
//+-----------------------------------------------------------------------------------------+ 
 
include_once('../include/header.inc'); 
include_once('../DataAccessManager.inc.php');
include_once('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

// Inlude filescript so user can download files from contacts
include_once('../include/filescript.inc'); 


// Get user ID
$ID = $_GET['id'];
if(empty($ID)) echo "ERROR in usercontacts.php.  Please contact your system administrator.";


// A flag is passed through GET indicating that all issues related to
// the user should be displayed. 
if(isset($_GET['viewallcontacts']) && $_GET['viewallcontacts']){
   $contacts=$dam->getUserContacts($ID,0); // retrieve all contacts related to user
}
else{
   $contacts=$dam->getUserContacts($ID,1); // retrieve only most recent contacts
}


// Display contacts
if ( sizeof( $contacts ) == 0 ) echo 'There are no contacts for this user.';
for($i=0; $i < sizeof($contacts); $i++){
	$contact=$dam->viewContact('', $contacts[$i]);
	$Creator = $dam->viewUser('', $contact['Creator']);
	echo '<table class="contact"><tr><td class="left">'.$contact['DateCreated'].'</td><td class="right"> <a href="mailto:'.$Creator['Email'].'">'.$Creator['FirstName'].' '.$Creator['LastName'].'</a>';
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
				echo '<a href="./viewuser.php?id='.$ID.'&fileop=download&fileid='.$files[$j].'">[Open]</a>';
			}
			if($dam->userCanDeleteFile('',$files[$j])){
				echo '<a href="./viewuser.php?id='.$ID.'&fileop=delete&fileid='.$files[$j].'">[Delete]</a>';
			}
			echo '</td></tr>';
		}
		echo '<tr><td height="5"></td></tr>';
	}
	// done showing files
	
	echo '<tr>
	<td class="left"><a href="mailto:?subject=Phronesis contact email&body='.urlencode(html_entity_decode(stripslashes($contact['Description']))).'"><strong>[Email this contact]</strong></td>
	<td class="right"><a href="./viewissue.php?id='.$contact['Issue'].'" TARGET="Main">Issue '.$contact['Issue'][0].'-'.substr($contact['Issue'],1).'</table><p>';
}
?>
