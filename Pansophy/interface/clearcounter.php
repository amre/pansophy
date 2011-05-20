<?php
/**
 * This file contains the Administrative Task for resetting the InterimCounter field in
 * the `students` table to 0 for all students.
 *
 * Also, this file allows individual students to have their interim counters reset.
 */

include('../include/header.inc');
include('../DataAccessManager.inc.php');
$dam = new DataAccessManager();

if(strcmp($_POST['submit'], 'Yes') == 0){
	$dam->clearInterimCounter('');
	echo '<meta http-equiv="Refresh" content="0; URL=../main.php">';	
}
if(strcmp($_POST['submit'], 'No') == 0 || strcmp($_POST['submit'], 'Back') == 0){
   $_POST['submit'] = '';
	//echo '<meta http-equiv="Refresh" content="0; URL=../main.php">';
}
if(strcmp($_POST['submit'], 'Clear All') == 0){
	echo '<h1>Clear Interim Counter</h1>
	<p>Are you sure that you want to clear the InterimCounter field for all students?</p>
	<br />
	<form action="./clearcounter.php" method="POST">
	<input type="submit" name="submit" value="Yes"><input type="submit" name="submit" value="No">
	</form>
	</body></html>';
}
if(strcmp($_POST['submit'], 'Clear') == 0) {
	for($r = 0; $r < $_POST['length']; $r++)
	{
		if(isset($_POST['students'.$r.'']))
		{
			$studentID = $_POST['students'.$r.''];
			$dam->clearIndividualCounter('', $studentID );
		}
	}
	echo '<meta http-equiv="Refresh" content="0; URL=../main.php">';
}
if(strcmp($_POST['submit'], 'Clear Individuals') == 0) {
	$result = $dam->preClearInterimsIndividual();
	
	echo '<h1>Clear Interim Counter</h1>
	<p>Listed below are all of the students who have one or more Interims.<br />
	Click the checkboxes for all of the students for whom you wish to reset the counter to zero.</p>';
	echo '<br /><form action="./clearcounter.php" method="POST">';
	$t = 0;
	while($value = mysql_fetch_assoc($result)) {
		echo '<input type="checkbox" name="students'.$t.'" value="'.$value['ID'].'" />'.$value['FIRST_NAME'].' '.$value['LAST_NAME'].' - '.$value['ID'].' ['.$value['InterimCounter'].' Interim(s)]<br />';
		$t++;
	}
	echo '<br /><input type="hidden" name="length" value="'.$t.'"><input type="submit" name="submit" value="Clear"><input type="submit" name="submit" value="Back">';
	echo '</form></body></html>';
	
}
if(strcmp($_POST['submit'], 'Clear') != 0 && strcmp($_POST['submit'], 'Back') != 0 && strcmp($_POST['submit'], 'Clear Individuals') != 0 && strcmp($_POST['submit'], 'Yes') != 0 && strcmp($_POST['submit'], 'No') != 0 && strcmp($_POST['submit'], 'Clear All') != 0){
	$value = $dam->getLastClear();
	extract($value);
	
	if(strcmp($LastClear, "Not Set") == 0)
	{
		$LastClear = "Counter has not been cleared previously.";
	}
	
	echo '<h1>Clear Interim Counter(s)</h1>
	<p>This page allows all student InterimCounters to be cleared at once or allows for certain students to have their counters cleared.
	<br /><br />The Clear All button should only be done at the start of a new semester. (Last reset: '.$LastClear.')</p>
	<br />
	<form action="./clearcounter.php" method="POST">
	<input type="submit" name="submit" value="Clear All">&nbsp;<input type="submit" name="submit" value="Clear Individuals">
	</form>
	<br />
	<p><a href="./tasks.php">[Return to User Tasks]</a></p></body></html>';
}
?>
