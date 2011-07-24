<?php 

/**
 * Displays all the new alerts that a user has.
 */ 

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
include ('../include/miscfunctions.inc.php');

$dam=new DataAccessManager();
$alerts=$dam->getUserAlerts();

function printBody() {
	global $dam, $user, $alerts;
	$user=$_SESSION['userid'];

	echo '<h1>Alerts <a href="./clearalerts.php" class ="darkcolor" TARGET="_top">[Clear all my alerts]</a></h1>';
	if(!$alerts){
		echo 'You have no alerts';
	}
	else{
		printAlertTable();
	}
	echo '</body></html>';
}

//print alerts a user has
function printAlertTable() {
	global $dam, $alerts;
	echo '<table><dl>';
	foreach($alerts as $alert){
		$message = $alert['Message'];
		if( $id = $alert['IssueID'] ) {
			$issue = $dam->viewIssue('',$alert['IssueID']);
			$description = $issue['Header'];
			echo "<tr><td><a class='alert' href='./viewissue.php?id=".$alert['IssueID']."'>Issue ".$alert['IssueID']." (".stripslashes_all($description).")</a> - $message</td></tr>";
		}
		else {
			if( $id = $alert['StudentID']) {
				$student = $dam->viewStudent('',$alert['StudentID']);
				$fullname = $student['FIRST_NAME']." ".$student['LAST_NAME'];
				echo "<tr><td><a class='alert' href='./viewstudent.php?id=".$alert['StudentID']."'>$fullname (Student #".$alert['StudentID'].")</a> - $message</td></tr>";
			}
		// IN PANSOPHY 2 THIS SHOULD NEVER HAPPEN.  WATCHING USERS IS ONLY SUPPORTED INTERNALLY.
		// THERE IS NO MECHANISM IN THE INTERFACE TO ALLOW WATCHING OF A USER.
			else {
				if( $id = $alert['OtherUserID'] ) {
						echo "<tr><td><a class='alert' href='./viewuser.php?id=$id'>User $id</a> - $message</td></tr>";
				}
			}
		}
	}
	echo '</td></tr></dl></table>';
}

printBody();


?>
