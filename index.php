<?php

/*
    API Demo
 
    This script provides a RESTful API interface for a web application
 
    Input:
 
        $_GET['formato'] = [ json | html | xml ]
        $_GET['servicio'] = [<nombre_del_servicio>]
 
    Output: A formatted HTTP response
 
    Author: Mark Roland
 
    History:
        11/13/2012 - Created
 
*/
 
// --- Step 1: Initialize variables and functions
 
/**
 * Deliver HTTP Response
 * @param string $format The desired HTTP response content type: [json, html, xml]
 * @param string $api_response The desired HTTP response data
 * @return void
 **/


include_once('clases/clase_configuracion.php');
include_once('clases/clase_utilidades.php');
include_once('clases/clase_bd.php');
include_once('clases/clase_constantes.php');
include_once('clases/clase_consulta_sql.php');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // The request is using the POST method
    header("HTTP/1.1 200 OK");
    return;
}

$objConfiguracion=new clsConfiguracion;

//Constante que controla si el servicio se llama desde el cliente
define('TOKEN_WEBSERVICE', $objConfiguracion->obtenerTokenWebservices());

function deliver_response($format, $api_response){
 
    // Define HTTP responses
    $http_response_code = array(
        200 => 'OK',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Error'
    );
 
    // Set HTTP Response
    header('HTTP/1.1 '.$api_response['status'].' '.$http_response_code[ $api_response['status'] ]);
 
    // Process different content types
    if( strcasecmp($format,'json') == 0 ){
 
        // Set HTTP Response Content Type
        header('Content-Type: application/json; charset=utf-8');
 
        // Format data into a JSON response
        $json_response = json_encode($api_response);
 
        // Deliver formatted data
        echo $json_response;
 
    }elseif( strcasecmp($format,'xml') == 0 ){
 
        // Set HTTP Response Content Type
        header('Content-Type: application/xml; charset=utf-8');
 
        // Format data into an XML response (This is only good at handling string data, not arrays)
        $xml_response = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<response>'."\n".
            "\t".'<code>'.$api_response['code'].'</code>'."\n".
            "\t".'<data>'.$api_response['data'].'</data>'."\n".
            '</response>';
 
        // Deliver formatted data
        echo $xml_response;
 
    }else{
 
        // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
        header('Content-Type: text/html; charset=utf-8');
 
        // Deliver formatted data
        echo $api_response['data'];
 
    }
 
    // End script process
    exit;
 
}
 
// Define whether an HTTPS connection is required
$HTTPS_required = FALSE;
 
// Define whether user authentication is required
$authentication_required = FALSE;
 
// Define API response codes and their related HTTP response
$api_response_code = array(
    0 => array('HTTP Response' => 400, 'Message' => _('ERROR')),
    1 => array('HTTP Response' => 200, 'Message' => _('OK')),
    2 => array('HTTP Response' => 403, 'Message' => _('HTTPS_OBLIGATORIO')),
    3 => array('HTTP Response' => 401, 'Message' => _('AUTENTICACION_REQUERIDA')),
    4 => array('HTTP Response' => 401, 'Message' => _('AUTENTICACION_FALLIDA')),
    5 => array('HTTP Response' => 404, 'Message' => _('PETICION_NO_VALIDA')),
    6 => array('HTTP Response' => 400, 'Message' => _('FORMATO_RESPUESTA_NO_VALIDA')),
    7 => array('HTTP Response' => 404, 'Message' => _('LLAMADA_NO_PERMITIDA')),
    8 => array('HTTP Response' => 404, 'Message' => _('PARAMETROS_INCORRECTOS')),
    9 => array('HTTP Response' => 404, 'Message' => _('OPERACION_CANCELADA_PARAMETROS_NO_VALIDOS')),
    10 => array('HTTP Response' => 500, 'Message' => _('ERROR'))
);
 
// Set default HTTP response of 'ok'
$response['code'] = 0;
$response['status'] = 200;
$response['data'] = NULL;


if(isset($_GET['format']))
    $formato_salida=$_GET['format'];
else
    $formato_salida="json";
 
// --- Step 2: Authorization
 
// Optionally require connections to be made via HTTPS
if( $HTTPS_required && $_SERVER['HTTPS'] != 'on' ){
    $response['code'] = 2;
    $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
    $response['data'] = $api_response_code[ $response['code'] ]['Message'];
 
    // Return Response to browser. This will exit the script.
    deliver_response($formato_salida, $response);
}

 
// Optionally require user authentication
if( $authentication_required ){
 
    if( empty($_POST['login_webservice']) || empty($_POST['password_webservice']) ){
        $response['code'] = 3;
        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
        $response['data'] = $api_response_code[ $response['code'] ]['Message'];
 
        // Return Response to browser
        deliver_response($formato_salida, $response);
 
    }
 
    // Return an error response if user fails authentication. This is a very simplistic example
    // that should be modified for security in a production environment
    elseif( $_POST['login_webservice'] != $objConfiguracion->obtenerUsuarioWebservices() || $_POST['password_webservice'] != $objConfiguracion->obtenerPasswordWebservices() )
    {
        $response['code'] = 4;
        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
        $response['data'] = $api_response_code[ $response['code'] ]['Message'];
 
        // Return Response to browser
        deliver_response($formato_salida, $response);
 
    }
 
}
 
if(isset($_GET['servicio']))
    $servicio=$_GET['servicio'];
else
    $servicio="";


include('servicios/registro_servicios.php');

 
// Devolvemos la respuesta
deliver_response($formato_salida, $response);
 
?>