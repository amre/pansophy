<?php

/**
 * Form for adding an interim report.
 */

//+-----------------------------------------------------------------------------------------+ 

include('../include/header.inc');
include('../DataAccessManager.inc.php');
include('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+ 


// global variables
$pageNum = 1;
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
$text = '';


// check user access level
if(!$dam->userCanCreateInterim('')){
   echo '<script language=javascript>alert("You don\'t have access to view this page. Redirecting to the main page.");</script>
	<meta http-equiv="Refresh" content="0; URL=../index.php">';
}
// user access level is okay, display page
else{

   // start form
   echo '<form action="addinterim.php" method="POST" target="_self">';

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

      // check name button pressed
      else if(strcmp($_POST['submit'], 'Verify') == 0){
      }

      // skip button pressed
      else if(strcmp($_POST['submit'], 'Skip') == 0){
         // move to next page without parsing
         $pageNum = 2;
      }

      // process button pressed
      else if(strcmp($_POST['submit'], 'Process') == 0){
         // parse whats in the text box and move to next page
         $pageNum = 2;
         parseInterim();
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

            // submit the interim to database
            $interim = $dam->createInterim('',$studentId,$course,$instructor,$formatedDate,$problemString,$comment,$actionString,$otherAction);

            // go to the page for the new interim
            echo '<meta http-equiv="Refresh" content="0; URL=./viewinterim.php?id='.$interim.'">';
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
//+   FUNCTIONS                                                                             |
//+-----------------------------------------------------------------------------------------+ 

// puts all variables into POST
function setData()
{
   global $pageNum; 

   echo '<input type="hidden" name="pagenum" value="'.$pageNum.'">';
}

// retrieve all variables from POST
function getData()
{
   global $pageNum,$studentId,$course,$instructor,
          $date,$problem,$hasProblem,$comment,$action,
          $hasAction,$deanEmail,$otherAction,$text;

   // get problem status
   for($i = 0; $i < count($problem); $i++){
      if(isset($_POST['problem'.$i.'']) && strcmp($_POST['problem'.$i.''],"on") == 0) $hasProblem[$i] = 1;
   }   

   // get action status
   for($i = 0; $i < count($action); $i++){
      if(isset($_POST['action'.$i.'']) && strcmp($_POST['action'.$i.''],"on") == 0) $hasAction[$i] = 1;
   }  

   // get other stuff
   if(isset($_POST['pagenum'])) $pageNum = $_POST['pagenum'];
   if(isset($_POST['studentid'])) $studentId = $_POST['studentid'];
   if(isset($_POST['course'])) $course = $_POST['course'];
   if(isset($_POST['instructor'])) $instructor = $_POST['instructor'];
   if(isset($_POST['date'])) $date = $_POST['date'];
   if(isset($_POST['comment'])) $comment = $_POST['comment'];
   if(isset($_POST['deanemail'])) $deanEmail = $_POST['deanemail'];
   if(isset($_POST['otheraction'])) $otherAction = $_POST['otheraction'];
   if(isset($_POST['text'])) $text = $_POST['text'];
}

// function to counteract the mischief done by magic_quotes
function antiMagic()
{
   global $studentId,$course,$instructor,
          $date,$comment,$deanEmail,$otherAction,$text;

   if(get_magic_quotes_gpc()){
      $studentId = stripslashes($studentId);
      $course = stripslashes($course);
      $instructor = stripslashes($instructor);
      $date = stripslashes($date);
      $comment = stripslashes($comment);
      $deanEmail = stripslashes($deanEmail);
	   $otherAction = stripslashes($otherAction);
      $text = stripslashes($text);
   }
}

// parses data inputed by user
function parseInterim()
{
   global $studentId,$course,$instructor,
          $date,$hasProblem,$comment,$hasAction,
          $otherAction,$text;

   // gather together the indices of substring postions
   $indices = array();
   $indices[] = strpos($text,"STUDENT ID:");
   $indices[] = strpos($text,"STUDENT NAME:");
   $indices[] = strpos($text,"COURSE NUMBER & TITLE:");
   $indices[] = strpos($text,"INSTRUCTOR:");
   $indices[] = strpos($text,"DATE:");
   $indices[] = strpos($text,"PROBLEM:");
   $indices[] = strpos($text,"COMMENTS:");
   $indices[] = strpos($text,"RECOMMENDED ACTION:");
   $indices[] = strpos($text,"OTHER RECOMMENDED ACTION:");
   $indices[] = strpos($text,"ACADEMIC ADVISER:");

   // extract student id
   if($indices[0] !== false && $indices[1] !== false){
      $start = $indices[0] + 12;
      $length = $indices[1] - $start;
      $chunk = substr($text,$start,$length);
      $studentId = trim($chunk);     
   }   

   // extract course number and title
   if($indices[2] !== false && $indices[3] !== false){
      $start = $indices[2] + 23;
      $length = $indices[3] - $start;
      $chunk = substr($text,$start,$length);
      $course = trim($chunk);     
   }  

   // extract intstructor name
   if($indices[3] !== false && $indices[4] !== false){
      $start = $indices[3] + 12;
      $length = $indices[4] - $start;
      $chunk = substr($text,$start,$length);
      $instructor = trim($chunk);     
   } 

   // extract date
   if($indices[4] !== false && $indices[5] !== false){
      $start = $indices[4] + 6;
      $length = $indices[5] - $start;
      $chunk = substr($text,$start,$length);
      $unformattedDate = trim($chunk);
      $date = readableDate($unformattedDate);
   } 

   // extract problems
   if($indices[5] !== false && $indices[6] !== false){
      $start = $indices[5] + 9;
      $length = $indices[6] - $start;
      $chunk = substr($text,$start,$length);

      // search for unique strings
      if (strpos($chunk, "Poor Class Attendance") !== false)
         $hasProblem[0] = 1;
      if (strpos($chunk, "Poor Class Participation") !== false)
         $hasProblem[1] = 1;
      if (strpos($chunk, "Failure to Turn in") !== false)
         $hasProblem[2] = 1;
      if (strpos($chunk, "Poor Performance on") !== false)
         $hasProblem[3] = 1;
      if (strpos($chunk, "Has Not Responded to") !== false)
         $hasProblem[4] = 1;
      if (strpos($chunk, "Attended For a While") !== false)
         $hasProblem[5] = 1;
      if (strpos($chunk, "No Show, Never Attended") !== false)
         $hasProblem[6] = 1;
      if (strpos($chunk, "Student Reports That") !== false)
         $hasProblem[7] = 1;  
   } 

   // extract comment
   if($indices[6] !== false && $indices[7] !== false){
      $start = $indices[6] + 10;
      $length = $indices[7] - $start;
      $chunk = substr($text,$start,$length);
      $comment = trim($chunk);
   } 

   // extract actions
   if($indices[7] !== false && $indices[8] !== false){
      $start = $indices[7] + 20;
      $length = $indices[8] - $start;
      $chunk = substr($text,$start,$length);

      // search for unique strings
      if (strpos($chunk, "Conference With Course Instructor") !== false)
         $hasAction[0] = 1;
      if (strpos($chunk, "Conference With Faculty Adviser") !== false)
         $hasAction[1] = 1;
      if (strpos($chunk, "Conference With a Dean") !== false)
         $hasAction[2] = 1;
      if (strpos($chunk, "Consultation With Writing Center") !== false)
         $hasAction[3] = 1;  
   } 

   // extract other action
   if($indices[8] !== false && $indices[9] !== false){
      $start = $indices[8] + 26;
      $length = $indices[9] - $start;
      $chunk = substr($text,$start,$length);
      $otherAction = trim($chunk);
   }
}

// returns a standardized date format
function formatDate()
{
   global $date;

   if(empty($date)) $newDate = '';
   else{

      // the DOS wants strings like '07-25-2010' to be recognized, but the
      // strtotime() function doesn't seem to do that. So we use preg_replace...
      $newDate = preg_replace('[\Q-\E]','/',$date); // replaces '-' with '/'

      // now make conversion
      $newDate = strtotime($newDate);
      if($newDate !== false) $newDate = date('Y-m-d',$newDate);
      else $newDate = '';
   }
   return $newDate;
}

// checks that all user inputs are valid
function checkInput()
{
   global $dam,$studentId,$course,$instructor,$date;
   $valid = true;

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
   global $pageNum,$studentId,$course,$instructor,
          $date,$hasProblem,$problem,$comment,$hasAction,
          $action,$deanEmail,$otherAction,$dam;

   // display form for first page
   if($pageNum == 1){

      // text box for parsing      
      echo '<table width="80%" ><tr><td align="left">
            <textarea name="text" cols="100" rows="20"></textarea>
            </td></tr></table>';

      // process/skip buttons
	   echo '<input type="submit" name="submit" value="Process"> <input type="submit" name="submit" value="Skip">';
   }

   // display form for second page
   else{

      echo '<table width="80%" ><tr><td align="left" width="20%">';

         // get the students name
         $studentInfo = $dam->viewStudent('',$studentId);
         $studentWarning = ' Warning: Invalid student ID.';
         if(!empty($studentInfo)) $studentWarning = ' Name: '.$studentInfo['FIRST_NAME'].' '.$studentInfo['LAST_NAME'];

         echo '<b>Student ID: </b></td><td align="left"><input type="text" name="studentid" value="'.htmlspecialchars($studentId).'" maxlength="9" size="40">'.'<input type="submit" name="submit" value="Verify">'.$studentWarning;

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
}

// displays the page
function displayPage()
{
   global $pageNum;

   // display first page
   if($pageNum == 1){

      // title and instructions
      echo '<h1>Import Interim Report</h1>';
      echo '<p class="mediumhead">Copy and paste the text from the interim report e-mail into the textbox below.<br />
            Start at the ID and copy down to the end of Other Recommended Action.</p>';     
   }
   // display second page
   else{
       echo '<h1>Create Interim Report</h1>';
   }

   displayForm();
}

?>
