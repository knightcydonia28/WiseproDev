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
    setcookie("search_job_posting", 1);
    setcookie("home", "", time() - 3600);
    setcookie("choose_timesheet", "", time() - 3600);
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
        <title>Search Job Posting</title>
        <style>
            table, th, td {
                border:1px solid black;
            }
            label {
                display: inline-block;
                width: 100px;
            }
            .error {
                color: #FF0000;
            }
        </style>
    </head>
    <body>
    <a href="home.php">Home</a><br /><br />
        <?php
            include("logout.php");
        ?>
    <a href='?logout=true'>Logout</a>
    <h2>Search Job Posting:</h2>
    <p>Please fill in one, some, or all of the following:</p>
    <?php
        if (isset($_POST['search_job_posting_submit'])) {
            function test_input($data) {
                $data = trim($data);
                $data = stripslashes($data);
                $data = htmlspecialchars($data);
                return $data;
            }

            if (empty($_POST['vendor_name']) && empty($_POST['client_name']) && empty($_POST['job_title']) && empty($_POST['job_type']) && empty($_POST['job_location']) && empty($_POST['month']) && empty($_POST['year']) && empty($_POST['job_status'])) {
                echo "<p class=\"error\">Please fill in at least one of the input fields.</p>";
            }
            else {
                if (!empty($_POST['vendor_name'])) {
                    if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['vendor_name'])) {
                        $vendor_name_error = "Please ensure that vendor name has letters and whitespaces only";
                    }
                    else {
                        $vendor_name = test_input($_POST['vendor_name']);
                        include("database.php");
                        $stmt = $DBConnect->prepare("SELECT vendor_id FROM vendors WHERE vendor_name = ?");
                        $stmt->bind_param("s", $vendor_name); 
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($retrieved_vendor_id);
                        $stmt->fetch();
                    }    
                }
                else {
                    $vendor_name = NULL;
                }
                if (!empty($_POST['client_name'])) {
                    if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['client_name'])) {
                        $client_name_error = "Please ensure that client name has letters and whitespaces only";
                    }
                    else {
                        $client_name = test_input($_POST['client_name']);
                        include("database.php");
                        $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                        $stmt->bind_param("s", $client_name); 
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($retrieved_client_id);
                        $stmt->fetch();
                    }    
                }
                else {
                    $client_name = NULL;
                }
                if (!empty($_POST['job_title'])) {
                    if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['job_title'])) {
                        $job_title_error = "Please ensure that job title has letters and whitespaces only";
                    }
                    else {
                        $job_title = test_input($_POST['job_title']);
                    }    
                }
                else {
                    $job_title = NULL;
                }
                if (!empty($_POST['job_type'])) {
                    if ($_POST['job_type'] != "full-time" && $_POST['job_type'] != "part-time" && $_POST['job_type'] != "contract" && $_POST['job_type'] != "internship") {
                        $job_type_error = "Please select an appropriate job type";
                    }
                    else {
                        $job_type = test_input($_POST['job_type']);
                    }    
                }
                else {
                    $job_type = NULL;
                }
                if (!empty($_POST['job_location'])) {
                    if (!preg_match("/^[a-zA-Z,;\s]*$/", $_POST['job_location'])) {
                        $job_location_error = "Please ensure that job location has letters, commas, semicolons, and whitespaces only";
                    }
                    else {
                        $job_location = test_input($_POST['job_location']);
                    }    
                }
                else {
                    $job_location = NULL;
                }
                if (!empty($_POST['month'])) {
                    if (!is_numeric($_POST['month'])) {
                        $month_error = "Please ensure that month is numeric";
                    }
                    else {
                        $month = test_input($_POST['month']);
                    }    
                }
                else {
                    $month = NULL;
                }
                if (!empty($_POST['year'])) {
                    if (!is_numeric($_POST['year'])) {
                        $year_error = "Please ensure that year is numeric";
                    }
                    else {
                        $year = test_input($_POST['year']);
                    }    
                }
                else {
                    $year = NULL;
                }
                if (!empty($_POST['job_status'])) {
                    if ($_POST['job_status'] != "active" && $_POST['job_status'] != "inactive") {
                        $job_status_error = "Please select the appropriate job status";
                    }
                    else {
                        $job_status = test_input($_POST['job_status']);
                    }    
                }
                else {
                    $job_status = NULL;
                }
            }
        }
    ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="vendor_name">Vendor:</label>
        <input list="vendors" name="vendor_name" id="vendor_name" placeholder="vendor" pattern="^[a-zA-Z\s]*$" title="Please ensure that vendor name has letters and whitespaces only"><span class="error"> <?php echo $vendor_name_error; ?></span>
            <datalist id="vendors">
            <?php
                include("database.php");
                $stmt = $DBConnect->prepare("SELECT DISTINCT vendors.vendor_name FROM jobs INNER JOIN vendors ON jobs.vendor_id = vendors.vendor_id");
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($retrieved_vendor_name);
                while($stmt->fetch()) {
                    echo "<option value=\"$retrieved_vendor_name\"></option>";
                }
            ?>
            </datalist><br /><br />
        <label for="client_name">Client:</label>
        <input list="clients" name="client_name" id="client_name" placeholder="client" pattern="^[a-zA-Z\s]*$" title="Please ensure that client name has letters and whitespaces only"><span class="error"> <?php echo $client_name_error; ?></span>
            <datalist id="clients">
            <?php
                include("database.php");
                $stmt = $DBConnect->prepare("SELECT DISTINCT clients.client_name FROM jobs INNER JOIN clients ON jobs.client_id = clients.client_id");
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($retrieved_client_name);
                while($stmt->fetch()) {
                    echo "<option value=\"$retrieved_client_name\"></option>";
                }
            ?>
            </datalist><br /><br />
        <label for="job_title">Job Title:</label>
        <input list="job_titles" name="job_title" id="job_title" placeholder="job title" pattern="^[a-zA-Z\s]*$" title="Please ensure that job title has letters and whitespaces only"><span class="error"> <?php echo $job_title_error; ?></span>
            <datalist id="job_titles">
            <?php
                include("database.php");
                $stmt = $DBConnect->prepare("SELECT DISTINCT job_title FROM jobs");
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($retrieved_job_title);
                while($stmt->fetch()) {
                    echo "<option value=\"$retrieved_job_title\"></option>";
                }
            ?>
            </datalist><br /><br />
        <label for="job_type">Job Type:</label>
        <select id="job_type" name="job_type">
                <option value="" selected disabled>Select Job Type</option>
                <option value="full-time">Full-time</option>
                <option value="part-time">Part-time</option>
                <option value="contract">Contract</option>
                <option value="internship">Internship</option>
        </select><span class="error"> <?php echo $job_type_error; ?></span><br /><br />
        <label for="job_location">Job Location:</label>
        <input list="job_locations" name="job_location" id="job_location" placeholder="job location" pattern="^[a-zA-Z,;\s]*$" title="Please ensure that job location has letters, commas, semicolons, and whitespaces only">
            <datalist id="job_locations">
                <?php
                    include("database.php");
                    $stmt = $DBConnect->prepare("SELECT DISTINCT job_location FROM jobs");
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($retrieved_job_location);
                    while($stmt->fetch()) {
                        echo "<option value=\"$retrieved_job_location\"></option>";
                    }
                ?>
            </datalist><br /><br />
        <label for="job_posted_date">Job Posted Date:</label>
        <select id="month" name="month">
            <option value="" selected disabled>Select Month</option>
            <option value="01">January</option>
            <option value="02">February</option>
            <option value="03">March</option>
            <option value="04">April</option>
            <option value="05">May</option>
            <option value="06">June</option>
            <option value="07">July</option>
            <option value="08">August</option>
            <option value="09">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
        </select>
        &nbsp;&nbsp;
        <select id="year" name="year">
            <option value="" selected disabled>Select Year</option>
            <?php
                $years_array = array(2022);
                $current_year = date('Y');
                for ($count = $current_year; $count >= $years_array[0]; $count--) {
                    echo "<option value=\"$count\">$count</option>";
                }
            ?>
        </select><span class="error"> <?php echo $month_error, $year_error; ?></span><br /><br />
        <label for="job_status">Job Status:</label>
        <select id="job_status" name="job_status">
            <option value="" selected disabled>Select Job Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select><span class="error"> <?php echo $job_status_error; ?></span><br /><br />
        <input type="submit" name="search_job_posting_submit" value="Search Job Posting" />
    </form>
    <?php
        if (isset($_POST['search_job_posting_submit'])) {
            include("database.php");
            $stmt = $DBConnect->prepare("SELECT jobs.job_id, vendors.vendor_name, clients.client_name, jobs.job_title, jobs.job_type, jobs.job_location, jobs.job_posted_date, jobs.job_status FROM jobs INNER JOIN clients ON jobs.client_id = clients.client_id LEFT OUTER JOIN vendors ON jobs.vendor_id = vendors.vendor_id WHERE jobs.vendor_id = ? OR jobs.client_id = ? OR jobs.job_title LIKE CONCAT(?, '%') OR jobs.job_type LIKE CONCAT(?, '%') OR jobs.job_location LIKE CONCAT(?, '%') OR MONTH(job_posted_date) = ? OR YEAR(job_posted_date) = ? OR jobs.job_status LIKE CONCAT(?, '%')");
            $stmt->bind_param("iisssiis", $retrieved_vendor_id, $retrieved_client_id, $job_title, $job_type, $job_location, $month, $year, $job_status);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($retrieved_job_id, $retrieved_vendor_name, $retrieved_client_name, $retrieved_job_title, $retrieved_job_type, $retrieved_job_location, $retrieved_job_posted_date, $retrieved_job_status);
            if ($stmt->num_rows > 0) {
                echo
                "<br />
                <table>
                    <tr>
                        <th>Job Id</th>
                        <th>Vendor Name</th>
                        <th>Client Name</th>
                        <th>Job Title</th>
                        <th>Job Type</th>
                        <th>Job Location</th>
                        <th>Job Posted Date</th>
                        <th>Job Status</th>
                    </tr>";
                while($stmt->fetch()) {
                    echo
                    "<tr>
                        <td>$retrieved_job_id</td>
                        <td>$retrieved_vendor_name</td>
                        <td>$retrieved_client_name</td>
                        <td>$retrieved_job_title</td>
                        <td>$retrieved_job_type</td>
                        <td>$retrieved_job_location</td>
                        <td>$retrieved_job_posted_date</td>
                        <td>$retrieved_job_status</td>
                    </tr>";
                }
                echo "</table>";
            }
            else {
                echo "<p>Job(s) not found.</p>";
            }
        }
    ?>
    </body>
</html>