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
$files=$dam->viewAllAttachedFiles('',$studentId);
//print_r($files);

echo '<table><tr><td>';

if(!empty($files)){
	for($i = 0; $i < sizeof($files); $i++){
      $file = $files[$i];
      
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
			echo '<a href="./viewstudent.php?id='.$studentId.'&fileop=download&fileid='.$file['fileid'].'">[Open]</a>';
		}
		if($dam->userCanDeleteFile('',$file['fileid'])){
			echo '<a href="./viewstudent.php?id='.$studentId.'&fileop=delete&fileid='.$file['fileid'].'">[Delete]</a>';
		}

      echo '</td></tr><tr><td>';
	}
}

else {
	echo 'There are no attached files for this student.';
}
echo '</td></tr></table>';
?>
