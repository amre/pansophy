<?php

//*****MAGIC_QUOTES*****//
/*
$magic_gpc = get_magic_quotes_gpc();
$magic_runtime = get_magic_quotes_runtime();
$magic_sybase = ini_get ('magic_quotes_sybase');

if($magic_gpc) echo 'magic_quotes_gpc on';
else echo 'magic_quotes_gpc off';
echo '<br>';
if($magic_runtime) echo 'magic_quotes_runtime on';
else echo 'magic_quotes_runtime off';
echo '<br>';
if($magic_sybase) echo 'magic_quotes_sybase on';
else echo 'magic_quotes_sybase off';
*/

// be REALLY careful with this magic_quotes stuff, turning them
// on and off can cause many unexpected problems...
//ini_set('magic_quotes_sybase', FALSE); 
//*********************//


require_once( './MyDate.inc.php' );

//returns the date in m/d/y format
function readableDate( $date ) {
	$mydate = new MyDate( $date );
	return $mydate->humanDateNumerical();
}

//returns the date and time as "m/d/y at xx:xx"
function readableDateAndTime( $date ) {
	$mydate = new MyDate( $date );
	$return  = $mydate->humanDateNumerical();
	$return .= ' at ';
	$return .= $mydate->humanTime12Hour();
	return $return;
}

// returns the date in sql format "Y-m-d H:i:s"
function sqlDate( $date )
{
   if(empty($date)) $date = date('Y-m-d H:i:s');
   else{
      $date = strtotime($date);
      if($date !== false) $date = date('Y-m-d H:i:s',$date);
   }
   return $date;
}

/**this function prints out a <select> with all the users passed to in in $userArray.
* this is good because we're lazy and don't like writing the loops to do it over and over again
* because the deans like having a huge list of users to select for various things.
**/
function printUserSelect( $userArray, $inputName, $labelForBlankValue, $method = 'request' ) {
	
	if( strtolower( $method ) == 'get' ) $inputPreviousValue = $_GET[$inputname];
	else if( strtolower( $method ) == 'post' ) $inputPreviousValue = $_POST[$inputname];
	else $inputPreviousValue = $_REQUEST[$inputname];
	
	echo "
		<select name='$inputName'>
			<option value=''>$labelForBlankValue</option>";
	foreach( $userArray as $user ) {
		extract( $user );
		
		$selected = "";
		if( $inputPreviousValue == $ID ) $selected = " selected";
		
		echo "
			<option value='$ID'$selected>$Label</option>";
	}
	echo "
				</select>
			</td>
		</tr>";
}

function verifyLoggedIn() {
	if( !$_SESSION['userid'] ) {
		include( 'newlogin.php' );
		exit;
	}
}

/* Stripslashes method to remove all of those pesky backslashes that get added to text
 * when fields are updated to the SQL database. Used currently any time that the Header field
 * from `issues` appears, the Description field from `contacts` appears, and the RedFlag or VIP
 * fields from `students` appear. - Josh Thomas
 *   
 * How many times does "Josh Thomas" appear in the
 * code? Good luck in your quest... Hint: There are more than 10.
 * Also, using find is CHEATING!
 */

function stripslashes_all( $field ) {
    do {
         $field = stripslashes($field);
    }
    while (strstr($field, '\\') !== FALSE);
    return $field;
}

/**
 * Converts X_PNSY field names to look less gross
 * (PS: "bonny" = "pretty" in Scotland - think "bonny lass")
 *
 * @param $originalField field name to make look nice
 *
 * @return field name with first letters of words capitalized
 */
 function bonnyFieldName( $originalField ) {
 	$lowerCase = strtolower($originalField);
 	$_toSpace = str_replace("_", " ", $lowerCase);
 	$bonnyField = ucwords($_toSpace);
 	
 	return $bonnyField;
 }
 
/**
 * Scans the description field of a contact for an interim report or issue id.
 * If there is one, then the text is returned with a link. Else, the untouched text is returned.
 */
function scanTextForLinks($text)
{
   // scan for interims
   $interimRegEx = "/R[0-9]{8}\-[0-9]{1,}/";
   if(preg_match_all($interimRegEx,$text,$interims)){   
      $interims = $interims[0];
      $offset = 0;
      for($i = 0; $i < count($interims); $i++){
         // check to see if id exists in database
         $query = "select ID from interims where ID = '$interims[$i]'";
         $result = mysql_query($query);
         if(mysql_num_rows($result) > 0){
            // replace the id with a link
            $index = strpos($text,$interims[$i],$offset);
            if($index !== false){
               $link = '<a href="./viewinterim.php?id='.$interims[$i].'" TARGET="Main">'.$interims[$i].'</a>';
               $text = substr_replace($text,$link,$index,strlen($interims[$i]));
               $offset = $index + strlen($link);
            }
         }
      }
   }

   // scan for issues
   $issueRegEx = "/I[0-9]{8}\-[0-9]{1,}/";
   if(preg_match_all($issueRegEx,$text,$issues)){   
      $issues = $issues[0];
      $offset = 0;
      for($i = 0; $i < count($issues); $i++){
         // check to see if id exists in database
         $query = "select ID from issues where ID = '$issues[$i]'";
         $result = mysql_query($query);
         if(mysql_num_rows($result) > 0){
            // replace the id with a link
            $index = strpos($text,$issues[$i],$offset);
            if($index !== false){
               $link = '<a href="./viewissue.php?id='.$issues[$i].'" TARGET="Main">'.$issues[$i].'</a>';
               $text = substr_replace($text,$link,$index,strlen($issues[$i]));
               $offset = $index + strlen($link);
            }      
         }
      }
   }

   return $text;
}

?>
