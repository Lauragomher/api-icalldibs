<?php

class clsConfiguracion{
    private $estado_app;
    private $array_parametros = array();

    //Constructor de la clase
    function __construct()
    {
  
        //PARAMETROS DE LA APLICACION
        $this->array_parametros["servidor_bd"] = "localhost";    
        $this->array_parametros["charset_mysql"] = "UTF-8";
        $this->array_parametros["nombre_bd"] = "practica";       
        $this->array_parametros["usuario_bd"] = "adminidbcalldibs";
        $this->array_parametros["password_bd"] = "fOEUeGj1Z3LBM9q";
        $this->array_parametros["puerto_bd"] = "3306";
       
        //URL y carpeta raiz de la aplicación
        $this->array_parametros["protocolo_navegacion"]="http";
        //Ruta física y nombre de la carpeta raiz
        $this->array_parametros["carpeta_raiz"] = dirname(dirname(__FILE__));        
        $this->array_parametros["dominio_raiz"] = $this->obtenerDominioRaiz();

        $this->array_parametros["mostrar_errores_php"] ="1";
        $this->array_parametros["modo_debug"]="0";
        
        //WEBSERVICES
        $this->array_parametros['usuario_webservice']='master';
        $this->array_parametros['password_webservice']='cice';
        $this->array_parametros['token_webservice']='0123456789';

        //Establecemos la visualización de errores PHP
        if ($this->array_parametros["mostrar_errores_php"]=="1")
            error_reporting(E_ALL);
        else
            error_reporting(0);
    }
  
    public function obtenerClaveEncriptacion()
    {
        return $this->array_parametros['clave_encriptacion'];
    }

    public function obtenerModoDebug()
    {
        if($this->array_parametros['modo_debug']=='0')
            return false;
        else
            return true;
    }

    public function obtenerClaveAES()
    {
        return $this->array_parametros['clave_aes'];
    }

    public function obtenerCharSetMySQL()
    {
      if (array_key_exists("charset_mysql", $this->array_parametros))
          return $this->array_parametros["charset_mysql"];
      else
          return "";
    }

    public function obtenerNombreApp() 
    {       
        if (array_key_exists("nombre_app", $this->array_parametros))
            return $this->array_parametros["nombre_app"];
        else
            return "";
    }

    public function obtenerDominioRaiz() 
    {       
        $url=$this->array_parametros['protocolo_navegacion']."://".$_SERVER['HTTP_HOST'];            
        return $url;
    }

    public function obtenerCarpetaRaiz()
    {
        if (array_key_exists("carpeta_raiz", $this->array_parametros))
            return $this->array_parametros["carpeta_raiz"];
        else
            return "";
    }

    public function obtenerURLActual()
    {
        $url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        return $url;
    }

    public function obtenerServidorBD()
    {
        if (array_key_exists("servidor_bd", $this->array_parametros))
            return $this->array_parametros["servidor_bd"];
        else
            return "";
    }

    public function obtenerNombreBD()
    {
        if (array_key_exists("nombre_bd", $this->array_parametros))
            return $this->array_parametros["nombre_bd"];
        else
            return "";
    }

    public function obtenerUsuarioBD()
    {
        if (array_key_exists("usuario_bd", $this->array_parametros))
            return $this->array_parametros["usuario_bd"];
        else
            return "";
    }

    public function obtenerPasswordBD()
    {
        if (array_key_exists("password_bd", $this->array_parametros))
            return $this->array_parametros["password_bd"];
        else
            return "";
    }

    public function obtenerPuertoBD()
    {
        if (array_key_exists("puerto_bd", $this->array_parametros))
            return $this->array_parametros["puerto_bd"];
        else
            return "";
    }

    public function obtenerUsuarioWebservices() {
        if (array_key_exists("usuario_webservice", $this->array_parametros))
            return $this->array_parametros["usuario_webservice"];
        else
            return "";
    }

    public function obtenerPasswordWebservices() {
        if (array_key_exists("password_webservice", $this->array_parametros))
            return $this->array_parametros["password_webservice"];
        else
            return "";
    }

    public function obtenerTokenWebservices() {
        if (array_key_exists("token_webservice", $this->array_parametros))
            return $this->array_parametros["token_webservice"];
        else
            return "";
    }

    
}
?>