<?php
  function ErrorOut($Error) {
    die ($Error);
  }

session_start();


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


//connect to mysql
include('./DataAccessManager.inc.php');
$dam = new DataAccessManager();

//connect to the ftp server
  include ("./FTPVars.inc.php");
  $LocalDir = '.';
  $FTP = ftp_connect($FtpServer); 
  $LoginResult = ftp_login($FTP, $FtpUser, $FtpPassword);
  if ((!$FTP) || (!$LoginResult)) {
    ErrorOut("Could not connect to FTP Server ($FtpServer)");
  }

//Check if files are present
  $DirList = ftp_nlist($FTP, ".");
  $Needed = array('pansophy.dat');
  foreach($DirList as $FileName) {
    foreach($Needed as $Key => $NeededFile) {
      if ($NeededFile == $FileName) {
        unset($Needed[$Key]);

//Grab them
        ftp_get($FTP, $LocalDir.'/'.$FileName, $FileName, FTP_ASCII);
        if (!(file_exists($FileName) && is_readable($FileName))) {
          ErrorOut('The files could not be uploaded.');
        }
        $InFile = fopen($FileName, "r");
        flock($InFile, LOCK_EX);
	
//update student records
if($InFile){
	//echo 'Please wait while the database is updated ...';
	while( !feof( $InFile ) ){
		$row = fgets( $InFile );
		$studentRecord = parseRow( $row );
		$dam->createOrReplaceStudent( '', $studentRecord );
	}
	echo '<p><b>Data import complete.</b>';
}
else{
	echo 'ERROR: Unable to open file.';
}

//delete the source files
        fclose($InFile);
        unlink($FileName);
        if ($Error) {
          ErrorOut($Error);
        }
        ftp_delete($FTP, $FileName);
      }
    }
  }

//disconnect from the servers
  ftp_close($FTP);
  mysql_close($DB);

?>