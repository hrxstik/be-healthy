<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <noscript>Пожалуйста включите JavaScript</noscript>
    <title>Будь здоров</title>
    <script type="module">
        import {loadDoctors} from "./loadDoctors.js";
        window.loadDoctors = loadDoctors;
    </script>
</head>
<body>
    <?php
        session_start();
        if (isset($_SESSION['user'])) {
            ?>
            <p>Добро пожаловать, <?= htmlspecialchars($_SESSION['user']); ?>!</p>
            <?php if ($_SESSION['user'] !== 'admin') {?>
                <div> <a href="lk.php">Личный кабинет</a></div>
            <?php }
            else {
                ?><div> <a href="admin_panel.php">Администрирование</a></div> <?php
            }?>
            <form action="logout.php">
                <button type="submit">Выйти</button>
            </form>
            <?php
        }
        else {
            ?>
            <div> <a href="sign_in.php">Войти в личный кабинет</a></div>
            <div> <a href="sign_up.php">Зарегистрироваться</a></div>
            <?php
        }
    ?>
    <button onclick="loadDoctors()">Список докторов</button>
    <div id="doctorsTable"></div>
</body>
</html>