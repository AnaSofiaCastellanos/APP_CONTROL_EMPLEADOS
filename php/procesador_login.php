<?php 
    if(isset($_POST["iniciarSesion"])){
        session_start();
        include("conexion_db.php");

        $numDoc=$_POST["numDoc"];
        $contrasena=$_POST["contrasena"];
        $contrasena=md5($contrasena);

        $_SESSION["documento"]=$numDoc;
        $_SESSION["contrasena"]=$contrasena;

        include("funciones.php");

        $resultadoAdmins=obtenerDatosAdmin($numDoc,$contrasena, $conexion_db);
        $resultadoEmpleados=obtenerDatosUsuario($numDoc, $contrasena,$conexion_db);
        if (mysqli_num_rows($resultadoAdmins)){
            echo "<script> 
                window.location='home_admins.php';
            </script>";

        }else if(mysqli_num_rows($resultadoEmpleados)){
            echo "<script> 
                window.location='home_Empleados.php';
            </script>";
        }else{
            echo "<script> 
                alert('La información ingresada es incorrecta')
                window.location='../login.html';
            </script>";
        }
    }
?>