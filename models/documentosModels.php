<?php

require_once __DIR__ . '/../config/Database.php';
class Tramites {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function obtenerDatosLogin($email) {
        try {
            $sql = "EXEC sp_ObtenerDatosLogin @Email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerDatosLogin: " . $e->getMessage());
            return false;
        }
    }
    public function AgregarDocumentoVoluntario($Paraque, $Nombre,$VoluntarioID,$RutaArchivo) {
        try {
            $sql = "exec [AgregarDocumentoVoluntario]
                @ParaQue = :ParaQue
                ,@Nombre = :Nombre
                ,@VoluntarioID = :VoluntarioID
                ,@RutaArchivo = :RutaArchivo";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':ParaQue', $Paraque, PDO::PARAM_INT);
            $stmt->bindParam(':Nombre', $Nombre, PDO::PARAM_INT);
            $stmt->bindParam(':VoluntarioID', $VoluntarioID, PDO::PARAM_INT);
            $stmt->bindParam(':RutaArchivo', $RutaArchivo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

            //return true; // Si no hay error, la ejecución fue exitosa
        } catch (PDOException $e) {
            error_log("Error en agregar documentos" . $e->getMessage());
            return false;
        }
    }
}
?>