<?php 

/**
 * Printer-friendly version of viewissue without the links or the other frames.
 */

//+-----------------------------------------------------------------------------------------+

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
include( '../include/miscfunctions.inc.php' );
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

function printAssignmentSelect( $currentAssignment = '' ) {
	global $dam;
	
		foreach ( $dam->getUserSelectList() as $user ) {
			$userid = $user['ID'];
			$label = $user['Label'];
			if ( $currentAssignment == $userid ) {
				echo $label;
				break;
			}
		}
}







if(strcmp($_POST['submit'], 'Change Status')==0){
	$result=$dam->setIssueStatus('', $_GET['id'], $_POST['Status']);
	if($result) {
		echo 'Issue status changed.<p>';
	}
	else {
		echo 'Issue status could not be changed at this time.  Please try again later.<p>';
	}
}
elseif(strcmp($_POST['submit'], 'Change Category')==0){
	$result=$dam->setIssueStatus('', $_GET['id'], $_POST['Category']);
	if($result) {
		echo 'Issue category changed.<p>';
	}
	else {
		echo 'Issue category could not be changed at this time.  Please try again later.<p>';
	}
}
else if( $_POST['reassign'] ) {
	$result = $dam->assignUserToIssue( $_POST['assignedto'], $_GET['id'] );
	if( $result ) {
		echo 'Issue reassigned.<p>';
	}
	else {
		echo 'Issue could not be reassigned at this time.  Please try again later.<p>';
	}
}



$issue = $dam->viewIssue('', $_GET['id']);
//This is the array of the potential values for an issue status.
$Statuses = array('Open', 'Closed', 'Special Case'); 
//This is an array of the potential values for an issue category.
$Categories = array('Academic - Grades', 'Academic - Other', 'Health', 'Family', 'Emergency', 'Financial', 
				'Social', 'Transfer', 'Housing', 'Judicial', 'Complaints', 'International', 'Other');
//Make variable names with the same names as the database fields.  MMM....variable variables.
$keys = array_keys($issue);
for($i=0; $i<count($keys); $i++){
	$$keys[$i]=$issue[$keys[$i]];
}
//Format all the data fields for better displaying.
$Students = explode(",", $Students);
$Staff = explode(',', $Staff);




$Creator = $dam->viewUser('', $Creator);
$Modifier = $dam->viewUser('', $Modifier);
$UsersWatching = explode(',', $UsersWatching);
$alerts=$dam->getUserAlerts();
$alerts=explode(',', $alerts);
$nothing = array('');
$alerts=array_diff($alerts, $nothing);
$alerts = array_values($alerts);
$count = (array_count_values($alerts));

//Display the Issue information
echo '<h1>Issue '.$ID[0].'-'.substr($ID,1).'</h1><center><br><br>
<table><tr>
<td><p class="largeheading">'.stripslashes($Header).'</p></td>';

echo '</tr></table>';
//Checks to see if you're watching an issue, and displays the option to do what you aren't doing.
if($dam->userIsWatchingIssue( '', $ID )){
	echo '<b>You ARE currently watching this issue</b>';
}
else{
	if($dam->userCanWatchIssue('', $ID)){
		echo '<b>You are NOT currently watching this issue</b>';
	}
}

if($count[$ID] > 0){
	echo '<p><font class="alerttext">This issue has generated '.$count[$ID].' alert(s) since you last viewed it.</font></p>';
	$dam->removeIssueFromAlerts($_GET['id']);
}

echo '</center><br><br>';
echo '<table width="100%" >';





echo "<tr><td align='left'>Assigned to: </td>";
echo '<td>';
if($AssignedTo == '')
	echo '(unassigned)';
else
 	printAssignmentSelect( $AssignedTo );
echo "</td></tr>";




echo '<tr><td align="left">Status: </td><td align="left">'.$Status.'</td>
<tr>';

if($Category == '')
	$Category = '(uncategorized)';
echo '<tr><td align="left">Category: </td><td align="left">'.$Category.'</td>
	  <tr>';

if($Level == 'A')
	$Level = 'Sensitive';
else
	$Level = 'Normal';
echo '<tr><td align="left">Level: </td><td align="left">'.$Level.'</td>
<tr>';

echo '<tr><td align="left">Created: </td><td align="left">'.readableDateAndTime( $DateCreated ).' by <a href="mailto:'.rawurlencode( html_entity_decode( $Creator['Email'] ) ).'">'.$Creator['FirstName'].' '.$Creator['LastName'].'</a></td></tr>';
echo '<tr><td align="left">Modified: </td><td align="left">'.readableDateAndTime( $LastModified ).' by <a href="mailto:'.rawurlencode( html_entity_decode( $Modifier['Email'] ) ).'">'.$Modifier['FirstName'].' '.$Modifier['LastName'].'</a></td></tr>
<tr><td></td><td></td></tr>
<tr height="10"><td align="left" width ="20%">Students associated: </td><td align="left">';
for($i=0; $i<count($Students); $i++){
	
	$Student=$dam->ViewStudent('',$Students[$i]);
	$FirstName=$Student['FIRST_NAME'];
	$LastName=$Student['LAST_NAME'];
	echo '<a href="./viewstudent.php?id='.$Students[$i].'">'.$FirstName.' '.$LastName.'</a>';
	if(!($i == count($Students)-1)){
		echo ', ';
	}
}
echo '</td></tr>
<tr height="10"><td align="left" width ="20%">Staff associated: </td><td align="left">';
for($i=0; $i<count($Staff); $i++){
	$user = $dam->viewUser('', $Staff[$i]);
	echo '<a href="mailto:'.$user['Email'].'">'.$user['FirstName'].' '.$user['LastName'].'</a>';
	if(!($i == count($Staff)-1)){
		echo ', ';
	}
}
echo '</table><p><p>
<table  cellspacing="3"><tr><td nowrap><p class="largeheading">Contact History</p></td>';

echo '</tr></table><p>';
include( './issuecontacts.php' );
echo '
</body></html>';



// I've looked at the issue.  Lose the alerts.
$dam->removeIssueFromAlerts($_GET['id']);





mysql_close();
?>

