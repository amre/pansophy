<?php

/**
 * Form for editing an interim report.
 */

//+-----------------------------------------------------------------------------------------+ 

include('../include/header.inc');
include('../DataAccessManager.inc.php');
include('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+ 

// global variables
$interimId = '';
$studentId = '';
$course = '';
$instructor = '';
$date = '';
$hasProblem = array(0,0,0,0,0,0,0,0);
$problem = array("Poor Class Attendance","Poor Class Participation","Failure to Turn in Assignments",
					  "Poor Performance on Assignments/Examinations","Has Not Responded to My Offer of Assistance",
					  "Attended For a While and Then Disappeared","No Show, Never Attended",
					  "Student Reports That She/He Had Medical, Emotional, Personal, or Family Problems");
$comment = '';
$hasAction = array(0,0,0,0,0,0);
$action = array(  "Conference with Course Instructor","Conference with Faculty Adviser",
					   "Conference with a Dean","Consultation with Writing Center",
					   "Consultation with Math Center","Consultation with Learning Center");
$deanEmail = '';
$otherAction = '';


// check user access level
if(!$dam->userCanEditInterim('')){
   echo '<script language=javascript>alert("You don\'t have access to view this page. Redirecting to the main page.");</script>
	<meta http-equiv="Refresh" content="0; URL=../index.php">';
}
// user access level is okay, display page
else{

   // start form
   echo '<form action="editinterim.php" method="POST" target="_self">';

   // retrieve data
   getData();

   // if a button has been pressed
   if(isset($_POST['submit'])){
      
      // cancel button pressed
      if(strcmp($_POST['submit'], 'Cancel') == 0){
         // return to main menu
         echo '<meta http-equiv="Refresh" content="0; URL=../main.php">';
         exit();
      }

      // submit button pressed
      else if(strcmp($_POST['submit'], 'Submit') == 0){

         // check that user input is valid
         $valid = checkInput();

         // if valid, combine the infomation for submittion
         if($valid){
            // format the date
            $formatedDate = formatDate();
            
            // create a string containing all problems
            $problemString = '';
            for($i = 0; $i < count($problem); $i++){
               if($hasProblem[$i] > 0){
                  if(!empty($problemString)) $problemString .= ';';
                  $problemString .= $problem[$i];
               }
            }   

            // create a string containing all recommended actions
            $actionString = '';
            for($i = 0; $i < count($action); $i++){
               if($hasAction[$i] > 0){
                  if(!empty($actionString)) $actionString .= ';';
                  if($i == 2){
                     $actionString .= $action[$i].' ['.$deanEmail.']';
                  }
                  else $actionString .= $action[$i];
               }
            }  

            // submit updated interim
            $dam->editInterim('',$interimId,$studentId,$course,$instructor,$formatedDate,$problemString,$comment,$actionString,$otherAction);

            // go to the page for the new interim
            echo '<meta http-equiv="Refresh" content="0; URL=./viewinterim.php?id='.$interimId.'">';
            exit();
         }
      }
   }

   // display page
   antiMagic();
   displayPage();

   // save data
   setData();

   // end form
   echo '</form>';
}

// end html started in header.inc
echo '</body></html>';

//+-----------------------------------------------------------------------------------------+ 

// puts all variables into POST
function setData()
{
   global $interimId, $studentId; 

   echo '<input type="hidden" name="interimid" value="'.$interimId.'">';
   echo '<input type="hidden" name="studentid" value="'.$studentId.'">';
}

// retrieve all variables from POST
function getData()
{
   global $dam,$interimId,$studentId,$course,$instructor,
          $date,$problem,$hasProblem,$comment,$action,
          $hasAction,$deanEmail,$otherAction;

   // if this is the first time the page has been accessed
   if(!isset($_POST['submit'])){

      // get interim information
      if(isset($_GET['interimid'])){
         $interimId = $_GET['interimid'];
         $interim = $dam->viewInterim('',$interimId);

         $studentId = $interim['StudentID'];
         $course = $interim['CourseNumberTitle'];
         $instructor = $interim['Instructor'];
         $comment = $interim['Comments'];
         $otherAction = $interim['OtherAction'];
         $date = readableDate($interim['Date']);

         // get problem status
         for($i = 0; $i < count($problem); $i++){
            if(strpos($interim['Problem'],$problem[$i]) !== false){
               $hasProblem[$i] = 1;
            }         
         } 

         // get action status
         for($i = 0; $i < count($action); $i++){
            if(strpos($interim['RecommendAction'],$action[$i]) !== false){ 
               $hasAction[$i] = 1;
               // search for dean email
               if($i == 2){
                  $index = strpos($interim['RecommendAction'],$action[$i].' [');
                  if($index !== false){
                     $deanEmail = substr($interim['RecommendAction'],$index + 24);
                     $deanEmail = substr($deanEmail,0,strpos($deanEmail,']'));
                  }
               }
            }
         } 
      }
   }
   else{

      // get problem status
      for($i = 0; $i < count($problem); $i++){
         if(isset($_POST['problem'.$i.'']) && strcmp($_POST['problem'.$i.''],"on") == 0) $hasProblem[$i] = 1;
      }   

      // get action status
      for($i = 0; $i < count($action); $i++){
         if(isset($_POST['action'.$i.'']) && strcmp($_POST['action'.$i.''],"on") == 0) $hasAction[$i] = 1;
      }  

      // get other stuff
      if(isset($_POST['interimid'])) $interimId = $_POST['interimid'];
      if(isset($_POST['studentid'])) $studentId = $_POST['studentid'];
      if(isset($_POST['course'])) $course = $_POST['course'];
      if(isset($_POST['instructor'])) $instructor = $_POST['instructor'];
      if(isset($_POST['date'])) $date = $_POST['date'];
      if(isset($_POST['comment'])) $comment = $_POST['comment'];
      if(isset($_POST['deanemail'])) $deanEmail = $_POST['deanemail'];
      if(isset($_POST['otheraction'])) $otherAction = $_POST['otheraction'];
   }
}

// function to counteract the mischief done by magic_quotes
function antiMagic()
{
   global $studentId,$course,$instructor,
          $date,$comment,$deanEmail,$otherAction;

   if(get_magic_quotes_gpc()){
      $studentId = stripslashes($studentId);
      $course = stripslashes($course);
      $instructor = stripslashes($instructor);
      $date = stripslashes($date);
      $comment = stripslashes($comment);
      $deanEmail = stripslashes($deanEmail);
	   $otherAction = stripslashes($otherAction);
   }
}

// returns a standardized date format
function formatDate()
{
   global $date;

   if(empty($date)) $newDate = '';
   else{
      $newDate = strtotime($date);
      if($newDate !== false) $newDate = date('Y-m-d',$newDate);
      else $newDate = '';
   }
   return $newDate;
}

// checks that all user inputs are valid
function checkInput()
{
   global $dam,$interimId,$studentId,$course,$instructor,$date;
   $valid = true;

   // check interim id
   if(empty($interimId)){
      $valid = false;
   }  

   // check student id
   $student = $dam->verifyStudent($studentId);
   if(empty($student)){
      echo '<font color="red">Sorry, there is no student that matches the \'Student ID\' provided.</font><br>';
      $valid = false;
   }     

   // check date
   $formatedDate = formatDate();
   if(empty($formatedDate)){ 
      echo '<font color="red">Sorry, invalid \'Date\' format. Try to input the date as \'mm/dd/yyyy\'.</font><br>';
      $valid = false;
   }

   // return result
   return $valid;
}

// displays the form on the page
function displayForm()
{
   global $studentId,$course,$instructor,
          $date,$hasProblem,$problem,$comment,$hasAction,
          $action,$deanEmail,$otherAction,$dam;

   echo '<table width="80%" ><tr><td align="left" width="20%">';

      // get the students name
      $studentInfo = $dam->viewStudent('',$studentId);
      $studentName = '';
      if(!empty($studentInfo)) $studentName = ' ('.$studentInfo['FIRST_NAME'].' '.$studentInfo['LAST_NAME'].')';

      echo '<b>Student ID: </b></td><td align="left">'.$studentId.' '.$studentName;

   echo '</td></tr>';
	echo '<tr><td align="left" width="20%">';

      echo '<b>Course Number & Title: </b></td><td align="left"><input type="text" name="course" value="'.htmlspecialchars($course).'" size="40" maxlength="100">';

   echo '</td></tr>';
   echo '<tr><td align="left" width="20%">';

      $faculty = $dam->findInstructor($instructor);
      $facultyWarning = '';
      if($faculty === false && !empty($instructor)) $facultyWarning = ' Notice: No Wooster Email exists for name given.';
      else if(!empty($faculty['WOOSTER_EMAIL'])) $facultyWarning = ' Email: '.$faculty['WOOSTER_EMAIL'];
      echo '<b>Instructor (First Last): </b></td><td align="left"><input type="text" name="instructor" value="'.htmlspecialchars($instructor).'" size="40" maxlength="30">'.$facultyWarning;

   echo '</td></tr>';
	echo '<tr><td align="left" width="20%">';

      echo '<b>Date: </b></td><td align="left"><input type="text" name="date" value="'.htmlspecialchars($date).'" size="40">';
   
   echo '</td></tr>';
	echo '<tr><td align="left" valign="top" width="20%">';

      echo '<b>Problems: </b></td><td align="left">';
	   for($i = 0; $i < count($problem); $i++){
         echo '<input type="checkbox" name="problem'.$i.'"';
         if($hasProblem[$i] > 0)
            echo 'CHECKED>';
         else
            echo '>';
         echo $problem[$i].'<br />';
      }

	echo '</td></tr>';
   echo '<tr><td align="left" valign="top" width="20%">';

      echo '<b>Comments: </b></td><td align="left"><textarea name="comment" cols="80" rows="10">'.$comment.'</textarea>';

   echo '</td></tr>';
	echo '<tr><td align="left" valign="top" width="20%">';
   
      echo '<b>Recommended Action: </b></td><td align="left">';
	   for($i = 0; $i < count($action); $i++){
         echo '<input type="checkbox" name="action'.$i.'"';
         if($hasAction[$i] > 0)
            echo 'CHECKED>';
         else
            echo '>';
         echo $action[$i].' ';

         if($i == 2) echo ' [Email]: <input type="text" name="deanemail" value="'.htmlspecialchars($deanEmail).'" size="30"><br />';
         else echo '<br />';
      }

	echo '</td></tr>';
   echo '<tr><td align="left" width="20%" valign="top">';

         echo '<b>Other Recommended Action: </b></td><td align=" left"><textarea name="otheraction" cols="80" rows="10">'.$otherAction.'</textarea>';
   
	echo '</td></tr></table>';

   // submit/cancel buttons
   echo '<input type="submit" name="submit" value="Cancel"> <input type="submit" name="submit" value="Submit">';
}

// displays the page
function displayPage()
{
   global $interimId;
   echo '<h1>Edit Interim Report - '.$interimId.'</h1>';
   displayForm();
}

?>
