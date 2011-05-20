<html>
<head>
<title>test page</title>
</head>
<body>
<form action="michael.php" method="POST" target="_self">

<?php 
$header = '';
if(isset($_POST['header'])) $header = $_POST['header']; 
echo $header;
echo '<br>';
echo '<input type="text" name="header" value="'.$header.'">';
?>

</form>
</body>
</html>
