<?php
    session_start();
    if (!isset($_SESSION['login_status'])) {
        header('Location: login.php');
    }
    if ($_SESSION['password_expiration'] == 0) {
        header('Location: change_password.php');
    }
    if ($_SESSION['secret_key'] == 1) {
        header('Location: home.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Setup MFA</title>
    </head>
    <body>
        <form method="post" action="#">
            <input type="submit" name="logout" value="Logout" />
        </form>
        <h3>Setup MFA</h3>
        <p>Please click the "Activate MFA" button to start the process of enabling multi-factor authentication (MFA) with Google Authenticator.</p>
        <?php
            if (isset($_POST['logout'])) {
                include("logout.php");
                logout();
            }
            if(isset($_GET['change_secret_key'])) { 
                function changeSecretKey() {
                    unset($_SESSION['setup_mfa_disabled']);
                    $_SESSION['secret_key'] = 1;
                    header('Location: home.php');
                }
                changeSecretKey();
            }
            if (isset($_POST['submit'])) {

                require __DIR__ . '/vendor/autoload.php';

                $google2fa = new \PragmaRX\Google2FA\Google2FA();
                $secret_key = $google2fa->generateSecretKey();

                $username = $_SESSION['username'];

                include("database.php");
                $stmt = $DBConnect->prepare("UPDATE users SET secret_key = ? WHERE username = ?");
                $stmt->bind_param("ss", $secret_key, $username);
                if ($stmt->execute()) {
                    $_SESSION['setup_mfa_disabled'] = 1;
                    echo 
                    "<p>MFA activation was successful.</p>
                    <p>Please enter the following number into your phone: $secret_key</p>
                    <p>Please <a href=\"?change_secret_key=true\">Click Here</a> to return to the home page or logout to exit this page.</p>";
                }
                else {
                    echo "<p>MFA activation was unsuccessful.</p>";
                }
            }
        ?>
        <form method="post" action="#">
            <input type="submit" name="submit" value="Activiate MFA" <?php if (isset($_SESSION['setup_mfa_disabled'])) {echo "disabled";} ?>/>
        </form>
    </body>
</html>

