<?php
session_start();
if (!isset($_SESSION['user'])) {
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
        'doctor_id' => 'Доктор',
        'appointment_date' => 'Дата',
        'appointment_time' => 'Время'
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

    $user_id = $_SESSION['user_id'];
    $doctor_id = $_GET['doctor_id'];
    $appointment_date = $_GET['appointment_date'];
    $appointment_time = $_GET['appointment_time'];

    $currentDate = date('Y-m-d');
    if ($appointment_date < $currentDate) {
        ?><p>Нельзя выбирать дату записи меньше текущей</p><?php
        die;
    }

    include __DIR__ . '/error_messages.php';

    $user_id = $_SESSION['user_id']; // Получаем ID текущего пользователя

    $checkQuery = "SELECT EXISTS(SELECT 1 FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND user_id = ? AND status = 'Pending')";
    $checkAppointment = $connection->prepare($checkQuery);
    $checkAppointment->bind_param("isi", $doctor_id, $appointment_date, $user_id);
    $checkAppointment->execute();
    $checkAppointment->bind_result($exists);
    $checkAppointment->fetch();
    $checkAppointment->close();

    if ($exists) {
        ?><p>У вас уже есть запись к этому врачу на указанную дату.</p><?php
        exit();
    }

    $query = "INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time) VALUES (?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("iiss", $user_id, $doctor_id, $appointment_date, $appointment_time);

    if ($stmt->execute()) {
        ?><p>Вы записались на прием.</p><?php
    } else {
        ?><p>Ошибка: <?= $stmt->error?></p><?php
    }

    $stmt->close();
    $connection->close();
}