<?php
    $host = "localhost";
    $user = "root";
    $clave = "1234";
    $bd = "biometrico";
    $conexion = mysqli_connect($host,$user,$clave,$bd);
    if (mysqli_connect_errno()){
        echo "No se pudo conectar a la base de datos";
        exit();
    }
    mysqli_select_db($conexion,$bd) or die("No se encuentra la base de datos");
    mysqli_set_charset($conexion,"utf8");
	
	$hostw = "190.151.107.22";
    $userw = "eugbiometrico";
    $clavew = "admin2510";
    $bdw = "biometrico";
    $conexionw = mysqli_connect($hostw,$userw,$clavew,$bdw);
    if (mysqli_connect_errno()){
        echo "No se pudo conectar a la base de datos";
        exit();
    }
    mysqli_select_db($conexionw,$bdw) or die("No se encuentra la base de datos");
    mysqli_set_charset($conexionw,"utf8");
?>
