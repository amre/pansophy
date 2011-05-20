<?php

/** 
 * Deletes a saved report.
 */

include( "../DataAccessManager.inc.php" );

$dam = new DataAccessManager();

$dam->deleteReportForSelf( $_GET['type'], $_GET['name'] );

echo "<meta http-equiv='Refresh' content='0; URL=./reports.php'>";

?>