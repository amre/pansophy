<?php 

/**
 * Displays student profile.
 */

//+-----------------------------------------------------------------------------------------+
// We need to make some changes if the user is browsing in Internet Explorer.
$u_agent = $_SERVER['HTTP_USER_AGENT'];
    $usingIE = False;
    if(preg_match('/MSIE/i',$u_agent))
    {
        $usingIE = True;
    }
    
if($usingIE)
{
include('../include/viewstudentheaderIE.inc'); 
}
else
{
include('../include/header.inc');
}
include('../DataAccessManager.inc.php');
include( '../include/miscfunctions.inc.php' );
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+


// process assignment here
if( isset($_POST['reassign']) && $_POST['reassign'] ) {
	$result = $dam->assignUserToStudent( $_POST['assignedto'], $_GET['id'] );
	if( $result ) echo 'Student reassigned.<p>';
	else echo 'Student could not be reassigned at this time.  Please try again later.<p>';
}

//process first watch here
if(isset($_POST['fwreason']) && $_POST['addtofw'] ) {
	$dam->placeOnFirstWatch('', $_GET['id'], $_POST['fwreason']);//function already checks whether user can modify FW
}


// Get student info
$studentId = $_GET['id'];
$student = $dam->viewStudent('',$studentId);

// for minimizing and maximizing issues/contacts displayed
$viewAllIssues = false;
$viewAllContacts = false;
if(isset($_GET['viewallissues']) && $_GET['viewallissues']) $viewAllIssues = true;
if(isset($_GET['viewallcontacts']) && $_GET['viewallcontacts']) $viewAllContacts = true;


//FOR DISPLAYING PICTURE//$picture = $dam->getProfilePicture($studentId);

// display title section

if($dam->dontContactParents('',$studentId)){
	echo '<div style="position:fixed ;background:#f0f0f0;border:thick solid #000000; top: 0%;z-index:95;width:100%;padding:0%;margin:0;"><h1 style="color:red;">';
}
else{
	echo '<div style="position:fixed ;background:#b78f02;border:thick solid #000000; top: 0%;z-index:95;width:100%;padding:0%;margin:0;"><h1 style="color:#000000;">';
}
echo $student['FIRST_NAME'].' '.$student['MIDDLE_NAME'].' '.$student['LAST_NAME'].' - '.$studentId.'</h1></div></br>';
if($usingIE)
{
echo '<div style = "height:100%;overflow:auto">';
}
echo '<p><br />';

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
<<<<<<< HEAD
		if(!empty($student['FERPA'])){
			$flagstream=$flagstream.'<tr><td><font class="viptext"><font color="blue">FERPA: </font></font>'.$student['FERPA'].'</td></tr>';
		}
		else
  		$flagstream=$flagstream.'<tr><td><font class="viptext"><font color="blue">FERPA </font></td></tr>';
	}
=======
            if(!empty($student['FERPA'])){
                  $flagstream=$flagstream.'<tr><td><font size="2" font face="Arial" color="blue"><b>FERPA: </b></font>'.$student['FERPA'].'</td></tr>';
             }
            else
                  $flagstream=$flagstream.'<tr><td><font class="viptext"><font color="blue">FERPA </font></td></tr>';
        }
>>>>>>> 266fde08e538ab7ce1f2dee08d520597a23e0cd5
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

echo '<p><table width="100%"  cellpadding="5"><tr><td valign="top" rowspan="3" width="30%">';
   
   // subtable one

   echo '<div style="position:relative; top:0%;right=65%;left=0%;">';   
   echo '<table><tr><td>'; 

      // student details section
      echo '<p><p><table cellspacing="3"><tr><td nowrap>';
         echo '<p class="largeheading">Student Information</p></td><td nowrap valign="center">';
		   if($dam->userCanModifyStudent('')){//, $ID)){
			   echo '<a href="./editstudent.php?studentId='.$studentId.'"><b>[Edit this student]</b></a>';
		   
}
	   echo '</td></tr></table></p></p>';
	   echo '<table  cellspacing="5" cellpadding="4">'; 

//Display student picture if there is one on record
$pictureUrl="http://webapps.wooster.edu/webbadge/ShowImage.ashx?id=".$studentId;
if(is_array(getimagesize($pictureUrl)))
{
	echo "<tr><img src=\"".$pictureUrl."\" width=\"100\" /></tr>";
}

		   //FOR DISPLAYING PICTURE//<tr><td align="left" nowrap><IMG SRC="'.$picture.'" height="120" width="90" class="darkbd"></td></tr>';

		   echo '<tr><td align="left" nowrap>Modified: </td><td align="left">'.readableDateAndTime( $student['LastModified'] ).' ';
		   if(!empty($student['Modifier'])){
			   $modifier = $dam->viewUser('',$student['Modifier']);
			   echo 'by <a href="mailto:'.$modifier['Email'].'">'.$modifier['FirstName'].' '.$modifier['LastName'].'</a>';
		   }
		   echo '</td></tr>		
		   <tr><td align="left" nowrap>Class year: </td><td align="left">'.$student['CLASS_YEAR'].'</td></tr>
		   <tr><td align="left" nowrap>Birth date: </td><td align="left">';
		   if($student['BIRTHDAY']!="0000-00-00 00:00:00"){ echo readableDate($student['BIRTHDAY']);}
		   else {echo 'Unknown';}
		   echo '</td></tr>
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
				   <br />Home Phone: '.$parent['HOME_PHONE'].'
				   <br />Cell Phone: '.$parent['CELL_PHONE'];

				   //privacy flag
				   if(!empty($parent['PRIVACY_FLAG'])){
					if(strpos($parent['PRIVACY_FLAG'],'N')===false )
						echo '<br />No Contact: '.$parent['PRIVACY_FLAG'];
					else
						echo '<br /><font color="red"><b>No Contact: '.$parent['PRIVACY_FLAG'].'</b></font>';
				   }
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
   echo '</td></tr></table>';
   echo '</div>';
//end subtable one
 
   //begin subtable two
// div one  
echo '<td valign="top" rowspan="3" width="35%" style="background:#f0f0f0" >';
echo '<div style="position:relative;right:0%;left:0%;"></br></br>';
//watching
echo '<a href="./viewstudentpf.php?id='.$studentId.'&viewallissues='.$viewAllIssues.'&viewallcontacts='.$viewAllContacts.'" target="_blank">[Click here for printer-friendly version]</a><br><br>';
if($dam->userIsWatchingStudent( '', $studentId )){
	echo '<img width="13px" src="../img/watchstu.PNG"><b> You ARE currently watching this student</b> <a class="bold" href="./watchstudent.php?watch=0&id='.$studentId.'">[Stop watching]</a>';
}
else{
	if($dam->userCanWatchStudent('', $studentId)){
		echo '<img width="13px" src="../img/nowatch.PNG"><b> You are NOT currently watching this student</b> <a class="bold" href="./watchstudent.php?watch=1&id='.$studentId.'">[Watch]</a>';
	}
}
//first watch
if($reasons=$dam->studentOnFW('',$studentId))
{
	echo '<br><br><img width="13px" src="../img/firstwatch.PNG"><b> This student IS currently on First Watch for: '.$reasons[0];
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
	echo '<br><br><img width="13px" src="../img/nowatch.PNG"><b> This student is NOT currently on First Watch. </b>';

if($dam->userCanModifyFW(''))
{
	// drop down menu strings
	$FWReasonArr = array('Academic', 'Financial', 'Medical', 'Possible Transfer', 'Personal', 'Watch List' );

	echo 	"<form action='./viewstudent.php?id=$studentId' method='POST' target='_self'>
	<p class='colortext'> Add to First Watch List:&nbsp;<select size='1' name='fwreason'>
	</p>";
	for($i=0; $i<sizeof($FWReasonArr); $i++){
		if($FWReasonArr[$i] != ''){
			if(strcmp($FWReasonArr[$i], $FWReason)==0)	echo '<option value="'.$FWReasonArr[$i].'" SELECTED>'.$FWReasonArr[$i].'</option>';
			else								echo '<option value="'.$FWReasonArr[$i].'">'.$FWReasonArr[$i].'</option>';
		}
	}
	echo "</select>&nbsp;<input type='submit' name='addtofw' value='Add to FW'></form>";
}

// for assignment select
//echo "<br><br>";
echo 	"<form action='./viewstudent.php?id=$studentId' method='POST' target='_self'>
	<p class='colortext'> Assigned to:&nbsp;<select size='1' name='assignedto'>
	<option value=''>(unassigned)</option></p>";
foreach( $dam->getActiveUserSelectList() as $user ) {
	$userid = $user['ID'];
	$label = $user['Label'];
	$selected = "";
	if( $student['AssignedTo'] == $userid ) $selected = " selected";
	echo "<option value='$userid'$selected>$label</option>";
}
echo "</select>&nbsp;<input type='submit' name='reassign' value='Reassign'></form>";
//add user issue
if($dam->userCanCreateContact('', $studentId)){
echo '</br><center><a href="./addcontact.php?isnewissue=1&students='.$studentId.'&Redirect=1" style="font-size:18px";"><b> [Add new student issue]</b></a></center>';
//echo '<div style="position:fixed; top:7.2%;left:45%;z-index:96;">';
//echo '<a href="./addcontact.php?isnewissue=1&students='.$studentId.'&Redirect=1" style="left:65%;"><b> [Add new student issue]</b></a></div>';
	      }
echo '</div>';
// div two

echo '<div style="position:relative;">'; 
echo '<table><tr><td>';
      echo '<tr><td>'; 

      // files section
	   include('./studentfiles.php');
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
   // end subtable two

   echo '</div>';
   echo '</td>';
  echo '<td valign="top" rowspan="3" width="35%">';
   echo '<div style="position:relative;">';
   // subtable three
  echo '<table><tr><td>'; 

// issue section

echo '<p><p><table cellspacing="3"><tr><td nowrap><p class="largeheading">Issue History</p></td>';

         if($viewAllIssues) echo '<td nowrap><a href="./viewstudent.php?id='.$studentId.'&viewallissues=0&viewallcontacts='.$viewAllContacts.'"><b>[Reduce]</b></a>';
         else echo '<td nowrap><a href="./viewstudent.php?id='.$studentId.'&viewallissues=1&viewallcontacts='.$viewAllContacts.'"><b>[View All]</b></a>';

	      if($dam->userCanCreateContact('', $studentId)){
		      // Michael Thompson * 12/14/2005 * Made redirect cap to match other routines
	      }
      echo '</tr></table></p></p>';
      include( './studentissuesnew.php' );
   echo '</td></tr></table>';
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

// since user is viewing page, we can remove student from user's alerts
$dam->removeStudentFromAlerts($studentId);

?>

