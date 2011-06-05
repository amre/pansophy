<?php
// This file was replaced in Phronesis v2.0 by the Report Generator.

//This is the pending issues script.  It performs a search for pending issues, and then outputs the results
include('../include/header.inc');
include('../DataAccessManager.inc.php');
include_once('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();
$display = array('Header', 'ID', 'Students', 'DateCreated', 'LastModified');
echo '<h2>Open Issues</h2>';
echo '<table cellpadding="2" cellspacing="1" width="100%" class="greywithborder"><tr class="colorbg">';
for ($i = 0; $i < sizeof($display); $i++) {	
	echo "<th>".$display[$i]."</th>";
}
// Michael Thompson * 12/07/2005 * Added search order field to performSearch call
$issues = $dam->performSearch('', 'issues', 'Status', 'Open', '');
	for($i=0; $i < sizeof($issues); $i++){
		for($j=0; $j<sizeof($display); $j++){
			$$display[$j]=$issues[$i][$display[$j]];
			//echo $display[$j].' '.$$display[$j].'<p>';
		}
		echo '<tr>';
		$Students = explode(",", $Students);
		for($j=0; $j < sizeof($display); $j++){
			echo '<td>';
			if($display[$j] == 'Students'){
				for($k=0; $k<count($Students); $k++){
					$Student=$dam->ViewStudent('',$Students[$k]);
					$FirstName=$Student['FirstName'];
					$LastName=$Student['LastName'];
					echo '<a href="./viewstudent.php?id='.$Students[$k].'">'.$FirstName.' '.$LastName.'</a>';
					if(!($k == count($Students)-1)){
						echo ', ';
					}
				}
				echo '</td>';
			}
			else if (strcmp($display[$j], 'LastModified')==0){
				echo readableDateAndTime( $LastModified );
				echo '</td>';
			}
					
			else if (strcmp($display[$j], 'DateCreated')==0){
				echo readableDateAndTime( $DateCreated );
				echo '</td>';
			}
			else if ($display[$j] == 'Header'){
				echo '<a href="./viewissue.php?id='.$ID.'">'.stripslashes($Header).'</a>';		
			}
			else{
				echo $$display[$j].'</td>';
			}
		}
		echo '</tr>';
	}

echo '</table>';
mysql_close();
?>
