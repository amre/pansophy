<?php

/**
 * brings up file download dialog for the selected file.
 * this download script is called by filescript.inc when a user indicates 
 * a specific file to download.
 */
 
include('../DataAccessManager.inc.php');

$dam = new DataAccessManager();

// get file info from db
$fileID = $_GET['fileid'];
$fileName = $dam->getAttachedFileName($fileID);
$fileExt = $dam->getAttachedFileExt($fileID);	

$path = $file_upload_folder.$fileID; // file_upload_folder variable declared in DataAccessManager
if(file_exists($path)){
   if ($fd = fopen ($path, "r")) {
	   $fsize = filesize($path);
	   switch ($fileExt) {
		   case "pdf":
		   header("Content-type: application/pdf"); // add here more headers for diff. extensions
		   header("Content-Disposition: attachment; filename=\"".$fileName."\""); // use 'attachement' to force a download
		   break;
		   default;
		   header("Content-type: application/octet-stream");
		   header("Content-Disposition: filename=\"".$fileName."\"");
	   }
	   header("Content-length: $fsize");
	   header("Cache-control: private"); //use this to open files directly
	   while(!feof($fd)) {
		   $buffer = fread($fd, 2048);
		   echo $buffer;
	   }
   }
   fclose ($fd);
}
exit;
	
?>
