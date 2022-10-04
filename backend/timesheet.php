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
    if (!isset($_COOKIE['search_user']) && !isset($_COOKIE['home'])) {
        header('Location: home.php');
        exit();
    }
    $GLOBALS['username'] = isset($_COOKIE['home']) ? $_SESSION['username'] : $_COOKIE['username'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Timesheet</title>
        <style>
            table, th, td {
                border:1px solid black;
            }
        </style>
        <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
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
            function add_hours_confirm() {
                var agree = confirm("Click 'OK' if you want to submit your timesheet. Otherwise, click 'Cancel'.");
                if (agree)
                 return true ;
                else
                 return false ;
            }
            function notesValidation(f) {
                !(/^[a-zA-Z0-9\., ]*$/i).test(f.value)?f.value = f.value.replace(/[^a-zA-Z0-9\., ]/ig,''):null;
            } 
        </script>
        <script>
            var _global = typeof window === 'object' && window.window === window
            ? window : typeof self === 'object' && self.self === self
            ? self : typeof global === 'object' && global.global === global
            ? global
            : this

            function bom (blob, opts) {
            if (typeof opts === 'undefined') opts = { autoBom: false }
            else if (typeof opts !== 'object') {
                console.warn('Deprecated: Expected third argument to be a object')
                opts = { autoBom: !opts }
            }

            // prepend BOM for UTF-8 XML and text/* types (including HTML)
            // note: your browser will automatically convert UTF-16 U+FEFF to EF BB BF
            if (opts.autoBom && /^\s*(?:text\/\S*|application\/xml|\S*\/\S*\+xml)\s*;.*charset\s*=\s*utf-8/i.test(blob.type)) {
                return new Blob([String.fromCharCode(0xFEFF), blob], { type: blob.type })
            }
            return blob
            }

            function download (url, name, opts) {
            var xhr = new XMLHttpRequest()
            xhr.open('GET', url)
            xhr.responseType = 'blob'
            xhr.onload = function () {
                saveAs(xhr.response, name, opts)
            }
            xhr.onerror = function () {
                console.error('could not download file')
            }
            xhr.send()
            }

            function corsEnabled (url) {
            var xhr = new XMLHttpRequest()
            // use sync to avoid popup blocker
            xhr.open('HEAD', url, false)
            try {
                xhr.send()
            } catch (e) {}
            return xhr.status >= 200 && xhr.status <= 299
            }

            // `a.click()` doesn't work for all browsers (#465)
            function click (node) {
            try {
                node.dispatchEvent(new MouseEvent('click'))
            } catch (e) {
                var evt = document.createEvent('MouseEvents')
                evt.initMouseEvent('click', true, true, window, 0, 0, 0, 80,
                                    20, false, false, false, false, 0, null)
                node.dispatchEvent(evt)
            }
            }

            // Detect WebView inside a native macOS app by ruling out all browsers
            // We just need to check for 'Safari' because all other browsers (besides Firefox) include that too
            // https://www.whatismybrowser.com/guides/the-latest-user-agent/macos
            var isMacOSWebView = /Macintosh/.test(navigator.userAgent) && /AppleWebKit/.test(navigator.userAgent) && !/Safari/.test(navigator.userAgent)

            var saveAs = _global.saveAs || (
            // probably in some web worker
            (typeof window !== 'object' || window !== _global)
                ? function saveAs () { /* noop */ }

            // Use download attribute first if possible (#193 Lumia mobile) unless this is a macOS WebView
            : ('download' in HTMLAnchorElement.prototype && !isMacOSWebView)
            ? function saveAs (blob, name, opts) {
                var URL = _global.URL || _global.webkitURL
                var a = document.createElement('a')
                name = name || blob.name || 'download'

                a.download = name
                a.rel = 'noopener' // tabnabbing

                // TODO: detect chrome extensions & packaged apps
                // a.target = '_blank'

                if (typeof blob === 'string') {
                // Support regular links
                a.href = blob
                if (a.origin !== location.origin) {
                    corsEnabled(a.href)
                    ? download(blob, name, opts)
                    : click(a, a.target = '_blank')
                } else {
                    click(a)
                }
                } else {
                // Support blobs
                a.href = URL.createObjectURL(blob)
                setTimeout(function () { URL.revokeObjectURL(a.href) }, 4E4) // 40s
                setTimeout(function () { click(a) }, 0)
                }
            }

            // Use msSaveOrOpenBlob as a second approach
            : 'msSaveOrOpenBlob' in navigator
            ? function saveAs (blob, name, opts) {
                name = name || blob.name || 'download'

                if (typeof blob === 'string') {
                if (corsEnabled(blob)) {
                    download(blob, name, opts)
                } else {
                    var a = document.createElement('a')
                    a.href = blob
                    a.target = '_blank'
                    setTimeout(function () { click(a) })
                }
                } else {
                navigator.msSaveOrOpenBlob(bom(blob, opts), name)
                }
            }

            // Fallback to using FileReader and a popup
            : function saveAs (blob, name, opts, popup) {
                // Open a popup immediately do go around popup blocker
                // Mostly only available on user interaction and the fileReader is async so...
                popup = popup || open('', '_blank')
                if (popup) {
                popup.document.title =
                popup.document.body.innerText = 'downloading...'
                }

                if (typeof blob === 'string') return download(blob, name, opts)

                var force = blob.type === 'application/octet-stream'
                var isSafari = /constructor/i.test(_global.HTMLElement) || _global.safari
                var isChromeIOS = /CriOS\/[\d]+/.test(navigator.userAgent)

                if ((isChromeIOS || (force && isSafari) || isMacOSWebView) && typeof FileReader !== 'undefined') {
                // Safari doesn't allow downloading of blob URLs
                var reader = new FileReader()
                reader.onloadend = function () {
                    var url = reader.result
                    url = isChromeIOS ? url : url.replace(/^data:[^;]*;/, 'data:attachment/file;')
                    if (popup) popup.location.href = url
                    else location = url
                    popup = null // reverse-tabnabbing #460
                }
                reader.readAsDataURL(blob)
                } else {
                var URL = _global.URL || _global.webkitURL
                var url = URL.createObjectURL(blob)
                if (popup) popup.location = url
                else location.href = url
                popup = null // reverse-tabnabbing #460
                setTimeout(function () { URL.revokeObjectURL(url) }, 4E4) // 40s
                }
            }
            )

            _global.saveAs = saveAs.saveAs = saveAs

            if (typeof module !== 'undefined') {
            module.exports = saveAs;
            }
        </script>
    </head>
    <body>
        <?php
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

            //This if statement will check whether the 'save_timesheet' button is pressed. If pressed, the PHP code below will execute.
            if(isset($_POST['save_timesheet'])){
                echo "<p>Timesheet has been saved.</p>"; 

                //Updates the hour value of an already existing date (if located in the database)
                function update_hours($formatted_date, $hours_array, $DBConnect, $hours, $first_day_of_week, $last_day_of_week, $day_types_array, $notes_array){
                    $stmt = $DBConnect->prepare("UPDATE timesheets SET hours=?, day_type=?, notes=? WHERE work_date=? AND username =?");
                    $stmt->bind_param("sssss", $hours,  $day_types_array, $notes_array, $formatted_date, $GLOBALS['username']);
                    $stmt->execute();
                }

                //Adds a new row to the database including the work day, hours of that day, and week start/end info
                function add_hours($formatted_date, $hours_array, $DBConnect, $hours, $first_day_of_week, $last_day_of_week, $day_types_array, $notes_array){
                    include("database.php");
                    $client_id = 1; //temporary values, DELETE WHEN NEEDED
                    $vendor_id = 1;
                    $timesheet_status = 0;
                    $sql = "INSERT INTO timesheets (username, work_date, client_id, vendor_id, day_type, hours, notes, first_day_week, last_day_week, timesheet_status) VALUES (?,?,?,?,?,?,?,?,?,?)";
                    $stmt = $DBConnect->prepare($sql);
                    $stmt->bind_param("ssssssssss", $GLOBALS['username'], $formatted_date, $client_id, $vendor_id, $day_types_array, $hours, $notes_array, $first_day_of_week, $last_day_of_week, $timesheet_status);
                    $stmt->execute();
                }
                                    
                //Here, the hours (with zeros from the previous function) are added into the array.
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
                    include("database.php");
                    $stmt = $DBConnect->prepare("SELECT COUNT(work_date) AS COUNT FROM timesheets WHERE work_date = ?");
                    $stmt->bind_param("s", $formatted_date);
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($retrieved_work_date_count);
                    $stmt->fetch();
                    
                    //result variable will either be a 1 if is already there, or a 0 if not
                    if ($retrieved_work_date_count == 1) {
                        update_hours($formatted_date, $hours_array, $DBConnect, $hours, $first_day_of_week, $last_day_of_week, $day_types_array[$day_type_count], $notes_array[$notes_count]);
                        $date_count++;
                        $day_type_count++;
                        $notes_count++;
                    } else {
                        add_hours($formatted_date, $hours_array, $DBConnect, $hours, $first_day_of_week, $last_day_of_week, $day_types_array[$day_type_count], $notes_array[$notes_count]);
                        $date_count++;
                        $day_type_count++;
                        $notes_count++;
                    }
                }
            }
            //This if statement will check whether the 'submit_timesheet' button is pressed. If pressed, the PHP code below will execute.
            if (isset($_POST['submit_timesheet'])) {
                date_default_timezone_set('EST');
                echo "<p>Timesheet has been submitted.</p>";
                echo "<p>Signature: <i>"."NAME "."</i>; ",date("H:i m/d/Y T"),"<p>";
                $hours_array = null_to_zero($_POST['hours']);
                $date_count = 1;
                $timesheet_status = 1;
                //Iterates through all of the hours submitted and either updates or adds to database depending on if the date already exists in the table
                foreach ($hours_array as $hours) {
                    $date = date_create($_SESSION['year']."/".$_SESSION['month']."/".$date_count);
                    $formatted_date = date_format($date,"Y-m-d");
                    $time = strtotime($formatted_date);
                    $first_day_of_week = date('Y-m-d', strtotime('Last Monday', $time));
                    $last_day_of_week = date('Y-m-d', strtotime('Next Sunday', $time));

                    include("database.php");
                    $stmt = $DBConnect->prepare("UPDATE timesheets SET timesheet_status=? WHERE work_date=? AND username =?");
                    $stmt->bind_param("sss", $timesheet_status, $formatted_date, $GLOBALS['username']);
                    $stmt->execute();
                    $date_count++;
                }
            }
        ?>
        
        <form method="post" action="#">
            <input type="submit" name="logout" value="Logout" />
        </form>
        <?php
            if (isset($_POST['logout'])) {
                include("logout.php");
                logout();
            }
        ?>
        <h4>Timesheet: Swag Edition v2</h4>
        <p>Please enter your hours below.</p>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="timesheet_date">

                <?php
                    //Queries for the users employment start date
                    include("database.php");
                    $stmt = $DBConnect->prepare("SELECT employment_start_date FROM employments WHERE username = ?");
                    $stmt->bind_param("s", $GLOBALS['username']);
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($employment_start_date);
                    $stmt->fetch();
                    $start_date_array = explode("-", $employment_start_date);
                ?>

                <select name="year" required>
                    <option value="">&nbsp;</option>
                    <?php
                        //This code will print out the current year with the 3 previous years.
                        $current_year = date("Y");
                        for ($year = $start_date_array[0]; $year <= $current_year; $year++) {
                            echo "<option value=\"$year\""; if ($year==$current_year) {echo "selected";} echo ">$year</option>"; 
                        }
                    ?>
                </select>
                <select name="month" required>
                    <option value="" selected disabled>Select a month</option>
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
            function getInformation($date) {
                $formatted_date = date_format($date,"Y-m-d");
                include("database.php");
                $stmt = $DBConnect->prepare("SELECT day_type, hours, notes, timesheet_status FROM timesheets WHERE username = ? AND work_date = ?");
                $stmt->bind_param("ss", $GLOBALS['username'], $formatted_date);
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
                        echo "<td><select name='day_types[]' ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled " : "",">
                            <option value='weekend' ";if($daytype=='weekend'){echo 'selected';} echo">Weekend</option>    
                            <option value='workday' ";if($daytype=='workday'){echo 'selected';} echo">Work Day</option>
                            </select></td>";
                        break;
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                        echo "<td><select name='day_types[]' ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : "",">
                            <option value='workday' ";if($daytype=='workday'){echo 'selected';} echo">Work Day</option>
                            <option value='fed_holiday' ";if($daytype=='fed_holiday'){echo 'selected';} echo">Federal Holiday</option>
                            <option value='sickday' ";if($daytype=='sickday'){echo 'selected';} echo">Sick Leave</option>
                            </select></td>";
                        break;
                    case 6:
                        echo "<td><select name='day_types[]' ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : "",">
                            <option value='weekend' ";if($daytype=='weekend'){echo 'selected';} echo">Weekend</option>    
                            <option value='workday' ";if($daytype=='workday'){echo 'selected';} echo">Work Day</option>
                            </select></td>";
                        break;
                }
            }
            //This if statement will check whether the 'display_timesheet' button is pressed. If pressed, the PHP code below will execute.
            if (isset($_POST['display_timesheet'])) {  
                
                function test_input($data) {
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                }

                $year = test_input($_POST['year']);
                $month = test_input($_POST['month']);
                $_SESSION['year'] = $year;
                $_SESSION['month'] = $month;

                $number_of_days_in_month = date("t", mktime(0,0,0,$month,1,$year));
                $total_hours = 0.00;
                //Here, the form with the table is going to be printed.
                echo 
                "<form method='post' action='#'>
                    <div class='timesheet_table'>
                        <table id='tbl_exporttable_to_xls'>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Reporting Code</th>
                                <th>Hours</th>
                                <th>Notes</th>
                            </tr>";
                            for ($numerical_day = 1; $numerical_day <= $number_of_days_in_month; $numerical_day++) {
                                $current_month = date('m');
                                $current_year = date("Y"); 
                                $date = date_create("$month/$numerical_day/$year");
                                $textual_day = date("l", mktime(0,0,0,$month,$numerical_day,$year));
                                $info = getInformation($date);
                                $day_hours = $info['hours']; $day_type = $info['day_type']; $day_notes = $info['notes']; $day_timesheet_status = $info['timesheet_status'];
                                $total_hours += $day_hours;
                                //Here, the day (mon-fri) of a date is determined for the day type dropdown.
                                $labels_for_days = date("w", mktime(0,0,0,$month,$numerical_day,$year));
                                switch($labels_for_days) {
                                    case 0:
                                        echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly /></td>";
                                        echo "<td>$textual_day</td>";
                                        dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                        echo "<td><input ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' /></td>";
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                    case 1:
                                        echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly /></td>";
                                        echo "<td>$textual_day</td>";
                                        dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                        echo "<td><input ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' /></td>";
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                    case 2:
                                        echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly /></td>";
                                        echo "<td>$textual_day</td>";
                                        dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                        echo "<td><input ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' /></td>";
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                    case 3:
                                        echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly /></td>";
                                        echo "<td>$textual_day</td>";
                                        dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                        echo "<td><input ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' /></td>";
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                    case 4:
                                        echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly /></td>";
                                        echo "<td>$textual_day</td>";
                                        dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                        echo "<td><input ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' /></td>";
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                    case 5:
                                        echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly /></td>";
                                        echo "<td>$textual_day</td>";
                                        dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                        echo "<td><input ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' /></td>";
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                    case 6:
                                        echo "<tr><td><input type='text' name='dates' value='".date_format($date, "m/d/Y")."' readonly /></td>";
                                        echo "<td>$textual_day</td>";
                                        dropdown($labels_for_days, $day_type, $month, $current_month, $year, $current_year, $day_timesheet_status);
                                        echo "<td><input ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : ""," type='number' value=$day_hours class='hours' oninput='findTotal()' name='hours[]' min='0' max='24' step='0.05' /></td>";
                                        echo "<td><textarea id='notes' name='notes[]' rows='1' cols='30' onkeyup='notesValidation(this)' onblur='notesValidation(this)'",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : "",">".$day_notes."</textarea></td></tr>";
                                        break;
                                }
                            }
                            echo "<tr>
                                    <th colspan='2'>Total</th>
                                    <td> </td>
                                    <td><input type='number' name='total' id='sum' readonly value=$total_hours /></td>
                                </tr>
                        </table> 
                        <br />";
                        echo "<input ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : ""," type='submit' name='submit_timesheet' value='Submit Timesheet' onclick='return add_hours_confirm()'/>&nbsp;";
                        echo "<input ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : ""," type='submit' name='save_timesheet' value='Save Timesheet' />
                    
                        </div>
                </form>";
                    $ex_month = $month;
                    $ex_year = $year;
                    echo "<button onclick='getFormData()' ",((($month != $current_month) || ($year != $current_year)) || $day_timesheet_status == 1) ? "disabled" : "",">Export table to excel</button>
                        <script>
                            src=\"https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.2/xlsx.full.min.js\"
                            function getFormData() {
                                let res = [];
                                let a = document.getElementsByName(\"dates\");
                                let b = document.getElementsByName(\"hours[]\");
                                let c = document.getElementsByName(\"day_types[]\");
                                let d = document.getElementsByName(\"notes[]\");
                                let e = 0;
                            
                                for (let i = 0; i < a.length; i++) {
                                    let obj = {
                                        \"Date\" : a[i].value,
                                        \"Reporting Code\" : c[i].value,
                                        \"Hours\" : b[i].value,
                                        \"Notes\" : d[i].value
                                    } 
                                    num = parseFloat(b[i].value);
                                    e += num;
                                    res.push(obj);
                                }
                                let obj = {
                                    \"Date\" : 'Total',
                                    \"Reporting Code\" : e
                                }
                                res.push(obj);
                                console.log(res);
                                downloadAsExcel(res);
                            }
                            document.getElementById(\"json\").innerHTML = JSON.stringify(data,undefined,4);
                            const EXCEL_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8';
                            const EXCEL_EXTENSION = '.xlsx';

                            function downloadAsExcel(data){
                                const worksheet  = XLSX.utils.json_to_sheet(data);
                                const workbook = {
                                    Sheets:{
                                        'data':worksheet
                                    },
                                    SheetNames:['data']
                                };
                                const excelBuffer = XLSX.write(workbook,{bookType:'xlsx',type:'array'});
                                console.log(excelBuffer);
                                let month = '$ex_month';
                                let year = '$ex_year';
                                file_name = ".json_encode($_SESSION['uname'])." + \"_\" + year + \"_\" + month;
                                saveAsExcel(excelBuffer,file_name);
                            }

                            function saveAsExcel(buffer,filename){
                                const data = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8' });
                                saveAs(data,filename);
                            }

                        </script>";
            }
        ?>
    </body>
</html>