<?php

require_once __DIR__ . '/../config/Database.php';
class Tramites
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function obtenerDatosLogin($email)
    {
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
    public function AgregarDocumentoVoluntario($Paraque, $Nombre, $VoluntarioID, $RutaArchivo)
    {
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

/**
 * Modelo para gestión de documentos usando procedimientos almacenados existentes
 */
class DocumentoModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los documentos del expediente (para admin)
     */
    public function obtenerTodos()
    {
        try {
            $sql = "SELECT 
                        cd.DocumentoID,
                        cd.Nombre AS NombreArchivo,
                        cd.TipoDocumento AS Seccion,
                        cd.TipoVer AS TipoDocumento,
                        cd.RutaPlantilla,
                        vd.VoluntarioDocumentoID,
                        vd.VoluntarioID,
                        vd.NombreArchivo AS ArchivoSubido,
                        vd.RutaArchivo,
                        vd.EstatusValidacion AS Estatus,
                        vd.FechaSubida,
                        CONCAT(v.Nombres, ' ', v.ApellidoPaterno, ' ', ISNULL(v.ApellidoMaterno, '')) AS NombreVoluntario
                    FROM dbo.CatDocumentos AS cd
                    LEFT JOIN dbo.VoluntarioDocumento AS vd ON cd.DocumentoID = vd.DocumentoID
                    LEFT JOIN dbo.Voluntarios AS v ON vd.VoluntarioID = v.VoluntarioID
                    WHERE cd.TipoDocumento = 'Expediente'
                    ORDER BY 
                        CASE WHEN vd.EstatusValidacion = 'Pendiente' THEN 1
                             WHEN vd.EstatusValidacion = 'Validado' THEN 2
                             WHEN vd.EstatusValidacion = 'Rechazado' THEN 3
                             ELSE 4 END,
                        vd.FechaSubida DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener documentos de un voluntario específico
     */
    public function obtenerPorVoluntario($voluntarioId)
    {
        try {
            $sql = "SELECT 
                        cd.DocumentoID,
                        cd.Nombre AS NombreArchivo,
                        cd.TipoDocumento AS Seccion,
                        cd.TipoVer AS TipoDocumento,
                        cd.RutaPlantilla,
                        vd.VoluntarioDocumentoID,
                        vd.NombreArchivo AS ArchivoSubido,
                        vd.RutaArchivo,
                        vd.EstatusValidacion AS Estatus,
                        vd.FechaSubida
                    FROM dbo.CatDocumentos AS cd
                    LEFT JOIN dbo.VoluntarioDocumento AS vd 
                        ON cd.DocumentoID = vd.DocumentoID 
                        AND vd.VoluntarioID = :voluntarioId
                    WHERE cd.TipoDocumento = 'Expediente'
                    ORDER BY cd.Nombre";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':voluntarioId', $voluntarioId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorVoluntario: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Obtener un documento específico por VoluntarioDocumentoID
     */
    public function obtenerPorId($voluntarioDocId)
    {
        try {
            $sql = "SELECT 
                        vd.*,
                        cd.Nombre AS NombreDocumento,
                        cd.TipoVer AS TipoDocumento,
                        cd.RutaPlantilla,
                        CONCAT(v.Nombres, ' ', v.ApellidoPaterno, ' ', ISNULL(v.ApellidoMaterno, '')) AS NombreVoluntario
                    FROM dbo.VoluntarioDocumento AS vd
                    INNER JOIN dbo.CatDocumentos AS cd ON vd.DocumentoID = cd.DocumentoID
                    LEFT JOIN dbo.Voluntarios AS v ON vd.VoluntarioID = v.VoluntarioID
                    WHERE vd.VoluntarioDocumentoID = :voluntarioDocId";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':voluntarioDocId', $voluntarioDocId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Agregar una plantilla o tipo de documento (solo admin)
     * TipoVer: 'Plantilla' (con archivo) o 'Solo Subir' (sin plantilla)
     */
    public function agregarTipoDocumento($nombreDocumento, $tipoVer, $rutaPlantilla = null)
    {
        try {
            $sql = "EXEC dbo.AgregarExpediente
                        @VoluntarioID = NULL,
                        @NombreDocumento = :nombreDocumento,
                        @Tipo = 'Expediente',
                        @NombreArchivoSubido = NULL,
                        @RutaArchivoSubido = NULL,
                        @TipoVer = :tipoVer,
                        @RutaPlantilla = :rutaPlantilla";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':nombreDocumento', $nombreDocumento, PDO::PARAM_STR);
            $stmt->bindParam(':tipoVer', $tipoVer, PDO::PARAM_STR);

            // Manejo especial para NULL
            if ($rutaPlantilla === null) {
                $stmt->bindValue(':rutaPlantilla', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':rutaPlantilla', $rutaPlantilla, PDO::PARAM_STR);
            }

            $result = $stmt->execute();

            // Limpiar resultados del stored procedure
            $stmt->closeCursor();

            return $result;
        } catch (PDOException $e) {
            error_log("Error en agregarTipoDocumento: " . $e->getMessage());
            error_log("SQL: " . ($sql ?? 'N/A'));
            return false;
        }
    }

    /**
     * Subir documento por voluntario
     */
    public function subirDocumentoVoluntario($voluntarioId, $nombreDocumento, $nombreArchivo, $rutaArchivo)
    {
        try {
            // Primero verificar si existe el documento en el catálogo
            $sqlCheck = "SELECT DocumentoID FROM dbo.CatDocumentos WHERE Nombre = :nombreDocumento AND TipoDocumento = 'Expediente'";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->bindParam(':nombreDocumento', $nombreDocumento, PDO::PARAM_STR);
            $stmtCheck->execute();
            $documento = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$documento) {
                error_log("ERROR: No se encontró el documento '$nombreDocumento' en el catálogo");
                return false;
            }

            $documentoID = $documento['DocumentoID'];

            // Insertar directamente en VoluntarioDocumento
            $sql = "INSERT INTO dbo.VoluntarioDocumento 
                    (VoluntarioID, DocumentoID, NombreArchivo, RutaArchivo, EstatusValidacion, FechaSubida)
                    VALUES 
                    (:voluntarioId, :documentoId, :nombreArchivo, :rutaArchivo, 'Pendiente', GETDATE())";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':voluntarioId', $voluntarioId, PDO::PARAM_INT);
            $stmt->bindParam(':documentoId', $documentoID, PDO::PARAM_INT);
            $stmt->bindParam(':nombreArchivo', $nombreArchivo, PDO::PARAM_STR);
            $stmt->bindParam(':rutaArchivo', $rutaArchivo, PDO::PARAM_STR);

            $result = $stmt->execute();

            return $result;
        } catch (PDOException $e) {
            error_log("Error en subirDocumentoVoluntario: " . $e->getMessage());
            error_log("SQL: " . ($sql ?? 'N/A'));
            return false;
        }
    }

    /**
     * Aprobar un documento
     */
    public function aprobar($voluntarioDocId, $aprobadoPor)
    {
        try {
            $sql = "UPDATE dbo.VoluntarioDocumento 
                    SET EstatusValidacion = 'Validado',
                        AdminValidadorID = :aprobadoPor
                    WHERE VoluntarioDocumentoID = :voluntarioDocId";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':voluntarioDocId', $voluntarioDocId, PDO::PARAM_INT);
            $stmt->bindParam(':aprobadoPor', $aprobadoPor, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en aprobar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rechazar un documento
     */
    public function rechazar($voluntarioDocId, $motivo, $rechazadoPor)
    {
        try {
            $sql = "UPDATE dbo.VoluntarioDocumento 
                    SET EstatusValidacion = 'Rechazado',
                        AdminValidadorID = :rechazadoPor
                    WHERE VoluntarioDocumentoID = :voluntarioDocId";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':voluntarioDocId', $voluntarioDocId, PDO::PARAM_INT);
            $stmt->bindParam(':rechazadoPor', $rechazadoPor, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en rechazar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un documento de voluntario
     */
    public function eliminar($voluntarioDocId)
    {
        try {
            $sql = "DELETE FROM dbo.VoluntarioDocumento WHERE VoluntarioDocumentoID = :voluntarioDocId";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':voluntarioDocId', $voluntarioDocId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en eliminar: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Eliminar tipo de documento del catálogo (solo admin)
     */
    public function eliminarTipoDocumento($documentoId)
    {
        try {
            $sql = "DELETE FROM dbo.CatDocumentos WHERE DocumentoID = :documentoId";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':documentoId', $documentoId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en eliminarTipoDocumento: " . $e->getMessage());
            return false;
        }
    }
}
