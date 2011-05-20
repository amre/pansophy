<?php
/**
 * includes two important operations for dealing with file links. the first is for when a 
 * user clicks a link to delete a file from the server folder and database. the second is
 * for a link to download the file.
 */

//+-----------------------------------------------------------------------------------------+
if(isset($_GET['fileop'])) $fileOp = $_GET['fileop'];// file operation
else $fileOp = "";
if(isset($_GET['fileid'])) $fileID = $_GET['fileid'];// which file we are dealing with
else $fileID = "";
//+-----------------------------------------------------------------------------------------+

// if we want to download the file
if(strcmp($fileOp,'download') == 0){
	// call download script
	echo '<meta http-equiv="Refresh" content="0; URL=./downloadfile.php?fileid='.$fileID.'">';
}
// die file die!!!
else if(strcmp($fileOp,'delete') == 0){
	if(!$dam->deleteFile($fileID)){
	}
}
// if no command given, do nothing
else{
}
?>
