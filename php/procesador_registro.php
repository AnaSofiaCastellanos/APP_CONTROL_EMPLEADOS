<?php
    if(isset($_POST["registrarse"])){
        require_once("conexion_db.php");

        $nombre=$_POST["nombre"];
        $apellido=$_POST["apellido"];
        $tipoDoc=$_POST["tipoDoc"];
        $numDoc=$_POST["numDoc"];
        $telefono=$_POST["telefono"];
        $cargo=$_POST["cargo"];
        $contrasena=$_POST["contrasena"];
        $contrasena=md5($contrasena);
        $estadoSolicitud="No";

        $query_registro="INSERT INTO empleados (Nombre, Apellido, Tipo_Doc, Num_doc, Cargo,Id_estado, Contrasena, Telefono, confirmar_Pago_Sueldo) VALUES ('$nombre', '$apellido',
        '$tipoDoc', '$numDoc', '$cargo','2', '$contrasena', '$telefono','$estadoSolicitud')";
        $result_query=mysqli_query($conexion_db, $query_registro);

        if($result_query==true){
            echo "<script> 
                alert('Registro exitoso');

                window.location='../login.html';
                </script>";
        }else{
            echo "<script> 
                alert('Error a la hora de registrar sus datos');
                window.location='../registro.html';
            </script>";
        }

    }
?>