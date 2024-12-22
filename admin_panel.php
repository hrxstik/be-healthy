<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <noscript>Пожалуйста включите JavaScript</noscript>
    <title>Панель администрирования</title>
</head>
    <body>
        <?php
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
            header("Location: index.php");
            exit();
        }
        ?>

        <h2>Добавление врача</h2>
        <form id="addDoctorForm">
            <label for="full_name">ФИО:</label>
            <input type="text" name="full_name" id="full_name" required>

            <label for="specialty">Специальность:</label>
            <input type="text" name="specialty" id="specialty" required>

            <button type="submit">Добавить врача</button>
        </form>

        <div id="result"></div>

        <script>
            document.getElementById('addDoctorForm').addEventListener('submit', function(event) {
                event.preventDefault();

                const xhr = new XMLHttpRequest();
                let formData = new FormData(this);
                let queryString = new URLSearchParams(formData).toString();

                xhr.open('GET', 'add_doctor.php?' + queryString, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        document.getElementById('result').innerHTML = xhr.responseText;
                        document.getElementById('addDoctorForm').reset();
                    } else {
                        document.getElementById('result').innerHTML = 'Произошла ошибка: ' + xhr.statusText;
                    }
                };

                xhr.send();
            });
        </script>
    </body>
</html>