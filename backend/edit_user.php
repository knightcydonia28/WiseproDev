<?php
    session_start();
    if (!isset($_SESSION['login_status'])) {
        header('Location: login.php');
        exit();
    }
    if ($_SESSION['password_expiration'] == 0) {
        header('Location: change_password.php');
        exit();
    }
    if ($_SESSION['secret_key'] == 0) {
        header('Location: setup_mfa.php');
        exit();
    }
    if ($_SESSION['user_role'] != "administrator") {
        header('Location: home.php');
        exit();
    }
    if (!isset($_COOKIE['search_user'])) {
        header('Location: home.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            if (time() - $_SESSION['login_time'] > 900) {
                function destroySession() {
                    $_SESSION = array();
                    if (ini_get("session.use_cookies")) {
                        $params = session_get_cookie_params();
                        setcookie(session_name(), '', time() - 42000,
                            $params["path"], $params["domain"],
                            $params["secure"], $params["httponly"]
                        );
                    }
                    session_destroy();
                }
                destroySession();
                echo 
                "<script>
                    alert(\"Your session has expired.\");
                    window.location.replace(\"http://wisepro.com/testing6/login.php\");
                </script>";
            }
            if (time() - $_SESSION['login_time'] < 900) {
                $added_time = time() - $_SESSION['login_time'];
                $_SESSION['login_time'] += $added_time;
            }
        ?>
        <meta charset="UTF-8">
        <title>Edit User</title>
        <style>
            label {
                display: inline-block;
                width: 100px;
            }
            .error {
                color: #FF0000;
            }
            .tooltip {
                position: relative;
                display: inline-block;
                cursor: help;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }
            .tooltip_text {
                visibility: hidden;
                width: 200px;
                left: 113%;
                top: -5px; 
                background-color: #0c5adb;
                color: #FFFFFF;
                text-align: left;
                position: absolute;
                z-index: 1;
                padding: 6px 6px 6px 6px;
                border-radius: 10px;
                margin-top: -95%;
            }
            .tooltip:hover .tooltip_text {
                visibility: visible;
            }
            .tooltip .tooltip_text::after {
                content: "";
                position: absolute;
                top: 50%;
                right: 100%;
                margin-top: -5px;
                border-width: 5px;
                border-style: solid;
                border-color: transparent #0c5adb transparent transparent;
            }
        </style>
        <script>
            function passwordVisibility(input_id) {
              var x = document.getElementById(input_id);
              if (x.type === "password") {
                x.type = "text";
              } else {
                x.type = "password";
              }
            }
        </script>
    </head>
    <body>
        <a href="home.php">Home</a>        
        <?php
            include("logout.php");
        ?>
        <br><br>
        <a href='?logout=true'>Logout</a>
        <?php
            if ($_SERVER['REQUEST_METHOD'] === "POST") {
                
                function test_input($data) {
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                }
                function validateDate($date, $format = 'Y-m-d') {
                    $d = DateTime::createFromFormat($format, $date);
                    return $d && $d->format($format) == $date;
                }
                
                if (empty($_POST['password'])) {
                    if (empty($_POST['user_middle_name'])) {
                        if($_POST['password_expiration'] != 0 && $_POST['password_expiration'] != 1) {
                            $_SESSION['password_expiration_error'] = "Please select an appropriate value for the password expiration";
                            header("Location: edit_user.php", true, 303);
                            exit();
                        }
                        else {
                            $password_expiration = test_input($_POST['password_expiration']);
                            if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                $_SESSION['user_role_error'] = "Please select an appropriate user role";
                                header("Location: edit_user.php", true, 303);
                                exit();
                            }
                            else {
                                $user_role = test_input($_POST['user_role']);
                                if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_first_name'])) {
                                    $_SESSION['user_first_name_error'] = "Please ensure that your first name has letters, dashes, apostrophes and whitespaces only";
                                    header("Location: edit_user.php", true, 303);
                                    exit();
                                }
                                else {
                                    $user_first_name = test_input($_POST['user_first_name']);
                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_last_name'])) {
                                        $_SESSION['user_last_name_error'] = "Please ensure that your last name has letters, dashes, apostrophes and whitespaces only";
                                        header("Location: edit_user.php", true, 303);
                                        exit();
                                    }
                                    else {
                                        $user_last_name = test_input($_POST['user_last_name']);
                                        $user_email = test_input($_POST['user_email']);
                                        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                                            $_SESSION['user_email_error'] = "Please enter a valid email address (e.g., yourname@example.com)";
                                            header("Location: edit_user.php", true, 303);
                                            exit();
                                        }
                                        else {
                                            if (!preg_match("/^[0-9]{10}$/", $_POST['user_phone'])) {
                                                $_SESSION['user_phone_error'] = "Please enter a 10 digit phone number (without special characters including whitespaces)";
                                                header("Location: edit_user.php", true, 303);
                                                exit();
                                            }
                                            else {
                                                $user_phone = test_input($_POST['user_phone']);
                                                $user_birth_date = test_input($_POST['user_birth_date']);
                                                if (!validateDate($user_birth_date)) {
                                                    $_SESSION['user_birth_date_error'] = "Please enter a valid birth date";
                                                    header("Location: edit_user.php", true, 303);
                                                    exit();
                                                }
                                                else {
                                                    if ($_POST['user_status'] != "active" && $_POST['user_status'] != "inactive") {
                                                        $_SESSION['user_status_error'] = "Please select an appropriate user status";
                                                        header("Location: edit_user.php", true, 303);
                                                        exit();
                                                    }
                                                    else {
                                                        $user_status = test_input($_POST['user_status']);
                                                        if ($_POST['secret_key'] != 0 && $_POST['secret_key'] != 1) {
                                                            $_SESSION['secret_key_error'] = "Please select an appropriate value for the secret key";
                                                            header("Location: edit_user.php", true, 303);
                                                            exit();
                                                        }
                                                        else {
                                                            $secret_key = test_input($_POST['secret_key']);
                                                            if ($secret_key == 0) {
                                                                $secret_key = NULL;
                                                                $user_middle_name = NULL;
                                                                
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date = ?, user_status = ?, secret_key = ? WHERE username = ?");
                                                                $stmt->bind_param("issssssssis", $password_expiration, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $secret_key, $_COOKIE['username']);
                                                                if ($stmt->execute()) {
                                                                    $_SESSION["edit_user_confirmation"] = "<p>Changes have been made successfully.</p>";
                                                                    header("Location: edit_user.php", true, 303);
                                                                    exit();
                                                                }
                                                                else {
                                                                    $_SESSION["edit_user_error"] = "<p>Changes have not been made successfully.</p>";
                                                                    header("Location: edit_user.php", true, 303);
                                                                    exit();
                                                                }
                                                            }
                                                            else {
                                                                $user_middle_name = NULL;

                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date = ?, user_status = ? WHERE username = ?");
                                                                $stmt->bind_param("isssssssss", $password_expiration, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $_COOKIE['username']);
                                                                if ($stmt->execute()) {
                                                                    $_SESSION["edit_user_confirmation"] = "<p>Changes have been made successfully.</p>";
                                                                    header("Location: edit_user.php", true, 303);
                                                                    exit();
                                                                }
                                                                else {
                                                                    $_SESSION["edit_user_error"] = "<p>Changes have not been made successfully.</p>";
                                                                    header("Location: edit_user.php", true, 303);
                                                                    exit();
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }  
                    }
                    else {
                        if($_POST['password_expiration'] != 0 && $_POST['password_expiration'] != 1) {
                            $_SESSION['password_expiration_error'] = "Please select an appropriate value for the password expiration";
                            header("Location: edit_user.php", true, 303);
                            exit();
                        }
                        else {
                            $password_expiration = test_input($_POST['password_expiration']);
                            if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                $_SESSION['user_role_error'] = "Please select an appropriate user role";
                                header("Location: edit_user.php", true, 303);
                                exit();
                            }
                            else {
                                $user_role = test_input($_POST['user_role']);
                                if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_first_name'])) {
                                    $_SESSION['user_first_name_error'] = "Please ensure that your first name has letters, dashes, apostrophes and whitespaces only";
                                    header("Location: edit_user.php", true, 303);
                                    exit();
                                }
                                else {
                                    $user_first_name = test_input($_POST['user_first_name']);
                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_middle_name'])) {
                                        $_SESSION['user_middle_name_error'] = "<p>Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only.</p>";
                                        header("Location: edit_user.php", true, 303);
                                        exit();
                                    }
                                    else {
                                        $user_middle_name = $_POST['user_middle_name'];
                                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_last_name'])) {
                                            $_SESSION['user_last_name_error'] = "Please ensure that your last name has letters, dashes, apostrophes and whitespaces only";
                                            header("Location: edit_user.php", true, 303);
                                            exit();
                                        }
                                        else {
                                            $user_last_name = test_input($_POST['user_last_name']);
                                            $user_email = test_input($_POST['user_email']);
                                            if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                                                $_SESSION['user_email_error'] = "Please enter a valid email address (e.g., yourname@example.com)";
                                                header("Location: edit_user.php", true, 303);
                                                exit(); 
                                            }
                                            else {
                                                if (!preg_match("/^[0-9]{10}$/", $_POST['user_phone'])) {
                                                    $_SESSION['user_phone_error'] = "Please enter a 10 digit phone number (without special characters including whitespaces)";
                                                    header("Location: edit_user.php", true, 303);
                                                    exit();
                                                }
                                                else {
                                                    $user_phone = test_input($_POST['user_phone']);
                                                    $user_birth_date = test_input($_POST['user_birth_date']);
                                                    if (!validateDate($user_birth_date)) {
                                                        $_SESSION['user_birth_date_error'] = "Please enter a valid birth date";
                                                        header("Location: edit_user.php", true, 303);
                                                        exit();
                                                    }
                                                    else {
                                                        if ($_POST['user_status'] != "active" && $_POST['user_status'] != "inactive") {
                                                            $_SESSION['user_status_error'] = "Please select an appropriate user status";
                                                            header("Location: edit_user.php", true, 303);
                                                            exit();
                                                        }
                                                        else {
                                                            $user_status = test_input($_POST['user_status']);
                                                            if ($_POST['secret_key'] != 0 && $_POST['secret_key'] != 1) {
                                                                $_SESSION['secret_key_error'] = "Please select an appropriate value for the secret key";
                                                                header("Location: edit_user.php", true, 303);
                                                                exit();
                                                            }
                                                            else {
                                                                $secret_key = test_input($_POST['secret_key']);
                                                                if ($secret_key == 0) {
                                                                    $secret_key = NULL;

                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ?, user_status = ?, secret_key = ? WHERE username = ?");
                                                                    $stmt->bind_param("issssssssis", $password_expiration, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $secret_key, $_COOKIE['username']);
                                                                    if ($stmt->execute()) {
                                                                        $_SESSION["edit_user_confirmation"] = "<p>Changes have been made successfully.</p>";
                                                                        header("Location: edit_user.php", true, 303);
                                                                        exit(); 
                                                                    }
                                                                    else {
                                                                        $_SESSION["edit_user_error"] = "<p>Changes have not been made successfully.</p>";
                                                                        header("Location: edit_user.php", true, 303);
                                                                        exit(); 
                                                                    }
                                                                }
                                                                else {

                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ?, user_status = ? WHERE username = ?");
                                                                    $stmt->bind_param("isssssssss", $password_expiration, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $_COOKIE['username']);
                                                                    if ($stmt->execute()) {
                                                                        $_SESSION["edit_user_confirmation"] = "<p>Changes have been made successfully.</p>";
                                                                        header("Location: edit_user.php", true, 303);
                                                                        exit();
                                                                    }
                                                                    else {
                                                                        $_SESSION["edit_user_error"] = "<p>Changes have not been made successfully.</p>";
                                                                        header("Location: edit_user.php", true, 303);
                                                                        exit();
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }  
                    }
                }
                else {
                    if (empty($_POST['user_middle_name'])) {
                        if($_POST['password_expiration'] != 0 && $_POST['password_expiration'] != 1) {
                            $_SESSION['password_expiration_error'] = "Please select an appropriate value for the password expiration";
                            header("Location: edit_user.php", true, 303);
                            exit();
                        }
                        else {
                            $password_expiration = test_input($_POST['password_expiration']);
                            if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                $_SESSION['user_role_error'] = "Please select an appropriate user role";
                                header("Location: edit_user.php", true, 303);
                                exit();
                            }
                            else {
                                $user_role = test_input($_POST['user_role']);
                                $password = $_POST['password'];
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_first_name'])) {
                                    $_SESSION['user_first_name_error'] = "Please ensure that your first name has letters, dashes, apostrophes and whitespaces only";
                                    header("Location: edit_user.php", true, 303);
                                    exit();
                                }
                                else {
                                    $user_first_name = test_input($_POST['user_first_name']);
                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_last_name'])) {
                                        $_SESSION['user_last_name_error'] = "Please ensure that your last name has letters, dashes, apostrophes and whitespaces only";
                                        header("Location: edit_user.php", true, 303);
                                        exit();
                                    }
                                    else {
                                        $user_last_name = test_input($_POST['user_last_name']);
                                        $user_email = test_input($_POST['user_email']);
                                        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                                            $_SESSION['user_email_error'] = "Please enter a valid email address (e.g., yourname@example.com)";
                                            header("Location: edit_user.php", true, 303);
                                            exit();
                                        }
                                        else {
                                            if (!preg_match("/^[0-9]{10}$/", $_POST['user_phone'])) {
                                                $_SESSION['user_phone_error'] = "Please enter a 10 digit phone number (without special characters including whitespaces)";
                                                header("Location: edit_user.php", true, 303);
                                                exit();
                                            }
                                            else {
                                                $user_phone = test_input($_POST['user_phone']);                                      
                                                $user_birth_date = test_input($_POST['user_birth_date']);
                                                if (!validateDate($user_birth_date)) {
                                                    $_SESSION['user_birth_date_error'] = "Please enter a valid birth date";
                                                    header("Location: edit_user.php", true, 303);
                                                    exit(); 
                                                }
                                                else {
                                                    if ($_POST['user_status'] != "active" && $_POST['user_status'] != "inactive") {
                                                        $_SESSION['user_status_error'] = "Please select an appropriate user status";
                                                        header("Location: edit_user.php", true, 303);
                                                        exit();
                                                    }
                                                    else {
                                                        $user_status = test_input($_POST['user_status']);
                                                        if ($_POST['secret_key'] != 0 && $_POST['secret_key'] != 1) {
                                                            $_SESSION['secret_key_error'] = "Please select an appropriate value for the secret key";
                                                            header("Location: edit_user.php", true, 303);
                                                            exit();
                                                        }
                                                        else {
                                                            $secret_key = test_input($_POST['secret_key']);
                                                            if ($secret_key == 0) {
                                                                $secret_key = NULL;
                                                                $user_middle_name = NULL;

                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, password = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date = ?, user_status = ?, secret_key = ? WHERE username = ?");
                                                                $stmt->bind_param("isssssssssss", $password_expiration, $hashed_password, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $secret_key, $_COOKIE['username']);
                                                                if ($stmt->execute()) {
                                                                    $_SESSION["edit_user_confirmation"] = "<p>Changes have been made successfully.</p>";
                                                                    header("Location: edit_user.php", true, 303);
                                                                    exit();
                                                                }
                                                                else {
                                                                    $_SESSION["edit_user_error"] = "<p>Changes have not been made successfully.</p>";
                                                                    header("Location: edit_user.php", true, 303);
                                                                    exit();
                                                                }
                                                            }
                                                            else {
                                                                $user_middle_name = NULL;

                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, password = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date = ?, user_status = ? WHERE username = ?");
                                                                $stmt->bind_param("issssssssss", $password_expiration, $hashed_password, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $_COOKIE['username']);
                                                                if ($stmt->execute()) {
                                                                    $_SESSION["edit_user_confirmation"] = "<p>Changes have been made successfully.</p>";
                                                                    header("Location: edit_user.php", true, 303);
                                                                    exit();
                                                                }
                                                                else {
                                                                    $_SESSION["edit_user_error"] = "<p>Changes have not been made successfully.</p>";
                                                                    header("Location: edit_user.php", true, 303);
                                                                    exit();
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else {                 
                        if($_POST['password_expiration'] != 0 && $_POST['password_expiration'] != 1) {
                            $_SESSION['password_expiration_error'] = "Please select an appropriate value for the password expiration";
                            header("Location: edit_user.php", true, 303);
                            exit();
                        }
                        else {
                            $password_expiration = test_input($_POST['password_expiration']);
                            if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                $_SESSION['user_role_error'] = "Please select an appropriate user role";
                                header("Location: edit_user.php", true, 303);
                                exit();
                            }
                            else {
                                $user_role = test_input($_POST['user_role']);
                                $password = $_POST['password'];
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_first_name'])) {
                                    $_SESSION['user_first_name_error'] = "Please ensure that your first name has letters, dashes, apostrophes and whitespaces only";
                                    header("Location: edit_user.php", true, 303);
                                    exit();
                                }
                                else {
                                    $user_first_name = test_input($_POST['user_first_name']);
                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_middle_name'])) {
                                        $_SESSION['user_middle_name_error'] = "<p>Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only.</p>";
                                        header("Location: edit_user.php", true, 303);
                                        exit();
                                    }
                                    else {
                                        $user_middle_name = $_POST['user_middle_name'];
                                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_last_name'])) {
                                            $_SESSION['user_last_name_error'] = "Please ensure that your last name has letters, dashes, apostrophes and whitespaces only";
                                            header("Location: edit_user.php", true, 303);
                                            exit();
                                        }
                                        else {
                                            $user_last_name = test_input($_POST['user_last_name']);
                                            $user_email = test_input($_POST['user_email']);
                                            if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                                                $_SESSION['user_email_error'] = "Please enter a valid email address (e.g., yourname@example.com)";
                                                header("Location: edit_user.php", true, 303);
                                                exit();
                                            }
                                            else {
                                                if (!preg_match("/^[0-9]{10}$/", $_POST['user_phone'])) {
                                                    $_SESSION['user_phone_error'] = "Please enter a 10 digit phone number (without special characters including whitespaces)";
                                                    header("Location: edit_user.php", true, 303);
                                                    exit();
                                                }
                                                else {
                                                    $user_phone = test_input($_POST['user_phone']);
                                                    $user_birth_date = test_input($_POST['user_birth_date']);
                                                    if (!validateDate($user_birth_date)) {
                                                        $_SESSION['user_birth_date_error'] = "Please enter a valid birth date";
                                                        header("Location: edit_user.php", true, 303);
                                                        exit();
                                                    }
                                                    else {
                                                        if ($_POST['user_status'] != "active" && $_POST['user_status'] != "inactive") {
                                                            $_SESSION['user_status_error'] = "Please select an appropriate user status";
                                                            header("Location: edit_user.php", true, 303);
                                                            exit();
                                                        }
                                                        else {
                                                            $user_status = test_input($_POST['user_status']);
                                                            if ($_POST['secret_key'] != 0 && $_POST['secret_key'] != 1) {
                                                                $_SESSION['secret_key_error'] = "Please select an appropriate value for the secret key";
                                                                header("Location: edit_user.php", true, 303);
                                                                exit();
                                                            }
                                                            else {
                                                                $secret_key = test_input($_POST['secret_key']);
                                                                if ($secret_key == 0) {
                                                                    $secret_key = NULL;

                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, password = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ?, user_status = ?, secret_key = ? WHERE username = ?");
                                                                    $stmt->bind_param("isssssssssis", $password_expiration, $hashed_password, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $secret_key, $_COOKIE['username']);
                                                                    if ($stmt->execute()) {
                                                                        $_SESSION["edit_user_confirmation"] = "<p>Changes have been made successfully.</p>";
                                                                        header("Location: edit_user.php", true, 303);
                                                                        exit();
                                                                    }
                                                                    else {
                                                                        $_SESSION["edit_user_error"] = "<p>Changes have not been made successfully.</p>";
                                                                        header("Location: edit_user.php", true, 303);
                                                                        exit(); 
                                                                    }
                                                                }
                                                                else {

                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, password = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ?, user_status = ? WHERE username = ?");
                                                                    $stmt->bind_param("issssssssss", $password_expiration, $hashed_password, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $_COOKIE['username']);
                                                                    if ($stmt->execute()) {
                                                                        $_SESSION["edit_user_confirmation"] = "<p>Changes have been made successfully.</p>";
                                                                        header("Location: edit_user.php", true, 303);
                                                                        exit(); 
                                                                    }
                                                                    else {
                                                                        $_SESSION["edit_user_error"] = "<p>Changes have not been made successfully.</p>";
                                                                        header("Location: edit_user.php", true, 303);
                                                                        exit();
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
                if (isset($_SESSION["edit_user_confirmation"])) {echo $_SESSION["edit_user_confirmation"];}
                if (isset($_SESSION["edit_user_error"])) {echo $_SESSION["edit_user_error"];}
            }
            include("database.php");
            $stmt = $DBConnect->prepare("SELECT username, password_expiration, user_role, user_first_name, user_middle_name, user_last_name, user_email, user_phone, user_birth_date, user_status, secret_key FROM users WHERE username = ?");
            $stmt->bind_param("s", $_COOKIE["username"]); 
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($retrieved_username, $retrieved_password_expiration, $retrieved_user_role, $retrieved_user_first_name, $retrieved_user_middle_name, $retrieved_user_last_name, $retrieved_user_email, $retrieved_user_phone, $retrieved_user_birth_date, $retrieved_user_status, $retrieved_secret_key);
            $stmt->fetch();
            $stmt->close();
            $DBConnect->close();
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <h2>Edit User</h2>
            <p>Please utilize the form below to make changes to the selected account:</p>
            <p><span class="error">* required field</span></p>
            <label for="username"><b>Username:</b></label>
            <input type="text" id="username" name="username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric" value="<?php echo $_COOKIE["username"]; ?>" readonly required><span class="error"> * </span><br><br>
            <label for="password_expiration"><b>Password Expiration:</b></label>
            <select id="password_expiration" name="password_expiration" required>
                <option value="">&nbsp;</option>
                <option value="0" <?php if ($retrieved_password_expiration == 0) {echo "selected";} ?>>0</option>
                <option value="1" <?php if ($retrieved_password_expiration == 1) {echo "selected";} ?>>1</option>
            </select><span class="error"> * <?php if (isset($_SESSION['password_expiration_error'])) {echo $_SESSION['password_expiration_error'];} ?></span>
            &nbsp;
            <div class="tooltip">What is this?
                <span class="tooltip_text" style="margin-top: -91%;">The password expiration shows whether a user has reset their temporary password (1) or not (0). <br><br> By selecting 0 (if previously 1), the password expiration can also be used as a way to require the user to change their password.</span>
            </div>
            <br><br>
            <label for="password"><b>Password:</b></label>
            <input type="password" id="password" name="password">&nbsp;&nbsp;<input type="checkbox" onclick="passwordVisibility('password')">Show Password <br><br>
            <label for="user_role"><b>User Role:</b></label>
            <select id="user_role" name="user_role" required>
                <option value="">&nbsp;</option>
                <option value="user" <?php if ($retrieved_user_role == "user") {echo "selected";} ?>>User</option>
                <option value="recruiter" <?php if ($retrieved_user_role == "recruiter") {echo "selected";} ?>>Recruiter</option>
                <option value="administrator" <?php if ($retrieved_user_role == "administrator") {echo "selected";} ?>>Administrator</option>
            </select><span class="error"> * <?php if (isset($_SESSION['user_role_error'])) {echo $_SESSION['user_role_error'];} ?></span><br><br>
            <label for="user_first_name"><b>First name:</b></label>
            <input type="text" id="user_first_name" name="user_first_name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your first name has letters, dashes, apostrophes and whitespaces only" value="<?php echo $retrieved_user_first_name; ?>" required><span class="error"> * <?php if (isset($_SESSION['user_first_name_error'])) {echo $_SESSION['user_first_name_error'];} ?></span><br><br>
            <label for="user_middle_name"><b>Middle Name:</b></label>
            <input type="text" id="user_middle_name" name="user_middle_name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only" value="<?php echo $retrieved_user_middle_name; ?>"><span class="error"> <?php if (isset($_SESSION['user_middle_name_error'])) {echo $_SESSION['user_middle_name_error'];} ?></span><br><br>
            <label for="user_last_name"><b>Last Name:</b></label>
            <input type="text" id="user_last_name" name="user_last_name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your last name has letters, dashes, apostrophes and whitespaces only" value="<?php echo $retrieved_user_last_name; ?>" required><span class="error"> * <?php if (isset($_SESSION['user_last_name_error'])) {echo $_SESSION['user_last_name_error'];} ?></span><br><br>
            <label for="user_email"><b>Email:</b></label>
            <input type="email" id="user_email" name="user_email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address (e.g., yourname@example.com)" value="<?php echo $retrieved_user_email; ?>" required><span class="error"> * <?php if (isset($_SESSION['user_email_error'])) {echo $_SESSION['user_email_error'];} ?></span><br><br>
            <label for="phone"><b>Phone Number:</b></label>
            <input type="tel" id="user_phone" name="user_phone" pattern="[0-9]{10}" title="Please enter a 10 digit phone number (without special characters including whitespaces)" value="<?php echo $retrieved_user_phone; ?>" required><span class="error"> * <?php if (isset($_SESSION['user_phone_error'])) {echo $_SESSION['user_phone_error'];} ?></span><br><br>
            <label for="user_birth_date"><b>Birth Date:</b></label>
            <?php $minimum_year = date("Y") - 75; $maximum_year = date("Y") - 16; ?>
            <input type="date" id="user_birth_date" name="user_birth_date" min="<?php echo "$minimum_year-01-01" ?>" max="<?php echo "$maximum_year-01-01"; ?>" required><span class="error"> * <?php if (isset($_SESSION['user_birth_date_error'])) {echo $_SESSION['user_birth_date_error'];} ?></span><br><br>
            <label for="user_status"><b>User Status:</b></label>
            <select id="user_status" name="user_status" required>
                <option value="">&nbsp;</option>
                <option value="active" <?php if ($retrieved_user_status == "active") {echo "selected";} ?>>Active</option>
                <option value="inactive" <?php if ($retrieved_user_status == "inactive") {echo "selected";} ?>>Inactive</option>
            </select><span class="error"> * <?php if (isset($_SESSION['user_status_error'])) {echo $_SESSION['user_status_error'];} ?></span><br><br>
            <label for="secret_key"><b>Secret Key:</b></label>
            <?php
                if ($retrieved_secret_key == NULL) {
                    $retrieved_secret_key = 0;
                }
                else {
                    $retrieved_secret_key = 1;
                }
            ?>
            <select id="secret_key" name="secret_key" required>
                <option value="">&nbsp;</option>
                <option value="0" <?php if ($retrieved_secret_key == 0) {echo "selected";} ?>>0</option>
                <option value="1" <?php if ($retrieved_secret_key == 1) {echo "selected";} ?>>1</option>
            </select><span class="error"> * <?php if (isset($_SESSION['secret_key_error'])) {echo $_SESSION['secret_key_error'];} ?></span>
            &nbsp;
            <div class="tooltip">What is this?
                <span class="tooltip_text" style="margin-top: -130%;">The secret key is assigned to each user so that they can use Multi-factor authentication (MFA). <br><br> 1 indicates a user has a secret key whereas 0 indicates the opposite. <br><br> Select 0 to require a user to go through the MFA process again by resetting their secret key (if they previously had 1).</span>
            </div>
            <br><br>
            <input type="submit" name="edit_user_submit" value="Submit Changes">
        </form>
        <?php
            if ($_SERVER['REQUEST_METHOD'] === "GET" || !isset($_POST['edit_user_submit'])) {
                echo 
                "<script>
                    document.getElementById('user_birth_date').value = \"".$retrieved_user_birth_date."\";
                </script>";
            }
        ?>
    </body>
</html>
<?php
    if (isset($_SESSION['edit_user_confirmation'])) {unset($_SESSION['edit_user_confirmation']);}
    if (isset($_SESSION['edit_user_error'])) {unset($_SESSION['edit_user_error']);}
    if (isset($_SESSION['password_expiration_error'])) {unset($_SESSION['password_expiration_error']);}
    if (isset($_SESSION['user_role_error'])) {unset($_SESSION['user_role_error']);}
    if (isset($_SESSION['user_first_name_error'])) {unset($_SESSION['user_first_name_error']);}
    if (isset($_SESSION['user_middle_name_error'])) {unset($_SESSION['user_middle_name_error']);}
    if (isset($_SESSION['user_last_name_error'])) {unset($_SESSION['user_last_name_error']);}
    if (isset($_SESSION['user_email_error'])) {unset($_SESSION['user_email_error']);}
    if (isset($_SESSION['user_phone_error'])) {unset($_SESSION['user_phone_error']);}
    if (isset($_SESSION['user_birth_date_error'])) {unset($_SESSION['user_birth_date_error']);}
    if (isset($_SESSION['user_status_error'])) {unset($_SESSION['user_status_error']);}
    if (isset($_SESSION['secret_key_error'])) {unset($_SESSION['secret_key_error']);}
?>