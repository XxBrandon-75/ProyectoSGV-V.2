<?php
// /models/Voluntario.php
// En models/voluntario.php
//haber
require_once __DIR__ . '/../config/Database.php';
class Tramites {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    // ... (método obtenerDatosLogin sin cambios) ...
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
    public function AgregarTramite($Nombre, $Descripcion,$ReglaAntigueda,$ReglaAnioPares,$Activo) {
        try {
            $sql = " exec [AgregarTramite] @Nombre = :Nombre,
                    @Description = :Description,
                    @ReglaAntiguedad = :ReglaAntiguedad,
                    @ReglaAnioPares= :ReglaAnioPares,
                    @Activo = :Activo";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':Nombre', $Nombre, PDO::PARAM_INT);
            $stmt->bindParam(':Description', $Descripcion, PDO::PARAM_INT);
            $stmt->bindParam(':ReglaAntiguedad', $ReglaAntigueda, PDO::PARAM_INT);
            $stmt->bindParam(':ReglaAnioPares', $ReglaAnioPares, PDO::PARAM_INT);
            $stmt->bindParam(':Activo', $Activo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

            //return true; // Si no hay error, la ejecución fue exitosa
        } catch (PDOException $e) {
            error_log("Error en aprobarVoluntario: " . $e->getMessage());
            return false;
        }
    }
    public function SolicitarTramite($datos) {
        try {
            $arreglotramite
            $sql = "EXEC dbo.SolicitarTramite
                    @VoluntariosID = :VoluntariosID,
                    @TipoTramite = :TipoTramite,
                    @EstatusSolicitud = :EstatusSolicitud,
                    @AdminValidadorID = :AdminValidadorID,
                    @Observaciones = :Observaciones,
                    @AniosServicioReconocidos = :AniosServicioReconocidos,
                    @FechaIngresoVerificada = :FechaIngresoVerificada, 
                    @NombreEnCredencial = :NombreEnCredencial,
                    @NumeroCredencial = :NumeroCredencial,
                    @VigenciaCredencial = :VigenciaCredencial,     
                    @Puesto = :Puesto,
                    @SumaAsegurada = :SumaAsegurada,
                    @NumeroGuiaCIE = :NumeroGuiaCIE,
                    @ImporteSeguro = :ImporteSeguro,
                    @CategoriaAsociado = :CategoriaAsociado,
                    @SeAnexaDocumentos = :SeAnexaDocumentos, --este es un bit
                    @NombreArchivo = :NombreArchivo,
                    @RutaArchivo = :RutaArchivo";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':VoluntariosID', $datos['VoluntariosID'], PDO::PARAM_INT);
            $stmt->bindParam(':TipoTramite', $datos['TipoTramite'], PDO::PARAM_INT);
            $stmt->bindParam(':EstatusSolicitud', $datos['EstatusSolicitud'], PDO::PARAM_INT);
            $stmt->bindParam(':Observaciones', $datos['Observaciones'], PDO::PARAM_INT);
            $stmt->bindParam(':AniosServicioReconocidos',$datos['AniosServicioReconocidos'], PDO::PARAM_INT);
            $stmt->bindParam(':FechaIngresoVerificada', $datos['FechaIngresoVerificada'], PDO::PARAM_INT);
            $stmt->bindParam(':NombreEnCredencial', $datos['NombreEnCredencial'], PDO::PARAM_INT);
            $stmt->bindParam(':NumeroCredencial', $datos[], PDO::PARAM_INT);
            $stmt->bindParam(':VigenciaCredencial', $datos[], PDO::PARAM_INT);
            $stmt->bindParam(':Puesto', $datos[], PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

            //return true; // Si no hay error, la ejecución fue exitosa
        } catch (PDOException $e) {
            error_log("Error en aprobarVoluntario: " . $e->getMessage());
            return false;
        }
    }

    
}

?>