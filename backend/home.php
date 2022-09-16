<?php
    session_start();
    if (!isset($_SESSION['login_status'])) {
        header('Location: login.php');
    }
    if ($_SESSION['password_expiration'] == 0) {
        header('Location: change_password.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Home</title>
    </head>
    <body>
        <?php
            unset($_SESSION['search_user']);
            unset($_SESSION['edit_user_authentication']);
            if (isset($_POST['logout'])) {
                include("logout.php");
                logout();
            }
        ?>
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
        <form method="post" action="#">
        <input type="submit" name="logout" value="Logout" />
        </form>
    </body>
</html>    