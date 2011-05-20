<?php

/**
 * Reads in specifications to generate a user report.
 */

//+-----------------------------------------------------------------------------------------+

include( "../DataAccessManager.inc.php" );
include( "../include/miscfunctions.inc.php" );
include( "../include/header.inc" );
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

if($dam->canViewUsers('')){
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
	
	
	$reportName = $dam->getReportNameForSelf( 'user', urlGetLine() );	
	
	
	
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
	
	
	
	
	
	
	function printPage() {
		global $datetypeselectname, $datetypeselectlabel, $datetypes, $startdatelabel, $startdatename,
				$enddatelabel, $enddatename, $submitbuttonname, $submitbuttonvalue;
	
		echo "<center><h2>User Report</h2>";
		
		//start print user form table
		echo "<form><table class='greybg' cellspacing='0' cellpadding='5'>";
		
		//print date type select
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
		
		//print submit button
		echo "
			<tr>
				<td class='colorbg' colspan='2' valign='top' align='center'>
					<input type='submit' name='$submitbuttonname' value='$submitbuttonvalue'>
				</td>
			</tr>";
		
		//end print user form table
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
	


	
	
	
	
//processing functions	
	
	
/**
 * Calls dam function to process a user report
 *
 * @return user report
 */	
function processUserReport() {
	global $dam, $datetypeselectname, $startdatename, $enddatename,
		   $classyearname, $residenceselectname, $userselectname,
		   $ethnicname, $userselectname, $watchedname, $acproname,
		   $housingwaitlistname, $parkingwaitlistname;
		   
	$theuser = $_SESSION['userid'];
	
	
	return $dam->getUserReport( $_GET[$datetypeselectname], $_GET[$startdatename],
					 $_GET[$enddatename] );
}


function printUserReport( $report ) {		
	echo "<center>
	<table width='80%' class='report'>";
	foreach( $report as $rowNumber => $user ) {
		
		// USING THIS FUNCTION CAN BE PROBLEMATIC IF YOU'RE NOT CAREFUL
		//
		// To work correctly, we must be able to assume that the associative
		// array $user always contains the same key set. Otherwise, values
		// from the last iteration of the loop can linger and contaminate
		// the data of the next $user array
		extract( $user );		
		
		echo "<tr><td>";
		echo "
			<a href='./viewuser.php?id=$ID'>$FullName ($ID)</a><br><br>
		";
		echo "
			Email address: <a href='mailto:$Email'>$Email</a><br>
			Extension: $Extension<br><br>
		";
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
		echo "</td></tr>";
	}
	echo "</table></center>";
}

/**
 * Prints form to save a user report
 */		
function printSaveUserReportForm() {
	global $dam;
	
	echo "<h2>New User Report</h2>";
	
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
 * Prints a user report's name
 */
function printUserReportName() {
	global $reportName;
	echo "
	<h2>User Report: $reportName</h2>
	<center>This report is saved. <a href='deletereport.php?type=user&name=".urlencode($reportName)."'>[Delete]</a></center><br>
	";
}	
	
	
	
	
	
	
	
	
	
	
	if( $_POST['savereport'] && $_POST['reportname'] ) {
		$dam->saveReportForSelf( 'user', $_POST['reportname'], $_POST['getline'] );
		$reportName = $dam->getReportNameForSelf( 'user', urlGetLine() );
		if( $reportName ) printUserReportName();
		else printSaveUserReportForm();
		printUserReport( processUserReport() );
	}
	else if( $_GET[$submitbuttonname] ) {
		if( $reportName ) printUserReportName();
		else printSaveUserReportForm();
		printUserReport( processUserReport() );
	}
	else printPage();
}	
	
?>