<?php 

/**
 * Page that makes the final call to delete a user from the database
 */

include('../include/header.inc'); 
include('../DataAccessManager.inc.php');

$dam = new DataAccessManager();

// retrieve user id
if(empty($_POST['ID'])){
	$ID=$_GET['id'];
}
else{
	$ID = $_POST['ID'];
}

// get user info
$user=$dam->viewUser('', $ID);
@$keys = array_keys($user);
for($i=0; $i<sizeof($keys); $i++){
	$$keys[$i]=$user[$keys[$i]];
}

// display confirmation buttons
if(strcmp($_POST['submit'], 'Yes') == 0){
	$dam->deleteUser('', $ID);
	echo '<meta http-equiv="Refresh" content="0; URL=../main.php">';
}
else if(strcmp($_POST['submit'], 'No') == 0){
	echo '<meta http-equiv="Refresh" content="0; URL=./viewuser.php?id='.$ID.'">';
}
else{
	echo "Do you really want to delete $FirstName $LastName from the system?";
	echo '<form action="./deleteuser.php" method="POST" target="_self">
		<input type="hidden" name="ID" value="'.$ID.'">
		<input type="submit" name="submit" value="Yes"> <input type="submit" name="submit" value="No">
	</form>';
}

?>
