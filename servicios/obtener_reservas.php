<?php
	
	$objBD = new clsBD;
	$objConfiguracion = new clsConfiguracion;
	$objUtilidades = new clsUtilidades;
	$objConsultaSQL = new clsConsultaSQL();

	if(defined('TOKEN_WEBSERVICE')){

		if(constant('TOKEN_WEBSERVICE')==$objConfiguracion->obtenerTokenWebservices()){	

			//Recogemos los parámetros que lleguen por POST (raw y form-data)
			$parametrosRecibidos = $objUtilidades->obtenerParametrosPOST();

			//CONSTRUIMOS LA CONSULTA
			$objConsultaSQL->addTablaFrom("reserva");
			$objConsultaSQL->addCampoSelect('reserva.id','id_reserva');
			$objConsultaSQL->addCampoSelect('reserva.id_usuario','id_usuario');
			$objConsultaSQL->addCampoSelect('reserva.id_zona_comun','id_zona');
			$objConsultaSQL->addCampoSelect('reserva.fecha_hora_inicio','inicio_reserva');
			$objConsultaSQL->addCampoSelect('reserva.fecha_hora_fin','fin_reserva');

			//CONSTRUIMOS LIMIT
			if(array_key_exists("fila_inicial", $parametrosRecibidos) && array_key_exists("numero_filas", $parametrosRecibidos)){
				$objConsultaSQL->establecerLimitInferior($parametrosRecibidos['fila_inicial']-1);
				$objConsultaSQL->establecerLimitFilas($parametrosRecibidos['numero_filas']);
			}

			//CONSTRUIMOS FILTRADOS
			if(array_key_exists("id_reserva", $parametrosRecibidos))
				$objConsultaSQL->addCondicionWhere("reserva.id"," = '".$parametrosRecibidos['id_reserva']."' ");
			
			if(array_key_exists("id_usuario", $parametrosRecibidos))
				$objConsultaSQL->addCondicionWhere("reserva.id_usuario"," = '".$parametrosRecibidos['id_usuario']."' ", clsConstantes::$AND);

			if(array_key_exists("id_administrador", $parametrosRecibidos))
				$objConsultaSQL->addCondicionWhere("reserva.id_zona_comun"," = '".$parametrosRecibidos['id_zona']."' ", clsConstantes::$AND);


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