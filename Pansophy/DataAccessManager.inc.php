<?php
if (file_exists('./logtimer.inc.php'))
{
include('./logtimer.inc.php');
}
else
{
include('../logtimer.inc.php');
}
/**
 * ACCESS LEVELS
 *
 * Level 10 - Administrator - [read/write/edit/delete A&B issues; modify users]
 * Level 9 - Priveleged user - [read/write/edit A&B issues]
 * Level 8 - First Watch - [read/write A&B issues]
 * Level 7 - Normal - [read/write B issues]
 * Level 5 - Read only access (Full) - [read A&B issues]
 * Level 4 - Read only access (Normal) - [read B issues]
 *
 * (Levels 1-3, 6 currently unused)
 *
 * Level 0 - No access; instead of deleting a user, one can simply set his/her access to 0,
 *	keeping the user information available for the future, but discontinuing that user's
 *	access
 */
define("ADMINISTRATOR", 10, true);
define("PRIVILEGED", 9, true);
define("FIRSTWATCH", 8, true);
define("NORMAL", 7, true);
define("READONLYFULL", 5, true);
define("READONLY", 4, true);
define("NOACCESS", 0, true);

//------target path for file uploads------------+
// note: this dir is requested from the 
// pansophy/interface folder, NOT pansophy/ ,
// in which this file resides
$file_upload_folder = '../../pansophy_uploads/';
//----------------------------------------------+



class DataAccessManager {
	
	var $link;
	var $dbname;
	var $success;
	
	/**
	 * Class constructor. Connects to the MySQL database.
	 */
	function DataAccessManager() {
		@session_start();
		//check for historical db request
		if ($_SESSION['historical'] == true)
		{
			$settingsfile = "./settingshistorical.php";
		}
		else
		{
			$settingsfile = "./settings.php";
		}
		//Assumes that if settings are not in current directory, they're one directory up.
		if( file_exists($settingsfile) ) {
			$file = fopen($settingsfile, 'rb');
		}
		else if( file_exists( '.'.$settingsfile) ) {
			$file = fopen('.'.$settingsfile, 'rb');
		}
		
		if($file){
			for($i = 0; !feof($file); $i++){
				$input = fgets($file);
				if (strpos($input, ':') !== FALSE){
					$input = explode(':', $input);
					$$input[0] = trim($input[1]);
				}
			}
		}
		else{
			$this->success = false;
			return;
		}
		$this->dbname = $name;
		fclose($file);
		@$this->link = mysql_connect($host, $user, $pass);
		if(!$this->link){
			echo "<br>Could not connect to database.  Please try again later.<br>";
			return;
		}
		mysql_select_db($name);
		$this->success = true;
	}
// modified to use less resources, probably works
	/**
	 * Returns the calling user's security access level.
	 *
	 * @return the calling user's security access level
	 */
	function getAccessLevel() {
		$userID = $_SESSION['userid'];
		//$browser = $_SESSION['browser'];
		//$operationSystem = $_SESSION['os'];
		//$ipAddress = $_SESSION['ip'];
		
		// THIS NEEDS TO BE CHANGED TO ACTUALLY CHECK SESSION DATA AT SOME POINT
		$table='users';
		if(empty($this->link)){
			echo "<br>Failed to connect to database.  Operation failed.<br>";
			exit;
		}
		//mysql_select_db('students');
		$query = "SELECT AccessLevel FROM $table WHERE ID = '$userID'";
		$result = mysql_query($query);
		$results = mysql_fetch_array( $result );
		//echo $results['AccessLevel'];
		return $results['AccessLevel'];
	}
	
	
	/**
	* Returns the specified user's access level.
	*
	* @param $user the uid for which to get the access level
	*
	* @return the user's access level
	*/
	function getUserAccessLevel( $user ) {
		$userID = $user;
		//$browser = $_SESSION['browser'];
		//$operationSystem = $_SESSION['os'];
		//$ipAddress = $_SESSION['ip'];
		
		// THIS NEEDS TO BE CHANGED TO ACTUALLY CHECK SESSION DATA AT SOME POINT
		$table='users';
		if(empty($this->link)){
			echo "<br>Failed to connect to database.  Operation failed.<br>";
			//return 'BLAH!!!';
			exit;
		}
		$query = "SELECT AccessLevel FROM $table WHERE ID = '$userID'";
		$result = mysql_query($query);
		$results = mysql_fetch_array( $result );
		return $results['AccessLevel'];
	}
	
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently the user must be an administrator
	 * to be able to view other users
	 *
	 * @param $sessionID session information like IP address to verify for security
	 */
	function canViewUsers( $sessionID ){
		return $this->getAccessLevel() == ADMINISTRATOR;
	}
	
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently the user must be an administrator
	 * or be the user him/herself
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $userID the ID of the user to view
	 */
	function userCanViewUser( $sessionID, $userID ) {
		$thisUserID = $_SESSION['userid'];
		if($thisUserID == $userID)
			return TRUE;
		else
			return $this->canViewUsers($sessionID);
	}
// ok
	/**
	 * Returns an associative array where the keys are names of fields in the user table
	 * of the database, and the values are the corresponding values of those fields.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $userID the ID of the user to view
	 */
	function viewUser( $sessionID, $userID ) {
		//@session_start();
		if( $this->userCanViewUser( $sessionID, $userID ) ) {
			$query="SELECT * FROM users WHERE ID = '$userID'";
			$result = mysql_query($query);
			return mysql_fetch_assoc($result);
		}
      		// if current user has insufficient access to view all of another user's info
      		// return only the other user's name and email
      		else if($this->getAccessLevel() != NOACCESS){
         		$query="SELECT `FirstName`, `LastName`, `Email` FROM `users` WHERE `ID` = '$userID'";
			$result = mysql_query($query);
			return mysql_fetch_assoc($result);
      		}
	}
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently the user must be an administrator.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 */
	function userCanCreateUser( $sessionID ) {
		return $this->getAccessLevel() == ADMINISTRATOR;
	}
// probably ok
	/**
	 * Creates a new system user in the database.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $userInfo an associative array in which the keys are field names in the user table
	 * and the values are the desired new values of these fields
	 */
	function createUser( $sessionID, $userInfo ) {
		if( $this->userCanCreateUser( $sessionID ) ) {
			array_pop($userInfo);
			$columns = array_keys( $userInfo );
			$values = array_values( $userInfo );
			for( $i = 0; $i < sizeof( $userInfo ); $i++ ) {
				$columns[$i] = '`'.$columns[$i].'`';
				$values[$i] = '"'.$values[$i].'"';
			}
			$columns = implode( ',', $columns );
			$values = implode( ',', $values );
			$query = "INSERT INTO `users` ( $columns ) VALUES ( $values )";
			$result = mysql_query( $query );
			return $result;
		}
	}
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently the user must be an administrator.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $username the ID of the user to modify
	 */
	function userCanModifyUser( $sessionID, $username ) {
		return $this->getAccessLevel() == ADMINISTRATOR;
	}
	
// WORKS BUT FIX SECURITY PROBLEM!!!!!!!
	/**
	 * Modifies a user's information in the database table.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param the ID of the user to modify
	 * @param $userInfo an associative array in which the keys are field names in the user table
	 * and the values are the desired new values of these fields
	 *
	 *		!!!!!!	NOTE:  CURRENTLY ANY USER WITH PERMISSION TO MODIFY USERS CAN CHANGE HIS OR HER ACCESS LEVEL.  THAT'S BAD  !!!!!!!!
	 */
	function modifyUser( $sessionID, $username, $userInfo ) {
		if( $this->userCanModifyUser( $sessionID, $username ) ) {
			array_pop($userInfo);
			$keys = array_keys($userInfo);
			$query = 'UPDATE users SET ';
			for ($i=0; $i<sizeof($userInfo); $i++){
				if ($i == 0){
					$query .= $keys[$i]."='".addslashes(htmlspecialchars($userInfo[$keys[$i]]))."'";
				}
				else{
					$query .= ', '.$keys[$i]."='".addslashes(htmlspecialchars($userInfo[$keys[$i]]))."'";
				}
			}
			$query .= "WHERE ID='".$username."'";
			mysql_query($query);
		}
	}
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently the user must be an administrator.
	 * Usually in the system a user is not deleted, but deprecated to access level 0 (no access).
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $username the name of the user to delete
	 */
	function userCanDeleteUser( $sessionID, $username ) {
		return $this->getAccessLevel() == ADMINISTRATOR;
	}
// ok
	/**
	 * Deletes a system user from the database if they have no associated issues or contacts.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $username the ID of the user to delete
	 */
	function deleteUser( $sessionID, $username ) {
		//@session_start();
		if( $this->userCanDeleteUser( $sessionID, $username ) ) {
			if($this->getUserIssues($ID,0) || $this->getUserContacts($ID,0)){
				return FALSE;
			}
			else{
				$query = "DELETE FROM `users` WHERE ID='".$username."'";
				//echo $query;
				return mysql_query( $query );
			}
		}
	}
// ok
	/**
	* Gets contacts the specified user has created.
	*
	* @param $userid the uid for the user to get contacts for
	* @param $recent flag indicating the we should only retrieve the most recent contacts
	* @return return the contacts as an array
	*/
	function getUserContacts( $userID, $recent ){

		$query = "SELECT `ID` FROM contacts 
               where `Creator` LIKE '%$userID%' || `Modifier` LIKE '%$userID%'
               ORDER BY DateCreated DESC";

      if($recent){
         //$now = date('Y-m-d H:i:s');
         $past = date('Y-m-d H:i:s', time() - (30*24*60*60)); // a month ago
		   $query = "SELECT `ID` FROM contacts 
                  where `DateCreated` >= '$past'
                  and (`Creator` LIKE '%$userID%' || `Modifier` LIKE '%$userID%') 
                  ORDER BY DateCreated DESC";
      }

		$result = mysql_query($query);
		for($i=0; $results = mysql_fetch_array($result); $i++){
			$IDs[$i]=$results['ID'];
		}
		return $IDs;
	}
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently no users have permission to make an unrestricted
	 * query to the database.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 */
	function userCanQueryDatabase( $sessionID ) {
		return FALSE;
	}
// ok
	/**
	* Queries the mySQL database
	*
	* @param $sessionID the current session ID
	* @param $query the SQL query to be sent to the database
	*
	* @return returns the results of the query
	*/
	function queryDatabase( $sessionID, $query ) {
		if( $this->userCanQueryDatabase( $sessionID ) ) {
			return mysql_query($query);
		}
	}
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not.  Currently any active user has access.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 */
	function userCanViewStudent( $sessionID ){
		return $this->getAccessLevel() != NOACCESS;
	}
// ok
	/**
	 * Returns an associative array where the keys are names of fields in the student table
	 * of the database, and the values are the corresponding values of those fields.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID the ID of the student to view
	 */
	function viewStudent( $sessionID, $studentID ) {
		if( $this->userCanViewStudent( $sessionID )){
         if(!empty($studentID)){
			   $this->verifyStudent($studentID);
			   $query="SELECT * FROM X_PNSY_STUDENT ss, students s WHERE ss.ID = '$studentID' AND ss.ID = s.StudentID";
			   $result = mysql_query($query);
			   return mysql_fetch_assoc($result);
         }
		}
	}

	/**
	 * Returns an associative array where the keys are names of fields in the X_PNSY_ADDRESS table
	 * of the database, and the values are the corresponding values of those fields.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $addressID the ID of the student's address to view
	 */
	function viewStudentAddress( $sessionID, $addressID ) {
		if( $this->userCanViewStudent( $sessionID )) {
			$query = "SELECT * FROM X_PNSY_ADDRESS WHERE ADDRESS_ID='$addressID'";
			$result = mysql_query($query);
			
			return mysql_fetch_assoc($result);
		}
	}
	
	/**
	 * Returns an associative array where the keys are names of fields in the X_PNSY_RELATIONSHIP table
	 * of the database, and the values are the corresponding values of those fields.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID the ID of the student
	 */
	function viewStudentRelationships( $sessionID, $studentID ) {
		if( $this->userCanViewStudent( $sessionID )) {
			$query = "SELECT * FROM X_PNSY_RELATIONSHIP WHERE ID_1 = '$studentID' OR ID_2 = '$studentID'";
			$result = mysql_query($query);

			$temp = array();

			while ($row = mysql_fetch_assoc($result)) {
				if($row['ID_1'] == $studentID) {
					array_push( $temp, $row['ID_2'] );
					array_push( $temp, $row['RELATIONSHIP'] );
				}
				if($row['ID_2'] == $studentID) {
					array_push( $temp, $row['ID_1'] );
					array_push( $temp, $row['RELATIONSHIP'] );
				}
			}	
			return $temp;
		}
	}
	
	/**
	 * Returns an associative array where the keys are names of fields in the X_PNSY_PARENT table
	 * of the database, and the values are the corresponding values of those fields.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $parentID the ID of the student's parent
	 */
	function viewStudentParent( $sessionID, $parentID ) {
		if( $this->userCanViewStudent( $sessionID )) {
			$query = "SELECT * FROM X_PNSY_PARENT WHERE ID = '$parentID'";
			$result = mysql_fetch_assoc(mysql_query($query));
			
			return $result;
		}
	}
	
	/**
	 * Returns an associative array where the keys are names of fields in the X_PNSY_FACULTY table
	 * of the database, and the values are the corresponding values of those fields.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID the ID of the student
	 */
	function viewStudentAdvisor( $sessionID, $studentID ) {
		if( $this->userCanViewStudent( $sessionID )) {
			$query = "SELECT ADVISOR FROM X_PNSY_STUDENT WHERE ID = '$studentID'";
			$result = mysql_fetch_assoc(mysql_query($query));
			extract($result);
		
			$query = "SELECT FIRST_NAME, LAST_NAME, WOOSTER_EMAIL FROM X_PNSY_FACULTY WHERE ID = '$ADVISOR'";
			$result = mysql_fetch_assoc(mysql_query($query));
			
			return $result;
		}
	}
	
	/**
	* Checks to determine if the current user has security access to get the
	* specified student's issues.
	*
	* @param $studentID
	*
	* @return true if the user can get the student's issues, false if not
	*/
	function userCanGetStudentIssues( $studentID ) {
		return $this->userCanViewStudent('');
	}
	
	
	/**
	* Gets the issues for the specified student.
	*
	* @param $studentid the ID of the student to get issues for
	*
	* @return the students issues in an array
	*/
	function getStudentIssues( $studentid ) {
		if( $this->userCanGetStudentIssues( $studentid ) ) {
			$query = "select distinct i.ID from issues i, contacts c, `contacts-students` cs
				  where cs.studentid = '$studentid'
				  and cs.contactid = c.id
				  and c.issue = i.id
              order by i.DateCreated desc";


			$result = mysql_query($query);
			$return = array();
			while( $row = mysql_fetch_assoc($result) ) {
				if( $this->userCanViewIssue('',$row['ID']) ) {
					array_push( $return, $this->viewIssue('',$row['ID']) );
				}
			}
			return $return;
		}
	}
	
	
	/**
	* Tells if the current user can get another user's issues based upon
	* the current user's security level.
	*
	* @param $userid the uid of the user for which you want to get issues
	*
	* @return true if the current user can get the specified user's issues, false if not
	*/
	function userCanGetUserIssues( $userid ) {
		return $this->userCanViewUser('',$userid);
	}
	
	
	/**
	* Gets issues the specified user has been involved in.
	*
	* @param $userid the uid for the user to get issues for
   	* @param $recent flag indicating the we should only retrieve the most recent issues
	* @return return the issues as an array
	*/
	function getUserIssues( $userid , $recent) {
		if( $this->userCanGetUserIssues( $userid ) ) {

		   $query = "select distinct i.ID from issues i, contacts c
              where c.Creator = '$userid'
			     and c.issue = i.id
              order by i.DateCreated desc";

         if($recent){
            //$now = date('Y-m-d H:i:s');
            $past = date('Y-m-d H:i:s', time() - (30*24*60*60)); // a month ago
			   $query = "select distinct i.ID from issues i, contacts c
				     where c.datecreated >= '$past'
                 and c.Creator = '$userid'
				     and c.issue = i.id
                 order by i.DateCreated desc";
         }

			$result = mysql_query($query);
			$return = array();
			while( $row = mysql_fetch_assoc($result) ) {
				if( $this->userCanViewIssue('',$row['ID']) ) {
					array_push( $return, $this->viewIssue('',$row['ID']) );
				}
			}
			return $return;
		}
	}
	
	
	
	
	
	
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID the ID of the student to watch
	 */
	function userCanWatchStudent( $sessionID, $studentID ) {
		return $this->userCanViewStudent( $sessionID );
	}
// FIXED FOR NEW DB
	/**
	 * Indicates whether or not the calling user is watching a particular student.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID the ID of the student to check
	 *
	 * @return true if the the user is watching the issue, false if not.
	 */
	function userIsWatchingStudent( $sessionID, $studentID ) {
		if(empty($this->link)){
			echo "Not connected to database. Unexpected error. Contact your system administrator.";
			exit;	
		}
		$user = $_SESSION['userid'];
		$query = "SELECT studentid FROM studentwatch where userid='$user' and studentid='$studentID'";
		$result = mysql_query($query);
		return mysql_num_rows($result) > 0;
	}
// FIXED FOR NEW DATABASE!!!!
	/**
	 * Make the calling user watch a particular student.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID the ID of the student to watch
	 */
	function watchStudent( $sessionID, $studentID ) {
		if( $this->userCanWatchStudent( $sessionID, $studentID ) && !( $this->userIsWatchingStudent( $sessionID, $studentID )) ) {
			if(empty($this->link)){
				echo "Not connected to database. Unexpected error. Contact your system administrator.";
				exit;
			}
			//$query = "SELECT UsersWatching FROM students WHERE StudentID='$studentID'";
			$user = $_SESSION['userid'];
			$query="insert into studentwatch (userid, studentid) values ('$user','$studentID')";
			mysql_query($query);
		}
	}
// FIXED FOR NEW DB
	/**
	 * Halts the calling user's watch of a particular student.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID the ID of the student to stop watching
	 */
	function stopWatchingStudent( $sessionID, $studentID ) {
		$user = $_SESSION['userid'];
		$query="delete from studentwatch where userid='$user' and studentid='$studentID'";
		mysql_query($query);
	}
	
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently they must be an Administrative or Privileged user.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 */
	function userCanModifyStudent( $sessionID){
		return $this->getAccessLevel() >= PRIVILEGED;
	}
// probably ok
	/**
	 * Modifies a student's record in the database table. ONLY affects information in the `students` table.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID the ID of the student to modify
	 * @param $studentInfo an associative array in which the keys are field names in the student table
	 * and the values are the desired new values of these fields
	 */
	function modifyStudent( $sessionID, $studentID, $studentInfo ) {
		if( $this->userCanModifyStudent( $sessionID ) ) {
			$keys = array_keys($studentInfo);
			$query = 'UPDATE students SET ';
			for ($i=0; $i<sizeof($studentInfo); $i++){
				if ($i == 0){
					$query .= $keys[$i]."='".addslashes(htmlspecialchars($studentInfo[$keys[$i]]))."'";
				}
				else{
					$query .= ', '.$keys[$i]."='".addslashes(htmlspecialchars($studentInfo[$keys[$i]]))."'";
				}
			}
			$query .= " ,Modifier='".$_SESSION['userid']."' WHERE StudentID='".$studentID."'";
			$result = mysql_query($query);
		}
	}
// ok
	/**
	* Determines if the current user can create/replace a student
	* Right now, this is an Administrative task.
	*
	* @param $sessionID the current session ID
	*
	* @return true if the user can, false if not
	*/
	function userCanCreateOrReplaceStudent( $sessionID ) {
		return $this->getAccessLevel() == ADMINISTRATOR;
	}
// ok

	/**
	* Creates or replaces a student.
	*
	* @param $sessionID the current session ID
	* @param $studentInfo an array of information about the student; uses the SQL field names
	*
	* @return the result of the SQL query
	*/
	// used to import from SRN
	function createOrReplaceStudent( $sessionID, $studentInfo ) {
		if( $this->userCanCreateOrReplaceStudent( $sessionID ) ) {
			$columns = array_keys( $studentInfo );
			$values = array_values( $studentInfo );
			for( $i = 0; $i < sizeof( $studentInfo ); $i++ ) {
				$columns[$i] = '`'.$columns[$i].'`';
				$values[$i] = '"'.$values[$i].'"';
			}
			$columns = implode( ',', $columns );
			$values = implode( ',', $values );
			$query = "REPLACE INTO `students` ( $columns ) VALUES ( $values )";
			$result = mysql_query( $query );
			return $result;
		}
	}
	/**
	 * Checks to see if student ID is in `students` table, and not just the X_PNSY_STUDENT table.
	 * If it is not there, then a default entry with the given ID is added. This function should
	 * be used whenever student IDs are used.
	 *
	 * @param student ID to check
	 * @return success or not
	 */
	function verifyStudent($studentID){
      if(!empty($studentID)){
		   // check X_PNSY_STUDENT table
		   $query = "select ID from `X_PNSY_STUDENT` where ID = '$studentID'";
		   $result = mysql_query($query);		
		   if (mysql_num_rows($result) == 0) return false;

		
		   // check students table
		   $query = "select StudentID from `students` where StudentID = '$studentID'";
		   $result = mysql_query($query);		
		   if (mysql_num_rows($result) != 0) return true;

		
		   // add student as a default entry
		   $query = "insert into students (StudentID) value ('$studentID')";
		   $result = mysql_query($query);	
		   return $result;
      }

      return false;
	}
	
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently deletion of students it not permitted. Not supported at this time.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID the ID of the student to delete
	 */
//	function userCanDeleteStudent( $sessionID, $studentID ) {
//		return FALSE;
//	}

// ok
	/**
	 * Deletes a student record from the database.  May break things that are associated
	 * with the student. Not supported at this time.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID the ID of the student to delete
	 */
//	function deleteStudent( $sessionID, $studentID ) {
//		if( $this->userCanDeleteStudent( $sessionID, $studentID ) ) {
//			$query = "DELETE FROM students WHERE ID='".$studentID."'";
//			return mysql_query($query);
//		}
//	}

// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not.  Currently the user must be active and cannot be read-only.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 */
	function userCanCreateIssue( $sessionID ) {
		$level = $this->getAccessLevel();
		return ($level == NORMAL || $level == FIRSTWATCH || $level == PRIVILEGED || $level == ADMINISTRATOR);
	}
// MIGHT WORK WITH NEW DB
	/**
	 * Creates a new issue in the database.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $Header header for the issue
	 * @param $Status status of the issue
	 * @param $DateCreated date the issue was created
	 * @param $Students students associated with the issue
	 * @param $Description first contact of the issue
	 * @param $watch who to watch the issue
	 * @param $Level sensitivity of the issue (read in as 1 or 2 for sensitive or normal)
	 * @param $Category category under which the issue falls
	 *
	 * @return the id of the new issue if issue is created, false if not
	 */
	function createIssue( $sessionID, $Header, $Status, $DateCreated, $Students, $Description, $watch, $Level, $Category ) {
		if( $this->userCanCreateIssue( $sessionID ) ) {
			$Creator =  $_SESSION['userid'];
			if(empty($this->link)){
				echo "Not connected to database  You must instantiate the DataAccessManager before performing
					database accesses.";
					exit;	
			}
			
			// begin hack to create ID number
			$IDs = array();
			$ID = date('m').date('d').date('Y').'-';
			$result = mysql_query("SELECT * FROM issues WHERE ID like '%$ID%'");
			for($i=0; $results = mysql_fetch_assoc($result); $i++){
				$IDs[$i] = $results['ID'];
			}
			$idnumber = mysql_num_rows($result) + 1;
			while(in_array('I'.$ID.$idnumber, $IDs)){
						$idnumber++;
			}
			$ID ='I'.$ID.$idnumber;
			// end hack to create ID number
			
			
			$Header = htmlspecialchars($Header);
			$Header = addslashes($Header);
			if($Level == 1)
				$Level = 'A';
			else
				$Level = 'B';
			$query = "insert into issues (id, header, creator, status, datecreated, Level, Category)
				  values ('$ID', '$Header', '$Creator', '$Status', '$DateCreated', '$Level', '$Category')";
			//echo $query; exit;
			mysql_query($query);
			$this->createContact($sessionID, $DateCreated, $Students, $Description, $ID, $watch);
			return $ID; //Yay, it works!
		}
		else {
			echo 'You do not have permission to create that issue.';
			return false; //Oh no, failure!
		}
	}
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently the user must be an active user
	 * to view a B issue and Read-Only(Full), First Watch, Privileged,
	 * or Administrator to view an A issue 
	 * (or be the creator of/assigned to the A issue).
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $issueID the ID of the issue to view
	 */
	function userCanViewIssue( $sessionID, $issueID ) {
	$arr = str_split($issueID);
		if($issueID == '')
			return FALSE;
		elseif($arr[0] != 'I'){
			return FALSE;
		}
		else{
			$query= "SELECT Level FROM issues WHERE ID = '$issueID'";
			$result = mysql_query($query);
			$value = mysql_fetch_array($result);
			extract($value);
			$query2 = "SELECT Creator FROM issues WHERE ID = '$issueID'";
			$result2 = mysql_query($query2);
			$value2 = mysql_fetch_array($result2);
			extract($value2);
			$query3 = "SELECT AssignedTo FROM issues WHERE ID = '$issueID'";
			$result3 = mysql_query($query3);
			$value3 = mysql_fetch_array($result3);
			extract($value3);
			$userID = $_SESSION['userid'];
			$userLevel = $this->getAccessLevel();
			if($Level == 'A')
				if($userLevel == READONLYFULL || $userLevel == FIRSTWATCH || $userLevel == PRIVILEGED || $userLevel == ADMINISTRATOR)
					return TRUE;
				elseif($Creator == $userID)
					return TRUE;
				elseif($AssignedTo == $userID)
					return TRUE;
				else
					return FALSE;
			elseif($Level == 'B')	
				return $userLevel != NOACCESS;
			else
				return FALSE;
		}
	}
// ok
	/**
	 * Returns an issue record from the database in array form.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $issueID the ID of the issue to view
	 *
	 * @return issue record for viewing
	 */
	function viewIssue( $sessionID, $issueID ) {
		//@session_start();
		if( $this->userCanViewIssue( $sessionID, $issueID ) ) {
			//mysql_selectdb('students');
			$query="SELECT * FROM issues WHERE ID = '$issueID'";
			$result = mysql_query($query);
			$issue = mysql_fetch_array($result);
			$query = "select distinct (studentid) from `contacts-students`
				  where contactid in (
				  	select id from contacts where issue='$issueID'
				  )";
			$result = mysql_query($query);
			$students = array();
			$i=0;
			while( $row = mysql_fetch_row($result) ) {
				$students[$i] = $row[0];
				$i++;
			}
			$students = implode(',',$students);
			$query = "select distinct (Creator) from `contacts` where ID in (
				  	select ID from contacts where Issue='$issueID'
				  )";
			$result = mysql_query($query);
			$users = array();
			$i=0;
			while( $row = mysql_fetch_row($result) ) {
				$users[$i] = $row[0];
				$i++;
			}
			$users = implode(',',$users);
			$issue['Students'] = $students;
			$issue['Staff'] = $users;
			return $issue;
		}
	}
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $issueID the ID of the issue to watch
	 */
	function userCanWatchIssue( $sessionID, $issueID ) {
		return $this->userCanViewIssue( $sessionID, $issueID );
	}
// MIGHT WORK WITH NEW DB
	/**
	 * Indicates whether or not the calling user is watching a particular issue.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $issueID the ID of the issue to watch
	 *
	 * @return true if the the user is watching the issue, false if not.
	 */
	function userIsWatchingIssue( $sessionID, $issueID ) {
		if(empty($this->link)){
			echo "Not connected to database. Unexpected error. Contact your system administrator.";
			exit;	
		}
		$user = $_SESSION['userid'];
		$query = "SELECT issueid FROM issuewatch WHERE userid='$user' and issueid='$issueID'";
		$result = mysql_query($query);
		return mysql_num_rows($result) > 0;
		
	}
// FIXED FOR NEW DB
	/**
	 * Make the calling user watch a particular issue.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $issueID the ID of the issue to watch
	 */
	function watchIssue( $sessionID, $issueID ) {
		if( $this->userCanWatchIssue( $sessionID, $issueID ) && !($this->userIsWatchingIssue( $sessionID, $issueID )) ) {
			if(empty($this->link)){
				echo "Not connected to database. Unexpected error. Contact your system administrator.";
				exit;	
			}
			$user = $_SESSION['userid'];
			$query="insert into issuewatch (userid, issueid) values ('$user','$issueID')";
			mysql_query($query);
		}
	}
	
	
	/**
	* Determines if another user is watching the specified issue.
	*
	* @param $user the user to check for watching the issue
	* @param $issueID the issue to check if the user is watching
	*
	* @return true if the user is watching, false if not
	*/
	function otherUserIsWatchingIssue( $user, $issueID ) {
		if(empty($this->link)){
			echo "Not connected to database. Unexpected error. Contact your system administrator.";
			exit;	
		}
		$query = "SELECT issueid FROM issuewatch WHERE userid='$user' and issueid='$issueID'";
		$result = mysql_query($query);
		return mysql_num_rows($result) > 0;
		
	}
	
	
	/**
	* Determines if a specified user can view a specific issue.
	*
	* @param $user the user to check if able to view an issue
	* @param $issueID the issue to check
	*
	* @return true if user can view, false if not
	*/
	//this works exactly like the original, except for a specified user
	function otherUserCanViewIssue( $user, $issueID ) {
		$query= "SELECT Level FROM issues WHERE ID = '$issueID'";
		$result = mysql_query($query);
		$value = mysql_fetch_array($result);
		extract($value);
		$query2 = "SELECT Creator FROM issues WHERE ID = '$issueID'";
		$result2 = mysql_query($query2);
		$value2 = mysql_fetch_array($result2);
		extract($value2);
		$userID = $_SESSION['userid'];
		$userLevel = $this->getUserAccessLevel($user);
			if($Level == 'A')
				if($userLevel == READONLYFULL || $userLevel == FIRSTWATCH || $userLevel == PRIVILEGED || $userLevel == ADMINISTRATOR)
					return TRUE;
				elseif($Creator == $userID)
					return TRUE;
				elseif($AssignedTo == $userID)
					return TRUE;
				else
					return FALSE;
			elseif($Level == 'B')	
				return $userLevel != NOACCESS;
			else
				return FALSE;
	}
		
	
	//this works exactly like the original, except for a specified user
	function otherUserCanWatchIssue( $user, $issueID ) {
		return $this->otherUserCanViewIssue( $user, $issueID );
	}
	
	/**
	 * Make the specified user watch a particular issue.
	 *
	 * @param $user user to set to watch said issue
	 * @param $issueID the ID of the issue to watch
	 */
	function otherUserWatchIssue( $user, $issueID ) {
		if( $this->otherUserCanWatchIssue( $user, $issueID ) && !($this->otherUserIsWatchingIssue( $user, $issueID )) ) {
			if(empty($this->link)){
				echo "Not connected to database. Unexpected error. Contact your system administrator.";
				exit;	
			}
			$query="insert into issuewatch (userid, issueid) values ('$user','$issueID')";
			mysql_query($query);
		}
	}
	
	/**
	* This function sets all users in the system to watch a particular issue
	*
	* @param $issueID the ID of the issue to set everyone to watch
	*/
	function setAllWatch( $issueID ) {
		$query = "select * from users";
		
		$result = mysql_query( $query );
		while( $row = mysql_fetch_assoc( $result ) ) {
			$this->otherUserWatchIssue( $row['ID'], $issueID );
		}
	}
	
	/**
	* This function sets users from First Watch and up to watch a particular issue
	*
	* @param $issueID the ID of the issue to set watch on
	*/
	function setFirstWatchToWatch( $issueID ){
		$query = "SELECT * FROM users WHERE AccessLevel > 7";
		$result = mysql_query($query);
		while($row = mysql_fetch_assoc($result)){
			$this->otherUserWatchIssue( $row['ID'], $issueID );
		}
	}
	
// FIXED FOR NEW DB
	/**
	 * Halts the calling user's watch of a particular issue.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $issueID the ID of the issue to stop watching
	 */
	function stopWatchingIssue( $sessionID, $issueID ) {
		$user = $_SESSION['userid'];
		$query="delete from issuewatch where userid='$user' and issueid='$issueID'";
		mysql_query($query);
	}
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently the user must be an administrator
	 * or be the Creator AND be Privileged.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $issueID the ID of the issue to modify
	 */
	function userCanModifyIssue( $sessionID, $issueID ) {
		$query = "SELECT Creator FROM issues WHERE ID = '$issueID'";
		$result = mysql_query($query);
		$value = mysql_fetch_array($result);
		extract($value);
		$userID = $_SESSION['userid'];
		if($userID == $Creator)
			return $this->getAccessLevel() == PRIVILEGED;
		else
			return $this->getAccessLevel() == ADMINISTRATOR;
	}
// ok
	/**
	 * Modifies an issue's record in the database.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $issueID the ID of the issue to modify
	 * @param
	 *		NOTE: ADD MORE DOCUMENTATION HERE!
	 */
	function modifyIssue( $sessionID, $issueID, $OTHER_STUFF_HERE ) {
		if( $this->userCanModifyIssue( $sessionID, $issueID ) ) {
			
			// UNIMPLEMENTED!
			
		}
	}
// ok
	/**
	 * Determines if the current user can set an issue's status.
	 * Right now, this calls userCanModifyIssue unless the user
	 * is the creator - if so, they can change the status if they are
	 * still a non-read only user who has access to the system.
	 *
	 * @param $sessionID the current session ID
	 * @param $issueID the issue to check if the status can be set
	 *
	 * @return true if the user can set, false if not
	 */
	function userCanSetIssueStatus( $sessionID, $issueID ) {
		$query = "SELECT Creator FROM issues WHERE ID = '$issueID'";
		$result = mysql_query($query);
		$value = mysql_fetch_array($result);
		extract($value);
		$userID = $_SESSION['userid'];
		$level = $this->getAccessLevel();
		if($Creator == $userID)
			return ($level == NORMAL || $level == FIRSTWATCH || $level == PRIVILEGED || $level == ADMINISTRATOR);
		else
			return $this->userCanModifyIssue( $sessionID, $issueID );
	}
// ok
	/**
	 * Sets an issue's status.
	 *
	 * @param $sessionID the current session ID
	 * @param $issueID the ID of the issue's status to be changed
	 * @param $status what to change the status to
	 */
	function setIssueStatus( $sessionID, $issueID, $status ) {
		if( $this->userCanSetIssueStatus( $sessionID, $issueID ) ) {
			$userID = $_SESSION[ 'userid' ];
			$this->sendIssueAlerts($issueID, 'Issue status changed.');
			$query="UPDATE issues SET Modifier='$userID', Status='$status' WHERE ID='$issueID'";
			$result=mysql_query($query);
			return $result;
		}
	}
	
	/**
	 * Determines if the current user can set an issue's category.
	 *
	 * @param $sessionID the current session ID
	 * @param $issueID the issue to check if the category can be set
	 *
	 * @return true if the user can set, false if not
	 */
	function userCanSetIssueCategory( $sessionID, $issueID ) {
		return $this->userCanSetIssueStatus( $sessionID, $issueID );
	}

	/**
	 * Sets an issue's category.
	 *
	 * @param $sessionID the current session ID
	 * @param $issueID the ID of the issue's status to be changed
	 * @param $category what to change the category to
	 */
	function setIssueCategory( $sessionID, $issueID, $category ) {
		if($this->userCanSetIssueCategory( $sessionID, $issueID )){
	 		$userID = $_SESSION[ 'userid' ];
			$this->sendIssueAlerts($issueID, 'Issue level changed.');
			$query="UPDATE issues SET Modifier='$userID', Category='$category' WHERE ID='$issueID'";
			$result=mysql_query($query);
			return $result;
		}
	}
	
	/**
	 * Determines if the current user can set an issue's level.
	 *
	 * @param $sessionID the current session ID
	 * @param $issueID the issue to check if the level can be set
	 *
	 * @return true if the user can set, false if not
	 */
	function userCanSetIssueLevel( $sessionID, $issueID ) {
		return $this->userCanSetIssueStatus( $sessionID, $issueID );
	}
	
	/**
	 * Sets an issue's level.
	 *
	 * @param $sessionID the current session ID
	 * @param $issueID the ID of the issue's level to be changed
	 * @param $level what to change the level to
	 */
	 function setIssueLevel( $sessionID, $issueID, $level ){
	 	if($this->userCanSetIssueLevel( $sessionID, $issueID )){
	 		$userID = $_SESSION[ 'userid' ];
			$this->sendIssueAlerts($issueID, 'Issue level changed.');
			$query="UPDATE issues SET Modifier='$userID', Level='$level' WHERE ID='$issueID'";
			$result=mysql_query($query);
			return $result;
		}
	}
	
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not.  Currently the user must be able to delete all contacts which
	 * are a part of the issue.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $issueID the ID of the issue to delete
	 */
	function userCanDeleteIssue( $sessionID, $issueID ) {
		$userCan = TRUE;
		$contacts = $this->getIssuesContacts( $issueID );
		
		for( $i = 0; $i < sizeof( $contacts ); $i++ ) {
			if( !$this->userCanDeleteContact( $sessionID, $contacts[$i] ) ) {
				$userCan = FALSE;
				break;
			}
		}
		return $userCan;
	}
// PROBABLY WORKS WITH NEW DB
	/**
	 * Deletes an issue's record from the database, as well as the records of all associated
	 * contacts.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $issueID the ID of the issue to delete
	 */
	function deleteIssue( $sessionID, $issueID ) {
		//@session_start();
		if( $this->userCanDeleteIssue( $sessionID, $issueID ) ) {
			
			// delete attached files and references to files in db
			$contacts = $this->getIssuesContacts($issueID);
			for($i=0; $i<sizeof($contacts); $i++){
				$contact = $this->viewContact('', $contacts[$i]);
				if(!$this->deleteAllFiles($contact['ID'])){
					echo 'Error deleting files for contact '.$contact['ID'];
				}
			}
		
		// Michael Thompson * 12/14/2005 * Also delete contacts, watches, xrefs, etc...
			$query = "DELETE FROM issues WHERE ID='".$issueID."'";
			$result = mysql_query( $query );
			$query = "delete from `issuealert` where IssueID ='".$issueID."'";
			$result = mysql_query( $query );
			$query = "delete from `issuewatch` where IssueID ='".$issueID."'";
			$result = mysql_query( $query );
			$result = mysql_query($query);
			$query = "DELETE FROM `contacts-students` WHERE ContactID in (select id from contacts where issue = '".$issueID."' )";
			$result = mysql_query($query);
			$query = "delete from contacts where issue='$issueID'";
			$result = mysql_query($query);
		}
	}
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not.  Currently the user must have permission to create issues.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $issueID the ID of the issue to append this contact to
	 */
	function userCanCreateContact( $sessionID, $issueID ) {
		return $this->userCanCreateIssue( $sessionID, $issueID );
	}
// THIS COULD POSSIBLY WORK WITH THE NEW DB
	/**
	 * Creates a new contact to be associated with a particular issue.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $DateCreated date the contact was created (current date)
	 * @param $Students students associated with the contact
	 * @param $Description description of the contact
	 * @param $Issue ID of issue contact is appended to
	 * @param $watch who to watch the contact
	 * 
	 * @return the ID of the new contact
	 */
	function createContact( $sessionID, $DateCreated, $Students, $Description, $Issue, $watch) {
		if( $this->userCanCreateContact( $sessionID, $Issue ) ) {
			$Creator =  $_SESSION['userid'];
			$table='contacts';
			if(empty($this->link)){
				echo "Not connected to database.  You must instantiate the DataAccessManager before performing
					database accesses.";
				exit;	
			}
			
			// begin hack to generate id
			$IDs = array();
			$ID = date('m').date('d').date('Y').'-';
			$result = mysql_query("SELECT * FROM $table WHERE ID like '%$ID%'");
			for($i=0; $results = mysql_fetch_assoc($result); $i++){
				$IDs[$i] = $results['ID'];
			}
			$idnumber = mysql_num_rows($result) + 1;
			while(in_array('C'.$ID.$idnumber, $IDs)){
				$idnumber++;
			}
			$ID ='C'.$ID.$idnumber;
			// end hack to generate id
			
			
			$Description = addslashes(htmlspecialchars($Description));
			
			$query = "insert into contacts (id, datecreated, description, issue, creator)
				  values ('$ID','$DateCreated','$Description','$Issue','$Creator')";
				  
			mysql_query($query);
			

			$Students = explode(',', $Students);
			foreach($Students as $Student) {
				$query = "insert into `contacts-students` (contactid, studentid)
					  values ('$ID','$Student')";
				mysql_query($query);
				$this->sendStudentAlerts($Student,'New contact involved student.');
			}
			
			
			
			$this->sendIssueAlerts($Issue,'New contact appended to issue.');
			if($watch=='1'){
				$this->watchIssue('', $Issue);
			}
			else if($watch=='0'){
				$this->stopWatchingIssue('', $Issue);
			}
			else if($watch=='2') {
				$this->setAllWatch( $Issue );
			}
			else if($watch=='3'){
				$this->setFirstWatchToWatch( $Issue );
			}
			
			/*
			$query = "SELECT * FROM issues WHERE ID='$Issue'";
			$result = mysql_query($query);
			@$results = mysql_fetch_array($result);
			if(!$results){
				echo 'There is no issue with that ID.';
				exit;
			}
			$IssueStudents = explode(",", $results['Students']);
			$Students = array_diff($Students, $IssueStudents);
			if(sizeof($Students) > 0){
				$Students = implode(",", $Students);
				$Students = ','.$Students;
				$query="UPDATE issues SET Students=CONCAT(Students,'$Students') WHERE ID='$Issue'";
				mysql_query($query);
			}

			$Staff = "";//explode(',', $results['Staff']);
			$nothing = array('');
			$Staff = array_diff($Staff, $nothing);
			if(!in_array($Creator, $Staff)){
				if (sizeof($Staff) > 0){
					$query="UPDATE issues SET Staff=CONCAT(Staff,',$Creator') WHERE ID='$Issue'";
				}
				else{
					$query="UPDATE issues SET Staff=CONCAT(Staff,'$Creator') WHERE ID='$Issue'";
				}
				mysql_query($query);
			}
			if (sizeof($Staff) > 0){
					$query="UPDATE issues SET Modifier='$Creator' WHERE ID='$Issue'";
					mysql_query($query);
			}
			*/
			
			return $ID; //Yay, it works!	
		}
			return false; //Oh no, failure!
	}

	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not.  
	 *
	 * @param $sessionID session information like IP address to verify for security
	 */	
	function userCanAttachFile($sessionID, $issueID){
		return $this->userCanCreateContact( $sessionID, $issueID );
	}

	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not.  
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $fileID unique ID of file in db
	 */
	function userCanDeleteFile($sessionID, $fileID ){
		$query = "SELECT `ContactID` FROM `attachments` WHERE ID='$fileID'";
		$result = mysql_query($query);
		$result = mysql_fetch_array($result);
		$contactID = $result['ContactID'];
		return $this->userCanDeleteContact( $sessionID, $contactID );
	}
	
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $fileID unique ID of file in db
	 */
	function userCanViewFiles($sessionID, $contactID){
		return $this->userCanViewContact( $sessionID, $contactID );
	}
	
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $fileID unique ID of file in db
	 */	
	function userCanDownloadFile($sessionID, $fileID ){
		$query = "SELECT `ContactID` FROM `attachments` WHERE ID='$fileID'";
		$result = mysql_query($query);
		$result = mysql_fetch_array($result);
		$contactID = $result['ContactID'];
		return $this->userCanViewContact( $sessionID, $contactID );
	}
	
	
	
	/**
	 * Attaches a file to a contact. Creates an ID for file, uploads it to the server,
	 * updates 'attachments' table in database. Retrieves file information from $_FILES array.
	 *
	 * @param $contactID contact to add file to
	 *
	 * @return contact ID
	 */
	function attachFile($contactID){
		
		global $file_upload_folder;
		
		//extensions that can be uploaded
		$allowed_ext = 'jpg,gif,png,pdf,doc,docx,txt,zip,mp3,jpeg,xls,xlsx,ppt,pptx';	
		
		// important file variables
		$userfile_name = $_FILES['userfile']['name'];
		$userfile_size = $_FILES['userfile']['size'];
		$userfile_type = $_FILES['userfile']['type'];
		$userfile_error = $_FILES['userfile']['error'];
		$userfile_tmp_name = $_FILES['userfile']['tmp_name'];
		$max_file_size = $_POST['MAX_FILE_SIZE'];
		// get issue id
		$query = "SELECT `Issue` FROM `contacts` WHERE ID='$contactID'";
		$result = mysql_query($query);
		$result = mysql_fetch_array($result);	
		$issueID = $result['Issue'];
		
		if( $this->userCanAttachFile($sessionID, $issueID)){
		
			// test link
			if(empty($this->link)){
				echo "Not connected to database  You must instantiate the DataAccessManager before performing
					database accesses.";
				exit;	
			}
			
			// begin hack to generate attachment id
			$IDs = array();
			$ID = date('m').date('d').date('Y').'-';
			$result = mysql_query("SELECT * FROM attachments WHERE ID like '%$ID%'");
			for($i=0; $results = mysql_fetch_assoc($result); $i++){
				$IDs[$i] = $results['ID'];
			}
			$idnumber = mysql_num_rows($result) + 1;
			while(in_array('A'.$ID.$idnumber, $IDs)){
				$idnumber++;
			}
			$ID ='A'.$ID.$idnumber;
			// end hack to generate id
			
			// get extension
			$extension = pathinfo($userfile_name);
			$extension = $extension[extension];
			$extension = strtolower($extension);
		
			// check entension
			$allowed_paths = explode(',', $allowed_ext);
			for($i = 0; $i < count($allowed_paths); $i++){
				if ($allowed_paths[$i] == $extension){
					$ok = '1';
				}
			}
			if ($ok != '1') {
				print '<font color="red">Sorry, incorrect file type. File extension must be one of: '.$allowed_ext.'</font>';
				return false;
			}
			
			// do the upload
			mkdir("$file_upload_folder$ID");
			if(move_uploaded_file($userfile_tmp_name, "$file_upload_folder$ID/$userfile_name")) {
				// send queries to update 'attachments' table
				$query = "insert into `attachments` (id, extension, alias, contactid)
					values ('$ID','$extension','$userfile_name', '$contactID')";
				mysql_query($query);		

	
				return $ID;
			} 
			else{
				switch ($userfile_error){
			   	case 1:
					print '<font color="red">Error: The file is bigger than this PHP installation allows.</font>';
					break;
				case 2:
					print '<font color="red">Error: File must be no greater than '.($max_file_size / 1000).' KB.</font>';
					break;
				case 3:
					print '<font color="red">Error: Only part of the file was uploaded - please try again.</font>';
					break;
				case 4:
					print '<font color="red">Error: No file was uploaded.</font>';
					break;
				default: 
					print '<font color="red">Unknown error - please retry.</font>';
					break;
				}
				 
				return false;
			}
		}
		echo 'Permission to attach files denied.';
		return false; // user doesn't have permission
	}
	
	
	
	/**
	 * Deletes a user uploaded file from the database. Takes the file ID to be deleted.
	 * Drops relavent entry from 'attachments'. Updates 'contacts-attachments' appropriately.
	 * 
	 * @param $fileID file to be deleted
	 *
	 * @return true or false.
	 */
	function deleteFile($fileID){
		global $file_upload_folder;
		
		// IMPORTANT: Permission checking is currently not done here. It is done by the web gui. 
		// Links to delete files only show up when userCanDeleteFile() returns true.
		// This is something to keep in mind for future phronesis versions.
		
		// delete file from server
		if(file_exists($file_upload_folder.$fileID)){
			$scan = glob(rtrim($file_upload_folder.$fileID,'/').'/*');
			if(unlink($scan[0]) and rmdir($file_upload_folder.$fileID)){	
				// delete references of file from database
				$query = "DELETE FROM `attachments` WHERE ID='".$fileID."'";
				return mysql_query( $query );
			}
		}
	}
	
	/**
	 * Deletes all of the attached files for a given contact.
	 *
	 * @param $contactID contact to delete files for
	 *
	 * @return true if all deleted, false if not
	 */
	function deleteAllFiles($contactID){
		$fileIDs = $this->viewAttachedFiles('', $contactID);
		$success = true;
		for($i=0; $i<count($fileIDs); $i++){
			if(!$this->deleteFile($fileIDs[$i])){
				$success = false;
			}
		}
		return $success;
	}
	
	/**
	 * Retrieves all the attached files for a contact
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $contactID contact to view attached files of
	 *
	 * @return array of file IDs attached to given contact
	 */
	function viewAttachedFiles($sessionID, $contactID){
		if( $this->userCanViewFiles( $sessionID, $contactID )){
			$query = "SELECT * FROM `attachments` WHERE `contactid` = '$contactID'";
			$result = mysql_query($query);
			for($i=0; $results = mysql_fetch_array($result); $i++){
				$IDs[$i]=$results['ID'];
			}
			if(empty($IDs)) return;
			else return $IDs;		
		}
		//echo 'permission denied to view files';
	}
	
	/**
	 * Retrieves the original name that the file had when it was uploaded. Right now
	 * it requires no sort of user authentication. Perhaps it should...?
	 *
	 * @param $fileID file to look up alias for
	 *
	 * @return string of alias for the file, empty string if error
	 */
	function getAttachedFileName($fileID){
		$query = "SELECT `Alias` FROM `attachments` WHERE ID='$fileID'";
		$result = mysql_query($query);
		$result = mysql_fetch_array($result);	
		$result = $result['Alias'];
		return($result);
	}
	
	
	/**
	 * Retrieves extension for a given file. Right now it requires no sort of user
	 * authentication. Perhaps it should...?
	 *
	 * @param $fileID file to query for extension
	 *
	 * @return string of extension for this file, empty string if error
	 */
	function getAttachedFileExt($fileID){
		$query = "SELECT `extension` FROM `attachments` WHERE ID='$fileID'";
		$result = mysql_query($query);
		$result = mysql_fetch_array($result);		
		return($result['extension']);
	}
	

	/**
	 * Retrieves all the attached files for a student
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID to view attached files of
	 *
	 * @return array of file IDs attached to given student
	 */
	function viewAllAttachedFiles($sessionID, $studentID){
      $allFiles = array();
      $someFiles = array();
      $file = array();
      $contacts=$this->getStudentsContacts($studentID);
      for($i = 0; $i < sizeof($contacts); $i++){
         $contactID = $contacts[$i];
         $contact = $this->viewContact('',$contactID);
         $contactDate = $contact['DateCreated'];
         if(!empty($contactID)){
            unset($someFiles);
            $someFiles = $this->viewAttachedFiles('',$contactID);            
            for($j = 0; $j < sizeof($someFiles); $j++){
               unset($file);
               $file['fileid'] = $someFiles[$j];
               $file['contactid'] = $contactID;
               $file['name'] = $this->getAttachedFileName($someFiles[$j]);
               $file['date'] = $contactDate;
               array_push($allFiles,$file);
            }
         }
      }
      return($allFiles);
	}
	
	 
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently the user must have an access level of at least 9.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $contactID the ID of the contact to view
	 */
	function userCanViewContact( $sessionID, $contactID ) {
		if($contactID == '')
			return FALSE;
		$query="SELECT Issue FROM contacts WHERE ID = '$contactID'";


		$result = mysql_query($query);
		$value = mysql_fetch_array($result);
		extract($value);
		if($Issue == '')
			return FALSE;
		return $this->UserCanViewIssue($sessionID, $Issue);
	}
// ok
	/**
	 * Returns a contact record from the database in array form.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $contactID the ID of the contact to view
	 *
	 * @return contact to view
	 */
	function viewContact( $sessionID, $contactID ) { 
		if( $this->userCanViewContact( $sessionID, $contactID ) ) {
			//mysql_selectdb('students');
			$query="SELECT * FROM contacts WHERE ID = '$contactID'";
			$result = mysql_query($query);
			$contact = mysql_fetch_array($result);
			
			$query = "select distinct (`StudentID`) from `contacts-students`
				  where `ContactID`='$contactID'";
			//echo $query."<br>";
			$result = mysql_query($query);
			$students = array();
			$i=0;
			while( $row = mysql_fetch_row($result) ) {
				if(!empty($row[0])){
					$students[$i] = $row[0];
					$i++;
				}

			}
			$students = implode(',',$students);
			$contact['Students'] = $students;
			return $contact;
		}
	}
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently the user must be an administrator,
	 * or he/she must be the creator of the contact AND be Privileged.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $contactID the ID of the contact to modify
	 */
	function userCanModifyContact( $sessionID, $contactID ) {
		$results = mysql_fetch_array( mysql_query("select * from contacts where ID = '$contactID'") );
		if($this->getAccessLevel() == ADMINISTRATOR)
			return TRUE;
		else
			return ( ( $this->getAccessLevel() == PRIVILEGED ) && ( strcmp( $_SESSION['userid'], $results['Creator'] ) == 0 ) );
	}
// ok
	/**
	 * Modifies the contact's record in the database.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $ID is of contact to be modified
	 * @param $contactInfo an associative array in which the keys are field names in the contact table
	 */
	function modifyContact( $sessionID, $ID, $contactInfo ) {
		if( $this->userCanModifyContact( $sessionID, $ID ) ) {
			array_pop($contactInfo);
			$keys = array_keys($contactInfo);
			$query = 'UPDATE contacts SET ';
			for ($i=0; $i<sizeof($contactInfo); $i++){
				if ($i == 0){
					$query .= $keys[$i]."='".addslashes(htmlspecialchars($contactInfo[$keys[$i]]))."'";
				}
				else{
					$query .= ', '.$keys[$i]."='".addslashes(htmlspecialchars($contactInfo[$keys[$i]]))."'";
				}
			}
			$query .= " ,Modifier='".$_SESSION['userid']."' WHERE ID='".$ID."'";
			mysql_query($query);
		}
	}
// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently the user must be an administrator.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $contactID the ID of the contact to delete
	 */
	function userCanDeleteContact( $sessionID, $contactID ) {
		return $this->getAccessLevel() == ADMINISTRATOR;
	}
// PROBABLY WORKS WITH NEW DB
	/**
	 * Deletes the specified contact's record from the database.
	 * If this is the last contact, delete the whole issue.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $contactID the ID of the contact to delete
	 */
	function deleteContact( $sessionID, $contactID ) {
		if( $this->userCanDeleteContact( $sessionID, $contactID ) ) {
		
			// delete attached files and references in db
			if(!$this->deleteAllFiles($contactID)){
				echo 'Error deleting files for contact '.$contactID;
			}
				
		// Michael Thompson * 12/14/2005 * Always delete contact, delete issue if last contact, 
		//                                 added code to delete watches, xrefs, etc...
			$query = "select id from contacts where issue in (
				  	select issue from contacts where id='$contactID'
				  )";
			$result = mysql_query($query);
			// this is the last contact.  delete the issue.
			if( mysql_num_rows($result) <= 1 ) {
				$query = "delete from issues where id in (
					  	select issue from contacts
						where id='$contactID'
					  )";
				$result = mysql_query($query);
				$query = "delete from `issuealert` where IssueID in (
					  	select issue from contacts
						where id='$contactID'
					  )";
				$result = mysql_query($query);
				$query = "delete from `issuewatch` where IssueID in (
					  	select issue from contacts
						where id='$contactID'
					  )";
				$result = mysql_query($query);
			}
			// if its not the last contact for the issue, update whos the modifier
			else{
				$modifier =  $_SESSION['userid'];
				$query="update issues set modifier='$modifier' where id in (
					  	select issue from contacts
						where id='$contactID')";
				$result = mysql_query($query);
			}
			// now delete the contact itself
			$query = "delete from contacts where id='$contactID'";
			$result = mysql_query($query);
			$query = "delete from `contacts-students` where ContactID='$contactID'";
			$result = mysql_query($query);
			return;
		}
	}
// okgetIssues
	/**
	 * Returns a numerically indexed array containing the IDs of all the contacts
	 * assosciated with the designated Issue ID.
	 *
	 * @param $IssueID the ID of the issue to get contacts for
	 *
	 * @return IDs of contacts associated with the issue
	 */
	function getIssuesContacts($IssueID){
		if($this->userCanViewIssue('', $IssueID)){
			$query = "SELECT * FROM contacts WHERE `Issue` = '$IssueID' ORDER BY `DateCreated` DESC";
			$result = mysql_query($query);
			for($i=0; $results = mysql_fetch_array($result); $i++){
				$IDs[$i]=$results['ID'];
			}
			return $IDs;
		}
	}
	
	/**
	 * Returns a numerically indexed array containing the IDs of all the contacts
	 * assosciated with the designated Issue ID.
	 *
	 * @param $issue the ID of the issue to get contacts for
	 *
	 * @return contacts associated with the issue
	 */
	function issueContacts($issue) {
		if($this->userCanViewIssue('', $issue)){
			$query = "select * from contacts where issue = '$issue' order by datecreated desc";
			$result = mysql_query($query);
			$return = array();
			while( $row = mysql_fetch_assoc($result) ) {
				array_push( $return, $row );
			}
			return $return;
		}
	}
	
	/**
	 * Retrieves issues watched by the logged-in user
	 *
	 * @return array of issues being watched by the logged-in user
	 */
	function issuesWatched() {
		$user = $_SESSION['userid'];
		$query = "select i.* from issues i, issuewatch iw
			  where i.id = iw.issueid
			  and iw.userid = '$user'";
		$result = mysql_query($query);
		$return = array();
		while( $row = mysql_fetch_assoc($result) ) {
			array_push( $return, $row );
		}
		return $return;
	}

	/**
	 * Retrieves students watched by the logged-in user
	 *
	 * @return array of students being watched by the logged-in user
	 */
// Michael Thompson * 12/07/2005 * Added sort order per Kurt Holmes
	function studentsWatched() {
		$user = $_SESSION['userid'];
		$query = "select s.* from X_PNSY_STUDENT s, studentwatch sw
			  where s.ID = sw.studentid
			  and sw.userid = '$user' order by s.`LAST_NAME`, s.`FIRST_NAME`";
		$result = mysql_query($query);
		$return = array();
		while( $row = mysql_fetch_assoc($result) ) {
			array_push( $return, $row );
		}
		return $return;
	}
	
	
	
	
	
	
// PROBABLY WORKS WITH NEW DB
	/**
	 * Retrieves the IDs of all contacts associated with the specified student.
	 *
	 * @param $StudentID the ID of the student to get contacts for
	 *
	 * @return array of contacts for the student
	 */
// Michael Thompson * 12/07/2005 * Added Sort Order per Kurt Holmes
	function getStudentsContacts( $StudentID ){
		$query = "select contacts.* from contacts, `contacts-students`
			  	where contacts.id = `contacts-students`.contactid
				and `contacts-students`.studentid = '$StudentID'
                                order by contacts.`DateCreated` DESC";
		$result = mysql_query($query);
		$IDs = array();
		for($i=0; $results = mysql_fetch_array($result); $i++){
			$IDs[$i]=$results['ID'];
		}
		return $IDs;
	}
// ok
	/**
	 * Retrieves the ID of a student whose name is of the form <FirstName> <LastName>.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $StudentName name of student to look up ID for
	 *
	 * @return ID of the student
	 */
	function getStudentID($sessionID, $StudentName){
		$StudentName = explode(" ", $StudentName);
		$query = "SELECT ID FROM X_PNSY_STUDENT WHERE `LAST_NAME` = '".$StudentName[1]."' AND `FIRST_NAME` = '".$StudentName[0]."'";
		$result = mysql_query($query);
		for($i=0; $results = mysql_fetch_array($result); $i++){
			$IDs[$i] = $results['ID'];
		}
		if (sizeof($IDs) > 1){
			return $IDs;
		}
		else if (sizeof($IDs) == 1){
			return $IDs[0];
		}
		else if (sizeof($IDs) < 1){
			return NULL;
		}
	}
// ok
	/**
	 * Retrieves the login context of the specified user as stored in the database table
	 *
	 * @param $username the ID of the user to get the context of
	 *
	 * @return array of login context of the user as in the database table, beginning with the
	 * most specific organizational unit at the first index.
	 */
	function getUserContext($username){
		$table='users';
		$query="SELECT Context1, Context2 FROM $table WHERE `ID` = '$username'";
		$result = mysql_query($query);
		$context=mysql_fetch_array($result);
		return $context;
	}
// ok
	/**
	 * Retrieves the email address of the specified user as stored in the database table.
	 *
	 * @param $username the ID of the user to get the email address of
	 *
	 * @return email address of the user
	 */
	function getUserEmail($username){
		$table='users';
		$query="SELECT Email FROM $table WHERE `ID` = '$username'";
		$result = mysql_query($query);
		$Email=mysql_fetch_array($result);
		$Email=$Email['Email'];
		return $Email;
	}
	
	/**
	 * Checks if a field is in the specified table
	 *
	 * @param $table table in which to search fields
	 * @param $field field to check for in field names
	 *
	 * @return whether or not one of the field names in the table
	 */
	 function fieldInTable( $table, $field ) {
	 	$fieldFound = false;
		$fields = $this->getTableFields('', $table);
		$columns = mysql_num_fields($fields);
		for($i = 0; $i < $columns; $i++){
			if(mysql_field_name($fields, $i) == $field){
				$fieldFound = true;
				break;
			}
		}
		return $fieldFound;
	}

// ok
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. Currently any active user can perform a search.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $table the database table to search
	 */
	function userCanPerformSearch($sessionID, $table){
		return $this->getAccessLevel() != NOACCESS;
	}
	
// ok
	/**
	 * Performs a database search and returns the records in an assosciative array.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $table the database table to search
	 * @param $field the table field to search on
	 * @param $term the term to search for
	 * @param $order how to order the results
	 *
	 * @return associative array of the records that fit the search
	 */
// Michael Thompson * 12/07/2005 * Added search order parameter
	function performSearch($sessionID, $table, $field, $term, $order){
		if($this->userCanPerformSearch('', $table)){
			//Allow user to search by sensitive or normal for level
			if($table == 'issues' && $field == 'Level'
			   && $term!='A' && $term!='a' && $term!='B' && $term!='b'){
				if(strstr('sensitive', $term))
					$term = 'A';
				elseif(strstr('normal', $term))
					$term = 'B';
			}
			if($table == 'students'){
				if(!$this->fieldInTable($table, $field)){
					if($this->fieldInTable('X_PNSY_STUDENT', $field))
						$table = 'X_PNSY_STUDENT';
					else
						$table = 'X_PNSY_ADDRESS';
				}
			}
                               
                       
			if($table == 'X_PNSY_STUDENT'){
                                      $query = "SELECT * FROM $table JOIN X_PNSY_ADDRESS ON $table.ADDRESS_ID = X_PNSY_ADDRESS.ADDRESS_ID WHERE $field like '%$term%'";
			}
			elseif($table == 'X_PNSY_ADDRESS'){
				$query = "SELECT * FROM X_PNSY_STUDENT ss, $table s WHERE s.$field LIKE '%$term%'AND s.ADDRESS_ID IN 
							(SELECT ADDRESS_ID FROM $table WHERE ADDRESS_ID=s.ADDRESS_ID) 
							AND s.ADDRESS_ID=ss.ADDRESS_ID GROUP BY s.ADDRESS_ID";
				if($field == 'STREET'){
				//"SELECT ID, CONCAT(FirstName, ' ', MiddleIn, ' ', LastName) AS FullName FROM students WHERE
				//	CONCAT(FirstName, ' ', MiddleIn, ' ', LastName) LIKE '%$name%'";
				
					$query = "SELECT * FROM X_PNSY_STUDENT ss, $table s WHERE (s.STREET_1 LIKE '%$term%' OR s.STREET_2 LIKE '%$term%'
							OR s.STREET_3 LIKE '%$term%' OR s.STREET_4 LIKE '%$term%' OR s.STREET_5 LIKE '%$term%') AND s.ADDRESS_ID IN 
							(SELECT ADDRESS_ID FROM $table WHERE ADDRESS_ID=s.ADDRESS_ID) AND s.ADDRESS_ID=ss.ADDRESS_ID GROUP BY s.ADDRESS_ID";
				}
			}
			elseif($table == 'X_PNSY_STUDENT' && $field == 'ADVISOR'){
				$table = 'X_PNSY_FACULTY';
				$query = "SELECT s . * FROM X_PNSY_STUDENT s, $table ss WHERE (CONCAT(ss.FIRST_NAME, ' ',
							 ss.LAST_NAME) LIKE '%$term%') AND s.$field IN (SELECT ID FROM $table WHERE ID = s.$field)
							AND s.$field = ss.ID GROUP BY s.$field, s.ID";
			}
			else
				$query = "SELECT * FROM $table WHERE $field like '%$term%'";

// Michael Thompson * 12/07/2005 * Added Below line to tack order onto sql sentence
                        if ($order != "") $query .= " ORDER BY $order";
			$result = mysql_query($query);
			for($i=0; $results = mysql_fetch_assoc($result); $i++){
				$return[$i] = $results;
			}
			return $return;
		}
	}
	
	/**
	 * Retrieves a faculty member's name given their ID
	 *
	 * @param $facultyID faculty member's ID
	 *
	 * @return Name of the faculty member
	 */
	 function getFacultyName( $facultyID ) {
	 	$query = "SELECT CONCAT(FIRST_NAME, ' ', MIDDLE_NAME, ' ', LAST_NAME) AS FULL_NAME FROM X_PNSY_FACULTY
	 				WHERE ID=$facultyID";
		$result = mysql_query($query);
		$result = mysql_fetch_assoc( mysql_query($query) );
		return $result['FULL_NAME'];
	}
	
// THIS FUNCTION COULD BE MORE ELEGANT BUT IT WORKS
//
	/**
	 * Retrieves the active user's Alerts field from the SQL database
	 *
	 * @return array of logged-in user's Alerts
	 */
	function getUserAlerts(){
		$ID=$_SESSION['userid'];
		$norows = true;
		$i = 0;
		$return = array();
		
		$query="select * from studentalert where userid='$ID'";
		$result=mysql_query($query);
		while( $row = mysql_fetch_assoc($result) ) {
			$return[$i] = $row;
			$i++;
			$norows = false;
		}
		
		$query="select * from issuealert where userid='$ID'";
		$result=mysql_query($query);
		while( $row = mysql_fetch_assoc($result) ) {
			$return[$i] = $row;
			$i++;
			$norows = false;
		}
				
		$query="select * from useralert where userid='$ID'";
		$result=mysql_query($query);
		while( $row = mysql_fetch_assoc($result) ) {
			$return[$i] = $row;
			$i++;
			$norows = false;
		}
		
		if($norows) return FALSE;
		return $return;
	}
	
// PROBABLY WORKS WITH NEW DB
	/**
	* Sends alerts for the specified issue
	*
	* @param $Issue the issue to send alerts for
	* @param $Message short description of the alert
	*/
	function sendIssueAlerts( $Issue, $Message ){
		$user = $_SESSION['userid'];
		$query = "SELECT userid FROM issuewatch WHERE issueid='$Issue' and userid!='$user'";
		$result = mysql_query($query);
		while( $row = mysql_fetch_row($result) ) {
			$query = "insert into issuealert (userid, issueid, message) values ('".$row[0]."','$Issue','$Message')";
			mysql_query($query);
			//mysql_free_result($result);
		}
		$query = "select assignedto from issues where id='$Issue' and assignedto!='$user'";
		$result = mysql_query( $query );
		while( $row = mysql_fetch_row( $result ) ) {
			$query = "insert into issuealert (userid, issueid, message) values ('".$row[0]."','$Issue','$Message')";
			mysql_query( $query );
		}
	}
// PROBABLY WORKS WITH NEW DB
	/**
	* Stuff like student information updated.  It's an alert.  Same as other alerts.
	*
	* @param $Student the student for which there is an alert
	* @param $Message short description of the alert
	*/
	function sendStudentAlerts( $Student, $Message ){
		$user = $_SESSION['userid'];
		$query = "SELECT userid FROM studentwatch WHERE studentid='$Student' and userid!='$user'";
		$result = mysql_query($query);
		while( $row = mysql_fetch_array($result) ) {
			$query = "insert into studentalert (userid, studentid, message) values ('".$row[0]."','$Student','$Message')";
			mysql_query($query);
			//mysql_free_result($result);
		}
		$query = "select assignedto from students where studentid='$Student' and assignedto!='$user'";
		$result = mysql_query( $query );
		while( $row = mysql_fetch_row( $result ) ) {
			$query = "insert into studentalert (userid, studentid, message) values ('".$row[0]."','$Student','$Message')";
			mysql_query( $query );
		}
	}
// PROBABLY WORKS WITH NEW DB
	/**
	* clears the alerts for a user
	*/
	function clearUserAlerts(){
		$user = $_SESSION['userid'];
		$query1 = "delete from studentalert where userid='$user'";
		$query2 = "delete from issuealert where userid='$user'";
		$query3 = "delete from useralert where userid='$user'";
		$query4 = "delete from firstwatchalert where userid='$user'";
		mysql_query($query1);
		mysql_query($query2);
		mysql_query($query3);
		mysql_query($query4);
	}
// ok
	/**
	* stop getting alerts for a particular issue.  duh.
	*
	* @param $Issue the particular issue.  duh.
	*/
	function removeIssueFromAlerts($Issue){
		$user = $_SESSION['userid'];
		$query = "delete from issuealert where userid='$user' and issueid='$Issue'";
		mysql_query($query);
	}
// PROBABLY WORKS WITH NEW DB
	/**
	* stop getting alerts for a particular student.  duh.
	*
	* @param $student the particular student to stop getting alerts for.  duh.
	*/
	function removeStudentFromAlerts($student){
		$user = $_SESSION['userid'];
		$query = "delete from studentalert where userid='$user' and studentid='$student'";
		mysql_query($query);
	}
// probably ok
	/**
	 * A more powerful searching function
	 *
	 * @param $table table to search
	 * @terms $terms to search for
	 *
	 * @return multi-dimensional associative array of search results, false if no results
	 */
	function powerSearch($table, $terms){
		$query = "SELECT * FROM `$table` $terms";
		$result = mysql_query($query);
		for($i=0; $results = @mysql_fetch_assoc($result); $i++){
			$finished[$i] = $results;
		}
		if(!isset($finished)){
			return false;
		}
		return $finished;
	}
// ok
	/**
	* Policy implementation; checks if the user can set the database name.
	* Currently, this is an Administrative task
	*
	* @return true if the user can, false if they can't
	*/
	function userCanSetDbName(){
		return $this->getAccessLevel() == ADMINISTRATOR;
	}
// ok
	/** 
	 * Sets the database name in the data file used by the system. Does not affect
	 * the actual database in any way.
	 */
	function setDbName($sessionID, $name){
		if($this->userCanSetDbName()){
			// NOT IMPLEMENTED!!!!
		}
	}
// ok
	/**
	* Policy implementation; checks to see if the user can set the database password.
	* Currently, this is an administrative task.
	*
	* @return true if the user can, false if otherwise (they can't)
	*/
	function userCanSetDbPassword(){
		return $this->getAccessLevel() == ADMINISTRATOR;
	}
// ok
	/**
	 * Actually sets the database password.  duh.
	 * This function is not implemented, even a little bit. Don't use this. Ever. Unless you
	 * implement it.
	 *
	 * @param $sessionID the session id
	 * @param $password the password to change to
	 */
	/**
	 * Sets the database password in the data file used by the system.  Does not affect
	 * the actual database in any way.
	 */
	function setDbPassword($sessionID, $password){
		if ($this->userCanSetDbPassword()){
			// NOT IMPLEMENTED!!!!
		}
	}
// ok
	/**
	 * Gets fields for a paticular table
	 *
	 * @param $sessionID the session id.
	 * @param $table the table to get fields for
	 *
	 * @return the fields as an SQL result set
	 */
	/*Returns the resource result for mysql_list_fields*/
	function getTableFields($sessionID, $table){
		//echo $table;
		$result = mysql_list_fields($this->dbname, $table);
		return $result;		// jeremy originally called this variable $tom, and for that i shall kill him.
	}
// probably ok
	/**
	 *************NOT USED ANYMORE - ONLY IN ADVSEARCH.PHP***********************
	 *
	 * This function exists to search on students by full name.  This is something of a hack,
	 * making up for a poorly structured database.  It takes a student name in the FirstName
	 * MiddleIn LastName format.  The results returned from SQL have a field called FullName 
	 * made up of FirstName, MiddleIn, and LastName concatenated together with spaces between
	 * them. The array it returns has IDs as assosciative keys and FullNames as values.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $name student name in the FirstName MiddleIn LastName format
	 *
	 * @return array of IDs as associative array and FullNames as values
	 */
	function studentNameSearch($sessionID, $name){
		$name = str_replace(' ', '%', $name);
		$results = array();
		for($i=0; $i<sizeof($name); $i++){
			$query = "SELECT ID, CONCAT(FIRST_NAME, ' ', MIDDLE_NAME, ' ', LAST_NAME) AS FullName FROM X_PNSY_STUDENT WHERE
					CONCAT(FIRST_NAME, ' ', MIDDLE_NAME, ' ', LAST_NAME) LIKE '%$name%'";
				$result = mysql_query($query);
				//Cycles through the rows of $result and puts them into $results in the proper format.
				for($j=0; $thisResult = mysql_fetch_assoc($result); $j++){
					if(!array_key_exists($thisResult['ID'], $results)){
						$results[$thisResult['ID']] = $thisResult['FullName'];
					}
				}
		}
		return $results;
	}
// probably ok
	/**
	 **************NOT USED ANYMORE - ONLY IN ADVSEARCH.PHP***********************
	 * 
	 * This function exists to search on staff by full name.  This is something of a hack, 
	 * making up for a poorly structured database.  It takes a staff name in the FirstName
	 * MiddleIn LastName format.  The results returned from SQL have a field called FullName
	 * made up of FirstName, MiddleIn, and LastName concatenated together with spaces between
	 * them. The array it returns has IDs as assosciative keys and FullNames as values.
	 *
 	 * @param $sessionID session information like IP address to verify for security
	 * @param $name staff name in the FirstName MiddleIn LastName format
	 *
	 * @return array of IDs as associative array and FullNames as values
	 */
	function staffNameSearch($sessionID, $name){
		$name = str_replace(' ', '%', $name);
		$results = array();
		for($i=0; $i<sizeof($name); $i++){
			$query = "SELECT ID, CONCAT(FirstName, ' ', MiddleIn, ' ', LastName) AS FullName FROM users WHERE
					CONCAT(FirstName, ' ', MiddleIn, ' ', LastName) LIKE '%$name%'";
			$result = mysql_query($query);
			//Cycles through the rows of $result and puts them into $results in the proper format.
			for($j=0; $thisResult = mysql_fetch_assoc($result); $j++){
				if(!array_key_exists($thisResult['ID'], $results)){
					$results[$thisResult['ID']] = $thisResult['FullName'];
				}
			}
		}
		return $results;
	}
	
	
	
	
	
	
	
	
	
	
	/*
	* ALL OF THE FUNCTIONS AFTER THIS WERE ADDED FOR VERSION 2
	* ENJOY.
	*/
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Give it a full name like "Firstname Middleinitial Lastname".
	 * It uses spaces as wildcards and basically does a "contains" search.
	 * Then it gives you back issues relating to any students that matched.
	 *
	 * @param $name full name in format <FirstName><MiddleInitial><LastName>
	 * @order how to order the results
	 *
	 * @return an enumerated array of issues relating to matching students, 
	 * each issue as an associative array where keys are db field names
	 */
	function issuesByStudentName( $name, $order ){
		$name = str_replace(' ', '%', $name);
		$return = array();
		$query = "SELECT distinct i.ID, i.* from issues i, X_PNSY_STUDENT s, contacts c, `contacts-students` cs WHERE
				s.ID = cs.studentid and
				cs.contactid = c.id and
				c.issue = i.id and
				CONCAT(s.FIRST_NAME, ' ', s.MIDDLE_NAME, ' ', s.LAST_NAME) LIKE '%$name%'";
// Michael Thompson * 12/07/2005 * Added Below line to tack order onto sql sentence
                if ($order != "") $query .= " ORDER BY $order";
		$result = mysql_query($query);
		while( $row = mysql_fetch_assoc($result) ) {
			array_push( $return, $row );
		}
		return $return;
	}	
	
	/**
	 * Give it a full name like "Firstname Middleinitial Lastname".
	 * It uses spaces as wildcards and basically does a "contains" search.
	 * Then it gives you back contacts relating to any students that matched.
	 *
	 * @param $name full name in format <FirstName><MiddleInitial><LastName>
	 * @order how to order the results
	 *
	 * @return an enumerated array of contacts relating to matching students, 
	 * each contact as an associative array where keys are db field names
	 */
	function contactsByStudentName( $name, $order ){
		$name = str_replace(' ', '%', $name);
		$return = array();
		$query = "SELECT distinct c.ID, c.* from X_PNSY_STUDENT s, contacts c, `contacts-students` cs WHERE
				s.ID = cs.studentid and
				cs.contactid = c.id and
				CONCAT(s.FIRST_NAME, ' ', s.MIDDLE_NAME, ' ', s.LAST_NAME) LIKE '%$name%'";
// Michael Thompson * 12/07/2005 * Added Below line to tack order onto sql sentence
                if ($order != "") $query .= " ORDER BY $order";
		$result = mysql_query($query);
		while( $row = mysql_fetch_assoc($result) ) {
			array_push( $return, $row );
		}
		return $return;
	}	
	
	/**
	 * Give it a full name like "Firstname Middleinitial Lastname".
	 * It uses spaces as wildcards and basically does a "contains" search.
	 * Then it gives you back issues relating to any users that matched.
	 *
	 * @param $name full name in format <FirstName><MiddleInitial><LastName>
	 * @order how to order the results
	 *
	 * @return an enumerated array of issues relating to matching users, 
	 * each issue as an associative array where keys are db field names
	 */
	function issuesByUserName( $name, $order){
		$name = str_replace(' ', '%', $name);
		$return = array();
		$query = "SELECT distinct i.ID, i.* from issues i, users u, contacts c WHERE
				u.id = c.Creator and
				c.issue = i.id and
				CONCAT(u.FirstName, ' ', u.MiddleIn, ' ', u.LastName) LIKE '%$name%'";
// Michael Thompson * 12/07/2005 * Added Below line to tack order onto sql sentence
                if ($order != "") $query .= " ORDER BY $order";
		$result = mysql_query($query);
		while( $row = mysql_fetch_assoc($result) ) {
			array_push( $return, $row );
		}
		return $return;
	}	
	
		/**
	 * Give it a full name like "Firstname Middleinitial Lastname".
	 * It uses spaces as wildcards and basically does a "contains" search.
	 * Then it gives you back contacts relating to any users that matched.
	 *
	 * @param $name full name in format <FirstName><MiddleInitial><LastName>
	 * @order how to order the results
	 *
	 * @return an enumerated array of contacts relating to matching users, 
	 * each contact as an associative array where keys are db field names
	 */
	function contactsByUserName( $name, $order ){
		$name = str_replace(' ', '%', $name);
		$return = array();
		$query = "SELECT distinct c.ID, c.* from users u, contacts c WHERE
				u.id = c.Creator and
				CONCAT(u.FirstName, ' ', u.MiddleIn, ' ', u.LastName) LIKE '%$name%'";
// Michael Thompson * 12/07/2005 * Added Below line to tack order onto sql sentence
                if ($order != "") $query .= " ORDER BY $order";
		$result = mysql_query($query);
		while( $row = mysql_fetch_assoc($result) ) {
			array_push( $return, $row );
		}
		return $return;
	}
	
	/**
	 * Retrieves all open issues the current user created
	 * which have not been acted on in a specified number of days.
	 *
	 * @param $dayswithoutaction number of days to check if issues inactive for that long
	 *
	 * @return array of issues inactive for that period of time
	 */
	function inactiveOpenIssuesForUser( $dayswithoutaction ) {
		$userid = $_SESSION['userid'];
		$return = array();
		
		$query = "select distinct ID, Header, AssignedTo, datediff( curdate(), LastModified ) as DaysOld from issues where 
				Creator = '$userid' and Status = 'Open' and LastModified <= date_sub( curdate(), interval $dayswithoutaction day )
			  	order by LastModified asc";
			  	
		$result = mysql_query($query);		
		
		while( $row = mysql_fetch_assoc($result) ) {
			array_push( $return, $row );
		}
		
		// END NEW QUERY
		
		return $return;
	}
	
	/**
	 * Retrieves the information necessary to create an HTML <SELECT> list of users
	 *
	 * @return associative array of users and certain fields
	 */
	function getUserSelectList() {
		$return = array();
		$query = "select ID, CONCAT(LastName, ', ', FirstName, ' ', MiddleIn, ' (', ID, ')') as Label
			  from users order by LastName";
		$result = mysql_query($query);
		while( $row = mysql_fetch_assoc($result) ) {
			array_push( $return, $row );
		}
		return $return;
	}

	/**
	 * Retrieves the information necessary to create an HTML <SELECT> list of active users
	 * who are First Watch, Privileged, or Administrators.
	 *
	 * @return associative array of users and certain fields
	 */
	function getActiveUserSelectList() {
		$return = array();
		$query = "select ID, CONCAT(LastName, ', ', FirstName, ' ', MiddleIn, ' (', ID, ')') as Label
			  from users where AccessLevel >= 8 order by LastName";
		$result = mysql_query($query);
		while( $row = mysql_fetch_assoc($result) ) {
			array_push( $return, $row );
		}
		return $return;
	}
	
	/**
	 * Assign a user to an issue and create a contact documenting the assigning
	 *
	 * @param $userToAssign user to assign to issue
	 * @param $issueId ID of issue to be assigned
	 */
	function assignUserToIssue( $userToAssign, $issueId ) {
		if($this->userCanSetIssueStatus( $userToAssign, $issueId )){
			$Modifier =  $_SESSION['userid'];
			$query = "update issues set assignedto='$userToAssign' , modifier='$Modifier'
				  where id='$issueId'";
			$return = mysql_query($query);
	// Michael Thompson * 12/13/2005 * Add user to watch list and post a contact message to the issue
			$this->watchIssue("",$issueId);
            $Description = $userToAssign.' assigned to issue '.$issueId;

			// get students involved with issue
			$query = "select distinct (studentid) from `contacts-students`
				  where contactid in (select id from contacts where issue='$issueId')";
			$result = mysql_query($query);
			$students = array();
			$i=0;
			while( $row = mysql_fetch_row($result) ) {
				$students[$i] = $row[0];
				$i++;
			}
			$students = implode(',',$students);

			//create contact
			$this->createContact( '', date('Y-m-d H:i:s'), $students, $Description, $issueId, '-1' );
			//watch issue
			$this->watchIssue($userToAssign, $issueId);
			return $return;
		}
	}
	
	/**
	 * Assign a user to a student and create a contact documenting the assigning
	 *
	 * @param $userToAssign user to assign to student
	 * @param $studentId ID of student to be assigned
	 */
	function assignUserToStudent( $userToAssign, $studentId ) {
		if($this->userCanModifyStudent($_SESSION['userid'])){
			$modifier =  $_SESSION['userid'];
			if($userToAssign == ''){
				$query = "update students set assignedto=NULL , modifier='$modifier' where studentid='$studentId'";
				$userToAssign = '(unassigned)';
			}
			else
				$query = "update students set assignedto='$userToAssign' , modifier='$modifier' where studentid='$studentId'";
	// Michael Thompson * 12/13/2005 * Add user to watch list and post a contact message to the issue
			$this->watchStudent("",$studentId);
					$Description = $userToAssign.' assigned to student '.$studentId.'.';
					$Header = $userToAssign.' assigned to student '.$studentId.'.';
			$this->createIssue( '', $Header, "Closed", date("Y-m-d H:i:s"), $studentId, $Description, "0", "B" , "Other");
			return mysql_query($query);
		}
	}
	

	/**
	 * Used to generate a report of issues.
	 * 
	 * @param $startdate - date to start report on
	 * @param $enddate - date to end report on
	 * @param $datetype - specifies how date will be used
	 * @param $statusselect - filter by this status
	 * @param $levelselect - filter by level
	 * @param $categoryselect - filter by category
	 * @param $userselect - limit to issues involving this user
	 * @param $watched - limit to issues the current user is watching
	 * @param $modifiedvalue - says which $datetype indicates issues modified in the date range
	 * @param $createdvalue - says which $datetype indicates issues created in the date range
	 * @param $notmodifiedvalue - says which $datetype indicates issues were not modified in the date range
	 *
	 * @return an enumerated array of issues, each as an associative array.
	 * The keys 'Users' and 'Students' reference elements which are enumerated arrays of associative arrays themselves.
	 * If you're having trouble making sense of this, the rule is generally that anything that is a collection will be an enumerated array.
	 * Anything that is more like an object with properties will be an associative array.
	 * This is why result sets generally end up being enumerated arrays of associative arrays.
	 */
	function getIssueReport( $startdate, $enddate, $datetype, $statusselect, 
				 $levelselect, $categoryselect, $userselect, $watched, $modifiedvalue,
				 $createdvalue, $notmodifiedvalue ) {
		
		$userid = $_SESSION['userid'];
		
		if( $datetype == $modifiedvalue ) {
			$startdatecondition = "lastmodified >=";
			$enddatecondition = "lastmodified <=";
			$and_or = "and";
		}
		else if( $datetype == $createdvalue ) {
			$startdatecondition = "datecreated >=";
			$enddatecondition = "datecreated <=";
			$and_or = "and";
		}
		else if( $datetype == $notmodifiedvalue ) {
			$startdatecondition = "lastmodified <";
			$enddatecondition = "lastmodified >";
			$and_or = "or";
		}
		
		$query  = "select ID,
			  Header,
			  Status,
			  Level,
			  Category,
			  Creator,
			  Modifier,
			  DateCreated,
			  LastModified,
			  datediff( now(), LastModified ) as DaysInactive
		   from issues where 1";
		if( $startdate && $enddate )
			$query .= " and (
					 $startdatecondition '".getMySqlDate( $startdate )." 00:00:00' $and_or
					 $enddatecondition '".getMySqlDate( $enddate )." 23:59:59'
					)";
		else if( $startdate ) $query .= " and $startdatecondition '".getMySqlDate( $startdate )." 00:00:00'";
		else if( $enddate ) $query .= " and $enddatecondition '".getMySqlDate( $enddate )." 23:59:59'";
		if( $statusselect ) $query .= " and status = '$statusselect'";
		if( $levelselect ) $query .= " and level = '$levelselect'";
		if( $categoryselect ) $query .= " and category = '$categoryselect'";
		if( $userselect ) $query .= " and id in
			(select c.issue from contacts c
			 where c.Creator = '$userselect')
		";
		if( isset($watched) ) $query .= " and id in
			(select issueid from issuewatch where userid = '$userid')";
		$query .= " order by datecreated desc";
				
		$return = array();
		$result = mysql_query( $query );
		while( $row = mysql_fetch_assoc( $result ) ) {
			array_push( $return, $row );
		}
		
		foreach( $return as $rowNumber => $row ) {
			
			// include the user it is assigned to
			$query = "select distinct u.ID,
					 concat( u.LastName, ', ', u.FirstName, ' ', u.MiddleIn ) as FullName,
					 u.Email
				  from users u, issues i
				  where u.id = i.assignedto and
				  	i.id = '".$row['ID']."'
				 ";
			$result = mysql_query( $query );
			if( $assignedTo = mysql_fetch_assoc( $result ) ) {
				$return[$rowNumber]['AssignedTo'] = $assignedTo;
			}
			else $return[$rowNumber]['AssignedTo'] = false;
			
			// include the number of contacts
			$query = "select count(*) as NumContacts from contacts
				  where issue = '".$row['ID']."'";
			$result = mysql_query( $query );
			if( $numContactsArray = mysql_fetch_assoc( $result ) ) {
				$return[$rowNumber]['NumContacts'] = $numContactsArray['NumContacts'];
			}
			else $return[$rowNumber]['NumContacts'] = false;
			
			// include all the students associated with the contacts
			$query = "select distinct s.ID,
					 concat( s.LAST_NAME, ', ', s.FIRST_NAME, ' ', s.MIDDLE_NAME ) as FullName
				  from X_PNSY_STUDENT s, `contacts-students` cs, contacts c
				  where s.ID = cs.studentid and
				  	cs.contactid = c.id
					and c.issue = '".$row['ID']."'
				 ";
			$studentsArray = array();
			$result = mysql_query( $query );
			while( $studentrow = mysql_fetch_assoc( $result) ) {
				array_push( $studentsArray, $studentrow );
			}
			$return[$rowNumber]['Students'] = $studentsArray;
			
			// include all the users associated with the contacts
			$query = "select distinct u.ID,
					 concat( u.LastName, ', ', u.FirstName, ' ', u.MiddleIn ) as FullName,
					 u.Email
				  from users u, contacts c
				  where u.id = c.Creator and
					and c.issue = '".$row['ID']."'
				 ";
			$usersArray = array();
			$result = mysql_query( $query );
			//echo mysql_error();
			while( $userrow = mysql_fetch_assoc( $result) ) {
				array_push( $usersArray, $userrow );
			}
			$return[$rowNumber]['Users'] = $usersArray;
		}
		return $return;
	}
	
	/**
	 * Used to generate a report of students.
	 * 
	 * @param $datetypeselect - specifies how date will be used
	 * @param $startdate - date to start report on
	 * @param $enddate - date to end report on
	 * @param $classyear - filter by students in this graduating class
	 * @param $residenceselect - filter by students in this residential building
	 * @param $ethnic - limit to students with this ethnic code
	 * @param $userselect - limit to students in contact with this user
	 * @param $watched - limit to students the current user is watching
	 * @param $acpro - limit to students on academic probation
	 * @param $housingwaitlist - limit to students on the housing waiting list
	 * @param $parkingwaitlist - limit to students on the parking waiting list
	 * @param $field1 - limit to students flagged by flag1
	 * @param $field2 - limit to students flagged by flag2
	 * @param $field3 - limit to students flagged by flag3
	 *
	 * @return an enumerated array of issues, each as an associative array.
	 * The keys 'Users' references an element which is an enumerated array of associative arrays itself.
	 * If you're having trouble making sense of this, read the docs for getIssueReport() above.
	 */
	function getStudentReport( $datetypeselect, $startdate, $enddate, $classyear,
				   $residenceselect, $ethnic, $userselect, $watched, $redflag, $vip,
				   $acpro, $housingwaitlist, $field1, $field2, $field3 ) {
		
		$userid = $_SESSION['userid'];
		
		if( $datetypeselect == 0 ) $left = "left";
		
		
		if( $startdate ) $datestuff .= " and c.datecreated >= '".getMySqlDate( $startdate )." 00:00:00'";
		if( $enddate ) $datestuff .= " and c.datecreated <= '".getMySqlDate( $enddate )." 23:59:59'";
		
		$query = "
			SELECT x.ID,
			       concat( x.LAST_NAME, ', ', x.FIRST_NAME, ' ', x.MIDDLE_NAME ) as FullName,
			       concat( u.LastName, ', ', u.FirstName, ' ', u.MiddleIn ) as AssignedToName,
			       s.AssignedTo,
			       x.ENROLL_STATUS,
			       x.CAMPUS_PHONE,
			       x.CELL_PHONE,
			       x.WOOSTER_EMAIL,
			       x.PRIMARY_EMAIL,
			       x.CAMPUS_BOX,
			       x.CLASS_YEAR,
			       s.AcProbation,
			       s.HousingWaitList,
			       s.Field1,
			       s.Field2,
			       s.Field3,
			       s.VIP,
			       s.RedFlag,
			       x.HOME_PHONE,
			       concat( x.HOUSING_BLDG, ' ', x.HOUSING_ROOM ) as HousingAssignment,
			       x.ETHNIC,
			       x.ADVISOR
			FROM X_PNSY_STUDENT x, students s, users u, contacts c, `contacts-students` cs
			WHERE x.ID = s.StudentID AND cs.ContactID = c.ID $datestuff AND cs.StudentID = x.ID
			AND s.StudentID IN (SELECT ID FROM X_PNSY_STUDENT WHERE ID = s.StudentID)
		";
		
		if( $classyear ) $query .= " and x.CLASS_YEAR = '$classyear'";
		if( $residenceselect ) $query .= " and x.HOUSING_BDLG = '$residenceselect'";
		if( $userselect ) $query .= " and s.assignedto = '$userselect'";
		if( isset($watched) ) $query .= " and x.ID in (select studentid from studentwatch where userid = '$userid')";
		if( isset($redflag) ) $query .= " and s.redflag != ''";
		if( isset($vip) ) $query .= " and s.vip != ''";
		if( isset($acpro) ) $query .= " and s.acprobation = 1";
		if( isset($housingwaitlist) ) $query .= " and s.housingwaitlist = 1";
		if( isset($field1) ) $query .= " and s.Field1 = 1";
		if( isset($field2) ) $query .= " and s.Field2 = 1";
		if( isset($field3) ) $query .= " and s.Field3 = 1";
		$query .= " group by x.ID order by x.LAST_NAME";
				
		$return = array();
		$result = mysql_query( $query );
		while( $row = mysql_fetch_assoc( $result ) ) {
			array_push( $return, $row );
		}
		
		foreach( $return as $rowNumber => $row ) {
			
			// include the number of contacts
			$query = "select count(*) as NumContacts from contacts
				  where id in (
				  	select distinct contactid from `contacts-students` cs
					where cs.studentid = '".$row['ID']."'
				  )";
			$result = mysql_query( $query );
			if( $numContactsArray = mysql_fetch_assoc( $result ) ) {
				$return[$rowNumber]['NumContacts'] = $numContactsArray['NumContacts'];
			}
			else $return[$rowNumber]['NumContacts'] = false;
			
			// include the number of contacts in the date range
			$query = "select count(*) as NumContactsInRange from contacts
				  where id in (
				  	select distinct c.id from contacts c
					join `contacts-students` cs
					on cs.contactid = c.id
					and cs.studentid = '".$row['ID']."'$datestuff
				  )";
			$result = mysql_query( $query );
			if( $numContactsArray = mysql_fetch_assoc( $result ) ) {
				$return[$rowNumber]['NumContactsInRange'] = $numContactsArray['NumContactsInRange'];
			}
			else $return[$rowNumber]['NumContactsInRange'] = false;
			
			// include all the users associated with the contacts
			$query = "select distinct u.ID,
					 concat( u.LastName, ', ', u.FirstName, ' ', u.MiddleIn ) as FullName,
					 u.Email
				  from users u, `contacts-students` cs, contacts c
				  where u.id = c.Creator
				  and c.ID = cs.contactid
				  and cs.studentid = '".$row['ID']."'
				  and cs.contactid = c.id$datestuff
				 ";
			$usersArray = array();
			$result = mysql_query( $query );
			while( $userrow = mysql_fetch_assoc( $result ) ) {
				array_push( $usersArray, $userrow );
			}
			$return[$rowNumber]['Users'] = $usersArray;
			
			$return[$rowNumber]['StartDate'] = $startdate;
			$return[$rowNumber]['EndDate'] = $startdate;
		}
		return $return;
	}
	
	
	/**
	 * Used to generate a report of users.
	 * 
	 * @param $datetypeselect - specifies how date will be used
	 * @param $startdate - date to start report on
	 * @param $enddate - date to end report on
	 *
	 * @return an enumerated array of issues, each as an associative array.
	 * The keys 'Students' references an element which is an enumerated array of associative arrays itself.
	 * If you're having trouble making sense of this, read the docs for getIssueReport() above.
	 *
	 * NOTE: 'Students' might not even be used in the interface, but could easily be integrated later.
	 */
	function getUserReport( $datetypeselect, $startdate, $enddate ) {
		
		$userid = $_SESSION['userid'];
		
		
		if( $datetypeselect == 0 ) $left = " left";
		
		if( $startdate ) $datestuff .= " and c.datecreated >= '".getMySqlDate( $startdate )." 00:00:00'";
		if( $enddate ) $datestuff .= " and c.datecreated <= '".getMySqlDate( $enddate )." 23:59:59'";
		
		$query = "
			select u.ID,
			       concat( u.LastName, ', ', u.FirstName, ' ', u.MiddleIn ) as FullName,
			       u.Extension,
			       u.Email
			from users u$left join contacts c on c.Creator = u.id $datestuff
		";
		
		if( $datetypeselect == 0 || $datetypeselect == 1 ) {
			if( $startdate ) $query .= " and datecreated >= '".getMySqlDate( $startdate )." 00:00:00'";
			if( $enddate ) $query .= " and datecreated <= '".getMySqlDate( $enddate )." 23:59:59'";
		}
		else if( $datetypeselect == 2 && ($startdate || $enddate) ) {
			$query .= " and (0";
			if( $startdate ) $query .= " or datecreated < '".getMySqlDate( $startdate )." 00:00:00'";
			if( $enddate ) $query .= " or datecreated > '".getMySqlDate( $enddate )." 23:59:59'";
			$query .= ")";
		}
		
		$query .= " group by u.id order by u.lastname";
				
		$return = array();
		$result = mysql_query( $query );
		while( $row = mysql_fetch_assoc( $result ) ) {
			array_push( $return, $row );
		}
		
		foreach( $return as $rowNumber => $row ) {
			// include the number of contacts
			$query = "select count(*) as NumContacts from contacts c
				  where c.Creator = '".$row['ID']."'
				  ";
			$result = mysql_query( $query );
			if( $numContactsArray = mysql_fetch_assoc( $result ) ) {
				$return[$rowNumber]['NumContacts'] = $numContactsArray['NumContacts'];
			}
			else $return[$rowNumber]['NumContacts'] = false;
					
			// include the number of contacts in the date range
			$query = "select count(*) as NumContactsInRange from contacts c
				  where c.Creator = '".$row['ID']."'$datestuff
				  ";
			$result = mysql_query( $query );
			if( $numContactsArray = mysql_fetch_assoc( $result ) ) {
				$return[$rowNumber]['NumContactsInRange'] = $numContactsArray['NumContactsInRange'];
			}
			else $return[$rowNumber]['NumContactsInRange'] = false;
			
			// include all the students associated with the contacts
			$query = "select distinct s.ID,
					 concat( s.LAST_NAME, ', ', s.FIRST_NAME, ' ', s.MIDDLE_NAME ) as FullName,
					 s.WOOSTER_EMAIL
				  from X_PNSY_STUDENT s, `contacts-students` cs, contacts c
				  where s.ID = cs.studentid
				  and c.ID = cs.contactid
				  and c.Creator = '".$row['ID']."'
				  and cs.contactid = c.id$datestuff
				 ";
			$studentsArray = array();
			$result = mysql_query( $query );
			while( $studentrow = mysql_fetch_assoc( $result ) ) {
				array_push( $studentsArray, $studentrow );
			}
			$return[$rowNumber]['Students'] = $studentsArray;
			
			$return[$rowNumber]['StartDate'] = $startdate;
			$return[$rowNumber]['EndDate'] = $startdate;
		}
		
		return $return;
	}

   /**
	 * Used to generate a report of interims.
	 * 
	 * @param $startdate - date to start report on
	 * @param $enddate - date to end report on
	 *
	 * @return an enumerated array of interims, each as an associative array.
	 * The key 'Students' references an element which is an enumerated array of associative arrays itself.
	 * If you're having trouble making sense of this, the rule is generally that anything that is a collection will be an enumerated array.
	 * Anything that is more like an object with properties will be an associative array.
	 * This is why result sets generally end up being enumerated arrays of associative arrays.
	 */
	function getInterimReport( $startdate, $enddate ){
      $userid = $_SESSION['userid'];

      $query = "select `ID` as interimid, `StudentID` as studentid, `DateProcessed` as dateprocessed, `CourseNumberTitle` as course from `interims`";

      if($startdate && $enddate) $query .= " where `DateProcessed` >= '".getMySqlDate( $startdate )."' and `DateProcessed` <= '".getMySqlDate( $enddate )."'";
      else if( $startdate ) $query .= " where `DateProcessed` >= '".getMySqlDate( $startdate )."'";
      else if( $enddate ) $query .= " where `DateProcessed` <= '".getMySqlDate( $enddate )."'";

      $query .= " order by `DateProcessed`";

      $result =  mysql_query($query);
      $interims = array();
      while( $row = mysql_fetch_assoc( $result ) ) {
			array_push( $interims, $row );
	   }
      return($interims);   
   }
	

   /**

	 * Used to generate a report on the first watch list.
	 * 
	 * @param $startdate - date to start report on
	 * @param $enddate - date to end report on
    * @param $reason - reason for being placed on first watch
    * 
    * @return array of first watch entries. each entries has the following fields:
    *         studentID, reason, add date, removal date                        
	 */
	function getFirstWatchReport( $startdate, $enddate, $reason){

      $startdate = strtotime($startdate);
      $enddate = strtotime($enddate);

      $userid = $_SESSION['userid'];

      $query = "select c.ID, c.DateCreated, c.Description from `contacts` c, `issues` i where i.Header = 'INTERIM AND FIRST WATCH HISTORY' and i.ID = c.Issue order by c.DateCreated";

      //if( $startdate ) $query .= " and c.DateCreated >= '".getMySqlDate( $startdate )."'";
      //if( $enddate ) $query .= " and c.DateCreated <= '".getMySqlDate( $enddate )."'";
      //$query .= " order by c.DateCreated";
      $result =  mysql_query($query);
   
      $fw = array();
      while( $contact = mysql_fetch_assoc( $result ) ) {

         // flag for indicating this contact is one we are looking for
         $flag = true;

         // check date
         if(strpos($contact['Description'], "Student placed on the First Watch List [") === 0){
            if(strtotime($contact['DateCreated']) > $enddate) $flag = false; 
         }
         else if(strpos($contact['Description'], "Student removed from First Watch List [") === 0){
            if(strtotime($contact['DateCreated']) < $startdate) $flag = false; 
         }
         else{ 
            $flag = false;
         }   

         // if contact checks out, see what it says
         if($flag){

            // get student id
            $query2 = "select distinct `StudentID` from `contacts-students` where `ContactID` = '".$contact['ID']."'";
            $result2 =  mysql_query($query2);
            if($result2 = mysql_fetch_assoc($result2)) $studentid = $result2['StudentID'];

            // get reason
            $offset = 38;
            $allreasons = array('Academic','Financial','Interim Reports','Medical','Possible Transfer','Personal','Watch List');
            for($i = 0; $i < count($allreasons); $i++){
               if(strpos($contact['Description'], $allreasons[$i], $offset) !== false){
                  // add an array for the student in the firstwatch array
                  if(!isset($fw[$studentid])){
                     $fw[$studentid] = array();
                  }
                  // add reason to array
                  $fw[$studentid][] = $allreasons[$i];
               }
            }
         }
	   }

      // if a reason search criteria was provided, filter the results
      if(!empty($reason)){
         foreach(array_keys($fw) as $id){
            if(array_search($reason,$fw[$id]) === false) unset($fw[$id]);
         }
      }

      // clean up array, remove duplicates,
      foreach(array_keys($fw) as $id){
         $fw[$id] = array_unique($fw[$id]);
      }

      return $fw;   
   }


   /**
	 * Used to search database for a faculty member with the given name. Since an interim can be 
    * submitted with the faculty name in various different formats, we must be careful how we 
    * search the database. Not very robust, but hopefully good enough..
	 * 
	 * @param $unformattedName - name of faculty member, as provided by the submittor
	 *
	 * @return an array containing ID Number, First Name, Last Name, and Email. If a match cannot
    *  be found, return false.
	 */
	function findInstructor($unformattedName){

      if(empty($unformattedName)) return false;

      // remove commas, periods, and such
      $toRemove = array(".",",");
      $name = str_replace($toRemove, "", $unformattedName);

      // split name into words
      $name = explode(' ',$name);
      
      // remove titles
      $titles = array("Dr","dr","Prof","prof","");
      $name = array_diff($name,$titles);

      // reset indices
      $name = array_values($name);

      // with any luck, our array only contains the first and last names, and maybe middle
      $subQuery = "SELECT DISTINCT `ID`, `FIRST_NAME`, `MIDDLE_NAME`, `LAST_NAME`, `WOOSTER_EMAIL` FROM `X_PNSY_FACULTY` WHERE ";

      if(count($name) < 2) return false;
      if(count($name) > 1){
         // FIRST LAST
         $query1 = "".$subQuery."`FIRST_NAME`='".$name[0]."' AND `LAST_NAME`='".$name[1]."'";

         // LAST FIRST
         $query2 = $subQuery."`FIRST_NAME`='".$name[1]."' AND `LAST_NAME`='".$name[0]."'";
      }
      if(count($name) > 2){
         // FIRST MIDDLE LAST
         $query3 = $subQuery."`FIRST_NAME`='".$name[0]."' AND `MIDDLE_NAME`='".$name[1]."' AND `LAST_NAME`='".$name[2]."'";

         // LAST FIRST MIDDLE
         $query4 = $subQuery."`FIRST_NAME`='".$name[1]."' AND `MIDDLE_NAME`='".$name[2]."' AND `LAST_NAME`='".$name[0]."'";
      }

      // make queries
      $result = mysql_query($query1);
      if($info = mysql_fetch_assoc($result)) return $info;
      $result = mysql_query($query2);
      if($info = mysql_fetch_assoc($result)) return $info;
      if(!empty($query3)){
         $result = mysql_query($query3);
         if($info = mysql_fetch_assoc($result)) return $info;
      }
      if(!empty($query4)){
         $result = mysql_query($query4);
         if($info = mysql_fetch_assoc($result)) return $info;
      }

      // return false if nothing works
      return false;   
   }


	/**
	 * Saves a report generated by getStudent/Issue/UserReport() for the current user
	 *
	 * @param $reporttype - student, issue or user. No more and no less than one.
	 * @param $reportname - the report will show up on reportgenerator.php as this
	 * @param $getline - the current url-encoded string, used to re-generate the report.
	 */
	function saveReportForSelf( $reporttype, $reportname, $getline ) {
		return $this->saveReportForUser( $_SESSION['userid'], $reporttype, $reportname, $getline );
	}
	
	/**
	 * Saves a report generated by getStudent/Issue/UserReport() for the specified user
	 *
	 * @param $userid - the uid of the user to save the report for.
	 * @param $reporttype - student, issue or user. No more and no less than one.
	 * @param $reportname - the report will show up on reportgenerator.php as this
	 * @param $getline - the url-encoded string of the report.  i.e., "?id=000000000&status=Something"
	 */
	function saveReportForUser( $userid, $reporttype, $reportname, $getline ) {
		$query = "insert into reports (userid, type, name, getline) 
			  values ('$userid', '$reporttype', '$reportname', '$getline')";
		$result = mysql_query( $query );
		return $result;
	}
	
	/**
	 * Deletes a saved report for the current user
	 *
	 * @param $reporttype - student, issue or user. No more and no less than one.
	 * @param $reportname - the name of the report
	 */
	function deleteReportForSelf( $reporttype, $reportname ) {
		return $this->deleteReportForUser( $_SESSION['userid'], $reporttype, $reportname );
	}
	
	/**
	 * Deletes a saved report for the specified user
	 *
	 * @param $userid - the uid of the user to delete the report for
	 * @param $reporttype - student, issue or user. No more and no less than one.
	 * @param $reportname - the name of the report
	 */
	function deleteReportForUser( $userid, $reporttype, $reportname ) {
		$query = "delete from reports where userid='$userid' and
			  type='$reporttype' and name='$reportname'";
		$result = mysql_query( $query );
		return $result;
	}
	
	/**
	 * Gets the saved reports for the current user...
	 *
	 * @return an enumerated array of associative arrays that are the report
	 */
	function getSavedReportsForSelf() {
		return $this->getSavedReportsForUser( $_SESSION['userid'] );
	}
	
	/**
	 * Gets saved reports for the specified user
	 *
	 * @param $userid - the uid of the user to get reports for
	 *
	 * @return an enumerated array of associative arrays that are the report
	 */
	function getSavedReportsForUser( $userid ) {
		$query = "select * from reports where userid='$userid' order by type";
		$return = array();
		$result = mysql_query( $query );
		while( $row = mysql_fetch_assoc( $result ) ) {
			array_push( $return, $row );
		}
		return $return;
	}
	
	/**
	 * Get a report by name for the current user
	 *
	 * @param $reporttype - student, issue or user. No more and no less than one.
	 * @param $getline - the url-encoded string of the saved report.  i.e., "?id=000000000&status=Something"
	 *
	 * @return array of reports
	 */
	function getReportNameForSelf( $reporttype, $getline ) {
		return $this->getReportNameForUser( $_SESSION['userid'], $reporttype, $getline );
	}
	
	/**
	 * Get a report by name for the specified user
	 *
	 * @param $userid - user whose reports are being retrieved
	 * @param $reporttype - student, issue or user. No more and no less than one.
	 * @param $getline - the $_GET of the saved report
	 *
	 * @return array of reports
	 */
	function getReportNameForUser( $userid, $reporttype, $getline ) {
		$query = "select Name from reports where userid='$userid'
			  and getline='$getline' limit 1";
		$result = mysql_query( $query );
		if( $row = mysql_fetch_assoc( $result ) ) {
			return $row['Name'];
		}
		else return false;
	}
	
	/**
	 * determines if the specified report is already saved for the current user
	 *
	 * @param $reporttype - student, issue or user. No more and no less than one.
	 * @param $getline - the $_GET of the saved report
	 */
	function reportExistsForSelf( $reporttype, $getline ) {
		return $this->reportExistsForUser( $_SESSION['userid'], $reporttype, $getline );
	}
	
	/**
	 * determines if the specified report is already saved for the specified user
	 *
	 * @param $userid - user whose reports are being checked
	 * @param $reporttype - student, issue or user. No more and no less than one.
	 * @param $getline - the $_GET of the saved report
	 */
	function reportExistsForUser( $userid, $reporttype, $getline ) {
		if( $this->getReportNameForUser( $userid, $reporttype, $getline ) )
			return true;
		return false;
	}
	
	/**
	 * Gets all the alerts for the current user generated by the specified issue
	 *
	 * @param $issueid - ID of issue to check for user's alerts
	 *
	 * @return array of alerts for user generated by the issue
	 */
	function getAlertsForSelfAndIssue( $issueid ) {
		return $this->getAlertsForUserAndIssue( $_SESSION['userid'], $issueid );
	}
	
	/**
	 * Gets all the alerts for the specified user generated by the specified issue
	 *
	 * @param $userid - user whose alerts are being checked
	 * @param $issueid - ID of issue to check for user's alerts
	 *
	 * @return array of alerts for user generated by the issue
	 */
	function getAlertsForUserAndIssue( $userid, $issueid ) {
		$return = array();
		$query="select * from issuealert where userid='$userid' and issueid='$issueid'";
		$result=mysql_query($query);
		while( $row = mysql_fetch_assoc($result) ) {
			$return[$i] = $row;
			$i++;
			$norows = false;
		}
		return $return;
	}
	
	/**
	 * Gets all the alerts for the current user generated by the specified student
	 *
	 * @param $studentid - ID of student to check for user's alerts
	 *
	 * @return array of alerts for user generated by the student
	 */
	function getAlertsForSelfAndStudent( $studentid ) {
		return $this->getAlertsForUserAndStudent( $_SESSION['userid'], $studentid );
	}
	
	/**
	 * Gets all the alerts for the specified user generated by the specified student
	 *
	 * @param $userid - user whose alerts are being checked
	 * @param $studentid - ID of student to check for user's alerts
	 *
	 * @return array of alerts for user generated by the student
	 */
	function getAlertsForUserAndStudent( $userid, $studentid ) {
		$return = array();
		$query="select * from studentalert where userid='$ID'";
		$result=mysql_query($query);
		while( $row = mysql_fetch_assoc($result) ) {
			$return[$i] = $row;
			$i++;
			$norows = false;
		}
		return $return;
	}
	
	/**
	 * Checks to see if the user's access level is high enough to import interims.
	 * Currently, First Watch, Privileged, and Administrative users can do this.
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 *
	 * @return whether or not the current user can create interims
	 */
	function userCanCreateInterim( $sessionID ) {
		$level == $this->getAccessLevel();
		return ($level == FIRSTWATCH || $level == PRIVILEGED || $level == $ADMINISTRATOR);
	}
	
	/**
	 * Based off of createIssue. Inserts interims into the database.
	 * Checks and alerts First Watch, Priveleged, and Administrator
	 * users if student has 3+ interims.
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 * @param $stuID - ID of student to generate interim for
	 * @param $Course - course student recieved interim for
	 * @param $Prof - professor of the course
	 * @param $Date - date interim was sent
	 * @param $Probs - reasons for interim
	 * @param $Comment - comments on the interim
	 * @param $Actions - recommended actions for the problem(s)
	 * @param $Other - other recommended actions
	 *
	 * @return ID of the interim
	 */
	function createInterim( $sessionID, $stuID, $Course, $Prof, $Date, $Probs, $Comment, $Actions, $Other ) {
		if( $this->userCanCreateInterim( $sessionID ) ) {
			if(empty($this->link)){
				echo "Not connected to database  You must instantiate the DataAccessManager before performing
					database accesses.";
					exit;	
			}
			
			// begin hack to create ID number
			$IDs = array();
			$ID = date('m').date('d').date('Y').'-';
			$result = mysql_query("SELECT * FROM interims WHERE ID like '%$ID%'");
			for($i=0; $results = mysql_fetch_assoc($result); $i++){
				$IDs[$i] = $results['ID'];
			}
			$idnumber = mysql_num_rows($result) + 1;
			while(in_array('R'.$ID.$idnumber, $IDs)){
						$idnumber++;
			}
			$ID ='R'.$ID.$idnumber;
			// end hack to create ID number

         $DateProcessed = date('Y-m-d');
			$query = "INSERT INTO interims (ID, StudentID, Date, CourseNumberTitle, Instructor, Problem, Comments, RecommendAction, OtherAction, DateProcessed)
				  values ('$ID', '$stuID', '$Date', '$Course', '$Prof', '$Probs', '$Comment', '$Actions', '$Other', '$DateProcessed')";
			mysql_query($query);
			
			$query = "SELECT InterimCounter FROM students WHERE StudentID = '".$stuID."'";
			$result = mysql_query($query);
			$value = mysql_fetch_assoc($result);
			extract($value);
			
         // increment interim counter
			$InterimCounter++;
			
			$query = "UPDATE students SET InterimCounter=".$InterimCounter." WHERE StudentID = '".$stuID."'";
			mysql_query($query);
			
			$query = "SELECT InterimCounter FROM students WHERE StudentID = '".$stuID."'";
			$result = mysql_query($query);
			$value = mysql_fetch_assoc($result);
			extract($value);    

         // add an issue/contact to show that an interim was submitted
         $description = 'Interim Report '.$ID.' submitted.';
         $this->updateSpecialHistory($stuID,$description,0);
      
         // check the interim counter
			if($InterimCounter > 2){
				$query = "UPDATE students SET FirstWatch = 1 WHERE StudentID = '".$stuID."' AND FirstWatch=0";
				mysql_query($query);
				
				$query = "SELECT * FROM `students-FW` WHERE StudentID = '".$stuID."' AND Reason='Interim Reports'";
				$result = mysql_query($query);

				if(mysql_num_rows($result) == 0){
					$query = "INSERT INTO `students-FW` (StudentID, Reason) VALUES ('".$stuID."', 'Interim Reports')";
					mysql_query($query);

               // add a contact indicating that the student was added to first watch
               $description = "Student placed on the First Watch List [".$InterimCounter." Interim Reports].";
               $this->updateSpecialHistory($stuID,$description,1);
				}
			}

			return $ID; //Yay, it works!
		}
		else {
			echo 'You do not have permission to create this interim.';
			return false; //Oh no, failure!
		}
	}
	
  
	/**
	 * Checks to see if the user's access level is high enough to delete interims.
	 * Currently, Privileged and Administrative users can do this.
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 *
	 * @return whether or not the current user can delete interims
	 */
	function userCanDeleteInterim( $sessionID ) {
		$level = $this->getAccessLevel();
		return ($level == FIRSTWATCH || $level == PRIVILEGED || $level == ADMINISTRATOR);
	}
 

	/**
	 * Deletes an interim
	 *
	 * @param $sessionID - session information like IP address to verify for security
    * @param $interimID - ID of interim to delete
	 *
	 * @return success
	 */
	function deleteInterim($sessionID, $interimId) {
		if( $this->userCanDeleteInterim( $sessionID ) ) {
			if(empty($this->link)){
			   return false;
			}			

         // first get associated student id
         $query = "select `StudentID` from `interims` where `ID`='$interimId'";
         $result = mysql_query($query);
         $studentId = mysql_fetch_assoc($result);
         $studentId = $studentId['StudentID'];

         // delete interim
         $query = "delete from `interims` where id='$interimId'";
         $result = mysql_query($query);
      
         // update interim counter
	      $query = "SELECT InterimCounter FROM students WHERE StudentID = '".$studentId."'";
			$result = mysql_query($query);
			if($value = mysql_fetch_assoc($result)){
			   extract($value);	
            // decrement interim counter
            if($InterimCounter > 0){
			      $InterimCounter--;
			      $query = "UPDATE students SET InterimCounter=".$InterimCounter." WHERE StudentID = '".$studentId."'";
			      mysql_query($query);
            }
			}

         // add an issue/contact to show that an interim was deleted
         if(!empty($studentId)){
            $description = 'Interim Report '.$interimId.' deleted.';
            $this->updateSpecialHistory($studentId,$description,0);
         }

			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Checks to see if the user's access level is high enough to edit pre-existing interims.
	 * Currently, the user must be Privileged or Administrative.
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 *
	 * @return whether or not the current user can edit interims
	 */
	function userCanEditInterim( $sessionID ) {
		$level = $this->getAccessLevel();
		return ($level == PRIVILEGED || $level == ADMINISTRATOR);
	}


   /**
	 * Edits a pre-existing interim report.
	 *
	 * @param $sessionID - session information like IP address to verify for security
    * @param $interimID - ID of interim to edit
	 * @param $stuID - ID of student to generate interim for
	 * @param $Course - course student recieved interim for
	 * @param $Prof - professor of the course
	 * @param $Date - date interim was sent
	 * @param $Probs - reasons for interim
	 * @param $Comment - comments on the interim
	 * @param $Actions - recommended actions for the problem(s)
	 * @param $Other - other recommended actions
	 *
	 * @return true or false
	 */
	function editInterim( $sessionID, $interimId, $stuID, $Course, $Prof, $Date, $Probs, $Comment, $Actions, $Other ) {
		if( $this->userCanEditInterim( $sessionID ) ) {
			if(empty($this->link)){
			   return false;
			}			

         $query = "UPDATE `interims` SET 
                     StudentID='".$stuID."',
                     Date='".$Date."',
                     CourseNumberTitle='".$Course."',
                     Instructor='".$Prof."',
                     Problem='".$Probs."',
                     Comments='".$Comment."',
                     RecommendAction='".$Actions."',
                     OtherAction='".$Other."' 
                     WHERE ID = '".$interimId."'"; 

         $result = mysql_query($query);

			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Checks to see if user is permitted to view interims.
	 * Currently, a user must be Full Read-only, First Watch,
	 * Privileged or an Administrator
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 *
	 * @return - whether the user can view interims
	 */
	function userCanViewInterim( $sessionID ) {
		$level = $this->getAccessLevel();
		return ($level == READONLYFULL || $level == FIRSTWATCH || $level == PRIVILEGED || $level == ADMINISTRATOR);
	}

	/**
	 * Checks to see if user is permitted to send interims as emails. 
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 *
	 * @return - whether the user can send interims
	 */
	function userCanSendInterim( $sessionID ) {
		return ($this->userCanViewInterim(''));
	}

	/**
	 * Pulls up all information related to interim reports and returns the data.
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 * @param @interimID - ID of interim being queried
	 *
	 * @return - array of interim information
	 */
	function viewInterim( $sessionID, $interimID ) {
		//@session_start();
		if( $this->userCanViewInterim( $sessionID ) ) {
			$query="SELECT * FROM interims WHERE ID = '$interimID'";
			//echo $query."<br>";
			$result = mysql_query($query);
			$interim = mysql_fetch_array($result);
			
			return $interim;
		}
	}
	
	/**
	 * Checks whether a user is permitted to retrieve student interims.
	 *
	 * @param... 
	 * I'm not even bothering. This function is incredibly messed up.
	 */
	function userCanGetStudentInterims( $studentID ) {
		return $this->userCanViewStudent('');
	}
	
	// Calls viewInterim once the interim IDs are selected.
	function getStudentInterims( $studentID ) {
		//should be userCanGetStudentInterims( $studentID ), but that function's wrong
		//and this one makes more sense anyway
		if( $this->userCanViewInterim( '' ) ) {
			$query = "SELECT ID FROM interims WHERE StudentID = $studentID";
			$result = mysql_query($query);
			$return = array();
			while( $row = mysql_fetch_assoc($result) ) {
				if( $this->userCanViewInterim('') ) {
					array_push( $return, $this->viewInterim('',$row['ID']) );
				}
			}
			return $return;
		}
	}
	
	/**
	 * Checks to see if the user is permitted to view the First Watch list.
	 * Currently, a user must be above level 7
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 *
	 * @return - whether the user can view first watch list
	 */
	function userCanViewFW( $sessionID ) {
		return $this->userCanViewInterim($sessionID);
	}
	
	/**
	 * Checks to see if the user is permitted to modify the First Watch list.
	 * Currently, First Watch, Privileged, and Administrative users can do this.
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 *
	 * @return - whether the user can view first watch list
	 */
	function userCanModifyFW( $sessionID ) {
		$level = $this->getAccessLevel();
		return ($level == FIRSTWATCH || $level == PRIVILEGED || $level == ADMINISTRATOR);
	}

	/**
	 * Pulls in data related to students on First Watch.
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 *
	 * @return - array of students on first watch
	 */
	function viewFW ( $sessionID ) {
		if($this->userCanViewFW( $sessionID ))
		{
			$query = "SELECT x.ID, x.FIRST_NAME, x.LAST_NAME, f.Reason FROM `students` s, `X_PNSY_STUDENT` x, `students-FW` f WHERE s.FirstWatch=1 AND s.StudentID = f.StudentID AND s.StudentID = x.ID ORDER BY x.LAST_NAME, f.Reason";
			$result = mysql_query($query);
			
			return $result;
		}
	}

	/**
	 * Determines whether or not a student is on First Watch
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 *

	 * @return - array of students on first watch
	 */
	function studentOnFW ( $sessionID, $studentID) {
		if($this->userCanViewFW( $sessionID ))
		{
			if(!empty($studentID)){
				$this->verifyStudent($studentID);
				$query="select Reason from `students-FW` where StudentID='$studentID'";
				$result=mysql_query($query);
				//return $result;
				if (mysql_num_rows($result) == 0) return false;
				else
				{
					$return=array();
					while($row=mysql_fetch_assoc($result)){
						array_push($return,$row['Reason']);
					}
					return $return;
				}
					//return $result;
					//return mysql_fetch_assoc($result);
			}//return true;


			/*$query = "SELECT Reason FROM students-FW WHERE StudentID = $studentID";
			$result = mysql_query($query);
			$return = array();
			while( $row = mysql_fetch_assoc($result) ) {
				if( $this->userCanViewFW('') ) {
					array_push( $return, $row );
				}

			}
			return $return;*/

			/*$query = "SELECT x.ID, x.FIRST_NAME, x.LAST_NAME, f.Reason FROM `students` s, `X_PNSY_STUDENT` x, `students-FW` f WHERE s.FirstWatch=1 AND s.StudentID = f.StudentID AND s.StudentID = x.ID ORDER BY x.LAST_NAME, f.Reason";
			$result = mysql_query($query);*/
			//echo $result;
			
			//return $result;
		}
	}
	
   /** 
    * Create and update an interim report / first watch history for each student for whom this is a concern.
    * History is in a contact/issue format. There is only one issue with the title INTERIM AND FIRST WATCH HISTORY.
    * All activity is appended to this issue as contacts. Such activity includes, but is not limited to, when and why 
    * a student is added or removed from the first watch list, or when a student receives an interim.
    *
    * @param $studentId - id number or student
    * @param $description - text describing the latest activity. Ex: "student removed from first watch"
    *                       or "student added to first watch because..."
    * @param $timeOffset - kludge to space out contacts from the same issue that are submitted at nearly the same time
                           can be 0 (no offset), or 1 (one second offset). The reason for this option is so that when
                           the issue displays, contacts are sure to be in chronological order.
    * @return $issueId - id number of the issue containing the special history
    */
   function updateSpecialHistory($studentId, $description, $timeOffset){
      
      // add an issue/contact to show there has been recent first watch activity for student
      $modifier =  $_SESSION['userid'];
      $header = 'INTERIM AND FIRST WATCH HISTORY';
      if($timeOffset > 0) $date = date("Y-m-d H:i:s",time()+1);
      else $date = date("Y-m-d H:i:s");

      // check to see if there is already an issue with this header
      $query = "select distinct i.ID from issues i, contacts c, `contacts-students` cs
	               where cs.studentid = '$studentId'
	               and cs.contactid = c.id
	               and c.issue = i.id
                  and i.Header = '$header'
                  order by i.DateCreated asc";
      $result = mysql_query($query);
      if($result = mysql_fetch_assoc($result)){
         $issueId = $result['ID'];
         if(!empty($issueId)){
            // there is already an issue in the system with the header
            // no need to add another
            $this->createContact( '', $date, $studentId, $description, $issueId, '-1');
         }
      }
      else{
         // there is no issue in the system with the header
         // we should add one!
         $issueId = $this->createIssue( '', $header, "Closed", $date, $studentId, $description, "0", "B" , "Other");
      }

      return $issueId;
   }   
   

	/**
	 * Allows user to remove students from First Watch (single or multiple reasons).
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 * @param $studentID - ID of student to take off first watch
	 * @param $Reason - reason why student was on first watch (in case on for multiple reasons
	 *					and only taken off for one of those reasons)
	 */
	function clearStudentFW ( $sessionID, $studentID, $Reason ) {
		if($this->userCanModifyFW($sessionID)) {

         // add a contact indicating that the student was removed
         $description = "Student removed from First Watch List [".$Reason."].";
         $this->updateSpecialHistory($studentID,$description,0);   

         // update db
			$query = "DELETE FROM `students-FW` WHERE StudentID = '".$studentID."' AND Reason= '".$Reason."'";
			mysql_query($query);		
			$query = "SELECT * FROM `students-FW` WHERE StudentID = '".$studentID."'";
			$result = mysql_query($query);
			
			if(mysql_num_rows($result) == 0)
			{
				$query = "UPDATE students SET FirstWatch = 0 WHERE StudentID = '".$studentID."'";
				mysql_query($query);
			}
		}
	}
	
	/**
	 * Completely removes students from First Watch (all reasons).
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 * @param $studentID - ID of student being removed from First Watch
	 */
	
	function clearStudentAllFW ( $sessionID, $studentID ) {
		if($this->userCanModifyFW($sessionID)) {

         // get reasons why student is on list
         $query = "SELECT DISTINCT `Reason` FROM `students-FW` WHERE `StudentID` = '".$studentID."'";
	 $result = mysql_query($query);
         $reasons = "";
         while($temp = mysql_fetch_assoc($result)){
            if(!empty($reasons)) $reasons .= ", ";
            $reasons .= $temp['Reason'];            
         }

         // add a contact indicating that the student was removed
         $description = "Student removed from First Watch List [".$reasons."].";
         $this->updateSpecialHistory($studentID,$description,0);   

         // update db
			$query = "DELETE FROM `students-FW` WHERE `StudentID` = '".$studentID."'";
			mysql_query($query);
			$query = "UPDATE `students` SET `FirstWatch` = 0 WHERE `StudentID` = '".$studentID."'";
			mysql_query($query);
		}
	}
	
	
	/**
	 * place student on first watch for reason other than interims
	 * 
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID student to place on first watch
	 * @param $FWReason reason why student is on first watch
	 */
	function placeOnFirstWatch($sessionID, $studentID, $FWReason){
		if($this->userCanModifyFW( $sessionID )){
         $query = "UPDATE students SET FirstWatch=1";
         mysql_query($query);
			$query = "insert into `students-FW` (StudentID, Reason)
					values ('$studentID','$FWReason')";		  
			mysql_query($query);

         // add a contact indicating the student was added to first watch and why
         $description = "Student placed on the First Watch List [".$FWReason."].";
         $this->updateSpecialHistory($studentID, $description, 0);
		}
	}
	
	/**
	 * Checks to see if the user is an Administrator and allowed to clear the counter.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 *
	 * @return whether the user can clear the interim counter
	 */
	function userCanClearInterimCounter( $sessionID ) {
		return $this->getAccessLevel() == ADMINISTRATOR;
	}
	
	/**
	 * Sets the interim counter to 0 for all students.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 */
	function clearInterimCounter( $sessionID ) {
		if($this->userCanClearInterimCounter( $sessionID )) {
			$query = "UPDATE students SET InterimCounter=0";
			mysql_query($query);
			
			$date = date('F j, Y');
			
			$query = "UPDATE datecleared SET LastClear='".$date."'";
			mysql_query($query);
		}
	}
	
	/**
	 * Sets the interim counter to 0 for a specified student.
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $ID ID of student to set counter to 0 for
	 */
	function clearIndividualCounter ( $sessionID, $ID ) {
		if($this->userCanClearInterimCounter( $sessionID )) {
			$query = "UPDATE students SET InterimCounter=0 WHERE StudentID='$ID'";
			mysql_query($query);
		}
	}
	
	/**
	 * Checks to see if the user is an administrator and can change the various stored e-mails.
	 *
	 * @param $sessionID - session information like IP address to verify for security

	 */
	function userCanChangeEmails( $sessionID ) {
		return $this->getAccessLevel() == ADMINISTRATOR;
	}
	
	/**
	 * Updates the e-mail addresses for the Writing Center, Math Center, and Learning Center.
	 *
	 * @param $sessionID - session information like IP address to verify for security
	 * @param $Email1 - full e-mail address for contact for Writing Center
	 * @param $Email2 - full e-mail address for contact for Learning Center
	 * @param $Email3 - full e-mail address for contact for Math Center
	 */
	function changeEmails( $sessionID, $Email1, $Email2, $Email3 ) {
		if( $this->userCanChangeEmails( $sessionID ) ) {

         if($Email1 == "CLEAR" || $Email1 == "clear"){
            $query = "UPDATE emails SET WritingCenter=''";
				mysql_query($query);
         }
			else if($Email1 != "") {
				$query = "UPDATE emails SET WritingCenter='".$Email1."'";
				mysql_query($query);
			}

         if($Email2 == "CLEAR" || $Email2 == "clear"){
            $query = "UPDATE emails SET LearningCenter=''";
				mysql_query($query);
         }
			else if($Email2 != "") {
				$query = "UPDATE emails SET LearningCenter='".$Email2."'";
				mysql_query($query);
			}

         if($Email3 == "CLEAR" || $Email3 == "clear"){
            $query = "UPDATE emails SET MathCenter=''";
				mysql_query($query);
         }
			else if($Email3 != "") {
				$query = "UPDATE emails SET MathCenter='".$Email3."'";
				mysql_query($query);
			}
		}
	}
	
	/**
	 * Policy implementation. Returns true if the user has permission to perform the
	 * action, false if not. 
	 *
	 * @param $sessionID session information like IP address to verify for security
	 * @param $studentID the ID of the student to view
	 */
	function userCanViewSchedule( $sessionID ){
		return $this->userCanViewStudent($sessionID);
	}
	
	/**
	 * Returns a students complete schedule from the database.
	 *
	 * @param $studentID the ID of the student to look up the schedule for
	 */
	 function viewStudentSchedule($studentID){
	 	if($this->userCanViewSchedule('')){

			$course = array();
			$schedule = array();
			
			$query = "select course_id from `X_PNSY_SCHEDULE` where student_id = '$studentID'";
			$result = mysql_query($query);
			
			if(mysql_num_rows($result) == 0){
				return;
			}
			
			while ($row = mysql_fetch_assoc($result)){
				$courseID =	$row['course_id'];		
				
            // course information	
				$query = "select * from `X_PNSY_COURSE` where course_id = '$courseID'";
				$courseInfo = mysql_fetch_assoc(mysql_query($query));
				
            // retrieve all the course days/times. note: there can be more than one.
				$query = "select * from `X_PNSY_COURSE_DAYS` where course_id = '$courseID'";
            $daysResult = mysql_query($query);
            $meetingInfo = array();
            while($dayRow = mysql_fetch_assoc($daysResult)){
				   $meetingInfo[] = $dayRow;
            }				

            // retrieve prof
				$facultyID = $courseInfo['FACULTY_ID'];
            if(!empty($facultyID)){
   				$query = "select * from `X_PNSY_FACULTY` where id = '$facultyID'";
   				$facultyInfo = mysql_fetch_assoc(mysql_query($query));
				}

				// parse time of day. remember, there can be more than one day/time
            $courseDateInfo = '';
            for($i = 0; $i < count($meetingInfo); $i++){
               if(!empty($meetingInfo[$i]['DAYS'])){
				      $startTime[$i] = explode(' ',$meetingInfo[$i]['START_TIME']);
				      $startTime[$i] = explode(':',$startTime[$i][1]);
				      $startTime[$i] = $startTime[$i][0].':'.$startTime[$i][1];
				      $endTime[$i] = explode(' ',$meetingInfo[$i]['END_TIME']);
				      $endTime[$i] = explode(':',$endTime[$i][1]);
				      $endTime[$i] = $endTime[$i][0].':'.$endTime[$i][1];

                  if($i > 0) $courseDateInfo .= '<br>';
            
                  $courseDateInfo .= $meetingInfo[$i]['DAYS'].', '.$startTime[$i].'-'.$endTime[$i].', '.$meetingInfo[$i]['COURSE_BLDG'].' '.$meetingInfo[$i]['COURSE_ROOM'];
               }				
            }

				unset($course);	
            $course['id'] = 'n/a';
            $course['title'] = 'n/a';
            $course['info'] = 'n/a';
            $course['credits'] = 'n/a';
            $course['faculty'] = 'n/a';
            $course['email'] = 'n/a';
            $course['phone'] = 'n/a';

				$course['id'] = $courseID;
				if(!empty($courseInfo['TITLE'])) $course['title'] = $courseInfo['TITLE'];
            if(!empty($courseDateInfo)) $course['info'] = $courseDateInfo;
				if(!empty($courseInfo['CREDITS'])) $course['credits'] = $courseInfo['CREDITS'];
            if(!empty($facultyID)){
				   $course['faculty'] = $facultyInfo['FIRST_NAME'].' '.$facultyInfo['LAST_NAME'];
				   if(!empty($facultyInfo['WOOSTER_EMAIL'])) $course['email'] = $facultyInfo['WOOSTER_EMAIL'];
               if(!empty($facultyInfo['CAMPUS_PHONE'])) $course['phone'] = $facultyInfo['CAMPUS_PHONE'];
            }
				
				$schedule[] = $course;
			}
			
			return $schedule;
		}
	 }
	 
	 /**
	  * Retrieves profile pic for a student
	  *
	  * @param $studentID the ID of the student to look up photo of
	  *
	  * @return photo of the student or default if no picture available
	  */
	 /*function getProfilePicture($studentID){
		global $file_upload_folder;
		if($this->userCanViewStudent('')){
	 		$target = $file_upload_folder.'profile_pics/';
	 		$ext = 'jpg,jpeg,png,gif,tif';
	 		$ext = explode(',',$ext);
	 		
	 		// check for picture with all possible extensions
	 		for($i=0; $i<count($ext); $i++){
	 			if(file_exists($target.$studentID.'.'.$ext[$i])){
	 				return $target.$studentID.'.'.$ext[$i];
	 			}
			}
	 	}
		return $target.'no_photo_available.jpg';
	 }*/
	 
	 /**
	  * Retrieves the LastModified date and ID for each non-closed issue that a user has.
	  *
	  * @param $userID - the ID of the user that is logging in.
	  *
	  * @return - results of the query for comparison to the current date.
	  */
	 function getLastModifedIssue( $userID ) {
	 	$query = "SELECT LastModified, ID FROM issues WHERE Creator = '$userID' AND Status = 'Open'";
	 	$result = mysql_query($query);
	 	
	 	return $result;
	 }
	 
	 /**
	  * Closes issues older than 2+ years when a user logs in.
	  *
	  * @param $ID - ID for the issue being closed.
	  * @param $userID - the ID of the user that is logging in.
	  * @param $LastModified - the date the issue was previously last modified, so that the field
	  *						   isn't updated to the current time by the query.
	  */
	 function setOldIssuesClosed( $ID, $userID, $LastModified ) {
	 	$query = "UPDATE issues SET Status = 'Closed', LastModified = '$LastModified' WHERE Creator = '$userID' AND ID = '$ID'";
	 	mysql_query($query);
	 }
	 
	 /**
	  * Retrieves the three manually-set flags from the flags table.
	  *
	  * @return - associative array of results from the query.
	  */
	 function extractFlags() {
	 	$query = "SELECT Option1, Option2, Option3 FROM flags";
		$result = mysql_query($query);
		$value = mysql_fetch_assoc($result);
		
		return $value;
	}
	
	/**
	 * Gets the e-mail addresses for the Writing Center, Math Center, and Learning Center.
	 *
	 * @return - list of e-mails for the center
	 */
	function selectEmails() {
		$query = "SELECT * FROM emails";
		$result = mysql_query($query);
		$value = mysql_fetch_assoc($result);
		
		return $value;
	}
	
	/**
	 * Selects students whose interim counter is 3+.
	 *
	 * @return - list of information for students.
	 */
	function preClearInterimsIndividual() {
		$query = "SELECT ss.ID, ss.FIRST_NAME, ss.LAST_NAME, s.InterimCounter FROM students s, X_PNSY_STUDENT ss WHERE s.InterimCounter > 0 AND s.StudentID = ss.ID AND s.StudentID IN (SELECT ID FROM X_PNSY_STUDENT WHERE ID = s.StudentID) ORDER BY ss.LAST_NAME";
		
		return mysql_query($query);
	}
	
	/**
	 * Gets the date the All Clear for interim counters was last pressed.
	 *
	 * @return - Date of last clear.
	 */
	function getLastClear() {
		$query = "SELECT LastClear FROM datecleared";
		$result = mysql_query($query);
		$value = mysql_fetch_assoc($result);
		
		return $value;
	}
	
	/**
	 * Code that resets a field's value if it is being switched and changes the names of the fields.
	 *
	 * @param $F1 - string with the first field name.
	 * @param $F2 - string with the second field name.
	 * @param $F3 - string with the third field name.
	 */
	function updateFlags( $F1, $F2, $F3 ) {
		if($F1 != ""){
			$query = "UPDATE students SET Field1=0";
			mysql_query($query);
			
			$test = '';
			if(strcmp($F1, "remove") == 0){
				$query = 'UPDATE flags SET Option1="'.$test.'"';
			}
			else{
				$query = 'UPDATE flags SET Option1="'.$F1.'"';
			}
			mysql_query($query);
		}
		if($F2 != ""){
			$query = "UPDATE students SET Field2=0";
			mysql_query($query);
			
			$test = '';
			if(strcmp($F2, "remove") == 0){
				$query = 'UPDATE flags SET Option2="'.$test.'"';
			}
			else{
				$query = 'UPDATE flags SET Option2="'.$F2.'"';
			}
			mysql_query($query);
		}
		if($F3 != ""){
			$query = "UPDATE students SET Field3=0";
			mysql_query($query);
			
			$test = '';
			if(strcmp($F3, "remove") == 0){
				$query = 'UPDATE flags SET Option3="'.$test.'"';
			}
			else{
				$query = 'UPDATE flags SET Option3="'.$F3.'"';
			}
			mysql_query($query);
		}
	}
	
	/**
	 * Checks to see if a username already exists in the system.
	 *
	 * @param $userID - username to be checked against the system.
	 * @return - result of query.
	 */
	function getUserLookup( $userID ) {
		$query= "SELECT * FROM users WHERE ID = '$ID'";
		$result = mysql_query($query);
		
		return $result;
	}
	
	/**
	 * Grabs some information for viewing interims.
	 *
	 * @param $StudentID - ID of the student whose data is being retrieved.
	 * @return - Returns the result of the query, namely relevant student information.
	 */
	function preInterimInformation( $StudentID ) {
		$query = "SELECT FIRST_NAME, MIDDLE_NAME, LAST_NAME, WOOSTER_EMAIL, CLASS_YEAR FROM X_PNSY_STUDENT WHERE ID='".$StudentID."'";
		$result = mysql_fetch_assoc(mysql_query($query));
		
		return $result;
	}
	
	/**
	 * Grabs the email from the specified email field.
	 *
	 * @param $field - string with the name of the field being queried.
	 * @return - associative array with the email address for the field.
	 */
	function selectIndividualEmail( $field ) {
		$query = "SELECT $field FROM emails";
		$result = mysql_query($query);
		$value = mysql_fetch_assoc($result);
		
		return $value;
	}
	
	/**
	 * Grabs the full name of a user for displaying in Student Reports.
	 *
	 * @param $userID - user ID that the name is being looked up for.
	 * @return - full name of the user that was passed in.
	 */
	function getAssignedToName( $userID ) {
		$query = "SELECT concat(LastName, ', ', FirstName) as FullName FROMslowly turning into lyrics users WHERE ID = '$userID'";
		$result = mysql_query($query);
		$value = mysql_fetch_assoc($result);
		extract($value);
		
		return $FullName;
	}

	/**
	 * Does some basic security testing on emails.
	 *
	 */
	function validateEmail($TO,$SUBJECT,$BODY,$headers){

      if(!$this->getAccessLevel() != NOACCESS) {echo 'false'; return false;}

      //$server = "http://localhost/dev/pansophy"; // dev server
      $server = "http://pansophy.wooster.edu";
      $referer = $_SERVER['HTTP_REFERER'];
		
      // check if server string is a substring of the referer string
      $pos = strpos($referer,$server);

      if($pos === false)
         return false;
      else if($pos == 0)
         return true;
      else 
         return false;
	}

   
	/**
	 * Creates a contact showing that an interim email has been sent to student.
	 *
	 */
	function createInterimEmailContact($interimId){

      // get student id
      $query = "select `StudentID` from `interims` where `ID`='$interimId'";
      $result = mysql_query($query);
		$value = mysql_fetch_assoc($result);
      if(!isset($value['StudentID'])) return false;
      $studentId = $value['StudentID'];

      // submit special contact showing that an email has been sent for the given interim
      $description = "Email sent to student concerning interim ".$interimId;
      $this->updateSpecialHistory($studentId,$description,0);
   }
	/**
	 * Modifies the database to reflect an incorrect login attempt (used for password lockout feature)
	 *
	 * @param $user - userID of user who attempted to log in
	 * 
	 */
	function failedLoginAttempt($user){
		$query = "SELECT LoginAttempts from users where ID='$user'";
		$result = mysql_query($query);
		$value = mysql_fetch_assoc($result);
		if (!isset($value['LoginAttempts'])) $attempts=0;
		else $attempts=$value['LoginAttempts'];
		$attempts++;
		$timeval = time();
		$query = "UPDATE users SET LoginAttempts=$attempts, LastLogin=$timeval WHERE ID='$user'";
		mysql_query($query);
	}

	/**
	 * Modifies the database to reflect a correct login attempt (used for password lockout feature)
	 *
	 * @param $user - userID of user who logged on
	 * 
	 */
	function successfulLoginAttempt($user){
		$timeval = time();
		$query = "UPDATE users SET LoginAttempts=0, LastLogin=$timeval WHERE ID='$user'";
		mysql_query($query);
	}

	/**
	 * Queries the database to find out if the current user is locked out of the system (used for password lockout feature)
	 *
	 * @param $user - userID of user who is attempting to log in
	 * @return - boolean value: "true" is user is locked out, "false" is user is not.
	 */
	function isLockedOut($user){
		$query = "SELECT LoginAttempts, LastLogin from users where ID='$user'";
		$result = mysql_query($query);
		$value = mysql_fetch_assoc($result);
		$attempts = $value['LoginAttempts'];
		$lasttime = $value['LastLogin'];
		$thistime = time();
		$timearith = $thistime - $lasttime;
		if( ($thistime - $lasttime) > 300){
			$query = "UPDATE users SET LoginAttempts=0, LastLogin=$thistime WHERE ID='$user'";
			mysql_query($query);
			return false;
			}
		else{
			if($attempts > 3){
				return true;
				}
			else{
				return false;
				}
			}
		}
	/**
 	This function works by examining every single student whose class year occurs on or before the input $year. Each student's associated issues and contacts are examined. If a potential archivee student is found to be associated with a student who should not be archived, then the potential archivee will not be archived. Additionally, if a student's enroll status code is not one of the approved codes, then the student will not be archived. If neither of these things are true, then the student is added to the archived database. In a later segment of the code, the students' associated contacts, issues, attachments, and contact-student assocations are added to the archived database.
	 */
	function archiveYear($year){
		//debug
		error_reporting(E_ALL);
		ini_set("display_errors", 1);
		// 
		$studentquery='select * from `X_PNSY_STUDENT` where `CLASS_YEAR` <= "'.$year.'"';
		// list of enroll status codes for students we shouldn't archive
		$approvedstring="EM CS OP OC LM DP RE DD LP LA FE FY TR";
		$studentsresults = mysql_query($studentquery);
		$students = mysql_fetch_assoc($studentsresults);
		while ($students) // iterate through selected students
		{// code that works
			$contactquery = 'select * from `contacts-students` where StudentID ="'.$students['ID'].'"';
			$contactresults = mysql_query($contactquery);
			$contacts = mysql_fetch_assoc($contactresults);
			$iscurrent = false;
			if(strpos($approvedstring, $students['ENROLL_STATUS']) !== FALSE) $iscurrent = true;
			while ($contacts and !$iscurrent) // iterate through associated contacts
			{
				$contactID = $contacts['ContactID'];
				$stuIDquery= 'select `StudentID` from `contacts-students` where `ContactID`="'.$contactID.'"';
				$stuIDresults=mysql_query($stuIDquery);
				$stuID=mysql_fetch_assoc($stuIDresults);
				while($stuID and !$iscurrent) // iterate through other students involved with said contacts
				{
					$checkstuquery='select * from `X_PNSY_STUDENT` where ID="'.$stuID.'"';
					$checksturesults=mysql_query($checkstuquery);
					$checkstu=mysql_fetch_assoc($checksturesults);
					if(strpos($approvedstring, $checkstu['ENROLL_STATUS']) !== FALSE) $iscurrent=true;
					$stuID=mysql_fetch_assoc($stuIDresults);
					$lasterr = mysql_error();
					if (strcmp($lasterr,"") != 0) echo "error on line 4468: ".$lasterr."</br>";
				}
				$contacts = mysql_fetch_assoc($contactresults);
			}
					if (!$iscurrent){ // archive student info if student is not current.
							// It is important to note that all students must be archived before
							// archiving any associated contacts, issues, etc. due to foreign key constraints.
						foreach($y as $key => $s)
							{
								if (empty($s)) unset($y[$key]);
								else $s=addslashes(htmlspecialchars($s));
							}
						$stukey = '`'.implode('`,`',array_keys($y)).'`';
						$stuval = '"'.implode('","',array_values($y)).'"';
						$squerystring='insert into `pansophyhistorical`.`X_PNSY_STUDENT` ('.$stukey.') values('.$stuval.')';
						mysql_query($squerystring);
						//mysql_query("delete from `X_PNSY_STUDENTS` where ID=".$students['ID']);	
					}
					$students = mysql_fetch_assoc($studentsresults);
		} // end code that works
			$studentquery = "select * from `pansophyhistorical`.`X_PNSY_STUDENT`";
			foreach(/* PLACEHOLDER-- in the below code, $i is an iterator for an array of contact IDs for all contacts to be archived */){
			// This entire section of code needs to be redone. It should archive all relevant issues, contacts, contact-student associations, and attachments( in that order_
				if ($i === FALSE) continue;
				$issueIDquery = 'select `Issue` from `contacts` where ID="'.$i.'"';
				$issueIDresult = mysql_query($issueIDquery);
				$issueID = mysql_fetch_assoc($issueIDresult);
				$issuequery = 'select * from `issues` where ID="'.$issueID['Issue'].'"';
				$issueresult= mysql_query($issuequery);
				//debug
				$lasterr = mysql_error();
				if (strcmp($lasterr,"") != 0) echo "error on line 4493: ".$lasterr."</br>";
				//
				$issue = mysql_fetch_assoc($issueresult);
				if ($issue !== FALSE)
				{
					foreach($issue as $key => $s)
					{
						if (empty($s)) unset($issue[$key]);
						else $s=addslashes(htmlspecialchars($s));
					}
					$issuekey = '`'.implode('`,`',array_keys($issue)).'`';
					$issueval = '"'.implode('","',array_values($issue)).'"';
					$issueinsert='insert into `pansophyhistorical`.`issues` ('.$issuekey.') values('.$issueval.')';
					mysql_query($issueinsert);
					echo $issueinsert;
				}
				//debug'insert into `pansophyhistorical`.`contacts-students` (ContactID, StudentID) values ("'.$constu['ContactID'].'","'.$constu['StudentID'].'")'
				$lasterr = mysql_error();
				if (strcmp($lasterr,"") != 0) echo "error on line 4503: ".$lasterr."</br>";
				//
				//mysql_query('delete from `issues` where ID ="'.$issue['ID'].'"');
				$attachquery = 'select * from `attachments` where `ContactID`="'.$i.'"';
				$attachresult = mysql_query($attachquery);
				//debug
				$lasterr = mysql_error();
				if (strcmp($lasterr,"") != 0) echo "error on line 4511: ".$lasterr."</br>";;
				//
				$attach = mysql_fetch_assoc($attachresult);
				if ($attach !== FALSE)
				{
					foreach($attach as $key => $s)
					{
						if (empty($s)) unset($attach[$key]);
						else $s=addslashes(htmlspecialchars($s));
					}
					$attachkey = '`'.implode('`,`',array_keys($attach)).'`';
					$attachval = '"'.implode('","',array_values($attach)).'"';
					mysql_query('insert into `pansophyhistorical`.`attachments` ('.$attachkey.') values ('.$attachval.')');
					//mysql_query('delete from `attachments` where ID ="'.$attach['ID'].'"');
				}
				$nucontactquery='select * from `contacts` where ID="'.$i.'"';
				$nucontactresult=mysql_query($nucontactquery);
				//debug
				$lasterr = mysql_error();
				if (strcmp($lasterr,"") != 0) echo "error on line 4524: ".$lasterr."</br>";
				//
				$nucontact = mysql_fetch_assoc($nucontactresult);
				if ($nucontact !== FALSE)
				{
					foreach($nucontact as $key => $s)
					{
						if (empty($s)) unset($nucontact[$key]);
						else $s=addslashes(htmlspecialchars($s));
					}
					$contactkey = '`'.implode('`,`',array_keys($nucontact)).'`';
					$contactval = '"'.implode('","',array_values($nucontact)).'"';
					$nucontactinsert= 'insert into `pansophyhistorical`.`contacts` ('.$contactkey.') values('.$contactval.')';
					mysql_query($nucontactinsert);
				}
				//debug
				$lasterr = mysql_error();
				if (strcmp($lasterr,"") != 0) echo "error on line 4535: ".$lasterr."</br>";
				//
				//mysql_query('delete from `contacts` where ID="'.$nucontact['ID'].'"');
				$constuquery='select * from `contacts-students` where ContactID="'.$i.'"';
				$consturesult=mysql_query($constuquery);
				//debug
				$lasterr = mysql_error();
				if (strcmp($lasterr,"") != 0) echo "error on line 4542: ".$lasterr."</br>";
				//
				$constu=mysql_fetch_assoc($consturesult);
				while($constu)
				{
					foreach($constu as $s)
					{
						$s=addslashes(htmlspecialchars($s));
					}
					$constuinsert = 'insert into `pansophyhistorical`.`contacts-students` (ContactID, StudentID) values ("'.$constu['ContactID'].'","'.$constu['StudentID'].'")';
					mysql_query($constuinsert);
					//debug
					$lasterr = mysql_error();
					if (strcmp($lasterr,"") != 0) echo "error on line 4554: ".$lasterr."</br>";
					//
					//mysql_query('delete from `contacts-students` where ContactID=".$constu['ContactID']." and StudentID="'.$constu['StudentID'].'"');
					$constu=mysql_fetch_assoc($consturesult);
				}
			}
			

	}
	
}

?>
