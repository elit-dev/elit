<?php
class User extends Database
{
	public $id;
	public $nome;
	public $email;
	public $password;
	public $perfil;
	public $datacriado;
	public $dataalterado;
	public $estado;
	public $chatonline;
	public $tipo;
	public $sexo;
	public $telefone;
	public $foto;
	public $cargo;
	public $datanascimento;
	public $chatsom;
	public $coordenador;
	public $entidadesessao;
	public $telefone2;
	public $observacoes;
	public $departamento;
	public $sector;
	public $categoria;
	
	
	
	// Nomes das colunas a utilizar no insert e no update, SEM contar com a coluna ID.
	static protected $fieldnames  = array('nome','email','password','perfil','estado','chatonline','tipo','sexo','telefone','foto','cargo','datanascimento','chatsom','coordenador','entidadesessao','telefone2','observacoes','departamento','sector','categoria');
	static protected $fieldvalues = array(':nome',':email',':password',':perfil',':estado',':chatonline',':tipo',':sexo',':telefone',':foto',':cargo',':datanascimento',':chatsom',':coordenador',':entidadesessao',':telefone2',':observacoes',':departamento',':sector',':categoria');
	// Nomes das colunas com datas de cria��o e altera��o do registo
	static protected $insert_timestamp = 'datacriado';
	static protected $update_timestamp = 'dataalterado';
	// Array com defaults que s�o aplicados ao objecto antes de grava��o na BD
	static protected $defaults;
	
	// Nome da tabela na base de dados
	const TABLENAME = "fhassist_user";
	
	// Tipos de utilizador
	const TIPO_USER  = 2;
	const TIPO_ADMIN = 1;
	
	// Estado do registo de utilizador
	const ESTADO_PENDENTE   = 0;
	const ESTADO_CONFIRMADO = 1;
	const ESTADO_APAGADO  = -1;
	
	// ChatOnline
	const CHATONLINE_NAO = 0;
	const CHATONLINE_SIM = 1;
	
	//Tipo
	/*const TIPO_MEDICO = 1;
	const TIPO_ENFERMEIRO = 2;
	const TIPO_ADMINISTRATIVO = 3;*/
	
	/*********************************************************************
	/	Construtor.
	*********************************************************************/
	public function __construct()
	{
		parent::__construct();
		// definir DEFAULTS
		self::$defaults = array('chatonline'=>self::CHATONLINE_NAO, );
	}
	
	public static function getByName($nome,$usf)
	{
		$sql = "SELECT * FROM ".static::TABLENAME." WHERE nome like '%".$nome."%' and estado >= 0 AND id IN (SELECT userid FROM fhassist_userusf where usfid =".$usf->id.");";
				
		//$args = array(':nome'=>$nome);
		
		return self::fetchCustom($sql);	
	}
	
	public function titulo()
	{
		if($this->sexo == 'm' && $this->tipo == 1)
		{
			return 'Dr. ';
		}
		elseif($this->sexo == 'f' && $this->tipo == 1)
		{
			return 'Dra. ';
		}
				if($this->sexo == 'm' && $this->tipo == 1)
		{
			return 'Dr.';
		}
		elseif($this->sexo == 'm' && $this->tipo == 2)
		{
			return 'Enfermeiro ';
		}
		elseif($this->sexo == 'f' && $this->tipo == 2)
		{
			return 'Enfermeira ';
		}
		else
		{
			return '';	
		}
	}
	
	public function gerirutilizadores()
	{
		$permissoes = $this->fetchRelated('Permissao',array('id'=>1));
		
		if(count($permissoes) == 1)
		{
			return true;
		}
		else
		{
			return true;
		}
	}
	
	public function gerirdocumentos()
	{
		$permissoes = $this->fetchRelated('Permissao',array('id'=>13));
		
		if(count($permissoes) == 1)
		{
			return true;
		}
		else
		{
			return False;
		}
	}
	
	public function gerirhorarios()
	{
		$permissoes = $this->fetchRelated('Permissao',array('id'=>3));
		
		if(count($permissoes) == 1)
		{
			return true;
		}
		else
		{
			return true;
		}
	}
	
	public function gerirapoioclinico()
	{
		$permissoes = $this->fetchRelated('Permissao',array('id'=>7));
		
		if(count($permissoes) == 1)
		{
			return true;
		}
		else
		{
			return true;
		}
	}
	
	public function gerirusf()
	{
		$permissoes = $this->fetchRelated('Permissao',array('id'=>8));
		
		if(count($permissoes) == 1)
		{
			return true;
		}
		else
		{
			return true;
		}
	}
	
	public function gerircontactos()
	{
		$permissoes = $this->fetchRelated('Permissao',array('id'=>9));
		
		if(count($permissoes) == 1)
		{
			return true;
		}
		else
		{
			return true;
		}
	}
	
	public function gerirformacao()
	{
		$permissoes = $this->fetchRelated('Permissao',array('id'=>10));
		
		if(count($permissoes) == 1)
		{
			return true;
		}
		else
		{
			return true;
		}
	}
	
	public static function getUsersReport()
	{
		$sql = "SELECT fhassist_usf.id as _uid, fhassist_usf.nome, COUNT(userid) AS todos, SUM(fhassist_user.tipo=1) AS Medicos, SUM(fhassist_user.tipo=2) AS Enfermeiros, SUM(fhassist_user.tipo=3) AS Administrativos, fhassist_user.id, fhassist_userusf.* FROM fhassist_user, fhassist_userusf, fhassist_usf WHERE fhassist_user.id=fhassist_userusf.userid AND fhassist_userusf.usfid=fhassist_usf.id GROUP BY _uid";
		return self::fetchCustom($sql, 'assoc');
	}
	
	public static function getOnline($user, $usfid)
	{
		$dataActual = date("Y-m-d H:i:s");
		
		//$sql = "SELECT * FROM ".static::TABLENAME." WHERE  AND chatonline = 1 AND estado >= 0 AND id <> ".$user->id." AND id IN (SELECT userid FROM fhassist_userusf where usfid =".$usfid.");";
		
		$sql = "SELECT  u.id, u.nome, u.email, u.password, u.perfil, u.datacriado, u.dataalterado, u.estado, u.chatonline, u.tipo, u.sexo, u.telefone, u.foto  FROM ".static::TABLENAME." as u inner join fhassist_session as s on u.id = s.userid  WHERE  chatonline = 1 AND estado >= 0 AND u.id <> ".$user->id." AND u.id IN (SELECT userid FROM fhassist_userusf where usfid = ".$usfid.") AND TIME_TO_SEC(TIMEDIFF('".$dataActual."',s.datalastactivity)) <= 20;";
		
		
		return self::fetchCustom($sql);
		
		//SELECT u.id, u.nome, u.email, u.password, u.perfil, u.datacriado, u.dataalterado, u.estado, u.chatonline, u.tipo, u.sexo, u.telefone, u.foto FROM fhassist_user as u inner join fhassist_session as s on u.id = s.userid  WHERE  chatonline = 0 AND estado >= 0 AND u.id <> 1 AND u.id IN (SELECT userid FROM fhassist_userusf where usfid = 4);


	}
	
	public static function getOnSite($user, $usfid)
	{
		$dataActual = date("Y-m-d H:i:s");
		
		//$sql = "SELECT * FROM ".static::TABLENAME." WHERE  AND chatonline = 1 AND estado >= 0 AND id <> ".$user->id." AND id IN (SELECT userid FROM fhassist_userusf where usfid =".$usfid.");";
		
		$sql = "SELECT  u.id, u.nome, u.email, u.password, u.perfil, u.datacriado, u.dataalterado, u.estado, u.chatonline, u.tipo, u.sexo, u.telefone, u.foto  FROM ".static::TABLENAME." as u inner join fhassist_session as s on u.id = s.userid  WHERE estado >= 0 AND u.id <> ".$user->id." AND u.id IN (SELECT userid FROM fhassist_userusf where usfid = ".$usfid.") AND TIME_TO_SEC(TIMEDIFF('".$dataActual."',s.datalastactivity)) <= 40;";
		
		
		return self::fetchCustom($sql);
		
		//SELECT u.id, u.nome, u.email, u.password, u.perfil, u.datacriado, u.dataalterado, u.estado, u.chatonline, u.tipo, u.sexo, u.telefone, u.foto FROM fhassist_user as u inner join fhassist_session as s on u.id = s.userid  WHERE  chatonline = 0 AND estado >= 0 AND u.id <> 1 AND u.id IN (SELECT userid FROM fhassist_userusf where usfid = 4);


	}
}
?>