<?php
    session_start();
    $sesionDestruida=session_destroy();

    if ($sesionDestruida==True){
        echo "<script> 
            alert ('Sesión cerrada')
            window.location='../login.html';
        </script>";
    }
?>