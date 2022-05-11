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
				"login" => 1,
				"password" => 1
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
				//El login y el password han sido recibidos.
				//Comprobamos si son correctos
				//COMPROBAMOS SI EL USUARIO EXISTE
				//INSTANCIAMOS LA CONSULTA
				$objConsultaSQL = new clsConsultaSQL();
				$objConsultaSQL->addCampoSelect('usuario.id','id');
				$objConsultaSQL->addCampoSelect('usuario.login','login');
				$objConsultaSQL->addCampoSelect('usuario.password','password');				
				$objConsultaSQL->addCampoSelect('usuario.nombre','nombre');
				$objConsultaSQL->addCampoSelect('usuario.apellidos','apellidos');
				$objConsultaSQL->addCampoSelect('rol.id', 'id_rol');
				$objConsultaSQL->addCampoSelect('rol.nombre','rol');
				$objConsultaSQL->addTablaFrom('usuario');
 				$objConsultaSQL->addTablaInnerJoin('rol', 'usuario.id_rol = rol.id');
				$objConsultaSQL->addCondicionWhere("usuario.login"," = '". $parametrosRecibidos['login']."' ", clsconstantes::$AND);
				//EJECUTAMOS LA CONSULTA
				$result = $objBD->ejecutarConsulta($objConsultaSQL->obtenerConsultaSQL()); 
				//CONVERTIMOS EL RESULT EN UN ARRAY
				$arrayResult = $objBD->obtenerArrayResult($result);

				//si quisiéramos enviar una consulta SELECT 
				/*
				$result=$objBD->obtenerArrayResult(
					$objBD->ejecutarConsulta(
						"select u.id, u.login, u.nombre, u.apellidos, u.password " .
						"from usuario u " .
						"where u.login ='" . $parametrosRecibidos['login'] . "'"
					)
				);
				*/

				$valido=0;

				//El usuario existe, pero tenemos que comprobar la contraseña	
				if(!is_null($arrayResult)){
					if($arrayResult["password"] == md5($parametrosRecibidos["password"])){
						$valido=1;
					}				
				}

				//Preguntamos si el usuario es válido (Es decir, existe y la contraseña es válida)
				if($valido==1){
					//Es válido
			        $response['code'] = 1;
			        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
				    $response['message'] = $api_response_code[$response['code'] ]['Message'];
		    	    $response['data'] = array(
		        		'resultado' => "ok",
						'id' => utf8_encode($arrayResult['id']),
		        		'login' => utf8_encode($arrayResult['login']),
		        		'nombre' => utf8_encode($arrayResult['nombre']),
		        		'apellidos' => utf8_encode($arrayResult['apellidos']),
		        		'id_rol' => utf8_encode($arrayResult['id_rol']),
		        		'rol' => utf8_encode($arrayResult['rol']),
		        	);
		    	}
		    	else{		    	
					//No es válido
			        $response['code'] = 1;
			        $response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
				    $response['message'] = $api_response_code[$response['code'] ]['Message'];
		    	    $response['data'] = array(
		        		'resultado' => 'no_ok',
						'id'=>'',
		        		'login'=>'',
						'nombre'=>'',
		        		'apellidos'=>'',
		        		'id_rol' => '',
		        		'rol' => '',
					);			
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