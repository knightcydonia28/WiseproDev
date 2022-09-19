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
            session_start();
            session_regenerate_id();
            if (isset($_SESSION['login_status'])) {
                header('Location: home.php');
            }
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
                            session_start();
                            $_SESSION['login_status'] = 1;
                            $_SESSION['username'] = $retrieved_username;
                            
                            include("database.php");
                            $stmt = $DBConnect->prepare("SELECT password_expiration, user_role FROM users WHERE username = ?");
                            $stmt->bind_param("s", $username); 
                            $stmt->execute();
                            $stmt->store_result();
                            $stmt->bind_result($retrieved_password_expiration, $retrieved_user_role);
                            $stmt->fetch();
                            
                            $_SESSION['password_expiration'] = $retrieved_password_expiration;
                            $_SESSION['user_role'] = $retrieved_user_role;

                            if ($retrieved_password_expiration == 0) {
                                header('Location: change_password.php');
                            }
                            if ($retrieved_password_expiration == 1) {
                                header('Location: home.php');
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
                <input type="text" name="username" placeholder="username" required /><br /><br />
                <input type="password" id="password" name="password" placeholder="password" required /><br /><br />
                <input type="checkbox" onclick="passwordVisibility('password')" />Show Password <br /><br />
                <input type="submit" name="login" value="Login" />
            </form>
        </div>
    </body>
</html>

