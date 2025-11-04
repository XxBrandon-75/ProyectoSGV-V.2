<?php
require_once __DIR__ . '/../config/database.php';

class Notificacion
{
    private $conn;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
    }

    /**
     * Obtiene todos los voluntarios pendientes de aprobación
     * utilizando el procedimiento almacenado voluntariosSinAprobar
     * 
     * @return array Lista de voluntarios pendientes
     */
    public function getVoluntariosSinAprobar()
    {
        try {
            $sql = "EXEC voluntariosSinAprobar";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $voluntarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $voluntarios;
        } catch (PDOException $e) {
            error_log("Error en getVoluntariosSinAprobar: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los detalles completos de un voluntario por su ID
     * 
     * @param int $voluntarioId ID del voluntario
     * @return array|null Datos del voluntario o null si no existe
     */
    public function getVoluntarioById($voluntarioId)
    {
        try {
            $sql = "SELECT v.*, 
                           ev.Nombre AS EstatusNombre,
                           d.Nombre AS DelegacionNombre,
                           a.Nombre AS AreaNombre
                    FROM dbo.Voluntarios AS v
                    LEFT JOIN dbo.EstatusVoluntario AS ev ON v.EstatusID = ev.EstatusID
                    LEFT JOIN dbo.Delegaciones AS d ON v.DelegacionID = d.DelegacionID
                    LEFT JOIN dbo.Areas AS a ON v.AreaID = a.AreaID
                    WHERE v.VoluntarioID = :voluntarioId";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':voluntarioId', $voluntarioId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getVoluntarioById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Aprueba un voluntario usando el procedimiento almacenado sp_AprobarVoluntario
     * 
     * @param int $voluntarioId ID del voluntario a aprobar
     * @param int $adminId ID del administrador que aprueba
     * @param string $motivo Motivo de aprobación (opcional)
     * @return array Resultado con 'success' y 'message'
     */
    public function aprobarVoluntario($voluntarioId, $adminId, $motivo = 'Registro validado y aprobado.')
    {
        try {
            $sql = "EXEC sp_AprobarVoluntario 
                    @VoluntarioIDaAprobar = :voluntarioId,
                    @AdminIDqueAprueba = :adminId,
                    @MotivoAprobacion = :motivo";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':voluntarioId', $voluntarioId, PDO::PARAM_INT);
            $stmt->bindParam(':adminId', $adminId, PDO::PARAM_INT);
            $stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Voluntario aprobado exitosamente'
            ];
        } catch (PDOException $e) {
            error_log("Error en aprobarVoluntario: " . $e->getMessage());
            $errorMessage = $e->getMessage();

            if (strpos($errorMessage, 'no existe o ya no está pendiente') !== false) {
                return [
                    'success' => false,
                    'message' => 'El voluntario no existe o ya fue procesado anteriormente'
                ];
            }

            return [
                'success' => false,
                'message' => 'Error al aprobar el voluntario. Por favor, intente nuevamente.'
            ];
        }
    }

    /**
     * Rechaza un voluntario usando el procedimiento almacenado sp_RechazarVoluntario
     * 
     * @param int $voluntarioId ID del voluntario a rechazar
     * @param int $adminId ID del administrador que rechaza
     * @param string $motivo Motivo del rechazo (OBLIGATORIO)
     * @return array Resultado con 'success' y 'message'
     */
    public function rechazarVoluntario($voluntarioId, $adminId, $motivo)
    {
        try {
            if (empty(trim($motivo))) {
                return [
                    'success' => false,
                    'message' => 'El motivo del rechazo es obligatorio'
                ];
            }

            $sql = "EXEC sp_RechazarVoluntario 
                    @VoluntarioIDaRechazar = :voluntarioId,
                    @AdminIDqueRechaza = :adminId,
                    @MotivoRechazo = :motivo";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':voluntarioId', $voluntarioId, PDO::PARAM_INT);
            $stmt->bindParam(':adminId', $adminId, PDO::PARAM_INT);
            $stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Solicitud rechazada correctamente'
            ];
        } catch (PDOException $e) {
            error_log("Error en rechazarVoluntario: " . $e->getMessage());
            $errorMessage = $e->getMessage();

            if (strpos($errorMessage, 'Se requiere un motivo') !== false) {
                return ['success' => false, 'message' => 'El motivo del rechazo es obligatorio'];
            }

            if (strpos($errorMessage, 'no existe o ya no está pendiente') !== false) {
                return ['success' => false, 'message' => 'El voluntario no existe o ya fue procesado anteriormente'];
            }
<<<<<<< HEAD
            
=======

>>>>>>> c233d19cbec062fb8ce596706d82b95497b92612
            return ['success' => false, 'message' => 'Error al rechazar el voluntario. Por favor, intente nuevamente.'];
        }
    }

    /**
     * Cuenta el número total de voluntarios pendientes
     * 
     * @return int 
     */
    public function contarVoluntariosPendientes()
    {
        try {
            $sql = "EXEC voluntariosSinAprobar";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? count($rows) : 0;
        } catch (PDOException $e) {
            error_log("Error en contarVoluntariosPendientes: " . $e->getMessage());
            return 0;
        }
    }

    // ====================================================================
    // NUEVOS MÉTODOS PARA TRÁMITES
    // ====================================================================

    /**
     * Obtiene todas las solicitudes de trámites pendientes de validación
     * 
     * @return array Lista de trámites solicitados
     */
    public function getTramitesSolicitados()
    {
        try {
            $sql = "EXEC voluntariosSinAprobar @TipoNotify = 'Tramites'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getTramitesSolicitados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los detalles detallados de una solicitud de trámite
     * usando el procedimiento almacenado voluntariosSinAprobar con @TipoNotify = 'TramitesDetallado'
     * 
     * @param int $solicitudId ID de la solicitud
     * @return array|null Datos detallados de la solicitud
     */
    public function getDetallesTramiteCompleto($solicitudId)
    {
        try {
            $sql = "EXEC voluntariosSinAprobar @TipoNotify = 'TramitesDetallado', @solicitudid = :solicitudId";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':solicitudId', $solicitudId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getDetallesTramiteCompleto: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la información básica de una solicitud de trámite
<<<<<<< HEAD
     * 
     * @param int $solicitudId ID de la solicitud
     * @return array|null Datos básicos de la solicitud
=======
     * usando el procedimiento almacenado voluntariosSinAprobar con @TipoNotify = 'TramitesDetallado'
     * 
     * @param int $solicitudId ID de la solicitud
     * @return array|null Datos básicos de la solicitud con requerimientos
>>>>>>> c233d19cbec062fb8ce596706d82b95497b92612
     */
    public function getSolicitudTramiteById($solicitudId)
    {
        try {
<<<<<<< HEAD
            // Primero obtenemos la información básica de la solicitud
            $sql = "SELECT 
                        st.SolicitudID,
                        st.VoluntarioID,
                        CONCAT(v.Nombres, ' ', v.ApellidoPaterno, ' ', ISNULL(v.ApellidoMaterno, '')) AS NombreCompleto,
                        v.Email,
                        v.Telefono,
                        t.Nombre AS TramiteNombre,
                        t.Descripcion AS TramiteDescripcion,
                        tt.Nombre AS TipoTramite,
                        st.FechaSolicitud,
                        st.MotivoDeSolicitud,
                        es.Nombre AS EstatusNombre,
                        d.Nombre AS DelegacionNombre,
                        a.Nombre AS AreaNombre
                    FROM dbo.SolicitudesTramites st
                    INNER JOIN dbo.Voluntarios v ON st.VoluntarioID = v.VoluntarioID
                    INNER JOIN dbo.Tramites t ON st.TramiteID = t.TramiteID
                    INNER JOIN dbo.TiposTramite tt ON st.TipoTramiteID = tt.TipoTramiteID
                    INNER JOIN dbo.EstatusSolicitud es ON st.EstatusSolicitudID = es.EstatusSolicitudID
                    LEFT JOIN dbo.Delegaciones d ON v.DelegacionID = d.DelegacionID
                    LEFT JOIN dbo.Areas a ON v.AreaID = a.AreaID
                    WHERE st.SolicitudID = :solicitudId";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':solicitudId', $solicitudId, PDO::PARAM_INT);
            $stmt->execute();
            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($solicitud) {
                // Luego obtenemos los requerimientos detallados
                $solicitud['requerimientos'] = $this->getDetallesTramiteCompleto($solicitudId);
            }

            return $solicitud;
=======
            // Obtener información detallada usando el SP
            $requerimientos = $this->getDetallesTramiteCompleto($solicitudId);

            if (empty($requerimientos)) {
                return null;
            }

            // El SP devuelve: Requerimiento, TipoDato, DatoTexto, DatoNumero, DatoFecha, NombreArchivo, RutaArchivo, DatoSolicitudID
            return [
                'SolicitudID' => $solicitudId,
                'requerimientos' => $requerimientos
            ];
>>>>>>> c233d19cbec062fb8ce596706d82b95497b92612
        } catch (PDOException $e) {
            error_log("Error en getSolicitudTramiteById: " . $e->getMessage());
            return null;
        }
    }

<<<<<<< HEAD
   /**
=======
    /**
>>>>>>> c233d19cbec062fb8ce596706d82b95497b92612
     * Aprueba un trámite usando el procedimiento almacenado StatusTramite
     * 
     * @param int $solicitudId ID de la solicitud
     * @return array Resultado con 'success' y 'message'
     */
<<<<<<< HEAD
public function aprobarTramite($solicitudId)
{
    try {
        error_log("Intentando aprobar trámite ID: $solicitudId");
        
        $sql = "EXEC StatusTramite @solicitudId = :solicitudId, @TipoStatus = 'Aprobar'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':solicitudId', $solicitudId, PDO::PARAM_INT);
        $result = $stmt->execute();
        
        error_log("Resultado de ejecución: " . ($result ? 'true' : 'false'));
        
        return [
            'success' => true,
            'message' => 'Trámite aprobado exitosamente'
        ];
    } catch (PDOException $e) {
        error_log("Error en aprobarTramite: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al aprobar el trámite: ' . $e->getMessage()
        ];
    }
}

public function rechazarTramite($solicitudId)
{
    try {
        error_log("Intentando rechazar trámite ID: $solicitudId");
        
        $sql = "EXEC StatusTramite @solicitudId = :solicitudId, @TipoStatus = 'Rechazado'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':solicitudId', $solicitudId, PDO::PARAM_INT);
        $result = $stmt->execute();
        
        error_log("Resultado de ejecución: " . ($result ? 'true' : 'false'));
        
        return [
            'success' => true,
            'message' => 'Trámite rechazado exitosamente'
        ];
    } catch (PDOException $e) {
        error_log("Error en rechazarTramite: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al rechazar el trámite: ' . $e->getMessage()
        ];
    }
}


   /**
=======
    public function aprobarTramite($solicitudId)
    {
        try {
            error_log("Intentando aprobar trámite ID: $solicitudId");

            $sql = "EXEC StatusTramite @solicitudId = :solicitudId, @TipoStatus = 'Aprobar'";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':solicitudId', $solicitudId, PDO::PARAM_INT);
            $result = $stmt->execute();

            error_log("Resultado de ejecución: " . ($result ? 'true' : 'false'));

            return [
                'success' => true,
                'message' => 'Trámite aprobado exitosamente'
            ];
        } catch (PDOException $e) {
            error_log("Error en aprobarTramite: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al aprobar el trámite: ' . $e->getMessage()
            ];
        }
    }

    public function rechazarTramite($solicitudId)
    {
        try {
            error_log("Intentando rechazar trámite ID: $solicitudId");

            $sql = "EXEC StatusTramite @solicitudId = :solicitudId, @TipoStatus = 'Rechazado'";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':solicitudId', $solicitudId, PDO::PARAM_INT);
            $result = $stmt->execute();

            error_log("Resultado de ejecución: " . ($result ? 'true' : 'false'));

            return [
                'success' => true,
                'message' => 'Trámite rechazado exitosamente'
            ];
        } catch (PDOException $e) {
            error_log("Error en rechazarTramite: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al rechazar el trámite: ' . $e->getMessage()
            ];
        }
    }


    /**
>>>>>>> c233d19cbec062fb8ce596706d82b95497b92612
     * Cuenta el número total de trámites solicitados
     * usando el procedimiento almacenado voluntariosSinAprobar
     * 
     * @return int Número de trámites pendientes
     */
    public function contarTramitesSolicitados()
    {
        try {
            $sql = "EXEC voluntariosSinAprobar @TipoNotify = 'Tramites'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $tramites = $stmt->fetchAll(PDO::FETCH_ASSOC);
<<<<<<< HEAD
            
=======

>>>>>>> c233d19cbec062fb8ce596706d82b95497b92612
            return is_array($tramites) ? count($tramites) : 0;
        } catch (PDOException $e) {
            error_log("Error en contarTramitesSolicitados: " . $e->getMessage());
            return 0;
        }
    }
<<<<<<< HEAD
}
=======

    // ====================================================================
    // MÉTODOS PARA EXPEDIENTES/DOCUMENTOS
    // ====================================================================

    /**
     * Obtiene los documentos de expediente pendientes de validación
     * usando el procedimiento almacenado voluntariosSinAprobar con @TipoNotify = 'Expediente'
     *
     * @return array Lista de documentos pendientes con información del voluntario
     */
    public function getExpedientesPendientes()
    {
        try {
            $sql = "EXEC [dbo].[voluntariosSinAprobar] @TipoNotify = 'Expediente'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getExpedientesPendientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta el número de documentos de expediente pendientes
     *
     * @return int Número de documentos pendientes
     */
    public function contarExpedientesPendientes()
    {
        try {
            $documentos = $this->getExpedientesPendientes();
            return is_array($documentos) ? count($documentos) : 0;
        } catch (Exception $e) {
            error_log("Error en contarExpedientesPendientes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene las especialidades pendientes de aprobación
     * utilizando el procedimiento almacenado voluntariosSinAprobar con @TipoNotify = 'Especialidades'
     * 
     * @return array Lista de especialidades pendientes
     */
    public function getEspecialidadesPendientes()
    {
        try {
            $sql = "EXEC voluntariosSinAprobar @TipoNotify = 'Especialidades'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $especialidades;
        } catch (PDOException $e) {
            error_log("Error en getEspecialidadesPendientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el VoluntarioDocumentoID basándose en CURP y nombre del documento
     * 
     * @param string $curp CURP del voluntario
     * @param string $nombreDocumento Nombre del documento de especialidad
     * @return int|null ID del documento o null si no se encuentra
     */
    public function getVoluntarioDocumentoID($curp, $nombreDocumento)
    {
        try {
            $sql = "SELECT TOP 1 vd.VoluntarioDocumentoID 
                    FROM VoluntarioDocumento vd
                    INNER JOIN Voluntarios v ON v.VoluntarioID = vd.VoluntarioID
                    INNER JOIN CatDocumentos cd ON cd.DocumentoID = vd.DocumentoID
                    WHERE v.curp = :curp 
                    AND cd.Nombre = :nombreDocumento
                    AND vd.EstatusValidacion = 'Pendiente'
                    AND cd.TipoDocumento = 'Especialidad'";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':curp', $curp, PDO::PARAM_STR);
            $stmt->bindParam(':nombreDocumento', $nombreDocumento, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['VoluntarioDocumentoID'] : null;
        } catch (PDOException $e) {
            error_log("Error en getVoluntarioDocumentoID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cuenta el número de especialidades pendientes
     *
     * @return int Número de especialidades pendientes
     */
    public function contarEspecialidadesPendientes()
    {
        try {
            $especialidades = $this->getEspecialidadesPendientes();
            return is_array($especialidades) ? count($especialidades) : 0;
        } catch (Exception $e) {
            error_log("Error en contarEspecialidadesPendientes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Aprueba una especialidad usando el procedimiento almacenado AprobarEspecialidad
     * 
     * @param int $voluntarioDocumentoID ID del documento del voluntario
     * @param int $adminValidadorID ID del administrador que valida
     * @return array Resultado con 'success' y 'message'
     */
    public function aprobarEspecialidad($voluntarioDocumentoID, $adminValidadorID)
    {
        try {
            $sql = "EXEC AprobarEspecialidad 
                    @adminvalidador = :adminValidador,
                    @voluntariodocumentoid = :voluntarioDocumentoID,
                    @tipo = 'Validar'";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':adminValidador', $adminValidadorID, PDO::PARAM_INT);
            $stmt->bindParam(':voluntarioDocumentoID', $voluntarioDocumentoID, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Especialidad aprobada exitosamente'
            ];
        } catch (PDOException $e) {
            error_log("Error en aprobarEspecialidad: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al aprobar la especialidad: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Rechaza una especialidad usando el procedimiento almacenado AprobarEspecialidad
     * 
     * @param int $voluntarioDocumentoID ID del documento del voluntario
     * @param int $adminValidadorID ID del administrador que rechaza
     * @return array Resultado con 'success' y 'message'
     */
    public function rechazarEspecialidad($voluntarioDocumentoID, $adminValidadorID)
    {
        try {
            $sql = "EXEC AprobarEspecialidad 
                    @adminvalidador = :adminValidador,
                    @voluntariodocumentoid = :voluntarioDocumentoID,
                    @tipo = 'Rechazado'";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':adminValidador', $adminValidadorID, PDO::PARAM_INT);
            $stmt->bindParam(':voluntarioDocumentoID', $voluntarioDocumentoID, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Especialidad rechazada exitosamente'
            ];
        } catch (PDOException $e) {
            error_log("Error en rechazarEspecialidad: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al rechazar la especialidad: ' . $e->getMessage()
            ];
        }
    }
}
>>>>>>> c233d19cbec062fb8ce596706d82b95497b92612
