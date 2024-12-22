<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="style.css">
        <title>Личный кабинет</title>
        <script type="module">
            import {loadDoctors} from "./loadDoctors.js";
            import {cancelAppointment} from "./cancelAppointment.js";

            window.loadDoctors = loadDoctors;
            window.cancelAppointment = cancelAppointment;

            window.loadDoctorsByDate = function (event) {
                event.preventDefault();

                const specialty = document.getElementById('specialty').value;
                const appointmentDate = document.getElementById('date').value;

                if (!specialty || !appointmentDate) {
                    alert("Пожалуйста, выберите специальность и дату.");
                    return false;
                }
                document.getElementById('doctorsTableByTime').innerHTML = '';
                const xhr = new XMLHttpRequest();
                xhr.open("GET", `get_doctors_by_date.php?specialty=${specialty}&date=${appointmentDate}`, true);
                xhr.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('doctorsTableByTime').innerHTML = this.responseText;
                    } else {
                        document.getElementById('doctorsTableByTime').innerHTML = 'Ошибка получения информации о докторах';
                    }
                };
                xhr.send();

                return true;
            };

            window.addAppointment = function (doctorId, appointmentDate, appointmentTime) {
                const xhr = new XMLHttpRequest();
                xhr.open("GET", `add_appointment.php?doctor_id=${doctorId}&appointment_date=${appointmentDate}&appointment_time=${appointmentTime}`, true);

                xhr.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('appointmentMessage').innerHTML = this.responseText;
                        loadDoctorsByDate(event);
                    } else {
                        document.getElementById('appointmentMessage').innerHTML = 'Ошибка записи на прием. Попробуйте снова.';
                    }
                };

                xhr.onerror = function() {
                    document.getElementById('appointmentMessage').innerHTML = 'Ошибка сети. Попробуйте позже.';
                };

                xhr.send();
            }
        </script>
    </head>
    <body>
        <?php
            session_start();
            if (!isset($_SESSION['user']) || $_SESSION['user'] === 'admin') {
                header("Location: index.php");
                exit();
            }
        ?>
        <p>Добро пожаловать, <?= htmlspecialchars($_SESSION['user']); ?>!</p>
        <?php
            include __DIR__ . '/config.php';
            /**
             * @global mysqli $connection
             */
        ?>
        <button onclick="loadDoctors()">Список докторов</button>
        <div id="doctorsTable"></div>
        <form action="logout.php">
            <button type="submit">Выйти</button>
        </form>
        <?php
            $userId = $_SESSION['user_id'];
            $query = "
            SELECT appointments.id, appointments.appointment_date, appointments.appointment_time, doctors.full_name, doctors.specialty, appointments.status
            FROM appointments
            JOIN doctors ON appointments.doctor_id = doctors.id
            WHERE appointments.user_id = ?
            ORDER BY appointments.appointment_date DESC, appointments.appointment_time DESC";

            $stmt = $connection->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
        ?>
        <h2>Мои записи на прием</h2>
        <table>
            <tr>
                <th>Дата приема</th>
                <th>Время приема</th>
                <th>Доктор</th>
                <th>Специальность</th>
                <th>Статус</th>
                <th>Действие</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($appointment = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($appointment['appointment_date']); ?></td>
                        <td><?= htmlspecialchars($appointment['appointment_time']); ?></td>
                        <td><?= htmlspecialchars($appointment['full_name']); ?></td>
                        <td><?= htmlspecialchars($appointment['specialty']); ?></td>
                        <td><?= htmlspecialchars($appointment['status']); ?></td>
                        <td>
                            <?php if ($appointment['status'] === 'Pending'): ?>
                                <button onclick="cancelAppointment(<?= $appointment['id']; ?>)">Отменить запись</button>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">У вас нет записей на прием.</td>
                </tr>
            <?php endif; ?>
        </table>
        
        <div id="appointmentMessage"></div>

        <button onclick="document.getElementById('appointmentFormPopUp').style.display='block'">Записаться на прием</button>

        <div id="appointmentFormPopUp" style="display:none;">
            <h2>Запись на прием</h2>
            <form id="appointmentForm" method="get" onsubmit="return loadDoctorsByDate(event);">
                <label for="specialty">Выберите специальность:</label>
                <select id="specialty" name="specialty" required>
                    <?php
                    $query = "SELECT DISTINCT specialty FROM doctors";
                    $result = $connection->query($query);
                    if ($result->num_rows > 0) {
                        while ($specialty = $result->fetch_assoc()) {
                            ?><option value="<?= htmlspecialchars($specialty['specialty']);?>"><?=htmlspecialchars($specialty['specialty']);?></option><?php
                        }
                    }
                    ?>
                </select>

                <label for="date">Выберите дату:</label>
                <input type="date" id="date" name="appointment_date" required />
                <button type="submit">Поиск</button>

                <button onclick="document.getElementById('appointmentFormPopUp').style.display='none'">Закрыть</button>
            </form>
            <div id="doctorsTableByTime"></div>
        </div>
    </body>
</html>