<?php

class clsUtilidades
{
    //Validación de array $_POST
    //Cada elemento puede indicarse como obligatorio (debe enviarse) y además como necesario estar rellenado ("1") o puede estar vacio "0"
    public function validarParametrosPost($arrayCamposObligatorios,$arrayCamposPost)
    {
        $arrayCamposNoEnviados=array();
        foreach ($arrayCamposObligatorios as $campoObligatorio=>$necesarioRellenado) 
        {
            if(array_key_exists($campoObligatorio, $arrayCamposPost))
            {
                //Si es necesario que este rellenado
                if($necesarioRellenado=="1")
                {
                    if(trim($arrayCamposPost[$campoObligatorio])=='')
                    {
                        $arrayCamposNoEnviados[$campoObligatorio]='parametro_vacio';
                    }
                }
            }
            else
            {
                $arrayCamposNoEnviados[$campoObligatorio]='parametro_no_enviado';
            }
        }
        return $arrayCamposNoEnviados;
    }


    public function obtenerCadenaParametros($arrayCamposNoEnviados){
        $cadena="";
        foreach($arrayCamposNoEnviados as $clave => $valor){
            $cadena .= "[". $clave . " - " . $valor. "] ";
        }
        return trim($cadena);
    }

    public function obtenerParametrosPOST(){        
        $post = json_decode(file_get_contents("php://input"), true);
        if(!$post){
            $post = $_POST;
        }
        return $post;
    }

}
?>