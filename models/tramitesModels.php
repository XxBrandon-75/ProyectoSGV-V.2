<?php
// /models/tramitesModels.php

require_once __DIR__ . '/../config/database.php'; // Asegúrate que la ruta sea correcta

class Tramites {
    private $pdo;

    public function __construct() {
        // Asumiendo que tu clase Database es un Singleton
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    
    /**
     * Llama al procedure con @TipoVer = 0 para obtener 
     * todos los trámites activos.
     */
    public function obtenerTramitesActivos() {
        try {
            $sql = "exec VerTramites @TipoVer = :TipoVer";
            $stmt = $this->pdo->prepare($sql);
            
            $tipoVer = 0; // @TipoVer = 0 para ver trámites activos
            $stmt->bindParam(':TipoVer', $tipoVer, PDO::PARAM_INT);
            
            $stmt->execute();
            // Usamos fetchAll para obtener todas las filas
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en obtenerTramitesActivos: " . $e->getMessage());
            return []; // Devolver un array vacío en caso de error
        }
    }

    /**
     * Llama al procedure con @TipoVer = 1 y un ID de trámite
     * para obtener sus requerimientos específicos.
     */
    public function obtenerRequerimientosPorTramite($tipoTramiteID) {
        try {
            $sql = "exec VerTramites @TipoVer = :TipoVer, @TipoTramiteID = :TipoTramiteID";
            $stmt = $this->pdo->prepare($sql);
            
            $tipoVer = 1; // @TipoVer = 1 para ver requerimientos
            $stmt->bindParam(':TipoVer', $tipoVer, PDO::PARAM_INT);
            $stmt->bindParam(':TipoTramiteID', $tipoTramiteID, PDO::PARAM_INT);

            $stmt->execute();
            // Usamos fetchAll para obtener todos los requerimientos
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en obtenerRequerimientosPorTramite: " . $e->getMessage());
            return []; // Devolver un array vacío en caso de error
        }
    }
    
    /**
     * Llama al SP "GestionarTramiteCompleto" para crear/actualizar
     * un trámite y todos sus requerimientos asociados.
     *
     * @param string $nombreTramite El nombre del trámite.
     * @param string $descripcionTramite La descripción del trámite.
     * @param array $requerimientosArray Un array de PHP con los requerimientos.
     * @return array El resultado del Stored Procedure.
     */
    public function gestionarTramiteCompleto($nombreTramite, $descripcionTramite, $requerimientosArray) {
        try {
            // 1. Convertir el array de requerimientos a un string JSON
            $requerimientosJSON = json_encode($requerimientosArray);

            $sql = "exec [dbo].[usp_GestionarTramiteCompleto] 
                        @NombreTramite = :NombreTramite,
                        @DescripcionTramite = :DescripcionTramite,
                        @NuevosRequerimientos_JSON = :NuevosRequerimientos_JSON";
            
            $stmt = $this->pdo->prepare($sql);
            
            // 2. Vincular los parámetros
            $stmt->bindParam(':NombreTramite', $nombreTramite, PDO::PARAM_STR);
            $stmt->bindParam(':DescripcionTramite', $descripcionTramite, PDO::PARAM_STR);
            $stmt->bindParam(':NuevosRequerimientos_JSON', $requerimientosJSON, PDO::PARAM_STR);
            
            $stmt->execute();
            
            // Devuelve la fila de resultado ('Éxito' o 'Error')
            return $stmt->fetch(PDO::FETCH_ASSOC); 

        } catch (PDOException $e) {
            error_log("Error en gestionarTramiteCompleto: " . $e->getMessage());
            // Devolver un array con el formato de error
            return ['Estatus' => 'Error', 'Mensaje' => $e->getMessage()];
        }
    }

    /**
     * (NUEVA FUNCIÓN)
     * Llama al SP "usp_ModificarTramiteCompleto" para modificar
     * un trámite y sincronizar sus requerimientos.
     */
    public function modificarTramiteCompleto($tipoTramiteID, $nombreTramite, $descripcionTramite, $requerimientosArray) {
        try {
            $requerimientosJSON = json_encode($requerimientosArray);

            $sql = "exec [dbo].[usp_ModificarTramiteCompleto] 
                        @TipoTramiteID = :TipoTramiteID,
                        @NombreTramite = :NombreTramite,
                        @DescripcionTramite = :DescripcionTramite,
                        @NuevosRequerimientos_JSON = :NuevosRequerimientos_JSON";
            
            $stmt = $this->pdo->prepare($sql);
            
            // Vincular los parámetros
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
     * Llama al SP "usp_IniciarNuevaSolicitud"
     * Crea la solicitud principal y los registros vacíos en DatoSolicitud.
     */
    public function iniciarSolicitud($voluntarioID, $tipoTramiteID, $observaciones) {
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
            return $stmt->fetch(PDO::FETCH_ASSOC); // Devuelve {Estatus, Mensaje, SolicitudID}

        } catch (PDOException $e) {
            error_log("Error en iniciarSolicitud: " . $e->getMessage());
            return ['Estatus' => 'Error', 'Mensaje' => $e->getMessage()];
        }
    }

    /**
     * Llama al SP "usp_GuardarDatosSolicitud"
     * Actualiza las filas en DatoSolicitud con la info del usuario.
     */
    public function guardarDatosSolicitud($datosArray, $nuevoEstatus) {
        try {
            $datosJSON = json_encode($datosArray);

            $sql = "exec [dbo].[usp_GuardarDatosSolicitud] 
                        @NuevosDatos_JSON = :NuevosDatos_JSON,
                        @NuevoEstatusNombre = :NuevoEstatusNombre";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindParam(':NuevosDatos_JSON', $datosJSON, PDO::PARAM_STR);
            $stmt->bindParam(':NuevoEstatusNombre', $nuevoEstatus, PDO::PARAM_STR);
            
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); // Devuelve {Estatus, Mensaje}

        } catch (PDOException $e) {
            error_log("Error en guardarDatosSolicitud: " . $e->getMessage());
            return ['Estatus' => 'Error', 'Mensaje' => $e->getMessage()];
        }
    }
}
?>
