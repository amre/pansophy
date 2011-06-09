<?php

/**
 * Page allows user to create a report on issues.
 */

//+-----------------------------------------------------------------------------------------+

include( "../DataAccessManager.inc.php" );
//include( "../include/MyDate.inc.php" );
include( "../include/header.inc" );
include( '../include/miscfunctions.inc.php' );
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

// this should be put in a seperate file
function urlGetLine() {
	$getline = "";
	$first = true;
	foreach( $_GET as $attribute => $value ) {
		if( !$first ) $getline .= "&";
		$getline .= $attribute."=".urlencode( $value );
		$first = false;
	}
	if( !$first ) $getline = "?".$getline;
	return $getline;
}


$reportName = $dam->getReportNameForSelf( 'issue', urlGetLine() );


/////////////////////
//
//  form information
//
/////////////////////
$datetypeselectlabel = "Date type:";
$datetypeselectname = "datetype";
$modifiedvalue = "0";
$modifiedlabel = "Created/modified in this date range";
$createdvalue = "1";
$createdlabel = "Created in this date range";
$notmodifiedvalue = "2";
$notmodifiedlabel = "Not created/modified in this date range";
$datetypes = array();
$datetypes[$modifiedvalue] = $modifiedlabel;
$datetypes[$createdvalue] = $createdlabel;
$datetypes[$notmodifiedvalue] = $notmodifiedlabel;

$startdatelabel = "Start date:";
$startdatename = "start";

$enddatelabel = "End date:";
$enddatename = "end";

$statusselectlabel = "Status:";
$statusselectname = "status";

$categoryselectlabel = "Category:";
$categoryselectname = "category";

$levelselectlabel = "Level:";
$levelselectname = "level";

$userselectlabel = "User:";
$userselectname = "user";

$watchedlabel = "Issues I'm watching.";
$watchedname = "watchedonly";


$submitbuttonname = "submit";
$submitbuttonvalue = "Generate Report";
/////////////////////
//
//  end form information
//
/////////////////////




$jon = array();
$jon['ID'] = 'jbreitenbuch';
$jon['FIRST_NAME'] = 'Jon';
$jon['MIDDLE_NAME'] = '';
$jon['LAST_NAME'] = 'Breitenbucher';

$alex = array();
$alex['ID'] = 'mchvatal';
$alex['FIRST_NAME'] = 'Michael';
$alex['MIDDLE_NAME'] = 'A';
$alex['LAST_NAME'] = 'Chvatal';

$joel = array();
$joel['ID'] = 'jwietelmann';
$joel['FIRST_NAME'] = 'Joel';
$joel['MIDDLE_NAME'] = 'D';
$joel['LAST_NAME'] = 'Wietelmann';

$allusers = array( $jon, $alex, $joel );
$allstatuses = array('Open','Closed','Special Case');
$alllevels = array('A', 'B');
$allcategories = array('Academic - Grades', 'Academic - Other', 'Health', 'Family', 'Emergency',
				'Financial', 'Social', 'Transfer', 'Housing', 'Judicial', 'Complaints', 'International', 'Other');
$theuser = 'jwietelmann';





function printPage() {
	global $dam, $datetypeselectname, $datetypeselectlabel, $datetypes, $startdatelabel, $startdatename,
			$enddatelabel, $enddatename,  $allstatuses, $statusselectlabel, $statusselectname,
			$allcategories, $categoryselectlabel, $categoryselectname, $alllevels, $levelselectlabel, $levelselectname,
			$userselectlabel, $userselectname, $watchedlabel, $watchedname, $submitbuttonname, $submitbuttonvalue;
	//start page
	echo "<center><h2>Issue Report</h2>";
	echo "<form><table class='greybg'  cellspacing='0' cellpadding='5'>";
	
	//print date select	
	echo "<tr>
			  <th class='darkbg' valign='top' align='right'>
			  	 $datetypeselectlabel
			  </th>
		 	  <td valign='top' align='left'>
		 	  	 <select name='$datetypeselectname'>";
	foreach( $datetypes as $value => $label ) {
		$selected = "";
		if( $_GET[$datetypeselectname] == $value ) $selected = " selected";
		echo "		<option value='$value'$selected>
						$label
					</option>";
	}
	echo "		</select>
			  </td>
		  </tr>";
		
	//print dates	
	if( $_GET[$startdatename] ) $startdatevalue = getShortDate( $_GET[$startdatename] );
	if( $_GET[$enddatename] ) $enddatevalue = getShortDate( $_GET[$enddatename] );
	echo "<tr>
			 <th class='darkbg' valign='top' align='right'>
			 	 $startdatelabel
			 </th>
			 <td valign='top' align='left'>
			 	<input type='text' name='$startdatename' value='$startdatevalue'>
			 </td>
		  </tr>
		  <tr>
		 	 <th class='darkbg' valign='top' align='right'>
				 $enddatelabel
			 </th>
			 <td valign='top' align='left'>
				 <input type='text' name='$enddatename' value='$enddatevalue'>
			 </td>
		  </tr>";
		  
	//print status selection	
	echo "<tr>
			  <th class='darkbg' valign='top' align='right'>$statusselectlabel</th>
			  <td valign='top' align='left'>
			  	  <select name='$statusselectname'>
					  <option value=''>(any status)</option>";
	foreach( $allstatuses as $status ) {
		$selected = "";
		if( $_GET[$statusselectname] == $status ) $selected = " selected";
		echo "		  <option value='$status'$selected>$status</option>";
	}
	echo "		  </select>
			  </td>
		  </tr>";
		  
	//print category selection	
	echo "<tr>
			  <th class='darkbg' valign='top' align='right'>$categoryselectlabel</th>
			  <td valign='top' align='left'>
				  <select name='$categoryselectname'>
					  <option value=''>(any category)</option>";
	foreach( $allcategories as $category ) {
		
		$selected = "";
		if( $_GET[$categoryselectname] == $category ) $selected = " selected";
		echo "		  <option value='$category'$selected>$category</option>";
	}
	echo "		  </select>
			  </td>
		  </tr>";
	
	//print level selection	
	echo "<tr>
			  <th class='darkbg' valign='top' align='right'>$levelselectlabel</th>
			  <td valign='top' align='left'>
				  <select name='$levelselectname'>
					  <option value=''>(any level)</option>";
	foreach( $alllevels as $level ) {
		$selected = "";
		echo $_GET[$levelselectname];		
		if( $_GET[$levelselectname] == $level ) $selected = " selected";
		if($level == 'A')
			$actuallevel = 'Sensitive';
		else
			$actuallevel = 'Normal';
		echo "		  <option value='$level'$selected>$actuallevel</option>";
	}
	echo "		  </select>
			  </td>
		  </tr>";
	
	//print user selection
	echo "<tr>
			  <th class='darkbg' valign='top' align='right'>$userselectlabel</th>
			  <td valign='top' align='left'>
				  <select name='$userselectname'>
					  <option value=''>(any user)</option>";
	foreach( $dam->getUserSelectList() as $user ) {
		extract( $user );
		
		$selected = "";
		if( $_GET[$userselectname] == $ID ) $selected = " selected";
		
		echo "		  <option value='$ID'$selected>$Label</option>";
	}
	echo "		  </select>
			  </td>
		  </tr>";
		  
	//print checkbox filters
	if( isset( $_GET[$watchedname] ) ) $watchedchecked = " checked";
	echo "<tr>
			  <th class='darkbg' valign='top' align='right'>
			  	  Filter by:
			  </th>
			  <td valign='top' align='left'>
				  <input type='checkbox' name='$watchedname' value=''$watchedchecked> $watchedlabel
			  </td>
		  </tr>";
		
	//print submit button	
	echo "<tr>
			  <td class='colorbg' colspan='2' valign='top' align='center'>
			  	  <input type='submit' name='$submitbuttonname' value='$submitbuttonvalue'>
			  </td>
		  </tr>";
		  
	//end of page	  
	echo "</form></table>";
	echo "</center>";
}


function getShortDate( $date ) {
	$date = new MyDate( $date );
	return $date->humanDateNumerical();
}

function getMySqlDate( $date ) {
	$date = new MyDate( $date );
	return $date->mySqlDate();
}

function readableDateTime( $mysql_datetime ) {
	$datetime = new MyDate( $mysql_datetime );
	$date = $datetime->humanDateNumerical();
	$time = $datetime->humanTime12Hour();
	return $date." at ".$time;
}








/**
 * Calls dam function to process an issue report
 *
 * @return issue report
 */
function processIssueReport() {
	global $dam, $startdatename, $enddatename, $statusselectname, $categoryselectname, 
		   $levelselectname, $userselectname, $watchedname, $datetypeselectname,
		   $modifiedvalue, $createdvalue, $notmodifiedvalue;
					     
	return $dam->getIssueReport( $_GET[$startdatename], $_GET[$enddatename],
				     $_GET[$datetypeselectname], $_GET[$statusselectname], $_GET[$levelselectname],
				     $_GET[$categoryselectname], $_GET[$userselectname], $_GET[$watchedname],
				     $modifiedvalue, $createdvalue, $notmodifiedvalue );
}

/**
 * Prints form to save an issue report
 */
function printSaveIssueReportForm() {
	global $dam;
	echo '<center><p class="largecolorheading">New Issue Report</p>
		  </center>';
	
	echo "<center>
	<form method='post' action='".basename( $SELF ).urlGetLine()."'>
		<table  cellpadding='5' cellspacing='0'>
		<tr>
			<th class='darkbg'>Report name:
			</th>
			<td class='greybg'><input type='text' size='40%' name='reportname' value='".$_POST['reportname']."'>
			</td>
		</tr>
		<tr>
			<td align='center' colspan='2'><input type='submit' name='savereport' value='Save this Report'>
			</td>
		</tr>
		</table>
	<input type='hidden' name='getline' value='".urlGetLine()."'>
	</form>
	</center>";
}

/**
 * Prints an issue report's name
 */
function printIssueReportName() {
	global $reportName;
	echo "
	<h2>Issue Report: $reportName</h2>
	<center>This report is saved. <a href='deletereport.php?type=issue&name=".urlencode($reportName)."'>[Delete]</a></center><br>
	";
}

/**
 * Prints an issue report
 *
 * @param $report issue report generated by processIssueReport()
 */
function printIssueReport( $report ) {
	global $dam;

	echo "<center>
	<table width='80%' class='report'>";
	foreach( $report as $rowNumber => $issue ) {
		
		// USING THIS FUNCTION CAN BE PROBLEMATIC IF YOU'RE NOT CAREFUL
		//
		// To work correctly, we must be able to assume that the associative
		// array $issue always contains the same key set. Otherwise, values
		// from the last iteration of the loop can linger and contaminate
		// the data of the next $issue array
		extract( $issue );
		
		
		$numStudents = sizeof( $Students );
		$numUsers = sizeof( $Users );
		
		if( $AssignedTo ) {
			$assignedToLink = "<a href='./viewuser.php?id=".$AssignedTo['ID']."'>".$AssignedTo['FullName']."</a>";
		}
		else $assignedToLink = "(unassigned)";
		
		if( $Category == '')
			$Category = ('(uncategorized)');
		
		$studentLinks = array();
		foreach( $Students as $student ) {
			array_push( $studentLinks, "<a href='./viewstudent.php?id=".$student['ID']."'>".$student['FullName']."</a>" );
		}
		$studentLinks = implode( '; ', $studentLinks );
		
		$userLinks = array();
		foreach( $Users as $user ) {
			array_push( $userLinks, "<a href='./viewuser.php?id=".$user['ID']."'>".$user['FullName']."</a>" );
		}
		$userLinks = implode( '; ', $userLinks );
		
		if($Level == 'A')
			$Level = 'Sensitive';
		else
			$Level = 'Normal';
		
		if($dam->userCanViewIssue('', $ID)){
			echo "<tr><td>";
			echo "
				<a href='./viewissue.php?id=$ID'>Issue $ID: ".stripslashes($Header)."</a><br><br>
				Status: $Status<br>
				Category: $Category<br>
				Level: $Level<br>
				Assigned to: $assignedToLink<br>
				Days inactive: $DaysInactive<br><br>
				$numStudents student(s) involved: $studentLinks<br>
				$numUsers staff member(s) involved: $userLinks<br>
			";
			echo "</td></tr>";
		}
	}
	echo "</table></center>";
}




if( $_POST['savereport'] && $_POST['reportname'] ) {
	$dam->saveReportForSelf( 'issue', $_POST['reportname'], $_POST['getline'] );
	$reportName = $dam->getReportNameForSelf( 'issue', urlGetLine() );
	if( $reportName ) printIssueReportName();
	else printSaveIssueReportForm();
	printIssueReport( processIssueReport() );
}
else if( $_GET[$submitbuttonname] ) {
	if( $reportName ) printIssueReportName();
	else printSaveIssueReportForm();
	printIssueReport( processIssueReport() );
}
else printPage();


?>
