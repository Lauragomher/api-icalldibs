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
				"id_zona" => "1",
				"id_comunidad" => "1",
				"nombre_zona" => "1",
				"zona_descripcion" => "1",               
				"zona_activa" => "1",
				"zona_aforo" => "1",
				"imagen_zona" => "1"
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
    		    //Comprobamos si la zona común existe        
        		if(!$objBD->existe('zona_comun','id',$parametrosRecibidos['id_zona'])){
			        $response['code'] = 9;
			        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
			        $response['message'] = $api_response_code[$response['code'] ]['Message'];
			        $response['data']=array(
			       	 		'resultado' => 'id_zona_no_existe',
			       	 		'id_modificar' => $parametrosRecibidos['id_modificar'],
							'filas_afectadas' => '0'
			        );
        		}
				else{
					//Comprobamos que la comunidad existe y coincide con la introducida
					$objConsultaSQL = new clsConsultaSQL();
					$objConsultaSQL->addCampoSelect('comunidad_de_vecinos.id','id_comunidad');
					$objConsultaSQL->addTablaFrom('comunidad_de_vecinos');
					$objConsultaSQL->addCondicionWhere("comunidad_de_vecinos.id"," = '". $parametrosRecibidos['id_comunidad']."' ");
					//EJECUTAMOS LA CONSULTA
					$result = $objBD->ejecutarConsulta($objConsultaSQL->obtenerConsultaSQL()); 
					//CONVERTIMOS EL RESULT EN UN ARRAY
					$arrayResult = $objBD->obtenerArrayResult($result);

					//Si no existe
					if(!$arrayResult){
						$response['code'] = 9;
						$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
						$response['message'] = $api_response_code[$response['code'] ]['Message'];
						$response['data']=array(
												'resultado' => 'comunidad_no_existe',
												'id'=>''
						);   
					}else{
						//Modificamos el registro					
						$resultado = $objBD->ejecutarDelete(
							"update zona_comun " .
							"set " .
							"zona_comun.tipo = '" . $parametrosRecibidos['nombre_zona'] . "', " . 
							"zona_comun.descripcion = '" . $parametrosRecibidos['zona_descripcion'] . "', " .
							"zona_comun.activa = '" . $parametrosRecibidos['zona_activa'] . "', " .
							"zona_comun.aforo = '" . $parametrosRecibidos['zona_aforo'] . "', " .
							"zona_comun.imagen = '" . $parametrosRecibidos['imagen_zona'] . "' " .
							"where zona_comun.id = '" . $parametrosRecibidos['id_zona'] . "';"
						);

						if($resultado){
							$response['code'] = 1;
							$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
							$response['message'] = $api_response_code[$response['code'] ]['Message'];
							$response['data'] = array(
								'resultado' => 'ok',
								'id_modificar' => $parametrosRecibidos['id_zona'],
								'filas_afectadas' => $resultado 
							);
						}
						else{
								//Ha ocurrido un error desconocido en el servidor
								//ya que $resultado tiene 0 filas afectadas	
								$response['code'] = 0;
								$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
								$response['message'] = $api_response_code[$response['code'] ]['Message'];
								$response['data'] = array(
									'resultado' => 'error_servidor_bd',
									'id_modificar' => $parametrosRecibidos['id_zona'],
									'filas_afectadas' => $resultado 
								);
						}    			
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