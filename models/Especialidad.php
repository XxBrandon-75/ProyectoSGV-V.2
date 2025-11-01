<?php
class Especialidad
{
    private $db;

    public function __construct()
    {
        require_once __DIR__ . '/../config/database.php';
        $this->db = Database::getInstance()->getConnection();
    }

     public function tieneEspecialidad($voluntarioID, $nombreEspecialidad)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM VoluntarioEspecialidades ve
                INNER JOIN Especialidades e ON ve.EspecialidadID = e.EspecialidadID
                WHERE ve.VoluntarioID = :voluntarioID 
                AND e.Nombre = :nombreEspecialidad
            ");
            
            $stmt->bindParam(':voluntarioID', $voluntarioID, PDO::PARAM_INT);
            $stmt->bindParam(':nombreEspecialidad', $nombreEspecialidad, PDO::PARAM_STR);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en tieneEspecialidad: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Agregar una nueva especialidad para un voluntario
     * Ejecuta el procedimiento almacenado AgregarEspecialidad
     */
    public function agregarEspecialidad($voluntarioID, $nombreEspecialidad, $autodescripcion, $nombreDocumento, $archivoRuta)
    {
        try {
            $stmt = $this->db->prepare("EXEC AgregarEspecialidad 
                @VoluntarioEspecialidad = :voluntarioID, 
                @NombreEspecialidad = :nombreEspecialidad, 
                @Autodescripcion = :autodescripcion, 
                @NombreDocumento = :nombreDocumento, 
                @ArchivoRuta = :archivoRuta
            ");

            $stmt->bindParam(':voluntarioID', $voluntarioID, PDO::PARAM_INT);
            $stmt->bindParam(':nombreEspecialidad', $nombreEspecialidad, PDO::PARAM_STR);
            $stmt->bindParam(':autodescripcion', $autodescripcion, PDO::PARAM_STR);
            $stmt->bindParam(':nombreDocumento', $nombreDocumento, PDO::PARAM_STR);
            $stmt->bindParam(':archivoRuta', $archivoRuta, PDO::PARAM_STR);

            $result = $stmt->execute();
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error en agregarEspecialidad: " . $e->getMessage());
            throw new Exception("Error al agregar la especialidad: " . $e->getMessage());
        }
    }

    /**
     * Obtener las especialidades de un voluntario
     * Ejecuta el procedimiento almacenado VerEspecialidades
     */
    public function verEspecialidades($voluntarioID)
    {
        try {
            $stmt = $this->db->prepare("EXEC VerEspecialidades @VoluntarioID = :voluntarioID");
            $stmt->bindParam(':voluntarioID', $voluntarioID, PDO::PARAM_INT);
            $stmt->execute();

            $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $especialidades;
        } catch (PDOException $e) {
            error_log("Error en verEspecialidades: " . $e->getMessage());
            throw new Exception("Error al obtener las especialidades: " . $e->getMessage());
        }
    }

    /**
     * Obtener lista de especialidades disponibles en el catálogo
     * Este método podría consultar la tabla Especialidades si es necesario
     */
    public function obtenerEspecialidadesDisponibles()
    {
        try {
            $stmt = $this->db->query("SELECT EspecialidadID, Nombre FROM Especialidades ORDER BY Nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEspecialidadesDisponibles: " . $e->getMessage());
            // Si falla, devolver las especialidades hardcoded
            return [
                ['EspecialidadID' => 1, 'Nombre' => 'Tutorial'],
                ['EspecialidadID' => 2, 'Nombre' => 'Primeros Auxilios'],
                ['EspecialidadID' => 3, 'Nombre' => 'Rescate']
            ];
        }
    }
}