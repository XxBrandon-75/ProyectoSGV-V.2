<?php
// /controllers/VoluntarioController.php

// Incluimos el modelo que vamos a utilizar
require_once __DIR__ . '/../models/voluntario.php';

class VoluntarioController {

    /**
     * Maneja la lógica de login.
     * @param string $email
     * @param string $password
     * @return array El resultado de la operación.
     */
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email y contraseña son requeridos.'];
        }

        $voluntarioModel = new Voluntario();
        $userData = $voluntarioModel->obtenerDatosLogin($email);

        if ($userData && isset($userData['PasswordHash']) && password_verify($password, $userData['PasswordHash'])) {
            if ($userData['EstatusNombre'] === 'Activo') {
                return [
                    'success' => true,
                    'message' => 'Login exitoso.',
                    'user' => [
                        'id' => $userData['VoluntarioID'],
                        'nombre' => $userData['Nombres'],
                        'rol' => $userData['RolNombre']
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Tu cuenta está en proceso de validación o no se encuentra activa.'];
            }
        } else {
            return ['success' => false, 'message' => 'Correo electrónico o contraseña incorrectos.'];
        }
    }
    public function pendienteAprobacion() {
    // 1. Instanciamos el modelo
        $voluntarioModel = new Voluntario();
        
        // 2. Obtenemos la LISTA de voluntarios pendientes
        $voluntariosPendientes = $voluntarioModel->voluntariosSinAprobar();

        // 3. Verificamos si la lista tiene datos o está vacía
        if ($voluntariosPendientes) { // Si el array no está vacío
            return [
                'success' => true,
                'message' => 'Se encontraron voluntarios pendientes de aprobación.',
                'voluntarios' => $voluntariosPendientes // <-- Devolvemos la lista completa
            ];
        } else {
            // Esto se ejecuta si no se encontró ningún voluntario pendiente
            return [
                'success' => false, 
                'message' => 'No hay voluntarios pendientes de aprobación en este momento.'
            ];
        }
    }

    // public function AceptaVoluntario($postData){
    //     $requiredFields = [
    //         'VoluntarioID','AdminID'
    //     ]
    //     foreach($requiredFields as $field){
    //         if(empty($postData[$field])){
    //             return['success'=>false,'mesaje'=>"El campo '$field' es obligatorio"]
    //         }
    //     }
    //     $datosEnviados=[
    //         'VoluntarioID'=>$postData['VoluntarioID'],
    //         'AdminID'=>$postData['AdminID']
    //     ]
    //     $voluntarioModel = new Voluntario();
    //     $resultadoSP = $voluntarioModel->aprobarVoluntario($datosEnviados);
    // }

    public function register($postData) {
        // 1. Validación básica de campos requeridos
        $requiredFields = [
            'nombres', 'apellidoPaterno', 'fechaNacimiento', 'email', 'password',
            'calle', 'ciudadID', 'estadoID', 'areaID', 'delegacionID',
            'contactoEmergenciaNombre', 'contactoEmergenciaParentesco', 'contactoEmergenciaTelefono',
            'disponibilidadDia', 'disponibilidadTurno'
        ];

        foreach ($requiredFields as $field) {
            if (empty($postData[$field])) {
                return ['success' => false, 'message' => "El campo '$field' es obligatorio."];
            }
        }

        // 2. Encriptar la contraseña de forma segura
        $passwordHash = password_hash($postData['password'], PASSWORD_DEFAULT);

        // 3. Preparar el array de datos para el modelo
        $datosVoluntario = [
            'nombres' => $postData['nombres'],
            'apellidoPaterno' => $postData['apellidoPaterno'],
            'apellidoMaterno' => $postData['apellidoMaterno'] ?? null,
            'fechaNacimiento' => $postData['fechaNacimiento'],
            'email' => $postData['email'],
            'passwordHash' => $passwordHash,
            'curp' => $postData['curp'] ?? null,
            'sexo' => $postData['sexo'] ?? null,
            'lugarNacimiento' => $postData['lugarNacimiento'] ?? null,
            'nacionalidad' => $postData['nacionalidad'] ?? null,
            'estadoCivilID' => $postData['estadoCivilID'] ?? null,
            'grupoSanguineoID' => $postData['grupoSanguineoID'] ?? null,
            'telefonoCelular' => $postData['telefonoCelular'] ?? null,
            'telefonoParticular' => $postData['telefonoParticular'] ?? null,
            'telefonoTrabajo' => $postData['telefonoTrabajo'] ?? null,
            'calle' => $postData['calle'],
            'numeroExterior' => $postData['numeroExterior'] ?? null,
            'colonia' => $postData['colonia'] ?? null,
            'codigoPostal' => $postData['codigoPostal'] ?? null,
            'ciudadID' => $postData['ciudadID'],
            'estadoID' => $postData['estadoID'],
            'gradoEstudios' => $postData['gradoEstudios'] ?? null,
            'profesion' => $postData['profesion'] ?? null,
            'ocupacionActual' => $postData['ocupacionActual'] ?? null,
            'empresaLabora' => $postData['empresaLabora'] ?? null,
            'tieneLicenciaConducir' => $postData['tieneLicenciaConducir'] ?? 0,
            'enfermedades' => $postData['enfermedades'] ?? null,
            'alergias' => $postData['alergias'] ?? null,
            'areaID' => $postData['areaID'],
            'delegacionID' => $postData['delegacionID'],
            'tutorNombre' => $postData['tutorNombre'] ?? null,
            'tutorParentesco' => $postData['tutorParentesco'] ?? null,
            'tutorTelefono' => $postData['tutorTelefono'] ?? null,
            'contactoEmergenciaNombre' => $postData['contactoEmergenciaNombre'],
            'contactoEmergenciaParentesco' => $postData['contactoEmergenciaParentesco'],
            'contactoEmergenciaTelefono' => $postData['contactoEmergenciaTelefono'],
            'disponibilidadDia' => $postData['disponibilidadDia'],
            'disponibilidadTurno' => $postData['disponibilidadTurno']
        ];

        // 4. Llamar al modelo para ejecutar el SP
        $voluntarioModel = new Voluntario();
        $resultadoSP = $voluntarioModel->registrarNuevoVoluntario($datosVoluntario);

        // 5. Devolver el resultado
        if (isset($resultadoSP['NuevoVoluntarioID'])) {
            return ['success' => true, 'message' => 'Registro exitoso. Tu solicitud está en proceso de validación.'];
        } else {
            // Si el SP devuelve un error, lo capturamos y lo mostramos
            return ['success' => false, 'message' => 'Error al registrar: ' . ($resultadoSP['error'] ?? 'Ocurrió un problema desconocido.')];
        }
    }

    



}
?>

