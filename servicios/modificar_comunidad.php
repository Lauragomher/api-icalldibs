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
			  "id_comunidad" => "1",
			  "nombre_comunidad" => "1", 
			  "provincia_comunidad" => "1", 
			  "codigo_postal_comunidad" => "1", 
			  "calle_comunidad" => "1", 
			  "total_vecinos" => "1", 
			  "id_administrador" => "1"
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
        		if(!$objBD->existe('comunidad_de_vecinos','id',$parametrosRecibidos['id_comunidad'])){
			        $response['code'] = 9;
			        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
			        $response['message'] = $api_response_code[$response['code'] ]['Message'];
			        $response['data']=array(
			       	 		'resultado' => 'id_comunidad_no_existe',
			       	 		'id_modificar' => $parametrosRecibidos['id_modificar'],
							'filas_afectadas' => '0'
			        );
        		}
				else{
					//Comprobamos que el administrador existe (El usuario existe y ademças su rol es administrador)
					$objConsultaSQL = new clsConsultaSQL();
					$objConsultaSQL->addCampoSelect('usuario.id','id');
					$objConsultaSQL->addTablaFrom('usuario');
					$objConsultaSQL->addCondicionWhere("usuario.id"," = '". $parametrosRecibidos['id_administrador']."' ");
					$objConsultaSQL->addCondicionWhere("usuario.id_rol"," = '2' ", clsconstantes::$AND);
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
												'resultado' => 'administrador_no_existe',
												'id'=>''
						);   
					}else{
						//Modificamos el registro					
						$resultado = $objBD->ejecutarDelete(
							"update comunidad_de_vecinos " .
							"set " .
							"comunidad_de_vecinos.nombre = '" . $parametrosRecibidos['nombre_comunidad'] . "', " .
							"comunidad_de_vecinos.provincia = '" . $parametrosRecibidos['provincia_comunidad'] . "', " . 
							"comunidad_de_vecinos.codigo_postal = '" . $parametrosRecibidos['codigo_postal_comunidad'] . "', " . 
							"comunidad_de_vecinos.calle = '" . $parametrosRecibidos['calle_comunidad'] . "', " . 
							"comunidad_de_vecinos.total_vecinos = '" . $parametrosRecibidos['total_vecinos'] . "', " .  
							"comunidad_de_vecinos.id_administrador = '" . $parametrosRecibidos['id_administrador'] . "' " . 
							"where id = '" . $parametrosRecibidos['id_comunidad'] . "';"
						);

						if($resultado){
							$response['code'] = 1;
							$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
							$response['message'] = $api_response_code[$response['code'] ]['Message'];
							$response['data'] = array(
								'resultado' => 'ok',
								'id_modificar' => $parametrosRecibidos['id_comunidad'],
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
									'id_modificar' => $parametrosRecibidos['id_comunidad'],
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