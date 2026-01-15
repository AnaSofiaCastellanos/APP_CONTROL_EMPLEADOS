<?php
    //Funcion para recuperar los datos de los empleados
    function obtenerDatosUsuario($numUsuario, $contrasenaUsuario, $conexion_base){
        $queryDatosUsuario="SELECT * FROM empleados WHERE Num_doc='$numUsuario' AND Contrasena='$contrasenaUsuario'";
        $resultadoDatosUsuario=mysqli_query($conexion_base, $queryDatosUsuario);
        return $resultadoDatosUsuario;
    }

    //Funcion para recuperar los datos de los administradores
    function obtenerDatosAdmin($numUsuario, $contrasenaUsuario, $conexion_base){
        $queryDatosAdmins="SELECT * FROM admins WHERE Num_doc='$numUsuario' AND Contrasena='$contrasenaUsuario'";
        $resultadoDatosAdmins=mysqli_query($conexion_base, $queryDatosAdmins);
        return $resultadoDatosAdmins;
    }

    //Funcion para guardar los datos del empleado en un vector
    function vectorDatosUsuario($resultado_query){
        if(mysqli_num_rows($resultado_query)){
            $datosUsuario=$resultado_query->fetch_array();
            return $datosUsuario;
        }
    }

    //Funcion para iniciar la jornada de los empleados
    function iniciarJornada($fecha, $hora, $identidadEmpleado, $conexion_base){
        $queryRegistrarJornada="INSERT INTO ingreso_empleados (Fecha, Num_doc, Hora_ingreso) VALUES ('$fecha','$identidadEmpleado', '$hora')";
        $resultadoRegistrarJornada=mysqli_query($conexion_base, $queryRegistrarJornada);

        if($resultadoRegistrarJornada==True){
            $queryEmpleadoActivo="UPDATE empleados SET Id_estado='1' WHERE Num_doc='$identidadEmpleado'";
            $resultadoEmpleadoActivo=mysqli_query($conexion_base, $queryEmpleadoActivo);

            if ($resultadoEmpleadoActivo==True){
                $queryIngresoActivo="UPDATE ingreso_empleados SET Id_estado_ingreso='1' WHERE Num_doc='$identidadEmpleado' AND Hora_salida='00:00:00'";
                $resultadoIngresoActivo=mysqli_query($conexion_base, $queryIngresoActivo);

                if($resultadoIngresoActivo==True){
                    $queryDiaNoPago="UPDATE ingreso_empleados SET Id_estado_valor_dia='2' WHERE Num_doc='$identidadEmpleado' AND Hora_salida='00:00:00'";
                    $resultadoDiaNoPago=mysqli_query($conexion_base, $queryDiaNoPago);

                    return $resultadoDiaNoPago;
                }
            }
        }
    }

    //Funcion para terminar la jornada de los empleados
    function terminarJornada($horaSalida, $identidadEmpleado, $conexion_base, $fecha){
        $queryTerminarJornada="UPDATE ingreso_empleados SET Hora_salida='$horaSalida' WHERE Num_doc='$identidadEmpleado' AND Fecha='$fecha' AND Hora_salida='00:00:00'";
        $resultadoTerminarJornada=mysqli_query($conexion_base, $queryTerminarJornada);
        
        if($resultadoTerminarJornada==True){
            $queryEmpleadoInactivo="UPDATE empleados SET Id_estado='2' WHERE Num_doc='$identidadEmpleado'";
            $resultadoEmpleadoInactivo=mysqli_query($conexion_base, $queryEmpleadoInactivo);

            if($resultadoEmpleadoInactivo==True){
                $queryIngresoInactivo="UPDATE ingreso_empleados SET Id_estado_ingreso='2' WHERE Num_doc='$identidadEmpleado' AND Id_estado_ingreso='1'";
                $resultadoIngresoInactivo=mysqli_query($conexion_base, $queryIngresoInactivo);

                return $resultadoIngresoInactivo;
            }
        }
    }

    //Funcion para crear una lista con los empleados registrados en el sistema
    function listadoEmpleados($conexion_base){
        $queryListaEmpleados="SELECT * FROM empleados";
        $resultadoListaEmpleados=mysqli_query($conexion_base, $queryListaEmpleados);
        return $resultadoListaEmpleados;
    }

    //Funcion para recuperar la descripcion del id de cada empleado
    function recuperarDescripcionEstados($conexion_base, $estado){
        $queryEstados="SELECT * FROM estado_empleado WHERE Id_estado='$estado'";
        $resultadoEstados=mysqli_query($conexion_base, $queryEstados);

        if(mysqli_num_rows($resultadoEstados)){
            $estados=$resultadoEstados->fetch_array();
            return $estados["Descripcion"];
        }
    }

    //Funcion para calcular los segundos trabajados en un dia por un empleado que ya termino su jornada laboral (dia)
    function calcularSegundosTrabajados($identidadEmpleado,$conexion_base, $fecha){
        $queryEmpleadosRegistrados="SELECT * FROM ingreso_empleados WHERE Num_doc='$identidadEmpleado' AND Fecha='$fecha'";
        $resultadoEmpleadosRegistrados=mysqli_query($conexion_base, $queryEmpleadosRegistrados);

        $totalSegundosHoraInicio=0;
        $totalSegundosHoraFin=0;

        while($empleadosRegistrados=mysqli_fetch_array($resultadoEmpleadosRegistrados)){
            $horaInicioJornada=$empleadosRegistrados["Hora_ingreso"];
            $horaFinJornada=$empleadosRegistrados["Hora_salida"];

            $segundosHoraInicio=strtotime($horaInicioJornada);
            $segundosHoraFin=strtotime($horaFinJornada);
        }
        $segundosTrabajados=$segundosHoraFin-$segundosHoraInicio;
        return $segundosTrabajados;
    }
    
    //Funcion para calcular los segundos trabajados en un dia para un empleado que aun no ha terminado su jornada laboral (dia)
    function calcularSegundosTrabajadosDia($horaIngreso, $horaActual, $fechaActual){
        $segundosHoraInicio=strtotime($horaIngreso);
        $segundosHoraFin=strtotime($horaActual);

        $segundosTrabajados=$segundosHoraFin-$segundosHoraInicio;
        return $segundosTrabajados;
    }

    //Funcion para calcular los segundos trabajados en total por el empleado (acumulados)
    function calcularSegundosTrabajadosAcumulados($identidadEmpleado, $conexion_base){
        $queryEmpleadosRegistrados="SELECT * FROM ingreso_empleados WHERE Num_doc='$identidadEmpleado' AND Id_estado_ingreso='2' AND Id_estado_valor_dia='2'";
        $resultadoEmpleadosRegistrados=mysqli_query($conexion_base, $queryEmpleadosRegistrados);

        $totalSegundosHoraInicio=0;
        $totalSegundosHoraFin=0;

        while($empleadosRegistrados=mysqli_fetch_array($resultadoEmpleadosRegistrados)){
            $horaInicioJornada=$empleadosRegistrados["Hora_ingreso"];
            $horaFinJornada=$empleadosRegistrados["Hora_salida"];

            $segundosHoraInicio=strtotime($horaInicioJornada);
            $segundosHoraFin=strtotime($horaFinJornada);

            $totalSegundosHoraInicio=$totalSegundosHoraInicio+$segundosHoraInicio;
            $totalSegundosHoraFin=$totalSegundosHoraFin+$segundosHoraFin;
        }
        $segundosTrabajados=$totalSegundosHoraFin-$totalSegundosHoraInicio;
        return $segundosTrabajados;//Segundos
    }

    //Funcion para convertir los segundos trabajados a minutos
    function convertirHorasAMinutos($segundosTotales){
        $minutosTrabajados=floor($segundosTotales/60);
        return $minutosTrabajados;
    }

    //Funcion para calcular el valor monetario de las horas trabajadas (dia)
    function valorHoras($idIngreso, $horasLaborales, $valorTrabajado,$tiempoMinimo, $identidadEmpleado, $conexion_base, $fecha){
        if($horasLaborales>=$tiempoMinimo){
            $totalValorHorasLaboradas=40000;
        }else{
            $totalValorHorasLaboradas=$horasLaborales*$valorTrabajado;
        }

        $queryValorHoras="UPDATE ingreso_empleados SET Valor_dia='$totalValorHorasLaboradas' WHERE Id_ingreso='$idIngreso' AND Num_doc='$identidadEmpleado' AND Fecha='$fecha'";
        $resultadoValorHoras=mysqli_query($conexion_base, $queryValorHoras);

        return $totalValorHorasLaboradas;
    }

    //Funcion para calcular el valor monetario de las horas trabajadas (acumuladas)
    function valorHorasAcumuladas($identidadEmpleado, $conexion_base){
        $queryIngresosInactivos="SELECT * FROM ingreso_empleados WHERE Num_doc='$identidadEmpleado' AND Id_estado_ingreso='2' AND Id_estado_valor_dia='2'";
        $resultadoIngresosInactivos=mysqli_query($conexion_base, $queryIngresosInactivos);

        $totalPagoAcumulado=0;

        while($ingresosInactivos=mysqli_fetch_array($resultadoIngresosInactivos)){
            $valorDia=$ingresosInactivos["Valor_dia"];

            $totalPagoAcumulado=$totalPagoAcumulado+$valorDia;
        }
        return $totalPagoAcumulado;
    }

    //Funcion para verificar si el empleado tiene una jornada laboral activa
    function verificarFinJornada($identidadEmpleado, $conexion_base){
        $queryVerificarFin="SELECT * FROM ingreso_empleados WHERE Num_doc='$identidadEmpleado' AND Hora_salida='00:00:00'";
        $resultadoVerificarFin=mysqli_query($conexion_base, $queryVerificarFin);
        return $resultadoVerificarFin;
    }

    //Funcion para obtener los datos de los empleados que actualmente se encuentran activos 
    function obtenerDatosEmpleadosActivos($identidadEmpleado, $conexion_base){
        $queryDatosEmpleadosActivos="SELECT * FROM ingreso_empleados WHERE Num_doc='$identidadEmpleado' AND Id_estado_ingreso='1'";
        $resultadoEmpleadosActivos=mysqli_query($conexion_base, $queryDatosEmpleadosActivos);
        return $resultadoEmpleadosActivos;
    }

    //Funcion para crear un vector con los datos recuperados
    function datosEmpleadosInicioJornada($resultado_query){
        if(mysqli_num_rows($resultado_query)){
            $datosEmpleadosRegistrados=$resultado_query->fetch_array();
            return $datosEmpleadosRegistrados;
        }
    }

    //Funcion para recuperar el estado actual del empleado
    function verificarEstadoEmpleado($identidadEmpleado, $conexion_base){
        $queryEstadoEmpleado="SELECT * FROM empleados WHERE Num_doc='$identidadEmpleado'";
        $resultadoEstadoEmpleado=mysqli_query($conexion_base, $queryEstadoEmpleado);
        
        if (mysqli_num_rows($resultadoEstadoEmpleado)){
            $datosEmpleado=$resultadoEstadoEmpleado->fetch_array();
            $estado=$datosEmpleado["Id_estado"];
            return $estado;
        }
    }

    //Funcion para actualizar el sueldo del empleado
    function actualizarSueldoEmpleado($sueldo, $tiempoAcumulado, $identidadEmpleado, $conexion_base){
        $queryActualizarSueldo="UPDATE empleados SET Tiempo_acumulado='$tiempoAcumulado', Sueldo='$sueldo' WHERE Num_doc='$identidadEmpleado'";
        $resultadoActualizarSueldo=mysqli_query($conexion_base, $queryActualizarSueldo);
    }

    function pagarSueldoEmpleado($fechaPago, $sueldoEmpleado, $identidadEmpleado, $conexion_base){
        $queryPagarSueldo="INSERT INTO pago_empleados(Fecha_pago, Num_doc, Valor_pago) VALUES ('$fechaPago', '$identidadEmpleado', 
        '$sueldoEmpleado')";
        $resultadoPagarSueldo=mysqli_query($conexion_base, $queryPagarSueldo);

        if($resultadoPagarSueldo==True){
            $queryReiniciarSueldo="UPDATE empleados SET Sueldo='0', Tiempo_acumulado='0 minutos' WHERE Num_doc='$identidadEmpleado'";
            $resultadoReiniciarSueldo=mysqli_query($conexion_base, $queryReiniciarSueldo);
            
            if($resultadoReiniciarSueldo==True){
                $queryActualizarDiaPago="UPDATE ingreso_empleados SET Id_estado_valor_dia='1' WHERE Num_doc='$identidadEmpleado' AND Id_estado_valor_dia='2'";
                $resultadoActualizarDiaPago=mysqli_query($conexion_base, $queryActualizarDiaPago);

                return $resultadoActualizarDiaPago;
            }
        }
    }

    function solicitudPagarSueldo($identidadEmpleado, $conexion_base, $estadoPago){
        $querySolicitud="UPDATE empleados SET confirmar_Pago_Sueldo='$estadoPago' WHERE Num_doc='$identidadEmpleado'";
        $resultadoSolicitud=mysqli_query($conexion_base, $querySolicitud);
        return $resultadoSolicitud;
    }

    

?>