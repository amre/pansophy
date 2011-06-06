<?php

/**
 * Displays issue information baby, and allows for editing baby.
 */

//+-----------------------------------------------------------------------------------------+

include('../include/header.inc'); 
include('../DataAccessManager.inc.php'); 
include( '../include/miscfunctions.inc.php' );
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

$ID = $_GET['id'];

if(!$dam->userCanViewIssue('', $ID)) echo 'You are not authorized to view this issue.';
else{

	function printAssignmentSelect( $currentAssignment = '' ) {
		global $dam;
		$ID = $_GET['id'];
		echo "
			<form action='./viewissue.php?id=$ID' method='POST' target='_self'><select size='1' name='assignedto'>
				<option value=''>(unassigned)</option>";
		foreach( $dam->getUserSelectList() as $user ) {
			$userid = $user['ID'];
			$label = $user['Label'];
			
			$selected = "";
			if( $currentAssignment == $userid ) $selected = " selected";
			
			echo "
				<option value='$userid'$selected>$label</option>";
		}
		echo "
			</select>&nbsp<input type='submit' name='reassign' value='Reassign'></form>";
	}
	
	
	if(isset($_POST['submit']) && strcmp($_POST['submit'], 'Change Status')==0){
		$result=$dam->setIssueStatus('', $_GET['id'], $_POST['Status']);
		if($result) {
			echo 'Issue status changed.<p>';
		}
		else {
			echo 'You do not have permission to change issue status.<p>';
		}
	}
	elseif(isset($_POST['submit']) && strcmp($_POST['submit'], 'Change Category')==0){
		$result=$dam->setIssueCategory('', $_GET['id'], $_POST['Category']);
		if($result) {
			echo 'Issue category changed.<p>';
		}
		else {
			echo 'You do not have permission to change issue category.<p>';
		}
	}
	else if(!empty($_POST['reassign'])) {
		$result = $dam->assignUserToIssue( $_POST['assignedto'], $_GET['id'] );
		if( $result ) {
			echo 'Issue reassigned.<p>';
		}
		else {
			echo 'You do not have permission to reassign an issue.<p>';
		}
	}
	elseif(isset($_POST['submit']) && strcmp($_POST['submit'], 'Change Level')==0){
		$result=$dam->setIssueLevel('', $_GET['id'], $_POST['Level']);
		if($result) {
			echo 'Issue level changed.<p>';
		}
		else {
			echo 'You do not have permission to change issue level.<p>';
		}
	}
	
	
	
	$issue = $dam->viewIssue('', $_GET['id']);
	//This is the array of the potential values for an issue status.
	$Statuses = array('Open', 'Closed', 'Special Case');
	//This is an array of the potential values for an issue level.
	$Levels = array('A', 'B');
	//This is an array of the potential values for an issue category.
	$Categories = array('Academic - Grades', 'Academic - Other', 'Health', 'Family', 'Emergency', 'Financial', 
				'Social', 'Transfer', 'Housing', 'Judicial', 'Complaints', 'International', 'Other');
	//Make variable names with the same names as the database fields.  MMM....variable variables.
	$keys = array_keys($issue);
	for($i=0; $i<count($keys); $i++){
		$$keys[$i]=$issue[$keys[$i]];
	}
	
	
	//Format all the data fields for better displaying.
	$Students = explode(',', $Students);
	$Staff = explode(',', $Staff);

	
	
	$Creator = $dam->viewUser('', $Creator);
	$Modifier = $dam->viewUser('', $Modifier);
	//$UsersWatching = explode(',', $UsersWatching);
	$alerts=$dam->getAlertsForSelfAndIssue( $ID );
	$count = 0;
	foreach( $alerts as $alert ) {
		$count += 1;
	}
	
	
	
	$contacts=$dam->getIssuesContacts($ID);
	$emailBody = 'ISSUE INFORMATION:
	%0A%0AIssue: '.$ID.', '.stripslashes($Header).'
	%0AAssigned to: '.$AssignedTo.'
	%0AStatus: '.$Status.'
	%0ALevel: '.$Level.'
	%0ACreated: '.readableDateAndTime( $DateCreated ).' by '.$Creator['FirstName'].' '.$Creator['LastName'].'
	%0AModified:'.readableDateAndTime( $LastModified ).' by '.$Modifier['FirstName'].' '.$Modifier['LastName'].'%0A%0A
	CONTACT HISTORY%0A%0A';
	foreach($contacts as $key => $value){
		$contact=$dam->viewContact('', $value);
		$Creator = $dam->viewUser('', $contact['Creator']);
		//echo $Students; exit;
		$Students = explode(",", $contact['Students']);
	   //print_r( $Students );

		$lastStudent = $Students[ sizeof( $Students ) - 1 ];
		if(!empty($lastStudent)) $lastStudent = $dam->ViewStudent( '', $lastStudent );

		$emailStudents = "";
		foreach($Students as $key => $studentID){
			//$emailStudents = '';
         if(!empty($studentID)){
			   $Student=$dam->ViewStudent('',$studentID);
			   $FirstName=$Student['FIRST_NAME'];
			   $LastName=$Student['LAST_NAME'];
			   $emailStudents .= "".$FirstName." ".$LastName;
			   if ( $studentID != $lastStudent['ID'] ) {
				   $emailStudents .= ", ";
			   }
         }
		}

		$emailBody .= 'Contact: '.$contact['ID'].'
		%0AStaff Involved: '.$Creator['FirstName'].' '.$Creator['LastName'].'
		%0AStudents Involved: '.$emailStudents.'
		%0ADescription: '.stripslashes($contact['Description']).'%0A%0A';
	}
	
	
	
	//Display the Issue information
	echo '<h1>Issue '.$ID[0].'-'.substr($ID,1).'</h1><center>
	<a href="./viewissuepf.php?id=' . $ID . '" target="_blank">[Click here for printer-friendly version]</a>
	<a href="mailto:?subject=Phronesis issue email&body='.$emailBody.'">[Email this issue]</a><br><br>
	<table><tr>
	<td><p class="largeheading">'.stripslashes($Header).'</p></td>';
	if($dam->userCanDeleteIssue('', $ID)){
		echo '<td><a href="./deleteissue.php?ID='.$ID.'" class="link"><b>[Delete this issue]</b></a></td>';
	}
	echo '</tr></table>';
	//Checks to see if you're watching an issue, and displays the option to do what you aren't doing.
	if($dam->userIsWatchingIssue( '', $ID )){
		echo '<b>You ARE currently watching this issue</b> <a class="bold" href="./watchissue.php?watch=0&issue='.$ID.'">[Stop watching]</a>';
	}
	else{
		if($dam->userCanWatchIssue('', $ID)){
			echo '<b>You are NOT currently watching this issue</b> <a class="bold" href="./watchissue.php?watch=1&issue='.$ID.'">[Watch]</a>';
		}
	}
	if ( $dam->userCanWatchIssue('', $ID) ) {
		echo ' <a class="bold" href="./allwatchissue?all_watch=1&issue='.$ID.'">[Everyone watch]</a>';
	}
	if($count > 0){
		echo '<p><font class="alerttext">This issue has generated '.$count.' alert(s) since you last viewed it.</font></p>';
		$dam->removeIssueFromAlerts($_GET['id']);
	}
	
	echo '</center><br><br>';
	echo '<table width="100%" >';
	
	
	
	
	
	echo "<tr><td align='left'>Assigned to: </td><td align='left'>";
	printAssignmentSelect( $AssignedTo );
	echo "</td></tr>";
	
	
	
	
	echo '<tr><td align="left">Status: </td><td align="left">
	<form action="./viewissue.php?id='.$ID.'" method="POST" target="_self"><select size="1" name="Status">';
	for($i=0; $i<sizeof($Statuses); $i++){
		if(strcmp($Statuses[$i], $Status)==0){
			echo '
			<option value="'.$Statuses[$i].'" SELECTED>'.$Statuses[$i].'</option>';
		}
		else{
			echo '
			<option value="'.$Statuses[$i].'">'.$Statuses[$i].'</option>';
		}
	}
	echo '</select>&nbsp<input type="submit" name="submit" value="Change Status"></form></td><tr>';
	
	
	echo '<tr><td align="left">Category: </td><td align="left">
	<form action="./viewissue.php?id='.$ID.'" method="POST" target="_self"><select size="1" name="Category">';
	for($i=0; $i<sizeof($Categories); $i++){
		if(strcmp($Categories[$i], $Category)==0){
			echo '
			<option value="'.$Categories[$i].'" SELECTED>'.$Categories[$i].'</option>';
		}
		else{
			echo '
			<option value="'.$Categories[$i].'">'.$Categories[$i].'</option>';
		}
	}
	echo '</select>&nbsp<input type="submit" name="submit" value="Change Category"></form></td><tr>';
	
	
	echo '<tr><td align="left">Level: </td><td align="left">
	<form action="./viewissue.php?id='.$ID.'" method="POST" target="_self"><select size="1" name="Level">';
	for($i=0; $i<sizeof($Levels); $i++){
		if($Levels[$i] == 'A')
			$LevelView = 'Sensitive';
		else
			$LevelView = 'Normal';

		if(strcmp($Levels[$i], $Level)==0){
			echo '
			<option value="'.$Levels[$i].'" SELECTED>'.$LevelView.'</option>';
		}
		else{
			echo '
			<option value="'.$Levels[$i].'">'.$LevelView.'</option>';
		}
	}
	echo '</select>&nbsp<input type="submit" name="submit" value="Change Level"></form></td><tr>';
	
	
	echo '<tr><td align="left">Created: </td><td align="left">'.readableDateAndTime( $DateCreated ).' by <a href="mailto:'.$Creator['Email'].'">'.$Creator['FirstName'].' '.$Creator['LastName'].'</a></td></tr>';
	echo '<tr><td align="left">Modified: </td><td align="left">'.readableDateAndTime( $LastModified );
	
	if(!empty($Modifier)){
		echo ' by <a href="mailto:'.$Modifier['Email'].'">'.$Modifier['FirstName'].' '.$Modifier['LastName'].'</a></td></tr>';
	}
	
	// students associated
	echo '<tr><td></td><td></td></tr>
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
	if($dam->userCanCreateContact('', $ID)){
		echo '<td nowrap><a href="./addcontact.php?issueid='.$ID.'&isnewissue=0"><b>[Append a new contact to this issue]</b></a></td><p>';
		}
	echo '</tr></table><p>';
	include( './issuecontacts.php' );
	echo '</body></html>';
	
	
	
	// I've looked at the issue.  Lose the alerts.
	$dam->removeIssueFromAlerts($_GET['id']);
	
	
	
	
	
	mysql_close();
}
?>

