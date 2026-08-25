<?php
$host="localhost"; 
$user="root"; 
$pass=""; 
$banco="ifruit"; 
<<<<<<< HEAD
$conn=mysqli_connect($host, $user, $pass , $banco); 
mysqli_select_db($conn, $banco); 
=======
 $conn=mysqli_connect($host, $user, $pass , $banco); 
 mysqli_select_db($conn, $banco); 
 // alias for legacy pages expecting $con
 $con = $conn;
>>>>>>> minhas-alteracoes
?>