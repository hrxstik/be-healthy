<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include __DIR__ . '/config.php';
/**
 * @global mysqli $connection
 */

if (isset($_GET['appointment_id'])) {
    $appointment_id = $_GET['appointment_id'];

    $checkQuery = "SELECT user_id, appointment_date FROM appointments WHERE id = ?";
    $stmt = $connection->prepare($checkQuery);
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        if ($appointment['user_id'] == $_SESSION['user_id']) {
            $appointmentDate = strtotime($appointment['appointment_date']);
            $currentDate = time();

            $daysDifference = ($appointmentDate - $currentDate) / (60 * 60 * 24);

            if ($daysDifference >= 1) {
                $updateQuery = "UPDATE appointments SET status = 'Cancelled' WHERE id = ?";
                $stmt = $connection->prepare($updateQuery);
                $stmt->bind_param("i", $appointment_id);
                if ($stmt->execute()) {
                    echo "Запись успешно отменена!";
                } else {
                    echo "Ошибка: " . $connection->error;
                }
            }
            else {
                echo "Запись нельзя отменить менее чем за 1 день до приема.";
            }
        } else {
            echo "Вы не можете отменить эту запись.";
        }
    } else {
        echo "Запись не найдена.";
    }

    $stmt->close();
}
$connection->close();
