<?php

/**
 * Reads in specifications to generate a student report.
 */

//+-----------------------------------------------------------------------------------------+

require_once( "../DataAccessManager.inc.php" );
require_once( "../include/miscfunctions.inc.php" );
require_once( "../include/header.inc" );
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

$reportName = $dam->getReportNameForSelf( 'student', urlGetLine() );

// Get strings of what the current flags are actually set to.
$value = $dam->extractFlags();
extract($value);

/////////////////////
//
//  form information
//
/////////////////////
$datetypeselectlabel = "Date type:";
$datetypeselectname = "datetype";
$dateinfovalue = "0";
$dateinfolabel = "Include date-specific information";
$hascontactsvalue = "1";
$hascontactslabel = "Only return users with contacts in this date range";
$datetypes = array();
$datetypes[$dateinfovalue] = $dateinfolabel;
$datetypes[$hascontactsvalue] = $hascontactslabel;

$startdatelabel = "Start date:";
$startdatename = "start";

$enddatelabel = "End date:";
$enddatename = "end";

$classyearlabel = "Class year:";
$classyearname = "classyear";

$residenceselectlabel = "Residence:";
$residenceselectname = "residence";

$ethniclabel = "Ethnic code:";
$ethnicname = "ethniccode";

$userselectlabel = "Assigned to:";
$userselectname = "user";

$watchedlabel = "Students I'm watching.";
$watchedname = "watchedonly";

$redflaglabel = "Red flagged.";
$redflagname = "redflagonly";

$viplabel = "VIP students.";
$vipname = "viponly";

$acprolabel = "Academic probation.";
$acproname = "acproonly";

$housingwaitlistlabel = "Housing waiting list.";
$housingwaitlistname = "housingwaitlistonly";

$fieldonelabel = $Option1.' flag.';
$fieldonename = "fieldoneonly";

$fieldtwolabel = $Option2.' flag.';
$fieldtwoname = "fieldtwoonly";

$fieldthreelabel = $Option3.' flag.';
$fieldthreename = "fieldthreeonly";

$submitbuttonname = "submit";
$submitbuttonvalue = "Generate Report";
/////////////////////
//
//  end form information
//
/////////////////////

$allusers = $dam->getUserSelectList();
$allstatuses = array('Open','Closed','Special Case');
$allresidences = array('Armington Hall','Bornhuetter Hall','Kenarden Lodge','Kennedy Apartments');

$theuser = 'jwietelmann';






/**
 * Prints student report form
 */
function printPage() {
	global $dam, $datetypeselectname, $datetypeselectlabel, $datetypes, $startdatelabel, $startdatename, 
			$enddatelabel, $enddatename, $classyearlabel, $classyearname, $allresidences, $residenceselectlabel,
			$residenceselectname, $ethniclabel, $ethnicname, $userselectlabel, $userselectname,
			$submitbuttonname, $submitbuttonvalue;
	
	//start print page
	echo "<center><h2>Student Report</h2>";
	
	//start print student form table
	echo "
	<form><table class='greybg' cellspacing='0' cellpadding='5'>";
	
	//print date type selection
	echo "
		<tr>
			<th class='darkbg' valign='top' align='right'>$datetypeselectlabel</th>
			<td valign='top' align='left'>
				<select name='$datetypeselectname'>";
	foreach( $datetypes as $value => $label ) {
		
		$selected = "";
		if( $_GET[$datetypeselectname] == $value ) $selected = " selected";
		echo "
					<option value='$value'$selected>$label</option>";
	}
	echo "
				</select>
			</td>
		</tr>";
	
	//print dates
	if( $_GET[$startdatename] ) $startdatevalue = getShortDate( $_GET[$startdatename] );
	if( $_GET[$enddatename] ) $enddatevalue = getShortDate( $_GET[$enddatename] );
	
	echo "
		<tr>
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
	
	//print class year
	$classyearvalue = $_GET[$classyearname];
	echo "
		<tr>
			<th class='darkbg' valign='top' align='right'>
				$classyearlabel
			</th>
			<td valign='top' align='left'>
				<input type='text' name='$classyearname' value='$classyearvalue'>
			</td>
		</tr>";
	
	//print building selection
	echo "
		<tr>
			<th class='darkbg' valign='top' align='right'>$residenceselectlabel</th>
			<td valign='top' align='left'>
				<select name='$residenceselectname'>
					<option value=''>(any residence)</option>";
	foreach( $allresidences as $residence ) {
		
		$selected = "";
		if( $_GET[$residenceselectname] == $residence ) $selected = " selected";
		echo "
					<option value='$residence'$selected>$residence</option>";
	}
	echo "
				</select>
			</td>
		</tr>";
	
	//print ethnic code box
	 $ethnicvalue = $_GET[$ethnicname];
	echo "
		<tr>
			<th class='darkbg' valign='top' align='right'>
				$ethniclabel
			</th>
			<td valign='top' align='left'>
				<input type='text' name='$ethnicname' value='$ethnicvalue'>
			</td>
		</tr>";
	
	//print user selection
	echo "
		<tr>
			<th class='darkbg' valign='top' align='right'>$userselectlabel</th>
			<td valign='top' align='left'>
				<select name='$userselectname'>
					<option value=''>(any user)</option>";
	foreach( $dam->getUserSelectList() as $user ) {
		extract( $user );
		
		$selected = "";
		if( $_GET[$userselectname] == $ID ) $selected = " selected";
		
		echo "
					<option value='$ID'$selected>$Label</option>";
	}
	echo "
				</select>
			</td>
		</tr>";
	
	//print checkbox filters
	global $watchedlabel, $watchedname, $redflagname, $redflaglabel,
	       $vipname, $viplabel, $acprolabel, $acproname,
	       $housingwaitlistlabel, $housingwaitlistname, $fieldonelabel,
	       $fieldonename, $fieldtwolabel, $fieldtwoname, $fieldthreelabel,
	       $fieldthreename, $Option1, $Option2, $Option3;
	
	if( isset( $_GET[$watchedname] ) ) $watchedchecked = " checked";
	if( isset( $_GET[$redflagname] ) ) $redflagchecked = " checked";
	if( isset( $_GET[$vipname] ) ) $vipchecked = " checked";
	if( isset( $_GET[$acproname] ) ) $acprochecked = " checked";
	if( isset( $_GET[$housingwaitlistname] ) ) $housingwaitlistchecked = " checked";
	if( isset( $_GET[$fieldonename] ) ) $fieldonechecked = " checked";
	if( isset( $_GET[$fieldtwoname] ) ) $fieldtwochecked = " checked";
	if( isset( $_GET[$fieldthreename] ) ) $fieldthreechecked = " checked";
	
	$val = 5;
	
	// Updates the number of rows to span if each additional flag is set.
	
	if($Option1)
		$val += 1;
		
	if($Option2)
		$val += 1;
		
	if($Option3)
		$val += 1;
		
	echo "
		<tr>
			<th class='darkbg' align='right' valign='middle' rowspan='".$val."'>
				Filter by:
			</th>
			<td valign='top' align='left'>
				<input type='checkbox' name='$watchedname' value='$watchedchecked'> $watchedlabel
			</td>
		</tr>
		<tr>
			<td valign='top' align='left'>
				<input type='checkbox' name='$redflagname' value='$redflagchecked'> $redflaglabel
			</td>
		</tr>
		<tr>
			<td valign='top' align='left'>
				<input type='checkbox' name='$vipname' value='$vipchecked'> $viplabel
			</td>
		</tr>
		<tr>
			<td valign='top' align='left'>
				<input type='checkbox' name='$acproname' value='$acprochecked'> $acprolabel
			</td>
		</tr>
		<tr>
			<td valign='top' align='left'>
				<input type='checkbox' name='$housingwaitlistname' value='$housingwaitlistchecked'> $housingwaitlistlabel
			</td>
		</tr>";
		if($Option1){
		echo "<tr>
			<td valign='top' align='left'>
				<input type='checkbox' name='$fieldonename' value='$fieldonechecked'> $fieldonelabel
			</td>
		</tr>";}
		if($Option2){
		echo "<tr>
			<td valign='top' align='left'>
				<input type='checkbox' name='$fieldtwoname' value='$fieldtwochecked'> $fieldtwolabel
			</td>
		</tr>";}
		if($Option3){
		echo "<tr>
			<td valign='top' align='left'>
				<input type='checkbox' name='$fieldthreename' value='$fieldthreechecked'> $fieldthreelabel
			</td>
		</tr>";}

	//print submit button
	echo "
		<tr>
			<td class='colorbg' colspan='2' valign='top' align='center'>
				<input type='submit' name='$submitbuttonname' value='$submitbuttonvalue'>
			</td>
		</tr>";
	
	//end print student form table
	echo "</form></table>";
	
	//end print page
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



//processing functions



/**
 * Calls dam function to process a student report
 *
 * @return student report
 */
function processStudentReport() {
	global $dam, $datetypeselectname, $startdatename, $enddatename,
	       $classyearname, $residenceselectname, $ethnicname, $userselectname, 
	       $watchedname, $redflagname, $vipname, $acproname,
	       $housingwaitlistname, $fieldonename, $fieldtwoname, $fieldthreename;
	       
	$theuser = $_SESSION['userid'];
	
	
	return $dam->getStudentReport( $_GET[$datetypeselectname], $_GET[$startdatename],
				     $_GET[$enddatename], $_GET[$classyearname],
				     $_GET[$residenceselectname], $_GET[$ethnicname], $_GET[$userselectname],
				     $_GET[$watchedname], $_GET[$redflagname], $_GET[$vipname], $_GET[$acproname],
				     $_GET[$housingwaitlistname], $_GET[$fieldonename], $_GET[$fieldtwoname],
				     $_GET[$fieldthreename]);
}

/**
 * Prints form to save a student report
 */
function printSaveStudentReportForm() {
	global $dam;
	
	echo "<h2>New Student Report</h2>";
	
	echo "<center>
	<form method='post' action='".basename( $SELF ).urlGetLine()."'>
	<table  cellpadding='5' cellspacing='0'>
	<tr>
		<th class='darkbg'>Report name:</th>
		<td class='greybg'><input type='text' size='40%' name='reportname' value='".$_POST['reportname']."'></td>
	</tr><tr>
		<td align='center' colspan='2'><input type='submit' name='savereport' value='Save this Report'></td>
	</tr>
	</table>
	<input type='hidden' name='getline' value='".urlGetLine()."'>
	</form>
	</center>";
}

/**
 * Prints a student report's name
 */
function printStudentReportName() {
	global $reportName;
	echo "
	<h2>Student Report: $reportName</h2>
	<center>This report is saved. <a href='deletereport.php?type=student&name=".urlencode($reportName)."'>[Delete]</a></center><br>
	";
}

/**
 * Prints a student report
 *
 * @param $report student report generated by processStudentReport()
 */
function printStudentReport( $report ) {
	
	global $Option1, $Option2, $Option3, $dam;
	
	echo "<center>
	<table width='80%' class='report'>";
	foreach( $report as $rowNumber => $student ) {
		

		// USING THIS FUNCTION CAN BE PROBLEMATIC IF YOU'RE NOT CAREFUL
		//
		// To work correctly, we must be able to assume that the associative
		// array $student always contains the same key set. Otherwise, values
		// from the last iteration of the loop can linger and contaminate
		// the data of the next $student array
		extract( $student );
		$numUsers = sizeof( $Users );
		
		if( $AssignedTo ) {
			$AssignName = $dam->getAssignedToName($AssignedTo);
			$assignedToLink = "<a href='./viewuser.php?id=".$AssignedTo."'>".$AssignName."</a>";
		}
		
		$userLinks = array();
		foreach( $Users as $user ) {
			array_push( $userLinks, "<a href='./viewuser.php?id=".$user['ID']."'>".$user['FullName']."</a>" );
		}
		$userLinks = implode( '; ', $userLinks );
		
		
		echo "<tr><td>";
		echo "
			<a href='./viewstudent.php?id=$ID'>$FullName ($ID)</a><br><br>";
		if( $VIP || $RedFlag ) {
			if( $VIP ) echo "**VIP: ".stripslashes_all($VIP)."<br>";
			if( $RedFlag ) echo "**Red Flag: ".stripslashes_all($RedFlag)."<br>";
			echo "<br>";
		}
		echo "
			Class year: $CLASS_YEAR<br>
			Status: $ENROLL_STATUS<br>
		";
		if( $assignedToLink ) echo "Assigned to: $assignedToLink<br>";
		echo "
			<br>
		";
		if( $AcProbation || $HousingWaitList || $Field1 || $Field2 || $Field3 ) {
			if( $AcProbation ) echo "**Currently on Academic Probation.<br>";
			if( $HousingWaitList ) echo "**Currently on the waiting list for housing.<br>";
			if( $Field1 ) echo "**Currently flagged for ".$Option1.".<br>";
			if( $Field2 ) echo "**Currently flagged for ".$Option2.".<br>";
			if( $Field3 ) echo "**Currently flagged for ".$Option3.".<br>";
			echo "<br>";
		}
		if( $StartDate || $EndDate ) {
			$start = readableDate( $StartDate );
			$end = readableDate( $EndDate );
			if( $StartDate && $EndDate ) $datestuff = " from $start to $end";
			else if( $StartDate ) $datestuff = " after $start";
			else if( $EndDate ) $datestuff = " before $end";
			echo "
				Number of contacts$datestuff: $NumContactsInRange (of $NumContacts total)<br>
			";
		}
		else echo "
			Number of contacts: $NumContacts<br>
		";
		if( $numUsers ) echo "$numUsers staff member(s) in contact: $userLinks<br>";
		echo "</td></tr>";
	}
	echo "</table></center>";
}







if( $_POST['savereport'] && $_POST['reportname'] ) {
	$dam->saveReportForSelf( 'student', $_POST['reportname'], $_POST['getline'] );
	$reportName = $dam->getReportNameForSelf( 'student', urlGetLine() );
	if( $reportName ) printStudentReportName();
	else printSaveStudentReportForm();
	printStudentReport( processStudentReport() );
}
else if( $_GET[$submitbuttonname] ) {
	if( $reportName ) printStudentReportName();
	else printSaveStudentReportForm();
	printStudentReport( processStudentReport() );
}
else printPage();



?>