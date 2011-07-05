<?php
/**
 * This script is the setup script.  After it's run to completion, there will be an SQL database
 * formatted for this software, and one user in the system with administrator privileges.
 */
include('./include/mainheader.inc');
/*This is the part of the script that sets up the first administrative user*/
if(strcmp($_POST['submit'], 'Create User') == 0){
	$file = fopen('./settings.php', 'rb') or die;  //Must be an absolute filename
	for($i = 0; !feof($file); $i++){
		$input = fgets($file);
		$input = explode(':', $input);
		$$input[0] = trim($input[1]);
	}
	$link = mysql_connect($host, $user, $pass);
	if(!$link){
		echo "<br>Could not connect to database.  Please try again later.";
		exit;
	}
	
	mysql_select_db($name);
	array_pop($_POST);
	$columns = array_keys( $_POST );
	$values = array_values( $_POST );
	for( $i = 0; $i < sizeof( $_POST ); $i++ ) {
		$columns[$i] = '`'.$columns[$i].'`';
		$values[$i] = '"'.$values[$i].'"';
	}
	$columns = implode( ',', $columns );
	$values = implode( ',', $values );
	$query = "INSERT INTO `users` ( $columns ) VALUES ( $values )";
	$result = mysql_query($query);
	if(!$result){
		echo '<script language=javascript>
 				alert("query fail: '.$query.'");
 				</script>';
	}
	echo '<meta http-equiv="Refresh" content="0; URL=./index.php">';
}
/*This sets up the database, and then prints out the interface for user data entry.*/
else if(strcmp($_POST['submit'], 'Submit') == 0){
	$keys = array_keys($_POST);
	for($i = 0; $i < sizeof($keys); $i++){
		$$keys[$i] = $_POST[$keys[$i]];
	}
	//Assumes settings are in the current directory.
	$file = fopen('./settings.php', 'wb');
	$out = "<?php
host:$host
name:$name
user:$user
pass:$pass
?>";
	fwrite($file, $out);
	fclose($file);
	$link = mysql_connect($host, $user, $pass);
	mysql_select_db($name);
	if(mysql_num_rows(mysql_list_tables($name))){
		echo 'The database has already been set up.';
		exit;
	}
	$queries = array();
	//Create the tables
	$query = "CREATE TABLE `attachments` (
  		`ID` varchar(15) NOT NULL default '',
  		`Extension` varchar(5) NOT NULL default '',
  		`Alias` varchar(50) NOT NULL default '',
  		`ContactID` varchar(15) NOT NULL default '',
  		PRIMARY KEY  (`ID`),
  		KEY `attachments_ibfk_1` (`ContactID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `contacts` (
  		`ID` varchar(15) NOT NULL default '',
  		`Creator` varchar(20) NOT NULL default '',
  		`Issue` varchar(15) NOT NULL default '',
  		`Modifier` varchar(20) default NULL,
  		`DateCreated` datetime NOT NULL default '0000-00-00 00:00:00',
  		`LastModified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  		`Description` text NOT NULL,
  		PRIMARY KEY  (`ID`),
  		KEY `contacts_ibfk_1` (`Creator`),
  		KEY `contacts_ibfk_2` (`Issue`),
  		KEY `contacts_ibfk_3` (`Modifier`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `contacts-students` (
  		`ContactID` varchar(15) NOT NULL default '',
  		`StudentID` varchar(9) NOT NULL default '',
  		PRIMARY KEY  (`ContactID`,`StudentID`),
  		KEY `contacts@002dstudents_ibfk_2` (`StudentID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `datecleared` (
  		`LastClear` varchar(20) NOT NULL default 'Not Set',
  		PRIMARY KEY  (`LastClear`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `emails` (
  		`WritingCenter` varchar(40) NOT NULL default '',
  		`LearningCenter` varchar(40) NOT NULL default '',
  		`MathCenter` varchar(40) NOT NULL default ''
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `flags` (
  		`Option1` varchar(40) NOT NULL default '',
  		`Option2` varchar(40) NOT NULL default '',
  		`Option3` varchar(40) NOT NULL default ''
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `interims` (
  		`ID` varchar(15) NOT NULL default '',
  		`StudentID` varchar(9) NOT NULL default '',
  		`Date` date NOT NULL default '0000-00-00',
  		`CourseNumberTitle` varchar(100) NOT NULL default '',
  		`Instructor` varchar(30) NOT NULL default '',
  		`Problem` text NOT NULL,
  		`Comments` text NOT NULL,
  		`RecommendAction` text NOT NULL,
  		`OtherAction` text NOT NULL,
  		`DateProcessed` date NOT NULL default '0000-00-00',
  		PRIMARY KEY  (`ID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `issuealert` (
  		`ID` int(255) NOT NULL auto_increment,
  		`UserID` varchar(20) NOT NULL default '',
  		`IssueID` varchar(15) NOT NULL default '',
  		`Message` varchar(255) default NULL,
  		PRIMARY KEY  (`ID`),
  		KEY `issuealert_ibfk_1` (`UserID`),
  		KEY `issuealert_ibfk_2` (`IssueID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=8282 ;";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `issues` (
  		`ID` varchar(15) NOT NULL default '',
  		`Header` varchar(100) NOT NULL default '',
  		`Creator` varchar(45) default NULL,
  		`Modifier` varchar(45) default NULL,
  		`DateCreated` datetime NOT NULL default '0000-00-00 00:00:00',
  		`Status` varchar(25) NOT NULL default '',
  		`Level` char(1) NOT NULL default 'B',
  		`Category` varchar(20) NOT NULL default '',
  		`LastModified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  		`AssignedTo` varchar(20) default NULL,
  		PRIMARY KEY  (`ID`),
  		KEY `issues_ibfk_1` (`AssignedTo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `issuewatch` (
  		`UserID` varchar(20) NOT NULL default '',
  		`IssueID` varchar(15) NOT NULL default '',
  		PRIMARY KEY  (`UserID`,`IssueID`),
  		KEY `IssueID` (`IssueID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `reports` (
  		`UserID` varchar(20) NOT NULL default '',
  		`Type` varchar(20) NOT NULL default '',
  		`Name` varchar(100) NOT NULL default '',
  		`GetLine` varchar(255) NOT NULL default '',
  		PRIMARY KEY  (`UserID`,`Type`,`Name`),
  		UNIQUE KEY `GetLine` (`GetLine`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `studentalert` (
  		`ID` int(255) NOT NULL auto_increment,
  		`UserID` varchar(20) NOT NULL default '',
  		`StudentID` varchar(9) NOT NULL default '',
  		`Message` varchar(255) default NULL,
  		PRIMARY KEY  (`ID`),
  		KEY `studentalert_ibfk_1` (`UserID`),
  		KEY `studentalert_ibfk_2` (`StudentID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=5162 ;";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `students` (
  		`StudentID` varchar(9) NOT NULL default '',
  		`AssignedTo` varchar(20) default NULL,
  		`RedFlag` text NOT NULL,
  		`VIP` text,
  		`AcProbation` tinyint(1) NOT NULL default '0',
  		`HousingWaitList` tinyint(1) NOT NULL default '0',
  		`Field1` tinyint(1) NOT NULL default '0',
  		`Field2` tinyint(1) NOT NULL default '0',
  		`Field3` tinyint(1) NOT NULL default '0',
  		`AllWatch` tinyint(1) NOT NULL default '0',
  		`FirstWatch` tinyint(1) NOT NULL default '0',
  		`InterimCounter` int(2) NOT NULL default '0',
  		`LastModified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  		`Modifier` varchar(20) default NULL,
  		`DateCreated` datetime default NULL,
  		PRIMARY KEY  (`StudentID`),
  		KEY `students_ibfk_1` (`AssignedTo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `students-FW` (
  		`StudentID` varchar(9) NOT NULL default '',
  		`Reason` varchar(40) NOT NULL default '',
  		PRIMARY KEY  (`StudentID`,`Reason`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `studentwatch` (
  		`UserID` varchar(20) NOT NULL default '',
  		`StudentID` varchar(9) NOT NULL default '',
  		PRIMARY KEY  (`UserID`,`StudentID`),
  		KEY `studentwatch_ibfk_2` (`StudentID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `useralert` (
  		`ID` int(255) NOT NULL auto_increment,
  		`UserID` varchar(20) NOT NULL default '',
  		`OtherUserID` varchar(20) NOT NULL default '',
  		`Message` varchar(255) default NULL,
  		PRIMARY KEY  (`ID`),
  		KEY `useralert_ibfk_1` (`UserID`),
  		KEY `useralert_ibfk_2` (`OtherUserID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `users` (
  		`ID` varchar(20) NOT NULL default '',
  		`Context1` varchar(30) default NULL,
  		`Context2` varchar(30) default NULL,
  		`FirstName` varchar(30) NOT NULL default '',
  		`MiddleIn` varchar(6) NOT NULL default '',
  		`LastName` varchar(30) NOT NULL default '',
  		`AccessLevel` tinyint(4) NOT NULL default '0',
  		`Email` varchar(30) NOT NULL default '',
  		`Extension` varchar(4) NOT NULL default '',
  		`IsFaculty` tinyint(1) NOT NULL default '0',
  		`IsStaff` tinyint(1) NOT NULL default '0',
  		PRIMARY KEY  (`ID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `userwatch` (
  		`UserID` varchar(20) NOT NULL default '',
  		`OtherUserID` varchar(20) NOT NULL default '',
  		PRIMARY KEY  (`UserID`,`OtherUserID`),
  		KEY `userwatch_ibfk_2` (`OtherUserID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `X_PNSY_ADDRESS` (
  		`ADDRESS_ID` varchar(10) NOT NULL default '',
  		`STREET_1` varchar(30) default NULL,
  		`STREET_2` varchar(30) default NULL,
  		`STREET_3` varchar(30) default NULL,
  		`STREET_4` varchar(30) default NULL,
  		`STREET_5` varchar(30) default NULL,
  		`CITY` varchar(30) default NULL,
  		`STATE` char(2) default NULL,
  		`ZIP` varchar(10) default NULL,
  		`COUNTRY` varchar(30) default NULL,
  		PRIMARY KEY  (`ADDRESS_ID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `X_PNSY_COURSE` (
  		`COURSE_ID` varchar(28) NOT NULL default '',
  		`TITLE` varchar(40) NOT NULL default '',
  		`CREDITS` decimal(9,5) default NULL,
  		`FACULTY_ID` varchar(10) default NULL,
  		PRIMARY KEY  (`COURSE_ID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `X_PNSY_COURSE_DAYS` (
  		`MEETING_ID` varchar(10) NOT NULL default '',
  		`COURSE_ID` varchar(28) NOT NULL default '',
  		`COURSE_BLDG` varchar(4) default NULL,
  		`COURSE_ROOM` varchar(8) default NULL,
  		`START_TIME` datetime default NULL,
  		`END_TIME` datetime default NULL,
  		`DAYS` varchar(7) default NULL,
  		PRIMARY KEY  (`MEETING_ID`,`COURSE_ID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `X_PNSY_FACULTY` (
  		`ID` varchar(10) NOT NULL default '',
  		`FIRST_NAME` varchar(30) NOT NULL default '',
  		`MIDDLE_NAME` varchar(30) default NULL,
  		`LAST_NAME` varchar(55) NOT NULL default '',
  		`SUFFIX` varchar(25) default NULL,
  		`WOOSTER_EMAIL` varchar(45) default NULL,
  		`PRIMARY_EMAIL` varchar(45) default NULL,
  		`CAMPUS_PHONE` varchar(4) default NULL,
  		`HOME_PHONE` varchar(25) default NULL,
  		`CELL_PHONE` varchar(25) default NULL,
  		PRIMARY KEY  (`ID`),
  		KEY `FIRST_NAME` (`FIRST_NAME`,`LAST_NAME`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `X_PNSY_PARENT` (
  		`ID` varchar(10) NOT NULL default '',
  		`FIRST_NAME` varchar(30) NOT NULL default '',
  		`MIDDLE_NAME` varchar(30) default NULL,
  		`LAST_NAME` varchar(55) NOT NULL default '',
  		`SUFFIX` varchar(25) default NULL,
  		`GENDER` char(1) default NULL,
  		`ADDRESS_ID` varchar(10) default NULL,
  		`PRIMARY_EMAIL` varchar(45) default NULL,
  		`HOME_PHONE` varchar(25) default NULL,
  		`CELL_PHONE` varchar(25) default NULL,
  		`PRIVACY_FLAG` varchar(10) default NULL,
  		PRIMARY KEY  (`ID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `X_PNSY_RELATIONSHIP` (
  		`ID_1` varchar(10) NOT NULL default '',
  		`ID_2` varchar(10) NOT NULL default '',
  		`RELATIONSHIP` varchar(30) default NULL,
  		PRIMARY KEY  (`ID_1`,`ID_2`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `X_PNSY_SCHEDULE` (
  		`STUDENT_ID` varchar(10) NOT NULL default '',
  		`COURSE_ID` varchar(28) NOT NULL default '',
  		PRIMARY KEY  (`STUDENT_ID`,`COURSE_ID`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query );

	$query = "CREATE TABLE `X_PNSY_STUDENT` (
  		`ID` varchar(10) NOT NULL default '',
  		`FIRST_NAME` varchar(30) NOT NULL default '',
  		`MIDDLE_NAME` varchar(30) default NULL,
  		`LAST_NAME` varchar(55) NOT NULL default '',
  		`SUFFIX` varchar(25) default NULL,
  		`GENDER` char(1) default NULL,
  		`ETHNIC` varchar(5) default NULL,
  		`ADDRESS_ID` varchar(10) default NULL,
  		`CAMPUS_BOX` varchar(10) default NULL,
  		`WOOSTER_EMAIL` varchar(45) default NULL,
  		`PRIMARY_EMAIL` varchar(45) default NULL,
  		`CAMPUS_PHONE` varchar(4) default NULL,
  		`HOME_PHONE` varchar(25) default NULL,
  		`CELL_PHONE` varchar(25) default NULL,
  		`CLASS_YEAR` varchar(4) default NULL,
  		`ENROLL_STATUS` varchar(5) default NULL,
  		`HOUSING_BLDG` varchar(10) default NULL,
  		`HOUSING_ROOM` varchar(10) default NULL,
  		`ADVISOR` varchar(10) default NULL,
  		`PRIVACY_FLAG` varchar(10) default NULL,
  		`MAJOR_1` varchar(4) default NULL,
  		`MAJOR_2` varchar(4) default NULL,
  		PRIMARY KEY  (`ID`),
  		KEY `FIRST_NAME` (`FIRST_NAME`,`LAST_NAME`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	array_push( $queries, $query);
	
	//echo $query;
	foreach( $queries as $query ) {
		@$result = mysql_query($query);
		if(!$result){
			echo "<br>Could not setup database.  Please contact your system administrator<br>";
			echo mysql_error($link);
			exit;
		}
	}
	echo '<form action="./setup.php" method="POST">
		<center>
		<p>
		<p>
		<h1>Phronesis Contact Manager Setup - Step 2</h1>
		<table><td><h3>Administrator Information</h3></td><td><a href="./help/" class="black" target="_blank">[Help]</a></td></table>
		<table border="1" class="darkbg" RULES="BOX" cellpadding="4">
		<tr><td>
		<p class="plaincolortext">First Name</p>
		</td><td>
		<input type="text" name="FirstName">
		</td></tr>
		<tr><td>
		<p class="plaincolortext">Last Name</p>
		</td><td>
		<input type="text" name="LastName">
		</td></tr>
		<tr><td>
		<p class="plaincolortext">User name</p>
		</td><td>
		<input type="text" name="ID">
		</td></tr>
		<tr><td>
		<p class="plaincolortext">First context field</p>
		</td><td>
		<input type="text" name="Context1">
		</td></tr>
		<tr><td>
		<p class="plaincolortext">Second context field</p>
		</td><td>
		<input type="text" name="Context2">
		</td></tr>
		<tr><td>
		<p class="plaincolortext">Email Address</p>
		</td><td>
		<input type="text" name="Email">
		</td></tr>
		</table>
		<input type="hidden" name="AccessLevel" value="10">
		<p>
		<input type="submit" name="submit" value="Create User">
		</center>
		</form>';

}
/*This part of the script prints out the interface for the database information entry.*/
else{
	echo '<p>
	     <p>
	<h1>Phronesis Contact Manager Setup - Step 1</h1>
	<form action="./setup.php" method="POST">
		<center>
		<table><td><h3>Database Information</h3></td><td><a href="./help/" class="black" target="_blank">[Help]</a></td></table>
		<table border="1" class="darkbg" RULES="BOX" cellpadding="4">
		<tr><td>
			<p class="plaincolortext">MySQL server address (leave blank if it\'s localhost)</p>
		</td><td>
			<input type="text" name="host">
		</td></tr>
		<tr><td>
			<p class="plaincolortext">Database name</p>
		</td><td>
			<input type="text" name="name">
		</td></tr>
		<tr><td>
			<p class="plaincolortext">Database administration username</p>
		</td><td>
			<input type="text" name="user">
		</td></tr>
		<tr><td>
			<p class="plaincolortext">Database password</p>
		</td><td>
			<input type="password" name="pass">
		</td></tr>
		</table>
		<p>
		<input type="submit" name="submit" value="Submit">
		</center>
	</form>';
}
?>
