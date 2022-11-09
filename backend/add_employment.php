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
            if (time() - $_SESSION['login_time'] < 900) {
                $added_time = time() - $_SESSION['login_time'];
                $_SESSION['login_time'] += $added_time;
            }
        ?>
        <meta charset="UTF-8">
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
        <a href="home.php">Home</a>         
        <?php
            include("logout.php");
        ?>
        <br><br>
        <a href='?logout=true'>Logout</a>        
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
                
                $username = test_input($_POST['username']);
                if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['client_name'])) {
                    $_SESSION['client_name_error'] = "Please ensure that client name has letters and whitespaces only";
                    header("Location: add_employment.php", true, 303);
                    exit();
                }
                else {
                    $client_name = test_input($_POST['client_name']);
                    $_SESSION['client_name'] = $client_name;
                    if (empty($_POST['vendor_name'])) {
                        $vendor_name = NULL;
                        if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['job_position'])) {
                            $_SESSION['job_position_error'] = "Please ensure that job position has letters and whitespaces only";
                            header("Location: add_employment.php", true, 303);
                            exit();
                        }
                        else {
                            $job_position = test_input($_POST['job_position']);
                            if ($_POST['employment_type'] != "full-time" && $_POST['employment_type'] != "part-time" && $_POST['employment_type'] != "contract" && $_POST['employment_type'] != "internship") {
                                $_SESSION['employment_type_error'] = "Please select an appropriate employment type";
                                header("Location: add_employment.php", true, 303);
                                exit();
                            }
                            else {
                                $employment_type = test_input($_POST['employment_type']);
                                $employment_start_date = test_input($_POST['employment_start_date']);
                                if (!validateDate($employment_start_date)) {
                                    $_SESSION['employment_start_date_error'] = "Please enter a valid employment start date";
                                    header("Location: add_employment.php", true, 303);
                                    exit();
                                }
                                else {
                                    if ($_POST['employment_status'] != "employed") {
                                        $_SESSION['employment_status_error'] = "Please ensure that the employment status of the user is employed when adding employment";
                                        header("Location: add_employment.php", true, 303);
                                        exit();
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

                                            $_SESSION['job_position'] = $job_position;
                                            $_SESSION['employment_type'] = $employment_type;
                                            $_SESSION['employment_start_date'] = $employment_start_date;

                                            include("database.php");
                                            $stmt = $DBConnect->prepare("INSERT INTO employments (username, client_id, vendor_id, job_position, employment_type, employment_start_date, employment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                            $stmt->bind_param("siissss", $username, $retrieved_client_id, $vendor_id, $job_position, $employment_type, $employment_start_date, $employment_status); 
                                            if ($stmt->execute()) {
                                                $_SESSION['add_employment_confirmation'] = "<p>Employment was successfully added.</p>";
                                                header("Location: add_employment.php", true, 303);
                                                exit();
                                            }
                                            else {
                                                $_SESSION['add_employment_error'] = "<p>Employment was not successfully added.</p>";
                                                header("Location: add_employment.php", true, 303);
                                                exit();
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

                                                $_SESSION['job_position'] = $job_position;
                                                $_SESSION['employment_type'] = $employment_type;
                                                $_SESSION['employment_start_date'] = $employment_start_date;

                                                $stmt = $DBConnect->prepare("INSERT INTO employments (username, client_id, vendor_id, job_position, employment_type, employment_start_date, employment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                                $stmt->bind_param("siissss", $username, $retrieved_client_id, $vendor_id, $job_position, $employment_type, $employment_start_date, $employment_status); 
                                                if ($stmt->execute()) {
                                                    $_SESSION['add_employment_confirmation'] = "<p>Employment was successfully added.</p>";
                                                    header("Location: add_employment.php", true, 303);
                                                    exit();
                                                }
                                                else {
                                                    $_SESSION['add_employment_error'] = "<p>Employment was not successfully added.</p>";
                                                    header("Location: add_employment.php", true, 303);
                                                    exit();
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
                            $_SESSION['vendor_name_error'] = "Please ensure that vendor name has letters and whitespaces only";
                            header("Location: add_employment.php", true, 303);
                            exit();
                        }
                        else {
                            $vendor_name = test_input($_POST['vendor_name']);
                            $_SESSION['vendor_name'] = $vendor_name;
                            if (!preg_match("/^[a-zA-Z\s]*$/", $_POST['job_position'])) {
                                $_SESSION['job_position_error'] = "Please ensure that job position has letters and whitespaces only";
                                header("Location: add_employment.php", true, 303);
                                exit();
                            }
                            else {
                                $job_position = test_input($_POST['job_position']);
                                if ($_POST['employment_type'] != "full-time" && $_POST['employment_type'] != "part-time" && $_POST['employment_type'] != "contract" && $_POST['employment_type'] != "internship") {
                                    $_SESSION['employment_type_error'] = "Please select an appropriate employment type";
                                    header("Location: add_employment.php", true, 303);
                                    exit();
                                }
                                else {
                                    $employment_type = test_input($_POST['employment_type']);
                                    $employment_start_date = test_input($_POST['employment_start_date']);
                                    if (!validateDate($employment_start_date)) {
                                        $_SESSION['employment_start_date_error'] = "Please enter a valid employment start date";
                                        header("Location: add_employment.php", true, 303);
                                        exit();
                                    }
                                    else {
                                        if ($_POST['employment_status'] != "employed") {
                                            $_SESSION['employment_status_error'] = "Please ensure that the employment status of the user is employed when adding employment";
                                            header("Location: add_employment.php", true, 303);
                                            exit();
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

                                                    $_SESSION['job_position'] = $job_position;
                                                    $_SESSION['employment_type'] = $employment_type;
                                                    $_SESSION['employment_start_date'] = $employment_start_date;

                                                    include("database.php");
                                                    $stmt = $DBConnect->prepare("INSERT INTO employments (username, client_id, vendor_id, job_position, employment_type, employment_start_date, employment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                                    $stmt->bind_param("siissss", $username, $retrieved_client_id, $retrieved_vendor_id, $job_position, $employment_type, $employment_start_date, $employment_status); 
                                                    if ($stmt->execute()) {
                                                        $_SESSION['add_employment_confirmation'] = "<p>Employment was successfully added.</p>";
                                                        header("Location: add_employment.php", true, 303);
                                                        exit();
                                                    }
                                                    else {
                                                        $_SESSION['add_employment_error'] = "<p>Employment was not successfully added.</p>";
                                                        header("Location: add_employment.php", true, 303);
                                                        exit();
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

                                                        $_SESSION['job_position'] = $job_position;
                                                        $_SESSION['employment_type'] = $employment_type;
                                                        $_SESSION['employment_start_date'] = $employment_start_date;

                                                        $stmt = $DBConnect->prepare("INSERT INTO employments (username, client_id, vendor_id, job_position, employment_type, employment_start_date, employment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                                        $stmt->bind_param("siissss", $username, $retrieved_client_id, $retrieved_vendor_id, $job_position, $employment_type, $employment_start_date, $employment_status); 
                                                        if ($stmt->execute()) {
                                                            $_SESSION['add_employment_confirmation'] = "<p>Employment was successfully added.</p>";
                                                            header("Location: add_employment.php", true, 303);
                                                            exit();
                                                        }
                                                        else {
                                                            $_SESSION['add_employment_error'] = "<p>Employment was not successfully added.</p>";
                                                            header("Location: add_employment.php", true, 303);
                                                            exit();
                                                        }
                                                    }
                                                    else {
                                                        $_SESSION['add_employment_error'] = "<p>Employment was not successfully added.</p>";
                                                        header("Location: add_employment.php", true, 303);
                                                        exit();
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

                                                        $_SESSION['job_position'] = $job_position;
                                                        $_SESSION['employment_type'] = $employment_type;
                                                        $_SESSION['employment_start_date'] = $employment_start_date;

                                                        include("database.php");
                                                        $stmt = $DBConnect->prepare("INSERT INTO employments (username, client_id, vendor_id, job_position, employment_type, employment_start_date, employment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                                        $stmt->bind_param("siissss", $username, $retrieved_client_id, $retrieved_vendor_id, $job_position, $employment_type, $employment_start_date, $employment_status); 
                                                        if ($stmt->execute()) {
                                                            $_SESSION['add_employment_confirmation'] = "<p>Employment was successfully added.</p>";
                                                            header("Location: add_employment.php", true, 303);
                                                            exit();
                                                        }
                                                        else {
                                                            $_SESSION['add_employment_error'] = "<p>Employment was not successfully added.</p>";
                                                            header("Location: add_employment.php", true, 303);
                                                            exit();
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

                                                            $_SESSION['job_position'] = $job_position;
                                                            $_SESSION['employment_type'] = $employment_type;
                                                            $_SESSION['employment_start_date'] = $employment_start_date;

                                                            $stmt = $DBConnect->prepare("INSERT INTO employments (username, client_id, vendor_id, job_position, employment_type, employment_start_date, employment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                                            $stmt->bind_param("siisssss", $username, $retrieved_client_id, $retrieved_vendor_id, $job_position, $employment_type, $employment_start_date, $employment_status); 
                                                            if ($stmt->execute()) {
                                                                $_SESSION['add_employment_confirmation'] = "<p>Employment was successfully added.</p>";
                                                                header("Location: add_employment.php", true, 303);
                                                                exit();
                                                            }
                                                            else {
                                                                $_SESSION['add_employment_error'] = "<p>Employment was not successfully added.</p>";
                                                                header("Location: add_employment.php", true, 303);
                                                                exit();
                                                            }
                                                        }
                                                        else {
                                                            $_SESSION['add_employment_error'] = "<p>Employment was not successfully added.</p>";
                                                            header("Location: add_employment.php", true, 303);
                                                            exit();
                                                        }
                                                    }
                                                }
                                                else {
                                                    $_SESSION['add_employment_error'] = "<p>Employment was not successfully added.</p>";
                                                    header("Location: add_employment.php", true, 303);
                                                    exit();
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
            elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
                if (isset($_SESSION['add_employment_confirmation'])) {echo $_SESSION['add_employment_confirmation'];}
                if (isset($_SESSION['add_employment_error'])) {echo $_SESSION['add_employment_error'];}
            }
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <h2>Add Employment</h2>
            <p>Please fill the form below to add an employment for the selected user:</p>
            <p><span class="error">* required field</span></p>
                <label for="username"><b>Username:</b></label>
                <input type="text" id="username" name="username" placeholder="username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric" value="<?php if (isset($_COOKIE["username"])) {echo $_COOKIE["username"];} ?>" readonly required><br><br>
                <label for="client_name"><b>Client:</b></label>
                <input list="clients" name="client_name" id="client_name" placeholder="client" pattern="^[a-zA-Z\s]*$" title="Please ensure that client name has letters and whitespaces only" value="<?php if (isset($_SESSION['client_name'])) {echo $_SESSION['client_name'];} ?>" required><span class="error"> * <?php if (isset($_SESSION['client_name_error'])) {echo $_SESSION['client_name_error'];} ?></span>
                    <datalist id="clients">
                        <?php
                            include("database.php");
                            $stmt = $DBConnect->prepare("SELECT DISTINCT client_name FROM clients");
                            $stmt->execute();
                            $stmt->store_result();
                            $stmt->bind_result($retrieved_client_name);
                            while($stmt->fetch()) {
                                echo "<option value=\"$retrieved_client_name\"></option>";
                            }
                        ?>
                    </datalist><br><br>
                <label for="vendor_name"><b>Vendor:</b></label>
                <input list="vendors" name="vendor_name" id="vendor_name" placeholder="vendor" pattern="^[a-zA-Z\s]*$" title="Please ensure that vendor name has letters and whitespaces only" value="<?php if (isset($_SESSION['vendor_name'])) {echo $_SESSION['vendor_name'];} ?>"><span class="error"> <?php if (isset($_SESSION['vendor_name_error'])) {echo $_SESSION['vendor_name_error'];} ?></span>
                    <datalist id="vendors">
                        <?php
                            include("database.php");
                            $stmt = $DBConnect->prepare("SELECT DISTINCT vendor_name FROM vendors");
                            $stmt->execute();
                            $stmt->store_result();
                            $stmt->bind_result($retrieved_vendor_name);
                            while($stmt->fetch()) {
                                echo "<option value=\"$retrieved_vendor_name\"></option>";
                            }
                        ?>
                    </datalist><br><br>
                <label for="job_position"><b>Job Position:</b></label>
                <input list="job_positions" name="job_position" id="job_position" placeholder="job position" pattern="^[a-zA-Z\s]*$" title="Please ensure that job position has letters and whitespaces only" value="<?php if (isset($_SESSION['job_position'])) {echo $_SESSION['job_position'];} ?>" required><span class="error"> * <?php if (isset($_SESSION['job_position_error'])) {echo $_SESSION['job_position_error'];} ?></span>
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
                <label for="employment_type"><b>Employment Type:</b></label>
                <select id="employment_type" name="employment_type" required>
                    <option value="" <?php if (!isset($_POST['add_employment_submit'])) {echo "selected";} ?> disabled>Select Employment Type</option>
                    <option value="full-time" <?php if (isset($_SESSION['employment_type']) && $_SESSION['employment_type'] == "full-time") {echo "selected";} ?>>Full-time</option>
                    <option value="part-time" <?php if (isset($_SESSION['employment_type']) && $_SESSION['employment_type'] == "part-time") {echo "selected";} ?>>Part-time</option>
                    <option value="contract" <?php if (isset($_SESSION['employment_type']) && $_SESSION['employment_type'] == "contract") {echo "selected";} ?>>Contract</option>
                    <option value="internship" <?php if (isset($_SESSION['employment_type']) && $_SESSION['employment_type'] == "internship") {echo "selected";} ?>>Internship</option>
                </select><span class="error"> * <?php if (isset($_SESSION['employment_type_error'])) {echo $_SESSION['employment_type_error'];} ?></span><br><br>
                <label for="employment_start_date"><b>Employment Start Date:</b></label>
                <input type="date" id="employment_start_date" name="employment_start_date" required><span class="error"> * <?php if (isset($_SESSION['employment_start_date_error'])) {echo $_SESSION['employment_start_date_error'];} ?></span><br><br>
                <input type="hidden" id="employment_status" name="employment_status" value="employed"><span class="error"> <?php if (isset($_SESSION['employment_status_error'])) {echo $_SESSION['employment_status_error'];} ?></span>
                <input type="submit" name="add_employment_submit" value="Add Employment">
        </form>
        <?php
            if ($_SERVER['REQUEST_METHOD'] === "GET") {
                echo
                "<script>
                    document.getElementById(\"employment_start_date\").value = \"".$_SESSION['employment_start_date']."\";
                </script>";
            }
        ?>
    </body>
</html>
<?php
    if (isset($_SESSION['client_name'])) {unset($_SESSION['client_name']);}
    if (isset($_SESSION['vendor_name'])) {unset($_SESSION['vendor_name']);}
    if (isset($_SESSION['job_position'])) {unset($_SESSION['job_position']);}
    if (isset($_SESSION['employment_type'])) {unset($_SESSION['employment_type']);}
    if (isset($_SESSION['employment_start_date'])) {unset($_SESSION['employment_start_date']);}
    if (isset($_SESSION['client_name_error'])) {unset($_SESSION['client_name_error']);}
    if (isset($_SESSION['vendor_name_error'])) {unset($_SESSION['vendor_name_error']);}
    if (isset($_SESSION['job_position_error'])) {unset($_SESSION['job_position_error']);}
    if (isset($_SESSION['employment_type_error'])) {unset($_SESSION['employment_type_error']);}
    if (isset($_SESSION['employment_start_date_error'])) {unset($_SESSION['employment_start_date_error']);}
    if (isset($_SESSION['employment_status_error'])) {unset($_SESSION['employment_status_error']);}
    if (isset($_SESSION['add_employment_confirmation'])) {unset($_SESSION['add_employment_confirmation']);}
    if (isset($_SESSION['add_employment_error'])) {unset($_SESSION['add_employment_error']);}
?>