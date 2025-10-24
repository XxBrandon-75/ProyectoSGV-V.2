<?php

// Incluimos el archivo de configuración una sola vez


class Database {
    private static $instance = null;
    private $conn;

    // Las credenciales ahora se leen desde el archivo config.php
    private $serverName = 'tcp:serverbasedatos.database.windows.net'; 
    private $databaseName = 'base';
    private $uid = 'adminsql';
    private $pwd = 'SQLServer.';

    private function __construct() {
        try {
            $this->conn = new PDO("sqlsrv:server=$this->serverName;database=$this->databaseName", $this->uid, $this->pwd);
            
            // Configurar PDO para que lance excepciones en caso de error
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Conexión exitosa a la base de datos.";    
            // Se eliminó el "echo" de conexión exitosa. ¡Es una buena práctica!
            
        } catch (PDOException $e) {
            // En lugar de "die()", lanzamos la excepción para un manejo más flexible
            // o podrías registrar el error en un log y mostrar un mensaje genérico.
            throw new Exception("Error al conectar con la base de datos: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>