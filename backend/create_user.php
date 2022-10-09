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
    setcookie("search_user", "", time() - 3600);
    setcookie("choose_timesheet", "", time() - 3600);
    unset($_SESSION['disable_choose_timesheet']);
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
        <meta charset="UTF-8" />
        <title>Create User</title>
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
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>Logout</a>
        <h2>Create User</h2>
        <p>Please fill the form below to create an account:</p>
        <p><span class="error">* required field</span></p>
        <?php
            if (isset($_POST['create_another_user_submit'])) {
                header('Location: create_user.php');
                exit();
            }            
            if (isset($_POST['create_user_submit'])) {
                
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
                
                if (!ctype_alnum($_POST['username'])) {
                    $username_error = "Please ensure that your username is alphanumeric";
                }
                else {
                    $username = test_input($_POST['username']);

                    include("database.php");
                    $stmt = $DBConnect->prepare("SELECT username FROM users WHERE username = ?");
                    $stmt->bind_param("s", $username); 
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $username_error = "Username is already taken. Please choose another username";
                    }
                    else {
                        if($_POST['user_role'] != "user" && $_POST['user_role'] != "recruiter" && $_POST['user_role'] != "administrator") {
                            $user_role_error = "Please select an appropriate user role";
                        }
                        else {
                            $user_role = test_input($_POST['user_role']);
                            if (!preg_match("/^[a-zA-Z-' ]*$/", $_POST['user_first_name'])) {
                                $user_first_name_error = "Please ensure that your first name has letters, dashes, apostrophes and whitespaces only";
                            }
                            else {
                                $user_first_name = test_input($_POST['user_first_name']);
                                if (!preg_match("/^[a-zA-Z-' ]*$/", $_POST['user_last_name'])) {
                                    $user_last_name_error = "Please ensure that your last name has letters, dashes, apostrophes and whitespaces only";
                                }
                                else {
                                    $user_last_name = test_input($_POST['user_last_name']);
                                    $user_email = test_input($_POST['user_email']);
                                    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                                        $user_email_error = "Please enter a valid email address (e.g., yourname@example.com)"; 
                                    }
                                    else {
                                        $user_birth_date = test_input($_POST['user_birth_date']);
                                        if (!validateDate($user_birth_date)) {
                                            $user_birth_date_error = "Please enter a valid birth date"; 
                                        }
                                        else {
                                            $minimum_year = date("Y") - 75;
                                            if ($user_birth_date < "$minimum_year-01-01") {
                                                $user_birth_date_error = "Please ensure that birth date is not earlier than \"01/01/$minimum_year\"";  
                                            }
                                            else {
                                                $maximum_year = date("Y") - 16;
                                                if ($user_birth_date > "$maximum_year-01-01") {
                                                    $user_birth_date_error = "Please ensure that birth date is not later than \"01/01/$maximum_year\"";                                          
                                                }
                                                else {
                                                    if (!preg_match("/^[0-9]{10}$/", $_POST['user_phone'])) {
                                                        $user_phone_error = "Please enter a 10 digit phone number (without special characters including whitespace)"; 
                                                    }
                                                    else {
                                                        $user_phone = test_input($_POST['user_phone']);
                                                        if (($_POST['user_status'] != "active")) {
                                                            $user_status_error = "Please ensure that the status of the user is active upon creation";
                                                        }
                                                        else {
                                                            $user_status = test_input($_POST['user_status']);
                                                            $string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                                                            $temporary_password = str_shuffle($string);
                                                            $shortened_temporary_password = substr($temporary_password,0,15);
                                                            $hashed_temporary_password = password_hash($shortened_temporary_password, PASSWORD_DEFAULT);
                                                        
                                                            if (empty($_POST['user_middle_name'])) {
                                                                //Come back to encrypting the birth date.
                                                                $encryption_key = "random_key";
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("INSERT INTO users (username, password_expiration, password, user_role, user_first_name, user_last_name, user_email, user_phone, user_birth_date, user_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                $password_expiration = 0;
                                                                $stmt->bind_param("sissssssss", $username, $password_expiration, $hashed_temporary_password, $user_role, $user_first_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status);
                                                                if ($stmt->execute()) {                                                             
                                                                    echo 
                                                                    "<p>Account creation was successful.</p>
                                                                    <p>Temporary password: $shortened_temporary_password</p>";
                                                                    
                                                                    $to = $user_email;
                                                                    $subject = "Wisepro Account Temporary Password";
                                                                    $message = "Hi ".ucfirst(strtolower($user_first_name)).",\r\nThe temporary password for your account is: $shortened_temporary_password\r\nThanks,\r\nWisepro Administrative Team";
                                                                    $message = wordwrap($message, 70, "\r\n");
                                                                    $headers = array('From' => 'administration@wisepro.com', 'Reply-To' => 'administration@wisepro.com', 'X-Mailer' => 'PHP/' . phpversion());
                                                                    if (mail($to, $subject, $message, $headers)) {
                                                                        echo "<p>Email to user containing their temporary password was successfully accepted for delivery.</p>";
                                                                    }
                                                                    else {
                                                                        echo "<p>Email to user containing their temporary password was not accepted for delivery.</p>";
                                                                    }

                                                                    echo
                                                                    "<p>Create another user?</p>
                                                                    <form method=\"post\" action=\"#\">
                                                                        <input type=\"submit\" name=\"create_another_user_submit\" value=\"Create Another User\" />
                                                                    </form>
                                                                    <br />";
                                                                }
                                                                else {
                                                                    echo "<p>Account creation was unsuccessful.</p>";
                                                                }
                                                            }
                                                            else {
                                                                if (!preg_match("/^[a-zA-Z-' ]*$/", $_POST['user_middle_name'])) {
                                                                    $user_middle_name_error = "Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only";
                                                                }
                                                                else {
                                                                    $user_middle_name = test_input($_POST['user_middle_name']);
                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("INSERT INTO users (username, password_expiration, password, user_role, user_first_name, user_middle_name, user_last_name, user_email, user_phone, user_birth_date, user_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                    $password_expiration = 0;
                                                                    $stmt->bind_param("sisssssssss", $username, $password_expiration, $hashed_temporary_password, $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status);
                                                                    if ($stmt->execute()) {
                                                                        echo 
                                                                        "<p>Account creation was successful.</p>
                                                                        <p>Temporary password: $shortened_temporary_password</p>";
                                                                        
                                                                        $to = $user_email;
                                                                        $subject = "Wisepro Account Temporary Password";
                                                                        $message = "Hi ".ucfirst(strtolower($user_first_name)).",\r\nThe temporary password for your account is: $shortened_temporary_password\r\nThanks,\r\nWisepro Administrative Team";
                                                                        $message = wordwrap($message, 70, "\r\n");
                                                                        $headers = array('From' => 'administration@wisepro.com', 'Reply-To' => 'administration@wisepro.com', 'X-Mailer' => 'PHP/' . phpversion());
                                                                        if (mail($to, $subject, $message, $headers)) {
                                                                            echo "<p>Email to user containing their temporary password was successfully accepted for delivery.</p>";
                                                                        }
                                                                        else {
                                                                            echo "<p>Email to user containing their temporary password was not accepted for delivery.</p>";
                                                                        }

                                                                        echo
                                                                        "<p>Create another user?</p>
                                                                        <form method=\"post\" action=\"#\">
                                                                            <input type=\"submit\" name=\"create_another_user_submit\" value=\"Create Another User\" />
                                                                        </form>
                                                                        <br />";
                                                                    }
                                                                    else {
                                                                        echo "<p>Account creation was unsuccessful.</p>";
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
                $stmt->close();
                $DBConnect->close();
            }
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric" value="<?php echo $username ?>" required /><span class="error"> * <?php echo $username_error; ?></span><br /><br />
            <label for="user_role">User Role:</label>
            <select id="user_role" name="user_role" required>
                <option value="" <?php if (!isset($_POST['create_user_submit'])) {echo "selected";} ?> disabled>Select User Role</option>
                <option value="user" <?php if (isset($_POST['create_user_submit']) && isset($user_role) && $user_role == "user") {echo "selected";} ?>>User</option>
                <option value="recruiter" <?php if (isset($_POST['create_user_submit']) && isset($user_role) && $user_role == "recruiter") {echo "selected";} ?>>Recruiter</option>
                <option value="administrator" <?php if (isset($_POST['create_user_submit']) && isset($user_role) && $user_role == "administrator") {echo "selected";} ?>>Administrator</option>
            </select><span class="error"> * <?php echo $user_role_error; ?></span><br /><br />
            <label for="user_first_name">First Name:</label>
            <input type="text" id="user_first_name" name="user_first_name" placeholder="first name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your first name has letters, dashes, apostrophes and whitespaces only" value="<?php echo $user_first_name; ?>" required /><span class="error"> * <?php echo $user_first_name_error; ?></span><br /><br />
            <label for="user_middle_name">Middle Name:</label>
            <input type="text" id="user_middle_name" name="user_middle_name" placeholder="middle name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only" value="<?php echo $user_middle_name; ?>" /><span class="error"><?php echo $user_middle_name_error; ?></span><br /><br />
            <label for="user_last_name">Last Name:</label>
            <input type="text" id="user_last_name" name="user_last_name" placeholder="last name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your last name has letters, dashes, apostrophes and whitespaces only" value="<?php echo $user_last_name; ?>" required /><span class="error"> * <?php echo $user_last_name_error; ?></span><br /><br />
            <label for="user_email">Email:</label>
            <input type="email" id="user_email" name="user_email" placeholder="yourname@example.com" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address (e.g., yourname@example.com)" value="<?php echo $user_email; ?>" required /><span class="error"> * <?php echo $user_email_error; ?></span><br /><br />
            <label for="user_phone">Phone Number:</label>
            <input type="tel" id="user_phone" name="user_phone" placeholder="1112223333" pattern="[0-9]{10}" title="Please enter a 10 digit phone number (without special characters including whitespaces)" value="<?php echo $user_phone; ?>" required /><span class="error"> * <?php echo $user_phone_error; ?></span><br /><br />
            <label for="user_birth_date">Birth Date:</label>
            <?php $minimum_year = date("Y") - 75; $maximum_year = date("Y") - 16; ?>
            <input type="date" id="user_birth_date" name="user_birth_date" min="<?php echo "$minimum_year-01-01" ?>" max="<?php echo "$maximum_year-01-01" ?>" required /><span class="error"> * <?php echo $user_birth_date_error; ?></span><br /><br />
            <input type="hidden" id="user_status" name="user_status" value="active" /><span class="error"> <?php echo $user_status_error; ?></span>
            <input type="submit" name="create_user_submit" value="Create User" />
        </form>
        <?php
            if (isset($_POST['create_user_submit'])) {
                echo
                "<script>
                    document.getElementById(\"user_birth_date\").value = \""; echo $user_birth_date; echo "\";
                </script>";
            }
        ?>
    </body>
</html>
