<?php
/**
 * This script is for use in the inline files frame on view student
 */

//+-----------------------------------------------------------------------------------------+

include_once('../include/header.inc'); 
include_once('../DataAccessManager.inc.php');
include_once('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

include('../include/filescript.inc');

$studentId = $_GET['id'];
$normalfiles=$dam->viewAllNormalAttachedFiles('',$studentId);
$admissionsfiles=$dam->viewAllAdmissionsAttachedFiles('',$studentId);
//print_r($files);


//Normal Files Section
echo '<p><p><table  cellspacing="3"><tr><td nowrap><p class="largeheading">Normal Attached Files</p></td></tr></table></p></p>';

echo '<table><tr><td>';

if(!empty($normalfiles)){
	for($i = 0; $i < sizeof($normalfiles); $i++){
      		$file = $normalfiles[$i];
      
      		echo readableDate($file['date']);

      		echo '</td><td>';
      		echo '</td><td>';
      		echo '</td><td>';
      		echo '</td><td>';

      		echo $file['name'];

      		echo '</td><td>';
      		echo '</td><td>';
      		echo '</td><td>';
      		echo '</td><td>';   
      
		if($dam->userCanDownloadFile('',$file['fileid'])){
			echo '<a href="./viewstudent.php?id='.$studentId.'&fileop=download&fileid='.$file['fileid'].'" target="_blank">[Open]</a>';
		}
		if($dam->userCanDeleteFile('',$file['fileid'])){
			echo '<a href="./viewstudent.php?id='.$studentId.'&fileop=delete&fileid='.$file['fileid'].'">[Delete]</a>';			
		}

      	echo '</td></tr><tr><td>';
	}
}

else {
	echo 'There are no attached non-admissions files for this student.';
}
echo '</td></tr></table>';

//Admissions files section
echo '<p><p><table  cellspacing="3"><tr><td nowrap><p class="largeheading">Admissions Files</p></td></tr></table></p></p>';

echo '<table><tr><td>';

if(!empty($admissionsfiles)){
	for($i = 0; $i < sizeof($admissionsfiles); $i++){
      		$file = $admissionsfiles[$i];
      
      		echo readableDate($file['date']);

      		echo '</td><td>';
      		echo '</td><td>';
      		echo '</td><td>';
      		echo '</td><td>';

      		echo $file['name'];

      		echo '</td><td>';
      		echo '</td><td>';
      		echo '</td><td>';
      		echo '</td><td>';   
      
		if($dam->userCanDownloadFile('',$file['fileid'])){
			echo '<a href="./viewstudent.php?id='.$studentId.'&fileop=download&fileid='.$file['fileid'].'" target="_blank">[Open]</a>';
		}
		if($dam->userCanDeleteFile('',$file['fileid'])){
			echo '<a href="./viewstudent.php?id='.$studentId.'&fileop=delete&fileid='.$file['fileid'].'">[Delete]</a>';			
		}

      		echo '</td></tr><tr><td>';
	}
}

else {
	echo 'There are no attached admissions files for this student.';
}
echo '</td></tr></table>';
?>
