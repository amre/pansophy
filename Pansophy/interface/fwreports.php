<?php

/**
 * Page allows user to create a report on the first watch list.
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

$reportName = $dam->getReportNameForSelf( 'firstwatch', urlGetLine() );

/////////////////////
//
//  form information
//
/////////////////////
$startdatelabel = "Start date:";
$startdatename = "start";

$enddatelabel = "End date:";
$enddatename = "end";

$reasonlabel = "Reason:";
$reasonname = "reason";
$allreasons = array('Academic','Financial','Interim Reports','Medical','Possible Transfer','Personal','Watch List');

$submitbuttonname = "submit";
$submitbuttonvalue = "Generate Report";
/////////////////////
//
//  end form information
//
/////////////////////


function printPage() {
	global $dam, $startdatelabel, $startdatename, $enddatelabel, $enddatename, $reasonlabel, $reasonname, $allreasons, $submitbuttonname, $submitbuttonvalue;
	
   //start page
	echo "<center><h2>First Watch Report</h2>";
	echo "<form><table class='greybg'  cellspacing='0' cellpadding='5'>";
		
	//print dates	
	if( isset($_GET[$startdatename]) ) $startdatevalue = getShortDate( $_GET[$startdatename] );
	if( isset($_GET[$enddatename]) ) $enddatevalue = getShortDate( $_GET[$enddatename] );
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
		  </tr>
        <tr>
		 	 <th class='darkbg' valign='top' align='right'>
				 $reasonlabel
			 </th>
			 <td valign='top' align='left'>

				 <select name='$reasonname'>
					<option value=''>All</option>";
	            foreach( $allreasons as $reason ) {
		
		            $selected = "";
		            if( $_GET[$reasonname] == $reason ) $selected = " selected";
		            echo "
					            <option value='$reason'$selected>$reason</option>";
	            }
	         echo "</select>


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
 * Calls dam function to process an first watch report
 *
 * @return first watch report
 */
function processFirstWatchReport() {
	global $dam, $startdatename, $enddatename, $reasonname;
					     
	return $dam->getFirstWatchReport( $_GET[$startdatename], $_GET[$enddatename], $_GET[$reasonname] );
}

/**
 * Prints form to save a first watch report
 */
function printSaveFirstWatchReportForm() {
	global $dam;
	echo '<center><p class="largecolorheading">New First Watch Report</p>
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
 * Prints a first watch report's name
 */
function printFirstWatchReportName() {
	global $reportName;
	echo "
	<h2>First Watch Report: $reportName</h2>
	<center>This report is saved. <a href='deletereport.php?type=firstwatch&name=".urlencode($reportName)."'>[Delete]</a></center><br>
	";
}

/**
 * Prints an first watch report
 *
 * @param $report first watch report generated by processFirstWatchReport()
 */
function printFirstWatchReport( $report ) {
	global $dam;

	echo "<center>
	<table width='80%' class='report'>";

   $fw = $report;
   foreach(array_keys($fw) as $id){

      if($dam->userCanViewFW('')){

         $student = $dam->viewStudent('',$id);
         $reasons = implode(', ',$fw[$id]);

         echo '<tr><td>';
         echo 'Student: <a href="./viewstudent.php?id='.$id.'">'.$student['LAST_NAME'].', '.$student['FIRST_NAME'].' '.$student['MIDDLE_NAME'].'</a><br>';
         echo 'Reason: '.$reasons.'<br>';   
         echo '</td></tr>';
      }
   }
	echo "</table></center>";
}




if(isset($_POST['savereport']) && isset($_POST['reportname']) ) {
	$dam->saveReportForSelf( 'firstwatch', $_POST['reportname'], $_POST['getline'] );
	$reportName = $dam->getReportNameForSelf( 'firstwatch', urlGetLine() );
	if( $reportName ) printFirstWatchReportName();
	else printSaveFirstWatchReportForm();
	printFirstWatchReport( processFirstWatchReport() );
}
else if( isset($_GET[$submitbuttonname]) ) {
	if( $reportName ) printFirstWatchReportName();
	else printSaveFirstWatchReportForm();
	printFirstWatchReport( processFirstWatchReport() );
}
else printPage();


?>
