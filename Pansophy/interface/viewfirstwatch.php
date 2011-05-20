<?php
/**
 * Prints out the names and IDs of all students currently on First Watch.
 */

//+-----------------------------------------------------------------------------------------+

include('../DataAccessManager.inc.php');
include('../include/header.inc');
include('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

if(isset($_POST['submit']) && strcmp($_POST['submit'], 'Remove Checked Students') == 0) {
	for($r = 0; $r < $_POST['length']; $r++)
	{
		if(isset($_POST['all'.$r.'']))
		{
			$studentID = $_POST['all'.$r.''];
			$dam->clearStudentAllFW('', $studentID );
		}
		else {
			if(isset($_POST['names'.$r.'']))
			{
				$studentID = $_POST['names'.$r.''];
				$Reason = $_POST['reasons'.$r.''];
				$dam->clearStudentFW('', $studentID, $Reason );
			}
		}
	}
	echo '<meta http-equiv="Refresh" content="0; URL=./viewfirstwatch.php">';
}
else {
	echo '<h1>Students Currently on First Watch</h1>';
	
	if($dam->userCanViewFW('')){
	
		echo '<br /><div align="center"><a href="./viewfirstwatchpf.php" target="_blank">[Click here for printer-friendly version]</a></div><br />';
		
		echo '<p>This is a list of all students currently on First Watch.<br /><br />
		For people administrating this list:
		<ul>
		<li>Clicking the checkboxes to the left of the reasons and selecting the "Remove Checked Students" button will remove those reasons from the student.
		<li>Clicking the checkbox to the left of the student\'s name will remove all reasons from the student.
		<li>Students with no further reasons listed are completely removed from First Watch.</ul></p>';
		
		$value = $dam->viewFW('');
	
		echo '<p><table width="80%" ><tr><td align="left">
			<form action="./viewfirstwatch.php" method="POST"><ul class="nobullet">';
	
	
		$t = 0;
		while($row = mysql_fetch_assoc($value))
		{
			$IDs[] = $row['ID'];
			$FNames[] = $row['FIRST_NAME'];
			$LNames[] = $row['LAST_NAME'];
			$Reasons[] = $row['Reason'];
			$t++;
		}
		
		echo '<table cols="2" width="50%" >';
		
		for($q = 0; $q < $t; $q++)
		{
			echo '<tr>';
			if( $q == 0 || ( $IDs[$q] != $IDs[$q-1] && $q != 0 ) ) {
				if ($q != 0) {
					echo '<td align="left" colspan="2"><hr class="short" /></td></tr><tr>';
				}
				echo '<td align="left" width="25%"><input type="checkbox" name="all'.$q.'" value="'.$IDs[$q].'"><a href="./viewstudent.php?id='.$IDs[$q].'">'.$FNames[$q].' '.$LNames[$q].' (ID# '.$IDs[$q].')</a></td>';
			}
			else {
				echo '<td align="left" width="25%">&nbsp;</td>';
			}
			
			echo '<td align="left" width="10%"><li><input type="checkbox" name="names'.$q.'" value="'.$IDs[$q].'">'.$Reasons[$q];
			echo '<input type="hidden" name="reasons'.$q.'" value="'.$Reasons[$q].'"></td></tr>';
		}
		echo '</table>';
	}
	
	echo '<input type="hidden" name="length" value="'.$t.'">';
	if($t > 0) {
		echo '<br /><input type="submit" name="submit" value="Remove Checked Students"></ul>';
	}
	if($dam->userCanCreateInterim(''))
		echo '</form></td></tr></table><br /><a href="./tasks.php">[Return to User Tasks]</a></body></html>';
}
?>
