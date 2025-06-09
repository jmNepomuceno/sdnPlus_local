<?php

function connect_to_db(string $callback, mixed $params = null, string $errcallback = null)
{
    return connect_to_db_by_ini(CONFIG_FILE, "DBConn", $callback, $params, $errcallback);
}

function connect_to_db_by_ini(string $file, string $dbserver, string $callback, mixed $params = null, string $errcallback = null)
{
    $ini_arr = parse_ini_file($file, true);

    $dbcfg_arr = $ini_arr[$dbserver];

    $hostname = $dbcfg_arr["hostname"];
    $port = $dbcfg_arr["port"];
    $dbname = $dbcfg_arr["dbname"];
    $username = $dbcfg_arr["username"];
    $password = $dbcfg_arr["password"];

    try {
        $pdo = connect_to_db_inner($hostname, $port, $dbname, $username, $password, $callback, $params, $errcallback);
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new PDOException("Database connection failed: " . $e->getMessage());
        return false;
    }
}

function connect_to_db_inner(string $hostname, string $port, string $dbname, string $username, string $password, string $callback, mixed $params = null, string $errcallback = null)
{
    try
    {
        $pdo = new PDO("sqlsrv:Server=tcp:$hostname,$port;Database=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $result = null;

        if (is_callable($callback))
        {
            if ($params != null)
            {
                $result = call_user_func($callback, $params, $pdo);
            }
            else
            {
                $result = call_user_func($callback, $pdo);
            }
        }

        $pdo = null;

        unset($pdo);

        return $result;
    }
    catch (PDOException $e)
    {
        if ($errcallback != null && is_callable($errcallback))
        {
            call_user_func($errcallback, $e);
        }
        return null;
    }
}

function execute_query(string $sql, array $bindings = [], array $options = [], string $errcallback = null)
{
    try {
        $params = array(
            "sql" => $sql,
            "bindings" => $bindings,
            "options" => $options
        );

        $result = connect_to_db("execute_query_inner", $params, $errcallback);

        return $result;
    } catch (PDOException $e) {
        throw new PDOException("Error executing query: " . $e->getMessage() . " (SQL: $sql)", (int) $e->getCode());
    }
}


function execute_query_withcallback(string $sql, callable $callback, array $options = [], $errcallback = null)
{
    if (is_callable($callback))
    {
        $stmt = execute_query($sql, $options, $errcallback);

        if ($stmt != null)
        {
            call_user_func($callback, $stmt);

            $stmt = null;
            unset($stmt);
        }
    }
}

function execute_query_inner($params, $pdo)
{
    try {
        $sql = $params["sql"];
        $bindings = $params["bindings"];

        $stmt = $pdo->prepare($sql, $params["options"]);
        $stmt->execute($bindings);

        return $stmt;
    } catch (PDOException $e) {

        error_log("Error executing query: " . $e->getMessage() . " (SQL: $sql)");

        throw new PDOException("Error executing query: " . $e->getMessage() . " (SQL: $sql)", (int) $e->getCode());
    }
}

?>