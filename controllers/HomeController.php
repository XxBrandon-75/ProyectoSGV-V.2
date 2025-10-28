<?php
class HomeController
{
    private $user_role;
    
    private $base_script_path = '/ProyectoSGV/public/scripts/'; 
    
    private $global_menu_script = '/ProyectoSGV/public/scripts/g.script.js';

    public function __construct()
    {
        $this->user_role = 1;
    }

    public function index()
    {
        $titulo_pagina = "Inicio RVS | Red de Voluntarios";

        $styles = ['/ProyectoSGV/public/css/i.style.css'];

        $scripts = [
                $this->global_menu_script, 
                $this->base_script_path . 'i.script.js'
            ]; 

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

        $scripts = [
            $this->global_menu_script, 
            $this->base_script_path . 'e.script.js'
        ];
        
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

        $scripts = [
                $this->global_menu_script, 
                $this->base_script_path . 't.script.js'
        ];

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

        $scripts = [
                $this->global_menu_script, 
                $this->base_script_path . 'd.script.js'
        ];
        require_once "views/layout/header.php";

        require_once "views/home/documentacion.php";

        require_once "views/layout/footer.php";
    }
        public function notificaciones()
    {
        $titulo_pagina = "Notificaciones | Red de Voluntarios";

        $styles = ['/ProyectoSGV/public/css/n.style.css'];

         $scripts = [
            $this->global_menu_script, 
            $this->base_script_path . 'n.script.js'
        ];
        require_once "views/layout/header.php";

        require_once "views/home/notificaciones.php";

        require_once "views/layout/footer.php";
    } 

        public function perfil()
    {
        $titulo_pagina = "Especialidades | Red de Voluntarios";

        $styles = ['/ProyectoSGV/public/css/e.style.css']; 

        $scripts = [
            $this->global_menu_script, 
            $this->base_script_path . 'p.script.js'
        ];

        require_once "views/layout/header.php";

        require_once "views/home/perfil.php";

        require_once "views/layout/footer.php";
    }
}
?>