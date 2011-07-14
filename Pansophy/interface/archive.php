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
if(isset($_POST['database']))
{
	if(strcmp($_POST['database'],"Historical")==0 and !isset($_SESSION['historical']))
	{
		if ($dam->getAccessLevel() == 10) $_SESSION['historical']=TRUE;
	}
	else if (strcmp($_POST['database'],"Current")==0 and isset($_SESSION['historical']))
	{
		unset($_SESSION['historical']);
	}
}
if (isset($_POST['yes'])) // do purge
{
echo "year=".$_POST['year'];
//$dam->archiveYear($_POST['year']);
$dam->archiveEverything('');
echo '<br/><br/><center><p>System archived</p></center>';
//echo '<meta http-equiv="Refresh" content="3;URL=../main.php">';
}
else if (isset($_POST['no'])) // cancel purge
{
	header("Location: ../main.php");
}
else if (isset($_POST['year']) && preg_match("/\d\d\d\d/",$_POST['year'])) //warning message
{
echo
'<center><h1 style="color:ff0000"> WARNING </h1>
<p> This action is undoable. All records occuring on or before the year
of '.$_POST['year'].' will be archived. Are you sure you want to do this?</p>
<form action="archive.php" method="post"><table><tr><td style="padding:50px">
<input type="submit" name="yes" value="Yes"/></td>
<td style="padding:50px"><input type="submit" name="no" value="No"></td></tr></table><input type="hidden" name="year" value="'.$_POST['year'].'"></form></center>';
}
else
{
if (!isset($_SESSION['historical']))
{		// opening page
echo 
'<center><h1> Archive Database</h1></br>
<p>Enter a year to archive all students, contacts, and associated information occurring on or before that year</p>';
if (isset($_POST['year'])) //user entered incorrect input
{
	echo '<p style="color:#ff0000"> Incorrect year format</p>';
}
echo'<form action="archive.php" method="post">
<input type="text" size="4" maxlength="4" name="year">
<input type="submit" name="purge" value="Archive">
</form></center>';
}
//print database switcher
if ($dam->getAccessLevel() == 10 || isset($_SESSION['historical']))
{
	echo '</br><center><p>Administrators may access archived information by choosing to load the historical database below. Please note that, while browsing the historical database, no user is permitted to make changes to data. Select database:';
	echo '<form action="./archive.php" method="post">';
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
	echo '    <input type="submit" value="Select">';
	echo '</form></p></center>';
}

}
echo "</body></html>";
?>
