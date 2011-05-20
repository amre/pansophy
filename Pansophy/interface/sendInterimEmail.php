<?php

/**
 * Displays and sends an email containing interim information to the student concerned.
 */

//+-----------------------------------------------------------------------------------------+ 
 
include('../include/header.inc');
include('../DataAccessManager.inc.php');
include('../include/Email.inc.php');
include('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

// globals
$interimId;
$interim;
$student;
$TO='';
$CC='';
$SUBJECT='';
$BODY='';

// check user access level
if($dam->userCanSendInterim('')){

   // start form
   echo '<form action="sendInterimEmail.php" method="POST" target="_self">';

   // retrieve data
   getData();

   // if a button has been pressed
   if(isset($_POST['submit'])){
      
      // go back button pressed
      if(strcmp($_POST['submit'], 'Go Back') == 0){

         // return to the interim page
         echo '<meta http-equiv="Refresh" content="0; URL=./viewinterim.php?id='.$interimId.'">';
         exit();
      }
      
      // send button pressed
      else if(strcmp($_POST['submit'], 'Send') == 0){

         // Send
         $headers = getExtraHeaders();
         if($dam->validateEmail($TO,$SUBJECT,$BODY,$headers) === true)
            $mailsent = mail($TO,$SUBJECT,$BODY,$headers);
         else{
            echo '<font color="red">Mail could not be validated!</font><br>';
            $mailsent = false;
         }

         // error/ success msg
         if($mailsent){
            echo '<font color="green">Email has been sent!</font><br>';
            $dam->createInterimEmailContact($interimId);
         }
         else {
            echo '<font color="red">Could not send mail!</font><br>';
         }

      }
   }

   // display the page
   antiMagic();
   displayPage();

   // save data
   setData();

   // end form
   echo '</form>';

}
// user access level doesn't check out
else{
   echo '<script language=javascript>alert("You don\'t have access to view this page. Redirecting to the main page.");</script>
	<meta http-equiv="Refresh" content="0; URL=../index.php">';
}

// end html started in header.inc
echo '</body></html>';

//+-----------------------------------------------------------------------------------------+

// puts all variables into POST
function setData()
{
   global $interimId; 
   echo '<input type="hidden" name="interimid" value="'.$interimId.'">';
}

// retrieve all variables from POST
function getData()
{
   global $dam,$interimId,$interim,$student,$TO,$CC,$SUBJECT,$BODY;

   // if this is the first time displaying the page
   if(!isset($_POST['submit'])){
      $interimId = $_GET['interimid'];
      $interim = $dam->viewInterim('',$interimId);
      $student = $dam->preInterimInformation($interim['StudentID']);
      $TO = getTO();
      $CC = getCC();
      $SUBJECT = getSUBJECT();
      $BODY = getBODY(0);
   }

   // a button has been pressed and this isnt the first time displaying the page
   else{

      // get form data via post
      if(isset($_POST['interimid'])) $interimId = $_POST['interimid'];
      if(isset($_POST['TO'])) $TO = $_POST['TO'];
      if(isset($_POST['CC'])) $CC = $_POST['CC'];
      if(isset($_POST['SUBJECT'])) $SUBJECT = $_POST['SUBJECT'];
      if(isset($_POST['BODY'])) $BODY = $_POST['BODY'];
   }
}

// function to counteract the mischief done by magic_quotes
function antiMagic()
{
   global $TO,$CC,$SUBJECT,$BODY; 

   if(get_magic_quotes_gpc()){
      $TO = stripslashes($TO);
      $CC = stripslashes($CC);
      $SUBJECT = stripslashes($SUBJECT);
      $BODY = stripslashes($BODY);
   }
}

// displays the page
function displayPage()
{
   global $TO,$CC,$SUBJECT,$BODY; 

   echo '<input type="submit" name="submit" value="Go Back"><input type="submit" name="submit" value="Send">';
   echo '<table width="80%" ><tr valign="top"><td align="left" width="10%">';

      echo '<b>TO: </b></td><td align="left"><input type="text" name="TO" value="'.htmlspecialchars($TO).'" maxlength="80" size="80">';

   echo '</td></tr>';
	echo '<tr valign="top"><td align="left">';

      echo '<b>CC: </b></td><td align="left"><input type="text" name="CC" value="'.htmlspecialchars($CC).'" maxlength="80" size="80">';

   echo '</td></tr>';
   echo '<tr valign="top"><td align="left">';

      echo '<b>SUBJECT: </b></td><td align="left"><input type="text" name="SUBJECT" value="'.htmlspecialchars($SUBJECT).'" maxlength="80" size="80">';

   echo '</td></tr>';
   echo '<tr valign="top"><td align="left">';

      echo '<b>BODY: </b></td><td align="left"><textarea name="BODY" cols="92" rows="30">'.$BODY.'</textarea>';

   echo '</td></tr></table>';
}

// get TO address
function getTO()
{
   global $student; 
   if(empty($student)) return;

   $to = $student['WOOSTER_EMAIL'];

   //$to = "";
   return $to;
}

// get CC addresses, return as string with comma delimiter
function getCC()
{
   global $dam,$interim,$student;

   if(empty($interim) || empty($student)) return;

   extract($interim);
   extract($student);

   $cc = 'dos@wooster.edu';

   // append additional recipients if necessary
   $centers = $dam->selectEmails();

   // does Writing Center get mailed?
   if(strpos($RecommendAction,'Writing Center') !== false){
      if(!empty($centers['WritingCenter'])) $cc .= ','.$centers['WritingCenter'];
   }

   // does Learning Center get mailed?
   if(strpos($RecommendAction,'Learning Center') !== false){
      if(!empty($centers['LearningCenter'])) $cc .= ','.$centers['LearningCenter'];
   }

   // does Math Center get mailed?
   if(strpos($RecommendAction,'Math Center') !== false){
      if(!empty($centers['MathCenter'])) $cc .= ','.$centers['MathCenter'];
   }

   // does a Dean get emailed?
   $index = strpos($RecommendAction,'Dean');
   if($index !== false){
      $deanEmail = substr($RecommendAction,$index + 6);
      $deanEmail = substr($deanEmail,0,strpos($deanEmail,']'));
      if(!empty($deanEmail)) $cc .= ','.$deanEmail;
   }

   // does the students faculty advisor get emailed?
   if(strpos($RecommendAction,'Faculty Adviser') !== false){
      $advisor = $dam->viewStudentAdvisor('',$StudentID);
		if(!empty($advisor)){
         if(!empty($advisor['WOOSTER_EMAIL'])) $cc .= ','.$advisor['WOOSTER_EMAIL'];
      }
   }

   // does the course instructor get emailed?
   if(strpos($RecommendAction,'Course Instructor') !== false){
      $faculty = $dam->findInstructor($Instructor);
      if($faculty !== false){
         if(!empty($faculty['WOOSTER_EMAIL'])) $cc .= ','.$faculty['WOOSTER_EMAIL'];
      }
   }

   //$cc = "";
   return $cc;
}

// gets the extra headers for the php mail function
function getExtraHeaders()
{
   global $CC;
   $headers = 'From: Dean of Students <dos@wooster.edu>'. "\r\n";
   if(!empty($CC)) $headers .= 'Cc: '.$CC. "\r\n";
   return $headers;
}

// get email subject
function getSUBJECT()
{
   return "Interim Report Notification";
}

// get email body
function getBODY($i)
{
   global $dam,$interim,$student;
   extract($interim);
   extract($student);

   $break = array("","\n","<br>");
   $b = $break[$i];

$body = "Dear ".$FIRST_NAME.",
".$b."
".$b."It has come to our attention that you are experiencing difficulty in ".$CourseNumberTitle.".
".$b."
".$b."We encourage you to discuss your academic situation with one of the Deans on campus.  You may call 330-263-2545 or Extension 2545, to make an appointment.  If you are already meeting with a Dean in our office on a regular basis, please discuss this interim report at your next scheduled meeting.
".$b."
".$b."We look forward to hearing from you.
".$b."
".$b."Sincerely,
".$b."
".$b."Carolyn L. Buxton
".$b."Senior Associate Dean of Students
".$b."
".$b."Anne Gates
".$b."Associate Dean of Students
".$b."
".$b."Robyn Laditka
".$b."Assistant Dean of Students
".$b."
".$b."Susan Lee
".$b."Assistant Dean of Students
".$b."
".$b."
".$b."
".$b;

   $body .= getINTERIM($i);

   return $body;
}

// gets a copy of the interim text
function getINTERIM($i)
{
   global $dam,$interim,$student;
   extract($interim);
   extract($student);

   $break = array("","\n","<br>");
   $b = $break[$i];

   $body = "***********************************
".$b."   Copy of Interim Report below.
".$b."***********************************
".$b."
".$b."Student ID:
   ".$StudentID."
".$b."
".$b."Student Name:
   ".$FIRST_NAME." ".$MIDDLE_NAME." ".$LAST_NAME."
".$b."
".$b."Class Year:
   ".$CLASS_YEAR."
".$b."
".$b."Course:
   ".$CourseNumberTitle."
".$b."
".$b."Instructor:
   ".$Instructor."
".$b."
".$b."Date:
   ".readableDate($Date)."
".$b."
".$b."Problem:";

   $problems = explode(";",$Problem);
   for($i = 0; $i < count($problems); $i++){
      $body .= "".$b."   ".$problems[$i];
   }
   $body .= "".$b."
".$b."Comments:
".$b."   ".stripslashes($Comments)."
".$b."
".$b."Recommended Action:";

   $actions = explode(";",$RecommendAction);
   for($i = 0; $i < count($actions); $i++){
      if(strcmp($actions[$i],"Conference with a Dean []") == 0) $body .= $b."   Conference with a Dean";
      else $body .= $b."   ".$actions[$i];
   }
   $body .= $b."
".$b."Other Recommended Action:
".$b."   ".stripslashes($OtherAction)."
".$b."
".$b;

   return $body;
}

?>
