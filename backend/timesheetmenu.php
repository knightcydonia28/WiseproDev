<!-- Admin user menu for admin access only! Menu for creating and mutating user accounts-->
<?php
include "config.php";

// Check user login or not
if(!isset($_SESSION['uname'])){
    header('Location: index.php');
}

// Checks if user is admin, since this is an admin only page
if($_SESSION['admin'] != 1){
    header('Location: index.php');
}

// logout
if(isset($_POST['but_logout'])){
    session_destroy();
    header('Location: index.php');
}
?>
<!doctype html>
<html>
<head>
<style>
    #header{
        text-align: center;

    }
    #body{
        text-align: center;
        font-size: 30px;
        font-weight: bold;
    }
    #icon{
        font-size: 30px;
        cursor: pointer;
        position: fixed;
        top: 0;
        left: 0;    
        display: inline-block;
    }
    .sidenav{
        height: 100%;
        width: 0;
        position: fixed;
        z-index: 1;
        top: 0;
        left: 0;
        background-color: #10469a;
        overflow-x: hidden;
        transition: 0.5s;
        padding-top: 50px;
    }
    .sidenav a {
        padding: 8px 8px 10px 32px;
        text-decoration: none;
        font-size: 25px;
        color: black;
        display: block;
        transition: 0.5s;
    }

    .sidenav a:hover {
        color: #f1f1f1;
    }

    .sidenav .closenavbtn {
        position: absolute;
        top: 0;
        right: 25px;
        font-size: 36px;
        margin-left: 50px;
    }
    .logout-button{
        margin-top:20px;
        position: absolute;
        right: 25px;
        font-size: 25px;
        margin-left: 50px;
        border: none;
        background-color: none;
        cursor: pointer;
        color: #81818;
    }
</style>
</head>
<body>
    <span id="icon" onclick="openNav()">&#9776;</span>
    <h1 id="header">Admin Panel</h1>
    
    <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closenavbtn" onclick="closesideNav()">&times;</a>
        <a href="admin.php">Home</a>
        <a href="usermenu.php">User Menu</a>
        <a href="timesheetmenu.php">Timesheet Menu</a>
        <a href="#">Create New Job</a>
        <a href="#">Timesheet Edit</a>
        <a href="#">Document Repository</a>
        <a href="#">
            <button class="logout-button">
                Log out
            </button>
        </a>
    </div>

    <div class="timesheet_area">
    <?php
        //This if statement will check whether the 'submit_timesheet' button is pressed. If pressed, the PHP code below will execute.
        if (isset($_POST['submit_timesheet'])) {
            date_default_timezone_set('EST');
            echo "<p>Timesheet has been submitted.</p>";
            echo "<p>Signature: <i>".$_SESSION['name']."</i>; ",date("H:i m/d/Y T"),"<p>";
            
            //This function converts null values to zeros in case the user leaves a cell blank.
            function null_to_zero($given_hours) {
                $changed_hours = array();
                foreach ($given_hours as $hours) {
                    if ($hours=="") {
                        $hours=NULL;
                        $result=isset($hours) ? $hours : "0";
                        $changed_hours[]=$result;
                    } else {
                        $changed_hours[]=$hours; 
                    }  
                }
                return $changed_hours;
            }
            
            //Updates the hour value of an already existing date (if located in the database)
            function update_hours($formatted_date, $hours_array, $DBConnect, $hours, $first_day_of_week, $last_day_of_week, $day_types_array, $notes_array, $id){
                $sql = 'UPDATE `Timesheet` SET `hours`='.$hours.', `day_type`="'.$day_types_array.'", `notes`="'.$notes_array.'" WHERE work_date = "'.$formatted_date.'" and id = '.$id;
                $result = mysqli_query($DBConnect, $sql);
            }

            //Adds a new row to the database including the work day, hours of that day, and week start/end info
            function add_hours($formatted_date, $hours_array, $DBConnect, $hours, $first_day_of_week, $last_day_of_week, $day_types_array, $notes_array, $id){
                $sql = "INSERT INTO Timesheet (id, work_date, hours, first_day_week, last_day_week, day_type, notes) VALUES (?,?,?,?,?,?,?)";
                $stmt = $DBConnect->prepare($sql);
                $stmt->bind_param("isdssss", $id, $formatted_date, $hours, $first_day_of_week, $last_day_of_week, $day_types_array, $notes_array);
                $stmt->execute();
            }
                                
            //Here, the hours (with zeros from the previous function) are added into the array.
            $id_input = $_SESSION['id_input'];
            $hours_array = null_to_zero($_POST['hours']);
            $day_types_array = $_POST['day_types'];
            $notes_array = $_POST['notes'];
    
            $date_count = 1;
            $day_type_count = 0;
            $notes_count = 0;
            //Iterates through all of the hours submitted and either updates or adds to database depending on if the date already exists in the table
            foreach ($hours_array as $hours) {
                $date = date_create($_SESSION['year']."/".$_SESSION['month']."/".$date_count);
                $formatted_date = date_format($date,"Y-m-d");
                $time = strtotime($formatted_date);
                $first_day_of_week = date('Y-m-d', strtotime('Last Monday', $time));
                $last_day_of_week = date('Y-m-d', strtotime('Next Sunday', $time));
                
        
                //Checks database if the current date is already within the table
                $sql = 'SELECT COUNT(1) FROM Timesheet WHERE work_date="'.$formatted_date.'"';
                $result = mysqli_query($DBConnect, $sql);
                $result = mysqli_fetch_row($result);
                
                //result variable will either be a 1 if is already there, or a 0 if not
                if ($result[0] == 1) {
                    update_hours($formatted_date, $hours_array, $DBConnect, $hours, $first_day_of_week, $last_day_of_week, $day_types_array[$day_type_count], $notes_array[$notes_count], $id_input);
                    $date_count++;
                    $day_type_count++;
                    $notes_count++;
                } else {
                    add_hours($formatted_date, $hours_array, $DBConnect, $hours, $first_day_of_week, $last_day_of_week, $day_types_array[$day_type_count], $notes_array[$notes_count], $id_input);
                    $date_count++;
                    $day_type_count++;
                    $notes_count++;
                }
            }
        }
    ?>
    <h4>Timesheet:</h4>
    <p>Please enter your hours below.</p>
    <form method="post" action="#">
        <div class="timesheet_date">
            <input name="id_input" type="text" required="required" placeholder="Enter a User ID"></input>
            <select name="year" required="required">
                <option value="" selected="selected" disabled="disabled">Select a year</option>
                <?php
                    //This code will print out the current year with the 3 previous years.
                    $current_year = date("Y");
                    for ($year = $current_year; $year >= $current_year-3; $year--) {
                        echo "<option value = '$year'>$year</option>"; 
                    }
                ?>
            </select>
            <select name="month" required="required">
                <option value="" selected="selected" disabled="disabled">Select a month</option>
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
            <input type='submit' name='display_timesheet' value='Display Timesheet' />
        </div>
    </form>
    <br />
    <?php
        function get_hours($date, $id_input){
            $DBHost = "mysql-wisepro.wisepro.com";
            $DBUserName = "knachiappan01";
            $DBPassword = "Karman#02";
            $DBName = "wisepro";
            $DBConnect = mysqli_connect($DBHost, $DBUserName, $DBPassword, $DBName);
            if (!$DBConnect) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $formatted_date = date_format($date,"Y-m-d");
            $sql = 'SELECT hours, day_type, notes FROM Timesheet WHERE work_date="'.$formatted_date.'" and id='.$id_input;
            $result = mysqli_query($DBConnect, $sql);
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                        return $row;
                    }
            } else {
                return 0;
            }
            mysqli_close($DBConnect);
        }
        function dropdown($labels_for_days, $daytype){
            switch($labels_for_days){
                case 0:
                    echo "<td><select name='day_types[]' ",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : "",">
                        <option value='weekend' ";if($daytype=='weekend'){echo 'selected';} echo">Weekend</option>    
                        <option value='workday' ";if($daytype=='workday'){echo 'selected';} echo">Work Day</option>
                        </select></td>";
                    break;
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                    echo "<td><select name='day_types[]' ",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : "",">
                        <option value='workday' ";if($daytype=='workday'){echo 'selected';} echo">Work Day</option>
                        <option value='fed_holiday' ";if($daytype=='fed_holiday'){echo 'selected';} echo">Federal Holiday</option>
                        <option value='sickday' ";if($daytype=='sickday'){echo 'selected';} echo">Sick Leave</option>
                        </select></td>";
                    break;
                case 6:
                    echo "<td><select name='day_types[]' ",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : "",">
                        <option value='weekend' ";if($daytype=='weekend'){echo 'selected';} echo">Weekend</option>    
                        <option value='workday' ";if($daytype=='workday'){echo 'selected';} echo">Work Day</option>
                        </select></td>";
                    break;
            }
        }
        //This if statement will check whether the 'display_timesheet' button is pressed. If pressed, the PHP code below will execute.
        if (isset($_POST['display_timesheet'])) {    
            $_SESSION['id_input'] = $_POST['id_input'];
            $id_input = $_SESSION['id_input'];
            $year = $_POST['year'];
            $month = $_POST['month'];
            $_SESSION['year'] = $year;
            $_SESSION['month'] = $month;
            $number_of_days_in_month = date("t", mktime(0,0,0,$month,1,$year));
            $total_hours = 0.00;
            //Here, the form with the table is going to be printed.
            echo 
                "<form method='post' action='#'>
                    <div class='timesheet_table'>
                        <table>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Hours</th>
                                <th>Reporting Code</th>
                                <th>Notes</th>
                            </tr>";
                            for ($numerical_day = 1; $numerical_day <= $number_of_days_in_month; $numerical_day++) {
                                $current_month = date('m');
                                $current_year = date("Y"); 
                                $date = date_create("$month/$numerical_day/$year");
                                $textual_day = date("l", mktime(0,0,0,$month,$numerical_day,$year));
                                $info = get_hours($date, $id_input);
                                $day_hours = $info['hours']; $day_type = $info['day_type']; $day_notes = $info['notes'];
                                $total_hours += $day_hours;
                                //Here, the day (mon-fri) of a date is determined for the day type dropdown.
                                $labels_for_days = date("w", mktime(0,0,0,$month,$numerical_day,$year));

                                switch($labels_for_days) {
                                    case 0:
                                        echo "<tr><td>".date_format($date, "m/d/Y")."</td>";
                                        echo "<td>$textual_day</td>";
                                        echo "<td><input ",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.01' /></td>";
                                        dropdown($labels_for_days, $day_type);
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                    case 1:
                                        echo "<tr><td>".date_format($date, "m/d/Y")."</td>";
                                        echo "<td>$textual_day</td>";
                                        echo "<td><input ",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.01' /></td>";
                                        dropdown($labels_for_days, $day_type);
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                    case 2:
                                        echo "<tr><td>".date_format($date, "m/d/Y")."</td>";
                                        echo "<td>$textual_day</td>";
                                        echo "<td><input ",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.01' /></td>";
                                        dropdown($labels_for_days, $day_type);
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                    case 3:
                                        echo "<tr><td>".date_format($date, "m/d/Y")."</td>";
                                        echo "<td>$textual_day</td>";
                                        echo "<td><input ",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.01' /></td>";
                                        dropdown($labels_for_days, $day_type);
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                    case 4:
                                        echo "<tr><td>".date_format($date, "m/d/Y")."</td>";
                                        echo "<td>$textual_day</td>";
                                        echo "<td><input ",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.01' /></td>";
                                        dropdown($labels_for_days, $day_type);
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                    case 5:
                                        echo "<tr><td>".date_format($date, "m/d/Y")."</td>";
                                        echo "<td>$textual_day</td>";
                                        echo "<td><input ",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.01' /></td>";
                                        dropdown($labels_for_days, $day_type);
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                    case 6:
                                        echo "<tr><td>".date_format($date, "m/d/Y")."</td>";
                                        echo "<td>$textual_day</td>";
                                        echo "<td><input ",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.01' /></td>";
                                        dropdown($labels_for_days, $day_type);
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                }
                            }
                            echo "<tr>
                                    <th colspan='2'>Total</th>
                                    <td><input type='number' id='sum' readonly value=$total_hours /></td>
                                </tr>
                        </table> 
                        <br />";
                        echo "<input ",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : ""," type='submit' name='submit_timesheet' value='Submit Timesheet' onclick='return add_hours_confirm()'/>&nbsp;";
                        echo "<input ",(($month>$current_month) && ($year==$current_year)) ? "disabled='disabled'" : ""," type='submit' name='save_timesheet' value='Save Timesheet' />
                    </div>
                </form>";
        }
    ?>
    </div>
    <script>
    function openNav() {
        document.getElementById("mySidenav").style.width = "250px";
    }

    function closesideNav() {
        document.getElementById("mySidenav").style.width = "0";
    }
    </script>
</body>
</html>