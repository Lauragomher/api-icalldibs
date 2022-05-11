<?php
class clsConsultaSQL
{
	//Array de campos select. Key=nombre_bd Valor=nombre_mostrar 
	private $array_campos_select = array(); 
	private $array_tablas_from = array(); 
	private $array_tablas_inner_join = array();	
	private $array_tablas_left_join = array();	
	private $array_tablas_right_join = array();	

	//filtrado de datos invariable
	private $array_where_filtrado_politica_seguridad=array();
	//busqueda por todos los campos, el where se encierra entre () si no existe el anterior, y con and () si ya existe
	private $array_where_todos_campos=array();	
	//Busqueda mediante filtrado de un campo. Si existe el where anterior se incluye and ()
	private $array_where=array();

	//Grupo adicional de Busqueda mediante filtrado de un campo. Si existe el where anterior se incluye and ()
	//Permite añadir otro grupo de condiciones entre paréntesis
	private $array_where2=array();

	private $array_campos_orderby=array();
	private $array_campos_groupby=array();

	private $limitInferior;
	private $limitFilas;

	private $cadenaSQL;
	private $cadenaConteoSQL;

	function __construct()
	{
		$cadenaSQL='';
		$cadenaConteoSQL='';
		$limitInferior='0';
		$limitFilas='0';
	}

	public function construirConsultaSQL()
	{
		$indice=1;		
		$this->cadenaSQL='select ';
		//Construimos los campos select
		foreach ($this->array_campos_select as $nombre_campo_bd => $nombre_campo_mostrar) 
		{
			if($indice==count($this->array_campos_select))
				$this->cadenaSQL.=$nombre_campo_bd. " as `".$nombre_campo_mostrar."` ";
			else
				$this->cadenaSQL.=$nombre_campo_bd. " as `".$nombre_campo_mostrar."`, ";
			$indice++;
		}	
	
		//Contruimos el from
		$indice=1;
		if (count($this->array_tablas_from)>0)
		{
			$this->cadenaSQL.=' from ';
			foreach ($this->array_tablas_from as $nombre_tabla_from) 
			{
				if($indice==count($this->array_tablas_from))
					$this->cadenaSQL.=$nombre_tabla_from. ' ';
				else
					$this->cadenaSQL.=$nombre_tabla_from.', ';
				$indice++;
			}	
		}	

		//construimos inner join
		if (count($this->array_tablas_inner_join)>0)
		{
			foreach ($this->array_tablas_inner_join as $nombre_tabla => $condicion) 
			{
				$this->cadenaSQL.=' inner join ';				
				$this->cadenaSQL.=$nombre_tabla.' on '.$condicion;
			}	
		}	

		//construimos left join
		if (count($this->array_tablas_left_join)>0)
		{
			foreach ($this->array_tablas_left_join as $nombre_tabla => $condicion) 
			{
				$this->cadenaSQL.=' left join ';				
				$this->cadenaSQL.=$nombre_tabla.' on '.$condicion;
			}	
		}	

		//construimos right join
		if (count($this->array_tablas_right_join)>0)
		{
			foreach ($this->array_tablas_right_join as $nombre_tabla => $condicion) 
			{
				$this->cadenaSQL.=' right join ';				
				$this->cadenaSQL.=$nombre_tabla.' on '.$condicion;
			}	
		}	
		//Contruimos el where de filtrado de politica de seguridad
		if (count($this->array_where_filtrado_politica_seguridad)>0)
		{
			foreach ($this->array_where_filtrado_politica_seguridad as $condicion) 
			{
					$this->cadenaSQL.=$condicion. ' ';
			}	
			//Cerramos el paréntesis que abrió el primer elemento
			$this->cadenaSQL.= ')';
		}	

		//Contruimos el where primario
		if (count($this->array_where)>0)
		{
			//Si ya se ha aplicado un filtrado de politica de seguriodad
			if (count($this->array_where_filtrado_politica_seguridad)>0)				
			{
				$this->cadenaSQL.=' and(';
				$indice=0;	
				foreach ($this->array_where as $condicion) 
				{
					if ($indice==0)
						$this->cadenaSQL.=$condicion[1].' '.$condicion[2].' ';
					else
						$this->cadenaSQL.=$condicion[0].' '.$condicion[1].' '.$condicion[2].' ';
					$indice++;
				}
				$this->cadenaSQL.=') ';					
			}
			//Si no se ha aplicado un filtrado where de politica de seguridad anteriormente
			else
			{
				$indice=0;	
				foreach ($this->array_where as $condicion) 
				{
					if ($indice==0)
						$this->cadenaSQL.= ' where ('.$condicion[1].' '.$condicion[2].' ';
					else
						$this->cadenaSQL.=$condicion[0].' '.$condicion[1].' '.$condicion[2].' ';
					$indice++;
				}
				//Cerramos el paréntesis que abrió el primer elemento
				$this->cadenaSQL.=') ';									
			}	
		}

		//Contruimos el where secundario
		//Permite un segundo grupo de condiciones () and ()
		if (count($this->array_where2)>0)
		{
			//Si ya se ha aplicado un filtrado de politica de seguridad
			if (count($this->array_where_filtrado_politica_seguridad)>0 || count($this->array_where)>0)				
			{
				$this->cadenaSQL.=' and(';
				$indice=0;	
				foreach ($this->array_where2 as $condicion) 
				{
					if ($indice==0)
						$this->cadenaSQL.=$condicion[1].' '.$condicion[2].' ';
					else
						$this->cadenaSQL.=$condicion[0].' '.$condicion[1].' '.$condicion[2].' ';
					$indice++;
				}
				$this->cadenaSQL.=') ';					
			}
			//Si no se ha aplicado un filtrado where de politica de seguridad o where primario anteriormente
			else
			{
				$indice=0;	
				foreach ($this->array_where2 as $condicion) 
				{
					if ($indice==0)
						$this->cadenaSQL.= ' where ('.$condicion[1].' '.$condicion[2].' ';
					else
						$this->cadenaSQL.=$condicion[0].' '.$condicion[1].' '.$condicion[2].' ';
					$indice++;
				}
				//Cerramos el paréntesis que abrió el primer elemento
				$this->cadenaSQL.=') ';									
			}	
		}


		//Contruimos el Group By
		if (count($this->array_campos_groupby)>0)
		{
			foreach ($this->array_campos_groupby as $agrupacion) 
			{
					$this->cadenaSQL.=$agrupacion. ' ';
			}	
		}	

		//Contruimos el Order By
		if (count($this->array_campos_orderby)>0)
		{
			foreach ($this->array_campos_orderby as $ordenacion) 
			{
					$this->cadenaSQL.=$ordenacion. ' ';
			}	
		}			

		if($this->limitFilas!='0' && $this->limitFilas!='')
		{
			$this->cadenaSQL.= ' limit '.$this->limitInferior.', '.$this->limitFilas;	
		}
		
	}


	public function construirConsultaConteoSQL()
	{
		$indice=1;		
		$this->cadenaConteoSQL='select count(1) ';				
	
		//Contruimos el from
		$indice=1;
		if (count($this->array_tablas_from)>0)
		{
			$this->cadenaConteoSQL.=' from ';
			foreach ($this->array_tablas_from as $nombre_tabla_from) 
			{
				if($indice==count($this->array_tablas_from))
					$this->cadenaConteoSQL.=$nombre_tabla_from. ' ';
				else
					$this->cadenaConteoSQL.=$nombre_tabla_from.', ';
				$indice++;
			}	
		}	

		//construimos inner join
		if (count($this->array_tablas_inner_join)>0)
		{
			foreach ($this->array_tablas_inner_join as $nombre_tabla => $condicion) 
			{
				$this->cadenaConteoSQL.=' inner join ';				
				$this->cadenaConteoSQL.=$nombre_tabla.' on '.$condicion;
			}	
		}	

		//construimos left join
		if (count($this->array_tablas_left_join)>0)
		{
			foreach ($this->array_tablas_left_join as $nombre_tabla => $condicion) 
			{
				$this->cadenaConteoSQL.=' left join ';				
				$this->cadenaConteoSQL.=$nombre_tabla.' on '.$condicion;
			}	
		}	

		//construimos right join
		if (count($this->array_tablas_right_join)>0)
		{
			foreach ($this->array_tablas_right_join as $nombre_tabla => $condicion) 
			{
				$this->cadenaConteoSQL.=' right join ';				
				$this->cadenaConteoSQL.=$nombre_tabla.' on '.$condicion;
			}	
		}	

		//Contruimos el where de filtrado de politica de seguridad
		if (count($this->array_where_filtrado_politica_seguridad)>0)
		{
			foreach ($this->array_where_filtrado_politica_seguridad as $condicion) 
			{
					$this->cadenaConteoSQL.=$condicion. ' ';
			}	
			//Cerramos el paréntesis que abrió el primer elemento
			$this->cadenaConteoSQL.= ')';
		}	

		//Contruimos el where primario
		if (count($this->array_where)>0)
		{
			//Si ya se ha aplicado un filtrado de politica de seguriodad
			if (count($this->array_where_filtrado_politica_seguridad)>0)				
			{
				$this->cadenaConteoSQL.=' and(';
				$indice=0;	
				foreach ($this->array_where as $condicion) 
				{
					if ($indice==0)
						$this->cadenaConteoSQL.=$condicion[1].' '.$condicion[2].' ';
					else
						$this->cadenaConteoSQL.=$condicion[0].' '.$condicion[1].' '.$condicion[2].' ';
					$indice++;
				}
				$this->cadenaConteoSQL.=') ';					
			}
			//Si no se ha aplicado un filtrado where de politica de seguridad anteriormente
			else
			{
				$indice=0;	
				foreach ($this->array_where as $condicion) 
				{
					if ($indice==0)
						$this->cadenaConteoSQL.= ' where ('.$condicion[1].' '.$condicion[2].' ';
					else
						$this->cadenaConteoSQL.=$condicion[0].' '.$condicion[1].' '.$condicion[2].' ';
					$indice++;
				}
				//Cerramos el paréntesis que abrió el primer elemento
				$this->cadenaConteoSQL.=') ';									
			}	
		}

		//Contruimos el where secundario
		//Permite un segundo grupo de condiciones () and ()
		if (count($this->array_where2)>0)
		{
			//Si ya se ha aplicado un filtrado de politica de seguridad
			if (count($this->array_where_filtrado_politica_seguridad)>0 || count($this->array_where)>0)				
			{
				$this->cadenaConteoSQL.=' and(';
				$indice=0;	
				foreach ($this->array_where2 as $condicion) 
				{
					if ($indice==0)
						$this->cadenaConteoSQL.=$condicion[1].' '.$condicion[2].' ';
					else
						$this->cadenaConteoSQL.=$condicion[0].' '.$condicion[1].' '.$condicion[2].' ';
					$indice++;
				}
				$this->cadenaConteoSQL.=') ';					
			}
			//Si no se ha aplicado un filtrado where de politica de seguridad o where primario anteriormente
			else
			{
				$indice=0;	
				foreach ($this->array_where2 as $condicion) 
				{
					if ($indice==0)
						$this->cadenaConteoSQL.= ' where ('.$condicion[1].' '.$condicion[2].' ';
					else
						$this->cadenaConteoSQL.=$condicion[0].' '.$condicion[1].' '.$condicion[2].' ';
					$indice++;
				}
				//Cerramos el paréntesis que abrió el primer elemento
				$this->cadenaConteoSQL.=') ';									
			}	
		}
		

		//Contruimos el Group By
		if (count($this->array_campos_groupby)>0)
		{
			foreach ($this->array_campos_groupby as $agrupacion) 
			{
					$this->cadenaConteoSQL.=$agrupacion. ' ';
			}	
		}		
	}


	public function obtenerNumeroCamposSelect()
	{
		return  count($this->array_campos_select);
	}

	public function obtenerConsultaSQL()
	{
		$this->construirConsultaSQL();	
		return $this->cadenaSQL;
	}

	public function obtenerCamposSelect()
	{
		return $this->array_campos_select;
	}

	public function obtenerConsultaConteoSQL()
	{
		$this->construirConsultaConteoSQL();
		return $this->cadenaConteoSQL;
	}

	public function addCampoSelect($nombre_bd,$nombre_mostrar)
	{
		$this->array_campos_select[$nombre_bd]=$nombre_mostrar;
	}

	public function addTablaFrom($nombre_tabla)
	{
		array_push($this->array_tablas_from, $nombre_tabla);		
	}

	public function addTablaInnerJoin($nombre_tabla,$condicion)
	{
		$this->array_tablas_inner_join[$nombre_tabla]=$condicion;		
	}

	public function addTablaLeftJoin($nombre_tabla,$condicion)
	{
		$this->array_tablas_left_join[$nombre_tabla]=$condicion;		
	}

	public function addTablaRightJoin($nombre_tabla,$condicion)
	{
		$this->array_tablas_right_join[$nombre_tabla]=$condicion;		
	}

 	public function addCondicionWhere($campo,$condicion,$operadorLogico='')
	{
		if (empty($this->array_where) && empty($this->array_where_filtrado_politica_seguridad))
			array_push($this->array_where, array('',$campo,$condicion));
		else
		{
			array_push($this->array_where, array($operadorLogico,$campo,$condicion));
		}		
	}

 	public function addCondicionWhereSecundario($campo,$condicion,$operadorLogico='')
	{
		if (empty($this->array_where) && empty($this->array_where2) && empty($this->array_where_filtrado_politica_seguridad))
			array_push($this->array_where2, array('',$campo,$condicion));
		else
		{
			array_push($this->array_where2, array($operadorLogico,$campo,$condicion));
		}		
	}


	public function construirFiltradoParaTodosLosCampos($filtrado)
	{
		//Construimos un where con like para un valor en todos los campos de la consulta
		foreach ($this->array_campos_select as $nombre_campo_bd => $nombre_campo_mostrar) 
		{
			//Eliminamos del filtrado funciones MySQL
			$posSUM=strpos($this->eliminarEspaciosCadena($nombre_campo_bd), "sum(");
			if($posSUM===false)
				$this->addCondicionWhere(str_replace('distinct', '', $nombre_campo_bd),"like '%".$this->eliminarCaracteresNoValidos($filtrado)."%'"," or ");
		}	
	}

	private function eliminarEspaciosCadena($cadena){
    	$cadena = str_replace(' ', '', $cadena);
    	return $cadena;
	}

	public function eliminarCaracteresNoValidos($cadena)
	{
		$cadenaValida=str_replace("'","", $cadena);
		$cadenaValida=str_replace("\"","", $cadenaValida);
		return $cadenaValida;	
	}

	public function addCondicionWhereFiltradoPoliticaSeguridad($campo,$condicion,$operadorLogico=' and ')
	{
		if (empty($this->array_where_filtrado_politica_seguridad) && empty($this->array_where) && empty($this->array_where2))
			array_push($this->array_where_filtrado_politica_seguridad, " where (".$campo.' '.$condicion);
		else
			array_push($this->array_where_filtrado_politica_seguridad, " ".$operadorLogico." ".$campo.' '.$condicion);
	}

	public function addCampoOrderBy($campo,$tipoOrdenacion=' asc')
	{
		if (empty($this->array_campos_orderby))
			array_push($this->array_campos_orderby, " order by ".$campo. " ".$tipoOrdenacion);
		else
			array_push($this->array_campos_orderby, ", ".$campo." ".$tipoOrdenacion);
	}

	public function addCampoGroupBy($campo)
	{
		if (empty($this->array_campos_groupby))
			array_push($this->array_campos_groupby, " group by ".$campo);
		else
			array_push($this->array_campos_groupby, ", ".$campo);
	}

	public function establecerLimitInferior($limit)
	{
		$this->limitInferior=$limit;
	}

	public function establecerLimitFilas($limit)
	{
		$this->limitFilas=$limit;
	}

	public function obtenerArrayCamposSelect()
	{
		return $this->array_campos_select;
	}

	public function obtenerArrayWhere()
	{
		return $this->array_where;
	}

	public function obtenerArrayWhereFiltradoPoliticaSeguridad()
	{
		return $this->array_where_filtrado_politica_seguridad;
	}

	public function obtenerFrom()
	{
		$cadena='';
		//Contruimos el from
		$indice=1;
		if (count($this->array_tablas_from)>0)
		{
			$cadena.=' from ';
			foreach ($this->array_tablas_from as $nombre_tabla_from) 
			{
				if($indice==count($this->array_tablas_from))
					$cadena.=$nombre_tabla_from. ' ';
				else
					$cadena.=$nombre_tabla_from.', ';
				$indice++;
			}	
		}	
		return $cadena;
	}
		
	public function obtenerInnerJoin()
	{
		$cadena='';
		//construimos inner join
		if (count($this->array_tablas_inner_join)>0)
		{
			foreach ($this->array_tablas_inner_join as $nombre_tabla => $condicion) 
			{
				$cadena.=' inner join ';				
				$cadena.=$nombre_tabla.' on '.$condicion;
			}	
		}	
		return $cadena;
	}

	public function obtenerLeftJoin()
	{
		$cadena='';
		//construimos left join
		if (count($this->array_tablas_left_join)>0)
		{
			foreach ($this->array_tablas_left_join as $nombre_tabla => $condicion) 
			{
				$cadena.=' left join ';				
				$cadena.=$nombre_tabla.' on '.$condicion;
			}	
		}	
		return $cadena;
	}

	public function obtenerRightJoin()
	{
		$cadena='';
		//construimos right join
		if (count($this->array_tablas_right_join)>0)
		{
			foreach ($this->array_tablas_right_join as $nombre_tabla => $condicion) 
			{
				$cadena.=' right join ';				
				$cadena.=$nombre_tabla.' on '.$condicion;
			}	
		}	
		return $cadena;
	}

	public function obtenerWhere()
	{
		$cadena='';
		//Contruimos el where
		if (count($this->array_where)>0)
		{
			foreach ($this->array_where as $condicion) 
			{
					$cadena.=$condicion. ' ';
			}	
		}	
		return $cadena;
	}

	public function obtenerWhereFiltradopoliticaSeguridad()
	{
		$cadena='';
		//Contruimos el where
		if (count($this->array_where_filtrado_politica_seguridad)>0)
		{
			foreach ($this->array_where_filtrado_politica_seguridad as $condicion) 
			{
					$cadena.=$condicion. ' ';
			}	
		}	
		return $cadena;
	}


	public function obtegerGroupby()
	{
		$cadena='';
		//Contruimos el Group By
		if (count($this->array_campos_groupby)>0)
		{
			foreach ($this->array_campos_groupby as $agrupacion) 
			{
					$cadena.=$agrupacion. ' ';
			}	
		}	
	 	$cadena='';
	} 	

		public function obtenerLimitInferior()
	{
		if ($this->limitInferior<>'')
		{
			return $this->limitInferior;		
		}	
		else
			return 0;
	} 	

	public function obtenerLimitFilas()
	{
		if ($this->limitFilas<>'')
		{
			return $this->limitFilas;		
		}	
		else
			return 0;
	} 	
}
?>