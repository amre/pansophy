<?php 

/**
 * This is the menu. It goes in the top frame of the page and is tailored to the logged-in user.
 * It also contains the search bar.
 */
 
session_start();
include('./include/mainheader.inc'); include('./DataAccessManager.inc.php');
include('./include/miscfunctions.inc.php');
$dam = new DataAccessManager();
	
//start content table
echo '<table width="100%" cellpadding="2" cellspacing="0" vspace="0" hspace="0">';
echo '<tr>';

//print logo
echo '<td align="center" rowspan="2" width="50%" class="darkbg">
		<a href="http://www.wooster.edu" target="_blank">
		<img src="./img/woosterlogo.gif" border="0"></a></td>';

//start print top row
//print tasks
echo '<td align="center" nowrap colspan="2" width="50%" class="colorbg">
	<table  cellspacing="8">
		<td width="1%" class="colorbg"><a href="./main.php" target="Main" class="darkcolor">[Main]</a></td>
		<td align="left" nowrap width="1%" class="colorbg">
		<a href="./interface/issuereports.php?datetype=0&start=&end=&status=Open&user=&submit=Generate+Report" target="Main" class="darkcolor">
		[Open Issues]</a></td>';
if( $dam->userCanCreateIssue('') ) echo '
		<td align="left" nowrap width="1%" class="colorbg">
		<a href="./interface/addcontact.php?isnewissue=1" target="Main" class="darkcolor">[Add Issue]</a></td>';
if( $dam->userCanModifyStudent('') ) echo '
		<td align="left" nowrap width="1%" class="colorbg">
		<a href="./interface/addflag.php" target="Main" class="darkcolor">[Add Flag]</a></td>';
echo '	<td align="left" nowrap width="1%" class="colorbg">
		<a href="./help/index.html" target="_blank" class="darkcolor">[Help]</td></td><td width="40%"></td>
	</table>';
'</td>';

//print logout
echo '<td align="right" width="30%" nowrap colspan="2" class="colorbg">Logged in as '.$_SESSION['userid'].'
		<a href="logout.php" target="_top" class="darkcolor">[Logout]</a></p></td>';
echo '</tr>';

//start print bottom row
echo '<tr>';

//print table select form
echo '<form>
	<td height="50" valign="top" width="1%" align="left" class="darkbg"><p class="plaincolortext">Search Type<br>
		<select name="type" onChange = "location = this.options[this.selectedIndex].value";>  <!-- Is this JavaScript? I do not know! -->';
		$table = $_GET['table'];
		if(strcmp($table, 'students')==0 || strcmp($table, 'students')==0){
			echo '<option value="./menu.php?table=students" SELECTED>Students</option>';
			//$doNotDisplay = array('DateCreated', 'UsersWatching', 'LastModified', 'Modifier', 'Cellular', 'InterimCounter', 'FirstWatch', 'FWReason');
			$doNotDisplay = array('DateCreated', 'UsersWatching', 'LastModified', 'Modifier','InterimCounter', 'FirstWatch', 'FWReason',
									'ID', 'PRIVACY_FLAG', 'STREET_2', 'STREET_3', 'STREET_4', 'STREET_5');
		}
		else
			echo '<option value="./menu.php?table=students">Students</option>';
		if(strcmp($table, 'issues')==0){
			echo '<option value="./menu.php?table=issues" SELECTED>Issues</option>';
			$doNotDisplay = array('UsersWatching', 'LastModified', 'DateCreated');
		}
		else
			echo '<option value="./menu.php?table=issues">Issues</option>';
		if(strcmp($table, 'contacts')==0){
			echo '<option value="./menu.php?table=contacts" SELECTED>Contacts</option>';
			$doNotDisplay = array('DateCreated', 'LastModified');	
		}
		else
			echo '<option value="./menu.php?table=contacts">Contacts</option>';
		if(strcmp($table, 'users')==0 && $dam->canViewUsers('')){
			echo '<option value="./menu.php?table=users" SELECTED>Users</option>';
			$doNotDisplay = array('Context1', 'Context2', 'Alerts', 'AccessLevel');
		}
		else
			if($dam->canViewUsers(''))
				echo '<option value="./menu.php?table=users">Users</option>';
echo '		</select>
	</td>
</form>';

//print search form
// Query to select names of flags from the table.
$value = $dam->extractFlags();
extract($value);

if(isset($table)){
	$fields = $dam->getTableFields('', $table);
	$Xcolumns = 0;
	if(strcmp($table, 'students')==0){
		$Pfields = $fields;
		$Xfields = $dam->getTableFields('', 'X_PNSY_STUDENT');
		$Afields = $dam->getTableFields('', 'X_PNSY_ADDRESS');
		//$fields = array_merge($Pfields, $Xfields);
		$columns = mysql_num_fields($Pfields);
		$Xcolumns = mysql_num_fields($Xfields);
		$Acolumns = mysql_num_fields($Afields);
	}
	else{
		$fields = $dam->getTableFields('', $table);
		$columns = mysql_num_fields($fields);
	}
	echo '<form action="results.php" method="post" target="Main">';
	//echo $table;
	echo '<td height="15" valign="top" width="1%" align="left"  class="darkbg">
			<p class="plaincolortext">Search Field<br><select size="1" name="SearchField">';
	for($i = 0; $i < $columns; $i++){
		if(strcmp($table, 'students')==0)
			$name = mysql_field_name($Pfields, $i);
		else
			$name = mysql_field_name($fields, $i);
		if($dam->getAccessLevel() > 7 || $dam->getAccessLevel() == 5){
			if(!in_array($name, $doNotDisplay)){
				if(strcmp($name, 'Students')==0 )
					echo '<option value="'.$name.'" SELECTED>'.preg_replace('/(\w+)([A-Z])/U', '\\1 \\2', $name).'</option> \n';
				elseif(strcmp($name, 'VIP')==0 || strcmp($name, 'ID')==0 )
					echo '<option value="'.$name.'">'.$name.'</option> \n';
				else{
					/* Sets the Field1, Field2, and Field3 labels to their actual modifiers
					 * that are listed in the `flags` table. - Josh Thomas... You will never be
					 * able to forget his name - at least, if you're playing the game :)
					 */
					if(strcmp($name, 'Field1') == 0)
						$name = $Option1;
					if(strcmp($name, 'Field2') == 0)
						$name = $Option2;
					if(strcmp($name, 'Field3') == 0)
						$name = $Option3;
					if(strcmp($name, '') != 0){
						echo '<option value="'.$name.'">'.preg_replace('/(\w+)([A-Z])/U', '\\1 \\2', $name).'</option> \n';
					}
				}
			}
		}
		else{
			if(!in_array($name, $doNotDisplay)){
				if(strcmp($name, 'Students')==0 )
					echo '<option value="'.$name.'" SELECTED>'.preg_replace('/(\w+)([A-Z])/U', '\\1 \\2', $name).'</option> \n';
				elseif(strcmp($name, 'VIP')==0 || strcmp($name, 'ID')==0 )
					echo '<option value="'.$name.'">'.$name.'</option> \n';
				else{
					/* Sets the Field1, Field2, and Field3 labels to their actual modifiers
					 * that are listed in the `flags` table. - Josh Thomas... 's sister is a spaceship.
					 */
					if(strcmp($name, 'Field1') == 0)
						$name = $Option1;
					if(strcmp($name, 'Field2') == 0)
						$name = $Option2;
					if(strcmp($name, 'Field3') == 0)
						$name = $Option3;
					if(strcmp($name, '') != 0){
						echo '<option value="'.$name.'">'.preg_replace('/(\w+)([A-Z])/U', '\\1 \\2', $name).'</option> \n';
					}
				}
			}
		}
	}
	//loop for X_PNSY_STUDENT fields
	for($i = 0; $i < $Xcolumns; $i++){
		$name = mysql_field_name($Xfields, $i);
		if($dam->getAccessLevel() > 7 || $dam->getAccessLevel() == 5){
			if(!in_array($name, $doNotDisplay)){
				if(strcmp($name, 'LAST_NAME')==0 ||strcmp($name, 'Students')==0 )
					echo '<option value="'.$name.'" SELECTED>'.bonnyFieldName($name).'</option> \n';
				else{
					if(strcmp($name, 'Field1') == 0)
						$name = $Option1;
					if(strcmp($name, 'Field2') == 0)
						$name = $Option2;
					if(strcmp($name, 'Field3') == 0)
						$name = $Option3;
					if(strcmp($name, 'ADDRESS_ID') == 0){
						for($j = 0; $j < $Acolumns; $j++){
							$doNotDisplayAdd = array('ADDRESS_ID', 'STREET_1', 'STREET_2', 'STREET_3', 'STREET_4', 'STREET_5');
							$name = mysql_field_name($Afields, $j);
							if(!in_array($name, $doNotDisplayAdd))
								echo '<option value="'.$name.'">'.bonnyFieldName($name).'</option> \n';
							if($name == 'STREET_1'){
								$name = 'STREET';
								echo '<option value="'.$name.'">'.bonnyFieldName($name).'</option> \n';
							}
						}
					}
					elseif(strcmp($name, '') != 0){
						echo '<option value="'.$name.'">'.bonnyFieldName($name).'</option> \n';
					}
				}
			}
		}
		else{
			if(!in_array($name, $doNotDisplay)){
				if(strcmp($name, 'LAST_NAME')==0 ||strcmp($name, 'Students')==0 )
					echo '<option value="'.$name.'" SELECTED>'.bonnyFieldName($name).'</option> \n';
				else{
					if(strcmp($name, 'Field1') == 0)
						$name = $Option1;
					if(strcmp($name, 'Field2') == 0)
						$name = $Option2;
					if(strcmp($name, 'Field3') == 0)
						$name = $Option3;
					if(strcmp($name, 'ADDRESS_ID') == 0){
						for($j = 0; $j < $Acolumns; $j++){
							$doNotDisplayAdd = array('ADDRESS_ID', 'STREET_1', 'STREET_2', 'STREET_3', 'STREET_4', 'STREET_5');
							$name = mysql_field_name($Afields, $j);
							if(!in_array($name, $doNotDisplayAdd))
								echo '<option value="'.$name.'">'.bonnyFieldName($name).'</option> \n';
							if($name == 'STREET_1'){
								$name = 'STREET';
								echo '<option value="'.$name.'">'.bonnyFieldName($name).'</option> \n';
							}
						}
					}
					elseif(strcmp($name, '') != 0){
						echo '<option value="'.$name.'">'.bonnyFieldName($name).'</option> \n';
					}
				}
			}
		}
	}
	echo '</select></td><td height="15" valign="top" align ="left" width="1%" nowrap class="darkbg"><p class="plaincolortext">Search Term<br><input type="text" name="SearchTerm" size="20">';
	echo ' <input type="submit" value="Search"></td>';
	echo '<input type="hidden" name="table" value="'.$table.'">';
	echo '</form></td>';
}

//end print bottom row
echo '<td width="50%" class="darkbg"></td></tr>';

//end content table
echo '</table>';

?>
