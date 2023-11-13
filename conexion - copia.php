
<?php


/**
 * Summary of getConnection
 * Crea un objeto PDO
 * @return PDO|null un objeto PDO si ha habido éxito creando la conexión, null en caso contrario
 */
function getConnection(): ?PDO
{
    $config = parse_ini_file("./db_settings.ini", true);
    $database = $config['database'];

    $con = null;
    $host = $database['host'];
    $db = $database["schema"];
    $user = $database["user"];
    $pass = $database["pass"];
    $dsn = "mysql:host=$host;dbname=$db";

    try {

        $con = new PDO($dsn, $user, $pass,  array(
            PDO::ATTR_PERSISTENT => $database["persistent"]
        ));

        //Esto no hace falta en versión PHP 8 y superiores: https://www.php.net/manual/en/pdo.error-handling.php
        //PDO::ERRMODE_EXCEPTION: As of PHP 8.0.0, this is the default mode.
        //$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $ex) {

        echo "Error en la conexión: mensaje: " . $ex->getMessage();
    }
    return $con;
}
