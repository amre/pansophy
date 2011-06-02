<?php

/**
 * This file is included by many pages to bring up an interface for searching for and selecting
 * students from the database. When a student is found, he/she is added to a list. The list
 * is then used by the calling page to perform batch operations of whatever type. 
 * 
 * IMPORTANT: This file is dependent upon the context in which it is called. It is to be 
 * included as part of a form. Calling page needs to have a comma delimited '$students' string.
 */

//+-----------------------------------------------------------------------------------in_array------+

// add checked students
if(strcmp($_POST['submit'], 'Add') == 0){	
	if(isset($_POST['addarr'])) $addArr = $_POST['addarr'];
	else $addArr = array();
	$studentArr = explode(',',$students);
	for($i=0; $i<count($addArr); $i++){
		// take this moment to keep `students` table up to date
		$dam->verifyStudent($addArr[$i]);
		// add selected students to list
		if(!in_array($addArr[$i], $studentArr)){
			if(empty($students))$students = $addArr[$i];
			else				$students .= ','.$addArr[$i];		
		}	
	}
}
// remove checked students
else if(strcmp($_POST['submit'], 'Remove') == 0){
	if(isset($_POST['removearr'])) $removeArr = $_POST['removearr'];
	else $removeArr = array();
	$studentArr = explode(',',$students);
	if(!empty($removeArr)){
		$studentArr = array_diff($studentArr, $removeArr);
		$studentArr = array_diff($studentArr, array(''));
	}
	$students = implode(',',$studentArr);
}
// do search on first and last name provided by user
else if(strcmp($_POST['submit'], 'Search') == 0){
	$firstName = $_POST['firstname'];
	$lastName = $_POST['lastname'];
	if($lastName != '' || $firstName !=''){
		$searchResults = $dam->powerSearch('X_PNSY_STUDENT', "WHERE `LAST_NAME` LIKE '%$lastName%' AND `FIRST_NAME` LIKE '%$firstName%'");
	}
}
// default
else{
}

//+-----------------------------------------------------------------------------------------+

// hack to make the search button (appears later) the default button

echo '<input type="submit" name="submit" value="Search" style="visibility:hidden">';

// start left hand subtable
echo '<tr><td valign="top"><table>';

// this is the list of currently selected students
//echo '<tr><td><h3>Currently Selected</h3></td></tr>';

// list them out
if(!empty($students)){
		$studentArr = explode(',', $students);
		for($i=0; $i<count($studentArr); $i++){
			$student=$dam->ViewStudent('',$studentArr[$i]);
			echo '<tr><td><input type="checkbox" name="removearr[]" value="'.$student['ID'].'">
		'.$student['FIRST_NAME'].' '.$student['LAST_NAME'].'</td></tr><tr><td></td>';
		}
	}

//echo '<tr><td height="10"></td></tr>';	

// remove and done buttons
echo '<tr><td><input type="submit" name="submit" value="Remove">';
//echo '<center><input type="submit" name="submit" value="Back"></td></tr>';

// end left subtable and start right hand one
echo '</table></td><td valign="top"><table>';

// this is the search list for add new students to the list
//echo '<tr><td><h3>Add Students</h3></td></tr>';

// search text boxes and buttons
echo '<tr><td>First name <input type="text" name="firstname"></td></tr>';
echo '<tr><td>Last name <input type="text" name="lastname"></td></tr>';
echo '<tr><td><input type="submit" name="submit" value="Add">';
echo '<input type="submit" name="submit" value="Search"></td></tr>';

// list students found
if(!empty($searchResults)){
	for($i=0; $i<sizeof($searchResults); $i++){
		echo '<tr><td><input type="checkbox" name="addarr[]" value="'.$searchResults[$i]['ID'].'">
		'.$searchResults[$i]['FIRST_NAME'].' '.$searchResults[$i]['MIDDLE_NAME'].' '.$searchResults[$i]['LAST_NAME'].'</td>
		<td>'.$searchResults[$i]['CLASS_YEAR'].'</td></tr>';
	}
}

// end right hand subtable and main table
echo '</table></td></tr></table>';


?>
