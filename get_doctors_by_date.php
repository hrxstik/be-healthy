<?php
include __DIR__ . '/config.php';
/**
 * @global mysqli $connection
 */

if (isset($_GET['specialty']) && isset($_GET['date'])) {
    $specialty = $_GET['specialty'];
    $date = $_GET['date'];

    $query = "
        SELECT doctors.id, doctors.full_name, 
               5 - COALESCE(COUNT(appointments.id), 0) AS available_slots
        FROM doctors
        LEFT JOIN appointments ON doctors.id = appointments.doctor_id 
            AND appointments.appointment_date = ? AND appointments.status = 'Pending'
        WHERE doctors.specialty = ?
        GROUP BY doctors.id
        HAVING available_slots >= 0
    ";


    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss", $date, $specialty);
    $stmt->execute();
    $result = $stmt->get_result();

    $doctors = [];
    if ($result->num_rows > 0) { ?>
        <table>
            <tr><th>ФИО</th>
                <th>Свободные номерки</th>
                <th>Записаться</th>
            </tr>
            <?php
            while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['available_slots']) ?></td>
                    <td>
                        <?php if ($row['available_slots'] > 0): ?>
                            <button onclick="addAppointment(<?= $row['id']; ?>, '<?= $date; ?>', '<?= date('H:i:s'); ?>')">Записаться</button>
                        <?php else: ?>
                            <span>Нет свободных мест</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
        </table> <?php
    } else {
        ?> <p>Нет свободных врачей на выбранную дату.</p> <?php
    }
}

$connection->close();
