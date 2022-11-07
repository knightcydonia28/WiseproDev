<?php
    session_start();
    if (!isset($_SESSION['login'])) {
        header('Location: login.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            if (time() - $_SESSION['mfa_time'] > 300) {
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
        <meta charset="UTF-8">
        <title>MFA</title>
        <style>
            .mfa_form {
                border-style: double;
                width: fit-content;
                height: fit-content;
                display: block;
                margin: 0 auto;
                padding: 20px;
            }
            input::-webkit-outer-spin-button, input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            input[type=number] {
                -moz-appearance: textfield;
            }
        </style>
    </head>
    <body>
        <br>
        <div class="mfa_form">
            <h2>Multi-factor Authentication</h2>
            <p>Please enter your generated code below.</p>
            <?php
                if (isset($_POST['mfa_code_submit'])) {
                    
                    function test_input($data) {
                        $data = trim($data);
                        $data = stripslashes($data);
                        $data = htmlspecialchars($data);
                        return $data;
                    } 

                    if (!is_numeric($_POST['mfa_code']) || strlen($_POST['mfa_code']) != 6) {
                        unset($_SESSION["secret_key"]);
                        unset($_SESSION["mfa_time"]);
                        unset($_SESSION["username"]);
                        unset($_SESSION["password_expiration"]);
                        unset($_SESSION["user_role"]);
                        $_SESSION['mfa_error'] = "Invalid code entered.";
                        header('Location: login.php');
                        exit();
                    }
                    else {
                        $mfa_code = test_input($_POST['mfa_code']);
                        
                        require __DIR__ . '/vendor/autoload.php';

                        $google2fa = new \PragmaRX\Google2FA\Google2FA();
                        
                        $username = $_SESSION['username'];

                        $decryption_key = "random_key";
                        include("database.php");
                        $stmt = $DBConnect->prepare("SELECT AES_DECRYPT(secret_key, ?) FROM users WHERE username = ?");
                        $stmt->bind_param("ss", $decryption_key, $username); 
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($retrieved_secret_key);
                        $stmt->fetch();
                        
                        $valid = $google2fa->verifyKey($retrieved_secret_key, $mfa_code); 
                        
                        if ($valid) {
                            session_regenerate_id();
                            $_SESSION['login_status'] = 1;
                            $_SESSION['login_time'] = time();
                            unset($_SESSION['login']);
                            unset($_SESSION['mfa_time']);
                            header('Location: home.php');
                            exit();
                        }
                        else {
                            unset($_SESSION["secret_key"]);
                            unset($_SESSION["mfa_time"]);
                            unset($_SESSION["username"]);
                            unset($_SESSION["password_expiration"]);
                            unset($_SESSION["user_role"]);
                            $_SESSION['mfa_error'] = "Invalid code entered.";
                            header('Location: login.php');
                            exit();
                        }
                    }
                    $stmt->close();
                    $DBConnect->close();
                }
            ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="number" name="mfa_code" placeholder="000000" min="000000" max="999999" required><br><br>
                <input type="submit" name="mfa_code_submit" value="Submit MFA Code">
            </form>
        </div>
    </body>
</html>