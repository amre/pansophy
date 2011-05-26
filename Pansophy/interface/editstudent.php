<?php 

/**
 * Page to edit a specific students flags, VIP, redflags, etc.
 */

//+-----------------------------------------------------------------------------------------+ 
include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
include('../include/miscfunctions.inc.php');
$dam = new DataAccessManager();
//+-----------------------------------------------------------------------------------------+ 

// retrieve passed variables
if(isset($_GET['studentId'])) $studentId = $_GET['studentId'];
else $studentId = $_POST['studentId'];

// actions taken if submitted
if(!isset($_POST['submit'])){	
	$student = $dam->viewStudent('',$studentId);
	$redflag = $student['RedFlag'];
	$vip = $student['VIP'];
	$acpro = $student['AcProbation'];
	$hwl = $student['HousingWaitList'];
	$f1 = $student['Field1'];
	$f2 = $student['Field2'];
	$f3 = $student['Field3'];

	if($acpro == 1) $acpro = 'checked';
	if($hwl == 1) $hwl = 'checked';
	if($f1 == 1) $f1 = 'checked';
	if($f2 == 1) $f2 = 'checked';
	if($f3 == 1) $f3 = 'checked';
}
else if(strcmp($_POST['submit'], 'Cancel') == 0){
	echo '<meta http-equiv="Refresh" content="0; URL=./viewstudent.php?id='.$studentId.'">';
	exit();
}
else if(strcmp($_POST['submit'], 'Submit Changes') == 0){
	$redflag = $_POST['redflag'];
	$vip = $_POST['vip'];
	$acpro = 0;	
	$hwl = 0;
	$f1 = 0;
	$f2 = 0;
	$f3 = 0;

	if(isset($_POST['acpro']) && strcmp($_POST['acpro'],'on') == 0) $acpro = 1;
	if(isset($_POST['hwl']) && strcmp($_POST['hwl'],'on') == 0) $hwl = 1;
	if(isset($_POST['f1']) && strcmp($_POST['f1'],'on') == 0) $f1 = 1;
	if(isset($_POST['f2']) && strcmp($_POST['f2'],'on') == 0) $f2 = 1;
	if(isset($_POST['f3']) && strcmp($_POST['f3'],'on') == 0) $f3 = 1;

   // counteract the mischief done by magic_quotes
   if(get_magic_quotes_gpc()){
      $redflag = stripslashes($redflag);
      $vip = stripslashes($vip);
   }


	$edits['RedFlag'] = $redflag;
	$edits['VIP'] = $vip;
	$edits['AcProbation'] = $acpro;
	$edits['HousingWaitList'] = $hwl;
	$edits['Field1'] = $f1;
	$edits['Field2'] = $f2;
	$edits['Field3'] = $f3;

	// make modifications and go back to student page
	$dam->modifyStudent('',$studentId,$edits);
	echo '<meta http-equiv="Refresh" content="0; URL=./viewstudent.php?id='.$studentId.'">';
	exit();
}

//+-----------------------------------------------------------------------------------------+ 



// title section
echo '<center><p class="largecolorheading">Edit Student '.$studentId.' - '.$student['FIRST_NAME'].' '.$student['MIDDLE_NAME'].' '.$student['LAST_NAME'].'</p></center>';

// start form
echo '<form action="./editstudent.php?id='.$studentId.'" method="POST">';

// set hidden variables passed via post
echo '<input type="hidden" name="studentId" value="'.$studentId.'">';

// start table
echo '<table  cellspacing="5" cellpadding="4">';
	
	// red flag and vip text boxes	
	echo '<tr><td valign="top" align="left" nowrap>RedFlag: </td><td align="left"><TEXTAREA name="redflag" cols="40" rows="5">'.$redflag.'</TEXTAREA></td></tr>
	<tr><td valign="top" align="left" nowrap>VIP: </td><td align="left"><TEXTAREA name="vip" cols="40" rows="5">'.$vip.'</TEXTAREA></td></tr>';

	// acpro and housing waitlist flags
	echo '<tr><td valign="top" align="left" nowrap>Academic Probation: </td><td align="left">';
	echo '<input type="checkbox" name="acpro" '.$acpro.'>';
	echo '</td></tr>
	<tr><td valign="top" align="left" nowrap>Housing Waitlist: </td><td align="left">';
	echo '<input type="checkbox" name="hwl" '.$hwl.'>';
	echo '</td></tr>';

	// other user modified flags
	$flags = $dam->extractFlags();
	
	$flag = $flags['Option1'];
	if(!empty($flag)) {
		echo '<tr><td valign="top" align="left" nowrap>'.$flag.': </td><td align="left">';
		echo '<input type="checkbox" name="f1" '.$f1.'>';
		echo '</td></tr>';
	}
	$flag = $flags['Option2'];
	if(!empty($flag)) {
		echo '<tr><td valign="top" align="left" nowrap>'.$flag.': </td><td align="left">';
		echo '<input type="checkbox" name="f2" '.$f2.'>';
		echo '</td></tr>';
	}
	$flag = $flags['Option3'];
	if(!empty($flag)) {
		echo '<tr><td valign="top" align="left" nowrap>'.$flag.': </td><td align="left">';
		echo '<input type="checkbox" name="f3" '.$f3.'>';
		echo '</td></tr>';
	}
	

// end table
echo '<tr><td height="10"></td><td></td></tr></table>';
	
// submit/cancel buttons
echo '<input type="submit" name="submit" value="Cancel"> <input type="submit" name="submit" value="Submit Changes">';

// end form
echo '</form>';

echo '</body></html>';
?>
