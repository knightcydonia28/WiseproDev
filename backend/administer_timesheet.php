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
    $GLOBALS['username'] = isset($_COOKIE['home']) ? $_SESSION['username'] : $_COOKIE['username'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <a href="home.php">Home</a><br /><br />
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>Logout</a>
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
        <title>Administer Timesheet</title>
        <style>
            table, th, td {
                border: 1px solid black;
            }
            .timesheet_information {
                border: 1px solid black;
                padding: 5px;
                width: 821px;
            }
            #hidden_div {
                display: none;
            }
        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script>
            function findTotal() {
                var array = document.getElementsByClassName('hours');
                var total = 0;
                for(var i=0; i<array.length; i++){
                    if(parseFloat(array[i].value))
                        total += parseFloat(array[i].value);
                }
                document.getElementById('sum').value = total;
            }
            function displayTextArea() {
                let selected_option = document.getElementById("selected_decision").value;
                let label = document.getElementById("reject_reason_label");
                let textarea = document.getElementById("reject_reason");
                if (selected_option == 3) {
                    label.hidden = false;
                    textarea.hidden = false;
                    textarea.required = true;
                }
                else {
                    label.hidden = true;
                    textarea.hidden = true;
                    textarea.required = false;
                }
            }
        </script>
    </head>
    <body>
        <?php
            if (isset($_POST['administer_timesheet_submit'])) {
                $hours_array = $_POST['hours'];
                $day_types_array = $_POST['day_types']; 
                $notes_array = $_POST['notes'];
                $date_count = 1;
                $day_type_count = 0;
                $notes_count = 0;

                foreach ($hours_array as $hours) {
                    $date = date_create($_SESSION['year']."/".$_SESSION['month']."/".$date_count);
                    $formatted_date = date_format($date,"Y-m-d");
                    $time = strtotime($formatted_date);
                    $first_day_of_week = date('Y-m-d', strtotime('Last Monday', $time));
                    $last_day_of_week = date('Y-m-d', strtotime('Next Sunday', $time));

                    include("database.php");
                    $stmt = $DBConnect->prepare("UPDATE timesheets SET hours=?, day_type=?, notes=? WHERE work_date=? AND username =? AND client_id=?");
                    $stmt->bind_param("ssssss", $hours, $day_types_array[$day_type_count], $notes_array[$notes_count], $formatted_date, $GLOBALS['username'], $_COOKIE['client_id']);
                    $stmt->execute();
                    $date_count++;
                    $day_type_count++;
                    $notes_count++;
                }

                $selected_decision = $_POST['selected_decision'];
                $reject_reason = $_POST['reject_reason'];

                if($selected_decision == 3) { 
                    // Rejected timesheet
                    echo "<p>Timesheet has been rejected.</p>";
                    include("database.php");
                    $stmt = $DBConnect->prepare("UPDATE timesheets SET timesheet_status = 3, reject_reason = ? WHERE username = ? AND client_id = ? AND MONTH(work_date) = ? AND YEAR(work_date) = ?");
                    $stmt->bind_param("ssiss", $reject_reason, $GLOBALS['username'], $_COOKIE['client_id'], $_SESSION['month'], $_SESSION['year']);
                    $stmt->execute();
                }
                elseif($selected_decision == 2) {
                    // approved timesheet
                    echo "<p>Timesheet has been approved.</p>";
                    include("database.php");
                    $stmt = $DBConnect->prepare("UPDATE timesheets SET timesheet_status = 2, reject_reason = NULL WHERE username = ? AND client_id = ? AND MONTH(work_date) = ? AND YEAR(work_date) = ?");
                    $stmt->bind_param("siss", $GLOBALS['username'], $_COOKIE['client_id'], $_SESSION['month'], $_SESSION['year']);
                    $stmt->execute();
                }
                $stmt->close();
                $DBConnect->close();
            }
        ?>
        <h2>Administer Timesheet</h2>
        <p>Below is the selected timesheet to administer:</p>
        <?php
            include("database.php");
            $stmt = $DBConnect->prepare("SELECT users.user_first_name, users.user_middle_name, users.user_last_name, clients.client_name FROM ((users INNER JOIN employments ON users.username = employments.username) INNER JOIN clients ON clients.client_id = employments.client_id) WHERE employments.username = ? AND clients.client_id = ?");
            $stmt->bind_param("si", $GLOBALS['username'], $_COOKIE['client_id']);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($retrieved_user_first_name, $retrieved_user_middle_name, $retrieved_user_last_name, $retrieved_client_name);
            $stmt->fetch();
            $stmt->close();
            $DBConnect->close();
            echo 
            "<div class=\"timesheet_information\">
                <p><b>Username:</b> ".$GLOBALS['username']."</p>
                <p><b>Timesheet Period:</b> ".$_SESSION['month']."/".$_SESSION['year']."</p>
                <p><b>Full Name:</b> ".ucfirst(strtolower($retrieved_user_first_name))," ", ucfirst(strtolower($retrieved_user_middle_name))," ", ucfirst(strtolower($retrieved_user_last_name))."</p>
                <p><b>Client:</b> $retrieved_client_name</p>"; 
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <select id="selected_decision" name="selected_decision" onchange="displayTextArea()" required>
                <option value="" selected disabled>Select Decision</option>
                <option value=2>Approve</option>
                <option value=3>Reject</option>
            </select>
            <br>
            <p><label id="reject_reason_label" for="reject_reason" hidden>Rejection Reason (Required):</label></p>
            <textarea id="reject_reason" name="reject_reason" rows="3" cols="10" hidden></textarea>
            </div>
            <br>
            <div class='timesheet_table'>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Reporting Code</th>
                        <th>Hours</th>
                        <th>Notes</th>
                    </tr>
                    <?php
                        $year = $_SESSION['year'];
                        $month = $_SESSION['month'];
                        $number_of_days_in_month = date("t", mktime(0,0,0,$month,1,$year));
                        $total_hours = 0.00;
                        function getInformation($date) {
                            $formatted_date = date_format($date,"Y-m-d");
                            include("database.php");
                            $stmt = $DBConnect->prepare("SELECT day_type, hours, notes, timesheet_status FROM timesheets WHERE username = ? AND work_date = ? AND client_id = ?");
                            $stmt->bind_param("sss", $GLOBALS['username'], $formatted_date, $_COOKIE['client_id']);
                            $stmt->execute();
                            $stmt->store_result();
                            $stmt->bind_result($retrieved_day_type, $retrieved_hours, $retrieved_notes, $retrieved_timesheet_status);
                            $stmt->fetch();
                            $information_array = array();
                            $information_array['day_type'] = $retrieved_day_type;
                            $information_array['hours'] = $retrieved_hours;
                            $information_array['notes'] = $retrieved_notes;
                            $information_array['timesheet_status'] = $retrieved_timesheet_status;
                            return $information_array;
                        }
                        function dropdown($labels_for_days, $daytype, $month, $current_month, $year, $current_year, $day_timesheet_status){
                            switch($labels_for_days){
                                case 0:
                                    echo "<td><select name='day_types[]' >
                                        <option value='weekend' ";if($daytype=='weekend'){echo 'selected';} echo">Weekend</option>    
                                        <option value='workday' ";if($daytype=='workday'){echo 'selected';} echo">Work Day</option>
                                        </select></td>";
                                    break;
                                case 1:
                                case 2:
                                case 3:
                                case 4:
                                case 5:
                                    echo "<td><select name='day_types[]' >
                                        <option value='workday' ";if($daytype=='workday'){echo 'selected';} echo">Work Day</option>
                                        <option value='fed_holiday' ";if($daytype=='fed_holiday'){echo 'selected';} echo">Federal Holiday</option>
                                        <option value='sickday' ";if($daytype=='sickday'){echo 'selected';} echo">Sick Leave</option>
                                        </select></td>";
                                    break;
                                case 6:
                                    echo "<td><select name='day_types[]' >
                                        <option value='weekend' ";if($daytype=='weekend'){echo 'selected';} echo">Weekend</option>    
                                        <option value='workday' ";if($daytype=='workday'){echo 'selected';} echo">Work Day</option>
                                        </select></td>";
                                    break;
                            }
                        }
                        for ($numerical_day = 1; $numerical_day <= $number_of_days_in_month; $numerical_day++) {
                            $date = date_create("$month/$numerical_day/$year");
                            $textual_day = date("l", mktime(0,0,0,$month,$numerical_day,$year));
                            $info = getInformation($date);
                            $day_hours = $info['hours']; $day_type = $info['day_type']; $day_notes = $info['notes']; $day_timesheet_status = $info['timesheet_status'];
                            $total_hours += $day_hours;
                            $labels_for_days = date("w", mktime(0,0,0,$month,$numerical_day,$year));
                            switch($labels_for_days) {
                                case 0:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                                case 1:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                                case 2:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                                case 3:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                                case 4:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                                case 5:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                                case 6:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                            }
                        }
                    ?>
                        <tr>
                            <th>Total</th>
                            <td></td>
                            <td></td>
                            <td><input type='number' name='total' id='sum' readonly value='<?php echo $total_hours ?>' ></td>
                        </tr>
                </table> 
            </div>
            <br>
            <input type="submit" name="administer_timesheet_submit" value="Submit" />
        </form>
    </body>
</html>
