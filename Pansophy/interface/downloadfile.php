<?php

/**
* When a user requests a file download, the file is copied into a folder with a randomly generated name in a public area of the 
* site. The user is then provided with a direct download link to the copied folder. After 30 minutes, the copied file is
* considered "expired" and marked for deletion.
 */
 
include('../DataAccessManager.inc.php');

$dam = new DataAccessManager();

// get file info from db
$fileID = $_GET['fileid'];
$fileName = $dam->getAttachedFileName($fileID);
$fileExt = $dam->getAttachedFileExt($fileID);	

$path = $file_upload_folder.$fileID; // file_upload_folder variable declared in DataAccessManager
if(file_exists($path)){
/* Robert's old attachment management code
	if ($fd = fopen ($path, "r")) {
	   $fsize = filesize($path);
	   switch ($fileExt) {
		   case "pdf":
		   header("Content-type: application/pdf"); // add here more headers for diff. extensions
		   header("Content-Disposition: attachment; filename=\"".$fileName."\""); // use 'attachement' to force a download
		   break;
		   default:
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
   fclose ($fd);*/
	//clean up expired download requests
	$directorycleaner = glob("../tmp/*[!.]???");
	foreach ($directorycleaner as $n)
	{
		$timestamp = glob("$n/*[!.][!.]???");
		$realtimestamp = preg_replace("/(.*\/.*\/.*\/)(.*)/","$2",$timestamp[0]);
		if (time() - $realtimestamp > 600 || sizeof($timestamp) == 0)
		{ // If it's been 30 minutes or the timestamp is already deleted, then the file is expired.
			$delfiles = glob("$n/*");
			foreach ($delfiles as $m)
			{
				unlink("$m");
			}
			rmdir($n);
		}		
	}
	// create random folder name
	$usablechars="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghiklmnopqrstuv1234567890";
	$charlim=rand(8,20);
	$tmpfoldername="";
	for($i=1;$i<$charlim;$i++)
	{
		$tmpfoldername=$tmpfoldername.$usablechars[rand(0,strlen($usablechars))];
	}
	// copy desired folder
	if (mkdir("../tmp/$tmpfoldername"))
	{
		copy($path.'/'.$fileName,"../tmp/$tmpfoldername/$fileName");
		chmod("../tmp/$tmpfoldername",0777);
		chmod("../tmp/$tmpfoldername/$fileName",0777);
		$time = time();
		copy("../../checkout","../tmp/$tmpfoldername/$time");
		chmod("../tmp/$tmpfoldername/$time",0777);
		echo '<html><body><p>Click <a href="../tmp/'.$tmpfoldername.'/'.$fileName.'">here</a> for your file</p></body></html>';
	}
}
exit;
	
?>
