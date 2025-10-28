<?php
// /controllers/VoluntarioController.php

require_once __DIR__ . '/../models/voluntario.php';

class VoluntarioController
{
    /**
     * Función auxiliar para convertir cadenas vacías en NULL
     * @param mixed $value El valor a verificar
     * @return mixed El valor original o NULL si está vacío
     */
    private function emptyToNull($value)
    {
        // Si el valor es null, vacío o solo espacios en blanco, devolver null
        if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
            return null;
        }
        return $value;
    }

    /**
     * Maneja la lógica de login.
     * @param string $email
     * @param string $password
     * @return array El resultado de la operación.
     */
    public function login($email, $password)
    {
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
                        'rol' => $userData['RolNombre'],
                        'areaID' => $userData['AreaID'] ?? null,
                        'delegacionID' => $userData['DelegacionID'] ?? null
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Tu cuenta está en proceso de validación o no se encuentra activa.'];
            }
        } else {
            return ['success' => false, 'message' => 'Correo electrónico o contraseña incorrectos.'];
        }
    }

    /**
     * Maneja la lógica para registrar un nuevo voluntario.
     * Recibe los datos del POST, los valida y llama al modelo.
     * @param array $postData Los datos recibidos del formulario.
     * @return array El resultado de la operación.
     */
    public function register($postData)
    {
        // 1. Validación básica de campos requeridos
        $requiredFields = [
            'nombres',
            'apellidoPaterno',
            'fechaNacimiento',
            'email',
            'password',
            'calle',
            'ciudadID',
            'estadoID',
            'areaID',
            'delegacionID',
            'contactoEmergenciaNombre',
            'contactoEmergenciaParentesco',
            'contactoEmergenciaTelefono',
            'disponibilidadDia',
            'disponibilidadTurno'
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
            'apellidoMaterno' => $this->emptyToNull($postData['apellidoMaterno'] ?? null),
            'fechaNacimiento' => $postData['fechaNacimiento'],
            'email' => $postData['email'],
            'passwordHash' => $passwordHash,
            'curp' => $this->emptyToNull($postData['curp'] ?? null),
            'sexo' => $this->emptyToNull($postData['sexo'] ?? null),
            'lugarNacimiento' => $this->emptyToNull($postData['lugarNacimiento'] ?? null),
            'nacionalidad' => $this->emptyToNull($postData['nacionalidad'] ?? null),
            'estadoCivilID' => $this->emptyToNull($postData['estadoCivilID'] ?? null),
            'grupoSanguineoID' => $this->emptyToNull($postData['grupoSanguineoID'] ?? null),
            'telefonoCelular' => $this->emptyToNull($postData['telefonoCelular'] ?? null),
            'telefonoParticular' => $this->emptyToNull($postData['telefonoParticular'] ?? null),
            'telefonoTrabajo' => $this->emptyToNull($postData['telefonoTrabajo'] ?? null),
            'calle' => $postData['calle'],
            'numeroExterior' => $this->emptyToNull($postData['numeroExterior'] ?? null),
            'colonia' => $this->emptyToNull($postData['colonia'] ?? null),
            'codigoPostal' => $this->emptyToNull($postData['codigoPostal'] ?? null),
            'ciudadID' => $postData['ciudadID'],
            'estadoID' => $postData['estadoID'],
            'gradoEstudios' => $this->emptyToNull($postData['gradoEstudios'] ?? null),
            'profesion' => $this->emptyToNull($postData['profesion'] ?? null),
            'ocupacionActual' => $this->emptyToNull($postData['ocupacionActual'] ?? null),
            'empresaLabora' => $this->emptyToNull($postData['empresaLabora'] ?? null),
            'tieneLicenciaConducir' => $postData['tieneLicenciaConducir'] ?? 0,
            'licenciaVencimiento' => $this->emptyToNull($postData['licenciaVencimiento'] ?? null),
            'tienePasaporte' => $postData['tienePasaporte'] ?? 0,
            'pasaporteVencimiento' => $this->emptyToNull($postData['pasaporteVencimiento'] ?? null),
            'enfermedades' => $this->emptyToNull($postData['enfermedades'] ?? null),
            'alergias' => $this->emptyToNull($postData['alergias'] ?? null),
            'areaID' => $postData['areaID'],
            'delegacionID' => $postData['delegacionID'],
            'tutorNombre' => $this->emptyToNull($postData['tutorNombre'] ?? null),
            'tutorParentesco' => $this->emptyToNull($postData['tutorParentesco'] ?? null),
            'tutorTelefono' => $this->emptyToNull($postData['tutorTelefono'] ?? null),
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
            // Limpiar el mensaje de error del SQL Server
            $errorMessage = $resultadoSP['error'] ?? 'Ocurrió un problema desconocido.';

            // Extraer solo el mensaje después de "Error:" o "[SQL Server]"
            if (preg_match('/\[SQL Server\](.+)$/', $errorMessage, $matches)) {
                $errorMessage = trim($matches[1]);
            } elseif (preg_match('/Error:\s*(.+)$/', $errorMessage, $matches)) {
                $errorMessage = trim($matches[1]);
            }

            // NUEVO: Remover "Error:" si todavía está al inicio
            $errorMessage = preg_replace('/^Error:\s*/i', '', $errorMessage);

            return ['success' => false, 'message' => $errorMessage];
        }
    }

    /**
     * Maneja la lógica para aprobar un voluntario.
     * @param int $voluntarioIDaAprobar
     * @param int $adminIDqueAprueba
     * @return array El resultado de la operación.
     */
    public function aprobarVoluntario($voluntarioIDaAprobar, $adminIDqueAprueba)
    {
        $voluntarioModel = new Voluntario();
        try {
            $resultado = $voluntarioModel->aprobarVoluntario($voluntarioIDaAprobar, $adminIDqueAprueba);
            if ($resultado) {
                return ['success' => true, 'message' => 'Voluntario aprobado exitosamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al aprobar el voluntario.'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
