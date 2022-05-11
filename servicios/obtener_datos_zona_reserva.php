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
			$parametrosObligatorios=array(
				"id_zona" => 1,
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
				//El login y el password han sido recibidos.
				//Comprobamos si son correctos
				//COMPROBAMOS SI EL USUARIO EXISTE
				//INSTANCIAMOS LA CONSULTA
				$objConsultaSQL = new clsConsultaSQL();

				$objConsultaSQL->addCampoSelect('zona_comun.id','id_zona');
				$objConsultaSQL->addCampoSelect('zona_comun.tipo','tipo_zona');
				$objConsultaSQL->addCampoSelect('zona_comun.id_comunidad','id_comunidad');
				$objConsultaSQL->addCampoSelect('zona_comun.activa','zona_activa');
				$objConsultaSQL->addCampoSelect('zona_comun.descripcion','descripcion_zona');
				$objConsultaSQL->addCampoSelect('zona_comun.aforo','aforo_zona');
				$objConsultaSQL->addCampoSelect('zona_comun.imagen','imagen_zona');
				$objConsultaSQL->addTablaFrom('zona_comun');
				$objConsultaSQL->addTablaInnerJoin('comunidad_de_vecinos','comunidad_de_vecinos.id = zona_comun.id_comunidad');
				$objConsultaSQL->addCampoSelect('comunidad_de_vecinos.nombre','nombre_comunidad');



				//EJECUTAMOS LA CONSULTA
				$result = $objBD->ejecutarConsulta($objConsultaSQL->obtenerConsultaSQL()); 
				//CONVERTIMOS EL RESULT EN UN ARRAY
				$arrayResult = $objBD->obtenerArrayResult($result);
							//DEBUG. SOLO DESCOMENTAR SI QUERÉIS VER LA CONSULTA QUE SE EJECUTA
			//AL DESCOMENTAR, NO EJECUTARÁ LA CONSULTA, SOLO LA MOSTRARÁ
			//echo $objConsultaSQL->obtenerConsultaSQL();die();

			
			//CONSTRUIMOS LIMIT
			if(array_key_exists("fila_inicial", $parametrosRecibidos) && array_key_exists("numero_filas", $parametrosRecibidos)){
				$objConsultaSQL->establecerLimitInferior($parametrosRecibidos['fila_inicial']-1);
				$objConsultaSQL->establecerLimitFilas($parametrosRecibidos['numero_filas']);
			}

			//CONSTRUIMOS FILTRADOS
			if(array_key_exists("id_zona", $parametrosRecibidos))
				$objConsultaSQL->addCondicionWhere("zona_comun.id"," = 
				'".$parametrosRecibidos['id_zona']."' ");

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
								"id_zona" => $fila["id_zona"],
								"tipo_zona" => $fila["tipo_zona"],
								"id_comunidad" => $fila["id_comunidad"],
								"zona_activa" => $fila["zona_activa"],
								"descripcion_zona" => $fila["descripcion_zona"],
								"aforo_zona" => $fila["aforo_zona"],
								"imagen_zona" => $fila["imagen_zona"],
								"nombre_comunidad" => $fila["nombre_comunidad"],
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