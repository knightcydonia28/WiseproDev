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
        <title>Create Job Posting</title>
        <script>
            function preventTwoChecks(checkbox) {
                var checkbox_name = document.getElementsByName(checkbox.name);
                var checkbox_id = document.getElementById(checkbox.id);

                if (checkbox_id.checked) {
                    document.getElementById("job_location").disabled = true;
                    for (var count=0; count<checkbox_name.length; count++) {
                        if (!checkbox_name[count].checked) {
                            checkbox_name[count].disabled = true;
                        }
                        else {
                            checkbox_name[count].disabled = false;
                        }
                    } 
                }
                else {
                    document.getElementById("job_location").disabled = false;
                    for (var count=0; count<checkbox_name.length; count++) {
                        checkbox_name[count].disabled = false;
                    } 
                }    
            }
            function preventOneCheck(checkbox) {
                var checkbox_id = document.getElementById("job_location_hybrid");
                if (checkbox_id.checked) {
                    document.getElementById("job_location_remote").disabled = true;
                }
                else {
                    document.getElementById("job_location_remote").disabled = false;
                }
            }
            function checkInput() {
                var value = document.getElementById("vendor_name").value;
                if (value == "") {
                    document.getElementById("vendor_rate").disabled = true;
                    document.getElementById("vendor_rate_asterisk").innerHTML = "";
                }
                else {
                    document.getElementById("vendor_rate").disabled = false;
                    document.getElementById("vendor_rate_asterisk").innerHTML = " *";
                }
            }
            function checkRemote() {
                var checkbox_id = document.getElementById("job_location_remote");
                if (checkbox_id.checked) {
                    document.getElementById("job_location").disabled = true;
                    document.getElementById("job_location_hybrid").disabled = true;
                }
                else {
                    document.getElementById("job_location").disabled = false;
                    document.getElementById("job_location_hybrid").disabled = false;
                }
            }
        </script>
        <style>
            label {
                display: inline-block;
                width: 100px;
            }
            .error {
                color: #FF0000;
            }
            input::-webkit-outer-spin-button, input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            input[type=number] {
                -moz-appearance: textfield;
            }
        </style>
    </head>
    <body onload="checkInput(); preventOneCheck(); checkRemote();">
        <a href="home.php">Home</a><br /><br />
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>Logout</a>
        <h2>Create Job Posting</h2>
        <p>Please fill the form below to create and post a job:</p>
        <p><span class="error">* required field</span></p>
        <?php
            if (isset($_POST['create_job_submit'])) {

                function test_input($data) {
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                }
                function validateDate($date, $format = 'Y-m-d') {
                    $d = DateTime::createFromFormat($format, $date);
                    return $d && $d->format($format) == $date;
                }

                if (empty($_POST['vendor_name']) && !isset($_POST['vendor_rate'])) {
                    if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['client_name'])) {
                        $client_name_error = "Please ensure that client name has letters and whitespaces only";
                    }
                    else {
                        $client_name = test_input($_POST['client_name']);
                        if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['job_title'])) {
                            $job_title_error = "Please ensure that job title has letters and whitespaces only";
                        }
                        else {
                            $job_title = test_input($_POST['job_title']);
                            if ($_POST['job_type'] != "full-time" && $_POST['job_type'] != "part-time" && $_POST['job_type'] != "contract" && $_POST['job_type'] != "internship") {
                                $job_type_error = "Please select an appropriate job type";
                            }
                            else {
                                $job_type = test_input($_POST['job_type']);
                                if (!isset($_POST['job_location'])) {
                                    if ($_POST['job_location_alternative'] != "Remote") {
                                        $job_location_error = "Please ensure that job location is remote";
                                    }
                                    else {
                                        $job_location = test_input($_POST['job_location_alternative']);
                                        $job_description = $_POST['job_description'];
                                        if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['preferred_skills'])) {
                                            $preferred_skills_error = "Please ensure that preferred skills have letters, commas and whitespaces only";
                                        }
                                        else {
                                            $preferred_skills = test_input($_POST['preferred_skills']);
                                            if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['required_skills'])) {
                                                $required_skills_error = "Please ensure that required skills have letters, commas and whitespaces only";
                                            }
                                            else {
                                                $required_skills = test_input($_POST['required_skills']);
                                                $job_posted_date = test_input($_POST['job_posted_date']);
                                                if (!validateDate($job_posted_date)) {
                                                    $job_posted_date_error = "Please enter a valid job posted date"; 
                                                }
                                                else {
                                                    if ($_POST['job_status'] != "active") {
                                                        $job_status_error = "Please ensure that the status of the job is active upon creation";
                                                    }
                                                    else {
                                                        $job_status = test_input($_POST['job_status']);
                                                        include("database.php");
                                                        $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                        $stmt->bind_param("s", $client_name); 
                                                        $stmt->execute();
                                                        $stmt->store_result();
                                                        $stmt->bind_result($retrieved_client_id);
                                                        if ($stmt->num_rows > 0) {
                                                            $stmt->fetch();
                                                            include("database.php");
                                                            $stmt = $DBConnect->prepare("INSERT INTO jobs (client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                            $stmt->bind_param("issssssss", $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                            if ($stmt->execute()) {
                                                                echo "<p>Job was successfully created and posted.</p>";
                                                            }
                                                            else {
                                                                echo "<p>Job was not successfully created and posted.</p>";
                                                            }
                                                        }
                                                        else {
                                                            include("database.php");
                                                            $stmt = $DBConnect->prepare("INSERT INTO clients (client_name) VALUES (?)");
                                                            $stmt->bind_param("s", $client_name);
                                                            if ($stmt->execute()) {
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                $stmt->bind_param("s", $client_name); 
                                                                $stmt->execute();
                                                                $stmt->store_result();
                                                                $stmt->bind_result($retrieved_client_id);
                                                                $stmt->fetch();

                                                                $stmt = $DBConnect->prepare("INSERT INTO jobs (client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                $stmt->bind_param("issssssss", $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                if ($stmt->execute()) {
                                                                    echo "<p>Job was successfully created and posted.</p>";
                                                                }
                                                                else {
                                                                    echo "<p>Job was not successfully created and posted.</p>";
                                                                }
                                                            }
                                                            else {
                                                                echo "<p>Job was not successfully created and posted.</p>";
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                else {
                                    if (isset($_POST['job_location_alternative'])) {
                                        if ($_POST['job_location_alternative'] == "Hybrid") {
                                            $job_location = test_input($_POST['job_location'])."; ".test_input($_POST['job_location_alternative']);
                                            $job_description = $_POST['job_description'];
                                            if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['preferred_skills'])) {
                                                $preferred_skills_error = "Please ensure that preferred skills have letters, commas and whitespaces only";
                                            }
                                            else {
                                                $preferred_skills = test_input($_POST['preferred_skills']);
                                                if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['required_skills'])) {
                                                    $required_skills_error = "Please ensure that required skills have letters, commas and whitespaces only";
                                                }
                                                else {
                                                    $required_skills = test_input($_POST['required_skills']);
                                                    $job_posted_date = test_input($_POST['job_posted_date']);
                                                    if (!validateDate($job_posted_date)) {
                                                        $job_posted_date_error = "Please enter a valid job posted date"; 
                                                    }
                                                    else {
                                                        if ($_POST['job_status'] != "active") {
                                                            $job_status_error = "Please ensure that the status of the job is active upon creation";
                                                        }
                                                        else {
                                                            $job_status = test_input($_POST['job_status']);
                                                            include("database.php");
                                                            $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                            $stmt->bind_param("s", $client_name); 
                                                            $stmt->execute();
                                                            $stmt->store_result();
                                                            $stmt->bind_result($retrieved_client_id);
                                                            if ($stmt->num_rows > 0) {
                                                                $stmt->fetch();
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("INSERT INTO jobs (client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                $stmt->bind_param("issssssss", $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                if ($stmt->execute()) {
                                                                    echo "<p>Job was successfully created and posted.</p>";
                                                                }
                                                                else {
                                                                    echo "<p>Job was not successfully created and posted.</p>";
                                                                }
                                                            }
                                                            else {
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("INSERT INTO clients (client_name) VALUES (?)");
                                                                $stmt->bind_param("s", $client_name);
                                                                if ($stmt->execute()) {
                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                    $stmt->bind_param("s", $client_name); 
                                                                    $stmt->execute();
                                                                    $stmt->store_result();
                                                                    $stmt->bind_result($retrieved_client_id);
                                                                    $stmt->fetch();

                                                                    $stmt = $DBConnect->prepare("INSERT INTO jobs (client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                    $stmt->bind_param("issssssss", $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                    if ($stmt->execute()) {
                                                                        echo "<p>Job was successfully created and posted.</p>";
                                                                    }
                                                                    else {
                                                                        echo "<p>Job was not successfully created and posted.</p>";
                                                                    }
                                                                }
                                                                else {
                                                                    echo "<p>Job was not successfully created and posted.</p>";
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    else {
                                        if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['job_location'])) {
                                            $job_location_error = "Please ensure that job location has letters, commas and whitespaces only";
                                        }
                                        else {
                                            $job_location = test_input($_POST['job_location']);
                                            $job_description = $_POST['job_description'];
                                            if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['preferred_skills'])) {
                                                $preferred_skills_error = "Please ensure that preferred skills have letters, commas and whitespaces only";
                                            }
                                            else {
                                                $preferred_skills = test_input($_POST['preferred_skills']);
                                                if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['required_skills'])) {
                                                    $required_skills_error = "Please ensure that required skills have letters, commas and whitespaces only";
                                                }
                                                else {
                                                    $required_skills = test_input($_POST['required_skills']);
                                                    $job_posted_date = test_input($_POST['job_posted_date']);
                                                    if (!validateDate($job_posted_date)) {
                                                        $job_posted_date_error = "Please enter a valid job posted date"; 
                                                    }
                                                    else {
                                                        if ($_POST['job_status'] != "active") {
                                                            $job_status_error = "Please ensure that the status of the job is active upon creation";
                                                        }
                                                        else {
                                                            $job_status = test_input($_POST['job_status']);
                                                            include("database.php");
                                                            $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                            $stmt->bind_param("s", $client_name); 
                                                            $stmt->execute();
                                                            $stmt->store_result();
                                                            $stmt->bind_result($retrieved_client_id);
                                                            if ($stmt->num_rows > 0) {
                                                                $stmt->fetch();
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("INSERT INTO jobs (client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                $stmt->bind_param("issssssss", $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                if ($stmt->execute()) {
                                                                    echo "<p>Job was successfully created and posted.</p>";
                                                                }
                                                                else {
                                                                    echo "<p>Job was not successfully created and posted.</p>";
                                                                }
                                                            }
                                                            else {
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("INSERT INTO clients (client_name) VALUES (?)");
                                                                $stmt->bind_param("s", $client_name);
                                                                if ($stmt->execute()) {
                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                    $stmt->bind_param("s", $client_name); 
                                                                    $stmt->execute();
                                                                    $stmt->store_result();
                                                                    $stmt->bind_result($retrieved_client_id);
                                                                    $stmt->fetch();

                                                                    $stmt = $DBConnect->prepare("INSERT INTO jobs (client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                    $stmt->bind_param("issssssss", $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                    if ($stmt->execute()) {
                                                                        echo "<p>Job was successfully created and posted.</p>";
                                                                    }
                                                                    else {
                                                                        echo "<p>Job was not successfully created and posted.</p>";
                                                                    }
                                                                }
                                                                else {
                                                                    echo "<p>Job was not successfully created and posted.</p>";
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }  
                                    }
                                }
                            }
                        }
                    }
                }
                else {
                    if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['vendor_name'])) {
                        $vendor_name_error = "Please ensure that vendor name has letters and whitespaces only";
                    }
                    else {
                        $vendor_name = test_input($_POST['vendor_name']);
                        if (!is_numeric($_POST['vendor_rate'])) {
                            $vendor_rate_error = "Please ensure that vendor rate is numeric";
                        }
                        else {
                            $vendor_rate = test_input($_POST['vendor_rate']);
                            if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['client_name'])) {
                                $client_name_error = "Please ensure that client name has letters and whitespaces only";
                            }
                            else {
                                $client_name = test_input($_POST['client_name']);
                                if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['job_title'])) {
                                    $job_title_error = "Please ensure that job title has letters and whitespaces only";
                                }
                                else {
                                    $job_title = test_input($_POST['job_title']);
                                    if ($_POST['job_type'] != "full-time" && $_POST['job_type'] != "part-time" && $_POST['job_type'] != "contract" && $_POST['job_type'] != "internship") {
                                        $job_type_error = "Please select an appropriate job type";
                                    }
                                    else {
                                        $job_type = test_input($_POST['job_type']);
                                        if (!isset($_POST['job_location'])) {
                                            if ($_POST['job_location_alternative'] != "Remote") {
                                                $job_location_error = "Please ensure that job location is remote";
                                            }
                                            else {
                                                $job_location = test_input($_POST['job_location_alternative']);
                                                $job_description = $_POST['job_description'];
                                                if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['preferred_skills'])) {
                                                    $preferred_skills_error = "Please ensure that preferred skills have letters, commas and whitespaces only";
                                                }
                                                else {
                                                    $preferred_skills = test_input($_POST['preferred_skills']);
                                                    if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['required_skills'])) {
                                                        $required_skills_error = "Please ensure that required skills have letters, commas and whitespaces only";
                                                    }
                                                    else {
                                                        $required_skills = test_input($_POST['required_skills']);
                                                        $job_posted_date = test_input($_POST['job_posted_date']);
                                                        if (!validateDate($job_posted_date)) {
                                                            $job_posted_date_error = "Please enter a valid job posted date"; 
                                                        }
                                                        else {
                                                            if ($_POST['job_status'] != "active") {
                                                                $job_status_error = "Please ensure that the status of the job is active upon creation";
                                                            }
                                                            else {
                                                                $job_status = test_input($_POST['job_status']);
                                                                include("database.php");
                                                                $stmt = $DBConnect->prepare("SELECT vendor_id FROM vendors WHERE vendor_name = ?");
                                                                $stmt->bind_param("s", $vendor_name);
                                                                $stmt->execute();
                                                                $stmt->store_result();
                                                                $stmt->bind_result($retrieved_vendor_id);
                                                                if ($stmt->num_rows > 0) {
                                                                    $stmt->fetch();
                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                    $stmt->bind_param("s", $client_name); 
                                                                    $stmt->execute();
                                                                    $stmt->store_result();
                                                                    $stmt->bind_result($retrieved_client_id);
                                                                    if ($stmt->num_rows > 0) {
                                                                        $stmt->fetch();
                                                                        include("database.php");
                                                                        $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                        $stmt->bind_param("idissssssss", $retrieved_vendor_id, $vendor_rate, $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                        if ($stmt->execute()) {
                                                                            echo "<p>Job was successfully created and posted.</p>";
                                                                        }
                                                                        else {
                                                                            echo "<p>Job was not successfully created and posted.</p>";
                                                                        }
                                                                    }
                                                                    else {
                                                                        include("database.php");
                                                                        $stmt = $DBConnect->prepare("INSERT INTO clients (client_name) VALUES (?)");
                                                                        $stmt->bind_param("s", $client_name);
                                                                        if ($stmt->execute()) {
                                                                            include("database.php");
                                                                            $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                            $stmt->bind_param("s", $client_name); 
                                                                            $stmt->execute();
                                                                            $stmt->store_result();
                                                                            $stmt->bind_result($retrieved_client_id);
                                                                            $stmt->fetch();
        
                                                                            $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                            $stmt->bind_param("idissssssss", $retrieved_vendor_id, $vendor_rate, $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                            if ($stmt->execute()) {
                                                                                echo "<p>Job was successfully created and posted.</p>";
                                                                            }
                                                                            else {
                                                                                echo "<p>Job was not successfully created and posted.</p>";
                                                                            }
                                                                        }
                                                                        else {
                                                                            echo "<p>Job was not successfully created and posted.</p>";
                                                                        }
                                                                    }
                                                                }
                                                                else {
                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("INSERT INTO vendors (vendor_name) VALUES (?)");
                                                                    $stmt->bind_param("s", $vendor_name);
                                                                    if ($stmt->execute()) {
                                                                        include("database.php");
                                                                        $stmt = $DBConnect->prepare("SELECT vendor_id FROM vendors WHERE vendor_name = ?");
                                                                        $stmt->bind_param("s", $vendor_name); 
                                                                        $stmt->execute();
                                                                        $stmt->store_result();
                                                                        $stmt->bind_result($retrieved_vendor_id);
                                                                        $stmt->fetch();

                                                                        $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                        $stmt->bind_param("s", $client_name); 
                                                                        $stmt->execute();
                                                                        $stmt->store_result();
                                                                        $stmt->bind_result($retrieved_client_id);
                                                                        if ($stmt->num_rows > 0) {
                                                                            $stmt->fetch();
                                                                            include("database.php");
                                                                            $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                            $stmt->bind_param("idissssssss", $retrieved_vendor_id, $vendor_rate, $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                            if ($stmt->execute()) {
                                                                                echo "<p>Job was successfully created and posted.</p>";
                                                                            }
                                                                            else {
                                                                                echo "<p>Job was not successfully created and posted.</p>";
                                                                            }
                                                                        }
                                                                        else {
                                                                            include("database.php");
                                                                            $stmt = $DBConnect->prepare("INSERT INTO clients (client_name) VALUES (?)");
                                                                            $stmt->bind_param("s", $client_name);
                                                                            if ($stmt->execute()) {
                                                                                include("database.php");
                                                                                $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                                $stmt->bind_param("s", $client_name); 
                                                                                $stmt->execute();
                                                                                $stmt->store_result();
                                                                                $stmt->bind_result($retrieved_client_id);
                                                                                $stmt->fetch();
            
                                                                                $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                                $stmt->bind_param("idissssssss", $retrieved_vendor_id, $vendor_rate, $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                                if ($stmt->execute()) {
                                                                                    echo "<p>Job was successfully created and posted.</p>";
                                                                                }
                                                                                else {
                                                                                    echo "<p>Job was not successfully created and posted.</p>";
                                                                                }
                                                                            }
                                                                            else {
                                                                                echo "<p>Job was not successfully created and posted.</p>";
                                                                            }
                                                                        }
                                                                    }
                                                                    else {
                                                                        echo "<p>Job was not successfully created and posted.</p>";
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        else {
                                            if (isset($_POST['job_location_alternative'])) {
                                                if ($_POST['job_location_alternative'] == "Hybrid") {
                                                    $job_location = test_input($_POST['job_location'])."; ".test_input($_POST['job_location_alternative']);
                                                    $job_description = $_POST['job_description'];
                                                    if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['preferred_skills'])) {
                                                        $preferred_skills_error = "Please ensure that preferred skills have letters, commas and whitespaces only";
                                                    }
                                                    else {
                                                        $preferred_skills = test_input($_POST['preferred_skills']);
                                                        if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['required_skills'])) {
                                                            $required_skills_error = "Please ensure that required skills have letters, commas and whitespaces only";
                                                        }
                                                        else {
                                                            $required_skills = test_input($_POST['required_skills']);
                                                            $job_posted_date = test_input($_POST['job_posted_date']);
                                                            if (!validateDate($job_posted_date)) {
                                                                $job_posted_date_error = "Please enter a valid job posted date"; 
                                                            }
                                                            else {
                                                                if ($_POST['job_status'] != "active") {
                                                                    $job_status_error = "Please ensure that the status of the job is active upon creation";
                                                                }
                                                                else {
                                                                    $job_status = test_input($_POST['job_status']);
                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("SELECT vendor_id FROM vendors WHERE vendor_name = ?");
                                                                    $stmt->bind_param("s", $vendor_name);
                                                                    $stmt->execute();
                                                                    $stmt->store_result();
                                                                    $stmt->bind_result($retrieved_vendor_id);
                                                                    if ($stmt->num_rows > 0) {
                                                                        $stmt->fetch();
                                                                        include("database.php");
                                                                        $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                        $stmt->bind_param("s", $client_name); 
                                                                        $stmt->execute();
                                                                        $stmt->store_result();
                                                                        $stmt->bind_result($retrieved_client_id);
                                                                        if ($stmt->num_rows > 0) {
                                                                            $stmt->fetch();
                                                                            include("database.php");
                                                                            $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                            $stmt->bind_param("idissssssss", $retrieved_vendor_id, $vendor_rate, $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                            if ($stmt->execute()) {
                                                                                echo "<p>Job was successfully created and posted.</p>";
                                                                            }
                                                                            else {
                                                                                echo "<p>Job was not successfully created and posted.</p>";
                                                                            }
                                                                        }
                                                                        else {
                                                                            include("database.php");
                                                                            $stmt = $DBConnect->prepare("INSERT INTO clients (client_name) VALUES (?)");
                                                                            $stmt->bind_param("s", $client_name);
                                                                            if ($stmt->execute()) {
                                                                                include("database.php");
                                                                                $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                                $stmt->bind_param("s", $client_name); 
                                                                                $stmt->execute();
                                                                                $stmt->store_result();
                                                                                $stmt->bind_result($retrieved_client_id);
                                                                                $stmt->fetch();
            
                                                                                $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                                $stmt->bind_param("idissssssss", $retrieved_vendor_id, $vendor_rate, $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                                if ($stmt->execute()) {
                                                                                    echo "<p>Job was successfully created and posted.</p>";
                                                                                }
                                                                                else {
                                                                                    echo "<p>Job was not successfully created and posted.</p>";
                                                                                }
                                                                            }
                                                                            else {
                                                                                echo "<p>Job was not successfully created and posted.</p>";
                                                                            }
                                                                        }
                                                                    }
                                                                    else {
                                                                        include("database.php");
                                                                        $stmt = $DBConnect->prepare("INSERT INTO vendors (vendor_name) VALUES (?)");
                                                                        $stmt->bind_param("s", $vendor_name);
                                                                        if ($stmt->execute()) {
                                                                            include("database.php");
                                                                            $stmt = $DBConnect->prepare("SELECT vendor_id FROM vendors WHERE vendor_name = ?");
                                                                            $stmt->bind_param("s", $vendor_name); 
                                                                            $stmt->execute();
                                                                            $stmt->store_result();
                                                                            $stmt->bind_result($retrieved_vendor_id);
                                                                            $stmt->fetch();
    
                                                                            $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                            $stmt->bind_param("s", $client_name); 
                                                                            $stmt->execute();
                                                                            $stmt->store_result();
                                                                            $stmt->bind_result($retrieved_client_id);
                                                                            if ($stmt->num_rows > 0) {
                                                                                $stmt->fetch();
                                                                                include("database.php");
                                                                                $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                                $stmt->bind_param("idissssssss", $retrieved_vendor_id, $vendor_rate, $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                                if ($stmt->execute()) {
                                                                                    echo "<p>Job was successfully created and posted.</p>";
                                                                                }
                                                                                else {
                                                                                    echo "<p>Job was not successfully created and posted.</p>";
                                                                                }
                                                                            }
                                                                            else {
                                                                                include("database.php");
                                                                                $stmt = $DBConnect->prepare("INSERT INTO clients (client_name) VALUES (?)");
                                                                                $stmt->bind_param("s", $client_name);
                                                                                if ($stmt->execute()) {
                                                                                    include("database.php");
                                                                                    $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                                    $stmt->bind_param("s", $client_name); 
                                                                                    $stmt->execute();
                                                                                    $stmt->store_result();
                                                                                    $stmt->bind_result($retrieved_client_id);
                                                                                    $stmt->fetch();
                
                                                                                    $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                                    $stmt->bind_param("idissssssss", $retrieved_vendor_id, $vendor_rate, $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                                    if ($stmt->execute()) {
                                                                                        echo "<p>Job was successfully created and posted.</p>";
                                                                                    }
                                                                                    else {
                                                                                        echo "<p>Job was not successfully created and posted.</p>";
                                                                                    }
                                                                                }
                                                                                else {
                                                                                    echo "<p>Job was not successfully created and posted.</p>";
                                                                                }
                                                                            }
                                                                        }
                                                                        else {
                                                                            echo "<p>Job was not successfully created and posted.</p>";
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            else {
                                                if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['job_location'])) {
                                                    $job_location_error = "Please ensure that job location has letters, commas and whitespaces only";
                                                }
                                                else {
                                                    $job_location = test_input($_POST['job_location']);
                                                    $job_description = $_POST['job_description'];
                                                    if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['preferred_skills'])) {
                                                        $preferred_skills_error = "Please ensure that preferred skills have letters, commas and whitespaces only";
                                                    }
                                                    else {
                                                        $preferred_skills = test_input($_POST['preferred_skills']);
                                                        if (!preg_match("/^[a-zA-Z,\s]*$/", $_POST['required_skills'])) {
                                                            $required_skills_error = "Please ensure that required skills have letters, commas and whitespaces only";
                                                        }
                                                        else {
                                                            $required_skills = test_input($_POST['required_skills']);
                                                            $job_posted_date = test_input($_POST['job_posted_date']);
                                                            if (!validateDate($job_posted_date)) {
                                                                $job_posted_date_error = "Please enter a valid job posted date"; 
                                                            }
                                                            else {
                                                                if ($_POST['job_status'] != "active") {
                                                                    $job_status_error = "Please ensure that the status of the job is active upon creation";
                                                                }
                                                                else {
                                                                    $job_status = test_input($_POST['job_status']);
                                                                    include("database.php");
                                                                    $stmt = $DBConnect->prepare("SELECT vendor_id FROM vendors WHERE vendor_name = ?");
                                                                    $stmt->bind_param("s", $vendor_name);
                                                                    $stmt->execute();
                                                                    $stmt->store_result();
                                                                    $stmt->bind_result($retrieved_vendor_id);
                                                                    if ($stmt->num_rows > 0) {
                                                                        $stmt->fetch();
                                                                        include("database.php");
                                                                        $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                        $stmt->bind_param("s", $client_name); 
                                                                        $stmt->execute();
                                                                        $stmt->store_result();
                                                                        $stmt->bind_result($retrieved_client_id);
                                                                        if ($stmt->num_rows > 0) {
                                                                            $stmt->fetch();
                                                                            include("database.php");
                                                                            $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                            $stmt->bind_param("idissssssss", $retrieved_vendor_id, $vendor_rate, $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                            if ($stmt->execute()) {
                                                                                echo "<p>Job was successfully created and posted.</p>";
                                                                            }
                                                                            else {
                                                                                echo "<p>Job was not successfully created and posted.</p>";
                                                                            }
                                                                        }
                                                                        else {
                                                                            include("database.php");
                                                                            $stmt = $DBConnect->prepare("INSERT INTO clients (client_name) VALUES (?)");
                                                                            $stmt->bind_param("s", $client_name);
                                                                            if ($stmt->execute()) {
                                                                                include("database.php");
                                                                                $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                                $stmt->bind_param("s", $client_name); 
                                                                                $stmt->execute();
                                                                                $stmt->store_result();
                                                                                $stmt->bind_result($retrieved_client_id);
                                                                                $stmt->fetch();
            
                                                                                $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                                $stmt->bind_param("idissssssss", $retrieved_vendor_id, $vendor_rate, $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                                if ($stmt->execute()) {
                                                                                    echo "<p>Job was successfully created and posted.</p>";
                                                                                }
                                                                                else {
                                                                                    echo "<p>Job was not successfully created and posted.</p>";
                                                                                }
                                                                            }
                                                                            else {
                                                                                echo "<p>Job was not successfully created and posted.</p>";
                                                                            }
                                                                        }
                                                                    }
                                                                    else {
                                                                        include("database.php");
                                                                        $stmt = $DBConnect->prepare("INSERT INTO vendors (vendor_name) VALUES (?)");
                                                                        $stmt->bind_param("s", $vendor_name);
                                                                        if ($stmt->execute()) {
                                                                            include("database.php");
                                                                            $stmt = $DBConnect->prepare("SELECT vendor_id FROM vendors WHERE vendor_name = ?");
                                                                            $stmt->bind_param("s", $vendor_name); 
                                                                            $stmt->execute();
                                                                            $stmt->store_result();
                                                                            $stmt->bind_result($retrieved_vendor_id);
                                                                            $stmt->fetch();
    
                                                                            $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                            $stmt->bind_param("s", $client_name); 
                                                                            $stmt->execute();
                                                                            $stmt->store_result();
                                                                            $stmt->bind_result($retrieved_client_id);
                                                                            if ($stmt->num_rows > 0) {
                                                                                $stmt->fetch();
                                                                                include("database.php");
                                                                                $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                                $stmt->bind_param("idissssssss", $retrieved_vendor_id, $vendor_rate, $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                                if ($stmt->execute()) {
                                                                                    echo "<p>Job was successfully created and posted.</p>";
                                                                                }
                                                                                else {
                                                                                    echo "<p>Job was not successfully created and posted.</p>";
                                                                                }
                                                                            }
                                                                            else {
                                                                                include("database.php");
                                                                                $stmt = $DBConnect->prepare("INSERT INTO clients (client_name) VALUES (?)");
                                                                                $stmt->bind_param("s", $client_name);
                                                                                if ($stmt->execute()) {
                                                                                    include("database.php");
                                                                                    $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                                                                    $stmt->bind_param("s", $client_name); 
                                                                                    $stmt->execute();
                                                                                    $stmt->store_result();
                                                                                    $stmt->bind_result($retrieved_client_id);
                                                                                    $stmt->fetch();
                
                                                                                    $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                                                                    $stmt->bind_param("idissssssss", $retrieved_vendor_id, $vendor_rate, $retrieved_client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                                                                                    if ($stmt->execute()) {
                                                                                        echo "<p>Job was successfully created and posted.</p>";
                                                                                    }
                                                                                    else {
                                                                                        echo "<p>Job was not successfully created and posted.</p>";
                                                                                    }
                                                                                }
                                                                                else {
                                                                                    echo "<p>Job was not successfully created and posted.</p>";
                                                                                }
                                                                            }
                                                                        }
                                                                        else {
                                                                            echo "<p>Job was not successfully created and posted.</p>";
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }  
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $stmt->close();
                $DBConnect->close();
            }
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="job_id">Job Id:</label>
            <input type="text" id="job_id" name="job_id" value="new" readonly><br><br>
            <label for="vendor_name">Vendor:</label>
            <input list="vendors" name="vendor_name" id="vendor_name" placeholder="vendor" pattern="^[a-zA-Z\s]*$" title="Please ensure that vendor name has letters and whitespaces only" value="<?php echo $_POST['vendor_name'] ?>" onblur="checkInput()"><span class="error"> <?php echo $vendor_name_error; ?></span>
            <datalist id="vendors">
                <?php
                    include("database.php");
                    $stmt = $DBConnect->prepare("SELECT vendor_name FROM vendors");
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($retrieved_vendor_name);
                    while($stmt->fetch()) {
                        echo "<option value=\"$retrieved_vendor_name\"></option>";
                    }
                ?>
            </datalist><br /><br />
            <label for="vendor_rate">Vendor Rate:</label>
            <input type="number" id="vendor_rate" name="vendor_rate" placeholder="000.00" min="0" max="999" step="0.01" value="<?php echo $_POST['vendor_rate'] ?>" disabled/><span id="vendor_rate_asterisk" class="error"> <?php echo $vendor_rate_error; ?></span><br /><br />  
            <label for="client_name">Client:</label>
            <input list="clients" name="client_name" id="client_name" placeholder="client" pattern="^[a-zA-Z\s]*$" title="Please ensure that client name has letters and whitespaces only" value="<?php echo $_POST['client_name'] ?>" required><span class="error"> * <?php echo $client_name_error; ?></span>
            <datalist id="clients">
                <?php
                    include("database.php");
                    $stmt = $DBConnect->prepare("SELECT client_name FROM clients");
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($retrieved_client_name);
                    while($stmt->fetch()) {
                        echo "<option value=\"$retrieved_client_name\"></option>";
                    }
                ?>
            </datalist><br /><br />
            <label for="job_title">Job Title:</label>
            <input list="job_titles" name="job_title" id="job_title" placeholder="job title" pattern="^[a-zA-Z\s]*$" title="Please ensure that job title has letters and whitespaces only" value="<?php echo $_POST['job_title'] ?>" required /><span class="error"> * <?php echo $job_title_error; ?></span>
            <datalist id="job_titles">
                <?php
                    include("database.php");
                    $stmt = $DBConnect->prepare("SELECT job_title FROM jobs");
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($retrieved_job_title);
                    while($stmt->fetch()) {
                        echo "<option value=\"$retrieved_job_title\"></option>";
                    }
                    $stmt->close();
                    $DBConnect->close();
                ?>
            </datalist><br /><br />
            <label for="job_type">Job Type:</label>
            <select id="job_type" name="job_type" required>
                <option value="" <?php if (!isset($_POST['create_job_submit'])) {echo "selected";} ?> disabled>Select Job Type</option>
                <option value="full-time" <?php if (isset($_POST['create_job_submit']) && isset($_POST['job_type']) && $_POST['job_type'] == "full-time") {echo "selected";} ?>>Full-time</option>
                <option value="part-time" <?php if (isset($_POST['create_job_submit']) && isset($_POST['job_type']) && $_POST['job_type'] == "part-time") {echo "selected";} ?>>Part-time</option>
                <option value="contract" <?php if (isset($_POST['create_job_submit']) && isset($_POST['job_type']) && $_POST['job_type'] == "contract") {echo "selected";} ?>>Contract</option>
                <option value="internship" <?php if (isset($_POST['create_job_submit']) && isset($_POST['job_type']) && $_POST['job_type'] == "internship") {echo "selected";} ?>>Internship</option>
            </select><span class="error"> * <?php echo $job_type_error; ?></span><br /><br />
            <?php $job_location_pieces = explode(";", $job_location);?>
            <label for="job_location">Job Location:</label>
            <input list="usa_cities_and_states" name="job_location" id="job_location" placeholder="job location" pattern="^[a-zA-Z,\s]*$" title="Please ensure that job location has letters, commas and whitespaces only" value="<?php if ($job_location_pieces[0] != "Remote") {echo $job_location_pieces[0];} ?>" required>&nbsp;&nbsp;<input type="checkbox" name="job_location_alternative" id="job_location_remote" value="Remote" onclick="preventTwoChecks(this)" <?php if (isset($_POST['create_job_submit']) && isset($_POST['job_location']) && $_POST['job_location'] == "Remote") {echo "checked";} ?>>Remote&nbsp;&nbsp;<input type="checkbox" name="job_location_alternative" id="job_location_hybrid" value="Hybrid" onclick="preventOneCheck(this)" <?php if (isset($_POST['create_job_submit']) && isset($_POST['job_location']) && $job_location_pieces[1] == " Hybrid") {echo "checked";} ?>>Hybrid
            <datalist id="usa_cities_and_states">
                <?php
                    include("usa_cities_and_states.php");
                ?>
            </datalist><span class="error"> * <?php echo $job_location_error; ?></span><br /><br />
            <p><label for="job_description">Job Description:</label></p>
            <textarea id="job_description" name="job_description" placeholder="job description" rows="30" cols="50" required><?php echo $_POST['job_description']; ?></textarea><span class="error"> *</span><br /><br />
            <label for="preferred_skills">Preferred Skills:</label>
            <input type="text" id="preferred_skills" name="preferred_skills" placeholder="preferred skills" pattern="^[a-zA-Z,\s]*$" title="Please ensure that preferred skills have letters, commas and whitespaces only" value="<?php echo $_POST['preferred_skills']; ?>" required/><span class="error"> * <?php echo $preferred_skills_error; ?></span><br /><br />
            <label for="job_type">Required Skills:</label>
            <input type="text" id="required_skills" name="required_skills" placeholder="required skills" pattern="^[a-zA-Z,\s]*$" title="Please ensure that required skills have letters, commas and whitespaces only" value="<?php echo $_POST['required_skills']; ?>" required/><span class="error"> * <?php echo $required_skills_error; ?></span><br /><br />
            <?php $job_posted_date = date("Y-m-d"); ?>
            <input type="hidden" id="job_posted_date" name="job_posted_date" value="<?php echo $job_posted_date; ?>"><span class="error"> <?php echo $job_posted_date_error; ?></span>
            <input type="hidden" id="job_status" name="job_status" value="<?php echo "active"; ?>"><span class="error"> <?php echo $job_status_error; ?></span>
            <input type="submit" name="create_job_submit" value="Create & Post Job" />
        </form>
    </body>
</html>
