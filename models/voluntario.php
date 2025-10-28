<?php
// /models/Voluntario.php
// En models/voluntario.php

require_once __DIR__ . '/../config/database.php';
class Voluntario
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // ... (método obtenerDatosLogin sin cambios) ...
    public function obtenerDatosLogin($email)
    {
        try {
            $sql = "EXEC sp_ObtenerDatosLogin @Email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si el SP no devuelve AreaID y DelegacionID, obtenerlos manualmente
            if ($datos && (!isset($datos['AreaID']) || !isset($datos['DelegacionID']))) {
                $sqlExtra = "SELECT AreaID, DelegacionID FROM Voluntarios WHERE Email = :email";
                $stmtExtra = $this->pdo->prepare($sqlExtra);
                $stmtExtra->bindParam(':email', $email, PDO::PARAM_STR);
                $stmtExtra->execute();
                $extra = $stmtExtra->fetch(PDO::FETCH_ASSOC);

                if ($extra) {
                    $datos['AreaID'] = $extra['AreaID'];
                    $datos['DelegacionID'] = $extra['DelegacionID'];
                }
            }

            return $datos;
        } catch (PDOException $e) {
            error_log("Error en obtenerDatosLogin: " . $e->getMessage());
            return false;
        }
    }
    public function aprobarVoluntario($voluntarioIDaAprobar, $adminIDqueAprueba)
    {
        try {
            $sql = "EXEC sp_AprobarVoluntario 
                        @VoluntarioIDaAprobar = :voluntarioID, 
                        @AdminIDqueAprueba = :adminID";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':voluntarioID', $voluntarioIDaAprobar, PDO::PARAM_INT);
            $stmt->bindParam(':adminID', $adminIDqueAprueba, PDO::PARAM_INT);
            $stmt->execute();
            return true; // Si no hay error, la ejecución fue exitosa
        } catch (PDOException $e) {
            error_log("Error en aprobarVoluntario: " . $e->getMessage());
            return false;
        }
    }
    public function voluntariosSinAprobar() {
    try {
        $sql = "exec voluntariosSinAprobar";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        // ----> CAMBIO PRINCIPAL AQUÍ <----
        // Usamos fetchAll() para obtener TODOS los voluntarios, no solo el primero.
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            // ----> CAMBIO PRINCIPAL AQUÍ <----
            // Usamos fetchAll() para obtener TODOS los voluntarios, no solo el primero.
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // ----> CAMBIO MENOR AQUÍ <----
            // Corregimos el mensaje de error para que apunte a la función correcta.
            error_log("Error en voluntariosSinAprobar: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Llama al SP para registrar un nuevo voluntario con todos sus datos.
     * @param array $datos Un array asociativo con todos los datos del voluntario.
     * @return array El resultado de la operación.
     */
    public function registrarNuevoVoluntario($datos)
    {
        try {
            $sql = "EXEC sp_RegistrarNuevoVoluntarioCompleto
                        @Nombres = :nombres, @ApellidoPaterno = :apellidoPaterno, @ApellidoMaterno = :apellidoMaterno,
                        @FechaNacimiento = :fechaNacimiento, @Email = :email, @PasswordHash = :passwordHash,
                        @CURP = :curp, @Sexo = :sexo, @LugarNacimiento = :lugarNacimiento, @Nacionalidad = :nacionalidad,
                        @EstadoCivilID = :estadoCivilID, @GrupoSanguineoID = :grupoSanguineoID,
                        @TelefonoCelular = :telefonoCelular, @TelefonoParticular = :telefonoParticular, @TelefonoTrabajo = :telefonoTrabajo,
                        @Calle = :calle, @NumeroExterior = :numeroExterior, @Colonia = :colonia, @CodigoPostal = :codigoPostal,
                        @CiudadID = :ciudadID, @EstadoID = :estadoID,
                        @GradoEstudios = :gradoEstudios, @Profesion = :profesion, @OcupacionActual = :ocupacionActual,
                        @EmpresaLabora = :empresaLabora, @TieneLicenciaConducir = :tieneLicenciaConducir,
                        @Enfermedades = :enfermedades, @Alergias = :alergias,
                        @AreaID = :areaID, @DelegacionID = :delegacionID,
                        @TutorNombreCompleto = :tutorNombre, @TutorParentesco = :tutorParentesco, @TutorTelefono = :tutorTelefono,
                        @ContactoEmergenciaNombre = :contactoEmergenciaNombre,
                        @ContactoEmergenciaParentesco = :contactoEmergenciaParentesco,
                        @ContactoEmergenciaTelefono = :contactoEmergenciaTelefono,
                        @DisponibilidadDiaSemana = :disponibilidadDia,
                        @DisponibilidadTurno = :disponibilidadTurno";

            $stmt = $this->pdo->prepare($sql);

            // Asignar cada valor del array a su parámetro correspondiente.
            $stmt->bindParam(':nombres', $datos['nombres']);
            $stmt->bindParam(':apellidoPaterno', $datos['apellidoPaterno']);
            $stmt->bindParam(':apellidoMaterno', $datos['apellidoMaterno']);
            $stmt->bindParam(':fechaNacimiento', $datos['fechaNacimiento']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':passwordHash', $datos['passwordHash']);
            $stmt->bindParam(':curp', $datos['curp']);
            $stmt->bindParam(':sexo', $datos['sexo']);
            $stmt->bindParam(':lugarNacimiento', $datos['lugarNacimiento']);
            $stmt->bindParam(':nacionalidad', $datos['nacionalidad']);
            $stmt->bindParam(':estadoCivilID', $datos['estadoCivilID'], PDO::PARAM_INT);
            $stmt->bindParam(':grupoSanguineoID', $datos['grupoSanguineoID'], PDO::PARAM_INT);
            $stmt->bindParam(':telefonoCelular', $datos['telefonoCelular']);
            $stmt->bindParam(':telefonoParticular', $datos['telefonoParticular']);
            $stmt->bindParam(':telefonoTrabajo', $datos['telefonoTrabajo']);
            $stmt->bindParam(':calle', $datos['calle']);
            $stmt->bindParam(':numeroExterior', $datos['numeroExterior']);
            $stmt->bindParam(':colonia', $datos['colonia']);
            $stmt->bindParam(':codigoPostal', $datos['codigoPostal']);
            $stmt->bindParam(':ciudadID', $datos['ciudadID'], PDO::PARAM_INT);
            $stmt->bindParam(':estadoID', $datos['estadoID'], PDO::PARAM_INT);
            $stmt->bindParam(':gradoEstudios', $datos['gradoEstudios']);
            $stmt->bindParam(':profesion', $datos['profesion']);
            $stmt->bindParam(':ocupacionActual', $datos['ocupacionActual']);
            $stmt->bindParam(':empresaLabora', $datos['empresaLabora']);
            $stmt->bindParam(':tieneLicenciaConducir', $datos['tieneLicenciaConducir'], PDO::PARAM_BOOL);
            $stmt->bindParam(':enfermedades', $datos['enfermedades']);
            $stmt->bindParam(':alergias', $datos['alergias']);
            $stmt->bindParam(':areaID', $datos['areaID'], PDO::PARAM_INT);
            $stmt->bindParam(':delegacionID', $datos['delegacionID'], PDO::PARAM_INT);
            $stmt->bindParam(':tutorNombre', $datos['tutorNombre']);
            $stmt->bindParam(':tutorParentesco', $datos['tutorParentesco']);
            $stmt->bindParam(':tutorTelefono', $datos['tutorTelefono']);
            $stmt->bindParam(':contactoEmergenciaNombre', $datos['contactoEmergenciaNombre']);
            $stmt->bindParam(':contactoEmergenciaParentesco', $datos['contactoEmergenciaParentesco']);
            $stmt->bindParam(':contactoEmergenciaTelefono', $datos['contactoEmergenciaTelefono']);
            $stmt->bindParam(':disponibilidadDia', $datos['disponibilidadDia'], PDO::PARAM_INT);
            $stmt->bindParam(':disponibilidadTurno', $datos['disponibilidadTurno']);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // CORRECCIÓN: Devolver el mensaje de error específico de la base de datos.
            // Esto nos dirá si el error es por un email duplicado, CURP duplicado, etc.
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Obtener todos los datos completos de un voluntario
     */
    public function obtenerDatosCompletos($voluntarioID)
    {
        $query = ""; // Inicializar para evitar errores
        try {
            // Debug: Verificar qué ID estamos recibiendo
            error_log("obtenerDatosCompletos - VoluntarioID recibido: " . $voluntarioID);

            $query = "exec [obtenerDatosCompleto]
                        @VoluntarioID= :voluntarioID";

            $stmt = $this->pdo->prepare($query);

            if ($stmt === false) {
                $errorInfo = $this->pdo->errorInfo();
                error_log("Error preparando query: " . print_r($errorInfo, true));
                return false;
            }

            $stmt->bindParam(':voluntarioID', $voluntarioID, PDO::PARAM_INT);
            $executeResult = $stmt->execute();

            if ($executeResult === false) {
                $errorInfo = $stmt->errorInfo();
                error_log("Error ejecutando query: " . print_r($errorInfo, true));
                return false;
            }

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug: Ver qué devolvió la consulta
            if ($resultado) {
                error_log("obtenerDatosCompletos - Datos encontrados para VoluntarioID: " . $voluntarioID);
            } else {
                error_log("obtenerDatosCompletos - NO se encontraron datos para VoluntarioID: " . $voluntarioID);
            }

            return $resultado;
        } catch (PDOException $e) {
            error_log("EXCEPCION en obtenerDatosCompletos: " . $e->getMessage());
            error_log("Codigo error: " . $e->getCode());
            error_log("Query ejecutado: " . $query);
            error_log("VoluntarioID: " . $voluntarioID);
            return false;
        }
    }

    /**
     * Obtener voluntarios por delegación (para coordinadores de delegación)
     */
    public function obtenerVoluntariosPorDelegacion($delegacionID)
    {
        try {
            $query = "exec [ObtenerVoluntarioPorDelegacion] 
                        @DelegacionID = :delegacionID";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':delegacionID', $delegacionID, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerVoluntariosPorDelegacion: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los coordinadores (para administradores)
     * Los Superadministradores son invisibles para todos excepto otros Superadministradores
     * Los Administradores son invisibles excepto para Superadministradores
     */
    public function obtenerCoordinadores($rolUsuario = 'Administrador')
    {
        try {
            $query = "exec [obtenerCoordinadores]";

            // Si el usuario es Superadministrador, puede ver Administradores
            // Si el usuario es Administrador, no puede ver Administradores ni Superadministradores
            if ($rolUsuario === 'Superadministrador') {
                // Superadministrador puede ver todo excepto otros Superadministradores
                $query .= "@Rolusuario = 'diferent'";
            }
            // Si no es Superadministrador, no añadimos nada más (solo coordinadores)

            //$query .= " ORDER BY v.ApellidoPaterno, v.ApellidoMaterno, v.Nombres";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerCoordinadores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar datos editables del voluntario
     * Solo permite editar campos específicos según permisos
     */
    public function actualizarDatosEditables($voluntarioID, $datos, $camposPermitidos)
    {
        try {
            // Eliminar duplicados de camposPermitidos
            $camposPermitidos = array_unique($camposPermitidos);

            // Log para depuración
            error_log("=== INICIO actualizarDatosEditables ===");
            error_log("Campos permitidos: " . json_encode($camposPermitidos));
            error_log("Datos recibidos: " . json_encode($datos));

            // Construir query dinámicamente solo con campos permitidos
            $setClauses = [];
            $params = [':voluntarioID' => $voluntarioID];

            foreach ($camposPermitidos as $campo) {
                if (isset($datos[$campo])) {
                    $valor = $datos[$campo];

                    // Si el valor está vacío, convertirlo a NULL
                    if ($valor === '' || $valor === null) {
                        error_log("Campo '$campo' con valor vacío, se enviará NULL");
                        $valor = null;
                    } else {
                        error_log("Campo '$campo' encontrado con valor: " . $valor);
                    }

                    // Verificar que no hayamos agregado este campo ya
                    if (!isset($params[":$campo"])) {
                        $setClauses[] = "$campo = :$campo";
                        $params[":$campo"] = $valor;
                    }
                } else {
                    error_log("Campo '$campo' NO encontrado en datos");
                }
            }

            if (empty($setClauses)) {
                error_log("ERROR: No hay campos para actualizar");
                return ['success' => false, 'message' => 'No hay campos para actualizar'];
            }

            $query = "UPDATE Voluntarios SET " . implode(', ', $setClauses) . " WHERE VoluntarioID = :voluntarioID";

            // Log para depuración
            error_log("Query generada: " . $query);
            error_log("Parámetros: " . json_encode($params));

            $stmt = $this->pdo->prepare($query);
            $result = $stmt->execute($params);

            return [
                'success' => $result,
                'message' => $result ? 'Datos actualizados correctamente' : 'Error al actualizar datos'
            ];
        } catch (PDOException $e) {
            error_log("Error en actualizarDatosEditables: " . $e->getMessage());
            error_log("Query: " . ($query ?? 'No generada'));
            error_log("Params: " . json_encode($params ?? []));
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar dirección del voluntario
     * SEGURIDAD: Verifica que la dirección pertenezca al voluntario antes de actualizar
     */
    public function actualizarDireccion($voluntarioID, $datos)
    {
        try {
            $camposPermitidos = ['Calle', 'NumeroExterior', 'NumeroInterior', 'Colonia', 'CodigoPostal', 'CiudadID', 'EstadoID'];

            $setClauses = [];
            $params = [':voluntarioID' => $voluntarioID];

            foreach ($camposPermitidos as $campo) {
                if (isset($datos[$campo])) {
                    // Convertir valores vacíos a NULL
                    $valor = $datos[$campo];
                    if ($valor === '' || $valor === null) {
                        $valor = null;
                    }
                    $setClauses[] = "$campo = :$campo";
                    $params[":$campo"] = $valor;
                }
            }

            if (empty($setClauses)) {
                return ['success' => false, 'message' => 'No hay campos para actualizar'];
            }

            // Primero verificar si existe una dirección para este voluntario
            $checkQuery = "SELECT DireccionID FROM Direcciones WHERE VoluntarioID = :voluntarioID";
            $checkStmt = $this->pdo->prepare($checkQuery);
            $checkStmt->bindParam(':voluntarioID', $voluntarioID, PDO::PARAM_INT);
            $checkStmt->execute();
            $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($exists) {
                // SEGURIDAD: Verificar que la dirección pertenece al voluntario antes de actualizar
                // UPDATE existente - la cláusula WHERE garantiza que solo se actualice si pertenece al voluntario
                $query = "UPDATE Direcciones SET " . implode(', ', $setClauses) . " WHERE VoluntarioID = :voluntarioID";
                $stmt = $this->pdo->prepare($query);
                $result = $stmt->execute($params);

                // Log de la operación
                error_log("Dirección actualizada para VoluntarioID: $voluntarioID");
            } else {
                // INSERT nueva dirección
                $camposNombres = [];
                $placeholders = [];

                foreach ($camposPermitidos as $campo) {
                    if (isset($datos[$campo])) {
                        $camposNombres[] = $campo;
                        $placeholders[] = ":$campo";
                    }
                }

                $camposNombres[] = 'VoluntarioID';
                $placeholders[] = ':voluntarioID';

                $query = "INSERT INTO Direcciones (" . implode(', ', $camposNombres) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $this->pdo->prepare($query);
                $result = $stmt->execute($params);
            }

            return [
                'success' => $result,
                'message' => $result ? 'Dirección actualizada correctamente' : 'Error al actualizar dirección'
            ];
        } catch (PDOException $e) {
            error_log("Error en actualizarDireccion: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar contacto de emergencia del voluntario
     * SEGURIDAD: Verifica que el contacto pertenezca al voluntario antes de actualizar
     */
    public function actualizarContactoEmergencia($voluntarioID, $datos)
    {
        try {
            $camposPermitidos = ['NombreCompleto', 'Parentesco', 'Telefono'];

            $setClauses = [];
            $params = [':voluntarioID' => $voluntarioID];

            foreach ($camposPermitidos as $campo) {
                // Mapear desde los nombres usados en el frontend
                $frontendName = 'ContactoEmergencia' . ($campo === 'NombreCompleto' ? 'Nombre' : $campo);
                if (isset($datos[$frontendName])) {
                    // Convertir valores vacíos a NULL
                    $valor = $datos[$frontendName];
                    if ($valor === '' || $valor === null) {
                        $valor = null;
                    }
                    $setClauses[] = "$campo = :$campo";
                    $params[":$campo"] = $valor;
                }
            }

            if (empty($setClauses)) {
                return ['success' => false, 'message' => 'No hay campos para actualizar'];
            }

            // Primero verificar si existe un contacto de emergencia para este voluntario
            $checkQuery = "SELECT ContactoID FROM ContactosEmergencia WHERE VoluntarioID = :voluntarioID";
            $checkStmt = $this->pdo->prepare($checkQuery);
            $checkStmt->bindParam(':voluntarioID', $voluntarioID, PDO::PARAM_INT);
            $checkStmt->execute();
            $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($exists) {
                // SEGURIDAD: UPDATE - la cláusula WHERE garantiza que solo se actualice si pertenece al voluntario
                $query = "UPDATE ContactosEmergencia SET " . implode(', ', $setClauses) . " WHERE VoluntarioID = :voluntarioID";
                $stmt = $this->pdo->prepare($query);
                $result = $stmt->execute($params);

                // Log de la operación
                error_log("Contacto de emergencia actualizado para VoluntarioID: $voluntarioID");
            } else {
                // INSERT nuevo contacto
                $camposNombres = [];
                $placeholders = [];

                foreach ($camposPermitidos as $campo) {
                    $frontendName = 'ContactoEmergencia' . ($campo === 'NombreCompleto' ? 'Nombre' : $campo);
                    if (isset($datos[$frontendName])) {
                        $camposNombres[] = $campo;
                        $placeholders[] = ":$campo";
                    }
                }

                $camposNombres[] = 'VoluntarioID';
                $placeholders[] = ':voluntarioID';

                $query = "INSERT INTO ContactosEmergencia (" . implode(', ', $camposNombres) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $this->pdo->prepare($query);
                $result = $stmt->execute($params);
            }

            return [
                'success' => $result,
                'message' => $result ? 'Contacto de emergencia actualizado correctamente' : 'Error al actualizar contacto de emergencia'
            ];
        } catch (PDOException $e) {
            error_log("Error en actualizarContactoEmergencia: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $e->getMessage()
            ];
        }
    }
}
