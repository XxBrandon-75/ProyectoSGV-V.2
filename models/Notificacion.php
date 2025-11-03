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
            $sql = "SELECT 
                        st.SolicitudID,
                        st.VoluntarioID,
                        CONCAT(v.Nombres, ' ', v.ApellidoPaterno, ' ', ISNULL(v.ApellidoMaterno, '')) AS NombreCompleto,
                        v.Email,
                        t.Nombre AS TramiteNombre,
                        t.Descripcion AS TramiteDescripcion,
                        st.FechaSolicitud,
                        st.MotivoDeSolicitud,
                        st.NumeroCredencial,
                        st.VigenciaCredencial,
                        es.Nombre AS EstatusNombre,
                        d.Nombre AS DelegacionNombre,
                        a.Nombre AS AreaNombre
                    FROM dbo.SolicitudesTramites st
                    INNER JOIN dbo.Voluntarios v ON st.VoluntarioID = v.VoluntarioID
                    INNER JOIN dbo.Tramites t ON st.TramiteID = t.TramiteID
                    INNER JOIN dbo.EstatusSolicitud es ON st.EstatusSolicitudID = es.EstatusSolicitudID
                    LEFT JOIN dbo.Delegaciones d ON v.DelegacionID = d.DelegacionID
                    LEFT JOIN dbo.Areas a ON v.AreaID = a.AreaID
                    WHERE es.Nombre = 'Solicitado'
                    ORDER BY st.FechaSolicitud DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getTramitesSolicitados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los detalles de una solicitud de trámite por su ID
     * 
     * @param int $solicitudId ID de la solicitud
     * @return array|null Datos de la solicitud o null si no existe
     */
    public function getSolicitudTramiteById($solicitudId)
    {
        try {
            $sql = "SELECT 
                        st.*,
                        CONCAT(v.Nombres, ' ', v.ApellidoPaterno, ' ', ISNULL(v.ApellidoMaterno, '')) AS NombreCompleto,
                        v.Email,
                        v.Telefono,
                        t.Nombre AS TramiteNombre,
                        t.Descripcion AS TramiteDescripcion,
                        es.Nombre AS EstatusNombre,
                        d.Nombre AS DelegacionNombre,
                        a.Nombre AS AreaNombre
                    FROM dbo.SolicitudesTramites st
                    INNER JOIN dbo.Voluntarios v ON st.VoluntarioID = v.VoluntarioID
                    INNER JOIN dbo.Tramites t ON st.TramiteID = t.TramiteID
                    INNER JOIN dbo.EstatusSolicitud es ON st.EstatusSolicitudID = es.EstatusSolicitudID
                    LEFT JOIN dbo.Delegaciones d ON v.DelegacionID = d.DelegacionID
                    LEFT JOIN dbo.Areas a ON v.AreaID = a.AreaID
                    WHERE st.SolicitudID = :solicitudId";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':solicitudId', $solicitudId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getSolicitudTramiteById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Valida una solicitud de trámite usando el procedimiento almacenado
     * 
     * @param int $solicitudId ID de la solicitud
     * @param int $adminId ID del administrador que valida
     * @param int $nuevoEstatusId ID del nuevo estatus (aprobado/rechazado)
     * @param string $observaciones Observaciones de la validación
     * @param string $numeroCredencial Número de credencial (opcional)
     * @param string $vigenciaCredencial Fecha de vigencia (opcional)
     * @return array Resultado con 'success' y 'message'
     */
    public function validarSolicitudTramite($solicitudId, $adminId, $nuevoEstatusId, $observaciones = null, $numeroCredencial = null, $vigenciaCredencial = null)
    {
        try {
            $sql = "EXEC sp_ValidarSolicitudTramite 
                    @SolicitudID = :solicitudId,
                    @AdminID = :adminId,
                    @NuevoEstatusSolicitudID = :nuevoEstatusId,
                    @Observaciones = :observaciones,
                    @NumeroCredencial = :numeroCredencial,
                    @VigenciaCredencial = :vigenciaCredencial";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':solicitudId', $solicitudId, PDO::PARAM_INT);
            $stmt->bindParam(':adminId', $adminId, PDO::PARAM_INT);
            $stmt->bindParam(':nuevoEstatusId', $nuevoEstatusId, PDO::PARAM_INT);
            $stmt->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
            $stmt->bindParam(':numeroCredencial', $numeroCredencial, PDO::PARAM_STR);
            $stmt->bindParam(':vigenciaCredencial', $vigenciaCredencial, PDO::PARAM_STR);
            
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Trámite validado exitosamente'
            ];
        } catch (PDOException $e) {
            error_log("Error en validarSolicitudTramite: " . $e->getMessage());
            $errorMessage = $e->getMessage();
            
            if (strpos($errorMessage, 'no existe o ya no se encuentra en estado') !== false) {
                return [
                    'success' => false,
                    'message' => 'La solicitud no existe o ya fue procesada anteriormente'
                ];
            }
            
            if (strpos($errorMessage, 'no tiene permisos') !== false) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para validar trámites'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Error al validar el trámite. Por favor, intente nuevamente.'
            ];
        }
    }

    /**
     * Obtiene los IDs de los estatus de solicitud
     * 
     * @return array Array asociativo con los nombres y IDs de estatus
     */
    public function getEstatusSolicitudIds()
    {
        try {
            $sql = "SELECT EstatusSolicitudID, Nombre FROM dbo.EstatusSolicitud";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $estatus = [];
            foreach ($resultados as $row) {
                $estatus[$row['Nombre']] = $row['EstatusSolicitudID'];
            }
            
            return $estatus;
        } catch (PDOException $e) {
            error_log("Error en getEstatusSolicitudIds: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta el número total de trámites solicitados
     * 
     * @return int Número de trámites pendientes
     */
    public function contarTramitesSolicitados()
    {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM dbo.SolicitudesTramites st
                    INNER JOIN dbo.EstatusSolicitud es ON st.EstatusSolicitudID = es.EstatusSolicitudID
                    WHERE es.Nombre = 'Solicitado'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int) $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en contarTramitesSolicitados: " . $e->getMessage());
            return 0;
        }
    }
}