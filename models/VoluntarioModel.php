<?php
require_once __DIR__ . '/../config/database.php';

class VoluntarioModel
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
            // Preparar la llamada al procedimiento almacenado
            $sql = "EXEC voluntariosSinAprobar";
            $stmt = $this->conn->prepare($sql);
            
            // Ejecutar el procedimiento
            $stmt->execute();
            
            // Obtener todos los resultados
            $voluntarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $voluntarios;
            
        } catch (PDOException $e) {
            // Registrar el error y retornar array vacío
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
            // Preparar la llamada al procedimiento almacenado
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
            
            // Capturar el mensaje de error específico del procedimiento almacenado
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
            // Validar que el motivo no esté vacío antes de llamar al SP
            if (empty(trim($motivo))) {
                return [
                    'success' => false,
                    'message' => 'El motivo del rechazo es obligatorio'
                ];
            }

            // Preparar la llamada al procedimiento almacenado
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
            
            // Capturar mensajes específicos del procedimiento almacenado
            $errorMessage = $e->getMessage();
            
            if (strpos($errorMessage, 'Se requiere un motivo') !== false) {
                return [
                    'success' => false,
                    'message' => 'El motivo del rechazo es obligatorio'
                ];
            }
            
            if (strpos($errorMessage, 'no existe o ya no está pendiente') !== false) {
                return [
                    'success' => false,
                    'message' => 'El voluntario no existe o ya fue procesado anteriormente'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Error al rechazar el voluntario. Por favor, intente nuevamente.'
            ];
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
}