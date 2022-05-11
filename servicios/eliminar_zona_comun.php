<?php
	
	$objBD=new clsBD;
	$objConfiguracion=new clsConfiguracion;
	$objUtilidades=new clsUtilidades;

	if(defined('TOKEN_WEBSERVICE')){

		if(constant('TOKEN_WEBSERVICE')==$objConfiguracion->obtenerTokenWebservices()){	

			//VALIDAMOS PARAMETROS
			//Definimos un array con los campos obligatorios
			//Son los parámetros que tienen que lleganos por POST
			//Si tiene valor "1" además de llegarnos por POST, no pueden estar vacios
			//Si tiene valor "0" será necesario enviar el valor pero puede estar vacio
			//El parámetro login_teseo se emplea para el historial
			$parametrosObligatorios=array(
			  "id_zona"=>"1"
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
    		    //Comprobamos si el registro existe        
        		if(!$objBD->existe('zona_comun','id',$parametrosRecibidos['id_zona'])){
			        $response['code'] = 9;
			        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
			        $response['message'] = $api_response_code[$response['code'] ]['Message'];
			        $response['data']=array(
			       	 		'resultado' => 'id_zona_no_existe',
			       	 		'id_eliminar' => $parametrosRecibidos['id_zona'],
							'filas_afectadas' => '0'
			        );
        		}
				else{
					//Eliminamos el registro					
					$sentenciaDelete = "delete from zona_comun " .
					"where id = '" . $parametrosRecibidos['id_zona'] . "';";
					
					$resultado = $objBD->ejecutarDelete($sentenciaDelete);

					if($resultado){
						$response['code'] = 1;
						$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
						$response['message'] = $api_response_code[$response['code'] ]['Message'];
						$response['data'] = array(
							'resultado' => 'ok',
							'id_eliminar' => $parametrosRecibidos['id_zona'],
							'filas_afectadas' => $resultado 
						);
					}
					else{
							//Ha ocurrido un error desconocido en el servidor
							//ya que $resultado tiene 0 filas afectadas	
							$response['code'] = 10;
							$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
							$response['message'] = $api_response_code[$response['code'] ]['Message'];
							$response['data'] = array(
								'resultado' => 'error_servidor_bd',
								'id_eliminar' => $parametrosRecibidos['id_zona'],
								'filas_afectadas' => $resultado 
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