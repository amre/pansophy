<?php

session_start();
include('./include/mainheader.inc'); include('./DataAccessManager.inc.php');
$dam = new DataAccessManager();

if( $dam->userCanCreateOrReplaceStudent( '' ) ) {
	
	if( $_POST[ 'Submit' ] ) {
		function parseRow( $string ) {
			$srnFields = explode( '|', $string );
			
			$Name = explode( ', ', trim( $srnFields[0] ) );
			$return[ 'LastName' ] = $Name[0];
			
			$FirstMiddle = explode( ' ', $Name[1] );
			$return[ 'FirstName' ] = $FirstMiddle[0];
			
			for( $i = 1; $i < sizeof( $FirstMiddle ); $i++ ) {
				$MiddleIn .= substr( $FirstMiddle[$i], 0, 1 );
			}
			$return[ 'MiddleIn' ] = trim( $MiddleIn );
			
			$return[ 'ClassYear' ] = trim( $srnFields[1] );
			$return[ 'ID' ] = trim( $srnFields[2] );
			$return[ 'Email' ] = trim( $srnFields[3] );
			$return[ 'CampusBox' ] = trim( $srnFields[4] );
			$return[ 'Extension' ] = trim( $srnFields[5] );
			
			if( $Address1 = trim( $srnFields[6] ) ) $Address1 .= ', ';
			$return[ 'Address1' ] = $Address1.trim( $srnFields[7] );
			
			$return[ 'Address2' ] = trim( $srnFields[8] ).', '.trim( $srnFields[9] ).' '.trim( $srnFields[10] ).', '.trim( $srnFields[11] );
			$return[ 'Advisor' ] = trim( $srnFields[12] );
			$return[ 'Status' ] = trim( $srnFields[13] );
			$return[ 'Ethnic' ] = trim( $srnFields[14] );
			
			if( $HomeAreaCode = trim( $srnFields[15] ) ) $HomeAreaCode = '('.$HomeAreaCode.')';
			$return[ 'HomePhone' ] = $HomeAreaCode.' '.$srnFields[16];
			//print_r( $return );
			//echo '<br><br>';
			
			return $return;
		}
		
		@$file = fopen( $_FILES[ 'SRNFile' ][ 'tmp_name' ], 'rb' );
		if($file){
			//echo 'Please wait while the database is updated ...';
			while( !feof( $file ) ){
				$row = fgets( $file );
				$studentRecord = parseRow( $row );
				$dam->createOrReplaceStudent( '', $studentRecord );
			}
			echo '<p><b>Data import complete.</b>';
		}
		else{
			echo 'ERROR: Unable to open file.';
		}
	}
	echo '<h1>Import SRN Student Records</h1><br>';
	echo '<p>This page does not work with Pansophy in its current state.<br />Because student information is
		received through Datatel, any information that would be imported through this interface would be erased
		during the next push from Datatel.</p>
		<p><form enctype="multipart/form-data" action="'.basename($_SERVER['PHP_SELF']).'" method="post">
			<b>Specify the file to import records from:</b><br><br>
			<input type="file" name="SRNFile"><br><br>
			<input type="Submit" value="Submit" name="Submit">
		</form></p>
		<p><br><br><a href="./interface/tasks.php">[Return to User Tasks]</a></p>
	';
	/* Fixed a display issue using the "Go Back to Admin Tasks" link from this page and removed
	 * the bold text to be consistent with other links. - Josh Thomas
	 */
}
else {
	echo 'ERROR: Access denied.';
}

?>
