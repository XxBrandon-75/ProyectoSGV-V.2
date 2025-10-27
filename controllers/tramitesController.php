<?php
// /controllers/VoluntarioController.php

// Incluimos el modelo que vamos a utilizar
require_once __DIR__ . '/../models/tramitesModels.php';

class TramitesController {

    /**
     * Maneja la lógica de login.
     * @param string $email
     * @param string $password
     * @return array El resultado de la operación.
     */
    
    public function Tramites($Nombre, $Descripcion,$ReglaAntigueda,$ReglaAnioPares,$Activo) {
    // 1. Instanciamos el modelo
        $TramiteModels = new Tramites();
        
        // 2. Obtenemos la LISTA de voluntarios pendientes
        $Tramites = $TramiteModels->AgregarTramite($Nombre, $Descripcion,$ReglaAntigueda,$ReglaAnioPares,$Activo);

        // 3. Verificamos si la lista tiene datos o está vacía
        if ($Tramites) { // Si el array no está vacío
            return [
                'success' => true,
                'message' => 'Se encontraron voluntarios pendientes de aprobación.',
                'voluntarios' => $Tramites // <-- Devolvemos la lista completa
            ];
        } else {
            // Esto se ejecuta si no se encontró ningún voluntario pendiente
            return [
                'success' => false, 
                'message' => 'No hay tramites'
            ];
        }
    }

}
?>