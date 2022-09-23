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
    unset($_SESSION['search_user']);
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
        <title>Home</title>
    </head>
    <body>
        <?php
            if (isset($_POST['logout'])) {
                include("logout.php");
                logout();
            }
        ?>
        <form method="post" action="#">
            <input type="submit" name="logout" value="Logout" />
        </form>
        <h4>Home Page</h4>
        <ul>
            <?php
                if ($_SESSION['user_role'] == "user") {
                    echo 
                    "<li><a href=\"timesheet.php\">Timesheet</a></li>";
                }
                if ($_SESSION['user_role'] == "recruiter") {
                    echo 
                    "<li><a href=\"post_job.php\">Post Job</a></li>
                    <li><a href=\"search_job.php\">Search & Edit Job</a></li>
                    <li><a href=\"timesheet.php\">Timesheet</a></li>";
                }
                if ($_SESSION['user_role'] == "administrator") {
                    echo 
                    "<li><a href=\"create_user.php\">Create User</a></li>
                    <li><a href=\"search_user.php\">Search & Edit User</a></li>
                    <li><a href=\"post_job.php\">Post Job</a></li>
                    <li><a href=\"search_job.php\">Search & Edit Job</a></li>
                    <li><a href=\"timesheet.php\">Timesheet</a></li>";
                }
            ?>
        </ul>
    </body>
</html>    