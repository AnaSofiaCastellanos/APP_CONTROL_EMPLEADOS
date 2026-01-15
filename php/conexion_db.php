<?php
    $user="root";
    $host="localhost";
    $name_db="control_empleados";
    $password="";

    $conexion_db=mysqli_connect($host, $user,$password, $name_db);
    if(!$conexion_db){
        echo "Error a la hora de conectar con la base de datos";
    }
?>