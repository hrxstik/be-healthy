<?php
$host = 'localhost';
$db = 'be-healthy';
$user = 'root';
$password = '';

try {
    $connection = new mysqli($host, $user, $password, $db);

    if ($connection->connect_error) {
        throw new mysqli_sql_exception('Не удалось подключиться к базе данных: ' . $connection->connect_error);
    }

} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    ?> <p>Не удалось подключиться к базе данных</p> <?php
    exit;
}