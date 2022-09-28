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
    if ($_SESSION['secret_key'] == 1) {
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
        ?>
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
                    exit();
                }
                changeSecretKey();
            }
            if (isset($_POST['submit'])) {

                require __DIR__ . '/vendor/autoload.php';

                $google2fa = new \PragmaRX\Google2FA\Google2FA();                
                $secret_key = $google2fa->generateSecretKey();

                $username = $_SESSION['username'];
                include("database.php");
                $stmt = $DBConnect->prepare("SELECT user_email FROM users WHERE username = ?");
                $stmt->bind_param("s", $username); 
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($retrieved_user_email);
                $stmt->fetch();

                $g2faUrl = $google2fa->getQRCodeUrl(
                    $username,
                    $retrieved_user_email,
                    $secret_key
                );

                $renderer = new \BaconQrCode\Renderer\ImageRenderer(new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400), new \BaconQrCode\Renderer\Image\ImagickImageBackEnd());
                $writer = new BaconQrCode\Writer($renderer);
                $qrcode_image = base64_encode($writer->writeString($g2faUrl));
                
                $encryption_key = "random_key";
                $stmt = $DBConnect->prepare("UPDATE users SET secret_key = AES_ENCRYPT(?, ?) WHERE username = ?");
                $stmt->bind_param("sss", $secret_key, $encryption_key, $username);
                if ($stmt->execute()) {
                    $_SESSION['setup_mfa_disabled'] = 1;
                    echo 
                    "<p>MFA activation was successful.</p>
                    <p>You may enter the code below to your phone or use the QR Code.</p>
                    <p>Code: $secret_key</p>
                    <p>QR Code:</p>
                    <img src=\"data:image/png;base64, $qrcode_image\"/>
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

