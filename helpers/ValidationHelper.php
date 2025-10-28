<?php
class ValidationHelper
{
    /**
     * Valida datos de contacto
     * @param array $datos Datos a validar
     * @return array ['valido' => bool, 'errores' => array]
     */
    public static function validarContacto($datos)
    {
        $errores = [];

        // Email
        if (isset($datos['Email'])) {
            if (empty($datos['Email'])) {
                $errores['Email'] = 'El email es requerido';
            } elseif (!filter_var($datos['Email'], FILTER_VALIDATE_EMAIL)) {
                $errores['Email'] = 'El email no es válido';
            }
        }

        // Teléfono celular
        if (isset($datos['TelefonoCelular'])) {
            if (!empty($datos['TelefonoCelular']) && !preg_match('/^[0-9]{10}$/', $datos['TelefonoCelular'])) {
                $errores['TelefonoCelular'] = 'El teléfono celular debe tener 10 dígitos';
            }
        }

        // Teléfono particular
        if (isset($datos['TelefonoParticular']) && !empty($datos['TelefonoParticular'])) {
            if (!preg_match('/^[0-9]{10}$/', $datos['TelefonoParticular'])) {
                $errores['TelefonoParticular'] = 'El teléfono particular debe tener 10 dígitos';
            }
        }

        // Teléfono trabajo
        if (isset($datos['TelefonoTrabajo']) && !empty($datos['TelefonoTrabajo'])) {
            if (!preg_match('/^[0-9]{10}$/', $datos['TelefonoTrabajo'])) {
                $errores['TelefonoTrabajo'] = 'El teléfono de trabajo debe tener 10 dígitos';
            }
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }

    /**
     * Valida datos de dirección
     * @param array $datos Datos a validar
     * @return array ['valido' => bool, 'errores' => array]
     */
    public static function validarDireccion($datos)
    {
        $errores = [];

        // Calle
        if (isset($datos['Calle']) && empty($datos['Calle'])) {
            $errores['Calle'] = 'La calle es requerida';
        }

        // Colonia
        if (isset($datos['Colonia']) && empty($datos['Colonia'])) {
            $errores['Colonia'] = 'La colonia es requerida';
        }

        // Código Postal
        if (isset($datos['CodigoPostal'])) {
            if (empty($datos['CodigoPostal'])) {
                $errores['CodigoPostal'] = 'El código postal es requerido';
            } elseif (!preg_match('/^[0-9]{5}$/', $datos['CodigoPostal'])) {
                $errores['CodigoPostal'] = 'El código postal debe tener 5 dígitos';
            }
        }

        // Estado
        if (isset($datos['EstadoID']) && empty($datos['EstadoID'])) {
            $errores['EstadoID'] = 'El estado es requerido';
        }

        // Ciudad
        if (isset($datos['CiudadID']) && empty($datos['CiudadID'])) {
            $errores['CiudadID'] = 'La ciudad es requerida';
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }

    /**
     * Valida datos personales
     * @param array $datos Datos a validar
     * @return array ['valido' => bool, 'errores' => array]
     */
    public static function validarDatosPersonales($datos)
    {
        $errores = [];

        // Nombres
        if (isset($datos['Nombres'])) {
            if (empty($datos['Nombres'])) {
                $errores['Nombres'] = 'El nombre es requerido';
            } elseif (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $datos['Nombres'])) {
                $errores['Nombres'] = 'El nombre solo puede contener letras y espacios';
            }
        }

        // Apellido Paterno
        if (isset($datos['ApellidoPaterno'])) {
            if (empty($datos['ApellidoPaterno'])) {
                $errores['ApellidoPaterno'] = 'El apellido paterno es requerido';
            } elseif (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $datos['ApellidoPaterno'])) {
                $errores['ApellidoPaterno'] = 'El apellido paterno solo puede contener letras y espacios';
            }
        }

        // CURP
        if (isset($datos['CURP'])) {
            if (!empty($datos['CURP'])) {
                $curpUpper = strtoupper($datos['CURP']);
                if (!preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}$/', $curpUpper)) {
                    $errores['CURP'] = 'El CURP no tiene un formato válido';
                }
            }
        }

        // Fecha de Nacimiento
        if (isset($datos['FechaNacimiento'])) {
            if (empty($datos['FechaNacimiento'])) {
                $errores['FechaNacimiento'] = 'La fecha de nacimiento es requerida';
            } else {
                $fecha = \DateTime::createFromFormat('Y-m-d', $datos['FechaNacimiento']);
                if (!$fecha) {
                    $errores['FechaNacimiento'] = 'La fecha de nacimiento no es válida';
                } else {
                    // Verificar que no sea una fecha futura
                    $hoy = new \DateTime();
                    if ($fecha > $hoy) {
                        $errores['FechaNacimiento'] = 'La fecha de nacimiento no puede ser futura';
                    }

                    // Verificar edad mínima (ej: 12 años)
                    $edad = $hoy->diff($fecha)->y;
                    if ($edad < 12) {
                        $errores['FechaNacimiento'] = 'Debe tener al menos 12 años para registrarse';
                    }
                }
            }
        }

        // Sexo
        if (isset($datos['Sexo'])) {
            if (!in_array($datos['Sexo'], ['M', 'F'])) {
                $errores['Sexo'] = 'El sexo debe ser M o F';
            }
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }

    /**
     * Valida contacto de emergencia
     * @param array $datos Datos a validar
     * @return array ['valido' => bool, 'errores' => array]
     */
    public static function validarContactoEmergencia($datos)
    {
        $errores = [];

        // Nombre
        if (isset($datos['ContactoEmergenciaNombre']) && empty($datos['ContactoEmergenciaNombre'])) {
            $errores['ContactoEmergenciaNombre'] = 'El nombre del contacto de emergencia es requerido';
        }

        // Parentesco
        if (isset($datos['ContactoEmergenciaParentesco']) && empty($datos['ContactoEmergenciaParentesco'])) {
            $errores['ContactoEmergenciaParentesco'] = 'El parentesco es requerido';
        }

        // Teléfono
        if (isset($datos['ContactoEmergenciaTelefono'])) {
            if (empty($datos['ContactoEmergenciaTelefono'])) {
                $errores['ContactoEmergenciaTelefono'] = 'El teléfono es requerido';
            } elseif (!preg_match('/^[0-9]{10}$/', $datos['ContactoEmergenciaTelefono'])) {
                $errores['ContactoEmergenciaTelefono'] = 'El teléfono debe tener 10 dígitos';
            }
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }

    /**
     * Sanitiza un array de datos
     * @param array $datos Datos a sanitizar
     * @return array Datos sanitizados
     */
    public static function sanitizarDatos($datos)
    {
        $datosSanitizados = [];

        foreach ($datos as $clave => $valor) {
            if (is_array($valor)) {
                $datosSanitizados[$clave] = self::sanitizarDatos($valor);
            } else {
                // Eliminar espacios al inicio y final
                $valor = trim($valor);
                // Convertir a NULL si está vacío
                if ($valor === '') {
                    $datosSanitizados[$clave] = null;
                } else {
                    $datosSanitizados[$clave] = $valor;
                }
            }
        }

        return $datosSanitizados;
    }

    /**
     * Valida que un ID numérico sea válido
     * @param mixed $id ID a validar
     * @return bool
     */
    public static function validarID($id)
    {
        return is_numeric($id) && $id > 0;
    }
}
