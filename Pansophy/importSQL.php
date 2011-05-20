<?php

// settings for the old database
$old['host'] = 'webserv1.wooster.edu';
$old['username'] = 'xxx';
$old['password'] = 'xxx';
$old['dbname'] = 'studentaffairs';

// settings for the new database
$new['host'] = 'misclinux.wooster.edu';
$new['username'] = 'xxx';
$new['password'] = 'xxx';
$new['dbname'] = 'pansophy';

// these arrays store the queries as they are built
$primary = array();
$secondary = array();

// build insert statements from the data in the old database
buildQueries( $old['host'], $old['username'], $old['password'], $old['dbname'] );
// execute those insert statements on the new database
executeQueries( $new['host'], $new['username'], $new['password'], $new['dbname'] );

function primaryQuery( $query ) {
	global $primary;
	return array_push( $primary, $query );
}

function secondaryQuery( $query ) {
	global $secondary;
	return array_push( $secondary, $query );
}

function buildQueries( $host, $username, $password, $dbname ) {
	mysql_connect( $host, $username, $password );
	mysql_select_db( $dbname);
	
	///////////////////////////////////////////////////////////////////////////////
	// get data from old database to create insert statements for the new database
	///////////////////////////////////////////////////////////////////////////////
	
	// get data from user table ---- DOES NOT TRANSFER ALERTS
	$query = "select * from users";
	$result = mysql_query( $query );
	while( $row = mysql_fetch_assoc( $result ) ) {
		$alerts = explode( ',', $row['Alerts'] );
		unset( $row['Alerts'] );
		// put each value in 'single quotes';
		foreach( $row as $field => $value ) {
			$row[$field] = "'".mysql_escape_string( $value )."'";
		}
		// make the field names and values into strings for SQL use
		$fields = implode( ', ', array_keys( $row ) );
		$values = implode( ', ', $row );
		// create the user
		primaryQuery( "insert into users ($fields) values ($values)" );
		// add the alerts
		foreach( $alerts as $alert ) {
			if( $alert ) { // this 'if' statement keeps the extra comma in the field from giving us an extra blank ID
				
				// PUT SOMETHING HERE TO TRANSFER ALERTS
				
			}
		}
	}
	
	// get data from student table
	$query = "select * from students";
	$result = mysql_query( $query );
	while( $row = mysql_fetch_assoc( $result ) ) {
		$watchers = explode( ',', $row['UsersWatching'] );
		unset( $row['UsersWatching'] );
		// put each value in 'single quotes';
		foreach( $row as $field => $value ) {
			$row[$field] = "'".mysql_escape_string( $value )."'";
		}
		// make the field names and values into strings for SQL use
		$fields = implode( ', ', array_keys( $row ) );
		$values = implode( ', ', $row );
		// create the student
		primaryQuery( "insert into students ($fields) values ($values)" );
		// add the watchers
		foreach( $watchers as $watcher ) {
			if( $watcher ) { // this 'if' statement keeps the extra comma in the field from giving us an extra blank ID
				secondaryQuery( "insert into studentwatch (studentid, userid) values (".$row['ID'].", '".mysql_escape_string( $watcher )."')" );
			}
		}
	}
	
	// get data from issue table
	$query = "select * from issues";
	$result = mysql_query( $query );
	while( $row = mysql_fetch_assoc( $result ) ) {
		
		unset( $row['Students'] );	// DO NOTHING - THIS FIELD IS REDUNDANT AND CAN BE CONSTRUCTED FROM CONTACTS
		unset( $row['Staff'] );		// DO NOTHING - THIS FIELD IS REDUNDANT AND CAN BE CONSTRUCTED FROM CONTACTS
		
		$watchers = explode( ',', $row['UsersWatching'] );
		unset( $row['UsersWatching'] );
		
		// put each value in 'single quotes';
		foreach( $row as $field => $value ) {
			$row[$field] = "'".mysql_escape_string( $value )."'";
		}
		
		// make the field names and values into strings for SQL use
		$fields = implode( ', ', array_keys( $row ) );
		$values = implode( ', ', $row );
		
		// create the issue
		primaryQuery( "insert into issues ($fields) values ($values)" );
		
		// add the watchers
		foreach( $watchers as $watcher ) {
			if( $watcher ) { // this 'if' statement keeps the extra comma in the field from giving us an extra blank ID
				secondaryQuery( "insert into issuewatch (issueid, userid) values (".$row['ID'].", '".mysql_escape_string( $watcher )."')" );
			}
		}
	}
	
	// get data from contact table
	$query = "select * from contacts";
	$result = mysql_query( $query );
	while( $row = mysql_fetch_assoc( $result ) ) {
		
		$students = explode( ',', $row['Students'] );
		unset( $row['Students'] );
		$creator = $row['Creator'];
		
		// put each value in 'single quotes' or set it to null;
		foreach( $row as $field => $value ) {
			if( $value ) $row[$field] = "'".mysql_escape_string( $value )."'";
			else $row[$field] = "NULL";
		}
		
		// make the field names and values into strings for SQL use
		$fields = implode( ', ', array_keys( $row ) );
		$values = implode( ', ', $row );
		
		// create the contact
		primaryQuery( "insert into contacts ($fields) values ($values)" );
		
		// add the creator
		secondaryQuery( "insert into `contacts-users` (contactid, userid) values (".$row['ID'].", '".mysql_escape_string( $creator )."')" );
		
		// add the watchers
		foreach( $students as $student ) {
			if( $student ) { // this 'if' statement keeps the extra comma in the field from giving us an extra blank ID
				secondaryQuery( "insert into `contacts-students` (contactid, studentid) values (".$row['ID'].", '".mysql_escape_string( $student )."')" );
			}
		}
	}
	
	///////////////////////////////////////////////////////////////////////////////
	// done retrieving data from the old database and creating queries
	///////////////////////////////////////////////////////////////////////////////
	
	mysql_close();
}

function executeQueries( $host, $username, $password, $dbname ) {
	mysql_connect( $host, $username, $password );
	mysql_select_db( $dbname );
	
	global $primary, $secondary;
	foreach( $primary as $query ) {
		//echo $query."<br>";
		mysql_query( $query );
		if( $error = mysql_error() ) {
			echo $query."<br><br>";
			echo $error."<br><br><hr>";
		}
	}
	foreach( $secondary as $query ) {
		//echo $query."<br>";
		mysql_query( $query );
		if( $error = mysql_error() ) {
			echo $query."<br><br>";
			echo $error."<br><br><hr>";
		}
	}
	
	mysql_close();
}



?>
