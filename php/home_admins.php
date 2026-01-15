<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Administradores</title>
    <link rel="stylesheet" href="../css/homeAdmins_style.css">
</head>
<body>
    <?php 
        session_start();
        include("funciones.php");
        include("conexion_db.php");

        $documentoAdmin=$_SESSION["documento"];
        $contrasenaAdmin=$_SESSION["contrasena"];

        $resultadoDatosAdmin=obtenerDatosAdmin($documentoAdmin,$contrasenaAdmin,$conexion_db);
        if(mysqli_num_rows($resultadoDatosAdmin)){
            $datosAdmin=$resultadoDatosAdmin->fetch_array();
        }
    ?>
    <div class="contenedor_principal">
        <div class="nombreUsuario">Administrador/a <?php echo $datosAdmin["Nombre"];?></div>
        <div class="cerrarSesion"><a href="cerrar_Sesion.php">Cerrar Sesión</a></div>
    </div>
    <div class="tituloListado">Listado de empleados</div>
    <div class="contenedor_listado">
        <form method="get" action="home_admins.php">
            <table>
                <thead>
                    <th class="encabezado">Nombre completo</th>
                    <th>Cargo</th>
                    <th>Estado</th>
                    <th>Número de documento</th>
                </thead>
                <tbody>
                    <?php
                    $resultadoLista=listadoEmpleados($conexion_db);
                    

                    while($listaEmpleados=mysqli_fetch_array($resultadoLista)){
                        ?>
                        <tr>
                           <td><?php $nombreCompleto=$listaEmpleados["Nombre"] ." ". $listaEmpleados["Apellido"];
                           echo $nombreCompleto;?></td>
                           <td><?php echo $listaEmpleados["Cargo"] ?></td>
                           <?php $idEstado=$listaEmpleados["Id_estado"];
                            $descripcionEstado=recuperarDescripcionEstados($conexion_db, $idEstado);
                           ?>
                           <td><?php echo $descripcionEstado?></td>
                           <?php $numDocumento=$listaEmpleados['Num_doc'] ?>
                           <td><a href="home_admins.php?idE=<?php echo $numDocumento?>"><input type="submit" value="<?php echo $numDocumento?>" name="idE" id="idE">Más información</a>
                        
                        </tr>
                    <?php
                    }    
                    if(isset($_GET["idE"])){
                        $empleado=$_GET["idE"];
                        $_SESSION["empleado"]= $empleado;
                        echo "<script> 
                            window.location='informe_Empleado.php';
                        </script>";
                    }        
                    ?>
                </tbody>

            </table>
        </form>
    </div>
    
    
</body>
</html>