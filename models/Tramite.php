<?php
// /models/tramitesModels.php
require_once __DIR__ . '/../config/database.php';

class Tramite
{
    private $pdo;

    // ⚠️ CORREGIDO: El constructor no debe recibir parámetros
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Llama al procedure con @tipover = 0 y @voluntarioid para obtener 
     * todos los trámites con su estatus para un voluntario específico.
     * 
     * @param int $voluntarioID ID del voluntario
     * @return array Lista de trámites con columnas: TramiteID, NombreTramite, Descripcion, Estatus
     */
    public function obtenerTramitesActivos($voluntarioID = null)
    {
        try {
            if ($voluntarioID === null) {
                // Si no se proporciona ID, intentar obtenerlo de la sesión
                $voluntarioID = $_SESSION['user']['id'] ?? 0;
            }

            $sql = "EXEC [VerTramites] @tipover = 0, @voluntatioid = :voluntarioID";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':voluntarioID', $voluntarioID, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTramitesActivos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Llama al procedure con @TipoVer = 1 y un ID de trámite
     * para obtener sus requerimientos específicos.
     */
    public function obtenerRequerimientosPorTramite($tipoTramiteID)
    {
        try {
            $sql = "exec VerTramites @TipoVer = :TipoVer, @TipoTramiteID = :TipoTramiteID";
            $stmt = $this->pdo->prepare($sql);

            $tipoVer = 1;
            $stmt->bindParam(':TipoVer', $tipoVer, PDO::PARAM_INT);
            $stmt->bindParam(':TipoTramiteID', $tipoTramiteID, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerRequerimientosPorTramite: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Llama al SP "GestionarTramiteCompleto" para crear/actualizar
     * un trámite y todos sus requerimientos asociados.
     */
    public function gestionarTramiteCompleto($nombreTramite, $descripcionTramite, $requerimientosArray)
    {
        try {
            $requerimientosJSON = json_encode($requerimientosArray, JSON_UNESCAPED_UNICODE);

            $sql = "exec [dbo].[usp_GestionarTramiteCompleto] 
                        @NombreTramite = :NombreTramite,
                        @DescripcionTramite = :DescripcionTramite,
                        @NuevosRequerimientos_JSON = :NuevosRequerimientos_JSON";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':NombreTramite', $nombreTramite, PDO::PARAM_STR);
            $stmt->bindParam(':DescripcionTramite', $descripcionTramite, PDO::PARAM_STR);
            $stmt->bindParam(':NuevosRequerimientos_JSON', $requerimientosJSON, PDO::PARAM_STR);

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en gestionarTramiteCompleto: " . $e->getMessage());
            return ['Estatus' => 'Error', 'Mensaje' => $e->getMessage()];
        }
    }

    /**
     * Llama a [usp_IniciarNuevaSolicitud]
     * Crea la solicitud principal y los registros vacíos en DatoSolicitud.
     */
    public function iniciarSolicitud($voluntarioID, $tipoTramiteID, $observaciones)
    {
        try {
            $sql = "exec [dbo].[usp_IniciarNuevaSolicitud] 
                        @VoluntarioID = :VoluntarioID,
                        @TipoTramiteID = :TipoTramiteID,
                        @Observaciones = :Observaciones";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':VoluntarioID', $voluntarioID, PDO::PARAM_INT);
            $stmt->bindParam(':TipoTramiteID', $tipoTramiteID, PDO::PARAM_INT);
            $stmt->bindParam(':Observaciones', $observaciones, PDO::PARAM_STR);

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en iniciarSolicitud: " . $e->getMessage());
            return ['Estatus' => 'Error', 'Mensaje' => $e->getMessage()];
        }
    }

    /**
     * Llama a [usp_GuardarDatosSolicitud]
     * Actualiza los registros de DatoSolicitud con la información del usuario.
     */
    public function guardarDatosSolicitud($datosArray, $nuevoEstatusNombre)
    {
        try {
            $datosJSON = json_encode($datosArray, JSON_UNESCAPED_UNICODE);

            $sql = "exec [dbo].[usp_GuardarDatosSolicitud] 
                        @NuevosDatos_JSON = :NuevosDatos_JSON,
                        @NuevoEstatusNombre = :NuevoEstatusNombre";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':NuevosDatos_JSON', $datosJSON, PDO::PARAM_STR);
            $stmt->bindParam(':NuevoEstatusNombre', $nuevoEstatusNombre, PDO::PARAM_STR);

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en guardarDatosSolicitud: " . $e->getMessage());
            return ['Estatus' => 'Error', 'Mensaje' => $e->getMessage()];
        }
    }

    /**
     * ✅ NUEVO: Llama a [usp_ModificarTramiteCompleto]
     * Modifica un trámite existente y sincroniza sus requerimientos.
     */
    public function modificarTramiteCompleto($tipoTramiteID, $nombreTramite, $descripcionTramite, $requerimientosArray)
    {
        try {
            $requerimientosJSON = json_encode($requerimientosArray, JSON_UNESCAPED_UNICODE);

            $sql = "exec [dbo].[usp_ModificarTramiteCompleto] 
                        @TipoTramiteID = :TipoTramiteID,
                        @NombreTramite = :NombreTramite,
                        @DescripcionTramite = :DescripcionTramite,
                        @NuevosRequerimientos_JSON = :NuevosRequerimientos_JSON";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':TipoTramiteID', $tipoTramiteID, PDO::PARAM_INT);
            $stmt->bindParam(':NombreTramite', $nombreTramite, PDO::PARAM_STR);
            $stmt->bindParam(':DescripcionTramite', $descripcionTramite, PDO::PARAM_STR);
            $stmt->bindParam(':NuevosRequerimientos_JSON', $requerimientosJSON, PDO::PARAM_STR);

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en modificarTramiteCompleto: " . $e->getMessage());
            return ['Estatus' => 'Error', 'Mensaje' => $e->getMessage()];
        }
    }

    /**
     * ✅ NUEVO: Llama a [darBajaTramite]
     * Marca un trámite como inactivo (no lo elimina físicamente).
     */
    public function darBajaTramite($tipoTramiteID)
    {
        try {
            $sql = "exec [dbo].[darBajaTramite] @tipotramiteid = :tipotramiteid";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':tipotramiteid', $tipoTramiteID, PDO::PARAM_INT);

            $stmt->execute();

            // El SP no devuelve un resultado, así que asumimos éxito si no hay excepción
            return ['Estatus' => 'Éxito', 'Mensaje' => 'Trámite dado de baja correctamente'];
        } catch (PDOException $e) {
            error_log("Error en darBajaTramite: " . $e->getMessage());
            return ['Estatus' => 'Error', 'Mensaje' => $e->getMessage()];
        }
    }
}
