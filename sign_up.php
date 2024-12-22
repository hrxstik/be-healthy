<?php
    session_start();
    ob_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <title>Регистрация</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js"></script>
    <noscript>Пожалуйста включите JavaScript</noscript>
    <script type="module">
        import {errorMessages} from "./errorMessages.js";

        window.signUp = function(event) {
            event.preventDefault();
            const password = document.getElementById('password').value;
            const checkPassword = document.getElementById('check_password').value;
            const fullName = document.getElementById('full_name').value;
            const email = document.getElementById('email').value;

            const errorMessage = document.getElementById('errorMessage');

            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])[a-zA-Z]{6,}$/;
            const fullNameRegex = /^([А-ЯA-Z]|[А-ЯA-Z][\x27а-яa-z]{1,}|[А-ЯA-Z][\x27а-яa-z]{1,}\-([А-ЯA-Z][\x27а-яa-z]{1,}|(оглы)|(кызы)))\040[А-ЯA-Z][\x27а-яa-z]{1,}(\040[А-ЯA-Z][\x27а-яa-z]{1,})?$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            const errors = [];

            if (!passwordRegex.test(password)) {
                errors.push(errorMessages.invalidPassword);
            }

            if (password.length < 6) {
                errors.push(errorMessages.shortPassword);
            }

            if (password.length > 255) {
                errors.push(errorMessages.longPassword);
            }

            if (password !== checkPassword) {
                errors.push(errorMessages.passwordsMismatch);
            }

            if (!fullNameRegex.test(fullName.trim())) {
                errors.push(errorMessages.invalidFullName);
            }

            if (fullName.length > 255) {
                errors.push(errorMessages.longFullName);
            }

            if (!emailRegex.test(email.trim())) {
                errors.push(errorMessages.invalidEmail);
            }

            if (email.length > 190) {
                errors.push(errorMessages.longEmail);
            }

            if (errors.length > 0) {
                errorMessage.innerHTML = errors.join('<br>');
                history.replaceState(null, '', window.location);
                return false;
            }

            const hashedPassword = CryptoJS.MD5(password).toString();
            const hashedCheckPassword = CryptoJS.MD5(checkPassword).toString();

            document.getElementById('hashed_password').value = hashedPassword;
            document.getElementById('hashed_check_password').value = hashedCheckPassword;

            document.getElementById('signUp').submit();
            return true;
        }
    </script>
</head>
<body>
    <h2>Регистрация</h2>
    <form id="signUp" action="" method="get" onsubmit="return signUp(event);">
        <label for="full_name">ФИО:</label>
        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars(isset($_GET['full_name']) ? $_GET['full_name'] : '');?>" required>

        <label for="email">Email:</label>
        <input type="text" id="email" name="email" value="<?= htmlspecialchars(isset($_GET['email']) ? $_GET['email'] : '');?>" required>

        <label for="password">Пароль:</label>
        <input type="text" id="password" required>

        <label for="check_password">Подтверждение пароля:</label>
        <input type="text" id="check_password" required>

        <input type="hidden" id="hashed_password" name="hashed_password">
        <input type="hidden" id="hashed_check_password" name="hashed_check_password">

        <button type="submit">Зарегистрироваться</button>
        <p id="errorMessage" class="error-message"></p>
    </form>
    <?php
        if (isset($_SESSION['user'])) {
            header("Location: index.php");
            exit();
        }
        if (!empty($_GET)) {
            $errors = [];
            $emptyFieldsErrors = [
                'full_name' => 'ФИО',
                'email' => 'Email',
                'hashed_password' => 'Пароль',
                'hashed_check_password' => 'Подтверждение пароля'
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
            $email = htmlspecialchars($_GET['email']);
            $hashed_password = htmlspecialchars($_GET['hashed_password']);
            $hashed_check_password = htmlspecialchars($_GET['hashed_check_password']);

            $errors = [];
            include __DIR__ . '/error_messages.php';

            if (strlen($full_name) > 255) {
                $errors[] = LONG_FULL_NAME;
            }

            if (strlen($email) > 190) {
                $errors[] = LONG_EMAIL;
            }

            if (!preg_match('/^([А-ЯA-Z]|[А-ЯA-Z][\'а-яa-z]{1,}|[А-ЯA-Z][\'а-яa-z]{1,}-([А-ЯA-Z][\'а-яa-z]{1,}|(оглы)|(кызы)))\s+[А-ЯA-Z][\'а-яa-z]{1,}(\s+[А-ЯA-Z][\'а-яa-z]{1,})?$/u', trim($full_name))) {
                $errors[] = INVALID_FULL_NAME;
            }
            if (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', trim($email))) {
                $errors[] = INVALID_EMAIL;
            }

            if ($hashed_password !== $hashed_check_password) {
                $errors[] = PASSWORD_MISMATCH;
            }

            include __DIR__ . '/config.php';
            /**
             * @global mysqli $connection
             */

            $query = "SELECT EXISTS(SELECT 1 FROM users WHERE BINARY email = ?)";

            $stmt = $connection->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($exists);
            $stmt->fetch();
            $stmt->close();

            if ($exists) {
                $errors[] = EMAIL_ALREADY_EXISTS;
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    ?><?= $error; ?><br><?php
                }
                die;
            }

            $query = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("sss", $full_name, $email, $hashed_password);

            if ($stmt->execute()) {
                 header("Location: sign_in.php");
                 exit();
            } else {
                ?><p>Ошибка при регистрации. Попробуйте позже.</p><?php
            }
            ob_end_flush();

            $stmt->close();
            $connection->close();
        }
    ?>
</body>
</html>
