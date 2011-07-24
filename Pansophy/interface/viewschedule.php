<?php

/**
 * This page displays a student's schedule - important class information
 * for the current semester.
 */

//+-----------------------------------------------------------------------------------------+

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

// get student id from calling page
$studentID = $_GET['studentid'];

// get student info array
$studentID = $dam->viewStudent('', $studentID);
$keys = array_keys($studentID);
// using the previous pansophy group's lovely variable variables...
for($i=0; $i<count($keys); $i++){
	$$keys[$i]=$studentID[$keys[$i]];
}

// display student name at top of page
echo '<h1>'.$FIRST_NAME.' '.$MIDDLE_NAME.' '.$LAST_NAME.' - '.$ID.'</h1>';
echo '<p class="largeheading">Student Schedule</p><br />';

// retrieve schedule from DAM
$Schedule = $dam->viewStudentSchedule($studentID['ID']);

if(empty($Schedule)){
	echo 'Sorry, there is no schedule available for this student at this time.';
}
else{
	// display table for schedule info
	echo '<table border="1">';
	echo '<tr valign="top"><td><b>Course</b></td><td><b>Title</b></td><td><b>Meeting Information</b></td><td><b>Credits</b></td><td><b>Professor</b></td><td><b>Email</b></td><td><b>Phone</b></td></tr>';
	for($i=0; $i<count($Schedule); $i++){
		echo '<tr valign="top">
		<td>'.$Schedule[$i]['id'].'</td>
		<td>'.$Schedule[$i]['title'].'</td>
		<td>'.$Schedule[$i]['info'].'</td>
		<td>'.$Schedule[$i]['credits'].'</td>
		<td>'.$Schedule[$i]['faculty'].'</td>
		<td>'.$Schedule[$i]['email'].'</td>
      <td>'.$Schedule[$i]['phone'].'</td>
		</tr>';
	}
	echo '</table>';
}

echo'</body></html>';

?>
