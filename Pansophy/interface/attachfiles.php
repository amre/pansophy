<?php

/** 
 * page for attaching a file to a contact.
 * Robert
 */

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
$dam = new DataAccessManager();

include('../include/filescript.inc');

// retrieve variables
$IssueID = $_GET['issueid'];
$ContactID = $_GET['contactid'];
$type = '';	//declare variable for file type
if(isset($_POST['filetype'])) $type = $_POST['filetype'];

// attach a new file
if(strcmp($_POST['submit'], 'Attach') == 0){
	// give issue level a numeric value
	if($type=='admissions')	$type=1;
	else	$type=0;
	if(!$dam->attachFile($ContactID, $type)){	
	}
}

// finished - lets get the hizzy on outta her
else if(strcmp($_POST['submit'], 'Done') == 0){
	echo '<meta http-equiv="Refresh" content="0; URL=./viewissue.php?id='.$IssueID.'">';
}

// default action when nothing is selected
else{
}


// print upload form

echo '<form enctype="multipart/form-data" action="attachfiles.php?issueid='.$IssueID.'&contactid='.$ContactID.'" method="POST">
<input type="hidden" name="MAX_FILE_SIZE" value="10000000"/>';
echo '<table width="40%" >';
echo '<tr><td><b>Attachment Type:</b></td><td>';
if(strcmp($type,'admissions')==0){
	echo '<input type="radio" name="filetype" value="admissions" CHECKED>Admissions
	<input type="radio" name="filetype" value="normal">Normal';
}
else{
	echo '<input type="radio" name="filetype" value="admissions">Admissions
	<input type="radio" name="filetype" value="normal" CHECKED>Normal';
}
echo '</td></tr><tr><td height="10"></td></tr>';
echo '<tr><td><b>Attachment(s):</b></td><td> <input name="userfile" type="file"/></td>
<td align="left"><input type="submit" name="submit" value="Attach"/></td></tr>';
echo '<tr><td height="5"></td></tr>';

// show attached files
$files = $dam->viewAttachedFiles('',$ContactID);
for($j=0; $j<count($files); $j++){		
	// get and print filename
	$filename = $dam->getAttachedFileName($files[$j]);
	$admissionsfile= $dam->getAttachedFileType($files[$j]);
	echo '<tr><td></td><td>'.$filename.'</td>';
	if($admissionsfile)
		echo '<td><font color="mediumblue"> Type: Admissions </font></td>';
	else
		echo '<td><font color="mediumblue"> Type: Normal </font></td>';
	echo '<td><a href="./attachfiles.php?issueid='.$IssueID.'&contactid='.$ContactID.'&fileop=delete&fileid='.$files[$j].'">[Delete]</a>
	</td></tr>';
}
echo '<tr><td height="5"></td></tr>';	
//Josh Thomas


// finish form
echo '<tr><td><input type="submit" name="submit" value="Done"/></td></tr>';	
echo '</table>';	
echo '</form>';

// close html tags from header
echo '</body>';
echo '</html>';
	
?>
