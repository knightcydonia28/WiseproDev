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
    if ($_SESSION['secret_key'] == 0) {
        header('Location: setup_mfa.php');
        exit();
    }
    if ($_SESSION['user_role'] != "administrator") {
        header('Location: home.php');
        exit();
    }
    if (!isset($_COOKIE['search_user'])) {
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
        <title>View User</title>
    </head>
    <body>
        <a href="home.php">Home</a><br /><br />
        <form method="post" action="#">
            <input type="submit" name="logout" value="Logout" /><br /><br />
        </form>
        <h2>View User</h2>
        <p>Below is information about the selected user:</p>
        <?php
            if (isset($_POST['logout'])) {
                include("logout.php");
                logout();
            }
        ?>
        <div class="user_information">
            <p>Username: <span><?php echo $_COOKIE["username"]; ?></span></p>
            <p>Password Expiration: <span><?php echo $_COOKIE["password_expiration"]; ?></span></p>
            <p>User Role: <span><?php echo $_COOKIE["user_role"]; ?></span></p>
            <p>First name: <span><?php echo $_COOKIE["user_first_name"]; ?></span></p>
            <p>Middle Name: <span><?php echo $_COOKIE["user_middle_name"]; ?></span></p>
            <p>Last Name: <span><?php echo $_COOKIE["user_last_name"]; ?></span></p>
            <p>Email: <span><?php echo $_COOKIE["user_email"]; ?></span></p>
            <p>Phone Number: <span><?php echo $_COOKIE["user_phone"]; ?></span></p>
            <?php
                $user_birth_date = $_COOKIE["user_birth_date"];
                $array = explode("-", $user_birth_date);
                $formatted_user_birth_date = $array[1]."/".$array[2]."/".$array[0];
            ?>
            <p>Birth Date: <span id ="user_birth_date"><?php echo $formatted_user_birth_date; ?></span></p>
            <p>User Status: <span><?php echo $_COOKIE["user_status"]; ?></span></p>
            <p>Secret Key: <span><?php echo $_COOKIE["secret_key"]; ?></span></p>
        </div> 
    </body>
</html>