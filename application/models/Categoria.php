<?php
class Categoria extends Database
{
	public $id;
	public $parentid;
	public $nome;
	public $descricao;
	public $nivel;
	public $estado;
	public $datacriado;
	public $dataalterado;
	
	// Nomes das colunas a utilizar no insert e no update, SEM contar com a coluna ID.
	static protected $fieldnames  = array('parentid','nome','descricao','nivel','estado',);
	static protected $fieldvalues = array(':parentid',':nome',':descricao',':nivel',':estado',);
	
	// Nomes das colunas com datas de criação e alteração do registo
	static protected $insert_timestamp = 'datacriado';
	static protected $update_timestamp = 'dataalterado';
	
	// Array com defaults que são aplicados ao objecto antes de gravação na BD
	static protected $defaults = array();
	
	// Nome da tabela na base de dados
	const TABLENAME = "fhassist_categoria";
	
	/*********************************************************************
	/	Construtor.
	*********************************************************************/
	public function __construct()
	{
		parent::__construct();
		
		// definir DEFAULTS se houver
		
	}
	
	public static function getByUsfIdParentId($usfid,$parentid)
	{
		$sql = "SELECT * FROM ".static::TABLENAME." WHERE parentid = ".$parentid." and id in (select categoriaid from fhassist_categoriausf where usfid = ".$usfid.") ORDER BY nome ASC";
				
		//$args = array(':nome'=>$nome);
		
		return self::fetchCustom($sql);	
	}
        
        public static function getAll($usfid,$type=1)
                
	{       
                $typeSql = $type ==2 ? "group by categoriaid having count(*)>=2" : " ";
		$sql = "SELECT id , parentid , nome  FROM ".static::TABLENAME." WHERE id in (select distinct(categoriaid) from fhassist_categoriausf where usfid IN(".$usfid.") ".$typeSql.") ORDER BY nome ASC";
				
		//$args = array(':nome'=>$nome);
		
		return self::fetchCustom($sql);	
	}
}
?>