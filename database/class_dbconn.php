<?php

class MsSqlConnection {
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $pdo;

    public function __construct() {
        $config = parse_ini_file('config.ini', true);

        $this->host = $config['database']['hostname'];
        $this->port = $config['database']['port'];
        $this->dbname = $config['database']['dbname'];
        $this->username = $config['database']['username'];
        $this->password = $config['database']['password'];
    }

    public function connect() {
        $dsn = "sqlsrv:Server={$this->host},{$this->port};database={$this->dbname}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::SQLSRV_ATTR_ENCODING    => PDO::SQLSRV_ENCODING_UTF8,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);        
        } catch (PDOException $e) {
            exit("Failed to connect to MS SQL Server: " . $e->getMessage());
        }
        
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getPdo() {
        if (!$this->pdo) {
            $this->connect();
        }
        return $this->pdo;
    }
}

$msSqlConnection = new MsSqlConnection();

?>