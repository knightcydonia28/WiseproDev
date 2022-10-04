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
        ?>
        <meta charset="UTF-8" />
        <title>Edit User</title>
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
        <style>
            label {
                display: inline-block;
                width: 100px;
            }
            .error {
                color: #FF0000;
            }
        </style>
    </head>
    <body>
    <a href="home.php">Home</a><br /><br />
    <form method="post" action="#">
        <input type="submit" name="logout" value="Logout" /><br /><br />
    </form>
    <h2>Edit User</h2>
    <p>Please utilize the form below to make changes to the selected account:</p>
    <p><span class="error">* required field</span></p>
    <?php
        if (isset($_POST['logout'])) {
            include("logout.php");
            logout();
        }
        if (isset($_POST['edit_user_submit'])) {
            
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
                    if (!ctype_alnum($_POST['username'])) {
                        $username_error = "Please ensure that your username is alphanumeric";
                    }
                    else {
                        $username = test_input($_POST['username']); 
                        if($_POST['password_expiration'] != 0 && $_POST['password_expiration'] != 1) {
                            $password_expiration_error = "Please select an appropriate value for the password expiration";
                        }
                        else {
                            $password_expiration = test_input($_POST['password_expiration']);
                            if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                $user_role_error = "Please select an appropriate user role";
                            }
                            else {
                                $user_role = test_input($_POST['user_role']);
                                if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_first_name'])) {
                                    $user_first_name_error = "Please ensure that your first name has letters, dashes, apostrophes and whitespaces only";
                                }
                                else {
                                    $user_first_name = test_input($_POST['user_first_name']);
                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_last_name'])) {
                                        $user_last_name_error = "Please ensure that your last name has letters, dashes, apostrophes and whitespaces only";
                                    }
                                    else {
                                        $user_last_name = test_input($_POST['user_last_name']);
                                        $user_email = test_input($_POST['user_email']);
                                        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                                            $user_email_error = "Please enter a valid email address (e.g., yourname@example.com)"; 
                                        }
                                        else {
                                            if (!preg_match("/^[0-9]{10}$/", $_POST['user_phone'])) {
                                                $user_phone_error = "Please enter a 10 digit phone number (without special characters including whitespaces)";
                                            }
                                            else {
                                                $user_phone = test_input($_POST['user_phone']);
                                                $user_birth_date = test_input($_POST['user_birth_date']);
                                                if (!validateDate($user_birth_date)) {
                                                    $user_birth_date_error = "Please enter a valid birth date"; 
                                                }
                                                else {
                                                    if ($_POST['user_status'] != "active" && $_POST['user_status'] != "inactive") {
                                                        $user_status_error = "Please select an appropriate user status";
                                                    }
                                                    else {
                                                        $user_status = test_input($_POST['user_status']);
                                                        if ($_POST['secret_key'] != 0 && $_POST['secret_key'] != 1) {
                                                            $secret_key_error = "Please select an appropriate value for the secret key";
                                                        }
                                                        else {
                                                            $secret_key = test_input($_POST['secret_key']);
                                                            if ($secret_key == 0) {
                                                                $secret_key = NULL;
                                                                $user_middle_name = NULL;                                          
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date = ?, user_status = ?, secret_key = ? WHERE username = ?");
                                                                $stmt->bind_param("issssssssis", $password_expiration, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $secret_key, $username);
                                                                if ($stmt->execute()) {
                                                                    echo "<p>Changes have been made successfully.</p>"; 
                                                                }
                                                                else {
                                                                    echo "<p>Changes have not been made successfully.</p>"; 
                                                                }
                                                            }
                                                            else {
                                                                $user_middle_name = NULL;                                          
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date = ?, user_status = ? WHERE username = ?");
                                                                $stmt->bind_param("isssssssss", $password_expiration, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $username);
                                                                if ($stmt->execute()) {
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
                    if (!ctype_alnum($_POST['username'])) {
                        $username_error = "Please ensure that your username is alphanumeric";
                    }
                    else {
                        $username = test_input($_POST['username']);
                        if($_POST['password_expiration'] != 0 && $_POST['password_expiration'] != 1) {
                            $password_expiration_error = "Please select an appropriate value for the password expiration";
                        }
                        else {
                            $password_expiration = test_input($_POST['password_expiration']);
                            if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                $user_role_error = "Please select an appropriate user role";
                            }
                            else {
                                $user_role = test_input($_POST['user_role']);
                                if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_first_name'])) {
                                    $user_first_name_error = "Please ensure that your first name has letters, dashes, apostrophes and whitespaces only";
                                }
                                else {
                                    $user_first_name = test_input($_POST['user_first_name']);
                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_middle_name'])) {
                                        echo "<p>Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only.</p>";
                                    }
                                    else {
                                        $user_middle_name = $_POST['user_middle_name'];
                                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_last_name'])) {
                                            $user_last_name_error = "Please ensure that your last name has letters, dashes, apostrophes and whitespaces only";
                                        }
                                        else {
                                            $user_last_name = test_input($_POST['user_last_name']);
                                            $user_email = test_input($_POST['user_email']);
                                            if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                                                $user_email_error = "Please enter a valid email address (e.g., yourname@example.com)"; 
                                            }
                                            else {
                                                if (!preg_match("/^[0-9]{10}$/", $_POST['user_phone'])) {
                                                    $user_phone_error = "Please enter a 10 digit phone number (without special characters including whitespaces)";
                                                }
                                                else {
                                                    $user_phone = test_input($_POST['user_phone']);
                                                    $user_birth_date = test_input($_POST['user_birth_date']);
                                                    if (!validateDate($user_birth_date)) {
                                                        $user_birth_date_error = "Please enter a valid birth date"; 
                                                    }
                                                    else {
                                                        if ($_POST['user_status'] != "active" && $_POST['user_status'] != "inactive") {
                                                        $user_status_error = "Please select an appropriate user status";
                                                        }
                                                        else {
                                                            $user_status = test_input($_POST['user_status']);
                                                            if ($_POST['secret_key'] != 0 && $_POST['secret_key'] != 1) {
                                                                $secret_key_error = "Please select an appropriate value for the secret key";
                                                            }
                                                            else {
                                                                $secret_key = test_input($_POST['secret_key']);
                                                                if ($secret_key == 0) {
                                                                    $secret_key = NULL;
                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ?, user_status = ?, secret_key = ? WHERE username = ?");
                                                                    $stmt->bind_param("issssssssis", $password_expiration, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $secret_key, $username);
                                                                    if ($stmt->execute()) {
                                                                        echo "<p>Changes have been made successfully.</p>"; 
                                                                    }
                                                                    else {
                                                                        echo "<p>Changes have not been made successfully.</p>"; 
                                                                    }
                                                                }
                                                                else {
                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ?, user_status = ? WHERE username = ?");
                                                                    $stmt->bind_param("isssssssss", $password_expiration, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $username);
                                                                    if ($stmt->execute()) {
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
                }
            }
            else {
                if (empty($_POST['user_middle_name'])) {
                    if (!ctype_alnum($_POST['username'])) {
                        $username_error = "Please ensure that your username is alphanumeric";
                    }
                    else {
                        $username = test_input($_POST['username']);
                        if($_POST['password_expiration'] != 0 && $_POST['password_expiration'] != 1) {
                            $password_expiration_error = "Please select an appropriate value for the password expiration";
                        }
                        else {
                            $password_expiration = test_input($_POST['password_expiration']);
                            if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                $user_role_error = "Please select an appropriate user role";
                            }
                            else {
                                $user_role = test_input($_POST['user_role']);
                                $password = $_POST['password'];
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_first_name'])) {
                                    $user_first_name_error = "Please ensure that your first name has letters, dashes, apostrophes and whitespaces only";
                                }
                                else {
                                    $user_first_name = test_input($_POST['user_first_name']);
                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_last_name'])) {
                                        $user_last_name_error = "Please ensure that your last name has letters, dashes, apostrophes and whitespaces only";
                                    }
                                    else {
                                        $user_last_name = test_input($_POST['user_last_name']);
                                        $user_email = test_input($_POST['user_email']);
                                        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                                            $user_email_error = "Please enter a valid email address (e.g., yourname@example.com)"; 
                                        }
                                        else {
                                            if (!preg_match("/^[0-9]{10}$/", $_POST['user_phone'])) {
                                                $user_phone_error = "Please enter a 10 digit phone number (without special characters including whitespaces)";
                                            }
                                            else {
                                                $user_phone = test_input($_POST['user_phone']);                                      
                                                $user_birth_date = test_input($_POST['user_birth_date']);
                                                if (!validateDate($user_birth_date)) {
                                                    $user_birth_date_error = "Please enter a valid birth date"; 
                                                }
                                                else {
                                                    if ($_POST['user_status'] != "active" && $_POST['user_status'] != "inactive") {
                                                        $user_status_error = "Please select an appropriate user status";
                                                    }
                                                    else {
                                                        $user_status = test_input($_POST['user_status']);
                                                        if ($_POST['secret_key'] != 0 && $_POST['secret_key'] != 1) {
                                                            $secret_key_error = "Please select an appropriate value for the secret key";
                                                        }
                                                        else {
                                                            $secret_key = test_input($_POST['secret_key']);
                                                            if ($secret_key == 0) {
                                                                $secret_key = NULL;
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, password = ?, user_role = ?, user_first_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date = ?, user_status = ?, secret_key = ? WHERE username = ?");
                                                                $stmt->bind_param("issssssssis", $password_expiration, $hashed_password, $user_role, $user_first_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $secret_key, $username);
                                                                if ($stmt->execute()) {
                                                                    echo "<p>Changes have been made successfully.</p>"; 
                                                                }
                                                                else {
                                                                    echo "<p>Changes have not been made successfully.</p>"; 
                                                                }
                                                            }
                                                            else {
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, password = ?, user_role = ?, user_first_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date = ?, user_status = ? WHERE username = ?");
                                                                $stmt->bind_param("isssssssss", $password_expiration, $hashed_password, $user_role, $user_first_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $username);
                                                                if ($stmt->execute()) {
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
                    if (!ctype_alnum($_POST['username'])) {
                        $username_error = "Please ensure that your username is alphanumeric";
                    }
                    else {
                        $username = test_input($_POST['username']);                   
                        if($_POST['password_expiration'] != 0 && $_POST['password_expiration'] != 1) {
                            $password_expiration_error = "Please select an appropriate value for the password expiration";
                        }
                        else {
                            $password_expiration = test_input($_POST['password_expiration']);
                            if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                                $user_role_error = "Please select an appropriate user role";
                            }
                            else {
                                $user_role = test_input($_POST['user_role']);
                                $password = $_POST['password'];
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_first_name'])) {
                                    $user_first_name_error = "Please ensure that your first name has letters, dashes, apostrophes and whitespaces only";
                                }
                                else {
                                    $user_first_name = test_input($_POST['user_first_name']);
                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_middle_name'])) {
                                        echo "<p>Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only.</p>";
                                    }
                                    else {
                                        $user_middle_name = $_POST['user_middle_name'];
                                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_last_name'])) {
                                            $user_last_name_error = "Please ensure that your last name has letters, dashes, apostrophes and whitespaces only";
                                        }
                                        else {
                                            $user_last_name = test_input($_POST['user_last_name']);
                                            $user_email = test_input($_POST['user_email']);
                                            if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                                                $user_email_error = "Please enter a valid email address (e.g., yourname@example.com)"; 
                                            }
                                            else {
                                                if (!preg_match("/^[0-9]{10}$/", $_POST['user_phone'])) {
                                                    $user_phone_error = "Please enter a 10 digit phone number (without special characters including whitespaces)";
                                                }
                                                else {
                                                    $user_phone = test_input($_POST['user_phone']);
                                                    $user_birth_date = test_input($_POST['user_birth_date']);
                                                    if (!validateDate($user_birth_date)) {
                                                        $user_birth_date_error = "Please enter a valid birth date"; 
                                                    }
                                                    else {
                                                        if ($_POST['user_status'] != "active" && $_POST['user_status'] != "inactive") {
                                                        $user_status_error = "Please select an appropriate user status";
                                                        }
                                                        else {
                                                            $user_status = test_input($_POST['user_status']);
                                                            if ($_POST['secret_key'] != 0 && $_POST['secret_key'] != 1) {
                                                                $secret_key_error = "Please select an appropriate value for the secret key";
                                                            }
                                                            else {
                                                                $secret_key = test_input($_POST['secret_key']);
                                                                if ($secret_key == 0) {
                                                                    $secret_key = NULL;
                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, password = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ?, user_status = ?, secret_key = ? WHERE username = ?");
                                                                    $stmt->bind_param("isssssssssis", $password_expiration, $hashed_password, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $secret_key, $username);
                                                                    if ($stmt->execute()) {
                                                                        echo "<p>Changes have been made successfully.</p>"; 
                                                                    }
                                                                    else {
                                                                        echo "<p>Changes have not been made successfully.</p>"; 
                                                                    }
                                                                }
                                                                else {}
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, password = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date= ?, user_status = ? WHERE username = ?");
                                                                $stmt->bind_param("issssssssss", $password_expiration, $hashed_password, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status, $username);
                                                                if ($stmt->execute()) {
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
            }
            include("database.php");
            $stmt = $DBConnect->prepare("SELECT username, password_expiration, user_role, user_first_name, user_middle_name, user_last_name, user_email, user_phone, user_birth_date, user_status, secret_key FROM users WHERE username = ?");
            $stmt->bind_param("s", $username); 
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($retrieved_username, $retrieved_password_expiration, $retrieved_user_role, $retrieved_user_first_name, $retrieved_user_middle_name, $retrieved_user_last_name, $retrieved_user_email, $retrieved_user_phone, $retrieved_user_birth_date, $retrieved_user_status, $retrieved_secret_key);
            $stmt->fetch();
            if ($retrieved_secret_key == NULL) {
                $retrieved_secret_key = 0;
            }
            else {
                $retrieved_secret_key = 1;
            }
            setcookie("password_expiration", $retrieved_password_expiration);
            setcookie("user_role", $retrieved_user_role);
            setcookie("user_first_name", $retrieved_user_first_name);
            if ($retrieved_user_middle_name == NULL) {
                setcookie("user_middle_name", 0);
            }
            else {
                setcookie("user_middle_name", $retrieved_user_middle_name);
            }
            setcookie("user_last_name", $retrieved_user_last_name);
            setcookie("user_email", $retrieved_user_email);
            setcookie("user_phone", $retrieved_user_phone);
            setcookie("user_birth_date", $retrieved_user_birth_date);
            setcookie("user_status", $retrieved_user_status);
            if ($retrieved_secret_key == NULL) {
                $retrieved_secret_key = 0;
            }
            else {
                $retrieved_secret_key = 1;
            }
            setcookie("secret_key", $retrieved_secret_key);
            $stmt->close();
            $DBConnect->close();
        }
    ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric" value="<?php echo $_COOKIE["username"]; ?>" readonly required /><span class="error"> * <?php echo $username_error; ?></span><br /><br />
            <label for="password_expiration">Password Expiration:</label>
            <select id="password_expiration" name="password_expiration" required>
                <option value="">&nbsp;</option>
                <option value="0" <?php if ($_COOKIE["password_expiration"] == 0) {echo "selected";} ?>>0</option>
                <option value="1" <?php if ($_COOKIE["password_expiration"] == 1) {echo "selected";} ?>>1</option>
            </select><span class="error"> * <?php echo $password_expiration_error; ?></span><br /><br />
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" />&nbsp;&nbsp;<input type="checkbox" onclick="passwordVisibility('password')" />Show Password <br /><br />
            <label for="user_role">User Role:</label>
            <select id="user_role" name="user_role" required>
                <option value="">&nbsp;</option>
                <option value="user" <?php if ($_COOKIE["user_role"] == "user") {echo "selected";} ?>>User</option>
                <option value="recruiter" <?php if ($_COOKIE["user_role"] == "recruiter") {echo "selected";} ?>>Recruiter</option>
                <option value="administrator" <?php if ($_COOKIE["user_role"] == "administrator") {echo "selected";} ?>>Administrator</option>
            </select><span class="error"> * <?php echo $user_role_error; ?></span><br /><br />
            <label for="user_first_name">First name:</label>
            <input type="text" id="user_first_name" name="user_first_name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your first name has letters, dashes, apostrophes and whitespaces only" value="<?php echo $_COOKIE["user_first_name"]; ?>" required /><span class="error"> * <?php echo $user_first_name_error; ?></span><br /><br />
            <label for="user_middle_name">Middle Name:</label>
            <input type="text" id="user_middle_name" name="user_middle_name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only" value="<?php if ($_COOKIE["user_middle_name"] == "0") {echo NULL;} else {echo $_COOKIE["user_middle_name"];} ?>" /><span class="error"> <?php echo $user_middle_name_error; ?></span><br /><br />
            <label for="user_last_name">Last Name:</label>
            <input type="text" id="user_last_name" name="user_last_name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your last name has letters, dashes, apostrophes and whitespaces only" value="<?php echo $_COOKIE["user_last_name"]; ?>" required /><span class="error"> * <?php echo $user_last_name_error; ?></span><br /><br />
            <label for="user_email">Email:</label>
            <input type="email" id="user_email" name="user_email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address (e.g., yourname@example.com)" value="<?php echo $_COOKIE["user_email"]; ?>" required /><span class="error"> * <?php echo $user_email_error; ?></span><br /><br />
            <label for="phone">Phone Number:</label>
            <input type="tel" id="user_phone" name="user_phone" pattern="[0-9]{10}" title="Please enter a 10 digit phone number (without special characters including whitespaces)" value="<?php echo $_COOKIE["user_phone"]; ?>" required /><span class="error"> * <?php echo $user_phone_error; ?></span><br /><br />
            <label for="user_birth_date">Birth Date:</label>
            <?php $minimum_year = date("Y") - 75; $maximum_year = date("Y") - 16; ?>
            <input type="date" id="user_birth_date" name="user_birth_date" min="<?php echo "$minimum_year-01-01" ?>" max="<?php echo "$maximum_year-01-01"; ?>" required /><span class="error"> * <?php echo $user_birth_date_error; ?></span><br /><br />
            <label for="user_status">User Status:</label>
            <select id="user_status" name="user_status" required>
                <option value="">&nbsp;</option>
                <option value="active" <?php if ($_COOKIE["user_status"] == "active") {echo "selected";} ?>>Active</option>
                <option value="inactive" <?php if ($_COOKIE["user_status"] == "inactive") {echo "selected";} ?>>Inactive</option>
            </select><span class="error"> * <?php echo $user_status_error; ?></span><br /><br />
            <label for="secret_key">Secret Key:</label>
            <select id="secret_key" name="secret_key" required>
                <option value="">&nbsp;</option>
                <option value="0" <?php if ($_COOKIE["secret_key"] == 0) {echo "selected";} ?>>0</option>
                <option value="1" <?php if ($_COOKIE["secret_key"] == 1) {echo "selected";} ?>>1</option>
            </select><span class="error"> * <?php echo $secret_key_error; ?></span><br /><br />
            <input type="submit" name="edit_user_submit" value="Submit Changes" />
        </form>
    <?php
        if (isset($_POST['edit_user_submit'])) {
            echo 
            "<script>
                document.getElementById('user_birth_date').value = \"".$retrieved_user_birth_date."\";
            </script>";
        }
        else {
            echo
            "<script>
                document.getElementById('user_birth_date').value = \"".$_COOKIE["user_birth_date"]."\";
            </script>";
        }
    ?>
    </body>
</html>