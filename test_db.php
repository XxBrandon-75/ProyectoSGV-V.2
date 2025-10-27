<?php
// Asegúrate de que tu clase Database esté disponible (si no está en este archivo)
require_once 'config/database.php'; 

echo "Intentando conexión...\n";

try {
    // Intenta obtener la instancia de la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Si llegamos hasta aquí sin excepción, la conexión tuvo éxito.
    // Opcional: Ejecutar una consulta simple para doble verificación
    $stmt = $conn->query("SELECT GETDATE() AS CurrentDateTime");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Conexión a SQL Server exitosa.\n";
    echo "🕒 Fecha y hora actual del servidor: " . $result['CurrentDateTime'] . "\n";
    
} catch (Exception $e) {
    // Si hay un error, el bloque catch lo capturará.
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    
    // Muestra detalles importantes si el error es de PDO
    if ($e instanceof PDOException) {
        echo "PDO Error Code: " . $e->getCode() . "\n";
    }
}
?>