<?php 

/**
 * Printer-friendly version of viewstudent without the links or the other frames.
 */

//+-----------------------------------------------------------------------------------------+

include('../include/header.inc'); 
include('../DataAccessManager.inc.php'); 
include( '../include/miscfunctions.inc.php' );
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+


// Get student info
$studentId = $_GET['id'];
$student = $dam->viewStudent('',$studentId);

// for minimizing and maximizing issues/contacts displayed
$viewAllIssues = false;
$viewAllContacts = false;
if(isset($_GET['viewallissues']) && $_GET['viewallissues']) $viewAllIssues = true;
if(isset($_GET['viewallcontacts']) && $_GET['viewallcontacts']) $viewAllContacts = true;

// display title section
echo '<h1>'.$student['FIRST_NAME'].' '.$student['MIDDLE_NAME'].' '.$student['LAST_NAME'].' - '.$studentId.'</h1>';
echo 	'<center>Assigned to: '.$student['AssignedTo'].'</center><br />';

if($reasons=$dam->studentOnFW('',$studentId))
{
	echo '<center> This student IS currently on First Watch for: '.$reasons[0];
	$count=count($reasons);
	if($count==1)
		echo ' </b>';
	else
	{
		for($i=1; $i<$count-1; $i++)
			echo ', '.$reasons[$i];
		echo ' and '.$reasons[$count-1].' </b>';
	}
}
else if($dam->userCanViewFW(''))
	echo '<center> This student is NOT currently on First Watch. </b>';


// Displays the user controlled flags
if ( !empty( $student['RedFlag'] ) || !empty( $student['VIP'] ) || $student['ferpaCheck'] == 1 || $student['AcProbation'] == 1 || $student['HousingWaitList'] == 1 || $student['Field1'] == 1 || $student['Field2'] == 1 || $student['Field3'] == 1) {
	$flags = $dam->extractFlags();
	if(!$usingIE) //formatting issues with the floading title
	{
	echo '</br></br></br>';
	}
	$flagstream= '<table class="darkbd" RULES="NONE" FRAME="BOX" cellpadding="2" width="90%" align="center" style="background:#ffffff;">';
	if(!empty($student['RedFlag'])){
		//$flagstream = "</br>$flagstream";errortext
		$flagstream = $flagstream.'<tr><td><font class="errortext">Red Flag: </font>'.$student['RedFlag'].'</td></tr>';
	}
	if(!empty($student['VIP'])){
		//$flagstream = "</br>$flagstream";
		$flagstream=$flagstream.'<tr><td><font class="viptext">VIP: </font>'.$student['VIP'].'</td></tr>';
	}
        if($student['ferpaCheck'] == 1 ){
            if(!empty($student['FERPA'])){
                  $flagstream=$flagstream.'<tr><td><font size="2" font face="Arial" color="blue"><b>FERPA: </b></font>'.$student['FERPA'].'</td></tr>';
             }
            else
                  $flagstream=$flagstream.'<tr><td><font class="viptext"><font color="blue">FERPA </font></td></tr>';
        }
	if($student['AcProbation'] == 1) {
		//$flagstream = "</br>$flagstream";
		$flagstream=$flagstream."<tr><td><b>This student is on Academic Probation.</b></td></tr>";
	}
	if($student['HousingWaitList'] == 1) {
		//$flagstream = "</br>$flagstream";
		$flagstream=$flagstream."<tr><td><b>This student is on the waiting list for housing.</b></td></tr>";
	}
	if($student['Field1'] == 1) {
		//$flagstream = "</br>$flagstream";
		$flagstream=$flagstream.'<tr><td><b>This student is flagged for: '.$flags['Option1'].'</b></td></tr>';
	}
	if($student['Field2'] == 1) {
		//$flagstream = "</br>$flagstream";
		$flagstream=$flagstream.'<tr><td><b>This student is flagged for: '.$flags['Option2'].'</b></td></tr>';
	}
	if($student['Field3'] == 1) {
		//$flagstream = "</br>$flagstream";
		$flagstream=$flagstream.'<tr><td><b>This student is flagged for: '.$flags['Option3'].'</b></td></tr>';
	}
	$flagstream=$flagstream.'</table>';	
	echo $flagstream;
}


// display student info...

// main table
echo '<p><table width="100%"  cellpadding="5"><tr><td valign="top" rowspan="3" width="45%">';
   
   // subtable one   
   echo '<table><tr><td>'; 

      // student details section
      echo '<p><p><table cellspacing="3"><tr><td nowrap>';
         echo '<p class="largeheading">Student Information</p></td><td nowrap valign="center">';
		   /*if($dam->userCanModifyStudent('')){//, $ID)){
			   echo '<a href="./editstudent.php?studentId='.$studentId.'"><b>[Edit this student]</b></a>';
		   }*/
	   echo '</td></tr></table></p></p>';
	   echo '<table  cellspacing="5" cellpadding="4"><tr><td align="left" nowrap>'; 

		   //FOR DISPLAYING PICTURE//<tr><td align="left" nowrap><IMG SRC="'.$picture.'" height="120" width="90" class="darkbd"></td></tr>';

		   echo 'Modified: </td><td align="left">'.readableDateAndTime( $student['LastModified'] ).' ';
		   if(!empty($student['Modifier'])){
			   $modifier = $dam->viewUser('',$student['Modifier']);
			   echo 'by <a href="mailto:'.$modifier['Email'].'">'.$modifier['FirstName'].' '.$modifier['LastName'].'</a>';
		   }
		   echo '</td></tr>		
		   <tr><td align="left" nowrap>Class year: </td><td align="left">'.$student['CLASS_YEAR'].'</td></tr>
		   <tr><td align="left" nowrap>Status: </td><td align="left">'.$student['ENROLL_STATUS'].'</td></tr>
         <tr><td align="left" nowrap>Major(s): </td><td align="left">'.$student['MAJOR_1'];
         if(!empty($student['MAJOR_2'])) echo ', '.$student['MAJOR_2'];
         echo '</td></tr>
		   <tr><td align="left" nowrap>Ethnic Code: </td><td align="left">'.$student['ETHNIC'].'</td></tr>
		   <tr><td align="left" nowrap>Advisor: </td><td align="left">';
		   if(!empty($student['ADVISOR'])){
			   $advisor = $dam->viewStudentAdvisor('',$studentId);
			   echo '<a href="mailto:'.$advisor['WOOSTER_EMAIL'].'">'.$advisor['FIRST_NAME'].' '.$advisor['LAST_NAME'].'</a>';
		   }
		   echo '</td></tr>
		   <tr><td align="left" nowrap>Housing assignment: </td><td align="left">'.$student['HOUSING_BLDG'].' '.$student['HOUSING_ROOM'].'</td></tr>
		   <tr><td align="left" nowrap>Campus box: </td><td align="left">'.$student['CAMPUS_BOX'].'</td></tr>
		   <tr><td align="left" nowrap>Wooster Email: </td><td align="left"><a href="mailto:'.$student['WOOSTER_EMAIL'].'">'.$student['WOOSTER_EMAIL'].'</a></td></tr>
		   <tr><td align="left" nowrap>Primary Email: </td><td align="left"><a href="mailto:'.$student['PRIMARY_EMAIL'].'">'.$student['PRIMARY_EMAIL'].'</a></td></tr>
		   <tr><td align="left" nowrap>Extension: </td><td align="left">'.$student['CAMPUS_PHONE'].'</td></tr>
		   <tr><td align="left" nowrap>Cellular phone: </td><td align="left">'.$student['CELL_PHONE'].'</td></tr>
		   <tr><td align="left" nowrap>Home phone: </td><td align="left">'.$student['HOME_PHONE'].'</td></tr>
		   <tr><td align="left" valign="top" nowrap>Home address: </td><td align="left">';
		   if(!empty($student['ADDRESS_ID'])){
			   $address = $dam->viewStudentAddress('', $student['ADDRESS_ID']);
			   if($address['STREET_1']) echo $address['STREET_1'];
			   if($address['STREET_2']) echo '<br>'.$address['STREET_2'];
			   if($address['STREET_3']) echo '<br>'.$address['STREET_3'];
			   if($address['STREET_4']) echo '<br>'.$address['STREET_4'];
			   if($address['STREET_5']) echo '<br>'.$address['STREET_5'];
			   if($address['CITY']) echo '<br>'.$address['CITY'];
			   if($address['STATE']) echo ', '.$address['STATE'];
			   if($address['ZIP']) echo '&nbsp;'.$address['ZIP'];
			   if($address['COUNTRY']) echo '&nbsp;'.$address['COUNTRY'];
		   }
		   echo '</td></tr>
		   <tr><td align="left" valign="top" nowrap>Relations: </td><td align="left">';

		   $relations = $dam->viewStudentRelationships('', $studentId);

		   // parents and such
		   if(count($relations) == 0){
		   }
		   if(count($relations) % 2 == 0){
			   for($i = 0; $i < count($relations); $i = $i + 2){
				   $parent = $dam->viewStudentParent('',$relations[$i]);
				   $relationship = $relations[$i+1];
				   if(strcmp($relations[$i+1],'P') == 0) $relationship = 'Parent';
				   else if(strcmp($relations[$i+1],'SP') == 0) $relationship = 'Step-parent';
				
				   if(!empty($parent['PRIMARY_EMAIL'])) echo '<a href="mailto:'.$parent['PRIMARY_EMAIL'].'">'.$parent['FIRST_NAME'].' '.$parent['LAST_NAME'].'</a>';
				   else echo $parent['FIRST_NAME'].' '.$parent['LAST_NAME'];

				   echo '<br />Relationship: '.$relationship.'
				   <br />Gender: '.$parent['GENDER'].'
				   <br />Home Phone: '.$parent['HOME_PHONE'].'
				   <br />Cell Phone: '.$parent['CELL_PHONE'];

				   if(!empty($parent['PRIVACY_FLAG'])) echo '<br />No Contact: '.$parent['PRIVACY_FLAG'];
				   else echo '<br />No Contact: N/A';
				   echo '<br /><br />';
			   }
		   }
		   else echo 'Unexpected error - please contact your system administrator';

		   echo '</td></tr>';
		   if(!empty($students['STUDENT_ACTIVITIES'])){
			   echo '<tr><td align="left" nowrap>Student activities: </td><td align="left">';
			   $activities = explode(",", $students['STUDENT_ACTIVITIES']);
			   for($k = 0; $k < count($activities); $k++){
				   echo $activities[$k].'<br />';
			   }
			   echo '</td></tr>';
		   }			
		   echo '<tr><td nowrap><a href="./viewschedule.php?studentid='.$studentId.'" target="_blank"><b>[View Student Schedule]</b></a></td><td>';
	   echo '</td></tr></table>'; 
      // end student details section

      echo '</td></tr>';
      echo '<tr><td>'; 

      // files section
      /*echo '<p><p><table  cellspacing="3"><tr><td nowrap><p class="largeheading">Attached Files</p></td></tr></table></p></p>';
	   include('./studentfiles.php');*/
      // end files section

      echo '</td></tr>';
      echo '<tr><td>'; 

      // interim section
      if($dam->userCanViewInterim('')){
	      echo '<p><p><table  cellspacing="3"><tr><td nowrap><p class="largeheading">Interim Reports</p></td></tr></table></p></p>';
	      include('./studentinterims.php');
      } 
      // end interim section

      echo '</td></tr>';
      echo '<tr><td>'; 
      echo '</td></tr></table>'; 
      
      
// issue section
   echo '</td>';
   echo '<td valign="top" rowspan="3">';

   // subtable two
   echo '<table><tr><td>'; 

      // contacts section
     
echo '<p><p><table cellspacing="3"><tr><td nowrap><p class="largeheading">Issue History</p></td>';

         if($viewAllIssues) echo '<td nowrap><a href="./viewstudentpf.php?id='.$studentId.'&viewallissues=0&viewallcontacts='.$viewAllContacts.'"><b>[Reduce]</b></a>';
         else echo '<td nowrap><a href="./viewstudentpf.php?id='.$studentId.'&viewallissues=1&viewallcontacts='.$viewAllContacts.'"><b>[View All]</b></a>';

	      if($dam->userCanCreateContact('', $studentId)){
		      // Michael Thompson * 12/14/2005 * Made redirect cap to match other routines
	      }
      echo '</tr></table></p></p>';
      include( './studentissuespf.php' );
   echo '</td></tr></table>';
   echo '</td>';
   echo '</div>';
   // end subtable three
   echo '</td>';

echo '</td></tr></table></p>'; 
// end main table
if($usingIE)
{
echo '</br></br></br></br></div>';
}
echo '</body></html>';

      // end contacts section
      
   echo '</td></tr></table>'; 
   // end subtable two

echo '</td></tr></table></p>'; 
// end main table

echo '</body></html>';



?>
