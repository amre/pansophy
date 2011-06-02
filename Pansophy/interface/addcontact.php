<?php 

/** 
 * Page that handles issue and contact creation. Both completely new issues and new contacts attached
 * to an existing issue can be created. Uses batchstudents.inc to determine students involved. Calls 
 * attachfiles.php if user wants to attach a file to the contact.
 */

//+-----------------------------------------------------------------------------------------+

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
include('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

// set vars for dropdown menus
$statuses = 	array('Open', 'Closed', 'Special Case'); 
$categories = 	array('Academic - Grades', 'Academic - Other', 'Health', 'Family', 'Emergency', 'Financial', 
					  'Social', 'Transfer', 'Housing', 'Judicial', 'Complaints', 'International', 'Other');
$watchTypes = 	array('Do not watch','Just me','First Watch','Everyone');

// declare
$issueID = ''; // id number of issue
$isNewIssue = ''; // true or false
$header = ''; // issue header
$status = ''; // issue status
$category = '';	// type of issue
$level = ''; // issue level (A or B)
$students = ''; // list of students associated w issue
$description = ''; // contact description
$watch = ''; // who watches this contact
$attach = ''; // true or false
$dateCreated = date('m/d/Y');

// retrieve variables
if(isset($_POST['issueid'])) $issueID = $_POST['issueid'];
if(isset($_POST['isnewissue'])) $isNewIssue = $_POST['isnewissue'];
if(isset($_POST['header'])) $header = $_POST['header']; 
if(isset($_POST['status'])) $status = $_POST['status'];
if(isset($_POST['category'])) $category = $_POST['category'];
if(isset($_POST['level'])) $level = $_POST['level'];
if(isset($_POST['students'])) $students = $_POST['students'];	
if(isset($_POST['description'])) $description = $_POST['description'];
if(isset($_POST['watch'])) $watch = $_POST['watch'];
if(isset($_POST['attach'])) $attach = $_POST['attach'];
if(isset($_POST['datecreated'])) $dateCreated = $_POST['datecreated'];
//+-----------------------------------------------------------------------------------------+


// bring in data by other means if this is the first time the page has been brought up
if(!isset($_POST['submit'])){
	// issueID and isNewIssue might come from GET
	if(isset($_GET['issueid'])) $issueID = $_GET['issueid'];
	if(isset($_GET['isnewissue'])) $isNewIssue = $_GET['isnewissue'];
	if(isset($_GET['students'])) $students = $_GET['students'];
	
	// students list
	if(empty($students)){
		$students = $dam->viewIssue('', $issueID);
		$students = $students['Students'];
	}
	
	// set some defaults
	$watch = 'Do not watch';
        $status = 'Closed';
}
// if the user has cancelled, return to either the main menu or the parent issue page
else if(strcmp($_POST['submit'], 'Cancel') == 0){
	if($isNewIssue) echo '<meta http-equiv="Refresh" content="0; URL=../main.php?">';
	else			echo '<meta http-equiv="Refresh" content="0; URL=./viewissue.php?id='.$issueID.'">';
	exit();
}
// when the user finally submits the form, this code processes the user input and creates the new issue/contact
else if(strcmp($_POST['submit'], 'Submit') == 0){
	
	// give who watches a numerical value
	if(strcmp($watch, 'Just me') == 0)				$watch=1;
	else if (strcmp($watch,'Do not watch') == 0)	$watch=0;
	else if(strcmp($watch,'First Watch') == 0)		$watch=3;
	else											$watch=2;

	// give issue level a numeric value
	if($level=='A')	$level=1;
	else			$level=2;	
	
	// set date created
	if(strcmp($dateCreated,date('m/d/Y')) == 0) $sqlDate = sqlDate('');
   else $sqlDate = sqlDate($dateCreated);

	// we must check that our variables have values
	// DateCreated, Students, and Description are used in both add contact and add issue
	if($sqlDate === false){
		echo '<p class="errortext">Error: Incorrect date format. Try to input the date as \'mm/dd/yyyy\'.</p>';
		$_POST['submit'] = 0;
	}
	if(empty($students)){
		echo '<p class="errortext">Error: You must have at least one student associated.</p>';
		$_POST['submit'] = 0;
	}
	if(empty($description)){
		echo '<p class="errortext">Error: You must fill in a description.</p>';
		$_POST['submit'] = 0;
	}
	
	// issue creation
	if($isNewIssue){
		
		// check that all new issue specific vars are filled out
		if(empty($header)){
			echo '<p class="errortext">Error: You must fill in a header.</p>';
			$_POST['submit'] = 0;
		}
		
		// create an issue in the database
		if(strcmp($_POST['submit'],'Submit') == 0){
			$issueID = $dam->createIssue( '', $header, $status, $sqlDate, $students, $description, $watch, $level, $category );
			if(isset($_POST['attach']) && $_POST['attach'] == 'true'){
				// since the issue is newly created, we must retrieve the initial contact in order to attach a file to it
				$contactArr = $dam->getIssuesContacts($issueID);
				if(count($contactArr) != 1){
					echo 'A strange error has occured, please retry';
				}
				else{
					$contactID = $contactArr[0];
					echo '<meta http-equiv="Refresh" content="0; URL=./attachfiles.php?issueid='.$issueID.'&contactid='.$contactID.'">';
					exit();
				}
			}			
			else if ( isset($_POST['Redirect']) && $_POST['Redirect'] != 0 ){
				$RID=$_POST['Redirect'];
				echo '<meta http-equiv="Refresh" content="0; URL=./viewstudent.php?id='.$RID.'">';
				exit();
			}
			else {
				echo '<meta http-equiv="Refresh" content="0; URL=./viewissue.php?id='.$issueID.'">';
				exit();
			}
		}
	}
	// contact creation
	else{
		// double check that issue is not null
		if(empty($issueID)){
			echo '<p class="errortext">Error: Issue value is null.</p>';
			$_POST['submit'] = 0;
		}
		
		// create a new contact in the database
		if(strcmp($_POST['submit'],'Submit') == 0){
			$contactID = $dam->createContact( '', $sqlDate, $students, $description, $issueID, $watch);
			if($_POST['attach'] == 'true'){
				echo '<meta http-equiv="Refresh" content="0; URL=./attachfiles.php?issueid='.$issueID.'&contactid='.$contactID.'">';
				exit();
			}
			else{
				echo '<meta http-equiv="Refresh" content="0; URL=./viewissue.php?id='.$issueID.'">';
				exit();
			}
		}
	}	
}

//+-----------------------------------------------------------------------------------------+

// do some unmagic
antiMagic();

// the whole page is one giant form, starting here
echo '<form action="addcontact.php" method="POST" target="_self">';

// here is the default section for displaying the main issue/contact creation form

	// the top part of the display is different for new issue and new contact
	if($isNewIssue){
		echo '<center><p class="largecolorheading">Create a New Issue</p></center>';
		// start table

		echo '<table width="50%" >
		<tr cellpadding="0" cellspacing="0"><td align="left"></td><td></td></tr>';
		// display header text box
		echo '<tr><td><b>Header (100 char limit):</b></td><td><input type="text" name="header" maxlength="100" size=50" value="'.htmlspecialchars($header).'"></td></tr>
		<tr><td height="10"></td></tr>';
		
      // display date
      echo '<tr><td><b>Date:</b></td><td><input type="text" name="datecreated" maxlength="10" size=10" value="'.htmlspecialchars($dateCreated).'"></td></tr>
		<tr><td height="10"></td></tr>';

		// display status options
		echo '<tr><td align="left"><b>Status:</b></td><td align="left"><select size="1" name="status">';
		for($i=0; $i<sizeof($statuses); $i++){
			if(strcmp($statuses[$i], $status)==0)	echo '<option value="'.$statuses[$i].'" SELECTED>'.$statuses[$i].'</option>';
			else									echo '<option value="'.$statuses[$i].'">'.$statuses[$i].'</option>';
		}
		echo '</td></tr><tr><td height="10"></td></tr>';
		
		// display category options
		echo '<tr><td align="left"><b>Category:</b></td><td align="left"><select size="1" name="category">';
		for($i=0; $i<sizeof($categories); $i++){
			if(strcmp($categories[$i], $category)==0)	echo '<option value="'.$categories[$i].'" SELECTED>'.$categories[$i].'</option>';
			else										echo '<option value="'.$categories[$i].'">'.$categories[$i].'</option>';
		}
		echo '</td></tr><tr><td height="10"></td></tr>';
		
		// display issue level options	
		echo '<tr><td><b>Issue Level:</b></td><td>';
		if(strcmp($level,'A')==0){
			echo '<input type="radio" name="level" value="A" CHECKED>Sensitive
			<input type="radio" name="level" value="B">Normal';
		}
		else{
			echo '<input type="radio" name="level" value="A">Sensitive
			<input type="radio" name="level" value="B" CHECKED>Normal';
		}
		echo '</td></tr><tr><td height="10"></td></tr>';
	

	}
	else{
		echo '<center><p class="largecolorheading">Append a New Contact to '.$issueID.'</p></center>';

		// start table
		echo '<table width="80%" >
		<tr cellpadding="0" cellspacing="0"><td align="left"></td><td></td></tr>';

      // display date
      echo '<tr><td><b>Date: </b></td><td><input type="text" name="datecreated" maxlength="10" size=10" value="'.htmlspecialchars($dateCreated).'"></td></tr>
		<tr><td height="10"></td></tr>';
	}
	
	// all form components that follow are shared by both new issue and new contact //
	
	// prepare and list students
	echo '<tr cellpadding="0" cellspacing="0"><td align="left"><b>Students Associated:</b></td>';

   // adding/removing a student using batchstudents.inc
   include('./batchstudents.inc');
      echo '<table width="50%" >
		<tr cellpadding="0" cellspacing="0"><td align="left"></td><td></td></tr>';
     // description box
	echo '<tr><td valign="top"><b>Description:</b></td><td><textarea wrap=virtual cols="60" rows="10" name="description">'.$description.'</textarea></td></tr>';

// attach file options
        echo '<br>';
	echo '</td></tr><tr><td height="10"></td></tr>';
	echo '<tr><td align="left"><b>Attach Files:</b></td><td align="left">';
	if(strcmp($attach,'true') == 0)	echo '<input type="checkbox" name="attach" value="true" checked/></td></tr>';
	else					echo '<input type="checkbox" name="attach" value="true"/></td></tr>'; 
	
	// watch options
echo '<br>';
	echo '<tr><td height="10"></td></tr>';
	echo '<tr><td align="left"><b>Watch Assignment:</b></td><td align="left"><select size="1" name="watch">';
	for($i=0; $i<sizeof($watchTypes); $i++){
		if(strcmp($watchTypes[$i],$watch) == 0)	echo '<option value="'.$watchTypes[$i].'" SELECTED>'.$watchTypes[$i].'</option>';
		else									echo '<option value="'.$watchTypes[$i].'">'.$watchTypes[$i].'</option>';
	}

	// end table
	echo '<tr><td height="10"></td></tr></table>';
	// submit/cancel buttons
	echo '<input type="submit" name="submit" value="Cancel"> <input type="submit" name="submit" value="Submit">';


// set hidden variables passed via post
echo '
<input type="hidden" name="issueid" value="'.$issueID.'">
<input type="hidden" name="isnewissue" value="'.$isNewIssue.'">
<input type="hidden" name="students" value="'.$students.'">';

// end form
echo '</form>';

// end html started in header.inc
echo '</body></html>';


//+-----------------------------------------------------------------------------------------+

// function to counteract the mischief done by magic_quotes
function antiMagic()
{
   global $header, $description, $dateCreated;
   
   if(get_magic_quotes_gpc()){
      $header = stripslashes($header);
      $description = stripslashes($description);
      $dateCreated = stripslashes($dateCreated);
   }
}

?>
