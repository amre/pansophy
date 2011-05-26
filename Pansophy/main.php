<?php

/**
 * Displays the main page tailored to the user viewing it.
 */

session_start();
include('./include/mainheader.inc'); include('./DataAccessManager.inc.php');
include('./include/miscfunctions.inc.php');
$dam=new DataAccessManager();
$user=$_SESSION['userid'];
$watchedIssues=$dam->issuesWatched();
$watchedStudents=$dam->studentsWatched();
$inactiveIssues=$dam->inactiveOpenIssuesForUser( -1 );

//process closing issues
if(strcmp($_POST['submit'], 'Close') == 0){	
	if(isset($_POST['closeIssueArr'])) $issueArr = $_POST['closeIssueArr'];
	else $issueArr = array();
	//$studentArr = explode(',',$students);
	for($i=0; $i<count($issueArr); $i++){
		// take this moment to keep `students` table up to date
		//$dam->verifyStudent($addArr[$i]);
		// add selected students to list
		//$dam->stopWatchingIssue('', $issueArr[$i]);
		//$dam->setIssueStatus('', $issueArr[$i], 'Closed');
		$result=$dam->setIssueStatus('', $issueArr[$i], 'Closed');
		if($result) {
			echo 'Issue status changed.<p>';
		}
		else {
			echo '<meta http-equiv="Refresh" content="0; URL=./interface/addcontact.php">';
			//echo 'You do not have permission to change issue status.<p>';
		}
	}
	echo '<meta http-equiv="Refresh" content="0; URL=./main.php">';
}

	
//print header
echo '<h1>Pansophy - Student Affairs Contact Manager</h1>';

//start content table
echo '<table width=100% cellpadding="0" cellspacing="5" class="darkbg">';

//start print links and tasks
	echo '<tr><td NOWRAP valign="center" width="100%" class="darkbg" colspan="3">';
//print links
echo '<table width="100%"  cellspacing="0" cellpadding="5" class="darkbg"><tr>
		<td align="center"><a href="http://www.wooster.edu/Student-Life/Dean-of-Students" target="_blank" class="lightcolor">Dean Of Students</a></td><!--Honestly now, Dean of STD?-->
		<td align="center"><a href="http://www.wooster.edu/Academics/Academic-Affairs" target="_blank" class="lightcolor">Academic Affairs</a></td>
		<td align="center"><a href="http://www.wooster.edu/Academics/Registrar" target="_blank" class="lightcolor">Registrar\'s Office</a></td>
		<td align="center"><a href="http://www.wooster.edu/Student-Life/Security-and-Protective-Services" target="_blank" class="lightcolor">Safety and Security</a></td>
		<td align="center"><a href="http://contmgr2.wooster.edu/" target="_blank" class="lightcolor">Document Management</a></td>
		</tr></table>';
//print tasks
echo '<table width="100%"  cellspacing="0" cellpadding="5" class="colorbg" cols="5"><tr>
		<td align="center" width="25%"><a href="./interface/recentlycreated.php" target="_self" class="darkcolor">[Recently Created Issues]</a></td>
		<td align="center" width="25%"><a href="./interface/reports.php" target="_self" class="darkcolor">[Report Generator]</a></td>';
		echo '<td align="center" width="25%"><a href="./interface/viewuser.php?id='.$user.'" target="_self" class="darkcolor">[My User Page]</a></td>';
		if($dam->getAccessLevel() >= 8)
			echo '<td align="center" width="25%"><a href="./interface/tasks.php" target="_self" class="darkcolor">[User Tasks]</a></td>';
		if($dam->getAccessLevel() == 5)
			echo '<td align="center" width="25%"><a href="./interface/viewfirstwatchpf.php" target="_self" class="darkcolor">[First Watch List]</a></td>';


		
echo'</tr></table>';

//end print links and tasks
echo '</td></tr>';

//start print issues and students

//print inactive issues
echo '<tr><td valign="top" class="lightbg">';
echo '
	<table width="100%" >
	<tr><td class="lightbg"><h3>Your inactive issues</h3></td></tr>';
if($inactiveIssues) {
//start form
echo '<form action="./main.php" method="POST">';
	foreach( $inactiveIssues as $issue ){
		echo '<tr><td><input type="checkbox" name="closeIssueArr[]" value="'.$issue['ID'].'">
		<a href="./interface/viewissue.php?id='.$issue['ID'].'">'.$issue['ID'].'</a> ('.$issue['DaysOld'].' days old) - '.stripslashes_all($issue['Header']);
		if( $issue['AssignedTo'] == $_SESSION['userid'] ) echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>Assigned to <b>YOU</b>.</i>";
		echo '</td></tr>';
	}
	echo '<tr><td><input type="submit" name="submit" value="Close"></td></tr>';
	echo '</form>';
}
else {
	echo "<td><i>You currently have no inactive issues.</i></td>";
}
echo '</table>';

//print watched issues
echo '</td><td valign="top" class="lightbg">';
echo '
	<table width="100%" >
	<tr><td class="lightbg"><h3>Issues you are watching</h3></td></tr>';
if($watchedIssues){
	for($i=0; $i<sizeof($watchedIssues); $i++){
		echo '<tr><td><a href="./interface/viewissue.php?id='.$watchedIssues[$i]['ID'].'">'.$watchedIssues[$i]['ID'].'</a> - '.stripslashes_all($watchedIssues[$i]['Header']).'</td></tr>';
	}
}
else{
	echo "<td><i>You are currently watching no issues.</i></td>";
}
echo '</table>';

//print watched students
echo '</td><td valign="top" class="lightbg">';
echo'
	<table width="100%" class="lightbg" >
	<td><h3>Students you are watching</h3></td></tr>';
if($watchedStudents){
	for($i=0; $i<sizeof($watchedStudents); $i++){
		echo '<tr><td><a href="./interface/viewstudent.php?id='.$watchedStudents[$i]['ID'].'">'.$watchedStudents[$i]['FIRST_NAME'].' '.$watchedStudents[$i]['LAST_NAME'].'</a></td></tr>';
	}
}
else{
	echo '<td><i>You are currently watching no students.</i></td>';
}
//end print issues and students
echo '</table>';
echo '</td></tr>';

//end content table
echo '</table>';
?>
