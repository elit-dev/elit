<?php
class Database
{
   
    static private $dbHost,
			  	   $dbName,
			  	   $dbUser,
			  	   $dbPassword;
	
	static protected $dbCon;
	
	static protected $fieldnames, $defaults;
	
	/*********************************************************************
	/	Inicializações neste método à parte, que é para podermos usar
	/	esta classe e descendentes num contexto estático.
	/	Abre a ligação à base de dados e estabelece parâmetros iniciais.
	*********************************************************************/
	static protected function _init() 
	{       
                global $config;
		// Dados de ligação definidos em framework.php
		self::$dbHost = $config['db_host'];
		self::$dbName = $config['db_name'];
		self::$dbUser = $config['db_username'];
		self::$dbPassword = $config['db_password'];
		
		// ESTABELECER LIGAÇÃO
		try {  
			self::$dbCon = new PDO("mysql:host=".self::$dbHost.";dbname=".self::$dbName, self::$dbUser, self::$dbPassword,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));  
		}  
		catch(PDOException $e) {  
		    die($e->getMessage());  
		}
		
		// ERROR MODE
		self::$dbCon->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	}	

	/*********************************************************************
	/	Constructor.
	*********************************************************************/
	public function __construct() 
	{ 
		self::_init();
	}	

	/*********************************************************************
	/	Função que devolve um array com placeholders correspondentes aos
	/	nomes das colunas utilizáveis da tabela, para construir os 
	/	prepared statements de SQL a utilizar através de PDO.
	/
	*********************************************************************/
	protected function _fields()
	{
		$fields = array();
		foreach(static::$fieldnames as $v)
		{
			$k = ':'.$v;
			$fields[$k] = $this->$v;
		}
		return $fields;
	}
	
	/*********************************************************************
	/	Grava o objecto na base de dados.
	/	Se a propriedade ID for nula, cria um novo registo, de
	/	contrário actualiza o registo correspondente.
	/
	/	Este método deve ser chamado dentro de objectos pertencentes a
	/	classes que estendam esta classe!
	/
	*********************************************************************/
	public function save()
	{
		// Será INSERT ou UPDATE?
		if($this->id==null){
			// INSERT
			// Aplicar DEFAULTS ao objecto SÓ SE FOR INSERT!!!
			foreach(static::$defaults as $k=>$v)
				$this->$k = $v;
			$field_list = "";
			if(static::$insert_timestamp!="")
			{
				$fields = implode(", ", array_merge(static::$fieldnames, array( static::$insert_timestamp ) ));
				$values = implode(", ", array_merge(static::$fieldvalues) ).", '". date("Y-m-d H:i:s") ."'";
			}
			else
			{
				$fields = implode(", ", static::$fieldnames );
				$values = implode(", ", static::$fieldvalues);
			}
			$sql = "INSERT INTO ".static::TABLENAME."(".$fields.") "."VALUES (".$values." );";
		}
		else
		{
			// Construir os pares fieldname/value da query
			$f = array();
			for($i=0; $i<count(static::$fieldnames); $i++)
				$f[] = static::$fieldnames[$i].'='.static::$fieldvalues[$i];

			if(static::$update_timestamp!="")
				$f[] = static::$update_timestamp.'=\''. date("Y-m-d H:i:s") .'\'';
			
			// UPDATE
			$sql = "UPDATE ".static::TABLENAME." SET ".implode(", ", $f )." WHERE id=".$this->id;
		}
		//error_log($sql,0);
		if(!is_object(self::$dbCon)) self::_init();
		$statement = self::$dbCon->prepare($sql);
		$statement->execute( $this->_fields() );
		if($this->id==null)
		$this->id = self::$dbCon->lastInsertId();
                return $this->id;
	}
	

	/*********************************************************************
	/	Função estática para obter um registo de uma tabela.
	/	Se o argumento for integer, é considerado o ID.
	/	Se o argumento for array, deverá ser um array associativo com
	/	pares coluna=>valor a ser utlizados na cláusula WHERE.
	/	
	/	Retorna uma linha da base de dados.
	/
	*********************************************************************/
	public static function fetch($args)
	{
		// Abrir a ligação à BD caso não esteja ainda aberta
		if(self::$dbCon==null) self::_init();
		// $args pode ser o ID ou pares de campos/valores
		if(is_int($args) || is_string($args))
		{
			// pesquisar por ID
			$sql = "SELECT * FROM ".static::TABLENAME." WHERE id=:id LIMIT 1;";
			$sth = self::$dbCon->prepare($sql);
			$sth->execute( array(':id'=>$args) );
		}
		else if(is_array($args))
		{
			// pesquisar pelos campos específicos
			$campos_arr = "";
			$v_arr = array();
			foreach($args as $k=>$v)
			{
				$campos_arr[] = "$k=:$k";
				$v_arr[":$k"] = $v;
			}
			$campos = "";
			$campos = implode(" AND ", $campos_arr);
			$sql = "SELECT * FROM ".static::TABLENAME." WHERE $campos LIMIT 1;";
			//echo $sql;
			//error_log("SQL: $sql");
			$sth = self::$dbCon->prepare($sql);
			$sth->execute($v_arr);
		}
		else
		{
			// erro
			error_log("Não estamos preparados para isto. class.Database.php : linha 148 : ".gettype($args)." : ".print_r($args,2));
			die("Não estamos preparados para isto. class.Database.php : linha 148 : ".gettype($args)." : ".print_r($args,2));
		}
		$sth->setFetchMode(PDO::FETCH_CLASS, get_called_class() );
		$row = $sth->fetch();
		//error_log(print_r($row,2));
		return $row;
	}

	/*********************************************************************
	/	Função estática para obter todos os registos de uma tabela.
	/	O argumento deverá ser um array associativo com
	/	pares coluna=>valor a ser utlizados na cláusula WHERE, e ainda
	/	'limit'=>x, 'count'=>y
	/	
	/	Retorna um array de objectos User.
	/
	*********************************************************************/
	public static function fetchAll($args=null)
	{
		// Abrir a ligação à BD caso não esteja ainda aberta
		if(self::$dbCon==null) self::_init();
		$v_arr = array();
		// Argumento pode não existir, nesse caso é mesmo todos os registos da tabela
		if($args==null)
		{
			$sql = "SELECT * FROM ".static::TABLENAME." WHERE 1;";
		}
		else if(is_array($args))
		{
		//error_log(print_r($args,1));
			// Argumento tem de ser array
			$limit = 0;
			$count = 0;
			$orderby = '';
			$custom = '';
			// pesquisar pelos campos específicos
			$campos_arr = "";
			
			foreach($args as $k=>$v)
			{
				if($k=='limit')
					$limit = $v;
				else if($k=='count')
					$count = $v;
				else if($k=='orderby')
					$orderby = $v;
				else if($k == 'custom')
					$custom = $v;
				else
				{
					$campos_arr[] = "$k=:$k";
					$v_arr[":$k"] = $v;
				}
			}
			$campos = "";
			$campos = implode(" AND ", $campos_arr);
				
			$sql = "SELECT * FROM ".static::TABLENAME." WHERE $campos $custom";
			if($limit>0) $sql.= " LIMIT $limit";
			if($count>0) $sql.= ",$count";
			if($orderby!='') $sql.= " ORDER BY $orderby";
			//error_log(" >>>>>>>>>>>>>>>>>>> $orderby");
		}
		else if(is_string($args))
		{
			$sql = "SELECT * FROM ".static::TABLENAME." WHERE $args";
		}
		else
		{
			// erro, caso o argumento não seja array
			error_log("Não estamos preparados para isto. class.Database.php : linha 212 : ".gettype($args)." : ".print_r($args,2));
			die("Não estamos preparados para isto. class.Database.php : linha 212 : ".gettype($args)." : ".print_r($args,2));
		}
		error_log("SQL: $sql");
		$sth = self::$dbCon->prepare($sql);
		$sth->execute($v_arr);
		$sth->setFetchMode(PDO::FETCH_CLASS, get_called_class() );
		$rows = $sth->fetchAll();
		//error_log(print_r($rows,2));
		return $rows;
	}

	public static function count($args)
	{
		// Abrir a ligação à BD caso não esteja ainda aberta
		if(self::$dbCon==null) self::_init();
		// $args pode ser o ID ou pares de campos/valores
		if(is_array($args))
		{
			// pesquisar pelos campos específicos
			$campos_arr = "";
			$v_arr = array();
			foreach($args as $k=>$v)
			{
				$campos_arr[] = "$k=:$k";
				$v_arr[":$k"] = $v;
			}
			$campos = "";
			$campos = implode(" AND ", $campos_arr);
			$sql = "SELECT COUNT(*) FROM ".static::TABLENAME." WHERE $campos LIMIT 1;";
			//error_log("SQL: $sql");
			$sth = self::$dbCon->prepare($sql);
			$sth->execute($v_arr);
		}
		else
		{
			// erro
			error_log("Não estamos preparados para isto. class.Database.php : linha 180 : ".gettype($args)." : ".print_r($args,2));
			die("Não estamos preparados para isto. class.Database.php : linha 180 : ".gettype($args)." : ".print_r($args,2));
		}
		$sth->setFetchMode(PDO::FETCH_NUM);
		$row = $sth->fetch();
		//error_log(print_r($row,2));
		return $row[0];
	}
	
	public static function countCustom($where)
	{
		// Abrir a ligação à BD caso não esteja ainda aberta
		if(self::$dbCon==null) self::_init();
		// $args pode ser o ID ou pares de campos/valores
		$sql = "SELECT COUNT(*) FROM ".static::TABLENAME." WHERE $where LIMIT 1;";
		//error_log("SQL: $sql");
		$sth = self::$dbCon->prepare($sql);
		$sth->execute();
	
		$sth->setFetchMode(PDO::FETCH_NUM);
		$row = $sth->fetch();
		//error_log(print_r($row,2));
		return $row[0];
	}
	
	public static function countCustomHistory($sql)
	{
		// Abrir a ligação à BD caso não esteja ainda aberta
		if(self::$dbCon==null) self::_init();
		// $args pode ser o ID ou pares de campos/valores
		//$sql = "SELECT COUNT(*) FROM ".static::TABLENAME." WHERE $where LIMIT 1;";
		//error_log("SQL: $sql");
		$sth = self::$dbCon->prepare($sql);
		$sth->execute();
	
		$sth->setFetchMode(PDO::FETCH_NUM);
		$row = $sth->fetch();
		//error_log(print_r($row,2));
		return $row[0];
	}
	
	/*********************************************************************
	/	Função que retorna dados de determinado objecto,
	/	baseado na clausula where passada
	*********************************************************************/
	public static function fetchCustom($sql, $mode='class')
	{
		// Abrir a ligação à BD caso não esteja ainda aberta
		if(self::$dbCon==null) self::_init();
		
		//$sql = "SELECT * FROM ".static::TABLENAME." WHERE ".$where.";";

		
		$sth = self::$dbCon->prepare($sql);
		//error_log($sql);
		$sth->execute();
		if($mode=='class') $sth->setFetchMode(PDO::FETCH_CLASS, get_called_class() );
		if($mode=='assoc') $sth->setFetchMode(PDO::FETCH_ASSOC);
		$rows = $sth->fetchAll();
		//error_log(print_r($rows,2));
		return $rows;
		
	}

	/*********************************************************************
	/	Função estática para eliminar registos de uma tabela.
	/	Se o argumento for integer, é considerado o ID.
	/	Se o argumento for array, deverá ser um array associativo com
	/	pares coluna=>valor a ser utlizados na cláusula WHERE.
	*********************************************************************/
	public static function delete($args)
	{
		// Abrir a ligação à BD caso não esteja ainda aberta
		if(self::$dbCon==null) self::_init();
		// $args pode ser o ID ou pares de campos/valores
		if(is_int($args))
		{
			// pesquisar por ID
			$sql = "DELETE FROM ".static::TABLENAME." WHERE id=:id LIMIT 1;";
			$sth = self::$dbCon->prepare($sql);
			$sth->execute( array(':id'=>$args) );
                        return 1;
		}
		else if(is_array($args))
		{
			// pesquisar pelos campos específicos
			$campos_arr = "";
			$v_arr = array();
			foreach($args as $k=>$v)
			{
				$campos_arr[] = "$k=:$k";
				$v_arr[":$k"] = $v;
			}
			$campos = "";
			$campos = implode(" AND ", $campos_arr);
			$sql = "DELETE FROM ".static::TABLENAME." WHERE $campos ;";
			//error_log("SQL: $sql");
			$sth = self::$dbCon->prepare($sql);
			$sth->execute($v_arr);
                        return 1;
		}
		else
		{
			// erro
			error_log("Não estamos preparados para isto. class.User.php : linha 52");
			die("Não estamos preparados para isto. class.User.php : linha 52");
                        return 0;
		}
	}


	/*********************************************************************
	/	Função estática para executar SQL.
	*********************************************************************/
	public static function execute($sql)
	{
		// Abrir a ligação à BD caso não esteja ainda aberta
		if(self::$dbCon==null) self::_init();
		// $args pode ser o ID ou pares de campos/valores
		$sth = self::$dbCon->prepare($sql);
		$sth->execute();
	}


	/*********************************************************************
	/	Métodos que permitem a relação do Objecto $this por outro
	/	definido com parametro entrada $obj
	/	O parametro de entrada para inserção é sempre um e só um objecto
	/   O parametro de entrada para retorno dos dados 
	/   é o nome da class (string) com a qual se pretende retornar 
	/	a associação e as clausulas where do objecto de entrada
	/	Retorna sempre um array de objectos
	/   As relações são feitas de n para n
	*********************************************************************/
	
	public function relateTo($obj)
	{
		if(self::$dbCon==null) self::_init();
		
		$arrCols = array(get_class($this)=>$this->id,get_class($obj)=>$obj->id);
		ksort($arrCols);
				
		$tableName = '';
		$colunas = '';
		$valores = '';
		$colunasType = ''; 
		
		foreach($arrCols as $key=>$value)
		{
			$tableName .= $key;
			$colunasType .= $key.'id,';
			$colunas .= strtolower($key).'id,';
			$valores .= $value.',';
		}
		
		$colunas = rtrim($colunas,',');
		$valores = rtrim($valores,',');
		
		$arrColunas = explode(',',$colunasType);
					
		$sql = 'CREATE TABLE IF NOT EXISTS '. APPLICATION_TABLE_PREFIX . strtolower($tableName).' ('.strtolower($arrColunas[0]).' int not null, '. strtolower($arrColunas[1]).' int not null, primary key ('.strtolower($arrColunas[0]) .','.strtolower($arrColunas[1]).'));';
		$sql .= 'INSERT IGNORE INTO '.APPLICATION_TABLE_PREFIX. strtolower($tableName).' ('.$colunas.') VALUES ('.$valores.');';
		
		//echo $sql;
		$sth = self::$dbCon->prepare($sql);
		$sth->execute();
		//	echo $sql;		
	}
	
	public function fetchRelated($className, $args=null)
	{
		if(self::$dbCon==null) self::_init();
		
		if($this->id != null)
		{
			$arrCols = array(strtolower(get_class($this)),strtolower($className));
			sort($arrCols);
			
			// pesquisar pelos campos específicos
			$campos = "";
			$custom = "";
			$v_arr = array();
			if($args != null)
			{
				$campos = " AND ";
				$campos_arr = "";
				foreach($args as $k=>$v)
				{	
					if($k == 'custom')
					{
						$custom = $v;
					}
					else
					{
						$campos_arr[] = "$k=:$k";
						$v_arr[":$k"] = $v;
					}
				}
				$campos .= implode(" AND ", $campos_arr);
			}
			
			$tableAssocName = APPLICATION_TABLE_PREFIX . strtolower($arrCols[0]).strtolower($arrCols[1]);
			
			$tableThisName = APPLICATION_TABLE_PREFIX . strtolower(get_class($this));
			
			
			$sql = 'SELECT * FROM '. APPLICATION_TABLE_PREFIX . strtolower($className) .' where  id in ( select '. strtolower($className) .'id from '. $tableAssocName .' where '. strtolower(get_class($this)).'id = '. $this-> id.'  ) '. $campos .' '. $custom .';'; 
			
			error_log('SQL FETCHRELATED: '.$sql,0);
			$sth = self::$dbCon->prepare($sql);
			$sth->execute($v_arr);
			$sth->setFetchMode(PDO::FETCH_CLASS, $className );
			$rows = $sth->fetchAll();
			//error_log(print_r($rows,2));
			return $rows;
		}
	}
	
	public function unrelateTo($obj)
	{
		if(self::$dbCon==null) self::_init();
		
		$arrCols = array(get_class($this)=>$this->id,get_class($obj)=>$obj->id);
		ksort($arrCols);
				
		$tableName = '';
		$colunas = '';
		$valores = '';
		$colunasType = ''; 
		
		
		$where = '';
		foreach($arrCols as $key=>$value)
		{
			$tableName .= $key;
			
			$where .= strtolower($key).'id = '.$value.' and ';
			
			
			
			$colunasType .= $key.'id,';
			$colunas .= strtolower($key).'id,';
			$valores .= $value.',';
		}
		
		$where = rtrim($where,' and ');
		
		$arrColunas = explode(',',$colunasType);
					
		$sql = 'DELETE FROM '. APPLICATION_TABLE_PREFIX . strtolower($tableName) . ' WHERE '.$where.' LIMIT 1;';
		
		$sth = self::$dbCon->prepare($sql);
		$sth->execute();
	}
	
	public function unrelateAllTo($className)
	{
	
		if(self::$dbCon==null) self::_init();
		
		$arrCols = array(strtolower(get_class($this)),strtolower($className));
		sort($arrCols);
		
		$tableName = $arrCols[0].$arrCols[1];
		$where = strtolower(get_class($this)) . 'id='. $this->id; 
					
		$sql = 'DELETE FROM '. APPLICATION_TABLE_PREFIX . strtolower($tableName) . ' WHERE '.$where.';';
		
		try
		{
			$sth = self::$dbCon->prepare($sql);
			$sth->execute();
		}
		catch(Exception $e)
		{
			error_log($e,0);
			error_log($sql);	
			
		}

	}
	
	
		
	/*********************************************************************
	/	Destructor.
	/	Dá a ligação à base de dados como fechada.
	*********************************************************************/
	public function __destruct()
	{
		self::$dbCon = null;
	}
}
?>
