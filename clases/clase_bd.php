<?php

class clsBD
{
	private $conexionBD;
	private $servidorBD;
	private $nombreBD;
	private $usuarioBD;
	private $passwordBD;
    private $puertoBD;
	private $objConfiguracion;

    //variable que permite insertar caracteres comillas en los valores de los campos
    private $permitirCaracteresNoValidos=false;

    //CONSTRUCTOR DE LA CLASE
    public function __construct()
    {
        //instanciamos un objeto configuracion y leemos los parametros que nos interesan relacionados con la BD
        $this->objConfiguracion=new clsConfiguracion();

        //Inicializamos los atributos de clsBD con los parametros definidos en el objetod e configuración
        $this->servidorBD = $this->objConfiguracion->obtenerServidorBD();
        $this->nombreBD = $this->objConfiguracion->obtenerNombreBD();
        $this->usuarioBD = $this->objConfiguracion->obtenerUsuarioBD();
        $this->passwordBD = $this->objConfiguracion->obtenerPasswordBD();
        $this->puertoBD = $this->objConfiguracion->obtenerPuertoBD();
    }

	//Conexión a la base de datos según parámetros establecidos por el objeto configuración en el constructor
    public function conectar()
    {
        $this->conexionBD = new mysqli($this->servidorBD, $this->usuarioBD, $this->passwordBD,$this->nombreBD, $this->puertoBD);
        if (mysqli_connect_errno()) {
            $this->error ('Error','001','Error conectando a la base de datos');
            //header ("location: ".$this->objConfiguracion->obtenerDominioRaiz()."/".$this->objConfiguracion->obtenerNombreCarpetaRaiz()."/".$this->objConfiguracion->obtenerPaginaMantenimiento());
            exit();
        }
        $this->conexionBD->set_charset($this->objConfiguracion->obtenerCharSetMySQL());
    }

    public function desconectar()
    {
    	mysqli_close($this->conexionBD);
    }

	public function obtenerArrayAsociativo($result)
    {
        if($result)
        if(mysqli_num_rows($result)>0)
        {
            return mysqli_fetch_assoc($result);
        }
        else
            return Array();
        else
        return Array();
    }

  	public function codificarFilaUTF8($fila)
	{
  		foreach ($fila as $id => $campo) {
			$fila[$id]=utf8_encode($fila[$id]);
		}
		return $fila;
	}

	public function ejecutarConsulta($cadenaConsulta)
 	{
 		//debug
 		if ($this->objConfiguracion->obtenerModoDebug())
			echo '[clase-bd->ejecutarConsulta]->'. $cadenaConsulta;
    	$this->conectar();
  		$result = mysqli_query ($this->conexionBD,utf8_decode($cadenaConsulta));
  		$this->desconectar();
  		return $result;
 	}

    public function ejecutarInsert($cadenaConsulta)
 	{
 		//debug
 		if ($this->objConfiguracion->obtenerModoDebug())
			echo '[clase-bd->ejecutarConsulta]->'. $cadenaConsulta;
    	$this->conectar();
  		$result = mysqli_query ($this->conexionBD,utf8_decode($cadenaConsulta));
        $last_insert_id = $this->conexionBD->insert_id;
  		$this->desconectar();
  		return $last_insert_id;
 	}

    public function ejecutarDelete($cadenaConsulta)
 	{
 		//debug
 		if ($this->objConfiguracion->obtenerModoDebug())
			echo '[clase-bd->ejecutarConsulta]->'. $cadenaConsulta;
    	$this->conectar();
  		$result = mysqli_query ($this->conexionBD,utf8_decode($cadenaConsulta));
        $filas_afectadas = $this->conexionBD->affected_rows;
  		$this->desconectar();
  		return $filas_afectadas;
 	}

    public function ejecutarUpdate($cadenaConsulta)
 	{
 		//debug
 		if ($this->objConfiguracion->obtenerModoDebug())
			echo '[clase-bd->ejecutarConsulta]->'. $cadenaConsulta;
    	$this->conectar();
  		$result = mysqli_query ($this->conexionBD,utf8_decode($cadenaConsulta));
        $filas_afectadas = $this->conexionBD->affected_rows;
  		$this->desconectar();
  		return $filas_afectadas;
 	}

    //Función que devuelve el primer campo contenido en la fila actual de un result
 	public function obtenerResultadoUnicoTextoResult($result)
 	{
 		if (mysqli_num_rows($result)>0)
  		{
  			$fila=mysqli_fetch_array($result);
  				return  utf8_encode($fila[0]);
  		}
  		else
  			return '';
 	}

    //Función que devuelve un campo concreto de la fila actual de un result según su id
    public function obtenerCampoResult($result,$idCampo)
    {
        if (mysqli_num_rows($result)>0)
        {
            $fila=mysqli_fetch_array($result);
            return  utf8_encode($fila[$idCampo]);
        }
        else
            return '';
    }

    //Función que convierte un result de 1 fila en un array
    public function obtenerArrayResult($result)
    {
        if (!empty($result) AND mysqli_num_rows($result)>0)
        {
            return mysqli_fetch_assoc($result);
        }
        else
            return null;
    }

    //Función que devuelve el primer campo contenido en la fila actual de un result
    public function obtenerPrimerCampoResult($result)
    {
        try
        {
        if (mysqli_num_rows($result)>0)
            {
            $fila=mysqli_fetch_array($result);
                return  utf8_encode($fila[0]);
            }
            else
            return '';
        }
        catch (Exception $e)
        {
        return "";
        }
    }

 	public function existe($tabla,$campo,$valor,$nombreCampoClaveExcluir='',$valorCampoClaveExcluir='')
 	{
		$this->conectar();

		$cadenaSQL="select count(1) as resultado from ".$tabla." where ucase(".$campo.")='". strtoupper($this->eliminarCaracteresNoValidos($valor)) ."'";
		if($nombreCampoClaveExcluir!='' && $valorCampoClaveExcluir!='')
			$cadenaSQL.=' and '.$nombreCampoClaveExcluir."<>'".$valorCampoClaveExcluir."' ";

		$result=mysqli_query($this->conexionBD,$cadenaSQL);

		if (mysqli_num_rows($result)>0)
  		{
  			$this->desconectar();
  			$fila=mysqli_fetch_array($result);
  			if ($fila['resultado']>0)
  				return true;
  			else
  				return false;
  		}
  		else
  		{
  			$this->desconectar();
			return false;
		}
 	}

    public function establecerPermitirCaracteresNoValidos($estado)
    {
        $this->permitirCaracteresNoValidos=$estado;
    }

    public function eliminarCaracteresNoValidos($cadena)
    {
        $cadenaValida=$cadena;
        if(!$this->permitirCaracteresNoValidos)
        {
        $cadenaValida=str_replace("'","", $cadena);
        $cadenaValida=str_replace("\"","", $cadenaValida);
        $cadenaValida=str_replace("&"," ", $cadenaValida);

        }
        return $cadenaValida;
    }

    public function obtenerCadenaParametros($arrayParametros)
    {
        $cadena='';
        foreach ($arrayParametros as $nombreParametro => $valorParametro)
        {
        $cadena.=$nombreParametro.'=>'.$valorParametro.clsConstantes::$CARACTER_SEPARADOR;
        }
        return $cadena;
    }

	private function error($tipo,$numero,$texto)
	{
		$archivo = fopen('problemas.log','a');
		fwrite($archivo,"[".date("r")."] $tipo $numero: $texto\r\n");
		fclose($archivo);
	}

    function eliminarCaracteresNoValidosJSON($cadena)
    {
    $cadenaRetorno=str_replace("'", "", $cadena);
    $cadenaRetorno=str_replace('"', "", $cadenaRetorno);
    //$cadenaRetorno=str_replace(',', "", $cadenaRetorno);
    return $cadenaRetorno;
    }

    function obtenerJSONMySQLSelect($consultaSQL)
    {
        $cadenaJSON='';
        $this->conectar();

        //la consulta debe tener 3 campos:
        //id
        //texto
        //subtexto

        $result = mysqli_query($this->conexionBD,$consultaSQL);
        if(mysqli_num_rows($result)>0)
        {
            $indiceFilas=1;
            $cadenaJSON.='[';
            while($fila=mysqli_fetch_assoc($result))
            {
                $cadenaJSON.='{';
                $indiceCampos=1;

                foreach ($fila as $valorCampo => $nombreCampo)
                {
                if ($indiceCampos<count($fila))
                    $cadenaJSON.= '"'.utf8_encode($this->eliminarCaracteresNoValidosJSON($valorCampo)).'": "'.utf8_encode($this->eliminarCaracteresNoValidosJSON($nombreCampo)).'",';
                else
                    $cadenaJSON.= '"'.utf8_encode($this->eliminarCaracteresNoValidosJSON($valorCampo)).'": "'.utf8_encode($this->eliminarCaracteresNoValidosJSON($nombreCampo)).'"';
                $indiceCampos++;
                }

                if($indiceFilas<mysqli_num_rows($result))
                $cadenaJSON.='},';
                else
                $cadenaJSON.='}';

                $indiceFilas++;
            }
            $cadenaJSON.=']';
        }
        $this->desconectar();
        return $cadenaJSON;
    }



}
?>
