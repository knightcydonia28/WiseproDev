<?php
    session_start();
    if (!isset($_SESSION['login_status'])) {
        header('Location: login.php');
    }
    if ($_SESSION['user_role'] != "administrator") {
        header('Location: home.php');
    }
    if ($_SESSION['password_expiration'] == 0) {
        header('Location: change_password.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Create User</title>
    </head>
    <body>
        <a href="home.php">Home</a><br /><br />
        <form method="post" action="#">
            <input type="submit" name="logout" value="Logout" />
        </form>
        <h2>Create User</h2>
        <p>Please fill the form below to create an account:</p>
        <?php
            unset($_SESSION['search_user']);
            unset($_SESSION['edit_user_authentication']);
            if (isset($_POST['logout'])) {
                include("logout.php");
                logout();
            }
            if (isset($_POST['create_another_user_submit'])) {
                header('Location: create_user.php');
            }
            if (isset($_POST['create_user_submit'])) {
                if (!ctype_alnum($_POST['username'])) {
                    echo "<p>Please ensure that your username is alphanumeric.</p>";
                }
                else {
                    $username = $_POST['username'];
                    include("database.php");
                    $stmt = $DBConnect->prepare("SELECT username FROM users WHERE username = ?");
                    $stmt->bind_param("s", $username); 
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        echo "<p>The username is already taken. Please choose another username.</p>";
                    }
                    else {
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
                                        function validateDate($birth_date, $format = 'Y-m-d') {
                                            $d = DateTime::createFromFormat($format, $birth_date);
                                            return $d && $d->format($format) == $birth_date;
                                        }
                                        $birth_date = $_POST['birth_date'];
                                        if (!validateDate($birth_date)) {
                                            echo "<p>Please enter a valid birth date.</p>"; 
                                        }
                                        else {
                                            if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
                                                echo "<p>Please enter a 10 digit phone number (without special characters including whitespace).</p>"; 
                                            }
                                            else {
                                                $phone = $_POST['phone'];
                                                $string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                                                $temporary_password = str_shuffle($string);
                                                $shortened_temporary_password = substr($temporary_password,0,10);
                                                $hashed_temporary_password = password_hash($shortened_temporary_password, PASSWORD_DEFAULT);
                                                
                                                if (empty($_POST['middle_name'])) {
                                                    include("database.php");
                                                    $stmt = $DBConnect->prepare("INSERT INTO users (username, password_expiration, password, user_role, user_first_name, user_last_name, user_email, user_phone, user_birth_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                    $password_expiration = 0;
                                                    $stmt->bind_param("sisssssss", $username, $password_expiration, $hashed_temporary_password, $user_role, $first_name, $last_name, $filtered_email, $phone, $birth_date);
                                                    if ($stmt->execute()) {
                                                        echo 
                                                        "<p>Account creation was successful.</p>
                                                        <p>Temporary password: $shortened_temporary_password</p>
                                                        <p>Create another user?</p>
                                                        <form method=\"post\" action=\"#\">
                                                            <input type=\"submit\" name=\"create_another_user_submit\" value=\"Create Another User\" />
                                                        </form>";
                                                    }
                                                    else {
                                                        echo "<p>Account creation was unsuccessful.</p>";
                                                    }
                                                }
                                                else {
                                                    if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['middle_name'])) {
                                                        echo "<p>Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only.</p>";
                                                    }
                                                    else {
                                                        $middle_name = $_POST['middle_name'];
                                                        include("database.php");
                                                        $stmt = $DBConnect->prepare("INSERT INTO users (username, password_expiration, password, user_role, user_first_name, user_middle_name, user_last_name, user_email, user_phone, user_birth_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                        $password_expiration = 0;
                                                        $stmt->bind_param("sissssssss", $username, $password_expiration, $hashed_temporary_password, $user_role, $first_name, $middle_name, $last_name, $filtered_email, $phone, $birth_date);
                                                        if ($stmt->execute()) {
                                                            echo 
                                                            "<p>Account creation was successful.</p>
                                                            <p>Temporary password: $shortened_temporary_password</p>
                                                            <p>Create another user?</p>
                                                            <form method=\"post\" action=\"#\">
                                                                <input type=\"submit\" name=\"create_another_user_submit\" value=\"Create Another User\" />
                                                            </form>";
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
                $stmt->close();
                $DBConnect->close();
            }
        ?>
        <form method="post" action="#">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric" value="<?php if (isset($_POST['create_user_submit'])) {echo $_POST['username'];} else {echo "";} ?>" required /><br /><br />
            <label for="user_role">User role:</label>
            <select id="user_role" name="user_role" required>
                <option value="" <?php if (!isset($_POST['create_user_submit'])) {echo "selected";} ?> disabled>Select User Role</option>
                <option value="user" <?php if (isset($_POST['create_user_submit']) && $_POST['user_role'] == "user") {echo "selected";} ?>>User</option>
                <option value="recruiter" <?php if (isset($_POST['create_user_submit']) && $_POST['user_role'] == "recruiter") {echo "selected";} ?>>Recruiter</option>
                <option value="administrator" <?php if (isset($_POST['create_user_submit']) && $_POST['user_role'] == "administrator") {echo "selected";} ?>>Administrator</option>
            </select><br /><br />
            <label for="first_name">First name:</label>
            <input type="text" id="first_name" name="first_name" placeholder="first name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your first name has letters, dashes, apostrophes and whitespaces only" value="<?php if (isset($_POST['create_user_submit'])) {echo $_POST['first_name'];} else {echo "";} ?>" required /><br /><br />
            <label for="middle_name">Middle name (optional):</label>
            <input type="text" id="middle_name" name="middle_name" placeholder="middle name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only" value="<?php if (isset($_POST['create_user_submit'])) {echo $_POST['middle_name'];} else {echo "";} ?>" /><br /><br />
            <label for="last_name">Last name:</label>
            <input type="text" id="last_name" name="last_name" placeholder="last name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your last name has letters, dashes, apostrophes and whitespaces only" value="<?php if (isset($_POST['create_user_submit'])) {echo $_POST['last_name'];} else {echo "";} ?>" required /><br /><br />
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="yourname@example.com" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address (e.g., yourname@example.com)" value="<?php if (isset($_POST['create_user_submit'])) {echo $_POST['email'];} else {echo "";} ?>" required /><br /><br />
            <label for="phone">Phone number:</label>
            <input type="tel" id="phone" name="phone" placeholder="1112223333" pattern="[0-9]{10}" title="Please enter a 10 digit phone number (without special characters including whitespaces)" value="<?php if (isset($_POST['create_user_submit'])) {echo $_POST['phone'];} else {echo "";} ?>" required /><br /><br />
            <label for="birth_date">Birth date:</label>
            <?php $minimum_year = date("Y") - 75; $maximum_year = date("Y") - 16; ?>
            <input type="date" id="birth_date" name="birth_date" min="<?php echo "$minimum_year-01-01" ?>" max="<?php echo "$maximum_year-01-01" ?>" required /><br /><br />
            <input type="submit" name="create_user_submit" value="Create User" />
        </form>
        <?php
            if (isset($_POST['create_user_submit'])) {
                echo
                "<script>
                    document.getElementById(\"birth_date\").value = \""; echo $_POST['birth_date']; echo "\";
                </script>";
            }
        ?>
    </body>
</html>

