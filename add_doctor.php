<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (!empty($_GET)) {
    include __DIR__ . '/config.php';
    /**
     * @global mysqli $connection
     */

    $errors = [];
    $emptyFieldsErrors = [
        'full_name' => 'ФИО',
        'specialty' => 'Email',
    ];
    foreach ($emptyFieldsErrors as $fieldName => $field) {
        if (!isset($_GET[$fieldName]) || $_GET[$fieldName] == '' ) {
            $errors[] = 'Поле ' . $field . ' не заполнено';
        }
    }
    if (!empty($errors)) {
        foreach ($errors as $error) {
            ?> <p><?= $error; ?></p> <?php
        }
        die;
    }

    $full_name = htmlspecialchars($_GET['full_name']);
    $specialty = htmlspecialchars($_GET['specialty']);

    include __DIR__ . '/error_messages.php';
    if (!preg_match('/^([А-ЯA-Z]|[А-ЯA-Z][\'а-яa-z]{1,}|[А-ЯA-Z][\'а-яa-z]{1,}-([А-ЯA-Z][\'а-яa-z]{1,}|(оглы)|(кызы)))\s+[А-ЯA-Z][\'а-яa-z]{1,}(\s+[А-ЯA-Z][\'а-яa-z]{1,})?$/u', trim($full_name))) {
        $errors[] = INVALID_FULL_NAME;
    }

    if (!preg_match('/^[а-яА-ЯёЁ0-9\s]+$/u', trim($specialty))) {
        $errors[] = INVALID_SPECIALTY;
    }

    if (strlen($full_name) > 255) {
        $errors[] = LONG_FULL_NAME;
    }

    if (strlen($specialty) > 255) {
        $errors[] = LONG_SPECIALTY;
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            ?> <p><?= $error; ?></p> <?php
        }
        die;
    }

    $query = "INSERT INTO doctors (full_name, specialty) VALUES (?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss", $full_name, $specialty);

    if ($stmt->execute()) {
        ?><p>Врач добавлен.</p><?php
    } else {
        ?><p>Ошибка при добавлении врача: <?= $stmt->error?></p><?php
    }

    $stmt->close();
    $connection->close();
}
