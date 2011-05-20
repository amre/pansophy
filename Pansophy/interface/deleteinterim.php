<?php
/**
 * This script deletes an interim.
 */

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');

$dam = new DataAccessManager();
if(empty($_GET['interimid'])) $interimId = $_POST['interimid'];
else $interimId = $_GET['interimid'];

if(isset($_POST['submit']) && strcmp($_POST['submit'], 'Yes')  == 0){
	$dam->deleteInterim('', $interimId);
   echo '<meta http-equiv="Refresh" content="0; URL=../main.php?">';
}

else if(isset($_POST['submit']) && strcmp($_POST['submit'], 'No') == 0){
	echo '<meta http-equiv="Refresh" content="0; URL=./viewinterim.php?id='.$interimId.'">';
}

else{

   // see if student associated with the interim is on first watch for interim reports
   $flag = false;
   $interim = $dam->powerSearch('interims', "WHERE `ID` = '$interimId'");
   if(!empty($interim[0]['StudentID'])){   
      $studentId = $interim[0]['StudentID'];
      if($dam->powerSearch('students-FW', "WHERE `StudentID` = '$studentId' AND `Reason`='Interim Reports'")){
         $flag = true;
      }
   }

	echo 'Do you really want to delete interim '.$interimId.' from the system?<br>';

   if($flag) echo 'WARNING: This student is currently on First Watch because of interims. You will have to manually remove him/her if this was an accident!<br>';

	echo '<form action="./deleteinterim.php" method="POST" target="_self">
                <input type="hidden" name="interimid" value="'.$interimId.'">
		<input type="submit" name="submit" value="Yes"> <input type="submit" name="submit" value="No">
	</form>';
}
?>
