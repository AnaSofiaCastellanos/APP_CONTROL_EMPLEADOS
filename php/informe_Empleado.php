<?php 
    session_start();
    include("funciones.php");
    include("conexion_db.php");
    
    $documentoUsuario=$_SESSION["documento"];
    $contrasenaUsuario=$_SESSION["contrasena"];

    $documentoEmpleado=$_SESSION["empleado"];

    $queryDatosUsuario="SELECT * FROM empleados WHERE Num_doc='$documentoEmpleado'";
    $resultadoDatosUsuario=mysqli_query($conexion_db, $queryDatosUsuario);

    $informacionUsuario=vectorDatosUsuario($resultadoDatosUsuario);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información <?php echo $informacionUsuario["Nombre"];?> </title>
    <link rel="stylesheet" href="../css/informe_style.css">
</head>
<body>
    <div class="contenedor_principal">
        <div class="titulo"><h1>Información básica del empleado</h1></div>
        <div class="regresarMenu"><a href="home_admins.php">Regresar al menú</a></div>
    </div>
    <div class="contenedor_informe">
        <table>
            <thead>
                <th>Nombres</th>
                <th>Apellidos</th>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $informacionUsuario["Nombre"];?></td>
                    <td><?php echo $informacionUsuario["Apellido"];?></td>
                </tr>
                <tr>
                    <td><b>Tipo de documento</b></td>
                    <td><b>Número de documento</b></td>
                </tr>
                <tr>
                    <td><?php echo $informacionUsuario["Tipo_Doc"];?></td>
                    <td><?php echo $documentoEmpleado?></td>
                </tr>
                <tr>
                    <td><b>Teléfono</b></td>
                    <td><b>Cargo</b></td>
                </tr>
                <tr>
                    <td><?php echo $informacionUsuario["Telefono"];?></td>
                    <td><?php echo $informacionUsuario["Cargo"];?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="contenedor_principal">
        <div class="titulo"><h1>Siguimiento laboral del empleado</h1></div>
     </div>
    <div class="contenedor_informe">
        <table>
            <?php
                $resultadoEstado=0;
                $resultadoEstado=verificarEstadoEmpleado($documentoEmpleado, $conexion_db);

                if($resultadoEstado==1){
                    $estado_Empleado="Activo";?>
                    <th><b>Estado actual del empleado</b></th>
                    <th class="estadoActivo"><?php echo $estado_Empleado?></th>
                    <th></th>
                    <?php
                        date_default_timezone_set("America/Bogota");
                        $fechaActual=date("Y-m-d");
                        $horaActual=date("H:i");

                        $resultadoEmpleadosActivos=obtenerDatosEmpleadosActivos($documentoEmpleado, $conexion_db);
                        $resultadoVectorDatos=datosEmpleadosInicioJornada($resultadoEmpleadosActivos);

                        $horaIngresoActual=$resultadoVectorDatos["Hora_ingreso"];
                        
                        $resultadoSegundosDiaTrabajados=calcularSegundosTrabajadosDia($horaIngresoActual, $horaActual,$fechaActual);
                            
                        $horasTrabajadas=floor($resultadoSegundosDiaTrabajados/3600);

                        if($horasTrabajadas<1){//El empleado trabajo menos de una hora
                            $minutosTrabajados=convertirHorasAMinutos($resultadoSegundosDiaTrabajados);
                            $valor=53;
                            $resultadoFinalDia=$minutosTrabajados*$valor;
                            ?>
                            <tr>
                                <td><b>Minutos laborales actuales</b></td>
                                <td><b>Valor día</b></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><?php echo $minutosTrabajados." minutos" ?></td>
                                <td><?php echo "$".$resultadoFinalDia ?></td>
                                <td></td>
                            </tr>
                            <?php
                        }elseif($horasTrabajadas>=1){//El empleado trabajo mas de una hora
                            $valor=3200;
                            $horasTrabajadas=floor($resultadoSegundosDiaTrabajados/3600);
                            $resultadoFinalDia=$horasTrabajadas*$valor;
                            ?>
                            <tr>
                                <td><b>Horas laborales actuales</b></td>
                                <td><b>Valor día</b></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><?php echo $horasTrabajadas. "horas" ?></td>
                                <td><?php echo "$".$resultadoFinalDia ?></td>
                                <td></td>
                            </tr>
                            <?php
                        }

                }elseif($resultadoEstado==2){
                    $estado_Empleado="Inactivo";
                    ?>
                    <tr>
                        <td><b>Estado actual del empleado</b></td>
                        <td class="estadoInactivo"><?php echo $estado_Empleado?></td>
                        <td></td>
                    </tr>
                    <?php  
                }
            ?>
                <tr>
                    <td><b>Tiempo laboral acumulado</b></td>
                    <td><b>Total a pagar</b></td>
                    <td></td>
                </tr>
                <?php 
                    $totalHorasAcumuladas="$". 0;
                    $horasLaboralesAcumuladas=0;
                    $resultadoSegundosAcumulados=calcularSegundosTrabajadosAcumulados($documentoEmpleado,$conexion_db);

                    $horasTrabajadas=floor($resultadoSegundosAcumulados/3600);

                    if($horasTrabajadas<1){//El empleado trabajo menos de una hora
                        $minutosTrabajados=convertirHorasAMinutos($resultadoSegundosAcumulados);
                        $horasLaboralesAcumuladas=$minutosTrabajados. " minutos";
                            
                    }else{//El empleado trabajo mas de una hora
                        $horasLaboralesAcumuladas=$horasTrabajadas." horas";
                    }
                    $resultadoPago=valorHorasAcumuladas($documentoEmpleado, $conexion_db);
                ?>
                <tr>
                    <?php 
                    $queryDatosSueldo="SELECT Tiempo_acumulado, Sueldo FROM empleados WHERE Num_doc='$documentoEmpleado'";
                    $resultadoDatosSueldo=mysqli_query($conexion_db, $queryDatosSueldo);

                    if(mysqli_num_rows($resultadoDatosSueldo)){
                        $datosSueldo=$resultadoDatosSueldo->fetch_array();
                        $tiempoLaboral=$datosSueldo["Tiempo_acumulado"];
                        $sueldo=$datosSueldo["Sueldo"];
                    }
                    ?>
                    <td><?php echo $tiempoLaboral?></td>
                    <td><?php echo "$".$sueldo?></td>
                    <?php 
                    actualizarSueldoEmpleado($resultadoPago, $horasLaboralesAcumuladas, $documentoEmpleado, $conexion_db);
                    if($resultadoPago!=0){?>
                        <form action="informe_Empleado.php" method="post">
                            <td><button type="submit" id="realizarPago" name="realizarPago">Realizar pago</button></td>
                        </form>
                        <?php
                        if(isset($_POST["realizarPago"])){
                            $confirmarPago=$informacionUsuario["confirmar_Pago_Sueldo"];

                            if ($confirmarPago=="No"){
                                $estadoSolicitud="En proceso";
                                $resultadoSolicitud=solicitudPagarSueldo($documentoEmpleado,$conexion_db, $estadoSolicitud);

                                if($resultadoSolicitud==True){
                                    echo "<tr><td><div class='mensaje'>Estamos esperando su confirmación de pago...</div></td></tr>";
                                }
                            }else if($confirmarPago=="En proceso"){
                                $estadoSolicitud="En proceso";
                                $resultadoSolicitud=solicitudPagarSueldo($documentoEmpleado,$conexion_db, $estadoSolicitud);

                                if($resultadoSolicitud==True){
                                    echo "<tr><td><div class='mensaje'>Estamos esperando su confirmación de pago...</div></td></tr>";
                                }
                            }else if($confirmarPago=="Si"){
                                $fechaPago=date("Y-m-d");
                                $resultadoPagar=pagarSueldoEmpleado($fechaPago,$sueldo, $documentoEmpleado,$conexion_db);

                                if($resultadoPagar==True){?>
                                    <meta http-equiv="refresh" content="1"> <!--Etiqueta HTML para actualizar la pagina actual-->
                                    <?php 
                                    echo "<tr><td><div class='mensaje'>Pago realizado exitosamente</div></td></tr>";
                                    $estadoSolicitud="No";
                                    $resultadoReiniciarSolicitud=solicitudPagarSueldo($documentoEmpleado,$conexion_db, $estadoSolicitud);
                                }
                            }
                        }
                    }
                    ?>
                </tr>
        </table> 
    </div>
</body>
</html>