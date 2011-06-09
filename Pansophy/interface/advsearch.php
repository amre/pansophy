<?php 
// This script is apparently no longer used as of Phronesis v2.0.

/*This script handles the printing out of forms for inputting advanced search data and the 
 *processing of that data. It uses the power serach function from the data access manager
 *and constructs the query to pass to it.
 */
include('../include/header.inc'); include('../DataAccessManager.inc.php');
$dam=new DataAccessManager();
/*This function exists in versions of php later than the one I'm using.  I need a function that
does this, so I just made a new one. Feel free to comment it out if your PHP has this function.*/
function array_combine($keys, $values){
	$result = array();
	for($i=0; $i<sizeof($keys); $i++){
		$key=$keys[$i];
		$value=$values[$i];
		$temp = array($key => $value);
		$result=array_merge($result, $temp);
	}
	return $result;
}
if(strcmp($_POST['submit'], 'Search') == 0){
	$keys=array_keys($_POST);
	for($i = 0; $i < sizeof($keys); $i++){
		$$keys[$i] = $_POST[$keys[$i]];
	}
	$query = "WHERE (";
	if (($table == 'issues' || $table == 'contacts') && ($field[0] == 'Students' || $field[0] == 'Staff')){
		if($field[0] == 'Students'){
			$students=$dam->studentNameSearch('', $term[0]);
			$keys = array_keys($students);
			for($i = 0; $i<sizeof($keys); $i++){
				if($i == 0){
					$query .= "Students LIKE '%".$keys[$i]."%'";
				}
				else{
					$query .= " OR Students LIKE '%".$keys[$i]."%'";
				}
			}
		}
		if($field[0] == 'Staff'){
			//echo 'Staff search';
			$staff=$dam->staffNameSearch('', $term[0]);
			$keys = array_keys($staff);
			for($i = 0; $i<sizeof($keys); $i++){
				if($i == 0){
					$query .= "Staff LIKE '%".$keys[$i]."%'";
				}
				else{
					$query .= " OR Staff LIKE '%".$keys[$i]."%'";
				}
			}
		}
	}
	else{
		$query .= $field[0]." LIKE '%".$term[0]."%'";
	}
	$query .= ')';
	for($i = 1; $i < 4; $i++){
		if(!empty($term[$i])){
			//Searching contacts or issues for students assosciated with them is a special case.  Thus, it needs a special search thingy.
			if (($table == 'issues' || $table == 'contacts') && ($field[$i] == 'Students' || $field[$i] == 'Staff')){
				if($field[$i] == 'Students'){
					$students=$dam->studentNameSearch('', $term[$i]);
					$keys = array_keys($students);
					for($j = 0; $j<sizeof($keys); $j++){
						if($j == 0){
							$query .= ' AND (';
							if($bool[$i] == 'AND'){
								$query .= "Students LIKE '%".$keys[$j]."%'";
							}
							if($bool[$i] == 'NOT'){
								$query .= "Students NOT LIKE '%".$keys[$j]."%'";
							}
						}
						else{
							if($bool[$i] == 'AND'){
								$query .= " OR Students LIKE '%".$keys[$j]."%'";
							}
							if($bool[$i] == 'NOT'){
								$query .= " AND Students NOT LIKE '%".$keys[$j]."%'";
							}
						}
					}
					$query .= ')';
				}
				if($field[$i] == 'Staff'){
					$staff=$dam->staffNameSearch('', $term[$i]);
					$keys = array_keys($staff);
					for($j = 0; $j<sizeof($keys); $j++){
						if($j == 0){
							$query .= ' AND (';
							if($bool[$i] == 'AND'){
								$query .= "Staff LIKE '%".$keys[$j]."%'";
							}
							if($bool[$i] == 'NOT'){
								$query .= "Staff NOT LIKE '%".$keys[$j]."%'";
							}
						}
						else{
							if($bool[$i] == 'AND'){
								$query .= " OR Staff LIKE '%".$keys[$j]."%'";
							}
							if($bool[$i] == 'NOT'){
								$query .= " AND Staff NOT LIKE '%".$keys[$j]."%'";
							}
						}
					}
					$query .= ')';
				}
			}
			else {
				if($bool[$i] == 'AND'){
					$query .= " AND (".$field[$i]." LIKE '%".$term[$i]."%')";
				}
				if($bool[$i] == 'NOT'){
					$query .= " AND (".$field[$i]." NOT LIKE '%".$term[$i]."%')";
				}
			}
		}
	}
	$results=$dam->powerSearch($table, $query);
	if($results){
		//Set the special case fields using the special case arrays
		if(strcmp($table, 'students') == 0){
			$links = array('LastName', 'Email');
			$doNotDisplay = array('LastModified', 'DateCreated', 'RedFlag', 'Cellular', 'UsersWatching', 'Address1', 'Address2', 
					'Modifier', 'Status', 'Ethnic', 'Advisor', 'HomePhone');
		}
		if(strcmp($table, 'issues') == 0){
			$links = array('Creator', 'Modifier', 'ID');
			$doNotDisplay = array ('UsersWatching', 'Staff'); 
		}
		if(strcmp($table, 'contacts') == 0){
			$links = array('Creator', 'Issue');
			$doNotDisplay = array ('Description', 'Modifier', 'LastModified');
		}
		if(strcmp($table, 'users') == 0){
			$links = array('ID','Email');
			$doNotDisplay = array('Context1','Context2','Alerts','AccessLevel');
		}
		
		$columns = array_keys($results[0]);
		//Output a table with the column names as headers
		echo '<table class="greywithborder" cellpadding="2" cellspacing="1" border="1" width="100%"><tr class="colorbg">';
		for ($i = 0; $i <sizeof($columns); $i++) {
			if(!in_array($columns[$i], $doNotDisplay))
			echo "<th>".$columns[$i]."</th>";
		}
		echo '</tr><tr>';
		//Cycles through the array of results and prints each one out as its own table row.
		for($h=0; $h<sizeof($results); $h++){
			for($i=0; $i < sizeof($columns); $i++){
				$$columns[$i]=$results[$h][$columns[$i]];
			}
			if(strcmp($table, 'students')==0){
				$urls= array('./viewstudent.php?id='.$ID, 'mailto:'.$Email);
			}
			if(strcmp($table, 'issues')==0){
				$urls= array('mailto:'.$dam->getUserEmail($Creator), 'mailto:'.$dam->getUserEmail($Modifier), 
				'./viewissue.php?id='.$ID);
			}
			if(strcmp($table, 'contacts')==0){
				$urls= array('mailto:'.$dam->getUserEmail($Creator), './viewissue.php?id='.$Issue);
			}
			if(strcmp($table, 'users')==0){
				$urls= array('./viewuser.php?id='.$ID, 'mailto:'.$Email);
			}
			$urls=array_combine($links, $urls);
			for($i=0; $i < sizeof($columns); $i++){
				$field=$columns[$i];
				if(!in_array($field, $doNotDisplay)){
					echo '<td>';
					if(strcmp($field, 'Students')==0){
						//echo $Students;
						$Students=explode(',', $Students);
						for($j=0; $j<count($Students); $j++){
							$Student=$dam->ViewStudent('',$Students[$j]);
							$FirstName=$Student['FirstName'];
							$LastName=$Student['LastName'];
							echo '<a href="./viewstudent.php?id='.$Students[$j].'">'.$FirstName.' '.$LastName.'</a>';
							if(!($j == count($Students)-1)){
								echo ', ';
							}
						}
						echo '</td>';
					}
					
					else if (strcmp($field, 'LastModified')==0){
						$DateModified = substr($LastModified, 0, 8);
						$DateModified = sprintf(substr($DateModified, 4, 2).'/'.substr($DateModified, 6, 2).'/'.substr($DateModified, 0, 4));
						$TimeModified = substr($LastModified, -6);
						$temp = substr($TimeModified, 0, 2);
						if( $temp > 12 ) {
							$temp -= 12;
							if( $temp < 10 ) {
								$temp = '0'.$temp;
							}
							$ampm = 'PM';
						}
						else {
							$ampm = 'AM';
						}
						$TimeModified = sprintf($temp.':'.substr($TimeModified, 2, 2).':'.substr($TimeModified, 4, 2).' '.$ampm);
						echo $DateModified.' at '.$TimeModified.'</td>';
					}
					
					else if (strcmp($field, 'DateCreated')==0){
						$DateCreated = explode(" ", $DateCreated);
						$TimeCreated = $DateCreated[1];
						$temp = substr($TimeCreated, 0, 2);
						if( $temp > 12 ) {
							$temp -= 12;
							if( $temp < 10 ) {
								$temp = '0'.$temp;
							}
							$ampm = 'PM';
						}
						else {
							$ampm = 'AM';
						}
						$TimeCreated = $temp.substr($TimeCreated, 2).' '.$ampm;
						
						$DateCreated = explode("-", $DateCreated[0]);
						$DateCreated = $DateCreated[1].'/'.$DateCreated[2].'/'.$DateCreated[0];
						echo $DateCreated.' at '.$TimeCreated.'</td>';
					}
					else{
						if(in_array($field, $links)){
							echo '<a href="'.$urls[$field].'">'.$$field.'</a></td>';
						}
						else{
							echo $$field."</td>";
						}
					}
				}
			}
			echo "</tr><tr>";
		}
	}
	else{
		echo 'There were no results for your search.';
	}
}	
else{
	echo '	<p class="largegoldhead">Advanced Search</p>
		<form>
		Please select the table you\'d like to search.<br>
		<select size="1" name="table"  onChange = "location = this.options[this.selectedIndex].value";>';
	$table = $_GET['table'];
	if(strcmp($table, 'students')==0)
		echo '<option value="./advsearch.php?table=students" SELECTED>Students</option>';
	else
		echo '<option value="./advsearch.php?table=students">Students</option>';
	if(strcmp($table, 'issues')==0)
		echo '<option value="./advsearch.php?table=issues" SELECTED>Issues</option>';
	else
		echo '<option value="./advsearch.php?table=issues">Issues</option>';
	if(strcmp($table, 'contacts')==0)
		echo '<option value="./advsearch.php?table=contacts" SELECTED>Contacts</option>';
	else
		echo '<option value="./advsearch.php?table=contacts">Contacts</option>';
	if(strcmp($table, 'users')==0)
		echo '<option value="./advsearch.php?table=users" SELECTED>Users</option>';
	else
		echo '<option value="./advsearch.php?table=users">Users</option>';
		
	echo '</select></form><p>';
	if(!empty($table)){
		echo '<form action = "./advsearch.php" method="POST">
		First search<br>';
		$fields = $dam->getTableFields('', $table);
		$columns = mysql_num_fields($fields);
		echo '<select size="1" name="field[0]">';
		for($i = 0; $i < $columns; $i++){
			$name = mysql_field_name($fields, $i);
			if(strcmp($name, 'LastName')==0 ||strcmp($name, 'Students')==0 )
				echo '<option value="'.$name.'" SELECTED>'.$name.'</option> \n';
			else
				echo '<option value="'.$name.'">'.$name.'</option> \n';
		}
		echo '</select>
		<input type="text" name="term[0]"><p>
		Additional queries<br>
		<select size="1" name="bool[1]">
		<option value=""></option>
		<option value="AND">AND</option>
		<option value="NOT">NOT</option>
		</select> ';
		echo '<select size="1" name="field[1]">';
		for($i = 0; $i < $columns; $i++){
			$name = mysql_field_name($fields, $i);
			if(strcmp($name, 'LastName')==0 ||strcmp($name, 'Students')==0 )
				echo '<option value="'.$name.'" SELECTED>'.$name.'</option> \n';
			else
				echo '<option value="'.$name.'">'.$name.'</option> \n';
		}
		echo '</select> <input type="text" name="term[1]"><p>
		<select size="1" name="bool[2]">
		<option value=""></option>
		<option value="AND">AND</option>
		<option value="NOT">NOT</option>
		</select> ';
		echo '<select size="1" name="field[2]">';
		for($i = 0; $i < $columns; $i++){
			$name = mysql_field_name($fields, $i);
			if(strcmp($name, 'LastName')==0 ||strcmp($name, 'Students')==0 )
				echo '<option value="'.$name.'" SELECTED>'.$name.'</option> \n';
			else
				echo '<option value="'.$name.'">'.$name.'</option> \n';
		}
		echo '</select> <input type="text" name="term[2]"><p>
		<select size="1" name="bool[3]">
		<option value=""></option>
		<option value="AND">AND</option>
		<option value="NOT">NOT</option>
		</select> ';
		echo '<select size="1" name="field[3]">';
		for($i = 0; $i < $columns; $i++){
			$name = mysql_field_name($fields, $i);
			if(strcmp($name, 'LastName')==0 ||strcmp($name, 'Students')==0 )
				echo '<option value="'.$name.'" SELECTED>'.$name.'</option> \n';
			else
				echo '<option value="'.$name.'">'.$name.'</option> \n';
		}
		echo '</select> <input type="text" name="term[3]"><p>
		<input type="hidden" name="table" value="'.$table.'"><input type="submit" name="submit" value="Search">';
	}
}
