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
        <title>View Employment</title>
    </head>
    <body>
        <a href="home.php">Home</a><br /><br />
        <form method="post" action="#">
            <input type="submit" name="logout" value="Logout" /><br /><br />
        </form>
        <h2>View Employment</h2>
        <p>Below is employment information about the selected user:</p>
        <?php
            if (isset($_POST['logout'])) {
                include("logout.php");
                logout();
            }
            include("database.php");
            $stmt = $DBConnect->prepare("SELECT employments.username, clients.client_name, vendors.vendor_name, employments.job_position, employments.employment_type, employments.employment_start_date, employments.employment_status, employments.employment_end_date FROM employments INNER JOIN clients ON employments.client_id = clients.client_id LEFT OUTER JOIN vendors ON employments.vendor_id = vendors.vendor_id WHERE employments.username = ? ORDER BY employments.employment_start_date DESC");
            $stmt->bind_param("s", $_COOKIE["username"]); 
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($retrieved_username, $retrieved_client_name, $retrieved_vendor_name, $retrieved_job_position, $retrieved_employment_type, $retrieved_employment_start_date, $retrieved_employment_status, $retrieved_employment_end_date);
            
            if ($stmt->num_rows > 0) {
                $employment_count = 1;
                while($stmt->fetch()) {           
                    echo
                    "<p><u>Employment Number: $employment_count</u></p>
                    <p>Username: $retrieved_username</p>
                    <p>Client: $retrieved_client_name</p>
                    <p>Vendor: $retrieved_vendor_name</p>
                    <p>Job Position: $retrieved_job_position</p>
                    <p>Employment Type: $retrieved_employment_type</p>";
                    $array = explode("-", $retrieved_employment_start_date);
                    $formatted_retrieved_employment_start_date = $array[1]."/".$array[2]."/".$array[0];
                    echo
                    "<p>Employment Start Date: $formatted_retrieved_employment_start_date</p>
                    <p>Employment Status: $retrieved_employment_status</p>
                    <p>Employment End Date: $retrieved_employment_end_date</p>";
                    $employment_count++;
                }
            }
            else {
                echo "<p>Employment(s) not found.</p>";
            }
        ?>
    </body>
</html>