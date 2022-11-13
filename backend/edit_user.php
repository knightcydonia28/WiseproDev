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
            include("session_timeout.php");
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
                
                function testInput($data) {
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                }

                function validateDate($date, $format = 'Y-m-d') {
                    $d = DateTime::createFromFormat($format, $date);
                    return $d && $d->format($format) == $date;
                }

                function validatePasswordExpiration($provided_password_expiration) {
                    $provided_password_expiration = testInput($provided_password_expiration);
                    if($provided_password_expiration != 0 && $provided_password_expiration != 1) {
                        $_SESSION['password_expiration_error'] = "<p class=\"error\">Please select an appropriate value for the password expiration</p>";
                        header("Location: edit_user_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_password_expiration;
                    }
                }

                function validateUserRole($provided_user_role) {
                    $provided_user_role = testInput($provided_user_role);
                    if ($provided_user_role != "user" && $provided_user_role != "recruiter" && $provided_user_role != "administrator") {
                        $_SESSION["user_role_error"] = "<p class=\"error\">Please select an appropriate user role</p>";
                        header("Location: edit_user_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_user_role;
                    }
                }

                function validateUserFirstName($provided_user_first_name) {
                    $provided_user_first_name = testInput($provided_user_first_name);
                    if (!preg_match("/^[a-zA-Z-' ]*$/", $provided_user_first_name)) {
                        $_SESSION["user_first_name_error"] = "<p class=\"error\">Please ensure that your first name has letters, dashes, apostrophes and whitespaces only</p>";
                        header("Location: edit_user_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_user_first_name;
                    }
                }

                function validateUserMiddleName($provided_user_middle_name) {
                    $provided_user_middle_name = testInput($provided_user_middle_name);
                    if (!preg_match("/^[a-zA-Z-' ]*$/", $provided_user_middle_name)) {
                        $_SESSION["user_middle_name_error"] = "<p class=\"error\">Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only</p>";
                        header("Location: edit_user_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_user_middle_name;
                    }
                }

                function validateUserLastName($provided_user_last_name) {
                    $provided_user_last_name = testInput($provided_user_last_name);
                    if (!preg_match("/^[a-zA-Z-' ]*$/", $provided_user_last_name)) {
                        $_SESSION["user_last_name_error"] = "<p class=\"error\">Please ensure that your last name has letters, dashes, apostrophes and whitespaces only</p>";
                        header("Location: edit_user_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_user_last_name;
                    }
                }

                function validateUserEmail($provided_user_email) {
                    $provided_user_email = testInput($provided_user_email);
                    if (!filter_var($provided_user_email, FILTER_VALIDATE_EMAIL)) {
                        $_SESSION["user_email_error"] = "<p class=\"error\">Please enter a valid email address (e.g., yourname@example.com)</p>";
                        header("Location: edit_user_procedural.php", true, 303);
                        exit(); 
                    }
                    else {
                        return $provided_user_email;
                    }
                }

                function validateUserPhone($provided_user_phone) {
                    $provided_user_phone = testInput($provided_user_phone);
                    if (!preg_match("/^[0-9]{10}$/", $provided_user_phone)) {
                        $_SESSION["user_phone_error"] = "<p class=\"error\">Please enter a 10 digit phone number (without special characters including whitespace)</p>";
                        header("Location: edit_user_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_user_phone;
                    }
                }

                function validateUserBirthDate($provided_user_birth_date) {
                    $provided_user_birth_date = testInput($provided_user_birth_date);
                    if (!validateDate($provided_user_birth_date)) {
                        $_SESSION["user_birth_date_error"] = "<p class=\"error\">Please enter a valid birth date</p>";
                        header("Location: edit_user_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_user_birth_date;
                    }
                }

                function validateUserStatus($provided_user_status) {
                    $provided_user_status = testInput($provided_user_status);
                    if (($provided_user_status != "active" && $provided_user_status != "inactive")) {
                        $_SESSION["user_status_error"] = "<p class=\"error\">Please ensure that the status of the user is active upon creation</p>";
                        header("Location: create_user_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_user_status;
                    }
                }

                function validateSecretKey($provided_secret_key) {
                    $provided_secret_key = testInput($provided_secret_key);
                    if ($provided_secret_key != 0 && $provided_secret_key != 1) {
                        $_SESSION['secret_key_error'] = "<p class=\"error\">Please select an appropriate value for the secret key</p>";
                        header("Location: edit_user_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_secret_key;
                    }
                }

                if (!empty($_POST['user_middle_name'])) {
                    $user_middle_name = validateUserMiddleName($_POST['user_middle_name']);
                }
                else {
                    $user_middle_name = NULL;
                }

                $username = $_COOKIE['username'];
                $password_expiration = validatePasswordExpiration($_POST['password_expiration']);
                $user_role = validateUserRole($_POST['user_role']);
                $user_first_name = validateUserFirstName($_POST['user_first_name']);
                $user_last_name = validateUserLastName($_POST['user_last_name']);
                $user_email = validateUserEmail($_POST['user_email']);
                $user_phone = validateUserPhone($_POST['user_phone']);
                $user_birth_date = validateUserBirthDate($_POST['user_birth_date']);
                $user_status = validateUserStatus($_POST['user_status']);
                $secret_key = validateSecretKey($_POST['secret_key']);

                $sql = "UPDATE users SET password_expiration = ?, user_role = ?, user_first_name = ?, user_middle_name = ?, user_last_name = ?, user_email = ?, user_phone = ?, user_birth_date = ?, user_status = ?";
                $where = " WHERE username = ?";
                $types = "isssssssss";
                $values = array($password_expiration, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status);

                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sql .= ", password = ?";
                    $types .= "s";
                    $values[] = $password;
                }

                if ($secret_key == 0) {
                    $secret_key = NULL;
                    $sql.=", secret_key = ?";
                    $types .= "s";
                    $values[] = $secret_key;
                }

                $sql = $sql.$where;
                $values[] = $username;
                
                include("database.php");
                $stmt = $DBConnect->prepare($sql);
                $stmt->bind_param($types, ...$values);
                if ($stmt->execute()) {
                    $_SESSION["edit_user_confirmation"] = "<p>Changes have been made successfully.</p>";
                    header("Location: edit_user_procedural.php", true, 303);
                    exit();
                }
                else {
                    $_SESSION["edit_user_error"] = "<p class=\"error\">Changes have not been made successfully.</p>";
                    header("Location: edit_user_procedural.php", true, 303);
                    exit();
                }
                $stmt->close();
                $DBConnect->close();
                
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
            <input type="text" id="username" name="username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric" value="<?php if (isset($_COOKIE["username"])) {echo $_COOKIE["username"];} ?>" readonly required><span class="error"> * </span><br><br>
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
