<?php
// /controllers/tramitesController.php

// Incluimos el modelo que vamos a utilizar
require_once __DIR__ . '/../models/tramitesModels.php'; // Asegúrate que el nombre de archivo coincide

class TramiteController {
    
    private $tramiteModel;

    public function __construct() {
        // Asumiendo que tu clase Database es un Singleton
        $pdo = Database::getInstance()->getConnection();
        $this->tramiteModel = new Tramites($pdo); // Asegúrate que el nombre de clase coincide
    }

    /**
     * Muestra la página principal con la lista de trámites disponibles.
     */
    public function mostrarTramitesDisponibles() {
        // 1. Pide los datos al Modelo
        $tramites = $this->tramiteModel->obtenerTramitesActivos();
        
        // 2. Carga la Vista y le pasa los datos
        // (Tu vista 'tramites.php' haría un 'foreach' sobre la variable $tramites)
        require 'vistas/tramites.php'; 
    }

    /**
     * Obtiene los requerimientos de un trámite específico (para AJAX).
     */
    public function obtenerRequerimientos() {
        // 1. Obtiene el ID del trámite (usualmente por GET)
        $tramiteID = 0;
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $tramiteID = (int)$_GET['id'];
        }

        if ($tramiteID === 0) {
            echo json_encode(['error' => 'ID de trámite no válido']);
            return;
        }

        // 2. Pide los requerimientos al Modelo
        $requerimientos = $this->tramiteModel->obtenerRequerimientosPorTramite($tipoTramiteID);

        // 3. Devuelve los datos como JSON para que JavaScript los lea
        header('Content-Type: application/json');
        echo json_encode($requerimientos);
    }

    /**
     * ACCIÓN 1: Se llama cuando el usuario hace clic en "Solicitar"
     * Inicia la solicitud y devuelve el nuevo SolicitudID.
     */
    public function iniciar() {
        try {
            // Asumimos que los datos vienen por POST (de un AJAX)
            $voluntarioID = $_POST['voluntarioID'] ?? 0;
            $tipoTramiteID = $_POST['tipoTramiteID'] ?? 0;
            $observaciones = $_POST['observaciones'] ?? '';

            if ($voluntarioID == 0 || $tipoTramiteID == 0) {
                 throw new Exception('Faltan IDs de voluntario o trámite.');
            }

            $resultado = $this->tramiteModel->iniciarSolicitud($voluntarioID, $tipoTramiteID, $observaciones);

            header('Content-Type: application/json');
            echo json_encode($resultado);

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['Estatus' => 'Error', 'Mensaje' => $e->getMessage()]);
        }
    }

    /**
     * ACCIÓN 2: Se llama cuando el usuario hace clic en "Enviar"
     * Guarda todos los datos del formulario.
     */
    public function guardar() {
        try {
            // 1. Obtener los datos del formulario
            // El formulario debe enviar los datos como arrays
            $datoSolicitudIDs = $_POST['DatoSolicitudID'] ?? [];
            $datosTexto = $_POST['DatoTexto'] ?? [];
            $datosNumero = $_POST['DatoNumero'] ?? [];
            $datosFecha = $_POST['DatoFecha'] ?? [];
            $nombresArchivo = $_POST['NombreArchivo'] ?? [];
            $rutasArchivo = $_POST['RutaArchivo'] ?? [];
            
            $nuevoEstatus = $_POST['nuevoEstatus'] ?? 'En Revisión';

            // 2. Re-formatear los datos en un array estructurado
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

            // 3. Llamar al Modelo con los datos listos
            $resultado = $this->tramiteModel->guardarDatosSolicitud($datosArray, $nuevoEstatus);

            // 4. Devolver el resultado como JSON a la vista (para AJAX)
            header('Content-Type: application/json');
            echo json_encode($resultado);

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['Estatus' => 'Error', 'Mensaje' => $e->getMessage()]);
        }
    }

    /**
     * Recibe los datos de un formulario de "Nuevo Trámite" y
     * lo guarda en la base de datos.
     */
    public function guardarTramite() {
        try {
            // 1. Obtener los datos principales del trámite
            $nombreTramite = $_POST['nombre_tramite'] ?? '';
            $descripcionTramite = $_POST['descripcion_tramite'] ?? '';

            // 2. Obtener los arrays de requerimientos (así es como un form HTML los envía)
            $nombresReq = $_POST['req_nombre'] ?? [];
            $tiposDatoReq = $_POST['req_tipodato'] ?? [];
            $nombresDocReq = $_POST['req_docnombre'] ?? [];
            $tiposDocReq = $_POST['req_tipodoc'] ?? [];

            // 3. Re-formatear los datos en un array estructurado
            $requerimientosArray = [];
            foreach ($nombresReq as $i => $nombre) {
                if (!empty($nombre)) { // Solo procesar si el nombre no está vacío
                    $requerimientosArray[] = [
                        'NombreRequerimiento' => $nombre,
                        'TipoDato' => $tiposDatoReq[$i] ?? null,
                        'NombreDocumento' => $nombresDocReq[$i] ?: null, // Usar null si está vacío
                        'TipoDocumento' => $tiposDocReq[$i] ?: null  // Usar null si está vacío
                    ];
                }
            }

            // 4. Llamar al Modelo con los datos listos
            $resultado = $this->tramiteModel->gestionarTramiteCompleto(
                $nombreTramite,
                $descripcionTramite,
                $requerimientosArray
            );

            // 5. Devolver el resultado como JSON a la vista (para AJAX)
            header('Content-Type: application/json');
            echo json_encode($resultado);

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['Estatus' => 'Error', 'Mensaje' => $e->getMessage()]);
        }
    }

    /**
     * (NUEVA ACCIÓN)
     * Recibe los datos de un formulario de "Modificar Trámite" y
     * lo actualiza en la base de datos.
     */
    public function modificarTramite() {
        try {
            // 1. Obtener los datos principales del trámite
            $tipoTramiteID = $_POST['tipo_tramite_id'] ?? 0; // ID del trámite a editar
            $nombreTramite = $_POST['nombre_tramite'] ?? '';
            $descripcionTramite = $_POST['descripcion_tramite'] ?? '';

            if ($tipoTramiteID == 0) {
                throw new Exception('No se especificó el ID del trámite a modificar.');
            }

            // 2. Obtener los arrays de requerimientos
            $nombresReq = $_POST['req_nombre'] ?? [];
            $tiposDatoReq = $_POST['req_tipodato'] ?? [];
            $nombresDocReq = $_POST['req_docnombre'] ?? [];
            $tiposDocReq = $_POST['req_tipodoc'] ?? [];

            // 3. Re-formatear los datos
            $requerimientosArray = [];
            foreach ($nombresReq as $i => $nombre) {
                if (!empty($nombre)) {
                    $requerimientosArray[] = [
                        'NombreRequerimiento' => $nombre,
                        'TipoDato' => $tiposDatoReq[$i] ?? null,
                        'NombreDocumento' => $nombresDocReq[$i] ?: null,
                        'TipoDocumento' => $tiposDocReq[$i] ?: null
                    ];
                }
            }

            // 4. Llamar al Modelo con los datos listos
            $resultado = $this->tramiteModel->modificarTramiteCompleto(
                $tipoTramiteID,
                $nombreTramite,
                $descripcionTramite,
                $requerimientosArray
            );

            // 5. Devolver el resultado como JSON a la vista (para AJAX)
            header('Content-Type: application/json');
            echo json_encode($resultado);

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['Estatus' => 'Error', 'Mensaje' => $e->getMessage()]);
        }
    }
}
?>
