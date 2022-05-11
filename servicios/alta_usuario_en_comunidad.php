<?php
	
	$objBD=new clsBD;
	$objConfiguracion=new clsConfiguracion;
	$objUtilidades=new clsUtilidades;

	if(defined('TOKEN_WEBSERVICE')){

		if(constant('TOKEN_WEBSERVICE')==$objConfiguracion->obtenerTokenWebservices()){	

			//VALIDAMOS PARAMETROS
			//Definimos un array con los campos obligatorios
			//Son los parámetros que tienen que lleganos por POST
			//Si tiene valor "1" además de llegarnos por GET, no pueden estar vacios
			//Si tiene valor "0" será necesario enviar el valor pero puede estar vacio
			$parametrosObligatorios=array(
              "id_usuario" => "1",
              "id_comunidad" => "1",             
			);

			//Recogemos los parámetros que lleguen por POST (raw y form-data)
			$parametrosRecibidos = $objUtilidades->obtenerParametrosPOST();

			//Rellenamos un array con los parámetros obligatorios no rellenados
			$arrayParametrosNoValidos=$objUtilidades->validarParametrosPost($parametrosObligatorios, $parametrosRecibidos);

			//Si hay algún elemento en el array de parámetros no rellenados, los parámetros no son correctos
			if(!empty($arrayParametrosNoValidos)){
		        $response['code'] = 8;
		        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
		        $response['message'] = $objUtilidades->obtenerCadenaParametros($arrayParametrosNoValidos);
				$response['data'] = $api_response_code[ $response['code'] ]['Message'];
			}
			else{				
				//Comprobamos si el registro ya existe
                if($objBD->existe('usuario_comunidad','id_usuario',$parametrosRecibidos['id_usuario'])){
			        $response['code'] = 9;
			        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
				    $response['message'] = $api_response_code[$response['code'] ]['Message'];
			        $response['data']=array(
			       	 		'resultado' => 'usuario_ya_existe',
							'filas_afectadas' => '0'
			        );        		
				}
				else{
					//Creamos el registro
                    $sentenciaInsert =  "insert into usuario_comunidad(id, id_usuario, id_comunidad) " .
                    "values (null, '" . $parametrosRecibidos['id_usuario'] . "', '" . ($parametrosRecibidos['id_comunidad']) . "');";

                    $resultado = $objBD->ejecutarInsert($sentenciaInsert);
					
					if($resultado){
						$response['code'] = 1;
				    	$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];		
                        $response['message'] = $api_response_code[$response['code'] ]['Message'];
						$response['data'] = array(
										       		'resultado' => 'ok',
										        	'id'=> $resultado
						);
					}else{
						$response['code'] = 10;
				    	$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];	
						$response['message'] = $api_response_code[$response['code'] ]['Message'];
						$response['data'] = array(
										       		'resultado' => 'no_ok',
										        	'id'=> $resultado
						);
					}
					
				}
			}					
		}
  		else{
			echo _("Token incorrecto");
		}
    }
    else{
		echo _("Llamada no autorizada");
	}

?>