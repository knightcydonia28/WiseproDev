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
    setcookie("search_user", 1);
    setcookie("home", "", time() - 3600);
    setcookie("choose_timesheet", "", time() - 3600);
    setcookie("choose_employment", "", time() - 3600);
    unset($_SESSION['disable_choose_timesheet']);
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
        <title>Search User</title>
        <script>
            function getUsername(row_id) {
                var tr = document.getElementById(row_id);
                var td = tr.getElementsByTagName("td");
                var username = (td[1].innerHTML);
                document.cookie = "username="+username; 
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
        <a href="home.php">Home</a><br><br>
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>Logout</a>
        <h2>Search User</h2>
        <p>Please fill in one, some, or all of the following:</p>
        <?php
            $search_user_error = array();
            if ($_SERVER['REQUEST_METHOD'] === "POST") {
                function test_input($data) {
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                }

                if (empty($_POST['username']) && empty($_POST['user_first_name']) && empty($_POST['user_middle_name']) && empty($_POST['user_last_name'])) {
                    $search_user_error[] = "<p class=\"error\">Please fill in at least one of the input fields.</p>";
                    $_SESSION["search_user_error"] = $search_user_error;
                }
                else {
                    if (!empty($_POST['username'])) {
                        if (!ctype_alnum($_POST['username'])) {
                            $_SESSION["username_error"] = "Please ensure that your username is alphanumeric";
                            header("Location: search_user.php", true, 303);
                            exit();
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
                            $_SESSION["user_first_name_error"] = "Please ensure that your first name has letters, dashes, apostrophes and whitespaces only";
                            header("Location: search_user.php", true, 303);
                            exit();
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
                            $_SESSION["user_middle_name_error"] = "Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only";
                            header("Location: search_user.php", true, 303);
                            exit();
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
                            $_SESSION["user_last_name_error"] = "Please ensure that your last name has letters, dashes, apostrophes and whitespaces only";
                            header("Location: search_user.php", true, 303);
                            exit();
                        }
                        else {
                            $user_last_name = test_input($_POST['user_last_name']);
                        }
                    }
                    else {
                        $user_last_name = NULL;
                    }
                }
            }
            elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
                if (isset($_SESSION["search_user_error"])) {echo $_SESSION["search_user_error"][0];}
            }
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric"><span class="error"> <?php if (isset($_SESSION["username_error"])) {echo $_SESSION["username_error"];} ?></span><br><br>
            <label for="user_first_name">First Name:</label>
            <input type="text" id="user_first_name" name="user_first_name" placeholder="first name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your first name has letters, dashes, apostrophes and whitespaces only"><span class="error"> <?php if (isset($_SESSION["user_first_name_error"])) {echo $_SESSION["user_first_name_error"];} ?></span><br><br>
            <label for="user_middle_name">Middle Name:</label>
            <input type="text" id="user_middle_name" name="user_middle_name" placeholder="middle name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only"><span class="error"> <?php if (isset($_SESSION["user_middle_name_error"])) {echo $_SESSION["user_middle_name_error"];} ?></span><br><br>
            <label for="user_last_name">Last Name:</label>
            <input type="text" id="user_last_name" name="user_last_name" placeholder="last name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your last name has letters, dashes, apostrophes and whitespaces only"><span class="error"> <?php if (isset($_SESSION["user_last_name_error"])) {echo $_SESSION["user_last_name_error"];} ?></span><br><br>
            <input type="submit" name="search_user_submit" value="Search User">
        </form> 
        <?php                        
            if ($_SERVER['REQUEST_METHOD'] === "POST") {              
                include("database.php");
                $stmt = $DBConnect->prepare("SELECT username, user_first_name, user_middle_name, user_last_name FROM users WHERE username LIKE CONCAT(?, '%') OR user_first_name LIKE CONCAT(?, '%') OR user_middle_name LIKE CONCAT(?, '%') OR user_last_name LIKE CONCAT(?, '%')");
                $stmt->bind_param("ssss", $username, $user_first_name, $user_middle_name, $user_last_name); 
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($retrieved_username, $retrieved_user_first_name, $retrieved_user_middle_name, $retrieved_user_last_name);
                
                if ($stmt->num_rows > 0) {
                    $table_row_count = 1;
                    echo
                    "<br>
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
                            <td><select name=\"action\" id=\"action_$table_row_count\" onclick=\"getUsername('$table_row_count')\" onchange=\"document.cookie='search_user=1'; window.location.replace(this.value);\">
                                    <option value=\"\" selected disabled>Select Action</option>
                                    <option value=\"http://wisepro.com/testing6/view_user.php\">View User</option>
                                    <option value=\"http://wisepro.com/testing6/edit_user.php\">Edit User</option>
                                    <option value=\"http://wisepro.com/testing6/add_employment.php\">Add Employment</option>
                                    <option value=\"http://wisepro.com/testing6/view_employment.php\">View Employment</option>
                                    <option value=\"http://wisepro.com/testing6/choose_employment.php\">Edit Employment</option>
                                    <option value=\"http://wisepro.com/testing6/choose_timesheet.php\">Timesheet</option>
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
                    $search_user_error[] = "<p>User(s) not found.</p>";
                    $_SESSION["search_user_error"] = $search_user_error;
                    header("Location: search_user.php", true, 303);
                    exit();
                }
                $stmt->close();
                $DBConnect->close();
            }
            elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
                if (isset($_SESSION["search_user_error"])) {echo $_SESSION["search_user_error"][1];}
            }
        ?>
    </body>
</html>
<?php
    if (isset($_SESSION["search_user_error"])) {unset($_SESSION["search_user_error"]);}
    if (isset($_SESSION["username_error"])) {unset($_SESSION["username_error"]);}
    if (isset($_SESSION["user_first_name_error"])) {unset($_SESSION["user_first_name_error"]);}
    if (isset($_SESSION["user_middle_name_error"])) {unset($_SESSION["user_middle_name_error"]);}
    if (isset($_SESSION["user_last_name_error"])) {unset($_SESSION["user_last_name_error"]);}
?>