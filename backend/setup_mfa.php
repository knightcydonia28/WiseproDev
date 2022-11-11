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
            include("session_timeout.php");
        ?>
        <meta charset="UTF-8" >
        <title>Setup MFA</title>
    </head>
    <body>
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>Logout</a>
        <h3>Setup MFA</h3>
        <p>The enterprise portal for Wisetek Providers Inc utilizes the Google Authenticator application for multi-factor authentication (MFA). This application can be installed from Google Play for Android users or from the App Store for iOS users. Once installed, please click the "Activate MFA" button to start the process of enabling MFA with Google Authenticator.</p>
        <?php
            if(isset($_GET['change_secret_key'])) { 
                function changeSecretKey() {
                    unset($_SESSION["setup_mfa_confirmation"]);
                    unset($_SESSION['setup_mfa_success']);
                    $_SESSION['secret_key'] = 1;
                    header('Location: home.php');
                    exit();
                }
                changeSecretKey();
            }
            if ($_SERVER['REQUEST_METHOD'] === "POST") {

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
                    
                    $_SESSION['setup_mfa_success'] = 1;
                    
                    $setup_mfa_confirmation = array();
                    $setup_mfa_confirmation[] = "<p>MFA activation was successful.</p>";
                    $setup_mfa_confirmation[] = "<p>You may enter the setup key below by clicking the plus icon in the Google Authenticator application and selecting \"Enter a setup key\". For faster setup, you can select \"Scan a QR code\" and scan the QR code below.</p>";
                    $setup_mfa_confirmation[] = "<p><b>Setup Key:</b> $secret_key</p>";
                    $setup_mfa_confirmation[] = "<p><b>QR Code:</b></p>";
                    $setup_mfa_confirmation[] = "<img src=\"data:image/png;base64,$qrcode_image\" alt=\"QR Code for the Secret Key\">";
                    $setup_mfa_confirmation[] = "<p>Please <a href=\"?change_secret_key=true\">Click Here</a> to return to the home page.</p>";
                    
                    $_SESSION["setup_mfa_confirmation"] = $setup_mfa_confirmation;
                    header("Location: setup_mfa.php", true, 303);
                    exit();
                }
                else {
                    $_SESSION["setup_mfa_error"] = "<p>MFA activation was unsuccessful.</p>";
                }
                
            }
            elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
                if (isset($_SESSION["setup_mfa_confirmation"])) {echo $_SESSION["setup_mfa_confirmation"][0], $_SESSION["setup_mfa_confirmation"][1], $_SESSION["setup_mfa_confirmation"][2], $_SESSION["setup_mfa_confirmation"][3], $_SESSION["setup_mfa_confirmation"][4], $_SESSION["setup_mfa_confirmation"][5];}
                if (isset($_SESSION["setup_mfa_error"])) {echo $_SESSION["setup_mfa_error"];}
            }
            if (!isset($_SESSION['setup_mfa_success'])) {
                echo
                "<form method=\"post\" action=\""; echo htmlspecialchars($_SERVER["PHP_SELF"]); echo "\">
                    <input type=\"submit\" name=\"submit\" value=\"Activiate MFA\">
                </form>";
            }
        ?>
    </body>
</html>
<?php
    if (isset($_SESSION["setup_mfa_error"])) {unset($_SESSION["setup_mfa_error"]);}
?>