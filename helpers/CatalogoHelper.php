<?php

require_once __DIR__ . '/../config/database.php';

class CatalogoHelper
{
    /**
     * @param string $tabla Nombre de la tabla
     * @param string $campoID Nombre del campo ID
     * @param string $campoNombre Nombre del campo a mostrar
     * @param string|null $orderBy Campo por el cual ordenar (opcional, por defecto usa $campoNombre)
     * @param string|array|null $lastValues Valor(es) que debe(n) aparecer al final (opcional)
     * @return array Array con los registros
     */
    public static function obtenerCatalogo($tabla, $campoID, $campoNombre, $orderBy = null, $lastValues = null)
    {
        try {
            $database = Database::getInstance();
            $conn = $database->getConnection();

            // Si hay valores que deben ir al final, usar CASE para ordenar
            if ($lastValues !== null) {
                $valuesArray = is_array($lastValues) ? $lastValues : [$lastValues];

                $caseParts = [];
                foreach ($valuesArray as $index => $value) {
                    $caseParts[] = "WHEN $campoNombre = :lastValue$index THEN " . ($index + 1);
                }
                $caseStatement = "CASE " . implode(" ", $caseParts) . " ELSE 0 END";

                $sql = "SELECT $campoID, $campoNombre FROM $tabla 
                        ORDER BY $caseStatement, $campoNombre";
                $stmt = $conn->prepare($sql);

                foreach ($valuesArray as $index => $value) {
                    $stmt->bindValue(":lastValue$index", $value, PDO::PARAM_STR);
                }
            } else {
                $orderByClause = $orderBy ?? $campoNombre;
                $sql = "SELECT $campoID, $campoNombre FROM $tabla ORDER BY $orderByClause";
                $stmt = $conn->prepare($sql);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener catálogo $tabla: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene grupos sanguíneos
     * @return array
     */
    public static function obtenerGruposSanguineos()
    {
        return self::obtenerCatalogo('CatGruposSanguineos', 'GrupoSanguineoID', 'Nombre', null, 'No sabe');
    }

    /**
     * Obtiene estados civiles
     * @return array
     */
    public static function obtenerEstadosCiviles()
    {
        return self::obtenerCatalogo('CatEstadosCiviles', 'EstadoCivilID', 'Nombre');
    }

    /**
     * Obtiene ciudades
     * @return array
     */
    public static function obtenerCiudades()
    {
        return self::obtenerCatalogo('CatCiudades', 'CiudadID', 'Nombre');
    }

    /**
     * Obtiene estados
     * @return array
     */
    public static function obtenerEstados()
    {
        return self::obtenerCatalogo('CatEstados', 'EstadoID', 'Nombre');
    }

    /**
     * Obtiene áreas
     * @return array
     */
    public static function obtenerAreas()
    {
        return self::obtenerCatalogo('Areas', 'AreaID', 'Nombre');
    }

    /**
     * Obtiene delegaciones
     * @return array
     */
    public static function obtenerDelegaciones()
    {
        return self::obtenerCatalogo('Delegaciones', 'DelegacionID', 'Nombre');
    }

    /**
     * Obtiene roles
     * @return array
     */
    public static function obtenerRoles()
    {
        return self::obtenerCatalogo('Roles', 'RolID', 'Nombre');
    }
}
