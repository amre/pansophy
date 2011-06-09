<?php

/**
 *This is the script that processes search information and returns the results.
 */
 
echo "<center>";

include_once("./include/mainheader.inc");
include('./DataAccessManager.inc.php');
include_once("./include/miscfunctions.inc.php");
$dam=new DataAccessManager();
$batch = array();
$resultcount = 0;

// Gets stored flag names.
$value1 = $dam->extractFlags();
extract($value1);

$booleanFields = array( 'AcProbation', 'HousingWaitList', $Option1 , $Option2, $Option3 );

//Get the relevant data from the Post method
/* Resets the value of $SearchField to Field1, Field2, and Field3 from the exact values in `flags`
 * so that it can be used in DataAccessMananger.inc.php. - Josh Thomas
 */
$SearchField = $_POST['SearchField'];
if( in_array( $_POST['SearchField'], $booleanFields ) ) $SearchTerm = '1';
else $SearchTerm = $_POST['SearchTerm'];
if(strcmp($SearchField, $Option1) == 0)
	$SearchField = "Field1";
if(strcmp($SearchField, $Option2) == 0)
	$SearchField = "Field2";
if(strcmp($SearchField, $Option3) == 0)
	$SearchField = "Field3";
$table = $_POST['table'];

// Michael Thompson * 12/07/2005 * Added sort orders for the various tables
//set sort order
if ($table=='students') $SearchOrder = '`LAST_NAME`, `FIRST_NAME`';
//else if ($table=='X_PNSY_STUDENTS') $SearchOrder = '`LAST_NAME`, `FIRST_NAME`';
else if ($table=='users') $SearchOrder = '`LastName`, `FirstName`';
else if ($table=='issues') $SearchOrder = '`DateCreated` DESC';
else if ($table=='contacts') $SearchOrder = '`DateCreated` DESC';
else $SearchOrder = '';

//do batch operations
if (isset($_POST['submit']) && $_POST['submit'] == 'Submit' ) {
	unset( $_POST['submitted'] );
	//print_r( $_POST );
	if( $_POST['results'] ) $items = array_keys( $_POST['results'] );
	else $items = array();
	$type = $_POST['type'];
	$action = $_POST['function'];
	foreach ( $items as $value ) {
/* Michael Thompson * 12/16/2005 * added ability to add students to the various lists
 * (housing, AcPro, Admin-controlled fields). - Addendum by Josh Thomas
 */
		if ( $type == 'students' ) {
			if ( $action == 'watch' ) $dam->watchStudent( '', $value );
			elseif ( $action == 'unwatch' ) $dam->stopWatchingStudent( '', $value );
			elseif ( $action == 'unacpro' ) {
				$StudentInfo['AcProbation'] = 0;
				$dam->modifyStudent( '', $value, $StudentInfo );
			}
			elseif ( $action == 'unF1' ) {
				$StudentInfo['Field1'] = 0;
				$dam->modifyStudent( '', $value, $StudentInfo );
			}
			elseif ( $action == 'unF2' ) {
				$StudentInfo['Field2'] = 0;
				$dam->modifyStudent( '', $value, $StudentInfo );
			}
			elseif ( $action == 'unF3' ) {
				$StudentInfo['Field3'] = 0;
				$dam->modifyStudent( '', $value, $StudentInfo );
			}
			elseif ( $action == 'unwaithouse' ) {
				$StudentInfo['HousingWaitList'] = 0;
				$dam->modifyStudent( '', $value, $StudentInfo );
			}
			elseif ( $action == 'acpro' ) {
				$StudentInfo['AcProbation'] = 1;
				$dam->modifyStudent( '', $value, $StudentInfo );
			}
			elseif ( $action == 'F1' ) {
				$StudentInfo['Field1'] = 1;
				$dam->modifyStudent( '', $value, $StudentInfo );
			}
			elseif ( $action == 'F2' ) {
				$StudentInfo['Field2'] = 1;
				$dam->modifyStudent( '', $value, $StudentInfo );
			}
			elseif ( $action == 'F3' ) {
				$StudentInfo['Field3'] = 1;
				$dam->modifyStudent( '', $value, $StudentInfo );
			}
			elseif ( $action == 'waithouse' ) {
				$StudentInfo['HousingWaitList'] = 1;
				$dam->modifyStudent( '', $value, $StudentInfo );
			}
		}
		elseif ( $type == 'issues' ) {
			if ( $action == 'watch' ) $dam->watchIssue( '', $value );
			elseif ( $action == 'unwatch' ) $dam->stopWatchingIssue( '', $value );
			elseif ( $action == 'close' ) $dam->setIssueStatus( '', $value, 'Closed' );
		}
	}
	echo '<div class="mediumheading">';
	if ( $type == 'students' ) {
		echo 'Student(s)';
// Michael Thompson * 12/16/2005 * added status messages for adding students to lists
// Added new Admin-controlled flag messages as well. - Josh Thomas
		if ( $action == 'watch' ) echo ' watched.';
		elseif ( $action == 'unwatch' ) echo ' removed from watch list.';
		elseif ( $action == 'unacpro' ) echo ' removed from academic probation.';
		elseif ( $action == 'unF1' ) echo ' removed from the '.$Option1.' flag.';
		elseif ( $action == 'unwaithouse' ) echo ' removed from the housing waitlist.';
		elseif ( $action == 'unF2' ) echo ' removed from the '.$Option2.' flag.';
		elseif ( $action == 'unF3' ) echo ' removed from the '.$Option3.' flag.';
		elseif ( $action == 'acpro' ) echo ' added to academic probation.';
		elseif ( $action == 'F1' ) echo ' added to the '.$Option1.' flag.';
		elseif ( $action == 'waithouse' ) echo ' added to the housing waitlist.';
		elseif ( $action == 'F2' ) echo ' added to the '.$Option2.' flag.';
		elseif ( $action == 'F3' ) echo ' added to the '.$Option3.' flag.';
	}
	elseif ( $type == 'issues' ) {
		echo 'Issue(s)';
		if ( $action == 'watch' ) echo ' watched.';
		elseif ( $action == 'unwatch' ) echo ' not watched.';
		elseif ( $action == 'close' ) echo ' closed.';
	}
	echo '</div><br><br>';
}

//Check to make sure we have all necessary fields
if ($SearchField == ""){
	print_r( $_POST );
	echo 'Search form filled out incorectly.  Please try again.';
	exit;
}
else if(strcmp($table, 'students') != 0 && strcmp($table, 'contacts') != 0 && strcmp($table, 'issues') != 0 && strcmp($table, 'users') != 0){
	echo '<meta http-equiv="Refresh" content="3; URL=./main.php">';
	echo 'Not a valid SQL table';
	exit;
}
//'Ethnic''ETHNIC'
else{
	$results = array();
	//Set the special case fields using the special case arrays
	if( $table == 'students' ) {
		$links = array('LastName', 'Email');
		$links = array('LAST_NAME', 'WOOSTER_EMAIL');
		//$doNotDisplay holds the fields that are returned from the search that you don't want to show up in the result table
		$doNotDisplay = array('LastModified', 'DateCreated', 'RedFlag', 'Cellular', 'UsersWatching', 'Address1', 'Address2', 
					'NickName', 'HomePhone', 'Status', 'Advisor', 'Modifier', 'VIP', 'AssignedTo', 
					'HousingBuilding', 'HousingRoom', 'ParentEmail', 'ParentCellPhone', 'AcProbation', 'HousingWaitList', 
					'Field1', 'Field2', 'Field3', 'AllWatch', 'ParentName1', 'ParentName2', 'City', 'State', 'ZIP', 'Country',
					'StudentActivities', 'InterimCounter', 'FirstWatch','MAJOR_1','MAJOR_2',
					'StudentID', 'SUFFIX', 'GENDER', 'BIRTHDAY', 'ADDRESS_ID', 'PRIMARY_EMAIL', 'HOME_PHONE', 'CELL_PHONE', 'CAMPUS_PHONE',
					'ADVISOR', 'HOUSING_BLDG', 'HOUSING_ROOM', 'PRIVACY_FLAG',
					'STREET_2', 'STREET_3', 'STREET_4', 'STREET_5', 'CITY', 'STATE', 'ZIP', 'COUNTRY');
	}
	if( $table == 'issues' ) {
		$links = array('Creator', 'Modifier', 'ID');
		//$doNotDisplay holds the fields that are returned from the search that you don't want to show up in the result table
		$doNotDisplay = array ('UsersWatching', 'Staff'); 
	}
	if( $table == 'contacts' ) {
		$links = array('Creator', 'Issue');
		//$doNotDisplay holds the fields that are returned from the search that you don't want to show up in the result table
		$doNotDisplay = array ('Description', 'Modifier', 'LastModified');
	}
	if( $table == 'users' ) {
		$links = array('ID','Email');
		//$doNotDisplay holds the fields that are returned from the search that you don't want to show up in the result table
		$doNotDisplay = array('Context1','Context2','Alerts','AccessLevel','IsFaculty','IsStaff');
	}
	//Searching contacts or issues for students assosciated with them is a special case.  Thus, it needs a special search thingy.
// Michael Thompson * 12/07/2005 * Added search order field to special issue and contact searches
	if (($table == 'issues' || $table == 'contacts') && $SearchField == 'Students'){
		if( $table == 'issues' ) {
			$results = $dam->issuesByStudentName( $_POST['Students'], $SearchOrder );
		}
		else if( $table == 'contacts' ) {
			$results = $dam->contactsByStudentName( $_POST['Students'], $SearchOrder );
		}
	}
	
	else if (($table == 'issues' || $table == 'contacts') && $SearchField == 'Staff'){
		if( $table == 'issues' ) {
			$results = $dam->issuesByUserName( $_POST['Staff'], $SearchOrder );
		}
		else if( $table == 'contacts' ) {
			$results = $dam->contactsByUserName( $_POST['Staff'], $SearchOrder );
		}
	}
	//Normal search
	else{
// Michael Thompson * 12/07/2005 * Added search order field to performSearch call
		
		$results = $dam->performSearch('', $table, $SearchField, $SearchTerm, $SearchOrder);
	}
	//checks to see if there were any search results
	if(!(sizeof($results) > 0)){
		echo "There were no matches for your search.";
		exit;	
	}
	else{
		$columns = array_keys($results[0]);
		
		// prints the first half of the batch operation form
		if ( $table == 'students' || $table == 'issues' ) {
			echo '
			<form action="./results.php" method="POST">
			<select name="function">';
			if ( $table == 'students' ) {
// Michael Thompson * 12/07/2005 * Added options for adding students to parking list, housing list, or academic probation
// Addendum - Parking list removed in place of three Admin-controlled flags. - Josh Thomas
				echo'
				<option value="watch">Watch marked student(s)</option>
				<option value="unwatch">Stop watching marked student(s)</option>
				<option value="acpro">Mark marked student(s) as on Academic Probation</option>
				<option value="unacpro">Mark marked student(s) as off Academic Probation</option>
				<option value="waithouse">Add marked student(s) to the housing wait list</option>
				<option value="unwaithouse">Take marked student(s) off the housing wait list</option>';
				if($Option1){
					echo '<option value="F1">Add marked student(s) to the '.$Option1.' flag</option>
					<option value="unF1">Take marked student(s) off the '.$Option1.' flag</option>';
				}
				if($Option2){
					echo '<option value="F2">Add marked student(s) to the '.$Option2.' flag</option>
					<option value="unF2">Take marked student(s) off the '.$Option2.' flag</option>';
				}
				if($Option3){
					echo '<option value="F3">Add marked student(s) to the '.$Option3.' flag</option>
					<option value="unF3">Take marked student(s) off the '.$Option3.' flag</option>';
				}
				echo '</select>';
			}
			else if ( $table == 'issues' ) {
				echo '
				<option value="watch">Watch marked issue(s)</option>
				<option value="unwatch">Stop watching marked issue(s)</option>
				<option value="close">Close marked issue(s)</option>
				</select>';
			}
			echo '&nbsp;<input type="submit" name="submit" value="Submit">';
			echo
			'<input type="hidden" name="SearchField" value="'.$SearchField.'">
			<input type="hidden" name="SearchTerm" value="'.$SearchTerm.'">
			<input type="hidden" name="table" value="'.$table.'">';
		}
		echo '<br/><br/>';
		
		echo '
		<table class="result"><tr>';
		if ( $table == 'students' || $table == 'issues' ) echo '<th class="nocontent"></th>';	//empty column for checkboxen
		for ($i = 0; $i <sizeof($columns); $i++) {
			if( (!in_array($columns[$i], $doNotDisplay)) || ($columns[$i] == $SearchField) ){
				// Changes heading display to actual flag name and then changes back to display value later - Josh Thomas
				if(strcmp($columns[$i], "Field1") == 0)
					$columns[$i] = $Option1;
				if(strcmp($columns[$i], "Field2") == 0)
					$columns[$i] = $Option2;
				if(strcmp($columns[$i], "Field3") == 0)
					$columns[$i] = $Option3;
				if(strcmp($columns[$i],"VIP") == 0 || strcmp($columns[$i],"ID") == 0 )
					echo "<th>".$columns[$i]."</th>";
				elseif($dam->fieldInTable('students', $columns[$i]) )
					echo "<th>".preg_replace('/(\w+)([A-Z])/U', '\\1 \\2', $columns[$i])."</th>";
				else
					echo "<th>".bonnyFieldName($columns[$i])."</th>";
				if(strcmp($columns[$i], $Option1) == 0)
					$columns[$i] = "Field1";
				if(strcmp($columns[$i], $Option2) == 0)
					$columns[$i] = "Field2";
				if(strcmp($columns[$i], $Option3) == 0)
					$columns[$i] = "Field3";
			}
		}
		echo '</tr>';
		
		// must start with the empty string as a value.
		// stupid PHP...
		$rowclasses = array( "", "lighter", "darker" );
		
		//Cycles through the array of results and prints each one out as its own table row.
		//this is the worst spaghetti code i have ever seen.  what does any of this do?!
		for($h=0; $h<sizeof($results); $h++){
			
			// this gets the CSS class for the row (mainly used to alternate colors)
			//
			// if you dont have any elements in $rowclasses you'll get yourself in an
			// infinite loop, so put something in it or comment this line out, fool!
			
			while( !( $trclass = next( $rowclasses ) ) ) reset( $rowclasses );
			
			echo "<tr class='$trclass'>";
			for($i=0; $i < sizeof($columns); $i++){
				$$columns[$i]=$results[$h][$columns[$i]];
				//jeremy might love variable variables, but no one else does.
			}
			if(strcmp($table, 'students')==0){
			
				// keep `students` table up to date
				$dam->verifyStudent($ID);
				
				$urls= array('./interface/viewstudent.php?id='.$ID, 'mailto:'.$WOOSTER_EMAIL);			
				$batch[$h] = $ID;
			}
			if(strcmp($table, 'issues')==0){
				$urls= array('mailto:'.$dam->getUserEmail($Creator), 'mailto:'.$dam->getUserEmail($Modifier), 
						'./interface/viewissue.php?id='.$ID);
				$batch[$h] = $ID;
			}
			if(strcmp($table, 'contacts')==0){
				$urls= array('mailto:'.$dam->getUserEmail($Creator), './interface/viewissue.php?id='.$Issue);
			}
			if(strcmp($table, 'users')==0){
				$urls= array('./interface/viewuser.php?id='.$ID, 'mailto:'.$Email);
			}
			$urls=array_combine($links, $urls);
			if ( $table == 'students' || ($table == 'issues' && $dam->userCanViewIssue('', $ID))) {
				echo '<td><input type="checkbox" name="results['.$results[$h]['ID'].']"></td>';	//checkbox at the beginning of each row for batch operations
			}
			if(($table == 'issues' && $dam->userCanViewIssue('', $ID))
				||($table == 'contacts' && $dam->userCanViewContact('', $ID))
				||($table == 'users') || ($table == 'students')){
				for($i=0; $i < sizeof($columns); $i++){
					$field=$columns[$i];
					if(!in_array($field, $doNotDisplay) || ($field == $SearchField)){
						echo '<td>'; //opening tag of every cell below the first row
						if(strcmp($field, 'Students')==0){
							/*$Students=explode(',', $Students);
							for($j=0; $j<count($Students); $j++){
								$Student=$dam->ViewStudent('',$Students[$j]);
								$FirstName=$Student['FirstName'];
								$LastName=$Student['LastName'];
								echo '<a href="./interface/viewstudent.php?id='.$Students[$j].'">'.$FirstName.' '.$LastName.'</a>';
								if(!($j == count($Students)-1)){
									echo ', ';
								}
							}
							echo '</td>';*/
						}
						
						else if (strcmp($field, 'LastModified')==0){
							echo readableDateAndTime( $LastModified ).'</td>';
						}
						
						else if (strcmp($field, 'DateCreated')==0){
							echo readableDateAndTime( $DateCreated ).'</td>';
						}
						else{
							if(in_array($field, $links)){
								//a third to a half of the table comes from this line.
								echo '<a href="'.$urls[$field].'">'.$$field.'</a></td>';
							}
							else{
								if($field == "StudentActivities")
								{
									$$field = str_replace("%", ", ", $$field);
								}
								elseif( $field == 'ADVISOR' ) {
									$$field = $dam->getFacultyName( $$field );
								}
								//the rest of it comes from here.
								echo stripslashes_all($$field)."</td>";
							}
						}
						//honestly, i haven't seen anything but the last if/else clause output anything, ever.  i don't know why it's there
						//but so that i don't break jeremy's spagetti(sp?) code, i'll leave it alone.
						//hope this helps anyone who may read this in the future:  if you want to change the output, change the last if/else
					}
				}
			}
			echo "</tr>";
		}
	}
	
	echo '</tr></table>';
	/**prints the second half of the batch operation form.
	* it's down here because $batch needs the values that are generated through 
	* the loops and whatnot above to be imploded.  if there's a cleaner way to
	* do this, then i don't know it : (
	**/
	if ( $table == 'students' || $table == 'issues' ) {
		echo '
		<input type="hidden" name="type" value="'.$table.'">';
		echo '
		</form>';
	}
}
mysql_close();




echo "</center>";





?>
