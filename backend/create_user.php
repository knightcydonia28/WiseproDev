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
    setcookie("search_job_posting", "", time() - 3600);
    setcookie("choose_timesheet", "", time() - 3600);
    setcookie("choose_employment", "", time() - 3600);
    setcookie("client_id", "", time() - 3600);
    unset($_SESSION['disable_choose_timesheet']);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            include("session_timeout.php");
        ?>
        <meta charset="UTF-8">
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
        <a href="home.php">Home</a>
        <br><br>
        <?php
            include("logout.php");
        ?>
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

                function validateUsername($provided_username) {
                    $provided_username = testInput($provided_username);
                    if (!ctype_alnum($provided_username)) {
                        $_SESSION["username_error"] = "<p class=\"error\">Please ensure that your username is alphanumeric</p>";
                        header("Location: create_user.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_username;
                    }
                }

                function validateUserRole($provided_user_role) {
                    $provided_user_role = testInput($provided_user_role);
                    if ($provided_user_role != "user" && $provided_user_role != "recruiter" && $provided_user_role != "administrator") {
                        $_SESSION["user_role_error"] = "<p class=\"error\">Please select an appropriate user role</p>";
                        header("Location: create_user.php", true, 303);
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
                        header("Location: create_user.php", true, 303);
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
                        header("Location: create_user.php", true, 303);
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
                        header("Location: create_user.php", true, 303);
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
                        header("Location: create_user.php", true, 303);
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
                        header("Location: create_user.php", true, 303);
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
                        header("Location: create_user.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_user_birth_date;
                    }
                }

                function validateUserStatus($provided_user_status) {
                    $provided_user_status = testInput($provided_user_status);
                    if (($provided_user_status != "active")) {
                        $_SESSION["user_status_error"] = "<p class=\"error\">Please ensure that the status of the user is active upon creation</p>";
                        header("Location: create_user.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_user_status;
                    }
                }

                function generateTemporaryPassword() {
                    $results = array();
                    $string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                    $temporary_password = str_shuffle($string);
                    $shortened_temporary_password = substr($temporary_password,0,15);
                    $results[] = $shortened_temporary_password;
                    $hashed_temporary_password = password_hash($shortened_temporary_password, PASSWORD_DEFAULT);
                    $results[] = $hashed_temporary_password;
                    return $results;
                }

                if (!empty($_POST['user_middle_name'])) {
                    $user_middle_name = validateUserMiddleName($_POST['user_middle_name']);
                }
                else {
                    $user_middle_name = NULL;
                }

                $username = validateUsername($_POST['username']);
                
                include("database.php");
                $stmt = $DBConnect->prepare("SELECT username FROM users WHERE username = ?");
                $stmt->bind_param("s", $username); 
                $stmt->execute();
                $stmt->store_result();
                $stmt->fetch();
                $stmt->close();
                $DBConnect->close();

                if ($stmt->num_rows > 0) {
                    $_SESSION["username_error"] = "<p class=\"error\">Username is already taken. Please choose another username</p>";
                    header("Location: create_user.php", true, 303);
                    exit();
                }
                else {
                    $user_role = validateUserRole($_POST['user_role']);
                    $user_first_name = validateUserFirstName($_POST['user_first_name']);
                    $user_last_name = validateUserLastName($_POST['user_last_name']);
                    $user_email = validateUserEmail($_POST['user_email']);
                    $user_phone = validateUserPhone($_POST['user_phone']);
                    $user_birth_date = validateUserBirthDate($_POST['user_birth_date']);
                    $user_status = validateUserStatus($_POST['user_status']);
                    $password_array = generateTemporaryPassword(); 
                    
                    $_SESSION["create_user_username"] = $username;
                    $_SESSION["create_user_user_role"] = $user_role;
                    $_SESSION["user_first_name"] = $user_first_name;
                    $_SESSION["user_middle_name"] = $user_middle_name;
                    $_SESSION["user_last_name"] = $user_last_name;
                    $_SESSION["user_email"] = $user_email;
                    $_SESSION["user_phone"] = $user_phone;
                    $_SESSION["user_birth_date"] = $user_birth_date;

                    include("database.php");
                    $stmt = $DBConnect->prepare("INSERT INTO users (username, password_expiration, password, user_role, user_first_name, user_middle_name, user_last_name, user_email, user_phone, user_birth_date, user_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $password_expiration = 0;
                    $stmt->bind_param("sisssssssss", $username, $password_expiration, $password_array[1], $user_role, $user_first_name, $user_middle_name, $user_last_name, $user_email, $user_phone, $user_birth_date, $user_status);
                    if ($stmt->execute()) { 
                        $create_user_confirmation = array();
                        $create_user_confirmation[] = "<p>Account creation was successful.</p>";                                                       
                        $create_user_confirmation[] = "<p>Temporary password: ".$password_array[0]."</p>";
                        
                        $to = $user_email;
                        $subject = "Wisepro Account Temporary Password";
                        $message = "Hi ".ucfirst(strtolower($user_first_name)).",\r\nThe temporary password for your account is: ".$password_array[0]."\r\nThanks,\r\nWisepro Administrative Team";
                        $message = wordwrap($message, 70, "\r\n");
                        $headers = array('From' => 'administration@wisepro.com', 'Reply-To' => 'administration@wisepro.com', 'X-Mailer' => 'PHP/' . phpversion());
                        if (mail($to, $subject, $message, $headers)) {
                            $create_user_confirmation[] = "<p>Email to user containing their temporary password was successfully accepted for delivery.</p>";
                        }
                        else {
                            $create_user_confirmation[] = "<p>Email to user containing their temporary password was not accepted for delivery.</p>";
                        }
                        $_SESSION["create_user_confirmation"] = $create_user_confirmation;
                    }
                    else {
                        $_SESSION["create_user_error"] = "<p>Account creation was unsuccessful.</p>";
                        header("Location: create_user.php", true, 303);
                        exit();
                    }
                    $stmt->close();
                    $DBConnect->close();
                }
            }
            elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
                if (isset($_SESSION["create_user_confirmation"])) {echo $_SESSION["create_user_confirmation"][0], $_SESSION["create_user_confirmation"][1], $_SESSION["create_user_confirmation"][2];}
                if (isset($_SESSION["create_user_error"])) {echo $_SESSION["create_user_error"];}
            }
        ?>      
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <h2>Create User</h2>
                <p>Please fill the form below to create an account:</p>
                <p><span class="error">* required field</span></p>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric" value="<?php if (isset($_SESSION["create_user_username"])) {echo $_SESSION["create_user_username"];} ?>" required><span class="error"> * <?php if (isset($_SESSION["username_error"])) {echo $_SESSION["username_error"];} ?></span><br><br>
                <label for="user_role">User Role:</label>
                <select id="user_role" name="user_role" required>
                    <option value="" <?php if (!isset($_POST['create_user_submit'])) {echo "selected";} ?> disabled>Select User Role</option>
                    <option value="user" <?php if (isset($_SESSION["create_user_user_role"]) && $_SESSION["create_user_user_role"] == "user") {echo "selected";} ?>>User</option>
                    <option value="recruiter" <?php if (isset($_SESSION["create_user_user_role"]) && $_SESSION["create_user_user_role"] == "recruiter") {echo "selected";} ?>>Recruiter</option>
                    <option value="administrator" <?php if (isset($_SESSION["create_user_user_role"]) && $_SESSION["create_user_user_role"] == "administrator") {echo "selected";} ?>>Administrator</option>
                </select><span class="error"> * <?php if (isset($_SESSION["user_role_error"])) {echo $_SESSION["user_role_error"];} ?></span><br><br>           
                <label for="user_first_name">First Name:</label>
                <input type="text" id="user_first_name" name="user_first_name" placeholder="First Name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your first name has letters, dashes, apostrophes and whitespaces only" value="<?php if (isset($_SESSION["user_first_name"])) {echo $_SESSION["user_first_name"];} ?>" required><span class="error"> * <?php if (isset($_SESSION["user_first_name_error"])) {echo $_SESSION["user_first_name_error"];} ?></span><br><br>           
                <label for="user_middle_name">Middle Name:</label>
                <input type="text" id="user_middle_name" name="user_middle_name" placeholder="Middle Name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only" value="<?php if (isset($_SESSION["user_middle_name"])) {echo $_SESSION["user_middle_name"];} ?>"><span class="error"> <?php if (isset($_SESSION["user_middle_name_error"])) {echo $_SESSION["user_middle_name_error"];} ?></span><br><br>          
                <label for="user_last_name">Last Name:</label>
                <input type="text" id="user_last_name" name="user_last_name" placeholder="Last Name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your last name has letters, dashes, apostrophes and whitespaces only" value="<?php if (isset($_SESSION["user_last_name"])) {echo $_SESSION["user_last_name"];} ?>" required><span class="error"> * <?php if (isset($_SESSION["user_last_name_error"])) {echo $_SESSION["user_last_name_error"];} ?></span><br><br>
                <label for="user_email">Email:</label>
                <input type="email" id="user_email" name="user_email" placeholder="yourname@example.com" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address (e.g., yourname@example.com)" value="<?php if (isset($_SESSION["user_email"])) {echo $_SESSION["user_email"];} ?>" required><span class="error"> * <?php if (isset($_SESSION["user_email_error"])) {echo $_SESSION["user_email_error"];} ?></span><br><br>        
                <label for="user_phone">Phone Number:</label>
                <input type="tel" id="user_phone" name="user_phone" placeholder="0123456789" pattern="[0-9]{10}" title="Please enter a 10 digit phone number (without special characters including whitespaces)" value="<?php if (isset($_SESSION["user_phone"])) {echo $_SESSION["user_phone"];} ?>" required><span class="error"> * <?php if (isset($_SESSION["user_phone_error"])) {echo $_SESSION["user_phone_error"];} ?></span><br><br> 
                <label for="user_birth_date">Birth Date:</label>
                <?php $minimum_year = date("Y") - 75; $maximum_year = date("Y") - 16; ?>
                <input type="date" id="user_birth_date" name="user_birth_date" min="<?php echo "$minimum_year-01-01" ?>" max="<?php echo "$maximum_year-01-01" ?>" required><span class="error"> * <?php if (isset($_SESSION["user_birth_date_error"])) {echo $_SESSION["user_birth_date_error"];} ?></span><br><br>
                <input type="hidden" id="user_status" name="user_status" value="active"><span class="error"> <?php if (isset($_SESSION["user_status_error"])) {echo $_SESSION["user_status_error"];} ?></span>
                <input type="submit" name="create_user_submit" value="Create User">
        </form>  
        <?php
            if ($_SERVER['REQUEST_METHOD'] === "GET") {
                echo
                "<script>
                    document.getElementById(\"user_birth_date\").value = \""; echo $_SESSION["user_birth_date"]; echo "\";
                </script>";
            }
        ?>
    </body>
</html>
<?php
    if (isset($_SESSION["create_user_username"])) {unset($_SESSION["create_user_username"]);}
    if (isset($_SESSION["create_user_user_role"])) {unset($_SESSION["create_user_user_role"]);}
    if (isset($_SESSION["user_first_name"])) {unset($_SESSION["user_first_name"]);}
    if (isset($_SESSION["user_middle_name"])) {unset($_SESSION["user_middle_name"]);}
    if (isset($_SESSION["user_last_name"])) {unset($_SESSION["user_last_name"]);}
    if (isset($_SESSION["user_email"])) {unset($_SESSION["user_email"]);}
    if (isset($_SESSION["user_phone"])) {unset($_SESSION["user_phone"]);}
    if (isset($_SESSION["user_birth_date"])) {unset($_SESSION["user_birth_date"]);}
    if (isset($_SESSION["create_user_confirmation"])) {unset($_SESSION["create_user_confirmation"]);}
    if (isset($_SESSION["create_user_error"])) {unset($_SESSION["create_user_error"]);}
    if (isset($_SESSION["username_error"])) {unset($_SESSION["username_error"]);}
    if (isset($_SESSION["user_role_error"])) {unset($_SESSION["user_role_error"]);}
    if (isset($_SESSION["user_first_name_error"])) {unset($_SESSION["user_first_name_error"]);}
    if (isset($_SESSION["user_middle_name_error"])) {unset($_SESSION["user_middle_name_error"]);}
    if (isset($_SESSION["user_last_name_error"])) {unset($_SESSION["user_last_name_error"]);}
    if (isset($_SESSION["user_email_error"])) {unset($_SESSION["user_email_error"]);}
    if (isset($_SESSION["user_phone_error"])) {unset($_SESSION["user_phone_error"]);}
    if (isset($_SESSION["user_birth_date_error"])) {unset($_SESSION["user_birth_date_error"]);}
    if (isset($_SESSION["user_status_error"])) {unset($_SESSION["user_status_error"]);}
?>