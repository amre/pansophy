<?
session_start();
if ((isset($_SESSION['lastclick'])) and (time() - $_SESSION['lastclick'] > 10800))
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
?>
