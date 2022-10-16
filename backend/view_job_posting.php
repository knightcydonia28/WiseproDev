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
    if (!isset($_COOKIE['search_job_posting'])) {
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
        <title>View Job Posting</title>
    </head>
    <body>
        <a href="home.php">Home</a><br /><br />
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>Logout</a>
        <h2>View Job Posting</h2>
        <p>Below is information about the selected job posting:</p>
        <?php
            include("database.php");
            $stmt = $DBConnect->prepare("SELECT jobs.job_id, vendors.vendor_name, jobs.vendor_rate, clients.client_name, jobs.job_title, jobs.job_type, jobs.job_location, jobs.job_description, jobs.preferred_skills, jobs.required_skills, jobs.job_posted_date, jobs.job_status, jobs.job_expired_date FROM jobs INNER JOIN clients ON jobs.client_id = clients.client_id LEFT OUTER JOIN vendors ON jobs.vendor_id = vendors.vendor_id WHERE jobs.job_id = ?");
            $stmt->bind_param("s", $_COOKIE['job_id']); 
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($retrieved_job_id, $retrieved_vendor_name, $retrieved_vendor_rate, $retrieved_client_name, $retrieved_job_title, $retrieved_job_type, $retrieved_job_location, $retrieved_job_description, $retrieved_preferred_skills, $retrieved_required_skills, $retrieved_job_posted_date, $retrieved_job_status, $retrieved_job_expired_date);
            $stmt->fetch();
            $stmt->close();
            $DBConnect->close();
        ?>
        <p>Job Id: <?php echo $retrieved_job_id; ?></p>
        <p>Vendor: <?php echo $retrieved_vendor_name; ?></p>
        <p>Vendor Rate: <?php echo "$",$retrieved_vendor_rate; ?></p>
        <p>Client: <?php echo $retrieved_client_name; ?></p>
        <p>Job Title: <?php echo $retrieved_job_title; ?></p>
        <p>Job Type: <?php echo $retrieved_job_type; ?></p>
        <p>Job Location: <?php echo $retrieved_job_location; ?></p>
        <p>Job Description: <br /> <?php echo $retrieved_job_description; ?></p>
        <p>Preferred Skills: <?php echo $retrieved_preferred_skills; ?></p>
        <p>Required Skills: <?php echo $retrieved_required_skills; ?></p>
        <?php
            $job_posted_date = $retrieved_job_posted_date;
            $array = explode("-", $job_posted_date);
            $formatted_job_posted_date = $array[1]."/".$array[2]."/".$array[0];
        ?>
        <p>Job Posted Date: <?php echo $formatted_job_posted_date; ?></p>
        <p>Job Status: <?php echo $retrieved_job_status; ?></p>
        <?php
            if ($retrieved_job_expired_date != NULL) {
                $job_expired_date = $retrieved_job_expired_date;
                $array = explode("-", $job_expired_date);
                $formatted_job_expired_date = $array[1]."/".$array[2]."/".$array[0];
            }
        ?>
        <p>Job Expired Date: <?php echo $formatted_job_expired_date; ?></p>
    </body>
</html>