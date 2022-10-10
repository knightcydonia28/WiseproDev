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
    setcookie("home", 1);
    setcookie("search_user", "", time() - 3600);
    setcookie("search_job_posting", "", time() - 3600);
    setcookie("choose_timesheet", "", time() - 3600);
    setcookie("choose_employment", "", time() - 3600);
    unset($_SESSION['disable_choose_timesheet']);
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
        <title>Home</title>
    </head>
    <body>
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>Logout</a>
        <h4>Home Page</h4>
        <ul>
            <?php
                if ($_SESSION['user_role'] == "user") {
                    echo 
                    "<li><a href=\"timesheet.php\">Timesheet</a></li>";
                }
                if ($_SESSION['user_role'] == "recruiter") {
                    echo 
                    "<li><a href=\"create_job_posting.php\">Create Job Posting</a></li>
                    <li><a href=\"search_job_posting.php\">Search Job Posting</a></li>
                    <li><a href=\"timesheet.php\">Timesheet</a></li>";
                }
                if ($_SESSION['user_role'] == "administrator") {
                    echo 
                    "<li><a href=\"create_user.php\">Create User</a></li>
                    <li><a href=\"search_user.php\">Search User</a></li>
                    <li><a href=\"create_job_posting.php\">Create Job Posting</a></li>
                    <li><a href=\"search_job_posting.php\">Search Job Posting</a></li>
                    <li><a href=\"timesheet.php\">Timesheet</a></li>";
                }
            ?>
        </ul>
    </body>
</html>    