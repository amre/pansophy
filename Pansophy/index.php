<?php

/**
 * Redirects to setup page if not setup
 * Redirects to login page if not logged in
 * Redirects to main page if logged in
 */
 

@session_start();
/*If the user is logged in and has access, load up the frames and their contents*/


/*if(!$dam->success){
	echo 'No DAM, redirecting to setup script.
	<meta http-equiv="Refresh" content="0; URL=./setup.php">';
	exit;
}*/

if( isset( $_SESSION['userid'] )){
	include( 'DataAccessManager.inc.php' );
	include( './include/miscfunctions.inc.php' );
	$MD = new MyDate("now");
	$dam = new DataAccessManager();
	if ($dam->getAccessLevel() > 0)
	{
	/* This grabs the user's username in order to perform the 'Old Issue'-related queries and updates */
	$userID = $_SESSION['userid'];
	
	/* Queries the Issues table in the MySQL database, compares the timestamps between the current
	 * date and time and the LastModified field to see if there is at least a 1 year differential,
	 * and performs and update query on the Status field changing it to Closed if the issue hasn't
	 * been modified for that long. (Automatic closure of old issues.) - Josh Thomas
	 */
	
	$result = $dam->getLastModifedIssue( $userID );
	
	while($value = mysql_fetch_assoc( $result )){
		extract($value);
		$ModDate = new MyDate( $LastModified );
		if($MD->subtract($ModDate) > 31536000){
				$dam->setOldIssuesClosed( $ID, $userID, $LastModified );
		}
	}
	
	/* Similar to the previous block, this queries the newly cleansed Issues table to find any remaining
	 * issues that are Open and haven't been modified over a span of 6 months to 1 year (non-inclusive).
	 * It echoes a JavaScript script that displays an alert prior to displaying the main Phronesis page
	 * that notifies the user that they have unresolved Open issues that possibly should be closed.
	 * (Requests user closure of old issues in the event that an issue was left open for a reason.)
	 * - Josh Thoma
	 */
	
	$result = $dam->getLastModifedIssue( $userID );
	$found=0;
	
	while($value = mysql_fetch_assoc( $result )){
		extract($value);
		if($found == 0){
			$ModDate = new MyDate( $LastModified );
			if($MD->subtract($ModDate) > 15768000){
				echo '<script language=javascript>
 				alert("You have one or more open issues that have not been accessed in at least six months. Please resolve any outstanding open issues that should be closed.\n\nClick OK to continue to the main Phronesis page.");
 				</script>';
				$found=1;
			}
		}
		else{
			break;
		}
	}

	include_once('./include/frameset.inc');
	}
}
/*Otherwise, redirect to the login page*/
else{
	echo '<meta http-equiv="Refresh" content="0; URL=./login/login.php">';
}
?>
