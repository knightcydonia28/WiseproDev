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
    if (!isset($_COOKIE['choose_employment'])) {
        header('Location: home.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            include("session_timeout.php");
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
                
                function testInput($data) {
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                }

                function validateDate($date, $format = 'Y-m-d') {
                    $d = DateTime::createFromFormat($format, $date);
                    return $d && $d->format($format) == $date;
                }
                
                function validateJobPosition($provided_job_position) {
                    $provided_job_position = testInput($provided_job_position);
                    if (!preg_match("/^[a-zA-Z\s]*$/", $provided_job_position)) {
                        $_SESSION['job_position_error'] = "<p class=\"error\">Please ensure that job position has letters and whitespaces only</p>";
                        header("Location: edit_employment.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_job_position;
                    }
                }

                function validateEmploymentType($provided_employment_type) {
                    $provided_employment_type = testInput($provided_employment_type);
                    if ($provided_employment_type != "full-time" && $provided_employment_type != "part-time" && $provided_employment_type != "contract" && $provided_employment_type != "internship") {
                        $_SESSION['employment_type_error'] = "<p class=\"error\">Please select an appropriate employment type</p>";
                        header("Location: edit_employment.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_employment_type;
                    }
                }

                function validateEmploymentStartDate($provided_employment_start_date) {
                    $provided_employment_start_date = testInput($provided_employment_start_date);
                    if (!validateDate($provided_employment_start_date)) {
                        $_SESSION['employment_start_date_error'] = "<p class=\"error\">Please enter a valid employment start date</p>";
                        header("Location: edit_employment.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_employment_start_date;
                    }
                }

                function validateEmploymentStatus($provided_employment_status) {
                    $provided_employment_status = testInput($provided_employment_status);
                    if ($provided_employment_status != "employed" && $provided_employment_status != "unemployed") {
                        $_SESSION['employment_status_error'] = "<p class=\"error\">Please ensure that the employment status of the user is employed when adding employment</p>";
                        header("Location: add_employment_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_employment_status;
                    }
                }

                function validateEmploymentEndDate($provided_employment_end_date) {
                    $provided_employment_end_date = testInput($provided_employment_end_date);
                    if (!validateDate($provided_employment_end_date)) {
                        $_SESSION['employment_end_date_error'] = "<p class=\"error\">Please enter a valid employment end date</p>";
                        header("Location: edit_employment.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_employment_end_date;
                    }
                }

                if (!empty($_POST['employment_end_date'])) {
                    $employment_end_date = validateEmploymentEndDate($_POST['employment_end_date']);
                }
                else {
                    $employment_end_date = NULL;
                }

                $username = $_COOKIE['username'];
                $client_id = $_COOKIE['client_id'];
                $job_position = validateJobPosition($_POST['job_position']);
                $employment_type = validateEmploymentType($_POST['employment_type']);
                $employment_start_date = validateEmploymentStartDate($_POST['employment_start_date']);          
                $employment_status = validateEmploymentStatus($_POST['employment_status']);

                include("database.php");
                $stmt = $DBConnect->prepare("UPDATE employments SET job_position = ?, employment_type = ?, employment_start_date = ?, employment_status = ?, employment_end_date = ? WHERE username = ? AND client_id = ?");
                $stmt->bind_param("ssssssi", $job_position, $employment_type, $employment_start_date, $employment_status, $employment_end_date, $username, $client_id);
                if ($stmt->execute()) {
                    $_SESSION['edit_employment_confirmation'] = "<p>Changes have been made successfully.</p>";
                    header("Location: edit_employment.php", true, 303);
                    exit(); 
                }
                else {
                    $_SESSION['edit_employment_error'] = "<p class=\"error\">Changes have not been made successfully.</p>";
                    header("Location: edit_employment.php", true, 303);
                    exit(); 
                }
                $stmt->close();
                $DBConnect->close();
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
            <input type="text" id="username" name="username" placeholder="username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric" value="<?php if (isset($_COOKIE["username"])) {echo $_COOKIE["username"];} ?>" readonly required><span class="error"> * </span><br><br>
            <label for="client_name">Client:</label>
            <input type="text" name="client_name" id="client_name" placeholder="client" pattern="^[a-zA-Z\s]*$" title="Please ensure that client name has letters and whitespaces only" value="<?php echo $retrieved_client_name; ?>" readonly required><span class="error"> * </span><br><br>
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
