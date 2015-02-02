<?php


class Noticias extends Controller {
    
    public function index()
    {
                $template = $this->loadView('main');
                $template->set('content', 'Noticias<br>Controller');
                $template->render();
    }
}

?>