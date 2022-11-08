<?php
    session_start();
    if (isset($_SESSION['login_status'])) {
        header('Location: home.php');
        exit();
    }
    if (isset($_SESSION['login'])) {
        session_regenerate_id();
        unset($_SESSION['login']);
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
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
            .login_form {
                border-style: double;
                width: fit-content;
                height: fit-content;
                display: block;
                margin: 0 auto;
                padding: 20px;
            }
        </style>
    </head>
    <body>
        <div class="login_form">
        <h2>Login</h2>
        <p>Please login to continue</p>
        <?php
            if ($_SERVER['REQUEST_METHOD'] === "POST") {

                function test_input($data) {
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                } 

                function getIpAddress() {
                    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
                    }
                    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    }
                    else {
                        $ip_address = $_SERVER['REMOTE_ADDR'];
                    }
                    return $ip_address;
                }

                $ip_address = test_input(getIpAddress());
                $login_time = test_input(time());

                include("database.php");
                $stmt = $DBConnect->prepare("SELECT ip_address, login_attempts, login_time FROM logins WHERE ip_address = ?");
                $stmt->bind_param("s", $ip_address);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($retrieved_ip_address, $retrieved_login_attempts, $retrieved_login_time);

                if ($stmt->num_rows > 0) {
                    $stmt->fetch();
                    if (time() - $retrieved_login_time > 900) {
                        include("database.php");
                        $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = 0 WHERE ip_address = ?");
                        $stmt->bind_param("s", $retrieved_ip_address);
                        $stmt->execute();
                        
                        if (!ctype_alnum($_POST['username'])) {
                            $_SESSION["login_error"] = "<p>Invalid username or password.</p>";
                            header("Location: login.php", true, 303);
                            exit();
                        }
                        else {
                            $username = test_input($_POST['username']);
                            $password = $_POST['password'];
                            include("database.php");
                            $stmt = $DBConnect->prepare("SELECT username, password FROM users WHERE username = ?");
                            $stmt->bind_param("s", $username); 
                            $stmt->execute();
                            $stmt->store_result();
                            $stmt->bind_result($retrieved_username, $retrieved_password);
                            
                            if ($stmt->num_rows > 0) {
                                $stmt->fetch();
                                if (password_verify($password, $retrieved_password)) {                                     
                                    include("database.php");
                                    $stmt = $DBConnect->prepare("SELECT user_status FROM users WHERE username = ?");
                                    $stmt->bind_param("s", $retrieved_username);
                                    $stmt->execute();
                                    $stmt->store_result();
                                    $stmt->bind_result($retrieved_user_status);
                                    $stmt->fetch();
                                
                                    if ($retrieved_user_status == "inactive") {
                                        include("database.php");
                                        $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = login_attempts + 1, login_time = ? WHERE ip_address = ?");
                                        $stmt->bind_param("ss", $login_time, $retrieved_ip_address);
                                        $stmt->execute();
                                        $_SESSION["login_error"] = "<p>Invalid username or password.</p>";
                                        header("Location: login.php", true, 303);
                                        exit();
                                    }
                                    else {
                                        $stmt = $DBConnect->prepare("SELECT password_expiration, secret_key, user_role FROM users WHERE username = ?");
                                        $stmt->bind_param("s", $retrieved_username); 
                                        $stmt->execute();
                                        $stmt->store_result();
                                        $stmt->bind_result($retrieved_password_expiration, $retrieved_secret_key, $retrieved_user_role);
                                        $stmt->fetch();
    
                                        if ($retrieved_secret_key == NULL) {
                                            session_start();
                                            $_SESSION['secret_key'] = 0;
                                        }
                                        else {
                                            session_start();
                                            $_SESSION['secret_key'] = 1;
                                        }
    
                                        if ($retrieved_password_expiration == 0) {
                                            include("database.php");
                                            $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = 0 WHERE ip_address = ?");
                                            $stmt->bind_param("s", $retrieved_ip_address);
                                            $stmt->execute();
                                            session_regenerate_id();
                                            $_SESSION['login_status'] = 1;
                                            $_SESSION['login_time'] = time();
                                            $_SESSION['username'] = $retrieved_username;
                                            $_SESSION['password_expiration'] = $retrieved_password_expiration;
                                            $_SESSION['user_role'] = $retrieved_user_role;
                                            header('Location: change_password.php');
                                            exit();
                                        }
                                        else {
                                            if ($_SESSION['secret_key'] == 0) {
                                                include("database.php");
                                                $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = 0 WHERE ip_address = ?");
                                                $stmt->bind_param("s", $retrieved_ip_address);
                                                $stmt->execute();
                                                session_regenerate_id();
                                                $_SESSION['login_status'] = 1;
                                                $_SESSION['login_time'] = time();
                                                $_SESSION['username'] = $retrieved_username;
                                                $_SESSION['password_expiration'] = $retrieved_password_expiration;
                                                $_SESSION['user_role'] = $retrieved_user_role;
                                                header('Location: setup_mfa.php');
                                                exit();
                                            }
                                            else {
                                                include("database.php");
                                                $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = 0 WHERE ip_address = ?");
                                                $stmt->bind_param("s", $retrieved_ip_address);
                                                $stmt->execute();
                                                session_regenerate_id();
                                                $_SESSION['mfa_time'] = time();
                                                $_SESSION['username'] = $retrieved_username;
                                                $_SESSION['password_expiration'] = $retrieved_password_expiration;
                                                $_SESSION['user_role'] = $retrieved_user_role;
                                                $_SESSION['login'] = 1;
                                                header('Location: mfa.php');
                                                exit();
                                            }  
                                        }
                                    }
                                }
                                else {
                                    include("database.php");
                                    $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = login_attempts + 1, login_time = ? WHERE ip_address = ?");
                                    $stmt->bind_param("ss", $login_time, $retrieved_ip_address);
                                    $stmt->execute();
                                    $_SESSION["login_error"] = "<p>Invalid username or password.</p>";
                                    header("Location: login.php", true, 303);
                                    exit();
                                }
                            }
                            else {
                                include("database.php");
                                $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = login_attempts + 1, login_time = ? WHERE ip_address = ?");
                                $stmt->bind_param("ss", $login_time, $retrieved_ip_address);
                                $stmt->execute();
                                $_SESSION["login_error"] = "<p>Invalid username or password.</p>";
                                header("Location: login.php", true, 303);
                                exit();
                            }
                        }
                    }
                    else {
                        if ($retrieved_login_attempts >= 5) {
                            $_SESSION["login_error"] = "<p>The maximum number of login attempts has been exceeded. Please try again in 15 minutes.</p>";
                            header("Location: login.php", true, 303);
                            exit();
                        }
                        else {
                            if (!ctype_alnum($_POST['username'])) {
                                $_SESSION["login_error"] = "<p>Invalid username or password.</p>";
                                header("Location: login.php", true, 303);
                                exit();
                            }
                            else {
                                $username = test_input($_POST['username']);
                                $password = $_POST['password'];
                                include("database.php");
                                $stmt = $DBConnect->prepare("SELECT username, password FROM users WHERE username = ?");
                                $stmt->bind_param("s", $username); 
                                $stmt->execute();
                                $stmt->store_result();
                                $stmt->bind_result($retrieved_username, $retrieved_password);
                                
                                if ($stmt->num_rows > 0) {
                                    $stmt->fetch();
                                    if (password_verify($password, $retrieved_password)) {                                     
                                        include("database.php");
                                        $stmt = $DBConnect->prepare("SELECT user_status FROM users WHERE username = ?");
                                        $stmt->bind_param("s", $retrieved_username);
                                        $stmt->execute();
                                        $stmt->store_result();
                                        $stmt->bind_result($retrieved_user_status);
                                        $stmt->fetch();
                                    
                                        if ($retrieved_user_status == "inactive") {
                                            include("database.php");
                                            $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = login_attempts + 1, login_time = ? WHERE ip_address = ?");
                                            $stmt->bind_param("ss", $login_time, $retrieved_ip_address);
                                            $stmt->execute();
                                            $_SESSION["login_error"] = "<p>Invalid username or password.</p>";
                                            header("Location: login.php", true, 303);
                                            exit();
                                        }
                                        else {
                                            $stmt = $DBConnect->prepare("SELECT password_expiration, secret_key, user_role FROM users WHERE username = ?");
                                            $stmt->bind_param("s", $retrieved_username); 
                                            $stmt->execute();
                                            $stmt->store_result();
                                            $stmt->bind_result($retrieved_password_expiration, $retrieved_secret_key, $retrieved_user_role);
                                            $stmt->fetch();
        
                                            if ($retrieved_secret_key == NULL) {
                                                session_start();
                                                $_SESSION['secret_key'] = 0;
                                            }
                                            else {
                                                session_start();
                                                $_SESSION['secret_key'] = 1;
                                            }
        
                                            if ($retrieved_password_expiration == 0) {
                                                include("database.php");
                                                $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = 0 WHERE ip_address = ?");
                                                $stmt->bind_param("s", $retrieved_ip_address);
                                                $stmt->execute();
                                                session_regenerate_id();
                                                $_SESSION['login_status'] = 1;
                                                $_SESSION['login_time'] = time();
                                                $_SESSION['username'] = $retrieved_username;
                                                $_SESSION['password_expiration'] = $retrieved_password_expiration;
                                                $_SESSION['user_role'] = $retrieved_user_role;
                                                header('Location: change_password.php');
                                                exit();
                                            }
                                            else {
                                                if ($_SESSION['secret_key'] == 0) {
                                                    include("database.php");
                                                    $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = 0 WHERE ip_address = ?");
                                                    $stmt->bind_param("s", $retrieved_ip_address);
                                                    $stmt->execute();
                                                    session_regenerate_id();
                                                    $_SESSION['login_status'] = 1;
                                                    $_SESSION['login_time'] = time();
                                                    $_SESSION['username'] = $retrieved_username;
                                                    $_SESSION['password_expiration'] = $retrieved_password_expiration;
                                                    $_SESSION['user_role'] = $retrieved_user_role;
                                                    header('Location: setup_mfa.php');
                                                    exit();
                                                }
                                                else {
                                                    include("database.php");
                                                    $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = 0 WHERE ip_address = ?");
                                                    $stmt->bind_param("s", $retrieved_ip_address);
                                                    $stmt->execute();
                                                    session_regenerate_id();
                                                    $_SESSION['mfa_time'] = time();
                                                    $_SESSION['username'] = $retrieved_username;
                                                    $_SESSION['password_expiration'] = $retrieved_password_expiration;
                                                    $_SESSION['user_role'] = $retrieved_user_role;
                                                    $_SESSION['login'] = 1;
                                                    header('Location: mfa.php');
                                                    exit();
                                                }  
                                            }
                                        }
                                    }
                                    else {
                                        include("database.php");
                                        $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = login_attempts + 1, login_time = ? WHERE ip_address = ?");
                                        $stmt->bind_param("ss", $login_time, $retrieved_ip_address);
                                        $stmt->execute();
                                        $_SESSION["login_error"] = "<p>Invalid username or password.</p>";
                                        header("Location: login.php", true, 303);
                                        exit();
                                    }
                                }
                                else {
                                    include("database.php");
                                    $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = login_attempts + 1, login_time = ? WHERE ip_address = ?");
                                    $stmt->bind_param("ss", $login_time, $retrieved_ip_address);
                                    $stmt->execute();
                                    $_SESSION["login_error"] = "<p>Invalid username or password.</p>";
                                    header("Location: login.php", true, 303);
                                    exit();
                                }
                            }
                        }         
                    } 
                }
                else {
                    $login_attempts = 1;
                    include("database.php");
                    $stmt = $DBConnect->prepare("INSERT INTO logins (ip_address, login_attempts, login_time) VALUES (?, ?, ?)");
                    $stmt->bind_param("sis", $ip_address, $login_attempts, $login_time);
                    $stmt->execute();

                    if (!ctype_alnum($_POST['username'])) {
                        $_SESSION["login_error"] = "<p>Invalid username or password.</p>";
                        header("Location: login.php", true, 303);
                        exit();
                    }
                    else {
                        $username = test_input($_POST['username']);
                        $password = $_POST['password'];
                        include("database.php");
                        $stmt = $DBConnect->prepare("SELECT username, password FROM users WHERE username = ?");
                        $stmt->bind_param("s", $username); 
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($retrieved_username, $retrieved_password);
                        
                        if ($stmt->num_rows > 0) {
                            $stmt->fetch();
                            if (password_verify($password, $retrieved_password)) {                                     
                                include("database.php");
                                $stmt = $DBConnect->prepare("SELECT user_status FROM users WHERE username = ?");
                                $stmt->bind_param("s", $retrieved_username);
                                $stmt->execute();
                                $stmt->store_result();
                                $stmt->bind_result($retrieved_user_status);
                                $stmt->fetch();
                            
                                if ($retrieved_user_status == "inactive") {
                                    include("database.php");
                                    $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = login_attempts + 1, login_time = ? WHERE ip_address = ?");
                                    $stmt->bind_param("ss", $login_time, $retrieved_ip_address);
                                    $stmt->execute();
                                    $_SESSION["login_error"] = "<p>Invalid username or password.</p>";
                                    header("Location: login.php", true, 303);
                                    exit();
                                }
                                else {
                                    $stmt = $DBConnect->prepare("SELECT password_expiration, secret_key, user_role FROM users WHERE username = ?");
                                    $stmt->bind_param("s", $retrieved_username); 
                                    $stmt->execute();
                                    $stmt->store_result();
                                    $stmt->bind_result($retrieved_password_expiration, $retrieved_secret_key, $retrieved_user_role);
                                    $stmt->fetch();

                                    if ($retrieved_secret_key == NULL) {
                                        session_start();
                                        $_SESSION['secret_key'] = 0;
                                    }
                                    else {
                                        session_start();
                                        $_SESSION['secret_key'] = 1;
                                    }

                                    if ($retrieved_password_expiration == 0) {
                                        include("database.php");
                                        $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = 0 WHERE ip_address = ?");
                                        $stmt->bind_param("s", $retrieved_ip_address);
                                        $stmt->execute();
                                        session_regenerate_id();
                                        $_SESSION['login_status'] = 1;
                                        $_SESSION['login_time'] = time();
                                        $_SESSION['username'] = $retrieved_username;
                                        $_SESSION['password_expiration'] = $retrieved_password_expiration;
                                        $_SESSION['user_role'] = $retrieved_user_role;
                                        header('Location: change_password.php');
                                        exit();
                                    }
                                    else {
                                        if ($_SESSION['secret_key'] == 0) {
                                            include("database.php");
                                            $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = 0 WHERE ip_address = ?");
                                            $stmt->bind_param("s", $retrieved_ip_address);
                                            $stmt->execute();
                                            session_regenerate_id();
                                            $_SESSION['login_status'] = 1;
                                            $_SESSION['login_time'] = time();
                                            $_SESSION['username'] = $retrieved_username;
                                            $_SESSION['password_expiration'] = $retrieved_password_expiration;
                                            $_SESSION['user_role'] = $retrieved_user_role;
                                            header('Location: setup_mfa.php');
                                            exit();
                                        }
                                        else {
                                            include("database.php");
                                            $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = 0 WHERE ip_address = ?");
                                            $stmt->bind_param("s", $retrieved_ip_address);
                                            $stmt->execute();
                                            session_regenerate_id();
                                            $_SESSION['mfa_time'] = time();
                                            $_SESSION['username'] = $retrieved_username;
                                            $_SESSION['password_expiration'] = $retrieved_password_expiration;
                                            $_SESSION['user_role'] = $retrieved_user_role;
                                            $_SESSION['login'] = 1;
                                            header('Location: mfa.php');
                                            exit();
                                        }  
                                    }
                                }
                            }
                            else {
                                include("database.php");
                                $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = login_attempts + 1, login_time = ? WHERE ip_address = ?");
                                $stmt->bind_param("ss", $login_time, $retrieved_ip_address);
                                $stmt->execute();
                                $_SESSION["login_error"] = "<p>Invalid username or password.</p>";
                                header("Location: login.php", true, 303);
                                exit();
                            }
                        }
                        else {
                            include("database.php");
                            $stmt = $DBConnect->prepare("UPDATE logins SET login_attempts = login_attempts + 1, login_time = ? WHERE ip_address = ?");
                            $stmt->bind_param("ss", $login_time, $retrieved_ip_address);
                            $stmt->execute();
                            $_SESSION["login_error"] = "<p>Invalid username or password.</p>";
                            header("Location: login.php", true, 303);
                            exit();
                        }
                    }
                }
                $stmt->close();
                $DBConnect->close();
            }
            elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
                if (isset($_SESSION["mfa_error"])) {echo $_SESSION["mfa_error"];}
                if (isset($_SESSION["login_error"])) {echo $_SESSION["login_error"];}
            }
        ?>
            <hr>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="text" name="username" placeholder="username" required><br><br>
                <input type="password" id="password" name="password" placeholder="password" required><br><br>
                <input type="checkbox" onclick="passwordVisibility('password')">Show Password <br><br>
                <input type="submit" name="login" value="Login">
            </form>
        </div>
    </body>
</html>
<?php
    if (isset($_SESSION["mfa_error"])) {unset($_SESSION["mfa_error"]);}
    if (isset($_SESSION["login_error"])) {unset($_SESSION["login_error"]);}
?>