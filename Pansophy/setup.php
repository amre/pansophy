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
	@$this->$link = mysql_connect($host, $user, $pass);
	if(!$this->$link){
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
	$query = "CREATE TABLE `users` (
			ID		VARCHAR(20)	NOT NULL,
			Context1	VARCHAR(30),
			Context2	VARCHAR(30), 
			FirstName	VARCHAR(30)	NOT NULL,
			MiddleIn	VARCHAR(6)	NOT NULL,
			LastName	VARCHAR(30)	NOT NULL,
			AccessLevel	TINYINT(4)	NOT NULL default 0,
			Email		VARCHAR(30)	NOT NULL, 
			Extension	VARCHAR(4)	NOT NULL,
			IsFaculty	boolean		not null,
			IsStaff		boolean		not null,
			
			primary key (ID)
		
		) type = innodb";
	array_push( $queries, $query );
	
	$query = "create table `students` (
			ID		varchar(9)	not null,
			LastName	varchar(30)	not null,
			FirstName	varchar(30),
			MiddleIn	varchar(6)	not null,
			NickName	VARCHAR(15),
			ClassYear	VARCHAR(4),
			Email		VARCHAR(42),
			HousingBuilding VARCHAR(50),
			HousingRoom	int(3),
			CampusBox	VARCHAR(4),
			Extension	VARCHAR(4),
			Cellular	VARCHAR(13),	
			HomePhone	VARCHAR(20),
			Address1	VARCHAR(50),
			Address2	VARCHAR(50),
			ParentEmail	VARCHAR(42),
			ParentCellPhone	VARCHAR(20),
			Status		CHAR(2),
			Ethnic		CHAR(2),
			AssignedTo	varchar(20),
			Advisor		VARCHAR(50),
			RedFlag		TEXT		NOT NULL,
			VIP		text,
			AcProbation	boolean		not null default 0,
			HousingWaitList	boolean		not null default 0,
			ParkingWaitList	boolean		not null default 0,
			AllWatch	boolean		not null default 0,
			LastModified	TIMESTAMP,
			Modifier	VARCHAR(20),
			DateCreated	DATETIME,
		
			primary key (ID),
		
			foreign key (AssignedTo) references users (ID)
				on update cascade
				on delete set null
		
		) type = innodb";
	array_push( $queries, $query );
	
	$query = "CREATE TABLE `issues` (
			ID		VARCHAR(15),
			Header		VARCHAR(100)	NOT NULL,
			Creator		VARCHAR(45), 
			Modifier	VARCHAR(45),
			DateCreated	DATETIME	NOT NULL,
			Status		VARCHAR(25)	NOT NULL,
			LastModified	TIMESTAMP,
			AssignedTo	varchar(20),
		
			primary key (ID),
		
			foreign key (AssignedTo) references users (ID)
				on update cascade
				on delete set null
		
		) type = innodb";
	array_push( $queries, $query );
	
	$query = "create table `contacts` (
			ID		varchar(15)	not null,
			Creator		VARCHAR(20)	NOT NULL,
			Issue		VARCHAR(15)	NOT NULL, 
			Modifier	VARCHAR(20),
			DateCreated	DATETIME	NOT NULL,
			LastModified	TIMESTAMP	NOT NULL,
			Description	TEXT		NOT NULL,
		
			primary key (ID),
			
			foreign key (Creator) references users (ID),
			foreign key (Issue) references issues (ID)
				on update cascade
				on delete cascade,
			foreign key (Modifier) references users (ID)
				on update cascade
				on delete set null
		
		) type = innodb";
	array_push( $queries, $query );
	
	$query = "create table `contacts-students` (
			ContactID	varchar(15)	not null,
			StudentID	varchar(9)		not null,
		
			primary key (ContactID, StudentID),
			
			foreign key (ContactID) references contacts (ID)
				on update cascade
				on delete cascade,
			foreign key (StudentID) references students (ID)
				on update cascade
				on delete cascade
		
		) type = innodb";
	array_push( $queries, $query );
	
	$query = "create table `contacts-users` (
			ContactID	varchar(15)	not null,
			UserID		varchar(20)	not null,
		
			primary key (ContactID, UserID),
			
			foreign key (ContactID) references contacts (ID)
				on update cascade
				on delete cascade,
			foreign key (UserID) references users (ID)
				on update cascade
				on delete cascade
		
		) type = innodb";
	array_push( $queries, $query );
	
	$query = "create table `studentwatch` (
			UserID		varchar(20)	not null,
			StudentID	varchar(9)		not null,
			
			primary key (UserID, StudentID),
			
			foreign key (UserID) references users (ID)
				on delete cascade
				on update cascade,
			foreign key (StudentID) references students (ID)
				on delete cascade
				on update cascade
		
		) type = innodb";
	array_push( $queries, $query );
	
	$query = "create table `issuewatch` (
			UserID		varchar(20)	not null,
			IssueID		varchar(15)	not null,
			
			primary key (UserID, IssueID),
			
			foreign key (UserID) references users (ID)
				on delete cascade
				on update cascade,
			foreign key (IssueID) references issues (ID)
				on delete cascade
				on update cascade
		
		) type = innodb";
	array_push( $queries, $query );
	
	$query = "create table `userwatch` (
			UserID		varchar(20)	not null,
			OtherUserID	varchar(20)	not null,
			
			primary key (UserID, OtherUserID),
			
			foreign key (UserID) references users (ID)
				on delete cascade
				on update cascade,
			foreign key (OtherUserID) references users (ID)
				on delete cascade
				on update cascade
		
		) type = innodb";
	array_push( $queries, $query );
	
	$query = "create table `studentalert` (
			ID		int(255)	not null auto_increment,
			UserID		varchar(20)	not null,
			StudentID	varchar(9)		not null,
			Message		varchar(255),
			
			primary key (ID),
		
			foreign key (UserID) references users (ID)
				on delete cascade
				on update cascade,
			foreign key (StudentID) references students (ID)
				on delete cascade
				on update cascade
		
		) type = innodb";
	array_push( $queries, $query );
	
	$query = "create table `issuealert` (
			ID		int(255)	not null auto_increment,
			UserID		varchar(20)	not null,
			IssueID		varchar(15)	not null,
			Message		varchar(255),
			
			primary key (ID),
			
			foreign key (UserID) references users (ID)
				on delete cascade
				on update cascade,
			foreign key (IssueID) references issues (ID)
				on delete cascade
				on update cascade
		
		) type = innodb";
	array_push( $queries, $query );
	
	$query = "create table `useralert` (
			ID		int(255)	not null auto_increment,
			UserID		varchar(20)	not null,
			OtherUserID	varchar(20)	not null,
			Message		varchar(255),
			
			primary key (ID),
			
			foreign key (UserID) references users (ID)
				on delete cascade
				on update cascade,
			foreign key (OtherUserID) references users (ID)
				on delete cascade
				on update cascade
		
		) type = innodb";
	array_push( $queries, $query );
	
	$query = "create table `reports` (
			UserID		varchar(20)	not null,
			Type		varchar(20)	not null,
			Name		varchar(100)	not null,
			GetLine		varchar(255)	not null,
		
			primary key (UserID, Type, Name),
			
			unique( GetLine ),
		
			foreign key (UserID) references users (ID)
		
		) type = innodb";
	array_push( $queries, $query );
	
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
		<h1>Pansophy Contact Manager Setup - Step 2</h1>
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
	<h1>Pansophy Contact Manager Setup - Step 1</h1>
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
