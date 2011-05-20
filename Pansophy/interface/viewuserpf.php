<?php 

/**
 * Printer-friendly version of viewuser without the links or the other frames.
 */

//+-----------------------------------------------------------------------------------------+

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');
$dam = new DataAccessManager();

//+-----------------------------------------------------------------------------------------+

$user = $dam->viewUser('', $_GET['id']);
$keys = array_keys($user);
//Make variable names the same as fields in database.  I love variable variables.
for($i=0; $i<count($keys); $i++){
	$$keys[$i]=$user[$keys[$i]];
}

// for minimizing and maximizing issues/contacts displayed
$viewAllIssues = false;
$viewAllContacts = false;
if(isset($_GET['viewallissues']) && $_GET['viewallissues']) $viewAllIssues = true;
if(isset($_GET['viewallcontacts']) && $_GET['viewallcontacts']) $viewAllContacts = true;

//Display all the data, with nested tables.
echo '<h1>'.$FirstName.' '.$MiddleIn.' '.$LastName.' - '.$ID.'.'.$Context1.'.'.$Context2.'</h1><p>';
echo '<table width="100%" ><tr><td valign="top" width="45%">';
   echo '<table><tr><td>';

	   echo '<table  cellspacing="3"><tr>';
		   echo '<td nowrap valign="center"><p class="largeheading">User Information</p></td>';
		   if($dam->userCanModifyUser('', $ID))
	         echo '<td nowrap valign="center"><a href="./edituser.php?id='.$ID.'"><b>[Edit this user]</b></a></td>';
		   if($dam->userCanDeleteUser('', $ID))
	         echo '<td nowrap valign="center"><a href="./deleteuser.php?id='.$ID.'"><b>[Delete this user]</b></a></td>';   
      echo	'</tr></table>';
   
	   echo '<table  cellspacing="5" cellpadding="4">
			   <tr><td align="left" nowrap>Name: </td><td align="left">'.$LastName.', '.$FirstName.' '.$MiddleIn.'</td></tr>
			   <tr><td align="left" nowrap>Email: </td><td align="left"><a href="mailto:'.$Email.'">'.$Email.'</td></tr>
			   <tr><td align="left" nowrap>Extension: </td><td align="left">'.$Extension.'</td></tr>';
	   echo '</table>';
	
   echo '</td></tr>';
   echo '<tr><td>';

      // issues section
      echo '<p><p><table cellspacing="3"><tr><td nowrap><p class="largeheading">Issue History</p></td>';
         if($viewAllIssues) echo '<td nowrap><a href="./viewuserpf.php?id='.$ID.'&viewallissues=0&viewallcontacts='.$viewAllContacts.'"><b>[Reduce]</b></a>';
         else echo '<td nowrap><a href="./viewuserpf.php?id='.$ID.'&viewallissues=1&viewallcontacts='.$viewAllContacts.'"><b>[View All]</b></a>';
         echo '</td></tr>';
      echo '</table>';
      include( './userissues.php' );
      // end issue section

   echo '</td></tr>';
   echo '</table>';

   echo '</td>';
   echo '<td rowspan="2" valign="top">';

      // contacts section
		echo '<p><p><table cellspacing="3"><tr><td nowrap><p class="largeheading">Contact History</p></td>';
         if($viewAllContacts) echo '<td nowrap><a href="./viewuserpf.php?id='.$ID.'&viewallcontacts=0&viewallissues='.$viewAllIssues.'"><b>[Reduce]</b></a>';
         else echo '<td nowrap><a href="./viewuserpf.php?id='.$ID.'&viewallcontacts=1&viewallissues='.$viewAllIssues.'"><b>[View All]</b></a>';
         echo '</td></tr>';
      echo '</table>';
		include( './usercontacts.php' );
      // end contacts section

   echo '</td></tr></table>';
echo '</body></html>';

mysql_close();
?>

