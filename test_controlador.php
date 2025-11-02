<?php
// test_controlador.php
// --- CONFIGURACIÓN ---
// Asegúrate de que las rutas a tus archivos sean correctas
require_once __DIR__ . '/config/database.php'; // CORREGIDO: 'database.php' en minúscula
require_once __DIR__ . '/models/tramitesModels.php'; // CORREGIDO: 'tramitesModels.php'
require_once __DIR__ . '/controllers/tramitesController.php'; // CORREGIDO: 'tramitesController.php'

// Silenciar la salida de 'header()' para evitar errores en la línea de comandos
// (Opcional, pero útil si pruebas fuera de un navegador)
if (headers_sent() === false) {
    ob_start();
}

echo "========================================\n";
echo "INICIANDO PRUEBAS DEL TRAMITECONTROLLER\n";
echo "========================================\n\n";

// Instanciamos el controlador. Esto también conecta a la BD.
try {
    // CORREGIDO: El nombre de tu clase en el controlador (probablemente es 'TramiteController' o 'tramitesController')
    // Asegúrate de que coincida con el nombre de la clase DENTRO de 'tramitesController.php'
    $controller = new TramiteController(); 
} catch (Exception $e) {
    echo "Error fatal al instanciar el controlador: " . $e->getMessage();
    die();
}


// --- PRUEBA 1: GUARDAR UN NUEVO TRÁMITE ---
echo "--- Prueba 1: guardarTramite() ---\n";

// 1. Simular los datos que enviaría el formulario
$_POST = [
    'nombre_tramite' => 'Trámite de Prueba ' . rand(100, 999),
    'descripcion_tramite' => 'Descripción de prueba para el trámite.',
    
    // Simular la lista de requerimientos
    'req_nombre' => [
        'Prueba de Requerimiento 1 (Texto)',
        'Prueba de Requerimiento 2 (Archivo)'
    ],
    'req_tipodato' => [
        'Texto',
        'Archivo'
    ],
    'req_docnombre' => [
        '', // El primero no tiene documento
        'Documento de Prueba'
    ],
    'req_tipodoc' => [
        '',
        'PDF' // Asumiendo que tu CHECK lo permite
    ]
];

// 2. Llamar al método
$controller->guardarTramite();
echo "\n\n";


// --- PRUEBA 2: OBTENER REQUERIMIENTOS ---
echo "--- Prueba 2: obtenerRequerimientos() (para Trámite ID 5) ---\n";

// 1. Simular la petición GET
$_GET['id'] = 5; // ID del trámite que SÍ existe (ej. Perseverancia)

// 2. Llamar al método
$controller->obtenerRequerimientos();
echo "\n\n";


// --- PRUEBA 3: INICIAR UNA SOLICITUD ---
echo "--- Prueba 3: iniciar() (Solicitar Trámite ID 5) ---\n";

// 1. Simular los datos POST
$_POST = [
    'voluntarioID' => 1, // ID de un voluntario que exista
    'tipoTramiteID' => 5, // ID del trámite a solicitar
    'observaciones' => 'Prueba de inicio de solicitud.'
];

// 2. Llamar al método
$controller->iniciar();
echo "\n\n";
// NOTA: Este test devuelve un JSON con el 'SolicitudID'. 
// Para la siguiente prueba, deberías usar ese ID.
// Por simplicidad, asumiremos que ya conoces los DatoSolicitudID.


// --- PRUEBA 4: GUARDAR DATOS DE UNA SOLICITUD ---
echo "--- Prueba 4: guardar() (Llenar Solicitud) ---\n";

// 1. Simular los datos del formulario (ej. para SolicitudID 1)
// DEBES cambiar estos DatoSolicitudID por los que se crearon en la Prueba 3
$_POST = [
    'DatoSolicitudID' => [1, 2, 3, 4, 5], // IDs de DatoSolicitud que SÍ existan
    'DatoTexto' => [
        'Dato de texto para el ID 1',
        null,
        null,
        null,
        null
    ],
    'DatoNumero' => [null, null, null, null, null],
    'DatoFecha' => [null, null, null, null, null],
    'NombreArchivo' => [
        null,
        'mi_archivo_1.pdf',
        'mi_archivo_2.jpg',
        'mi_archivo_3.docx',
        'mi_archivo_4.pdf'
    ],
    'RutaArchivo' => [
        null,
        'ruta/simulada/1.pdf',
        'ruta/simulada/2.jpg',
        'ruta/simulada/3.docx',
        'ruta/simulada/4.pdf'
    ],
    'nuevoEstatus' => 'En Revisión'
];

// 2. Llamar al método
$controller->guardar();
echo "\n\n";


echo "========================================\n";
echo "PRUEBAS FINALIZADAS\n";
echo "========================================\n";

if (headers_sent() === false) {
    ob_end_flush();
}
?>

