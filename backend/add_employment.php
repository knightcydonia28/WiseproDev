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
            if (time() - $_SESSION['login_time'] > 90000000) {
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
        <title>Add Employment</title>
        <style>
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
        <form method="post" action="#">
            <input type="submit" name="logout" value="Logout" />
        </form>
        <h2>Add Employment</h2>
        <p>Please fill the form below to add an employment for the selected user:</p>
        <p><span class="error">* required field</span></p>
        <?php
            if (isset($_POST['logout'])) {
                include("logout.php");
                logout();
            }
            if (isset($_POST['add_another_employment_submit'])) {
                header('Location: add_employment.php');
                exit();
            }
            if (isset($_POST['add_employment_submit'])) {
                
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
                
                $username = test_input($_POST['username']);
                if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['client_name'])) {
                    $client_name_error = "Please ensure that client name has letters and whitespaces only";
                }
                else {
                    $client_name = test_input($_POST['client_name']);
                    if (empty($_POST['vendor_name'])) {
                        $vendor_name = NULL;
                        if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['job_position'])) {
                            $job_position_error = "Please ensure that job position has letters and whitespaces only";
                        }
                        else {
                            $job_position = test_input($_POST['job_position']);
                            if ($_POST['employment_type'] != "full-time" && $_POST['employment_type'] != "part-time" && $_POST['employment_type'] != "contract" && $_POST['employment_type'] != "internship") {
                                $employment_type_error = "Please select an appropriate employment type";
                            }
                            else {
                                $employment_type = test_input($_POST['employment_type']);
                                $employment_start_date = test_input($_POST['employment_start_date']);
                                if (!validateDate($employment_start_date)) {
                                    $employment_start_date = "Please enter a valid employment start date"; 
                                }
                                else {
                                    if ($_POST['employment_status'] != "employed") {
                                        $employment_status_error = "Please ensure that the employment status of the user is employed when adding employment";
                                    }
                                    else {
                                        $employment_status = test_input($_POST['employment_status']);
                                        include("database.php");
                                        $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                                        $stmt->bind_param("s", $client_name); 
                                        $stmt->execute();
                                        $stmt->store_result();
                                        $stmt->bind_result($retrieved_client_id);
                                        if ($stmt->num_rows > 0) {
                                            $stmt->fetch();
                                            include("database.php");
                                            $stmt = $DBConnect->prepare("INSERT INTO employments (username, client_id, vendor_id, job_position, employment_type, employment_start_date, employment_status, employment_end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                            $stmt->bind_param("siisssss", $username, $retrieved_client_id, $vendor_id, $job_position, $employment_type, $employment_start_date, $employment_status, $employment_end_date); 
                                            if ($stmt->execute()) {
                                                echo "<p>Employment was successfully added.</p>";
                                            }
                                            else {
                                                echo "<p>Employment was not successfully added.</p>";
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

                                                $stmt = $DBConnect->prepare("INSERT INTO employments (username, client_id, vendor_id, job_position, employment_type, employment_start_date, employment_status, employment_end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                                $stmt->bind_param("siisssss", $username, $retrieved_client_id, $vendor_id, $job_position, $employment_type, $employment_start_date, $employment_status, $employment_end_date); 
                                                if ($stmt->execute()) {
                                                    echo "<p>Employment was successfully added.</p>";
                                                }
                                                else {
                                                    echo "<p>Employment was not successfully added.</p>";
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
                            if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['job_position'])) {
                                $job_position_error = "Please ensure that job position has letters and whitespaces only";
                            }
                            else {
                                $job_position = test_input($_POST['job_position']);
                                if ($_POST['employment_type'] != "full-time" && $_POST['employment_type'] != "part-time" && $_POST['employment_type'] != "contract" && $_POST['employment_type'] != "internship") {
                                    $employment_type_error = "Please select an appropriate employment type";
                                }
                                else {
                                    $employment_type = test_input($_POST['employment_type']);
                                    $employment_start_date = test_input($_POST['employment_start_date']);
                                    if (!validateDate($employment_start_date)) {
                                        $employment_start_date = "Please enter a valid employment start date"; 
                                    }
                                    else {
                                        if ($_POST['employment_status'] != "employed") {
                                            $employment_status_error = "Please ensure that the employment status of the user is employed when adding employment";
                                        }
                                        else {
                                            $employment_status = test_input($_POST['employment_status']);
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
                                                    $stmt = $DBConnect->prepare("INSERT INTO employments (username, client_id, vendor_id, job_position, employment_type, employment_start_date, employment_status, employment_end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                                    $stmt->bind_param("siisssss", $username, $retrieved_client_id, $retrieved_vendor_id, $job_position, $employment_type, $employment_start_date, $employment_status, $employment_end_date); 
                                                    if ($stmt->execute()) {
                                                        echo "<p>Employment was successfully added.</p>";
                                                    }
                                                    else {
                                                        echo "<p>Employment was not successfully added.</p>";
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

                                                        $stmt = $DBConnect->prepare("INSERT INTO employments (username, client_id, vendor_id, job_position, employment_type, employment_start_date, employment_status, employment_end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                                        $stmt->bind_param("siisssss", $username, $retrieved_client_id, $retrieved_vendor_id, $job_position, $employment_type, $employment_start_date, $employment_status, $employment_end_date); 
                                                        if ($stmt->execute()) {
                                                            echo "<p>Employment was successfully added.</p>";
                                                        }
                                                        else {
                                                            echo "<p>Employment was not successfully added.</p>";
                                                        }
                                                    }
                                                    else {
                                                        echo "<p>Employment was not successfully added.</p>";
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
                                                        $stmt = $DBConnect->prepare("INSERT INTO employments (username, client_id, vendor_id, job_position, employment_type, employment_start_date, employment_status, employment_end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                                        $stmt->bind_param("siisssss", $username, $retrieved_client_id, $retrieved_vendor_id, $job_position, $employment_type, $employment_start_date, $employment_status, $employment_end_date); 
                                                        if ($stmt->execute()) {
                                                            echo "<p>Employment was successfully added.</p>";
                                                        }
                                                        else {
                                                            echo "<p>Employment was not successfully added.</p>";
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

                                                            $stmt = $DBConnect->prepare("INSERT INTO employments (username, client_id, vendor_id, job_position, employment_type, employment_start_date, employment_status, employment_end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                                            $stmt->bind_param("siisssss", $username, $retrieved_client_id, $retrieved_vendor_id, $job_position, $employment_type, $employment_start_date, $employment_status, $employment_end_date); 
                                                            if ($stmt->execute()) {
                                                                echo "<p>Employment was successfully added.</p>";
                                                            }
                                                            else {
                                                                echo "<p>Employment was not successfully added.</p>";
                                                            }
                                                        }
                                                        else {
                                                            echo "<p>Employment was not successfully added.</p>";
                                                        }
                                                    }
                                                }
                                                else {
                                                    echo "<p>Employment was not successfully added.</p>";
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
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric" value="<?php if (isset($_COOKIE["username"])) {echo $_COOKIE["username"];} ?>" readonly required /><br /><br />
            <label for="client_name">Client:</label>
            <input list="clients" name="client_name" id="client_name" placeholder="client" pattern="^[a-zA-Z\s]*$" title="Please ensure that client name has letters and whitespaces only" required><span class="error"> * <?php echo $client_name_error; ?></span>
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
            <label for="vendor_name">Vendor:</label>
            <input list="vendors" name="vendor_name" id="vendor_name" placeholder="vendor" pattern="^[a-zA-Z\s]*$" title="Please ensure that vendor name has letters and whitespaces only"><span class="error"> <?php echo $vendor_name_error; ?></span>
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
                        $stmt->close();
                        $DBConnect->close();
                    ?>
                </datalist><br /><br />
            <label for="job_position">Job Position:</label>
            <input type="text" id="job_position" name="job_position" placeholder="job position" pattern="^[a-zA-Z\s]*$" title="Please ensure that job position has letters and whitespaces only" required /><span class="error"> * <?php echo $job_position_error; ?></span><br /><br />
            <label for="employment_type">Employment Type:</label>
            <select id="employment_type" name="employment_type" required>
                <option value="" <?php if (!isset($_POST['add_employment_submit'])) {echo "selected";} ?> disabled>Select Employment Type</option>
                <option value="full-time">Full-time</option>
                <option value="part-time">Part-time</option>
                <option value="contract">Contract</option>
                <option value="internship">Internship</option>
            </select><span class="error"> * <?php echo $employment_type_error; ?></span><br /><br />
            <label for="employment_start_date">Employment Start Date:</label>
            <input type="date" id="employment_start_date" name="employment_start_date" required /><span class="error"> * <?php echo $employment_start_date_error; ?></span><br /><br />
            <input type="hidden" id="employment_status" name="employment_status" value="employed" /><span class="error"> <?php echo $employment_status_error; ?></span>
            <input type="submit" name="add_employment_submit" value="Add Employment" />
        </form>
    </body>
</html>