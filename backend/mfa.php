<?php
    session_start();
    if (!isset($_SESSION['login'])) {
        header('Location: login.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
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
        </style>
    </head>
    <body>
        <br />
        <div class="mfa_form">
            <h2>Multi-factor Authentication</h2>
            <p>Please enter your generated code below. If the code is invalid, you will be redirected to the login page.</p>
            <?php
                if (isset($_POST['mfa_code_submit'])) {
                    if (!is_numeric($_POST['mfa_code']) || strlen($_POST['mfa_code']) != 6) {
                        include('logout.php');
                        logout();
                    }
                    else {
                        $mfa_code = $_POST['mfa_code'];
                        
                        require __DIR__ . '/vendor/autoload.php';

                        $google2fa = new \PragmaRX\Google2FA\Google2FA();
                        
                        $username = $_SESSION['username'];
                        
                        include("database.php");
                        $stmt = $DBConnect->prepare("SELECT secret_key FROM users WHERE username = ?");
                        $stmt->bind_param("s", $username); 
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($retrieved_secret_key);
                        $stmt->fetch();
                        
                        $valid = $google2fa->verifyKey($retrieved_secret_key, $mfa_code); 
                        
                        if ($valid) {
                            session_regenerate_id();
                            $_SESSION['login_status'] = 1;
                            unset($_SESSION['login']);
                            header('Location: home.php');
                        }
                        else {
                            include('logout.php');
                            logout();
                        }
                    }
                    $stmt->close();
                    $DBConnect->close();
                }
            ?>
            <form method="post" action="#">
                <input type="number" name="mfa_code" placeholder="000111" min="000000" max="999999" required /><br /><br />
                <input type="submit" name="mfa_code_submit" value="Submit MFA Code" />
            </form>
        </div>
    </body>
</html>