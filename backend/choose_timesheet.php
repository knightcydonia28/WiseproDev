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
    if (!isset($_COOKIE['search_user']) && !isset($_COOKIE['home'])) {
        header('Location: home.php');
        exit();
    }
    setcookie("choose_timesheet", 1);
    $GLOBALS['username'] = isset($_COOKIE['home']) ? $_SESSION['username'] : $_COOKIE['username'];
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
        <meta charset="UTF-8" />
        <title>Choose Timesheet</title>
    </head>
    <body>
        <a href="home.php">Home</a><br /><br />
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>Logout</a>
        <h2>Choose Timesheet</h2>
        <p>Please select a client from which the timesheet should be chosen.</p>
        <?php
            if (isset($_POST['select_client_submit'])) {
                function test_input($data) {
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                }
                if (!is_numeric($_POST['client_id'])) {
                    $client_id_error = "Please select an appropriate client";
                }
                else {
                    $client_id = test_input($_POST['client_id']);
                    echo $client_id;
                    //$_SESSION['choose_timesheet'] = 1;
                    //header('Location: timesheet.php');
                    //exit();
                }
            }
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="client_id">Client:</label>
            <select id="client_id" name="client_id" required>
                <option value="" <?php if (!isset($_POST['select_client_submit'])) {echo "selected";} ?> disabled>Select Client</option>
                <?php
                    include("database.php");
                    $stmt = $DBConnect->prepare("SELECT clients.client_id, clients.client_name FROM ((clients INNER JOIN employments ON clients.client_id = employments.client_id) INNER JOIN users ON employments.username= users.username) WHERE employments.username = ?");
                    $stmt->bind_param("s", $GLOBALS['username']);
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($retrieved_client_id, $retrieved_client_name);
                    if ($stmt->num_rows > 0) {
                        while($stmt->fetch()) {
                            echo "<option value=\"$retrieved_client_id\">$retrieved_client_name</option>";
                        }
                    }
                    else {
                        $_SESSION['disable_choose_timesheet'] = 1;
                    }
                ?>
            </select><br /><br />
            <input type="submit" name="select_client_submit" value="Select Client" <?php if (isset($_SESSION['disable_choose_timesheet'])) {echo "disabled";} ?>/>
        </form>
        <?php
            if (isset($_SESSION['disable_choose_timesheet'])) {
                echo 
                "<p>Employment(s) not found.</p>
                <p>Note: Employment(s) necessary to access and use timesheet.</p>";
            }
        ?>
    </body>
</html>

