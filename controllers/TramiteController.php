<?php
// /controllers/TramiteController.php
require_once __DIR__ . '/../models/tramitesModels.php';
require_once __DIR__ . '/../config/database.php';

class TramiteController {
    private $tramiteModel;

    public function __construct() {
        $this->tramiteModel = new Tramites();
    }

    /**
     * Muestra la página principal con la lista de trámites disponibles.
     */
    public function mostrarTramitesDisponibles() {
        $tramites = $this->tramiteModel->obtenerTramitesActivos();
        require 'views/home/tramites.php'; 
    }

    /**
     * Devuelve los trámites como JSON (para AJAX)
     */
    public function obtenerTramitesJSON() {
        $tramites = $this->tramiteModel->obtenerTramitesActivos();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($tramites, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtiene los DatoSolicitudID de una solicitud recién creada
     */
    public function obtenerDatosSolicitud() {
        try {
            $solicitudID = $_GET['solicitudID'] ?? 0;
            
            if ($solicitudID == 0) {
                throw new Exception('ID de solicitud no válido');
            }
            
            $sql = "SELECT 
                        ds.DatoSolicitudID,
                        ds.RequerimientosID,
                        cr.Nombre AS NombreRequerimiento,
                        cr.TipoDato
                    FROM DatoSolicitud ds
                    INNER JOIN CatRequerimientos cr ON ds.RequerimientosID = cr.RequerimientosID
                    WHERE ds.SolicitudID = :solicitudID";
            
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':solicitudID', $solicitudID, PDO::PARAM_INT);
            $stmt->execute();
            
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($datos, JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obtiene los requerimientos de un trámite específico (para AJAX).
     */
    public function obtenerRequerimientos() {
        try {
            $tramiteID = 0;
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $tramiteID = (int)$_GET['id'];
            }

            if ($tramiteID === 0) {
                throw new Exception('ID de trámite no válido');
            }

            $requerimientos = $this->tramiteModel->obtenerRequerimientosPorTramite($tramiteID);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($requerimientos, JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * ACCIÓN 1: Se llama cuando el usuario hace clic en "Solicitar"
     * Inicia la solicitud y devuelve el nuevo SolicitudID.
     */
    public function iniciar() {
        try {
            $voluntarioID = $_POST['voluntarioID'] ?? 0;
            $tipoTramiteID = $_POST['tipoTramiteID'] ?? 0;
            $observaciones = $_POST['observaciones'] ?? '';

            if ($voluntarioID == 0 || $tipoTramiteID == 0) {
                 throw new Exception('Faltan IDs de voluntario o trámite.');
            }

            $resultado = $this->tramiteModel->iniciarSolicitud($voluntarioID, $tipoTramiteID, $observaciones);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['Estatus' => 'Error', 'Mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * ACCIÓN 2: Se llama cuando el usuario hace clic en "Enviar"
     * Guarda todos los datos del formulario.
     */
    public function guardar() {
        try {
            $datoSolicitudIDs = $_POST['DatoSolicitudID'] ?? [];
            $datosTexto = $_POST['DatoTexto'] ?? [];
            $datosNumero = $_POST['DatoNumero'] ?? [];
            $datosFecha = $_POST['DatoFecha'] ?? [];
            $nombresArchivo = $_POST['NombreArchivo'] ?? [];
            $rutasArchivo = $_POST['RutaArchivo'] ?? [];
            
            $nuevoEstatus = $_POST['nuevoEstatus'] ?? 'En Revisión';

            $datosArray = [];
            foreach ($datoSolicitudIDs as $i => $id) {
                if (!empty($id)) {
                    $datosArray[] = [
                        'DatoSolicitudID' => (int)$id,
                        'DatoTexto' => $datosTexto[$i] ?: null,
                        'DatoNumero' => $datosNumero[$i] ?: null,
                        'DatoFecha' => $datosFecha[$i] ?: null,
                        'NombreArchivo' => $nombresArchivo[$i] ?: null,
                        'RutaArchivo' => $rutasArchivo[$i] ?: null
                    ];
                }
            }
            
            if (empty($datosArray)) {
                 throw new Exception('No se recibieron datos para guardar.');
            }

            $resultado = $this->tramiteModel->guardarDatosSolicitud($datosArray, $nuevoEstatus);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['Estatus' => 'Error', 'Mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * AÑADIDO: Recibe los datos de un formulario de "Nuevo Trámite" y
     * lo guarda en la base de datos.
     */
    public function guardarTramite() {
        try {
            $nombreTramite = $_POST['nombre_tramite'] ?? '';
            $descripcionTramite = $_POST['descripcion_tramite'] ?? '';

            if (empty($nombreTramite) || empty($descripcionTramite)) {
                throw new Exception('Nombre y descripción son obligatorios');
            }

            $nombresReq = $_POST['req_nombre'] ?? [];
            $tiposDatoReq = $_POST['req_tipodato'] ?? [];
            $nombresDocReq = $_POST['req_docnombre'] ?? [];
            $tiposDocReq = $_POST['req_tipodoc'] ?? [];

            $requerimientosArray = [];
            foreach ($nombresReq as $i => $nombre) {
                if (!empty($nombre)) {
                    $requerimientosArray[] = [
                        'NombreRequerimiento' => $nombre,
                        'TipoDato' => $tiposDatoReq[$i] ?? 'texto',
                        'NombreDocumento' => $nombresDocReq[$i] ?: null,
                        'TipoDocumento' => $tiposDocReq[$i] ?: null
                    ];
                }
            }

            if (empty($requerimientosArray)) {
                throw new Exception('Debes agregar al menos un requerimiento');
            }

            $resultado = $this->tramiteModel->gestionarTramiteCompleto(
                $nombreTramite,
                $descripcionTramite,
                $requerimientosArray
            );

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['Estatus' => 'Error', 'Mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * ✅ NUEVO: Modifica un trámite existente
     */
    public function modificarTramite() {
        try {
            $tipoTramiteID = $_POST['tipo_tramite_id'] ?? 0;
            $nombreTramite = $_POST['nombre_tramite'] ?? '';
            $descripcionTramite = $_POST['descripcion_tramite'] ?? '';

            if ($tipoTramiteID == 0) {
                throw new Exception('No se especificó el ID del trámite a modificar');
            }

            if (empty($nombreTramite) || empty($descripcionTramite)) {
                throw new Exception('Nombre y descripción son obligatorios');
            }

            $nombresReq = $_POST['req_nombre'] ?? [];
            $tiposDatoReq = $_POST['req_tipodato'] ?? [];
            $nombresDocReq = $_POST['req_docnombre'] ?? [];
            $tiposDocReq = $_POST['req_tipodoc'] ?? [];

            $requerimientosArray = [];
            foreach ($nombresReq as $i => $nombre) {
                if (!empty($nombre)) {
                    $requerimientosArray[] = [
                        'NombreRequerimiento' => $nombre,
                        'TipoDato' => $tiposDatoReq[$i] ?? 'texto',
                        'NombreDocumento' => $nombresDocReq[$i] ?: null,
                        'TipoDocumento' => $tiposDocReq[$i] ?: null
                    ];
                }
            }

            if (empty($requerimientosArray)) {
                throw new Exception('Debes agregar al menos un requerimiento');
            }

            $resultado = $this->tramiteModel->modificarTramiteCompleto(
                $tipoTramiteID,
                $nombreTramite,
                $descripcionTramite,
                $requerimientosArray
            );

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['Estatus' => 'Error', 'Mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * ✅ NUEVO: Da de baja (desactiva) un trámite
     */
    public function eliminarTramite() {
        try {
            // Puede venir por POST o GET
            $tipoTramiteID = $_POST['tipo_tramite_id'] ?? $_GET['id'] ?? 0;

            if ($tipoTramiteID == 0) {
                throw new Exception('No se especificó el ID del trámite a eliminar');
            }

            $resultado = $this->tramiteModel->darBajaTramite($tipoTramiteID);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['Estatus' => 'Error', 'Mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}
?>