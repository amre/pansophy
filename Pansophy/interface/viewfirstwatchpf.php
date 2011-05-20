<?php
/** 
 * Printer-friendly version of viewfirstwatch without the links or the other frames.
 */

//+-----------------------------------------------------------------------------------------+

include('../include/header.inc');
include('../DataAccessManager.inc.php');
include( '../include/miscfunctions.inc.php' );
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

echo '<h1>Students Currently on First Watch</h1>';
	
	if($dam->userCanViewFW('')){
		
		echo '<p>This is a list of all students currently on First Watch.</p>';
		
		$value = $dam->viewFW('');
	
		echo '<p><table width="80%" ><tr><td align="left"><ul class="nobullet">';
	
		$t = 0;
		while($row = mysql_fetch_assoc($value))
		{
			$IDs[] = $row["ID"];
			$FNames[] = $row["FIRST_NAME"];
			$LNames[] = $row["LAST_NAME"];
			$Reasons[] = $row["Reason"];
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
				echo '<td align="left" width="25%">'.$FNames[$q].' '.$LNames[$q].' (ID# '.$IDs[$q].')</td>';
			}
			else {
				echo '<td align="left" width="25%">&nbsp;</td>';
			}
			
			echo '<td align="left" width="10%">'.$Reasons[$q].'</td></tr>';
		}
		echo '</table>';
	}
	
	echo '</body></html>';

?>
