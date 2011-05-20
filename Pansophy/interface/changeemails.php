<?php

/* Administrator-only page that changes the default e-mails of the Writing Center, Learning Center
 * and Math Center.
 */

include('../DataAccessManager.inc.php');
include('../include/header.inc');
$dam = new DataAccessManager();

if($dam->userCanCreateUser('')){
	if(strcmp($_POST['submit'], 'Submit Changes') == 0){
		$Email1 = $_POST['WCenter'];
		$Email2 = $_POST['LCenter'];
		$Email3 = $_POST['MCenter'];
		
		$dam->changeEmails('', $Email1, $Email2, $Email3);
		
		echo '<meta http-equiv="Refresh" content="0; URL=./changeemails.php">';
	}
	
	else{
		$value = $dam->selectEmails();
		extract($value);
		
		if($WritingCenter == ''){
			$WritingCenterA = "No E-mail Address Set";
		}
		else {
			$WritingCenterA = $WritingCenter;
		}
		if($LearningCenter == ''){
			$LearningCenterA = "No E-mail Address Set";
		}
		else {
			$LearningCenterA = $LearningCenter;
		}
		if($MathCenter == ''){
			$MathCenterA = "No E-mail Address Set";
		}
		else{
			$MathCenterA = $MathCenter;
		}
		
		echo '<h1>Change Writing/Learning/Math Center E-mail Addresses</h1>
		<p class="mediumhead">Type the full email addresses in the fields which you wish to change.<br />
      Leave blank all fields that you do not wish to change.<br />
      To erase a current email address, type \'CLEAR\'.</p>
		<form action="./changeemails.php" method="POST">
		<br /><br /><table width="80%" >
		<tr><td>&nbsp;</td><td align="left" ><b>Current E-mail Addresses</b></td><td>&nbsp;</td><td align="left"><b>New E-mail Addresses</b></td></tr>
		<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
		<tr><td align="left"><b>Writing Center:</b></td><td align="left">'.$WritingCenterA.'</td><td>&nbsp;</td><td align="left"><input type="text" name="WCenter" maxlength="40" size=40"></td></tr>
		<tr><td align="left"><b>Learning Center:</b></td><td align="left">'.$LearningCenterA.'</td><td>&nbsp;</td><td align="left"><input type="text" name="LCenter" maxlength="40" size=40"></td></tr>
		<tr><td align="left"><b>Math Center:</b></td><td align="left">'.$MathCenterA.'</td><td>&nbsp;</td><td align="left"><input type="text" name="MCenter" maxlength="40" size=40"></td></tr>
		</table><br /><input type="submit" name="submit" value="Submit Changes"></form>';
		
		echo '<p><br><br><a href="./tasks.php">[Return to User Tasks]</a></p></body></html>';
	}
}
else{
	echo '<script language=javascript>alert("You don\'t have access to view this page. Redirecting to the main page.");</script>
	<meta http-equiv="Refresh" content="0; URL=../index.php">';
}

?>
