<?php

/**
 * Um, there are four of this same file... At least, I think they're the same... ?
 */

class MyDate {
	
	var $integerTimestamp;
	
	function MyDate( $strGnuSyntax ) {
		$this->integerTimestamp = strtotime( $strGnuSyntax );
	}
	
	function timestamp() {
		return $this->integerTimestamp;
	}
	
	// returns the value of $this - $MyDate_otherDate in seconds
	// Fixed error in return statement that had a typo in the variable name - Josh Thomas
	function subtract( $MyDate_otherDate ) {
		return $this->integerTimestamp - $MyDate_otherDate->timestamp();
	}
	
	function phpDate( $strPhpDateFormat ) {
		return date( $strPhpDateFormat, $this->integerTimestamp );
	}
	
	function mySqlDate() {
		return date( 'Y-m-d', $this->integerTimestamp );
	}
	
	function mySqlTime() {
		return date( 'H:i:s', $this->integerTimestamp );
	}
	
	function mySqlDateTime() {
		return date( 'Y-m-d H:i:s', $this->integerTimestamp );
	}
	
	function mySqlTimestamp() {
		return date( 'YmdHis', $this->integerTimestamp );
	}
	
	function humanDateText() {
		return date( 'F j, Y', $this->integerTimestamp );
	}
	
	function humanDateNumerical() {
		return date( 'm/d/Y', $this->integerTimestamp );
	}
	
	function humanTime12Hour() {
		return date( 'g:i A', $this->integerTimestamp );
	}
	
	function humanTime24Hour(){
		return date( 'H:i', $this->integerTimestamp );
	}
	
	function year() {
		return date( 'Y', $this->integerTimestamp );
	}
	
	function month() {
		return date( 'm', $this->integerTimestamp );
	}
	
	function dayOfMonth() {
		return date( 'd', $this->integerTimestamp );
	}
	
	// numerical, 0 (Sunday) through 6 (Saturday)
	function dayOfWeek() {
		return date( 'w', $this->integerTimestamp );
	}
	
	// returns a MyDate object
	function begginingOfDay() {
		return new MyDate( date( 'F j, Y 00:00:00', $this->integerTimestamp ) );
	}
	
	// returns a MyDate object
	function firstDayOfMonth() {
		return new MyDate( date( 'F 1, Y H:i:s', $this->integerTimestamp ) );
	}
	
	// returns a MyDate object
	function lastDayOfMonth() {
		return new MyDate( date( 'F t, Y H:i:s', $this->integerTimestamp ) );
	}
	
	// 24-hour format with leading 0's
	function hour() {
		return date( 'H', $this->integerTimestamp );
	}
	
	function minute() {
		return date( 'i', $this->integerTimestamp );
	}
	
	function second() {
		return date( 's', $this->integerTimestamp );
	}
	
	// returns a MyDate object
	// be careful; adding months can get fuzzy and daylight savings time is a pain
	function addTime( $intSeconds = 0, $intMinutes = 0, $intHours = 0, $intDays = 0, $intWeeks = 0,
			  $intMonths = 0, $intYears = 0 ) {
		
		$gnuSyntax  = "@".$this->integerTimestamp;
		$gnuSyntax .= " + $intYears years";
		$gnuSyntax .= " + $intMonths months";
		$gnuSyntax .= " + $intWeeks weeks";
		$gnuSyntax .= " + $intDays days";
		$gnuSyntax .= " + $intHours hours";
		$gnuSyntax .= " + $intMinutes minutes";
		$gnuSyntax .= " + $intSeconds seconds";
		
		return new MyDate( $gnuSyntax );
	}
	
	// returns a MyDate object
	function nextDay() {
		return new MyDate( date( 'F j, Y H:i:s', $this->integerTimestamp )." + 1 day" );
	}
	
	// returns a MyDate object
	function previousDay() {
		return new MyDate( date( 'F j, Y H:i:s', $this->integerTimestamp )." - 1 day" );
	}
	
	// returns a MyDate object
	function nextWeek() {
		return new MyDate( date( 'F j, Y H:i:s', $this->integerTimestamp )." + 1 week" );
	}
	
	// returns a MyDate object
	function previousWeek() {
		return new MyDate( date( 'F j, Y H:i:s', $this->integerTimestamp )." - 1 week" );
	}
}

?>
