<?php

// class MsSqlConnection {
//     private $host;
//     private $port;
//     private $dbname;
//     private $username;
//     private $password;
//     private $pdo;

//     public function __construct() {
//         // Read configuration from INI file
//         $config = parse_ini_file('config.ini', true);

//         // MS SQL Server configuration
//         $this->host = $config['database']['MS_SERVER'];
//         $this->port = $config['database']['MS_PORT'];
//         $this->dbname = $config['database']['MS_DBNAME'];
//         $this->username = $config['database']['MS_USERNAME'];
//         $this->password = $config['database']['MS_PASSWORD'];
//     }

//     public function connect() {
//         $dsn = "sqlsrv:Server={$this->host},{$this->port};Database={$this->dbname}";
//         $options = [
//             PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
//             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//             PDO::SQLSRV_ATTR_ENCODING    => PDO::SQLSRV_ENCODING_UTF8,
//         ];

//         try {
//             $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
//             return true; 
//         } catch (PDOException $e) {
//             return false; 
//         }
//     }

//     public function testConnection() {
//         $success = $this->connect();
//         if ($success) {
//             echo "Connected to MS SQL Server database successfully!";
//         } else {
//             echo "Failed to connect to MS SQL Server.";
//         }
//         ob_flush();
//     }

//     public function getPdo() {
//         return $this->pdo;
//     }

// }

class MySqlConnection {
    private $host;
    private $username;
    private $password;
    private $database;
    private $pdo;

    public function __construct() {
        // Read configuration from INI file
        $config = parse_ini_file('config.ini', true);

        // MySQL Server configuration
        $this->host = $config['database']['MYSQL_SERVER'];
        $this->username = $config['database']['MYSQL_USERNAME'];
        $this->password = $config['database']['MYSQL_PASSWORD'];
        $this->database = $config['database']['MYSQL_DBNAME'];
    }

    public function connect() {
        $dsn = "mysql:host={$this->host};dbname={$this->database}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
        ];

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            return true; 
        } catch (PDOException $e) {
            return false; 
        }
    }

    public function testConnection() {
        $success = $this->connect();
        if ($success) {
            echo "Connected to MySQL Server database successfully!";
        } else {
            echo "Failed to connect to MySQL Server.";
        }
        ob_flush();
    }

    public function getPdo() {
        return $this->pdo;
    }

}

?>
