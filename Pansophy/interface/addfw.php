<?php 

/**
 * Page that handles adding student to First Watch. Uses batchstudents.inc to 
 * determine a list of students to add.
 */

//+-----------------------------------------------------------------------------------------+

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

// drop down menu strings
$FWReasonArr = array('Academic', 'Financial', 'Medical', 'Possible Transfer', 'Personal', 'Watch List' );

// retrieve variables
if(isset($_POST['fwreason'])){
	$FWReason = $_POST['fwreason'];	// currently selected reason for adding student to first watch
}
else{
	$FWReason = 'Academic';
}
if(isset($_POST['students'])){
	$students = $_POST['students']; // list of students to be flagged
}
else{
	$students = "";
}

//+-----------------------------------------------------------------------------------------+

// if this is the first time calling the page
if(!isset($_POST['submit'])){
}
// if the user has cancelled, return to  user task page
else if(strcmp($_POST['submit'], 'Cancel') == 0){
	echo '<meta http-equiv="Refresh" content="0; URL=./tasks.php?">';
	//exit('Loading...');
}
// when the user finally submits the form, this code processes the user input and updates first watch list
else if(strcmp($_POST['submit'], 'Submit') == 0){	
	
	// check to make sure there are students selected
	if(empty($students)){
		echo '<p class="errortext">Error: You must select at least one student.</p>';
		$_POST['submit'] = 0;
	}
	
	// update first watch list in db
	if(strcmp($_POST['submit'],'Submit') == 0){
		$onfw['FirstWatch'] = 1;
		$studentArr = explode(',', $students);
		for($i=0; $i<count($studentArr); $i++){
			//$student = $dam->modifyStudent('', $studentArr[$i], $onfw);
			$dam->placeOnFirstWatch('', $studentArr[$i], $FWReason);
		}
		
		echo '<meta http-equiv="Refresh" content="0; URL=./viewfirstwatch.php">';
		exit();
	}
}

//+-----------------------------------------------------------------------------------------+

// start form
echo '<form action="addfw.php" method="POST" target="_self">';

// here is the section of the form for adding/removing a student using batchstudents.inc
if(	isset($_POST['submit']) && (
	strcmp($_POST['submit'], 'Add') == 0 || 
	strcmp($_POST['submit'], 'Remove') == 0 ||
	strcmp($_POST['submit'], 'Search') == 0 ||
	strcmp($_POST['submit'], 'Edit List') == 0)){

	// set hidden variables for form input so that they save during student list editiing
	echo '<input type="hidden" name="fwreason" value="'.$FWReason.'">';

	// display student list editing options
	include('./batchstudents.inc');
}
// here is the default section for displaying the main form
else{
	// title	
	echo '<center><p class="largecolorheading">Place Students on First Watch</p></center>';
	
	// start table
	echo '<table width="80%" >';
	
	// display flag options
	echo '<tr><td width ="20%"align="left"><b>Reason:</b></td><td align="left"><select size="1" name="fwreason">';
	for($i=0; $i<sizeof($FWReasonArr); $i++){
		if($FWReasonArr[$i] != ''){
			if(strcmp($FWReasonArr[$i], $FWReason)==0)	echo '<option value="'.$FWReasonArr[$i].'" SELECTED>'.$FWReasonArr[$i].'</option>';
			else								echo '<option value="'.$FWReasonArr[$i].'">'.$FWReasonArr[$i].'</option>';
		}
	}
	echo '</td></tr><tr><td height="10"></td></tr>';
	
	// prepare and list students
	echo '<tr cellpadding="0" cellspacing="0"><td align="left"><b>Students to add:</b></td>';
	if(!empty($students)){
		$studentArr = explode(',', $students);
		for($i=0; $i<count($studentArr); $i++){
			$student=$dam->ViewStudent('',$studentArr[$i]);
			echo '<td>'.$student['FIRST_NAME'].' '.$student['LAST_NAME'].'</td></tr><tr><td></td>';
		}
	}
	echo '<td><input type="submit" name="submit" value="Edit List"></td></tr>';	
	echo '</td></tr><tr><td height="10"></td></tr>';			
		
	// end table
	echo '</table>';	
		
	// submit/cancel buttons
	echo '<input type="submit" name="submit" value="Cancel"> <input type="submit" name="submit" value="Submit">';
}

// set hidden variables passed via post
echo '<input type="hidden" name="students" value="'.$students.'">';

// end form
echo '</form>';

// end html started in header.inc
echo '</body></html>';


?>
