<?php
    //THIS DOCUMENT IS BEING TESTED FOR POST-REDIRECT-GET METHODOLOGY.
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
    if (!isset($_COOKIE['choose_employment'])) {
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
            if (time() - $_SESSION['login_time'] < 900) {
                $added_time = time() - $_SESSION['login_time'];
                $_SESSION['login_time'] += $added_time;
            }
        ?>
        <meta charset="UTF-8">
        <title>Edit Employment</title>
        <script>
            function checkDropdown() {
                var value = document.getElementById("employment_status").value;
                if (value == "unemployed") {
                    document.getElementById("employment_end_date").disabled = false;
                    document.getElementById("employment_end_date_asterisk").innerHTML = " *";
                }
                else {
                    document.getElementById("employment_end_date").disabled = true;
                    document.getElementById("employment_end_date_asterisk").innerHTML = "";
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
        </style>
    </head>
    <body onload="checkDropdown()">
        <a href="home.php">Home</a><br><br>
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>Logout</a>
        <h2>Edit Employment</h2>
        <p>Please fill the form below to edit the employment of the selected user:</p>
        <p><span class="error">* required field</span></p>
        <?php
            if ($_SERVER['REQUEST_METHOD'] === "POST") {
                
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
                
                if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['job_position'])) {
                    $_SESSION['job_position_error'] = "Please ensure that job position has letters and whitespaces only";
                    header("Location: edit_employment.php", true, 303);
                    exit();
                }
                else {
                    $job_position = test_input($_POST['job_position']);
                    if ($_POST['employment_type'] != "full-time" && $_POST['employment_type'] != "part-time" && $_POST['employment_type'] != "contract" && $_POST['employment_type'] != "internship") {
                        $_SESSION['employment_type_error'] = "Please select an appropriate employment type";
                        header("Location: edit_employment.php", true, 303);
                        exit();
                    }
                    else {
                        $employment_type = test_input($_POST['employment_type']);
                        $employment_start_date = test_input($_POST['employment_start_date']);
                        if (!validateDate($employment_start_date)) {
                            $_SESSION['employment_start_date_error'] = "Please enter a valid employment start date";
                            header("Location: edit_employment.php", true, 303);
                            exit(); 
                        }
                        else {
                            if ($_POST['employment_status'] != "employed" && $_POST['employment_status'] != "unemployed") {
                                $_SESSION['employment_status_error'] = "Please ensure that the employment status of the user is employed when adding employment";
                                header("Location: edit_employment.php", true, 303);
                                exit();
                            }
                            else {
                                $employment_status = test_input($_POST['employment_status']);
                                if ($employment_status == "employed") {
                                    $employment_end_date = NULL;
                                    include("database.php");
                                    $stmt = $DBConnect->prepare("UPDATE employments SET job_position = ?, employment_type = ?, employment_start_date = ?, employment_status = ?, employment_end_date = ? WHERE username = ? AND client_id = ?");
                                    $stmt->bind_param("ssssssi", $job_position, $employment_type, $employment_start_date, $employment_status, $employment_end_date, $_COOKIE['username'], $_COOKIE['client_id']);
                                    if ($stmt->execute()) {
                                        $_SESSION['edit_employment_confirmation'] = "<p>Changes have been made successfully.</p>";
                                        header("Location: edit_employment.php", true, 303);
                                        exit(); 
                                    }
                                    else {
                                        $_SESSION['edit_employment_error'] = "<p>Changes have not been made successfully.</p>";
                                        header("Location: edit_employment.php", true, 303);
                                        exit(); 
                                    }
                                }
                                if ($employment_status == "unemployed" && !empty($_POST['employment_end_date'])) {
                                    $employment_end_date = test_input($_POST['employment_end_date']);
                                    if (!validateDate($employment_end_date)) {
                                        $_SESSION['employment_end_date_error'] = "Please enter a valid employment end date";
                                        header("Location: edit_employment.php", true, 303);
                                        exit();
                                    }
                                    else {
                                        include("database.php");
                                        $stmt = $DBConnect->prepare("UPDATE employments SET job_position = ?, employment_type = ?, employment_start_date = ?, employment_status = ?, employment_end_date = ? WHERE username = ? AND client_id = ?");
                                        $stmt->bind_param("ssssssi", $job_position, $employment_type, $employment_start_date, $employment_status, $employment_end_date, $_COOKIE['username'], $_COOKIE['client_id']);
                                        if ($stmt->execute()) {
                                            $_SESSION['edit_employment_confirmation'] = "<p>Changes have been made successfully.</p>";
                                            header("Location: edit_employment.php", true, 303);
                                            exit(); 
                                        }
                                        else {
                                            $_SESSION['edit_employment_error'] = "<p>Changes have not been made successfully.</p>";
                                            header("Location: edit_employment.php", true, 303);
                                            exit(); 
                                        }
                                    }
                                }
                            }
                        }   
                    } 
                }       
            }
            elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
                if (isset($_SESSION['edit_employment_confirmation'])) {echo $_SESSION['edit_employment_confirmation'];}
                if (isset($_SESSION['edit_employment_error'])) {echo $_SESSION['edit_employment_error'];}
            }
            include("database.php");
            $stmt = $DBConnect->prepare("SELECT clients.client_name, vendors.vendor_name, employments.job_position, employments.employment_type, employments.employment_start_date, employments.employment_status, employments.employment_end_date FROM employments INNER JOIN clients ON employments.client_id = clients.client_id LEFT OUTER JOIN vendors ON employments.vendor_id = vendors.vendor_id WHERE employments.username = ? AND employments.client_id = ?");
            $stmt->bind_param("si", $_COOKIE['username'], $_COOKIE['client_id']);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($retrieved_client_name, $retrieved_vendor_name, $retrieved_job_position, $retrieved_employment_type, $retrieved_employment_start_date, $retrieved_employment_status, $retrieved_employment_end_date);
            $stmt->fetch();
            $stmt->close();
            $DBConnect->close();
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric" value="<?php if (isset($_COOKIE["username"])) {echo $_COOKIE["username"];} ?>" readonly required><br><br>
            <label for="client_name">Client:</label>
            <input type="text" name="client_name" id="client_name" placeholder="client" pattern="^[a-zA-Z\s]*$" title="Please ensure that client name has letters and whitespaces only" value="<?php echo $retrieved_client_name; ?>" readonly required><br><br>
            <label for="vendor_name">Vendor:</label>
            <input type="text" name="vendor_name" id="vendor_name" placeholder="vendor" pattern="^[a-zA-Z\s]*$" title="Please ensure that vendor name has letters and whitespaces only" value="<?php echo $retrieved_vendor_name; ?>" readonly required><br><br>
            <label for="job_position">Job Position:</label>
            <input list="job_positions" name="job_position" id="job_position" placeholder="job position" pattern="^[a-zA-Z\s]*$" title="Please ensure that job position has letters and whitespaces only" value="<?php echo $retrieved_job_position; ?>" required><span class="error"> * <?php if (isset($_SESSION['job_position_error'])) {echo $_SESSION['job_position_error'];} ?></span>
                <datalist id="job_positions">
                    <?php
                        include("database.php");
                        $stmt = $DBConnect->prepare("SELECT DISTINCT job_position FROM employments");
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($retrieved_job_position);
                        while($stmt->fetch()) {
                            echo "<option value=\"$retrieved_job_position\"></option>";
                        }
                        $stmt->close();
                        $DBConnect->close();
                    ?>
                </datalist><br><br>
            <label for="employment_type">Employment Type:</label>
            <select id="employment_type" name="employment_type" required>
                <option value="">&nbsp;</option>
                <option value="full-time" <?php if ($retrieved_employment_type == "full-time") {echo "selected";} ?>>Full-time</option>
                <option value="part-time" <?php if ($retrieved_employment_type == "part-time") {echo "selected";} ?>>Part-time</option>
                <option value="contract" <?php if ($retrieved_employment_type == "contract") {echo "selected";} ?>>Contract</option>
                <option value="internship" <?php if ($retrieved_employment_type == "internship") {echo "selected";} ?>>Internship</option>
            </select><span class="error"> * <?php if (isset($_SESSION['employment_type_error'])) {echo $_SESSION['employment_type_error'];} ?></span><br><br>
            <label for="employment_start_date">Employment Start Date:</label>
            <input type="date" id="employment_start_date" name="employment_start_date" required><span class="error"> * <?php if (isset($_SESSION['employment_start_date_error'])) {echo $_SESSION['employment_start_date_error'];} ?></span><br><br>
            <label for="employment_status">Employment Status:</label>
            <select id="employment_status" name="employment_status" onchange="checkDropdown()" required>
                <option value="">&nbsp;</option>
                <option value="employed" <?php if ($retrieved_employment_status == "employed") {echo "selected";} ?>>Employed</option>
                <option value="unemployed" <?php if ($retrieved_employment_status == "unemployed") {echo "selected";} ?>>Unemployed</option>
            </select><span class="error"> * <?php if (isset($_SESSION['employment_status_error'])) {echo $_SESSION['employment_status_error'];} ?></span><br><br>
            <label for="employment_end_date">Employment End Date:</label>
            <input type="date" id="employment_end_date" name="employment_end_date" required><span id="employment_end_date_asterisk" class="error"> <?php if (isset($_SESSION['employment_end_date_error'])) {echo $_SESSION['employment_end_date_error'];} ?></span><br><br>
            <input type="submit" name="edit_employment_submit" value="Submit Changes">
        </form>
        <?php
            if ($_SERVER['REQUEST_METHOD'] === "GET" || !isset($_POST['edit_employment_submit'])) {
                echo
                "<script>
                    document.getElementById(\"employment_start_date\").value = \"".$retrieved_employment_start_date."\"
                    document.getElementById(\"employment_end_date\").value = \"".$retrieved_employment_end_date."\"
                </script>";
            }
        ?>
    </body>
</html>
<?php
    if (isset($_SESSION['job_position_error'])) {unset($_SESSION['job_position_error']);}
    if (isset($_SESSION['employment_type_error'])) {unset($_SESSION['employment_type_error']);}
    if (isset($_SESSION['employment_start_date_error'])) {unset($_SESSION['employment_start_date_error']);}
    if (isset($_SESSION['employment_status_error'])) {unset($_SESSION['employment_status_error']);}
    if (isset($_SESSION['employment_end_date_error'])) {unset($_SESSION['employment_end_date_error']);}
    if (isset($_SESSION['edit_employment_confirmation'])) {unset($_SESSION['edit_employment_confirmation']);}
    if (isset($_SESSION['edit_employment_error'])) {unset($_SESSION['edit_employment_error']);}
?>