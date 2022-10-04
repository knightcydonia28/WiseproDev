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
    unset($_SESSION['home']);
    setcookie("search_user", "", time() - 3600);
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
        ?>
        <meta charset="UTF-8" />
        <title>Search User</title>
        <script>
            function getUserInformation(username) {
                var xhttp;
                xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState != 4 && this.status != 200) {
                        console.log(this.readyState);
                        console.log(this.status);
                        //Come back to this part.
                    }
                };
                xhttp.open("GET", "get_user_information.php?q="+username, true);
                xhttp.send();
            }
            function getUsername(row_id) {
                var tr = document.getElementById(row_id);
                var td = tr.getElementsByTagName("td");
                var username = (td[1].innerHTML);
                return username;
            }
        </script>
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
        <form method="post" action="#">
            <input type="submit" name="logout" value="Logout" />
        </form>
        <?php
            if (isset($_POST['logout'])) {
                include("logout.php");
                logout();
            }
        ?>
        <h2>Search User</h2>
        <p>Please fill in one or all of the following:</p>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric" /><span class="error"> <?php echo $username_error; ?></span><br /><br />
            <label for="user_first_name">First Name:</label>
            <input type="text" id="user_first_name" name="user_first_name" placeholder="first name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your first name has letters, dashes, apostrophes and whitespaces only" /><span class="error"> <?php echo $user_first_name_error; ?></span><br /><br />
            <label for="user_middle_name">Middle Name:</label>
            <input type="text" id="user_middle_name" name="user_middle_name" placeholder="middle name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only" /><span class="error"> <?php echo $user_middle_name_error; ?></span><br /><br />
            <label for="user_last_name">Last Name:</label>
            <input type="text" id="user_last_name" name="user_last_name" placeholder="last name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your last name has letters, dashes, apostrophes and whitespaces only" /><span class="error"> <?php echo $user_last_name_error; ?></span><br /><br />
            <input type="submit" name="search_user_submit" value="Search User" />
        </form> 
        <?php                        
            if (isset($_POST['search_user_submit'])) {

                function test_input($data) {
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                }

                if (empty($_POST['username']) && empty($_POST['user_first_name']) && empty($_POST['user_middle_name']) && empty($_POST['user_last_name'])) {
                    echo "<p class=\"error\">Please fill in at least one of the input fields.</p>";
                }
                else {
                    if (!empty($_POST['username'])) {
                        if (!ctype_alnum($_POST['username'])) {
                            $username_error = "Please ensure that your username is alphanumeric";
                        }
                        else {
                            $username = test_input($_POST['username']);
                        }    
                    }
                    else {
                        $username = NULL;
                    }
                    if (!empty($_POST['user_first_name'])) {
                        if (!preg_match("/^[a-zA-Z-' ]*$/", $_POST['user_first_name'])) {
                            $user_first_name_error = "Please ensure that your first name has letters, dashes, apostrophes and whitespaces only";
                        }
                        else {
                            $user_first_name = test_input($_POST['user_first_name']);
                        }      
                    }
                    else {
                        $user_first_name = NULL;
                    }
                    if (!empty($_POST['user_middle_name'])) {
                        if (!preg_match("/^[a-zA-Z-' ]*$/", $_POST['user_middle_name'])) {
                            $user_middle_name_error = "Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only";
                        }
                        else {
                            $user_middle_name = test_input($_POST['user_middle_name']);
                        }
                    }
                    else {
                        $user_middle_name = NULL;
                    }
                    if (!empty($_POST['user_last_name'])) {
                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['user_last_name'])) {
                            $user_last_name_error = "Please ensure that your last name has letters, dashes, apostrophes and whitespaces only";
                        }
                        else {
                            $user_last_name = test_input($_POST['user_last_name']);
                        }
                    }
                    else {
                        $user_last_name = NULL;
                    }
                }
                
                include("database.php");
                $stmt = $DBConnect->prepare("SELECT username, user_first_name, user_middle_name, user_last_name FROM users WHERE username LIKE CONCAT(?, '%') OR user_first_name LIKE CONCAT(?, '%') OR user_middle_name LIKE CONCAT(?, '%') OR user_last_name LIKE CONCAT(?, '%')");
                $stmt->bind_param("ssss", $username, $user_first_name, $user_middle_name, $user_last_name); 
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($retrieved_username, $retrieved_user_first_name, $retrieved_user_middle_name, $retrieved_user_last_name);
                
                if ($stmt->num_rows > 0) {
                    $table_row_count = 1;
                    echo
                    "<br />
                    <table>
                        <tr>
                            <th>Action</th>
                            <th>Username</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Last Name</th>
                        </tr>";
                    while($stmt->fetch()) {
                        echo
                        "<tr id=\"$table_row_count\">
                            <td><select name=\"action\" id=\"action_$table_row_count\" onclick=\"getUserInformation(getUsername('$table_row_count'))\" onchange=\"document.cookie='search_user=1'; window.location.replace(this.value);\">
                                    <option value=\"\" selected disabled>Select Action</option>
                                    <option value=\"http://wisepro.com/testing6/view_user.php\">View User</option>
                                    <option value=\"http://wisepro.com/testing6/edit_user.php\">Edit User</option>
                                    <option value=\"http://wisepro.com/testing6/add_employment.php\">Add Employment</option>
                                    <option value=\"http://wisepro.com/testing6/choose_timesheet.php\">Choose Timesheet</option>
                                    <option value=\"http://wisepro.com/testing6/timesheet.php\">Timesheet</option>
                                </select>
                            </td>
                            <td>$retrieved_username</td>
                            <td>$retrieved_user_first_name</td>
                            <td>$retrieved_user_middle_name</td>
                            <td>$retrieved_user_last_name</td>
                        </tr>";
                        $table_row_count++;
                    }
                    echo "</table>";
                }
                else {
                    echo "<p>User(s) not found.</p>";
                }
                $stmt->close();
                $DBConnect->close();
            }
        ?>
    </body>
</html>

