<?php
/** 
 * Printer-friendly version of viewinterim without the links or the other frames.
 */

//+-----------------------------------------------------------------------------------------+

include('../include/header.inc');
include('../DataAccessManager.inc.php');
include( '../include/miscfunctions.inc.php' );
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

// globals
$interim;
$student;

// check user access level
if($dam->userCanViewInterim('')){

   // get information
   $interim = $dam->viewInterim('',$_GET['id']);
   $student = $dam->preInterimInformation($interim['StudentID']);
   
   // display the page
   displayPage();
}
// user access level doesn't check out
else{
   echo '<script language=javascript>alert("You don\'t have access to view this page. Redirecting to the main page.");</script>
	<meta http-equiv="Refresh" content="0; URL=../index.php">';
}

// end html started in header.inc
echo '</body></html>';

//+-----------------------------------------------------------------------------------------+

// function to display the page
function displayPage()
{
   global $interim,$student;
   extract($interim);
   extract($student);

   echo '<table width="40%" border="0"><tr><td><h1>Interim '.$ID.'</h1></td></tr></table>';

   echo '<table width="80%" border="0">';
      echo '<tr><td align="left" width="25%"><b>Student ID: </b></td><td align="left">'.$StudentID.'</td></tr>
      <tr><td align="left" width="25%"><b>Student Name: </b></td><td align="left">'.$FIRST_NAME.' '.$MIDDLE_NAME.' '.$LAST_NAME.'</td></tr>
      <tr valign="top"><td align="left" width="25%"><b>Class Year: </b></td><td align="left">'.$CLASS_YEAR.'</td></tr>
      <tr valign="top"><td align="left" width="25%"><b>Course Number & Title: </b></td><td align="left">'.$CourseNumberTitle.'</td></tr>
      <tr valign="top"><td align="left" width="25%"><b>Instructor: </b></td><td align="left">'.$Instructor.'</td></tr>
      <tr valign="top"><td align="left" width="25%"><b>Date: </b></td><td align="left">'.readableDate($Date).'</td></tr>
      <tr valign="top"><td align="left" width="25%">&nbsp;</td><td>&nbsp;</td></tr>
      <tr valign="top"><td align="left" width="25%"><b>Problem: </b></td><td align="left">';

      $problems = explode(';',$Problem);
      for($i = 0; $i < count($problems); $i++){
         echo $problems[$i].'<br />';
      }

      echo '</td></tr><tr><td align="left" width="25%">&nbsp;</td><td>&nbsp;</td></tr>';

      echo '<tr valign="top"><td align="left" width="25%"><b>Comments: </b></td><td align="left">'.stripslashes($Comments).'</td></tr>
      <tr><td align="left" width="25%">&nbsp;</td><td>&nbsp;</td></tr>
      <tr valign="top"><td align="left" width="25%"><b>Recommended Action: </b></td><td align="left">';

      $actions = explode(';',$RecommendAction);
      for($i = 0; $i < count($actions); $i++){
         if(strcmp($actions[$i],"Conference with a Dean []") == 0) echo 'Conference with a Dean<br />';
         else echo $actions[$i].'<br />';
      }

      echo '</td></tr><tr valign="top"><td align="left" width="25%">&nbsp;</td><td>&nbsp;</td></tr>';

      echo '<tr valign="top"><td align="left" width="25%"><b>Other Recommended Action: </b></td><td align="left">'.stripslashes($OtherAction).'</td></tr>
      <tr valign="top"><td align="left" width="25%"><b>Date Processed: </b></td><td align="left">'.readableDate($DateProcessed).'</td></tr>';

   echo '</table>';  
}

?>
