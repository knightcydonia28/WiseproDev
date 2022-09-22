<?php
    session_start();
    if (isset($_SESSION['login_status'])) {
        header('Location: home.php');
    }
    unset($_SESSION['login']);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
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
            if (isset($_POST['login'])) {
                if (!ctype_alnum($_POST['username'])) {
                    echo "<p>Invalid username or password.</p>";
                }
                else {
                    $username = $_POST['username'];
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
                                session_regenerate_id();
                                $_SESSION['login_status'] = 1;
                                $_SESSION['username'] = $retrieved_username;
                                $_SESSION['password_expiration'] = $retrieved_password_expiration;
                                $_SESSION['user_role'] = $retrieved_user_role;
                                header('Location: change_password.php');
                            }
                            else {
                                if ($_SESSION['secret_key'] == 0) {
                                    session_regenerate_id();
                                    $_SESSION['login_status'] = 1;
                                    $_SESSION['username'] = $retrieved_username;
                                    $_SESSION['password_expiration'] = $retrieved_password_expiration;
                                    $_SESSION['user_role'] = $retrieved_user_role;
                                    header('Location: setup_mfa.php');
                                }
                                else {
                                    session_regenerate_id();
                                    $_SESSION['username'] = $retrieved_username;
                                    $_SESSION['password_expiration'] = $retrieved_password_expiration;
                                    $_SESSION['user_role'] = $retrieved_user_role;
                                    $_SESSION['login'] = 1;
                                    header('Location: mfa.php');
                                }  
                            }
                        }
                        else {
                            echo "<p>Invalid username or password.</p>";
                        }
                    }
                    else {
                        echo "<p>Invalid username or password.</p>";
                    }
                    $stmt->close();
                    $DBConnect->close();
                }
            }     
        ?>
            <hr />
            <form method="post" action="#">
                <input type="text" name="username" placeholder="username" required <?php if (isset($_SESSION['disable_login'])) {echo "disabled";} ?> /><br /><br />
                <input type="password" id="password" name="password" placeholder="password" required <?php if (isset($_SESSION['disable_login'])) {echo "disabled";} ?> /><br /><br />
                <input type="checkbox" onclick="passwordVisibility('password')" <?php if (isset($_SESSION['disable_login'])) {echo "disabled";} ?> />Show Password <br /><br />
                <input type="submit" name="login" value="Login" <?php if (isset($_SESSION['disable_login'])) {echo "disabled";} ?> />
            </form>
        </div>
    </body>
</html>

