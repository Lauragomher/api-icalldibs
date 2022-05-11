<?php
	
	$objBD = new clsBD;
	$objConfiguracion = new clsConfiguracion;
	$objUtilidades = new clsUtilidades;
	$objConsultaSQL = new clsConsultaSQL();

	if(defined('TOKEN_WEBSERVICE')){

		if(constant('TOKEN_WEBSERVICE')==$objConfiguracion->obtenerTokenWebservices()){	
			$parametrosObligatorios=array(
				"id_usuario" => 1,
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

			//CONSTRUIMOS LA CONSULTA
			$objConsultaSQL->addTablaFrom('usuario');
			$objConsultaSQL->addCampoSelect('usuario.id','id_usuario');
			$objConsultaSQL->addCampoSelect('usuario.nombre','nombre_usuario');
			$objConsultaSQL->addCampoSelect('usuario.apellidos','apellidos_usuario');
			$objConsultaSQL->addCampoSelect('usuario.id_rol','id_rol');
			$objConsultaSQL->addCampoSelect('comunidad_de_vecinos.id','id_comunidad');
			$objConsultaSQL->addCampoSelect('comunidad_de_vecinos.nombre','nombre_comunidad');
			$objConsultaSQL->addCampoSelect('comunidad_de_vecinos.total_vecinos','total_vecinos_comunidad');
			$objConsultaSQL->addTablaInnerJoin("usuario_comunidad",'usuario.id = usuario_comunidad.id_usuario');
			$objConsultaSQL->addTablaInnerJoin("comunidad_de_vecinos","comunidad_de_vecinos.id = usuario_comunidad.id_comunidad", clsconstantes::$AND);
			$objConsultaSQL->addCampoOrderby("comunidad_de_vecinos.id","asc");


			//CONSTRUIMOS LIMIT
			if(array_key_exists("fila_inicial", $parametrosRecibidos) && array_key_exists("numero_filas", $parametrosRecibidos)){
				$objConsultaSQL->establecerLimitInferior($parametrosRecibidos['fila_inicial']-1);
				$objConsultaSQL->establecerLimitFilas($parametrosRecibidos['numero_filas']);
			}

			//CONSTRUIMOS FILTRADOS
			if(array_key_exists("id_usuario", $parametrosRecibidos))
				$objConsultaSQL->addCondicionWhere("usuario.id"," = '".$parametrosRecibidos['id_usuario']."' ");

			//DEBUG. SOLO DESCOMENTAR SI QUERÉIS VER LA CONSULTA QUE SE EJECUTA
			//AL DESCOMENTAR, NO EJECUTARÁ LA CONSULTA, SOLO LA MOSTRARÁ
			//echo $objConsultaSQL->obtenerConsultaSQL();die();

			//EJECUTAMOS LA CONSULTA
			$resultado= $objBD->ejecutarConsulta($objConsultaSQL->obtenerConsultaSQL()); 

			//Comprobamos si el resultado contiene filas
			if (!$resultado) {
					$response['code'] = 0;				
				    $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
			        $response['message'] = $api_response_code[$response['code'] ]['Message'];
			    	$response['numero_filas']=0;
					$response['data']=array(
			       	 		'resultado' => 'error_servidor_bd',
							'datos' => array()
							
			        );					
			}
			else{  
				//Si la consulta se ha podido ejecutar  			
				//Si no se devuelven filas
				if (mysqli_num_rows($resultado) == 0){
					$response['code'] = 1;				
				   	$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
					$response['message'] = $api_response_code[$response['code'] ]['Message'];
			    	$response['numero_filas']=0;
					$response['data']=array(
						'resultado' => 'sin_resultados',
						'datos' => array()
					);
				}
				else{
					//Si se devuelven filas
					$response['code'] = 1;				
				   	$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
					$response['message'] = $api_response_code[$response['code'] ]['Message'];
			    	$response['numero_filas']=mysqli_num_rows($resultado);
					$response['data']=array(
						'resultado' => 'ok',
						'datos' => array()
					);
					$indice=0;
					while ($fila = mysqli_fetch_assoc($resultado)) {
						$response['data']['datos'][$indice]= 
							array(
								"id_usuario" => $fila["id_usuario"],
								"nombre_usuario" => $fila["nombre_usuario"],
								"apellidos_usuario" => $fila["apellidos_usuario"],
								"id_rol" => $fila["id_rol"],
								"nombre_comunidad" => $fila["nombre_comunidad"],
								"id_comunidad" => $fila["id_comunidad"],
								"total_vecinos_comunidad" => $fila["total_vecinos_comunidad"],
							);	
						$indice++;					
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