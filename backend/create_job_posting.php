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
    if ($_SESSION['user_role'] != "administrator" && $_SESSION['user_role'] != "recruiter") {
        header('Location: home.php');
        exit();
    }
    setcookie("search_user", "", time() - 3600);
    setcookie("search_job_posting", "", time() - 3600);
    setcookie("choose_timesheet", "", time() - 3600);
    setcookie("choose_employment", "", time() - 3600);
    setcookie("client_id", "", time() - 3600);
    unset($_SESSION['disable_choose_timesheet']);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            include("session_timeout.php");
        ?>
        <meta charset="UTF-8">
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
        <a href="home.php">Home</a><br><br>
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>Logout</a>
        <h2>Create Job Posting</h2>
        <p>Please fill the form below to create and post a job:</p>
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

                function validateVendorName($provided_vendor_name) {
                    $provided_vendor_name = testInput($provided_vendor_name);
                    if (!preg_match("/^[a-zA-Z\s]*$/", $provided_vendor_name)) {
                        $_SESSION['vendor_name_error'] = "<p class=\"error\">Please ensure that vendor name has letters and whitespaces only</p>";
                        header("Location: create_job_posting.php", true, 303);
                        exit();
                    }
                    else {
                        $_SESSION['vendor_name'] = $provided_vendor_name;
                        return $provided_vendor_name;
                    }
                }

                function validateVendorRate($provided_vendor_rate) {
                    $provided_vendor_rate = testInput($provided_vendor_rate);
                    if (!is_numeric($provided_vendor_rate)) {
                        $_SESSION['vendor_rate_error'] = "<p class=\"error\">Please ensure that vendor rate is numeric</p>";
                        header("Location: create_job_posting.php", true, 303);
                        exit();
                    }
                    else {
                        $_SESSION['vendor_rate'] = $provided_vendor_rate;
                        return $provided_vendor_rate;
                    }
                }

                function validateClientName($provided_client_name) {
                    $provided_client_name = testInput($provided_client_name);
                    if (!preg_match("/^[a-zA-Z\s]*$/", $provided_client_name)) {
                        $_SESSION['client_name_error'] = "<p class=\"error\">Please ensure that client name has letters and whitespaces only</p>";
                        header("Location: create_job_posting.php", true, 303);
                        exit();
                    }
                    else {
                        $_SESSION['client_name'] = $provided_client_name;
                        return $provided_client_name;
                    }
                }

                function validateJobTitle($provided_job_title) {
                    $provided_job_title = testInput($provided_job_title);
                    if (!preg_match("/^[a-zA-Z\s]*$/", $provided_job_title)) {
                        $_SESSION['job_title_error'] = "<p class=\"error\">Please ensure that job title has letters and whitespaces only</p>";
                        header("Location: create_job_posting.php", true, 303);
                        exit();
                    }
                    else {
                        $_SESSION['job_title'] = $provided_job_title;
                        return $provided_job_title;
                    }
                }

                function validateJobType($provided_job_type) {
                    $provided_job_type = testInput($provided_job_type);
                    if ($provided_job_type != "full-time" && $provided_job_type != "part-time" && $provided_job_type != "contract" && $provided_job_type != "internship") {
                        $_SESSION['job_type_error'] = "<p class=\"error\">Please select an appropriate job type</p>";
                        header("Location: create_job_posting.php", true, 303);
                        exit();
                    }
                    else {
                        $_SESSION['job_type'] = $provided_job_type;
                        return $provided_job_type;
                    }
                }

                function validateJobLocation($provided_job_location) {
                    $provided_job_location = testInput($provided_job_location);
                    if (!preg_match("/^[a-zA-Z,;\s]*$/", $provided_job_location)) {
                        $_SESSION['job_location_error'] = "<p class=\"error\">Please ensure that job location has letters, commas, semicolons, and whitespaces only</p>";
                        header("Location: create_job_posting.php", true, 303);
                        exit();
                    }
                    else {
                        $_SESSION['job_location'] = $provided_job_location;
                        return $provided_job_location;
                    }
                }

                function validateJobDescription($provided_job_description) {
                    $provided_job_description = testInput($provided_job_description);
                    $_SESSION['job_description'] = $provided_job_description;
                    return $provided_job_description;
                }

                function validatePreferredSkills($provided_preferred_skills) {
                    $provided_preferred_skills = testInput($provided_preferred_skills);
                    if (!preg_match("/^[a-zA-Z,\s]*$/", $provided_preferred_skills)) {
                        $_SESSION['preferred_skills_error'] = "<p class=\"error\">Please ensure that preferred skills have letters, commas and whitespaces only</p>";
                        header("Location: create_job_posting.php", true, 303);
                        exit();
                    }
                    else {
                        $_SESSION['preferred_skills'] = $provided_preferred_skills;
                        return $provided_preferred_skills;
                    }
                }

                function validateRequiredSkills($provided_required_skills) {
                    $provided_required_skills = testInput($provided_required_skills);
                    if (!preg_match("/^[a-zA-Z,\s]*$/", $provided_required_skills)) {
                        $_SESSION['required_skills_error'] = "<p class=\"error\">Please ensure that required skills have letters, commas and whitespaces only</p>";
                        header("Location: create_job_posting.php", true, 303);
                        exit();
                    }
                    else {
                        $_SESSION['required_skills'] = $provided_required_skills;
                        return $provided_required_skills;
                    }
                }

                function checkClientId($provided_client_name) {
                    include("database.php");
                    $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                    $stmt->bind_param("s", $provided_client_name); 
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($retrieved_client_id);

                    if ($stmt->num_rows == 0) {
                        include("database.php");
                        $stmt = $DBConnect->prepare("INSERT INTO clients (client_name) VALUES (?)");
                        $stmt->bind_param("s", $provided_client_name);
                        $stmt->execute();

                        $stmt = $DBConnect->prepare("SELECT client_id FROM clients WHERE client_name = ?");
                        $stmt->bind_param("s", $provided_client_name); 
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($retrieved_client_id);
                        $stmt->fetch();

                        $result = $retrieved_client_id;
                    }
                    elseif ($stmt->num_rows > 0) {
                        $stmt->fetch();
                        $result = $retrieved_client_id;
                    }
                    
                    $stmt->close();
                    $DBConnect->close();

                    return $result;
                }

                function checkVendorId($provided_vendor_name) {
                    include("database.php");
                    $stmt = $DBConnect->prepare("SELECT vendor_id FROM vendors WHERE vendor_name = ?");
                    $stmt->bind_param("s", $provided_vendor_name); 
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($retrieved_vendor_id);

                    if ($stmt->num_rows == 0) {
                        include("database.php");
                        $stmt = $DBConnect->prepare("INSERT INTO vendors (vendor_name) VALUES (?)");
                        $stmt->bind_param("s", $provided_vendor_name);
                        $stmt->execute();

                        $stmt = $DBConnect->prepare("SELECT vendor_id FROM vendors WHERE vendor_name = ?");
                        $stmt->bind_param("s", $provided_vendor_name); 
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($retrieved_vendor_id);
                        $stmt->fetch();

                        $result = $retrieved_vendor_id;
                    }
                    elseif ($stmt->num_rows > 0) {
                        $stmt->fetch();
                        $result = $retrieved_vendor_id;
                    }
                    
                    $stmt->close();
                    $DBConnect->close();

                    return $result;
                }

                if (!empty($_POST['vendor_name'])) {
                    $vendor_name = validateVendorName($_POST['vendor_name']);
                    $vendor_id = checkVendorId($vendor_name);

                    $vendor_rate = validateVendorRate($_POST['vendor_rate']);
                }
                else {
                    $vendor_id = NULL;
                    $vendor_rate = NULL;
                }

                if (!isset($_POST['job_location'])) {
                    $job_location = validateJobLocation($_POST['job_location_alternative']);
                }
                else {
                    if (isset($_POST['job_location_alternative'])) {
                        $job_location_concatenation = $_POST['job_location'].";".$_POST['job_location_alternative'];
                        $job_location = validateJobLocation($job_location_concatenation);
                    }
                    else {
                        $job_location = validateJobLocation($_POST['job_location']);
                    }
                }

                $client_name = validateClientName($_POST['client_name']);
                $client_id = checkClientId($client_name);
                $job_title = validateJobTitle($_POST['job_title']);
                $job_type = validateJobType($_POST['job_type']);
                $job_description = validateJobDescription($_POST['job_description']);
                $preferred_skills = validatePreferredSkills($_POST['preferred_skills']);
                $required_skills = validateRequiredSkills($_POST['required_skills']);
                $job_posted_date = date("Y-m-d");
                $job_status = "active";
                
                include("database.php");
                $stmt = $DBConnect->prepare("INSERT INTO jobs (vendor_id, vendor_rate, client_id, job_title, job_type, job_location, job_description, preferred_skills, required_skills, job_posted_date, job_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("idissssssss", $vendor_id, $vendor_rate, $client_id, $job_title, $job_type, $job_location, $job_description, $preferred_skills, $required_skills, $job_posted_date, $job_status);
                if ($stmt->execute()) {
                    $_SESSION["create_job_posting_confirmation"] = "<p>Job was successfully created and posted.</p>";
                    header("Location: create_job_posting.php", true, 303);
                    exit();
                }
                else {
                    $_SESSION["create_job_posting_error"] = "<p class=\"error\">Job was not successfully created and posted.</p>";
                    header("Location: create_job_posting.php", true, 303);
                    exit();
                }
                $stmt->close();
                $DBConnect->close();
            }
            elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
                if (isset($_SESSION["create_job_posting_confirmation"])) {echo $_SESSION["create_job_posting_confirmation"];}
                if (isset($_SESSION["create_job_posting_error"])) {echo $_SESSION["create_job_posting_error"];}
            }
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="job_id">Job Id:</label>
            <input type="text" id="job_id" name="job_id" value="new" readonly><br><br>
            <label for="vendor_name">Vendor:</label>
            <input list="vendors" name="vendor_name" id="vendor_name" placeholder="vendor" pattern="^[a-zA-Z\s]*$" title="Please ensure that vendor name has letters and whitespaces only" value="<?php if (isset($_SESSION['vendor_name'])) {echo $_SESSION['vendor_name'];} ?>" onblur="checkInput()"><span class="error"> <?php if (isset($_SESSION['vendor_name_error'])) {echo $_SESSION['vendor_name_error'];} ?></span>
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
            <label for="vendor_rate">Vendor Rate:</label>
            <input type="number" id="vendor_rate" name="vendor_rate" placeholder="000.00" min="0" max="999" step="0.01" value="<?php if (isset($_SESSION['vendor_rate'])) {echo $_SESSION['vendor_rate'];} ?>" required disabled><span id="vendor_rate_asterisk" class="error"> <?php if (isset($_SESSION['vendor_rate_error'])) {echo $_SESSION['vendor_rate_error'];} ?></span><br><br>  
            <label for="client_name">Client:</label>
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
            <label for="job_title">Job Title:</label>
            <input list="job_titles" name="job_title" id="job_title" placeholder="job title" pattern="^[a-zA-Z\s]*$" title="Please ensure that job title has letters and whitespaces only" value="<?php if (isset($_SESSION['job_title'])) {echo $_SESSION['job_title'];} ?>" required><span class="error"> * <?php if (isset($_SESSION['job_title_error'])) {echo $_SESSION['job_title_error'];} ?></span>
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
                    $stmt->close();
                    $DBConnect->close();
                ?>
            </datalist><br><br>
            <label for="job_type">Job Type:</label>
            <select id="job_type" name="job_type" required>
                <option value="" <?php if (!isset($_POST['create_job_submit'])) {echo "selected";} ?> disabled>Select Job Type</option>
                <option value="full-time" <?php if (isset($_SESSION['job_type']) && $_SESSION['job_type'] == "full-time") {echo "selected";} ?>>Full-time</option>
                <option value="part-time" <?php if (isset($_SESSION['job_type']) && $_SESSION['job_type'] == "part-time") {echo "selected";} ?>>Part-time</option>
                <option value="contract" <?php if (isset($_SESSION['job_type']) && $_SESSION['job_type'] == "contract") {echo "selected";} ?>>Contract</option>
                <option value="internship" <?php if (isset($_SESSION['job_type']) && $_SESSION['job_type'] == "internship") {echo "selected";} ?>>Internship</option>
            </select><span class="error"> * <?php if (isset($_SESSION['job_type_error'])) {echo $_SESSION['job_type_error'];} ?></span><br><br>
            <?php $job_location_array = explode(";", $_SESSION['job_location']); ?>
            <label for="job_location">Job Location:</label>
            <input list="usa_cities_and_states" name="job_location" id="job_location" placeholder="job location" pattern="^[a-zA-Z,\s]*$" title="Please ensure that job location has letters, commas and whitespaces only" value="<?php if ($job_location_array[0] != "Remote") {echo $job_location_array[0];} ?>" required>&nbsp;&nbsp;<input type="checkbox" name="job_location_alternative" id="job_location_remote" value="Remote" onclick="preventTwoChecks(this)" <?php if ($job_location_array[0] == "Remote") {echo "checked";} ?>>Remote&nbsp;&nbsp;<input type="checkbox" name="job_location_alternative" id="job_location_hybrid" value="Hybrid" onclick="preventOneCheck(this)" <?php if ($job_location_array[1] == "Hybrid") {echo "checked";} ?>>Hybrid
            <datalist id="usa_cities_and_states">
                <?php
                    include("usa_cities_and_states.php");
                ?>
            </datalist><span class="error"> * <?php if (isset($_SESSION['job_location_error'])) {echo $_SESSION['job_location_error'];} ?></span><br><br>
            <p><label for="job_description">Job Description:</label></p>
            <textarea id="job_description" name="job_description" placeholder="job description" rows="30" cols="50" required><?php if (isset($_SESSION['job_description'])) {echo $_SESSION['job_description'];} ?></textarea><span class="error"> *</span><br><br>
            <label for="preferred_skills">Preferred Skills:</label>
            <input type="text" id="preferred_skills" name="preferred_skills" placeholder="preferred skills" pattern="^[a-zA-Z,\s]*$" title="Please ensure that preferred skills have letters, commas and whitespaces only" value="<?php if (isset($_SESSION['preferred_skills'])) {echo $_SESSION['preferred_skills'];} ?>" required><span class="error"> * <?php if (isset($_SESSION['preferred_skills_error'])) {echo $_SESSION['preferred_skills_error'];} ?></span><br><br>
            <label for="job_type">Required Skills:</label>
            <input type="text" id="required_skills" name="required_skills" placeholder="required skills" pattern="^[a-zA-Z,\s]*$" title="Please ensure that required skills have letters, commas and whitespaces only" value="<?php if (isset($_SESSION['required_skills'])) {echo $_SESSION['required_skills'];} ?>" required><span class="error"> * <?php if (isset($_SESSION['required_skills_error'])) {echo $_SESSION['required_skills_error'];} ?></span><br><br>
            <input type="submit" name="create_job_submit" value="Create & Post Job">
        </form>
    </body>
</html>
<?php
    if (isset($_SESSION['vendor_name_error'])) {unset($_SESSION['vendor_name_error']);}
    if (isset($_SESSION['vendor_name'])) {unset($_SESSION['vendor_name']);}
    if (isset($_SESSION['vendor_rate_error'])) {unset($_SESSION['vendor_rate_error']);}
    if (isset($_SESSION['vendor_rate'])) {unset($_SESSION['vendor_rate']);}
    if (isset($_SESSION['client_name_error'])) {unset($_SESSION['client_name_error']);}
    if (isset($_SESSION['client_name'])) {unset($_SESSION['client_name']);}
    if (isset($_SESSION['job_title_error'])) {unset($_SESSION['job_title_error']);}
    if (isset($_SESSION['job_title'])) {unset($_SESSION['job_title']);}
    if (isset($_SESSION['job_type_error'])) {unset($_SESSION['job_type_error']);}
    if (isset($_SESSION['job_type'])) {unset($_SESSION['job_type']);}
    if (isset($_SESSION['job_location'])) {unset($_SESSION['job_location']);}
    if (isset($_SESSION['job_location_error'])) {unset($_SESSION['job_location_error']);}
    if (isset($_SESSION['job_description'])) {unset($_SESSION['job_description']);}
    if (isset($_SESSION['preferred_skills_error'])) {unset($_SESSION['preferred_skills_error']);}
    if (isset($_SESSION['preferred_skills'])) {unset($_SESSION['preferred_skills']);}
    if (isset($_SESSION['required_skills_error'])) {unset($_SESSION['required_skills_error']);}
    if (isset($_SESSION['required_skills'])) {unset($_SESSION['required_skills']);}
    if (isset($_SESSION['create_job_posting_confirmation'])) {unset($_SESSION['create_job_posting_confirmation']);}
    if (isset($_SESSION['create_job_posting_error'])) {unset($_SESSION['create_job_posting_error']);}
?>
