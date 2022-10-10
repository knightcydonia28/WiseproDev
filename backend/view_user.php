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
            if (time() - $_SESSION['login_time'] < 900) {
                $added_time = time() - $_SESSION['login_time'];
                $_SESSION['login_time'] += $added_time;
            }
        ?>
        <meta charset="UTF-8" />
        <title>View User</title>
    </head>
    <body>
        <a href="home.php">Home</a><br /><br />
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>Logout</a>
        <h2>View User</h2>
        <p>Below is information about the selected user:</p>
        <?php
            include("database.php");
            $stmt = $DBConnect->prepare("SELECT username, password_expiration, user_role, user_first_name, user_middle_name, user_last_name, user_email, user_phone, user_birth_date, user_status, secret_key FROM users WHERE username = ?");
            $stmt->bind_param("s", $_COOKIE['username']);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($retrieved_username, $retrieved_password_expiration, $retrieved_user_role, $retrieved_user_first_name, $retrieved_user_middle_name, $retrieved_user_last_name, $retrieved_user_email, $retrieved_user_phone, $retrieved_user_birth_date, $retrieved_user_status, $retrieved_secret_key);
            $stmt->fetch();
            $stmt->close();
            $DBConnect->close();
        ?>
            <p>Username: <?php echo $_COOKIE["username"]; ?></p>
            <p>Password Expiration: <?php echo $retrieved_password_expiration; ?></p>
            <p>User Role: <?php echo $retrieved_user_role; ?></p>
            <p>First name: <?php echo $retrieved_user_first_name; ?></p>
            <p>Middle Name: <?php echo $retrieved_user_middle_name; ?></p>
            <p>Last Name: <?php echo $retrieved_user_last_name; ?></p>
            <p>Email: <?php echo $retrieved_user_email; ?></p>
            <p>Phone Number: <?php echo $retrieved_user_phone; ?></p>
            <?php
                $user_birth_date = $retrieved_user_birth_date;
                $array = explode("-", $user_birth_date);
                $formatted_user_birth_date = $array[1]."/".$array[2]."/".$array[0];
            ?>
            <p>Birth Date: <?php echo $formatted_user_birth_date; ?></p>
            <p>User Status: <?php echo $retrieved_user_status; ?></p>
            <?php
                if ($retrieved_secret_key == NULL) {
                    $retrieved_secret_key = 0;
                }
                else {
                    $retrieved_secret_key = 1;
                }
            ?>
            <p>Secret Key: <?php echo $retrieved_secret_key; ?></p>
    </body>
</html>