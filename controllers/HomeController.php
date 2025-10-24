<?php
class HomeController
{
    private $user_role;

    public function __construct()
    {
        $this->user_role = 3;
    }

    public function index()
    {
        $titulo_pagina = "Inicio RVS | Red de Voluntarios";

        $styles = ['/ProyectoSGV/public/css/i.style.css'];

        $scripts = ['/ProyectoSGV/public/scripts/i.script.js']; 

        require_once "views/layout/header.php";

        require_once "views/home/index.php";

        require_once "views/layout/footer.php";
    }

    public function especialidades()
    {
        $ver_cont_gest = ($this->user_role >=3);

        $ver_card_edit = ($this->user_role >=3);
        
        $titulo_pagina = "Especialidades | Red de Voluntarios";

        $styles = ['/ProyectoSGV/public/css/e.style.css'];

        $scripts = ['/ProyectoSGV/public/scripts/e.script.js']; 
        
        require_once "views/layout/header.php";

        require_once "views/home/especialidades.php";

        require_once "views/layout/footer.php";
    }
    
    public function tramites()
    {
        $ver_cont_gest = ($this->user_role >=3);

        $ver_card_edit = ($this->user_role >=3);

        $titulo_pagina = "Trámites | Red de Voluntarios";

        $styles = ['/ProyectoSGV/public/css/t.style.css']; 

        $scripts = ['/ProyectoSGV/public/scripts/t.script.js']; 

        require_once "views/layout/header.php";

        require_once "views/home/tramites.php";

        require_once "views/layout/footer.php";
    }

        public function documentacion()
    {
        $ver_cont_gest = ($this->user_role >=3);

        $ver_card_edit = ($this->user_role >=3);

        $titulo_pagina = "Documentación | Red de Voluntarios";

        $styles = ['/ProyectoSGV/public/css/d.style.css']; 

        $scripts = ['/ProyectoSGV/public/scripts/d.script.js'];

        require_once "views/layout/header.php";

        require_once "views/home/documentacion.php";

        require_once "views/layout/footer.php";
    }
        public function notificaciones()
    {
        $titulo_pagina = "Notificaciones | Red de Voluntarios";

        $styles = ['/ProyectoSGV/public/css/n.style.css'];

        $scripts = ['/ProyectoSGV/public/scripts/n.script.js'];

        require_once "views/layout/header.php";

        require_once "views/home/notificaciones.php";

        require_once "views/layout/footer.php";
    } 

        public function perfil()
    {
        $titulo_pagina = "Perfil | Red de Voluntarios";

        $styles = ['/ProyectoSGV/public/css/p.style.css']; 

        $scripts = ['/ProyectoSGV/public/scripts/p.script.js']; 

        require_once "views/layout/header.php";

        require_once "views/home/perfil.php";

        require_once "views/layout/footer.php";
    }
}
?>