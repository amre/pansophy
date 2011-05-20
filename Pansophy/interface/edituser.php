<?php 

/**
 * Page for an admin to edit user details.
 */

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');

$dam = new DataAccessManager();

if(strcmp($_POST['submit'], 'Submit Changes') == 0 ){
	$dam->modifyUser('', $_GET['id'], $_POST);
	echo '<meta http-equiv="Refresh" content="0; URL=./viewuser.php?id='.$_GET['id'].'">';	
}
if(strcmp($_POST['submit'], 'Cancel') == 0){
		echo ' <meta http-equiv="Refresh" content="0; URL=../main.php">';
}
else {
	$student = $dam->viewUser('', $_GET['id']);
	$keys = array_keys($student);
	//Make variable names the same as fields in database.  I love variable variables.
	for($i=0; $i<count($keys); $i++){
		$$keys[$i]=$student[$keys[$i]];
	}
	
	$DateModified = substr($LastModified, 0, 8);
	$DateModified = sprintf(substr($DateModified, 4, 2).'/'.substr($DateModified, 6, 2).'/'.substr($DateModified, 0, 4));
	
	$TimeModified = substr($LastModified, -6);
	$temp = substr($TimeModified, 0, 2);
	if( $temp > 12 ) {
		$temp -= 12;
		$temp -= 12;
		$ampm = 'PM';
	}
	else {
		$ampm = 'AM';
	}
	$TimeModified = sprintf($temp.':'.substr($TimeModified, 2, 2).':'.substr($TimeModified, 4, 2).' '.$ampm);
	$editableFields = array('Context1','Context2','LastName','FirstName','MiddleIn','Email','Extension');
	$doNotDisplay = array('ID', 'Alerts', 'AccessLevel', 'IsFaculty', 'IsStaff');
	//Display all the data, with nested tables.
	echo '<p class="mediumcolorheading">Edit user '.$ID.'</p>
	<p><table width="100%" >
	<tr><td valign="top" width="45%">
	<table  cellspacing="3"><tr>';
	echo	'</tr></table>
	<form action="./edituser.php?id='.$_GET['id'].'" method="POST">
	<table  cellspacing="5" cellpadding="4">';
	for($i=0; $i<count($keys); $i++){
		if(!in_array($keys[$i], $doNotDisplay)){
			if(in_array($keys[$i], $editableFields)){
				echo '<tr><td align="left" nowrap>'.$keys[$i].': </td><td align="left"><input type="text" name="'.$keys[$i].'" value="'.$$keys[$i].'"></td></tr>';
			}
			else{
				echo '<tr><td align="left" nowrap>'.$keys[$i].': </td><td align="left"><input type="text" name="'.$keys[$i].'" value="'.$$keys[$i].'" DISABLED></td></tr>';
			}
		}
	}
	$accessLevels = array(0 => 'No Access', 4 => 'Read Only User (Normal)', 5 => 'Read Only User (Full)', 7 => 'Normal User', 8=> 'First Watch', 9 => 'Privileged User', 10 => 'Administrative User');
	$keys = array_keys($accessLevels);
	echo '<tr><td align="left" nowrap>Access Level: <td><select size="1" name="AccessLevel">';
	for($i=0; $i<sizeof($keys); $i++){
		if(strcmp($keys[$i], $AccessLevel)==0){
			echo '<option value="'.$keys[$i].'" SELECTED>'.$accessLevels[$keys[$i]].'</option>';
		}
		else{
			echo '<option value="'.$keys[$i].'">'.$accessLevels[$keys[$i]].'</option>';
		}
	}
	echo'</select></td></tr>
	<tr><td><input type="hidden" name="ID" value="'.$ID.'"></td></tr>
	<tr><td><input type="submit" name="submit" value="Cancel"> <input type="submit" name="submit" value="Submit Changes"></td></tr>
	</table></form></td></table></body></html>';
	mysql_close();
}
?>

