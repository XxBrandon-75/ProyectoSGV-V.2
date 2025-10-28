<?php
class RolHelper
{
    const ROL_SUPERADMINISTRADOR = 'Superadministrador';
    const ROL_ADMINISTRADOR = 'Administrador';
    const ROL_COORDINADOR = 'Coordinador de Área';
    const ROL_VOLUNTARIO = 'Voluntario';

    /**
     * Verifica si el usuario tiene un rol específico
     * @param string $rol Nombre del rol a verificar
     * @return bool
     */
    public static function tieneRol($rol)
    {
        if (!isset($_SESSION['user']['rol'])) {
            return false;
        }
        return $_SESSION['user']['rol'] === $rol;
    }

    /**
     * Verifica si es Superadministrador
     * @return bool
     */
    public static function esSuperAdmin()
    {
        return self::tieneRol(self::ROL_SUPERADMINISTRADOR);
    }

    /**
     * Verifica si es Administrador
     * @return bool
     */
    public static function esAdmin()
    {
        return self::tieneRol(self::ROL_ADMINISTRADOR);
    }

    /**
     * Verifica si es Coordinador de Área
     * @return bool
     */
    public static function esCoordinador()
    {
        return self::tieneRol(self::ROL_COORDINADOR);
    }

    /**
     * Verifica si es Voluntario
     * @return bool
     */
    public static function esVoluntario()
    {
        return self::tieneRol(self::ROL_VOLUNTARIO);
    }

    /**
     * Verifica si el usuario puede ver el perfil de otro voluntario
     * @param array $voluntarioTarget Datos del voluntario objetivo
     * @return bool
     */
    public static function puedeVerPerfil($voluntarioTarget)
    {
        $usuario = $_SESSION['user'];

        // Superadmin puede ver todos
        if (self::esSuperAdmin()) {
            return true;
        }

        // Admin puede ver todos de su delegación
        if (self::esAdmin()) {
            $usuarioDelegacionID = $usuario['delegacionID'] ?? null;
            $targetDelegacionID = $voluntarioTarget['DelegacionID'] ?? null;
            return $usuarioDelegacionID && $usuarioDelegacionID == $targetDelegacionID;
        }

        // Coordinador puede ver todos de su área
        if (self::esCoordinador()) {
            $usuarioAreaID = $usuario['areaID'] ?? null;
            $targetAreaID = $voluntarioTarget['AreaID'] ?? null;
            return $usuarioAreaID && $usuarioAreaID == $targetAreaID;
        }

        // Voluntario solo puede ver su propio perfil
        if (self::esVoluntario()) {
            return $usuario['id'] == $voluntarioTarget['VoluntarioID'];
        }

        return false;
    }

    /**
     * Verifica si el usuario puede modificar el perfil de otro voluntario
     * @param array $voluntarioTarget Datos del voluntario objetivo
     * @return bool
     */
    public static function puedeModificarPerfil($voluntarioTarget)
    {
        $usuario = $_SESSION['user'];

        // Superadmin puede modificar todos
        if (self::esSuperAdmin()) {
            return true;
        }

        // Admin puede modificar todos de su delegación
        if (self::esAdmin()) {
            $usuarioDelegacionID = $usuario['delegacionID'] ?? null;
            $targetDelegacionID = $voluntarioTarget['DelegacionID'] ?? null;
            return $usuarioDelegacionID && $usuarioDelegacionID == $targetDelegacionID;
        }

        // Coordinador puede modificar todos de su área
        if (self::esCoordinador()) {
            $usuarioAreaID = $usuario['areaID'] ?? null;
            $targetAreaID = $voluntarioTarget['AreaID'] ?? null;
            return $usuarioAreaID && $usuarioAreaID == $targetAreaID;
        }

        // Voluntario solo puede modificar su propio perfil
        if (self::esVoluntario()) {
            return $usuario['id'] == $voluntarioTarget['VoluntarioID'];
        }

        return false;
    }

    /**
     * Verifica si el usuario puede editar roles
     * Solo Superadmin y Admin pueden editar roles
     * @return bool
     */
    public static function puedeEditarRoles()
    {
        return self::esSuperAdmin() || self::esAdmin();
    }

    /**
     * Verifica si puede ver la sección de coordinadores
     * @return bool
     */
    public static function puedeVerCoordinadores()
    {
        return self::esSuperAdmin() || self::esAdmin() || self::esCoordinador();
    }

    /**
     * Obtiene los campos que el usuario actual puede editar
     * @param bool $esPropioUsuario Si es true, es el perfil del usuario actual
     * @return array Array con los nombres de campos editables
     */
    public static function obtenerCamposEditables($esPropioUsuario = true)
    {
        // Los campos de Direcciones y ContactosEmergencia están en tablas separadas
        // y requieren su propio método de actualización
        $camposBasicos = [
            'Email',
            'TelefonoCelular',
            'TelefonoParticular',
            'TelefonoTrabajo',
            'GradoEstudios',
            'Profesion',
            'OcupacionActual',
            'EmpresaLabora',
            'Enfermedades',
            'Alergias',
            'autodescripcion',
            'EstadoCivilID'
        ];

        // Campos administrativos (datos personales y asignaciones)
        $camposAdmin = [
            'Nombres',
            'ApellidoPaterno',
            'ApellidoMaterno',
            'CURP',
            'FechaNacimiento',
            'Sexo',
            'EstadoCivilID',
            'GrupoSanguineoID',
            'AreaID',
            'DelegacionID',
            'RolID',

        ];

        // Campos académicos/profesionales adicionales
        $camposProfesionales = [
            'GradoEstudios',
            'Profesion'
        ];

        // Campos del tutor (para menores de edad)
        $camposTutor = [
            'TutorNombreCompleto',
            'TutorParentesco',
            'TutorTelefono'
        ];

        // SUPERADMIN puede editar TODO siempre (propio perfil o de otros)
        if (self::esSuperAdmin()) {
            return array_merge($camposBasicos, $camposAdmin, $camposProfesionales, $camposTutor);
        }

        // Si NO es su propio perfil
        if (!$esPropioUsuario) {
            // Admin puede editar todo de su delegación
            if (self::esAdmin()) {
                return array_merge($camposBasicos, $camposAdmin, $camposProfesionales, $camposTutor);
            }

            // Coordinador puede editar campos básicos de su área
            if (self::esCoordinador()) {
                return array_merge($camposBasicos, $camposProfesionales);
            }

            // Voluntario no puede editar perfiles de otros
            return [];
        }

        // Si ES su propio perfil: solo campos básicos (excepto Superadmin/Admin)
        if (self::esAdmin()) {
            return array_merge($camposBasicos, $camposAdmin);
        }

        return $camposBasicos;
    }

    /**
     * Obtiene el nombre del rol del usuario actual
     * @return string
     */
    public static function obtenerRolActual()
    {
        return $_SESSION['user']['rol'] ?? self::ROL_VOLUNTARIO;
    }

    /**
     * Verifica si tiene permisos de administración (Superadmin o Admin)
     * @return bool
     */
    public static function esAdministrativo()
    {
        return self::esSuperAdmin() || self::esAdmin();
    }
}
