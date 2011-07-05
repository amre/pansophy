<?php
/*
* Session expiration after 3 hours of inactivity-- automatically logs off inactive users.
*
*/
session_start();
if ((isset($_SESSION['lastclick'])) and (time() - $_SESSION['lastclick'] > 10800) and (!file_exists("./login.php")))
	{
	session_unset();
	echo '<html><body><script type="text/javascript">';
	if (file_exists('./logout.php'))
		{
			echo 'top.location.href="./logout.php"';
		}
	else
		{
			echo 'top.location.href="../logout.php"';
		}
	echo '</script></body></html>';
	}
else
	{
	$_SESSION['lastclick']=time();
	}
if (!isset($_SESSION['userid']) and !file_exists("./login.php"))
{
	header("Location: http://wooster.edu");
}

?>
