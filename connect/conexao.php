<?php
$host="localhost"; 
$user="root"; 
$pass=""; 
$banco="ifruit"; 
$conn=mysqli_connect($host, $user, $pass , $banco); 
mysqli_select_db($conn, $banco); 
?>