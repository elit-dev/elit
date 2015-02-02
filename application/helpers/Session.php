<?php

class Session extends Database
{
    public $sessionIdentifier,
    $id,
    $userid,
    $sessionid,
    $datalogin,
    $datalastactivity,
    $ip;

    // Nomes das colunas a utilizar no insert e no update, SEM contar com a coluna ID.
    static protected $fieldnames  = array('userid',   'sessionid',  'ip', );
    static protected $fieldvalues = array(':userid', ':sessionid', ':ip', );
    // Nomes das colunas com datas de criaÁ„o e alteraÁ„o do registo
    static protected $insert_timestamp = 'datalogin';
    static protected $update_timestamp = 'datalastactivity';
    // Array com defaults que s„o aplicados ao objecto antes de gravaÁ„o na BD
    static protected $defaults = array();

    // Nome da tabela na base de dados
    const TABLENAME = "fhassist_session";

    /*********************************************************************
    /	Construtor.
    *********************************************************************/
    public function __construct()
    {
        parent::__construct();
        $this->sessionIdentifier = SESSION_IDENTIFIER;
    }

    /*********************************************************************
    /	Verifica se o utilizador esta logado.
    /	Se nao, redirecciona para login.php
    *********************************************************************/
    function authorize($tiposPermitidos,$refUrl='')
    {
        $isLogado = false;
        if(isset($_SESSION[$this->sessionIdentifier."islogado"]) && isset($_SESSION[$this->sessionIdentifier."user"]) && isset($_SESSION[$this->sessionIdentifier."islogadoId"]))
        {
            $user = new User();
            $user = unserialize($_SESSION[$this->sessionIdentifier."user"]);
            $isLogado = (in_array($user->perfil,$tiposPermitidos) && $user->id > 0);

            //Verifica se existe a entrada na tabela session
            $ses = $this->fetch($_SESSION[$this->sessionIdentifier."islogadoId"]);
            if($ses==null)
            {
                $this->logout();
                $isLogado = false;
            }
            else
            {
                $this->id   	 = $ses->id;
                $this->userid    = $ses->userid;
                $this->sessionid = $ses->sessionid;
                $this->ip        = $_SERVER['REMOTE_ADDR'];
                $this->datalogin = $ses->datalogin;
                $this->datalastactivity = date("Y-m-d H:i:s");
                $this->save();
            }
        }

        if(!$isLogado)
        {
            $refUrl = $_SERVER['REQUEST_URI'];
            header("location: ". HOST_URL ."login.php?url=".$refUrl);
        }
    }

    /*********************************************************************
    /	Verifica se o utilizador esta logado.
    /	Se nao, redirecciona para login.php
    /   ESTA CLASS PERTENCE A NOVA FRAMEWORK
    *********************************************************************/
    function authorize_new($tiposPermitidos,$pageName,$url)
    {
        $isLogado = false;
        if(isset($_SESSION[$this->sessionIdentifier."islogado"]) && isset($_SESSION[$this->sessionIdentifier."user"]) && isset($_SESSION[$this->sessionIdentifier."islogadoId"]))
        {
            $user = new User();
            $user = unserialize($_SESSION[$this->sessionIdentifier."user"]);
            $isLogado = (in_array($user->perfil,$tiposPermitidos) && $user->id > 0);

            //Verifica se existe a entrada na tabela session
            $ses = $this->fetch($_SESSION[$this->sessionIdentifier."islogadoId"]);
            if($ses==null)
            {
                $this->logout();
                $isLogado = false;
            }
            else
            {
                $this->id   	 = $ses->id;
                $this->userid    = $ses->userid;
                $this->sessionid = $ses->sessionid;
                $this->ip        = $_SERVER['REMOTE_ADDR'];
                $this->datalogin = $ses->datalogin;
                $this->datalastactivity = date("Y-m-d H:i:s");
                $this->save();

                $history = new History;
                $history->saveHistory($ses, $pageName);
            }
        }

        if(!$isLogado)
        {
            $refUrl = $_SERVER['REQUEST_URI'];
            //header("location: login.php?url=".$refUrl);
            header("location: " . $url);
        }

        return $user;
    }


    /*********************************************************************
    /	Logar o user.
    *********************************************************************/
    function login($user)
    {
        if(isset($user) && get_class($user)=='User')
        {
            $_SESSION[$this->sessionIdentifier."user"] = serialize($user);
            $_SESSION[$this->sessionIdentifier."islogado"] = "true";

            //Apaga entradas de sess„o anteriores do mesmo utilizador
            $this->delete(array('userid'=>$user->id));

            //Inserir nova entrada de sess„o
            $this->userid    = $user->id;
            $this->sessionid = session_id();
            $this->ip        = $_SERVER['REMOTE_ADDR'];
            $this->save();

            $_SESSION[$this->sessionIdentifier."islogadoId"] = $this->id;
        }
    }

    /*********************************************************************
    /	Deslogar o user e redireccionar para o login.php
    *********************************************************************/
    function logout()
    {
        if(isset($_SESSION[$this->sessionIdentifier."islogado"]) && isset($_SESSION[$this->sessionIdentifier."user"]))
        {
            $user = unserialize($_SESSION[$this->sessionIdentifier."user"]);
            //Apaga entradas na tabela de Sess„o para este utilizador
            $this->delete(array('userid'=>$user->id));
        }
        unset($_SESSION[$this->sessionIdentifier."islogado"]);
        unset($_SESSION[$this->sessionIdentifier."user"]);
        unset($_SESSION[$this->sessionIdentifier."islogadoId"]);
        session_destroy();
        header("location:login.php");
    }

    /*********************************************************************
    /	Devolve o user que est· logado
    *********************************************************************/
    function getUser()
    {
        $user = null;
        if(isset($_SESSION[$this->sessionIdentifier."user"]))
        {
            $user = unserialize($_SESSION[$this->sessionIdentifier."user"]);
        }
        return $user;
    }


    /*********************************************************************
    /	Altera propriedade do user que est· logado
    *********************************************************************/
    function changeUserProperties($user)
    {
        if(isset($_SESSION[$this->sessionIdentifier."user"]))
        {
            $_SESSION[$this->sessionIdentifier."user"] = serialize($user);
        }
        return $user;
    }
}
?>