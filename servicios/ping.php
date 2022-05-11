<?php
	$objConfiguracion=new clsConfiguracion;

	if(defined('TOKEN_WEBSERVICE'))
	{
		if(constant('TOKEN_WEBSERVICE')==$objConfiguracion->obtenerTokenWebservices())
		{	
				//Retornamos code 1 	
			    $response['code'] = 1;
			    $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
		    	$response['data'] = array(
		        	'resultado' => 'ok'
		        );		
		}
  		else
		{
			echo _("Token incorrecto");
		}
    }
    else
    {
		echo _("Llamada no autorizada");
	}
?>