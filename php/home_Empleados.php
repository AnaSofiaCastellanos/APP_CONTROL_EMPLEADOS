<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Empleados</title>
    <link rel="stylesheet" href="../css/homeEmpleados_style.css">

</head>
<body>
    <?php 
        session_start();
        include("funciones.php");
        include("conexion_db.php");

        $documentoUsuario=$_SESSION["documento"];
        $contrasenaUsuario=$_SESSION["contrasena"];

        $resultado=obtenerDatosUsuario($documentoUsuario, $contrasenaUsuario,$conexion_db);

        $informacionUsuario=vectorDatosUsuario($resultado);
        $confirmarPago=$informacionUsuario["confirmar_Pago_Sueldo"];
    ?>

    <div class="contenedor_principal">
        <div class="contenedor_registro">
            <form method="post" action="home_empleados.php">
                <table>
                    <tbody>
                        <th>
                            <div class="nombreUsuario">Bienvenido/a <?php echo $informacionUsuario["Nombre"];?></div>
                        </th>
                        <tr><td><button type="submit" id="inicioJornada" name="inicioJornada">Iniciar</button></td></tr>
                        <tr><td><div class="cerrarSesion"><a href="cerrar_Sesion.php">Cerrar Sesión</a></div></td></tr>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
    <div class="contenedor_mensajes">
        <table>
            <?php 
            date_default_timezone_set("America/Bogota");

            if (isset($_POST["inicioJornada"])){

                $resultadoVerificarFinJornada=verificarFinJornada($documentoUsuario, $conexion_db);

                if(mysqli_num_rows($resultadoVerificarFinJornada)){
                    echo "<th><div class='mensaje'>Tiene un jornada laboral activa, le solicitamos terminarla</th></div>";?>
                    <div>
                        <form method="post" action="home_empleados.php">
                            <table>
                                 <tr><td><button type="submit" id="finJornada" name="finJornada">Terminar jornada</button></td></tr>
                            </table>
                        </form>
                    </div>
                    <?php
                }else{
                    $fechaIngreso=date("Y-m-d");
                    $horaIngreso=date("H:i a");

                    $resultadoIniciarJornada=iniciarJornada($fechaIngreso,$horaIngreso,$documentoUsuario, $conexion_db);

                    if($resultadoIniciarJornada==True){
                        echo "<th><div class='mensaje'>Su jornada laboral acaba de empezar</th></div>";

                        $resultadoEmpleadosActivos=obtenerDatosEmpleadosActivos($documentoUsuario, $conexion_db);
                        $resultadoVectorDatos=datosEmpleadosInicioJornada($resultadoEmpleadosActivos);
                        

                        $resultadoIdIngreso=$resultadoVectorDatos["Id_ingreso"];
                       
                        $_SESSION["identificacionInicioJornada"]=$resultadoIdIngreso;
                        ?>
                        <div>
                            <form method="post" action="home_empleados.php">
                                <table>
                                    <tr><td><button type="submit" id="finJornada" name="finJornada">Terminar jornada</button></td></tr>
                                </table>
                            </form>
                        </div>
                        <?php
                    }else{
                        echo "<tr><td><div class='mensaje'>Error al iniciar su jornada</td></tr></div>";
                    }

                }
                
            }else{
                echo "<tr><td><div class='mensaje'>No ha iniciado su jornada laboral</td></tr></div>";
            }
            $horaActual=date('H:i');
            if (isset($_POST["finJornada"])){
                $fechaSalida=date("Y-m-d");
                $horaSalida=date("H:i a");
                $finJornadaLaboral="12:00:00";
                
                if($horaSalida<$finJornadaLaboral){
                    echo "<tr><td><div class='mensaje'>Aún no ha terminado su jornada laboral</td></tr></div>";
                    ?>
                    <div>
                        <form method="post" action="home_empleados.php">
                            <table>
                                <tr><td><button type="submit" id="finJornada" name="finJornada">Terminar jornada</button></td></tr>
                            </table>
                        </form>
                    </div>
                    <?php 
                }else{
                    $resultadoTerminarJornada=terminarJornada($horaSalida,$documentoUsuario,$conexion_db,$fechaSalida);
                    if($resultadoTerminarJornada==True){
                        echo "<tr><td><div class='mensaje'>Hora de salida registrada $horaSalida</td></tr></div>";

                        $resultadoSegundosTrabajados=calcularSegundosTrabajados($documentoUsuario,$conexion_db, $fechaSalida);
                        $horasTrabajadas=floor($resultadoSegundosTrabajados/3600);

                        $IdIngreso=$_SESSION["identificacionInicioJornada"];

                    
                        if($horasTrabajadas<1){//El empleado trabajo menos de una hora
                            $minutosTrabajados=convertirHorasAMinutos($resultadoSegundosTrabajados);
                            $valor=53;
                            $minutosMinimosDia=720;
                            $resultadoFinalDia=valorHoras($IdIngreso,$minutosTrabajados, $valor,$minutosMinimosDia, $documentoUsuario, $conexion_db, $fechaSalida);

                            echo "<tr><td><div class='mensaje'>El día de hoy haz acumulado $minutosTrabajados minutos que equivalen a $$resultadoFinalDia</td></tr></div>";
                        }else{//El empleado trabajo mas de una hora
                            $valor=3200;
                            $horasMinimasDia=12;
                            $horasTrabajadas=floor($resultadoSegundosTrabajados/3600);
                            $resultadoFinalDia=valorHoras($IdIngreso,$horasTrabajadas, $valor, $horasMinimasDia,$documentoUsuario, $conexion_db, $fechaSalida);
                           
                            echo "<tr><td><div class='mensaje'>El día de hoy haz acumulado $horasTrabajadas horas que equivalen a $$resultadoFinalDia</td></tr></div>";
                        }
                    }else{
                        echo "<tr><td><div class='mensaje'>Error al terminar su jornada</td></tr></div>";
                    }
                }
            }else if($horaActual=='12:30:00'){//Si el empleado olvida terminar la jornada laboral
                $fechaSalida=date("Y-m-d");
                $horaSalida=$fechaActual; //La hora de salida del empleado sera 30 minutos despues del fin de la jornada laboral pactada

                $resultadoTerminarJornada=terminarJornada($horaSalida,$documentoUsuario,$conexion_db,$fechaSalida);
                if($resultadoTerminarJornada==True){
                    echo "<tr><td><div class='mensaje'>Hora de salida registrada $horaSalida</td></tr></div>";

                    $resultadoSegundosTrabajados=calcularSegundosTrabajados($documentoUsuario,$conexion_db, $fechaSalida);
                    $horasTrabajadas=floor($resultadoSegundosTrabajados/3600);


                    $IdIngreso=$_SESSION["identificacionInicioJornada"];

                    
                    if($horasTrabajadas<1){//El empleado trabajo menos de una hora
                        $minutosTrabajados=convertirHorasAMinutos($resultadoSegundosTrabajados);
                        $valor=53;
                        $minutosMinimosDia=720;
                        $resultadoFinalDia=valorHoras($IdIngreso,$minutosTrabajados, $valor,$minutosMinimosDia, $documentoUsuario, $conexion_db, $fechaSalida);
                            
                        echo "<tr><td><div class='mensaje'>El día de hoy haz acumulado $minutosTrabajados minutos que equivalen a $$resultadoFinalDia</td></tr></div>";
                    }else{//El empleado trabajo mas de una hora
                        $valor=3200;
                        $horasMinimasDia=12;
                        $horasTrabajadas=floor($resultadoSegundosTrabajados/3600);
                        $resultadoFinalDia=valorHoras($IdIngreso,$horasTrabajadas, $valor, $horasMinimasDia,$documentoUsuario, $conexion_db, $fechaSalida);
                           
                        echo "<tr><td><div class='mensaje'>El día de hoy haz acumulado $horasTrabajadas horas que equivalen a $$resultadoFinalDia</td></tr></div>";
                    }
                }else{
                    echo "<tr><td><div class='mensaje'>Error al terminar su jornada</td></tr></div>";
                }
            }
            ?>
        </table>
    </div>
    <?php if($confirmarPago=="En proceso"){ ?>
        <div class="contenedor_mensajes">
            <form method="post" action="home_Empleados.php">
                <table>
                    <th><div class='mensaje'><b>Solicitud de pago de salario</b></div></th>
                    <tr><td><div class="mensaje">El gerente del establecimiento, le solicita confirmar que usted acepta y recibe el pago de su salario</div></td></tr>
                    <tr>
                        <td><button type="submit" id="confirmarSolicitud" name="confirmarSolicitud">Confirmar</button></td>
                        <td><button type="submit" id="denegarSolicitud" name="denegarSolicitud">Denegar</button></td>
                    </tr>
                    <?php
                        if(isset($_POST["confirmarSolicitud"])){
                            $estadoSolicitud="Si";
                            $resultadoSolicitud=solicitudPagarSueldo($documentoUsuario,$conexion_db, $estadoSolicitud);
                                            
                            if($resultadoSolicitud==True){
                                ?>
                                <tr><td><div class='mensajeEspecial'>Solicitud de pago confirmada</div></td></tr>
                                <?php
                            }
                        }
                        if(isset($_POST["denegarSolicitud"])){
                            $estadoSolicitud="No";
                            $resultadoSolicitud=solicitudPagarSueldo($documentoUsuario,$conexion_db, $estadoSolicitud);

                            if($resultadoSolicitud==True){
                                ?>
                                <tr><td><div class='mensajeEspecial'>Solicitud de pago denegada</div></td></tr>
                                <?php
                            }
                        }?>
                </table> 
            </form> 
        </div>
    <?php } ?>
</body>
</html>