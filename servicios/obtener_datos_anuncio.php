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
				"id_anuncio" => 1,
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

				$objConsultaSQL->addTablaFrom('tablon_anuncios');
				$objConsultaSQL->addCampoSelect('tablon_anuncios.id','id_anuncio');
				$objConsultaSQL->addCampoSelect('tablon_anuncios.id_usuario','id_usuario');
				$objConsultaSQL->addCampoSelect('tablon_anuncios.id_comunidad','id_comunidad');
				$objConsultaSQL->addCampoSelect('tablon_anuncios.titulo','titulo_anuncio');
				$objConsultaSQL->addCampoSelect('tablon_anuncios.mensaje','mensaje_anuncio');
				$objConsultaSQL->addCampoSelect('tablon_anuncios.fecha_publicacion','fecha_anuncio');
				$objConsultaSQL->addTablaInnerJoin("usuario", "tablon_anuncios.id_usuario = usuario.id", clsconstantes::$AND);
				$objConsultaSQL->addCampoSelect('usuario.nombre','nombre_usuario');
				$objConsultaSQL->addCampoSelect('usuario.apellidos','apellidos_usuario');
				$objConsultaSQL->addTablaInnerJoin("comunidad_de_vecinos", "tablon_anuncios.id_comunidad= comunidad_de_vecinos.id");


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
			if(array_key_exists("id_anuncio", $parametrosRecibidos))
				$objConsultaSQL->addCondicionWhere("tablon_anuncios.id"," = 
				'".$parametrosRecibidos['id_anuncio']."' ");

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
								"id_anuncio" => $fila["id_anuncio"],
								"id_comunidad" => $fila["id_comunidad"],
								"id_usuario" => $fila["id_usuario"],
								"nombre_usuario" => $fila["nombre_usuario"],							
								"apellidos_usuario" => $fila["apellidos_usuario"],
								"titulo_anuncio" => $fila["titulo_anuncio"],
								"mensaje_anuncio" => $fila["mensaje_anuncio"],
								"fecha_anuncio" => $fila["fecha_anuncio"],
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