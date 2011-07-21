<?php
/*
This page gives an administrator the option to archive database contents that occurred on or before a given year.
*/
//+-----------------------------------------------------------------------------------------+ 

include('../include/header.inc');
include('../DataAccessManager.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+ 
if ($dam->getAccessLevel() != 10 and !isset($_SESSION['historical'])) // Admins only
{
header("Location: ../logout.php");
exit();
}
//process database switch
if(isset($_POST['switch']) && isset($_POST['database']))
{
	if(strcmp($_POST['database'],"Historical")==0 and !isset($_SESSION['historical']))
	{
		if ($dam->getAccessLevel() == 10) $_SESSION['historical']=TRUE;
		//header("Location: ../");
		echo '<META HTTP-EQUIV="Refresh" CONTENT="0;url=../main.php">';
	}
	else if (strcmp($_POST['database'],"Current")==0 and isset($_SESSION['historical']))
	{
		unset($_SESSION['historical']);
	}
}
if (isset($_POST['yes'])) // do purge
{
	echo '<center></br>Please DO NOT refresh or close this window - it may result in a partial/incomplete archive.</br></br>Processing...</br><br>';
	//force "Processing" to display
	echo str_pad('', 512);
	echo '<!-- -->';
	if(ob_get_length()){
		@ob_flush();
		@flush();
		@ob_end_flush();
	}
	@ob_start();
	//begin archive/deletion
	$dam->deleteFromCurrent('', $_POST['year']);
	echo '<br/><br/><center><p>Success! System archived.</p></center>';
}
else if (isset($_POST['no'])) // cancel purge
{
	header("Location: ../main.php");
}
else if (isset($_POST['year']) && preg_match("/\d\d\d\d/",$_POST['year'])) //warning message
{
	echo '<center><h1 style="color:ff0000"> WARNING </h1>
<p> This action is undoable. All students who graduated in or before '.$_POST['year'].' will be archived. </br>
Are you sure you want to do this?</p>
<form action="archive.php" method="post"><table><tr><td style="padding:50px">
<input type="submit" name="yes" value="Yes"/></td>
<td style="padding:50px"><input type="submit" name="no" value="No"></td></tr></table><input type="hidden" name="year" value="'.$_POST['year'].'"></form></center>';
}
//process bringing a student back into the current database
else if (isset($_POST['studentid']) && ctype_digit($_POST['studentid']) && strlen($_POST['studentid'])==7)
{
	$studentID=$_POST['studentid'];
	echo '<center></br>Please DO NOT refresh or close this window - it may cause only part of a student\'s information to be moved into the current database.</br></br>Processing...</br><br>';
	//force "Processing" to display
	echo str_pad('', 512);
	echo '<!-- -->';
	if(ob_get_length()){
		@ob_flush();
		@flush();
		@ob_end_flush();
	}
	@ob_start();
	//bring student in if the ID is valid
	if($dam->pullStudentFromArchive('', $_POST['studentid'])){
		echo 'Success!</br>';
		echo '<a href="./viewstudent.php?id='.$studentID.'"><b>Go to student\'s page</b></a><br>';
	}
	else
		echo '<p style="color:#ff0000"> Invalid ID </p>';
	echo '</center>';
}
//nothing valid has been set/chosen yet
else
{
	if (!isset($_SESSION['historical']))
	{		// opening page
		echo '<div style="background:#ffffff;border-bottom:solid #000000; top: 0%;z-index:95;width:100%;padding:0%;margin:0;">';
		echo '<center><h1> Archive Database </h1><p>Enter a year to archive all students whose class year is less than or equal to the given year:</p>';
		if (isset($_POST['year'])) //user entered incorrect input
		{
			echo '<p style="color:#ff0000"> Incorrect year format</p>';
		}
		echo'<form action="archive.php" method="post">
<input type="text" size="4" maxlength="4" name="year">
<input type="submit" name="purge" value="Archive">
</form></center>';
		echo '</div>';
	}
	//print database switcher
	if ($dam->getAccessLevel() == 10 || isset($_SESSION['historical']))
	{
		echo '<div style="background:#ffffff;border-bottom:solid #000000; top: 0%;z-index:95;width:100%;padding:0%;margin:0;">';
		echo '<center><h1> View Historical Database </h1>';
		echo '<form action="./archive.php" method="post">';
		echo '<p>Administrators may access archived information by choosing to load the historical database below.</br>While browsing the historical database, no user is permitted to make changes to data.</br><b>Note:</b> To return to the current database, log out and log back in.</br></br>Select database:';
		if ($_SESSION['historical'] === TRUE)
		{
			echo '<input type="radio" name="database" value="Current"/> Current';
			echo '<input type="radio" checked name="database" value="Historical"/> Historical';
		}
		else
		{
			echo '<input type="radio" checked name="database" value="Current"/> Current';
			echo '<input type="radio" name="database" value="Historical"/> Historical';
		}
		echo '    <input type="submit" name="switch" value="Select">';
		echo '</form></p></center>';
		echo '</div>';
	}
	//print pulling student
	echo '<div style="background:#ffffff;border-bottom:solid #000000; top: 0%;z-index:95;width:100%;padding:0%;margin:0;">';
	echo '<center><h1> Add Student From Archive </h1>';
	echo '<form action="archive.php" method="post">';
	echo 'Enter a student\'s ID number to bring them back into the current database. </br>The student will still exist in the historical database.</br>';
	if (isset($_POST['studentid'])) //user entered incorrect input
	{
		echo '<p style="color:#ff0000"> Incorrect ID format </p>';
	}
	else{echo'</br>';}
	echo 'Student ID: ';
	echo '<input type="text" size="7" maxlength="7" name="studentid">
		<input type="submit" name="addstudent" value="Add Student">
		</form></center>';
	echo '</div>';

}
echo "</body></html>";
?>
