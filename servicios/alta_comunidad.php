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
              "nombre_comunidad"=>"1",
              "id_administrador"=>"1",              
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
					//Creamos la comunidad	
					$sentenciaInsert = "insert into comunidad_de_vecinos(id, nombre, id_administrador, provincia, codigo_postal, calle, total_vecinos) " .
					"values (null, '" . $parametrosRecibidos['nombre_comunidad'] . "',
					 '" . $parametrosRecibidos['id_administrador'] . "',
					 '" . $parametrosRecibidos['provincia_comunidad'] . "',
					  '" . $parametrosRecibidos['codigo_postal'] . "',
					  '" . $parametrosRecibidos['calle_comunidad'] . "',
					   '" . $parametrosRecibidos['total_vecinos'] . "');";

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