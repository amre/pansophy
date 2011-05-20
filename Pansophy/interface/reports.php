<?php

/**
 * Page that directs user to different kinds of report generators.
 */


include_once( "../include/header.inc" );
include_once( "../DataAccessManager.inc.php" );

$dam = new DataAccessManager();

echo "<center>";

$savedreports = $dam->getSavedReportsForSelf();
//print_r( $savedreports );
$issuereports = array();
$studentreports = array();
$userreports = array();
$interimreports = array();
$fwreports = array();
foreach( $savedreports as $report ) {
	extract( $report );
	
	if( $Type == 'issue' ) $issuereports[$Name] = $GetLine;
	else if( $Type == 'student' ) $studentreports[$Name] = $GetLine;
	else if( $Type == 'user' ) $userreports[$Name] = $GetLine;
   else if( $Type == 'interim' ) $interimreports[$Name] = $GetLine;
   else if( $Type == 'firstwatch' ) $fwreports[$Name] = $GetLine;
}




echo "
<h2>Report Generator</h2>
<table class='savedreports'>
  <tr>
    <th>
      Issue Reports
    </th>
    <th>
      Student Reports
    </th>";
  if($dam->canViewUsers('')){
   echo"
    <th>
      User Reports
    </th>";
  }
  if($dam->userCanViewInterim('')){
   echo"
    <th>
      Interim Reports
    </th>";
  }
  if($dam->userCanViewFW('')){
   echo"
    <th>
      First Watch Reports
    </th>";
  }
  echo"
  </tr>
  <tr>
    <td>
      <a href='issuereports.php'>[Create New]</a><br/><br/>";
foreach( $issuereports as $name => $getline ) {
	echo "
	<a href='./issuereports.php$getline'>$name</a><br>";
}
echo "
    </td>
    <td>
      <a href='studentreports.php'>[Create New]</a><br/><br/>";
foreach( $studentreports as $name => $getline ) {
	echo "
	<a href='./studentreports.php$getline'>$name</a><br>";
}
if($dam->canViewUsers('')){
	echo "
		</td>
		<td>
		  <a href='userreports.php'>[Create New]</a><br/><br/>";
	foreach( $userreports as $name => $getline ) {
		echo "
		<a href='./userreports.php$getline'>$name</a><br>";
	}
}
if($dam->userCanViewInterim('')){
	echo "
		</td>
		<td>
		  <a href='interimreports.php'>[Create New]</a><br/><br/>";
	foreach( $interimreports as $name => $getline ) {
		echo "
		<a href='./interimreports.php$getline'>$name</a><br>";
	}
}
if($dam->userCanViewFW('')){
	echo "
		</td>
		<td>
		  <a href='fwreports.php'>[Create New]</a><br/><br/>";
	foreach( $fwreports as $name => $getline ) {
		echo "
		<a href='./fwreports.php$getline'>$name</a><br>";
	}
}
echo "
    </td>
  </tr>
</table>
";


echo "</center>";

?>
