<?php
    session_start();
    if (!isset($_SESSION['login'])) {
        header("Location: login.php", true, 303);
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
                    if (isset($_SERVER['HTTP_COOKIE'])) {
                        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
                        foreach($cookies as $cookie) {
                            $parts = explode('=', $cookie);
                            $name = trim($parts[0]);
                            setcookie($name, '', time()-1000);
                            setcookie($name, '', time()-1000, '/');
                        }
                    }
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
                if ($_SERVER['REQUEST_METHOD'] === "POST") {
                    
                    function testInput($data) {
                        $data = trim($data);
                        $data = stripslashes($data);
                        $data = htmlspecialchars($data);
                        return $data;
                    }

                    function mfaSessionReset() {
                        if (isset($_SESSION["secret_key"])) {unset($_SESSION["secret_key"]);}
                            if (isset($_SESSION["mfa_time"])) {unset($_SESSION["mfa_time"]);}
                            if (isset($_SESSION["username"])) {unset($_SESSION["username"]);}
                            if (isset($_SESSION["password_expiration"])) {unset($_SESSION["password_expiration"]);}
                            if (isset($_SESSION["user_role"])) {unset($_SESSION["user_role"]);}
                            
                            $_SESSION['mfa_error'] = "<p class=\"error\">Invalid code entered.</p>";
                            header("Location: login.php", true, 303);
                            exit();
                    }

                    function validateMfaCode($provided_mfa_code) {
                        $provided_mfa_code = testInput($provided_mfa_code);

                        if (!is_numeric($provided_mfa_code) || strlen($provided_mfa_code) != 6) {
                            mfaSessionReset();
                        }
                        else {
                            return $provided_mfa_code;
                        }
                    }

                    $mfa_code = validateMfaCode($_POST['mfa_code']);
                    
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
                        mfaSessionReset();
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