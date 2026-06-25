
<?php
class Conexion {
    private const SERVIDOR = 'localhost';
    private const NOMBRE_BD = 'jldesarr_gestion_calidad';
    private const USUARIO = 'jldesarr_sistema';
    private const PASSWORD = 'SecretariaMovilidad2025*';

    public static function Conectar() {
        $opciones = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION // Activar manejo de errores con excepciones
        );

        try {
            $conexion = new PDO(
                "mysql:host=" . self::SERVIDOR . ";dbname=" . self::NOMBRE_BD,
                self::USUARIO,
                self::PASSWORD,
                $opciones
            );
            return $conexion;
        } catch (PDOException $e) {
            die("Error de conexi¨®n a la base de datos: " . $e->getMessage());
        }
    }
}
?>
