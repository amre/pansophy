<?php

/** 
 * This file is used to display the Interim Report headings in viewstudent.php.
 */

//+-----------------------------------------------------------------------------------------+ 
 
include_once('../DataAccessManager.inc.php');
include_once('../include/header.inc');
include_once('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

$studentId = $_GET['id'];
$interims=$dam->getStudentInterims($studentId);

echo '<table><dl>';
if($interims){
	for($i=0; $i<sizeof($interims); $i++){
		if($interims[$i]['ID'] == ''){
		}
		else{
			if($dam->userCanViewInterim('')){
				echo '<tr><td><dt><a href="./viewinterim.php?id='.$interims[$i]['ID'].'" TARGET="Main">
				'.$interims[$i]['ID'].'</a>:';

            echo '</td><td>';
            echo '</td><td>';

            echo stripslashes($interims[$i]['CourseNumberTitle']);
				echo '</td></tr>';
			}
		}
	}
}
else {
	echo '<tr><td>There are no interims for this student.</td></tr>';
}
echo '</dl></table>';
?>
