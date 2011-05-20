<?php

/**
 * Logs out a user.
 */
 
session_start();
session_destroy();
echo '<meta http-equiv="Refresh" content="0; URL=./index.php">';
?>
