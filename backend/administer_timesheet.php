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
        <?php
            include("session_timeout.php");
        ?>
        <meta charset="UTF-8">
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
            .home-button {
                background-color: #10469A;
                color: white;
                padding-top: 8px;
                padding-bottom: 8px;
                padding-right: 8px;
                padding-left: 8px;
                border-radius: 20px;
                cursor: pointer;
                border:solid;
                display:inline-block;
                margin-bottom: 7px;
                width: 60px;
            }
            .logout-button {
                background-color: #10469A;
                color: white;
                padding-top: 8px;
                padding-bottom: 8px;
                padding-right: 8px;
                padding-left: 8px;
                border-radius: 20px;
                cursor: pointer;
                border:solid;
                width: 60px;
                margin-bottom: 7px;
            }
            a {
                text-decoration: none;
                outline: none;
            }
            table {
                border-collapse: collapse;
                width: 50%;
            }
            th, td {
                text-align: left;
                padding: 8px;
            }
            tr:nth-child(even){background-color: #f2f2f2}
            
            th {
                background-color: #10469A;
                color: white;
            }
            input[type=submit] {
                background-color: #10469A;
                color: white;
                padding: 12px 12px;
                margin: 8px 0;
                border: none;
                cursor: pointer;
                border-radius: 20px;
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
    <body onload="displayTextArea()">
        <a href="home.php">
            <button class="home-button">Home</button>
        </a>        
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>
            <button class="logout-button">Logout</button>
        </a>
        <?php
            if ($_SERVER['REQUEST_METHOD'] === "POST") {

                function testInput($data) {
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                }

                function nullToZero($provided_hours) {
                    if ($provided_hours == "") {
                        $provided_hours = NULL;
                        $provided_hours = isset($provided_hours) ? $provided_hours : "0";
                        return $provided_hours;
                    } 
                    else {
                        return $provided_hours;
                    }  
                }

                function validateHours($provided_hours) {
                    $provided_hours = testInput($provided_hours);
                    if (!is_numeric($provided_hours)) {
                        $_SESSION['hours_error'] = "<p class=\"error\">Please ensure that hours are numeric</p>";
                        header("Location: administer_timesheet_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_hours;
                    }
                }

                function validateDayTypes($provided_day_types) {
                    $provided_day_types = testInput($provided_day_types);
                    if ($provided_day_types != "weekend" && $provided_day_types != "workday" && $provided_day_types != "fed_holiday" && $provided_day_types != "sickday") {
                        $_SESSION['day_types_error'] = "<p class=\"error\">Please select an appropriate reporting code</p>";
                        header("Location: administer_timesheet_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_day_types;
                    }
                }

                function validateNotes($provided_notes) {
                    $provided_notes = testInput($provided_notes);
                    return $provided_notes;
                }

                function validatedArrays($provided_hours_array, $provided_day_types_array, $provided_notes_array) {
                    
                    $total_days = date("t", mktime(0,0,0,$_SESSION['month'],1,$_SESSION['year']));
                    
                    $validated_hours_array = array();
                    $validated_day_types_array = array();
                    $validated_notes_array = array();
                
                    
                    for ($i=0; $i<$total_days; $i++) {
                        $validated_hours_array[] = validateHours(nullToZero($provided_hours_array[$i]));
                        $validated_day_types_array[] = validateDayTypes($provided_day_types_array[$i]);
                        $validated_notes_array[] = validateNotes($provided_notes_array[$i]);
                    }

                    $result = array();
                    $result[] = $validated_hours_array;
                    $result[] = $validated_day_types_array;
                    $result[] = $validated_notes_array;
                    return $result;
                }

                function validateDecision($provided_decision) {
                    $provided_decision = testInput($provided_decision);
                    if ($provided_decision != 2 && $provided_decision != 3) {
                        $_SESSION['decision_error'] = "<p class=\"error\">Please select an appropriate decision</p>";
                        header("Location: administer_timesheet_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        return $provided_decision;
                    }
                }

                $sanitized_arrays = validatedArrays($_POST['hours'], $_POST['day_types'], $_POST['notes']);
                
                $hours_array = $sanitized_arrays[0];
                $day_types_array = $sanitized_arrays[1]; 
                $notes_array = $sanitized_arrays[2];

                $total_days = date("t", mktime(0,0,0,$_SESSION['month'],1,$_SESSION['year']));
                $date_range = range(1, $total_days);
                
                $confirmation_array = array();

                for ($i=0; $i<$total_days; $i++) {
                    $date = date_create($_SESSION['year']."/".$_SESSION['month']."/".strval($date_range[$i]));
                    $formatted_date = date_format($date,"Y-m-d");
                    $time = strtotime($formatted_date);
                    $first_day_of_week = date('Y-m-d', strtotime('Last Monday', $time));
                    $last_day_of_week = date('Y-m-d', strtotime('Next Sunday', $time));
                    
                    //echo "<p>UPDATE timesheets SET hours = ".$hours_array[$i].", day_type = ".$day_types_array[$i].", notes = ".$notes_array[$i]." WHERE work_date = ".$formatted_date." AND username = ".$GLOBALS['username']." AND client_id = ".$_COOKIE['client_id']."</p>";
                    
                    include("database.php");
                    $stmt = $DBConnect->prepare("UPDATE timesheets SET hours = ?, day_type = ?, notes = ? WHERE work_date = ? AND username = ? AND client_id = ?");
                    $stmt->bind_param("dssssi", $hours_array[$i], $day_types_array[$i], $notes_array[$i], $formatted_date, $GLOBALS['username'], $_COOKIE['client_id']);
                    if ($stmt->execute()) {
                        $confirmation_array[] = 1;
                    }
                    $stmt->close();
                    $DBConnect->close();
                }
                
                /*
                $selected_decision = validateDecision($_POST['selected_decision']);
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
                */
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

            $stmt = $DBConnect->prepare("SELECT DISTINCT timesheet_status, reject_reason FROM timesheets WHERE username = ? AND MONTH(work_date) = ? AND YEAR(work_date) = ? AND client_id = ?");
            $stmt->bind_param("siis", $GLOBALS['username'], $_SESSION['month'], $_SESSION['year'], $_COOKIE['client_id']);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($retrieved_timesheet_status, $retrieved_reject_reason);
            $stmt->fetch();

            $stmt->close();
            $DBConnect->close();
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <?php
            echo 
            "<div class=\"timesheet_information\">
                <p><b>Username:</b> ".$GLOBALS['username']."</p>
                <p><b>Timesheet Period:</b> ".$_SESSION['month']."/".$_SESSION['year']."</p>
                <p><b>Full Name:</b> ".ucfirst(strtolower($retrieved_user_first_name))," ", ucfirst(strtolower($retrieved_user_middle_name))," ", ucfirst(strtolower($retrieved_user_last_name))."</p>
                <p><b>Client:</b> $retrieved_client_name</p>"; 
            ?>
            <select id="selected_decision" name="selected_decision" onchange="displayTextArea()" required>
                <option value="" <?php if($retrieved_timesheet_status != 2 && $retrieved_timesheet_status != 3) {echo "selected";} ?> disabled>Select Decision</option>
                <option value=2 <?php if($retrieved_timesheet_status == 2) {echo "selected";} ?>>Approve</option>
                <option value=3 <?php if($retrieved_timesheet_status == 3) {echo "selected";} ?>>Reject</option>
            </select>
            <br>
            <p><label id="reject_reason_label" for="reject_reason" hidden>Rejection Reason (Required):</label></p>
            <textarea id="reject_reason" name="reject_reason" rows="3" cols="40" hidden><?php echo (is_null($retrieved_reject_reason)) ? "" : trim($retrieved_reject_reason); ?></textarea>
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
                                    echo "<td><textarea name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                                case 1:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                                case 2:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                                case 3:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                                case 4:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                                case 5:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                                case 6:
                                    echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly ></td>";
                                    echo "<td><input type='text' name='textual_day' value='".$textual_day."' readonly ></td>";
                                    dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                    echo "<td><input type='number' value='$day_hours' class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' ></td>";
                                    echo "<td><textarea name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'>".$day_notes."</textarea></td></tr>";
                                    break;
                            }
                        }
                    ?>
                        <tr>
                            <th>Total</th>
                            <td></td>
                            <td></td>
                            <td><input type='number' name='total' id='sum' readonly value='<?php echo $total_hours ?>' ></td>
                            <td></td>
                        </tr>
                </table> 
            </div>
            <br>
            <input type="submit" name="administer_timesheet_submit" value="Submit">
        </form>
    </body>
</html>
