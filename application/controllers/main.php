<?php

class Main extends Controller {
	
	function index()
	{
                $model = $this->loadModel('Categoria');
		$template = $this->loadView('main');
                $template->set('content', 'Jmarcel - Framework');
		$template->render();
	}
    
}

?>
