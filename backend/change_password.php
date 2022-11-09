<?php
    session_start();
    if (!isset($_SESSION['login_status'])) {
        header('Location: login.php');
        exit();
    }
    if ($_SESSION['password_expiration'] == 1) {
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
        <title>Change Password</title>
        <style>
            .error {
                color: #FF0000;
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
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>Logout</a>
        <h2>Change Password</h2>
        <p>Please review the following password complexity requirements:</p>
        <ul>
            <li>Password must have at least 1 uppercase character (A-Z)</li>
            <li>Password must have at least 1 lowercase character (a-z)</li>
            <li>Password must have at least 1 digit (0-9)</li>
            <li>Password must have at least 1 special character (between double quotes [includes whitespace]): " !\"#$%&'()*+,-./:;&lt;=&gt;?@[\]^_`{|}~"</li>
            <li>Password must be at least 10 characters</li>
            <li>Password must be at most 128 characters</li>
            <li>Password must not have more than 2 identical characters in a row (e.g., aaa is not allowed)</li>
        </ul>
        <p>Please note that if you have not setup Multi-factor Authentication (MFA) for you account, you will be required to do so after this process.</p>
        <?php
            if(isset($_GET['change_password_expiration'])) {
                function changePasswordExpiration() {
                    unset($_SESSION['change_password_confirmation']);
                    unset($_SESSION['change_password_success']);
                    $_SESSION['password_expiration'] = 1;
                    header('Location: home.php');
                    exit();
                }
                changePasswordExpiration();
            }
            if ($_SERVER['REQUEST_METHOD'] === "POST") {
                $old_password = $_POST['old_password'];
                $new_password = $_POST['new_password'];
                $repeated_new_password = $_POST['repeated_new_password'];

                include("database.php");
                $stmt = $DBConnect->prepare("SELECT password FROM users WHERE username = ?");
                $stmt->bind_param("s", $_SESSION['username']); 
                $stmt->execute();
                $stmt->bind_result($retrieved_password);
                $stmt->fetch();

                if (password_verify($old_password, $retrieved_password)) {
                    if (strcmp($old_password, $new_password) == 0) {
                        $_SESSION['password_error'] = "<p class=\"error\">Please ensure that your new password is different from your old password.</p>";
                        header("Location: change_password.php", true, 303);
                        exit();
                    }
                    else {
                        if (strcmp($new_password, $repeated_new_password) !== 0) {
                            $_SESSION['password_error'] = "<p class=\"error\">Both of the new passwords do not match. Please type both new passwords again.</p>";
                            header("Location: change_password.php", true, 303);
                            exit();
                        }
                        else {
                            if (strlen($new_password) >= 10) {
                                if (strlen($new_password) <= 128) {
                                    if(preg_match('/[A-Z]/', $new_password)) {
                                        if(preg_match('/[a-z]/', $new_password)) {
                                            if(preg_match('/[0-9]/', $new_password)) {
                                                if(preg_match('/\W/', $new_password)) {
                                                    if(preg_match('/(.)\\1{2}/', $new_password)) {
                                                        $_SESSION['password_error'] = "<p class=\"error\">Password must not have more than 2 identical characters in a row (e.g., aaa is not allowed)</p>";
                                                        header("Location: change_password.php", true, 303);
                                                        exit();
                                                    }
                                                    else {
                                                        include("database.php");
                                                        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                                                        $password_expiration = 1;
                                                        $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, password = ? WHERE username = ?");
                                                        $stmt->bind_param("iss", $password_expiration, $hashed_new_password, $_SESSION['username']);
                                                        if ($stmt->execute()) {
                                                            $_SESSION['change_password_success'] = 1;
                                                            $_SESSION['change_password_confirmation'] = "<p>Password was successfully changed. Please <a href=\"?change_password_expiration=true\">Click Here</a> to return to the home page.</p>";
                                                            header("Location: change_password.php", true, 303);
                                                            exit();
                                                        }
                                                        else {
                                                            $_SESSION['change_password_error'] = "<p class=\"error\">Password was not successfully changed.</p>";
                                                            header("Location: change_password.php", true, 303);
                                                            exit();
                                                        }
                                                    }
                                                }
                                                else {
                                                    $_SESSION['password_error'] = "<p class=\"error\">Password must have at least 1 special character (between double quotes [includes whitespace]): \" !\"#$%&'()*+,-./:;<=>?@[\]^_`{|}~\"</p>";
                                                    header("Location: change_password.php", true, 303);
                                                    exit();
                                                }
                                            }
                                            else {
                                                $_SESSION['password_error'] = "<p class=\"error\">Password must have at least 1 digit (0-9)</p>";
                                                header("Location: change_password.php", true, 303);
                                                exit();
                                            }
                                        }
                                        else {
                                            $_SESSION['password_error'] = "<p class=\"error\">Password must have at least 1 lowercase character (a-z)</p>";
                                            header("Location: change_password.php", true, 303);
                                            exit();
                                        }
                                    }
                                    else {
                                        $_SESSION['password_error'] = "<p class=\"error\">Password must have at least 1 uppercase character (A-Z)</p>";
                                        header("Location: change_password.php", true, 303);
                                        exit();
                                    }
                                }
                                else {
                                    $_SESSION['password_error'] = "<p class=\"error\">Password must be at most 128 characters</p>";
                                    header("Location: change_password.php", true, 303);
                                    exit();
                                }
                            }
                            else {
                                $_SESSION['password_error'] = "<p class=\"error\">Password must be at least 10 characters</p>";
                                header("Location: change_password.php", true, 303);
                                exit();
                            }
                        }
                    }
                }
                else {
                    $_SESSION['password_error'] = "<p class=\"error\">Your old password is invalid.</p>";
                    header("Location: change_password.php", true, 303);
                    exit();
                }
                $stmt->close();
                $DBConnect->close();
            }
            elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
                if (isset($_SESSION['password_error'])) {echo $_SESSION['password_error'];}
                if (isset($_SESSION['change_password_confirmation'])) {echo $_SESSION['change_password_confirmation'];}
                if (isset($_SESSION['change_password_error'])) {echo $_SESSION['change_password_error'];}
            }
            if (!isset($_SESSION['change_password_success'])) {
                echo 
                "<form method=\"post\" action=\""; echo htmlspecialchars($_SERVER["PHP_SELF"]); echo "\">
                    <label for=\"old_password\">Enter your old password:</label><br><br>
                    <input type=\"password\" id=\"old_password\" name=\"old_password\" placeholder=\"old password\" required>
                    <input type=\"checkbox\" id=\"checkbox1\" onclick=\"passwordVisibility('old_password')\">Show Password <br><br>
                    <label for=\"new_password\">Enter your new password:</label><br><br>
                    <input type=\"password\" id=\"new_password\" name=\"new_password\" placeholder=\"new password\" required>
                    <input type=\"checkbox\" id=\"checkbox2\" onclick=\"passwordVisibility('new_password')\">Show Password <br><br>
                    <label for=\"repeated_new_password\">Enter your new password again:</label><br><br>
                    <input type=\"password\" id=\"repeated_new_password\" name=\"repeated_new_password\" placeholder=\"new password\" required>
                    <input type=\"checkbox\" id=\"checkbox3\" onclick=\"passwordVisibility('repeated_new_password')\">Show Password <br><br>
                    <input type=\"submit\" id=\"change_password_submit\" name=\"change_password_submit\" value=\"Change Password\">
                </form>";
            }
        ?>
    </body>
</html>
<?php
    if (isset($_SESSION['password_error'])) {unset($_SESSION['password_error']);}
    if (isset($_SESSION['change_password_error'])) {unset($_SESSION['change_password_error']);}
?>