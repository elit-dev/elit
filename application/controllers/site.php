<?php

class Site extends Controller {
	
	function actionIndex()
	{
                $model = $this->loadModel('Categoria');
                $data = $model->fetchAll();
		$template = $this->loadView('main');
                $template->set('content', $data);
		$template->render();
	}
  
        function actionLogin()
        {
            if(!empty($_POST["username"]) && !empty($_POST["password"])){
                
                echo "escolha.php";
                    
//                $this->redirect('site/index')  ;
            }else{
                $template = $this->loadView('login');
                $template->set('content', 'Login');
                $template->render();
            }

        }
    
}

?>
