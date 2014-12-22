<?php

class Controller {
	
	public function loadModel($name)
	{
		require(APP_DIR .'models/'. $name .'.php');

		$model = new $name;
		return $model;
	}
	
	public function loadView($name)
	{
		$view = new View($name);
		return $view;
	}
	
	public function loadPlugin($name)
	{
		require(APP_DIR .'plugins/'. strtolower($name) .'.php');
	}
	
	public function loadHelper($name)
	{
		require(APP_DIR .'helpers/'. strtolower($name) .'.php');
		$helper = new $name;
		return $helper;
	}
	
	public function redirect($loc)
	{
		global $config;
		
		header('Location: '. $config['base_url'] . $loc);
	}
    
                /**
         * Method to call js or css files
         * @param string $path js or css path file
         * @return string  return script or link tag with file path
         */
        public function clientScript($path,$type)
        {
            
            if($type==="js")
                
                return '<script src="'.$path.'"></script>'."\n";
            elseif($type==="css")
                
                return'<link rel="stylesheet" href="'.$path.'">'."\n";
            else
                
                return "Notice: missing argument 2 type js or css";
            
        }
}

?>