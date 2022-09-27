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
    if (!isset($_SESSION['search_user'])) {
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
        ?>
        <meta charset="UTF-8" />
        <title>Edit User</title>
        <script>
            function retrieveCookie(cookie_name) {
                const name = cookie_name + "=";
                const decode = decodeURIComponent(document.cookie);
                const array = decode.split('; ');
                let value;
                array.forEach(val => {
                    if (val.indexOf(name) === 0) value = val.substring(name.length);
                })
                return value;
            }
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
    <a href="home.php">Home</a><br /><br />
    <form method="post" action="#">
        <input type="submit" name="logout" value="Logout" /><br /><br />
    </form>
    <h2>Edit User</h2>
    <p>Please utilize the form below to make changes to your account.</p> 
    <?php
        if (isset($_POST['logout'])) {
            include("logout.php");
            logout();
        }
        $decide_username = 0;
        if (isset($_POST['edit_user_submit'])) {
            if (empty($_POST['password'])) {
                if (empty($_POST['middle_name'])) {
                    if (!ctype_alnum($_POST['username'])) {
                        echo "<p>Please ensure that your username is alphanumeric.</p>";
                    }
                    else {
                        if ($_POST['username'] != $_COOKIE["username"]) {
                            include("database.php");
                            $stmt = $DBConnect->prepare("SELECT username FROM users WHERE username = ?");
                            $stmt->bind_param("s", $_POST['username']); 
                            $stmt->execute();
                            $stmt->store_result();
                        
                            if ($stmt->num_rows > 0) {
                                echo "<p>This username is already taken. Please choose another username.</p>";
                            }
                            else {
                                if($_POST['password_expiration'] != 0 && $_POST['password_expiration'] != 1) {
                                    echo "<p>Please select an appropriate value for the password expiration.</p>";
                                }
                                else {
                                    $password_expiration = $_POST['password_expiration'];
                                    if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                        echo "<p>Please select an appropriate user role.</p>";
                                    }
                                    else {
                                        $user_role = $_POST['user_role'];
                                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['first_name'])) {
                                            echo "<p>Please ensure that your first name has letters, dashes, apostrophes and whitespaces only.</p>";
                                        }
                                        else {
                                            $first_name = $_POST['first_name'];
                                            if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['last_name'])) {
                                                echo "<p>Please ensure that your last name has letters, dashes, apostrophes and whitespaces only.</p>";
                                            }
                                            else {
                                                $last_name = $_POST['last_name'];
                                                $filtered_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                                                if (!filter_var($filtered_email, FILTER_VALIDATE_EMAIL)) {
                                                    echo "<p>Please enter a valid email address (e.g., yourname@example.com).</p>"; 
                                                }
                                                else {
                                                    if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
                                                        echo "<p>Please enter a 10 digit phone number (without special characters including whitespaces).</p>"; 
                                                    }
                                                    else {
                                                        $phone = $_POST['phone'];
                                                        function validateDate($birth_date, $format = 'Y-m-d') {
                                                            $d = DateTime::createFromFormat($format, $birth_date);
                                                            return $d && $d->format($format) == $birth_date;
                                                        }
                                                        $birth_date = $_POST['birth_date'];
                                                        if (!validateDate($birth_date)) {
                                                            echo "<p>Please enter a valid birth date.</p>"; 
                                                        }
                                                        else {
                                                            include("database.php");
                                                            $stmt = $DBConnect->prepare("UPDATE users SET username = ?, password_expiration = ?, user_role = ?, user_first_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ? WHERE username = ?");
                                                            $stmt->bind_param("sisssssss", $_POST['username'], $password_expiration, $user_role, $first_name, $last_name, $filtered_email, $phone, $birth_date, $_COOKIE["username"]);
                                                            if ($stmt->execute()) {
                                                                $decide_username = 1;
                                                                setcookie("username", $_POST['username']);
                                                                echo "<p>Changes have been made successfully.</p>"; 
                                                            }
                                                            else {
                                                                echo "<p>Changes have not been made successfully.</p>"; 
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
                                echo "<p>Please select an appropriate value for the password expiration.</p>";
                            }
                            else {
                                $password_expiration = $_POST['password_expiration'];
                                if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                    echo "<p>Please select an appropriate user role.</p>";
                                }
                                else {
                                    $user_role = $_POST['user_role'];
                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['first_name'])) {
                                        echo "<p>Please ensure that your first name has letters, dashes, apostrophes and whitespaces only.</p>";
                                    }
                                    else {
                                        $first_name = $_POST['first_name'];
                                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['last_name'])) {
                                            echo "<p>Please ensure that your last name has letters, dashes, apostrophes and whitespaces only.</p>";
                                        }
                                        else {
                                            $last_name = $_POST['last_name'];
                                            $filtered_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                                            if (!filter_var($filtered_email, FILTER_VALIDATE_EMAIL)) {
                                                echo "<p>Please enter a valid email address (e.g., yourname@example.com).</p>"; 
                                            }
                                            else {
                                                if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
                                                    echo "<p>Please enter a 10 digit phone number (without special characters including whitespaces).</p>"; 
                                                }
                                                else {
                                                    $phone = $_POST['phone'];
                                                    function validateDate($birth_date, $format = 'Y-m-d') {
                                                        $d = DateTime::createFromFormat($format, $birth_date);
                                                        return $d && $d->format($format) == $birth_date;
                                                    }
                                                    $birth_date = $_POST['birth_date'];
                                                    if (!validateDate($birth_date)) {
                                                        echo "<p>Please enter a valid birth date.</p>"; 
                                                    }
                                                    else {                                                   
                                                        include("database.php");
                                                        $stmt = $DBConnect->prepare("UPDATE users SET username = ?, password_expiration = ?, user_role = ?, user_first_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date = ? WHERE username = ?");
                                                        $stmt->bind_param("sisssssss", $_POST['username'], $password_expiration, $user_role, $first_name, $last_name, $filtered_email, $phone, $birth_date, $_COOKIE["username"]);
                                                        if ($stmt->execute()) {
                                                            $decide_username = 0;
                                                            echo "<p>Changes have been made successfully.</p>"; 
                                                        }
                                                        else {
                                                            echo "<p>Changes have not been made successfully.</p>"; 
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
                    if (!ctype_alnum($_POST['username'])) {
                        echo "<p>Please ensure that your username is alphanumeric.</p>";
                    }
                    else {
                        if ($_POST['username'] != $_COOKIE["username"]) {
                            include("database.php");
                            $stmt = $DBConnect->prepare("SELECT username FROM users WHERE username = ?");
                            $stmt->bind_param("s", $_POST['username']); 
                            $stmt->execute();
                            $stmt->store_result();
                        
                            if ($stmt->num_rows > 0) {
                                echo "<p>This username is already taken. Please choose another username.</p>";
                            }
                            else {
                                if($_POST['password_expiration'] != 0 && $_POST['password_expiration'] != 1) {
                                    echo "<p>Please select an appropriate value for the password expiration.</p>";
                                }
                                else {
                                    $password_expiration = $_POST['password_expiration'];
                                    if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                        echo "<p>Please select an appropriate user role.</p>";
                                    }
                                    else {
                                        $user_role = $_POST['user_role'];
                                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['first_name'])) {
                                            echo "<p>Please ensure that your first name has letters, dashes, apostrophes and whitespaces only.</p>";
                                        }
                                        else {
                                            $first_name = $_POST['first_name'];
                                            if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['middle_name'])) {
                                                echo "<p>Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only.</p>";
                                            }
                                            else {
                                                $middle_name = $_POST['middle_name'];
                                                if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['last_name'])) {
                                                    echo "<p>Please ensure that your last name has letters, dashes, apostrophes and whitespaces only.</p>";
                                                }
                                                else {
                                                    $last_name = $_POST['last_name'];
                                                    $filtered_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                                                    if (!filter_var($filtered_email, FILTER_VALIDATE_EMAIL)) {
                                                        echo "<p>Please enter a valid email address (e.g., yourname@example.com).</p>"; 
                                                    }
                                                    else {
                                                        if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
                                                            echo "<p>Please enter a 10 digit phone number (without special characters including whitespaces).</p>"; 
                                                        }
                                                        else {
                                                            $phone = $_POST['phone'];
                                                            function validateDate($birth_date, $format = 'Y-m-d') {
                                                                $d = DateTime::createFromFormat($format, $birth_date);
                                                                return $d && $d->format($format) == $birth_date;
                                                            }
                                                            $birth_date = $_POST['birth_date'];
                                                            if (!validateDate($birth_date)) {
                                                                echo "<p>Please enter a valid birth date.</p>"; 
                                                            }
                                                            else {                                                           
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("UPDATE users SET username = ?, password_expiration = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ? WHERE username = ?");
                                                                $stmt->bind_param("sissssssss", $_POST['username'], $password_expiration, $user_role, $first_name, $middle_name, $last_name, $filtered_email, $phone, $birth_date, $_COOKIE["username"]);
                                                                if ($stmt->execute()) {
                                                                    $decide_username = 1;
                                                                    setcookie("username", $_POST['username']);
                                                                    echo "<p>Changes have been made successfully.</p>"; 
                                                                }
                                                                else {
                                                                    echo "<p>Changes have not been made successfully.</p>"; 
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
                                echo "<p>Please select an appropriate value for the password expiration.</p>";
                            }
                            else {
                                $password_expiration = $_POST['password_expiration'];
                                if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                    echo "<p>Please select an appropriate user role.</p>";
                                }
                                else {
                                    $user_role = $_POST['user_role'];
                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['first_name'])) {
                                        echo "<p>Please ensure that your first name has letters, dashes, apostrophes and whitespaces only.</p>";
                                    }
                                    else {
                                        $first_name = $_POST['first_name'];
                                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['middle_name'])) {
                                            echo "<p>Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only.</p>";
                                        }
                                        else {
                                            $middle_name = $_POST['middle_name'];
                                            if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['last_name'])) {
                                                echo "<p>Please ensure that your last name has letters, dashes, apostrophes and whitespaces only.</p>";
                                            }
                                            else {
                                                $last_name = $_POST['last_name'];
                                                $filtered_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                                                if (!filter_var($filtered_email, FILTER_VALIDATE_EMAIL)) {
                                                    echo "<p>Please enter a valid email address (e.g., yourname@example.com).</p>"; 
                                                }
                                                else {
                                                    if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
                                                        echo "<p>Please enter a 10 digit phone number (without special characters including whitespaces).</p>"; 
                                                    }
                                                    else {
                                                        $phone = $_POST['phone'];
                                                        function validateDate($birth_date, $format = 'Y-m-d') {
                                                            $d = DateTime::createFromFormat($format, $birth_date);
                                                            return $d && $d->format($format) == $birth_date;
                                                        }
                                                        $birth_date = $_POST['birth_date'];
                                                        if (!validateDate($birth_date)) {
                                                            echo "<p>Please enter a valid birth date.</p>"; 
                                                        }
                                                        else {
                                                            include("database.php");
                                                            $stmt = $DBConnect->prepare("UPDATE users SET username = ?, password_expiration = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ? WHERE username = ?");
                                                            $stmt->bind_param("sissssssss", $_POST['username'], $password_expiration, $user_role, $first_name, $middle_name, $last_name, $filtered_email, $phone, $birth_date, $_COOKIE["username"]);
                                                            if ($stmt->execute()) {
                                                                $decide_username = 0;
                                                                echo "<p>Changes have been made successfully.</p>"; 
                                                            }
                                                            else {
                                                                echo "<p>Changes have not been made successfully.</p>"; 
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
                if (empty($_POST['middle_name'])) {
                    if (!ctype_alnum($_POST['username'])) {
                        echo "<p>Please ensure that your username is alphanumeric.</p>";
                    }
                    else {
                        if ($_POST['username'] != $_COOKIE["username"]) {
                            include("database.php");
                            $stmt = $DBConnect->prepare("SELECT username FROM users WHERE username = ?");
                            $stmt->bind_param("s", $_POST['username']); 
                            $stmt->execute();
                            $stmt->store_result();
                        
                            if ($stmt->num_rows > 0) {
                                echo "<p>This username is already taken. Please choose another username.</p>";
                            }
                            else {
                                if($_POST['password_expiration'] != 0 && $_POST['password_expiration'] != 1) {
                                    echo "<p>Please select an appropriate value for the password expiration.</p>";
                                }
                                else {
                                    $password_expiration = $_POST['password_expiration'];
                                    if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                        echo "<p>Please select an appropriate user role.</p>";
                                    }
                                    else {
                                        $user_role = $_POST['user_role'];
                                        $password = $_POST['password'];
                                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['first_name'])) {
                                            echo "<p>Please ensure that your first name has letters, dashes, apostrophes and whitespaces only.</p>";
                                        }
                                        else {
                                            $first_name = $_POST['first_name'];
                                            if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['last_name'])) {
                                                echo "<p>Please ensure that your last name has letters, dashes, apostrophes and whitespaces only.</p>";
                                            }
                                            else {
                                                $last_name = $_POST['last_name'];
                                                $filtered_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                                                if (!filter_var($filtered_email, FILTER_VALIDATE_EMAIL)) {
                                                    echo "<p>Please enter a valid email address (e.g., yourname@example.com).</p>"; 
                                                }
                                                else {
                                                    if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
                                                        echo "<p>Please enter a 10 digit phone number (without special characters including whitespaces).</p>"; 
                                                    }
                                                    else {
                                                        $phone = $_POST['phone'];
                                                        function validateDate($birth_date, $format = 'Y-m-d') {
                                                            $d = DateTime::createFromFormat($format, $birth_date);
                                                            return $d && $d->format($format) == $birth_date;
                                                        }
                                                        $birth_date = $_POST['birth_date'];
                                                        if (!validateDate($birth_date)) {
                                                            echo "<p>Please enter a valid birth date.</p>"; 
                                                        }
                                                        else {
                                                            include("database.php");
                                                            $stmt = $DBConnect->prepare("UPDATE users SET username = ?, password_expiration = ?, password = ?, user_role = ?, user_first_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ? WHERE username = ?");
                                                            $stmt->bind_param("sissssssss", $_POST['username'], $password_expiration, $hashed_password, $user_role, $first_name, $last_name, $filtered_email, $phone, $birth_date, $_COOKIE["username"]);
                                                            if ($stmt->execute()) {
                                                                $decide_username = 1;
                                                                setcookie("username", $_POST['username']);
                                                                echo "<p>Changes have been made successfully.</p>"; 
                                                            }
                                                            else {
                                                                echo "<p>Changes have not been made successfully.</p>"; 
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
                                echo "<p>Please select an appropriate value for the password expiration.</p>";
                            }
                            else {
                                $password_expiration = $_POST['password_expiration'];
                                if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                    echo "<p>Please select an appropriate user role.</p>";
                                }
                                else {
                                    $user_role = $_POST['user_role'];
                                    $password = $_POST['password'];
                                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['first_name'])) {
                                        echo "<p>Please ensure that your first name has letters, dashes, apostrophes and whitespaces only.</p>";
                                    }
                                    else {
                                        $first_name = $_POST['first_name'];
                                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['last_name'])) {
                                            echo "<p>Please ensure that your last name has letters, dashes, apostrophes and whitespaces only.</p>";
                                        }
                                        else {
                                            $last_name = $_POST['last_name'];
                                            $filtered_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                                            if (!filter_var($filtered_email, FILTER_VALIDATE_EMAIL)) {
                                                echo "<p>Please enter a valid email address (e.g., yourname@example.com).</p>"; 
                                            }
                                            else {
                                                if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
                                                    echo "<p>Please enter a 10 digit phone number (without special characters including whitespaces).</p>"; 
                                                }
                                                else {
                                                    $phone = $_POST['phone'];
                                                    function validateDate($birth_date, $format = 'Y-m-d') {
                                                        $d = DateTime::createFromFormat($format, $birth_date);
                                                        return $d && $d->format($format) == $birth_date;
                                                    }
                                                    $birth_date = $_POST['birth_date'];
                                                    if (!validateDate($birth_date)) {
                                                        echo "<p>Please enter a valid birth date.</p>"; 
                                                    }
                                                    else {                                                   
                                                        include("database.php");
                                                        $stmt = $DBConnect->prepare("UPDATE users SET username = ?, password_expiration = ?, password = ?, user_role = ?, user_first_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date = ? WHERE username = ?");
                                                        $stmt->bind_param("sissssssss", $_POST['username'], $password_expiration, $hashed_password, $user_role, $first_name, $last_name, $filtered_email, $phone, $birth_date, $_COOKIE["username"]);
                                                        if ($stmt->execute()) {
                                                            $decide_username = 0;
                                                            echo "<p>Changes have been made successfully.</p>"; 
                                                        }
                                                        else {
                                                            echo "<p>Changes have not been made successfully.</p>"; 
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
                    if (!ctype_alnum($_POST['username'])) {
                        echo "<p>Please ensure that your username is alphanumeric.</p>";
                    }
                    else {
                        if ($_POST['username'] != $_COOKIE["username"]) {
                            include("database.php");
                            $stmt = $DBConnect->prepare("SELECT username FROM users WHERE username = ?");
                            $stmt->bind_param("s", $_POST['username']); 
                            $stmt->execute();
                            $stmt->store_result();
                        
                            if ($stmt->num_rows > 0) {
                                echo "<p>This username is already taken. Please choose another username.</p>";
                            }
                            else {
                                if($_POST['password_expiration'] != 0 && $_POST['password_expiration'] != 1) {
                                    echo "<p>Please select an appropriate value for the password expiration.</p>";
                                }
                                else {
                                    $password_expiration = $_POST['password_expiration'];
                                    if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                        echo "<p>Please select an appropriate user role.</p>";
                                    }
                                    else {
                                        $user_role = $_POST['user_role'];
                                        $password = $_POST['password'];
                                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['first_name'])) {
                                            echo "<p>Please ensure that your first name has letters, dashes, apostrophes and whitespaces only.</p>";
                                        }
                                        else {
                                            $first_name = $_POST['first_name'];
                                            if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['middle_name'])) {
                                                echo "<p>Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only.</p>";
                                            }
                                            else {
                                                $middle_name = $_POST['middle_name'];
                                                if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['last_name'])) {
                                                    echo "<p>Please ensure that your last name has letters, dashes, apostrophes and whitespaces only.</p>";
                                                }
                                                else {
                                                    $last_name = $_POST['last_name'];
                                                    $filtered_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                                                    if (!filter_var($filtered_email, FILTER_VALIDATE_EMAIL)) {
                                                        echo "<p>Please enter a valid email address (e.g., yourname@example.com).</p>"; 
                                                    }
                                                    else {
                                                        if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
                                                            echo "<p>Please enter a 10 digit phone number (without special characters including whitespaces).</p>"; 
                                                        }
                                                        else {
                                                            $phone = $_POST['phone'];
                                                            function validateDate($birth_date, $format = 'Y-m-d') {
                                                                $d = DateTime::createFromFormat($format, $birth_date);
                                                                return $d && $d->format($format) == $birth_date;
                                                            }
                                                            $birth_date = $_POST['birth_date'];
                                                            if (!validateDate($birth_date)) {
                                                                echo "<p>Please enter a valid birth date.</p>"; 
                                                            }
                                                            else {                                                           
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("UPDATE users SET username = ?, password_expiration = ?, password = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ? WHERE username = ?");
                                                                $stmt->bind_param("sisssssssss", $_POST['username'], $password_expiration, $hashed_password, $user_role, $first_name, $middle_name, $last_name, $filtered_email, $phone, $birth_date, $_COOKIE["username"]);
                                                                if ($stmt->execute()) {
                                                                    $decide_username = 1;
                                                                    setcookie("username", $_POST['username']);
                                                                    echo "<p>Changes have been made successfully.</p>"; 
                                                                }
                                                                else {
                                                                    echo "<p>Changes have not been made successfully.</p>"; 
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
                                echo "<p>Please select an appropriate value for the password expiration.</p>";
                            }
                            else {
                                $password_expiration = $_POST['password_expiration'];
                                if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                    echo "<p>Please select an appropriate user role.</p>";
                                }
                                else {
                                    $user_role = $_POST['user_role'];
                                    $password = $_POST['password'];
                                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['first_name'])) {
                                        echo "<p>Please ensure that your first name has letters, dashes, apostrophes and whitespaces only.</p>";
                                    }
                                    else {
                                        $first_name = $_POST['first_name'];
                                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['middle_name'])) {
                                            echo "<p>Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only.</p>";
                                        }
                                        else {
                                            $middle_name = $_POST['middle_name'];
                                            if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['last_name'])) {
                                                echo "<p>Please ensure that your last name has letters, dashes, apostrophes and whitespaces only.</p>";
                                            }
                                            else {
                                                $last_name = $_POST['last_name'];
                                                $filtered_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                                                if (!filter_var($filtered_email, FILTER_VALIDATE_EMAIL)) {
                                                    echo "<p>Please enter a valid email address (e.g., yourname@example.com).</p>"; 
                                                }
                                                else {
                                                    if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
                                                        echo "<p>Please enter a 10 digit phone number (without special characters including whitespaces).</p>"; 
                                                    }
                                                    else {
                                                        $phone = $_POST['phone'];
                                                        function validateDate($birth_date, $format = 'Y-m-d') {
                                                            $d = DateTime::createFromFormat($format, $birth_date);
                                                            return $d && $d->format($format) == $birth_date;
                                                        }
                                                        $birth_date = $_POST['birth_date'];
                                                        if (!validateDate($birth_date)) {
                                                            echo "<p>Please enter a valid birth date.</p>"; 
                                                        }
                                                        else {
                                                            include("database.php");
                                                            $stmt = $DBConnect->prepare("UPDATE users SET username = ?, password_expiration = ?, password = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ? WHERE username = ?");
                                                            $stmt->bind_param("sisssssssss", $_POST['username'], $password_expiration, $hashed_password, $user_role, $first_name, $middle_name, $last_name, $filtered_email, $phone, $birth_date, $_COOKIE["username"]);
                                                            if ($stmt->execute()) {
                                                                $decide_username = 0;
                                                                echo "<p>Changes have been made successfully.</p>"; 
                                                            }
                                                            else {
                                                                echo "<p>Changes have not been made successfully.</p>"; 
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
            $stmt->close();
            $DBConnect->close();
        }
        $username = $decide_username == 0 ? $_COOKIE["username"] : $_POST['username'];
        include("database.php");
        $stmt = $DBConnect->prepare("SELECT username, password_expiration, user_role, user_first_name, user_middle_name, user_last_name, user_email, user_phone, user_birth_date FROM users WHERE username = ?");
        $stmt->bind_param("s", $username); 
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($retrieved_username, $retrieved_password_expiration, $retrieved_user_role, $retrieved_first_name, $retrieved_middle_name, $retrieved_last_name, $retrieved_email, $retrieved_phone, $retrieved_birth_date);
        $stmt->fetch();
        $minimum_year = date("Y") - 75; 
        $maximum_year = date("Y") - 16;
        echo
        "<form method=\"post\" action=\"#\">
            <label for=\"username\">Username:</label>
            <input type=\"text\" id=\"username\" name=\"username\" pattern=\"[a-zA-Z0-9]+\" title=\"Please ensure that your username is alphanumeric\" value=\""; if (isset($_POST['edit_user_submit']) && isset($retrieved_username)) {echo $retrieved_username;} echo "\" required /><br /><br />
            <label for=\"password_expiration\">Password Expiration:</label>
            <select id=\"password_expiration\" name=\"password_expiration\" required>
                <option value=\"\">&nbsp;</option>
                <option value=\"0\" "; if (isset($_POST['edit_user_submit']) && isset($retrieved_password_expiration) && $retrieved_password_expiration == 0) {echo "selected";} echo ">0</option>
                <option value=\"1\" "; if (isset($_POST['edit_user_submit']) && isset($retrieved_password_expiration) && $retrieved_password_expiration == 1) {echo "selected";} echo ">1</option>
            </select><br /><br />
            <label for=\"user_role\">User role:</label>
            <select id=\"user_role\" name=\"user_role\" required>
                <option value=\"\">&nbsp;</option>
                <option value=\"user\" "; if (isset($_POST['edit_user_submit']) && isset($retrieved_user_role) && $retrieved_user_role == "user") {echo "selected";} echo ">User</option>
                <option value=\"recruiter\" "; if (isset($_POST['edit_user_submit']) && isset($retrieved_user_role) && $retrieved_user_role == "recruiter") {echo "selected";} echo ">Recruiter</option>
                <option value=\"administrator\" "; if (isset($_POST['edit_user_submit']) && isset($retrieved_user_role) && $retrieved_user_role == "administrator") {echo "selected";} echo ">Administrator</option>
            </select><br /><br />
            <label for=\"password\">Password:</label>
            <input type=\"password\" id=\"password\" name= \"password\" />&nbsp;&nbsp;<input type=\"checkbox\" onclick=\"passwordVisibility('password')\" />Show Password <br /><br />
            <label for=\"first_name\">First name:</label>
            <input type=\"text\" id=\"first_name\" name= \"first_name\" pattern=\"[a-zA-Z-'\s]*$\" title=\"Please ensure that your first name has letters, dashes, apostrophes and whitespaces only\" value=\""; if (isset($_POST['edit_user_submit']) && isset($retrieved_first_name)) {echo $retrieved_first_name;} echo "\" required /><br /><br />
            <label for=\"middle_name\">Middle name:</label>
            <input type=\"text\" id=\"middle_name\" name= \"middle_name\" pattern=\"[a-zA-Z-'\s]*$\" title=\"Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only\" value=\""; if (isset($_POST['edit_user_submit']) && isset($retrieved_middle_name)) {echo $retrieved_middle_name;} echo "\" /><br /><br />
            <label for=\"last_name\">Last name:</label>
            <input type=\"text\" id=\"last_name\" name= \"last_name\" pattern=\"[a-zA-Z-'\s]*$\" title=\"Please ensure that your last name has letters, dashes, apostrophes and whitespaces only\" value=\""; if (isset($_POST['edit_user_submit']) && isset($retrieved_last_name)) {echo $retrieved_last_name;} echo "\" required /><br /><br />
            <label for=\"email\">Email:</label>
            <input type=\"email\" id=\"email\" name= \"email\" pattern=\"[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$\" title=\"Please enter a valid email address (e.g., yourname@example.com)\" value=\""; if (isset($_POST['edit_user_submit']) && isset($retrieved_email)) {echo $retrieved_email;} echo "\" required /><br /><br />
            <label for=\"phone\">Phone number:</label>
            <input type=\"tel\" id=\"phone\" name= \"phone\" pattern=\"[0-9]{10}\" title=\"Please enter a 10 digit phone number (without special characters including whitespaces)\" value=\""; if (isset($_POST['edit_user_submit']) && isset($retrieved_phone)) {echo $retrieved_phone;} echo "\" required /><br /><br />
            <label for=\"birth_date\">Birth date:</label>
            <input type=\"date\" id=\"birth_date\" name= \"birth_date\" min=\"$minimum_year-01-01\"  max=\"$maximum_year-01-01\" required /><br /><br />
            <input type=\"submit\" name=\"edit_user_submit\" value=\"Submit Changes\" />
        </form>";
        
        if (!isset($_POST['edit_user_submit'])) {
            echo 
            "<script>
                document.getElementById('username').value = retrieveCookie('username');
                document.getElementById('password_expiration').value = retrieveCookie('password_expiration');
                document.getElementById('user_role').value = retrieveCookie('user_role');
                document.getElementById('first_name').value = retrieveCookie('first_name');
                document.getElementById('middle_name').value = retrieveCookie('middle_name');
                document.getElementById('last_name').value = retrieveCookie('last_name');
                document.getElementById('email').value = retrieveCookie('email');
                document.getElementById('phone').value = retrieveCookie('phone');
                document.getElementById('birth_date').value = retrieveCookie('birth_date');
            </script>";
        }
        else {
            echo 
            "<script>
                document.getElementById('birth_date').value = \""; if (isset($retrieved_birth_date)) {echo $retrieved_birth_date;} echo "\";
            </script>";
        }
        $stmt->close();
        $DBConnect->close();
    ?>
    </body>
</html> 