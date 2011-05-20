<?php

/**
 * Clears alerts for a user.
 */

include('../include/header.inc'); include('../DataAccessManager.inc.php');
$dam = new DataAccessManager();
$dam->clearUserAlerts();
echo '<meta http-equiv="Refresh" content="0; URL=../index.php">';
?>
