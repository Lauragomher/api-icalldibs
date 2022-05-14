<?php

    //AQUI TENEMOS QUE REGISTRAR CADA UNO DE LOS SERVICIOS 
    switch ($servicio) {
        //================================================
        //INICIOS DE SESION
        //================================================
        case 'login':
            include_once('servicios/login.php');
            break;                      
        //================================================
        //ALTAS
        //================================================ 
        case 'alta_usuario':
            include_once('servicios/alta_usuario.php');
            break;  
        case 'alta_comunidad':
            include_once('servicios/alta_comunidad.php');
            break;
        case 'alta_usuario_en_comunidad':
            include_once('servicios/alta_usuario_en_comunidad.php');
            break;
        case 'alta_zona_comun':
            include_once('servicios/alta_zona_comun.php');
            break;
        case 'alta_reserva':
            include_once('servicios/alta_reserva.php');
            break;
        case 'alta_mensaje_tablon':
                include_once('servicios/alta_mensaje_tablon.php');
                break;                   
        //================================================
        //MODIFICACIONES
        //================================================ 
        case 'modificar_comunidad':
            include_once('servicios/modificar_comunidad.php');
            break;
        case 'modificar_usuario':
                include_once('servicios/modificar_usuario.php');
                break;
        case 'modificar_zona_comun':
            include_once('servicios/modificar_zona_comun.php');
            break;
        //================================================     
        //CONSULTAS
        //================================================    
        case 'obtener_comunidades':
                include_once('servicios/obtener_comunidades.php');
                break;
        case 'obtener_administradores':
                include_once('servicios/obtener_administradores.php');
                break;
        case 'obtener_datos_comunidad':
                include_once('servicios/obtener_datos_comunidad.php');
                break;
        case 'obtener_anuncios_comunidad':
                include_once('servicios/obtener_datos_comunidad.php');
                break;
        case 'obtener_zonas_comunes':
                include_once('servicios/obtener_zonas_comunes.php');
                break;
        case 'obtener_zonas_admin':
                include_once('servicios/obtener_zonas_admin.php');
                break;
        case 'obtener_reservas_zona':
                include_once('servicios/obtener_reservas_zona.php');
                break;
        case 'obtener_comunidades_de_admin':
                include_once('servicios/obtener_comunidades_de_admin.php');
                break;
        case 'obtener_datos_usuario':
                include_once('servicios/obtener_datos_usuario.php');
                break;
        case 'obtener_zonas_usuario':
                include_once('servicios/obtener_zonas_usuario.php');
                break;
        case 'obtener_usuarios_comunidad':
                include_once('servicios/obtener_usuarios_comunidad.php');
                break;
        case 'obtener_datos_usuarios_comunidad':
                include_once('servicios/obtener_datos_usuarios_comunidad.php');
                break;
        case 'obtener_usuarios_sin_comunidad':
                include_once('servicios/obtener_usuarios_sin_comunidad.php');
                break;     
        case 'obtener_reservas':
                include_once('servicios/obtener_reservas.php');
                break;
        case 'obtener_reservas_usuario':
                include_once('servicios/obtener_reservas_usuario.php');
                break;
        case 'obtener_reserva_especifica':
                include_once('servicios/obtener_reserva_especifica.php');
                break;             
        case 'obtener_datos_zona_reserva':
                include_once('servicios/obtener_datos_zona_reserva.php');
                break;           
        //================================================     
        //ELIMINACIONES     
        //================================================     
        case 'eliminar_usuario':
                include_once('servicios/eliminar_usuario.php');
                break;
        case 'eliminar_comunidad':
                include_once('servicios/eliminar_comunidad.php');
                break;
        case 'eliminar_zona_comun':
                include_once('servicios/eliminar_zona_comun.php');
                break;
        case 'eliminar_reserva':
                include_once('servicios/eliminar_reserva.php');
                break;
        //TEST DE ESTADO    
        case 'ping':
                include_once('servicios/ping.php');    
                break;
            default:
                $response['code'] = 5;
                $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
                $response['data'] = $api_response_code[$response['code']]['Message'];
                break;
    }

?>