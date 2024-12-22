<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <noscript>Пожалуйста включите JavaScript</noscript>
    <title>Вход</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js"></script>
    <script type="module">
        import {errorMessages} from "./errorMessages.js";

        window.signIn = function (event) {
            event.preventDefault();
            const password = document.getElementById('password').value;

            const errorMessage = document.getElementById('errorMessage');

            const errors = [];

            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])[a-zA-Z]{6,}$/;

            if (!passwordRegex.test(password)) {
                errors.push(errorMessages.invalidPassword);
            }

            if (password.length < 6) {
                errors.push(errorMessages.shortPassword);
            }

            if (password.length > 255) {
                errors.push(errorMessages.longPassword);
            }

            if (errors.length > 0) {
                errorMessage.innerHTML = errors.join('<br>');
                history.replaceState(null, '', window.location);
                return false;
            }

            const hashedPassword = CryptoJS.MD5(password).toString();
            document.getElementById('hashed_password').value = hashedPassword;
            document.getElementById('login').submit();
            return true;
        }
    </script>
</head>
<body>
    <h2>Войти в личный кабинет</h2>
    <form id="login" action="" method="get" onsubmit="return signIn(event);">

        <label for="username">Логин:</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars(isset($_GET['username']) ? $_GET['username'] : '');?>" required>

        <label for="password">Пароль:</label>
        <input type="text" id="password"" required>

        <input type="hidden" id="hashed_password" name="hashed_password">
        <button type="submit">Войти</button>
        <p id="errorMessage" class="error-message"></p>
    </form>
    <?php
    session_start();
    if (isset($_SESSION['user'])) {
        header("Location: index.php");
        exit();
    }
    if (!empty($_GET)) {
        $errors = [];
        $emptyFieldsErrors = [
            'username' => 'Логин',
            'hashed_password' => 'Пароль',
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

        $username = htmlspecialchars($_GET['username']);
        $hashed_password = htmlspecialchars($_GET['hashed_password']);

        $errors = [];
        include __DIR__ . '/admin_data.php';

        if ($username === 'admin' && $hashed_password === ADMIN_PASSWORD) {
            $_SESSION['user'] = 'admin';
            $_SESSION['user_id'] = 0;
            header("Location: admin_panel.php");
            exit();
        }

        include __DIR__ . '/config.php';
        /**
         * @global mysqli $connection
         */

        $query = "SELECT * FROM users WHERE BINARY email = ? AND password = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ss", $username, $hashed_password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user'] = $username;
            $_SESSION['user_id'] = $user['id'];
            header("Location: lk.php");
            exit();
        } else {
            ?><p>Неверный логин или пароль.</p><?php
        }

        $stmt->close();
    }
    ?>
<script>

</script>
</body>
</html>
