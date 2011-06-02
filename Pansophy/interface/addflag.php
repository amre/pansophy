<?php 

/**
 * Page that handles adding student flags. Uses batchstudents.inc to 
 * determine a list of students to flag.
 */

//+-----------------------------------------------------------------------------------------+

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+


// obtain current flags from sql tables 
$value = $dam->extractFlags();
extract($value);
$flagArr = array( 'HousingWaitList', 'AcProbation', $Option1, $Option2, $Option3 );

// retrieve variables
$flag = 		$_POST['flag'];			// flag to be added to students
$students = 	$_POST['students'];		// list of students to be flagged

//+-----------------------------------------------------------------------------------------+

// if this is the first time calling the page
if(!isset($_POST['submit'])){
}
// if the user has cancelled, return to  main menu
else if(strcmp($_POST['submit'], 'Cancel') == 0){
	echo '<meta http-equiv="Refresh" content="0; URL=../main.php?">';
	exit('Loading...');
}
// when the user finally submits the form, this code processes the user input and creates the new flags
else if(strcmp($_POST['submit'], 'Submit') == 0){	
	
	// check to make sure there are students selected
	if(empty($students)){
		echo '<p class="errortext">Error: You must select at least one student.</p>';
		$_POST['submit'] = 0;
	}
	
	// update student table in db
	if(strcmp($_POST['submit'],'Submit') == 0){
		$value = $dam->extractFlags();
		extract($value);
		if($flag == $Option1){
			$flag = Field1;
		}
		elseif($flag == $Option2){
			$flag = Field2;
		}
		elseif($flag == $Option3){
			$flag = Field3;
		}
		$newflags[$flag] = 1;

		$studentArr = explode(',',$students);
		for($i=0; $i<count($studentArr); $i++){
			$student = $dam->modifyStudent('',$studentArr[$i], $newflags);
		}
	
		echo '<meta http-equiv="Refresh" content="0; URL=../main.php">';
		exit('Loading...');
	}
}

//+-----------------------------------------------------------------------------------------+

// start form
echo '<form action="addflag.php" method="POST" target="_self">';

// here is the default section for displaying the main form
	// title	
	echo '<center><p class="largecolorheading">Add Flag to Students</p></center>';
	
	// start table
	echo '<table width="80%" >';
	
	// display flag options
	echo '<tr><td width ="20%"align="left"><b>Flag:</b></td><td align="left"><select size="1" name="flag">';
	for($i=0; $i<sizeof($flagArr); $i++){
		if($flagArr[$i] != ''){
			if(strcmp($flagArr[$i], $flag)==0)	echo '<option value="'.$flagArr[$i].'" SELECTED>'.$flagArr[$i].'</option>';
			else								echo '<option value="'.$flagArr[$i].'">'.$flagArr[$i].'</option>';
		}
	}
	echo '</td></tr><tr><td height="10"></td></tr>';
	
	// prepare and list students
	echo '<tr cellpadding="0" cellspacing="0"><td align="left"><b>Students to flag:</b></td>';
        // adding/removing a student using batchstudents.inc
        include('./batchstudents.inc');
	if(!empty($students)){
		$studentArr = explode(',', $students);
		for($i=0; $i<count($studentArr); $i++){
			$student=$dam->ViewStudent('',$studentArr[$i]);
			echo '<td>'.$student['FIRST_NAME'].' '.$student['LAST_NAME'].'</td></tr><tr><td></td>';
		}
	}
	//echo '<td><input type="submit" name="submit" value="Edit List"></td></tr>';	
	echo '</td></tr><tr><td height="10"></td></tr>';			
		
	// end table
	echo '</table>';	
		
	// submit/cancel buttons
	echo '<input type="submit" name="submit" value="Cancel"> <input type="submit" name="submit" value="Submit">';


// set hidden variables passed via post
echo '<input type="hidden" name="students" value="'.$students.'">';

// end form
echo '</form>';

// end html started in header.inc
echo '</body></html>';


?>
