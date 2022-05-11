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
				"id_usuario" => 1
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

				$objConsultaSQL->addTablaFrom("reserva");
				$objConsultaSQL->addCampoSelect('reserva.id','id_reserva');
				$objConsultaSQL->addCampoSelect('reserva.id_usuario','id_usuario');
				$objConsultaSQL->addCampoSelect('reserva.id_zona_comun','id_zona');
				$objConsultaSQL->addCampoSelect('reserva.fecha_hora_inicio','inicio_reserva');
				$objConsultaSQL->addCampoSelect('reserva.fecha_hora_fin','fin_reserva');
				$objConsultaSQL->addCondicionWhere("reserva.id_usuario",
				" = '". $parametrosRecibidos['id_usuario']."' "," ",);
				$objConsultaSQL->addTablaInnerJoin("zona_comun", "zona_comun.id=reserva.id_zona_comun");
				$objConsultaSQL->addCampoSelect('zona_comun.tipo','nombre_zona');
				$objConsultaSQL->addCampoSelect('zona_comun.descripcion','descripcion_zona');
				$objConsultaSQL->addCampoSelect('zona_comun.imagen','imagen_zona');
				$objConsultaSQL->addCampoSelect('zona_comun.aforo','aforo_zona');





				//EJECUTAMOS LA CONSULTA
				$result = $objBD->ejecutarConsulta($objConsultaSQL->obtenerConsultaSQL()); 
				//CONVERTIMOS EL RESULT EN UN ARRAY
				$arrayResult = $objBD->obtenerArrayResult($result);


			
			//CONSTRUIMOS LIMIT
			if(array_key_exists("fila_inicial", $parametrosRecibidos) && array_key_exists("numero_filas", $parametrosRecibidos)){
				$objConsultaSQL->establecerLimitInferior($parametrosRecibidos['fila_inicial']-1);
				$objConsultaSQL->establecerLimitFilas($parametrosRecibidos['numero_filas']);
			}

			//CONSTRUIMOS FILTRADOS
			//if(array_key_exists("id_usuario", $parametrosRecibidos))
			//	$objConsultaSQL->addCondicionWhere("reserva.id_usuario"," = 
			//	'".$parametrosRecibidos['id_usuario']."' ", clsConstantes::$AND); 
			

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
								"id_reserva" => $fila["id_reserva"],
								"id_zona" => $fila["id_zona"],
								"inicio_reserva" => $fila["inicio_reserva"],
								"fin_reserva" => $fila["fin_reserva"],
								"nombre_zona" => $fila["nombre_zona"],
								"descripcion_zona" => $fila["descripcion_zona"],
								"imagen_zona" => $fila["imagen_zona"],
								"aforo_zona" => $fila["aforo_zona"],
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