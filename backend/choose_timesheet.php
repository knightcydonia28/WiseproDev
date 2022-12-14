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
    $GLOBALS['username'] = isset($_COOKIE['home']) ? $_SESSION['username'] : $_COOKIE['username'];
    setcookie("choose_timesheet", 1);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            include("session_timeout.php");
        ?>
        <meta charset="UTF-8">
        <title>Choose Timesheet</title>
        <style>
            .error {
                color: #FF0000;
            }
            a {
                text-decoration:none;
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
            input[type=submit] {
                background-color: #10469A;
                color: white;
                padding-top: 8px;
                padding-bottom: 8px;
                padding-right: 8px;
                padding-left: 8px;
                border-radius: 20px;
                cursor: pointer;
                border:solid;
                width: 100px;
                margin-bottom: 7px;
            }
        </style>
    </head>
    <body>
        <a href="home.php">
            <button class="home-button">Home</button>
        </a>         
        <?php
            include("logout.php");
        ?>
        <a href='?logout=true'>
            <button class="logout-button">Logout</button>
        </a>        
        <h2>Choose Timesheet</h2>
        <p>Please select a client from which the timesheet should be chosen.</p>
        <?php
            if (isset($_POST['select_client_submit'])) {
                function testInput($data) {
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                }

                function validateClientId($provided_client_id) {
                    $provided_client_id = testInput($provided_client_id);
                    if (!is_numeric($provided_client_id)) {
                        $_SESSION["client_id_error"] = "<p class=\"error\">Please select an appropriate client</p>";
                        header("Location: choose_employment_procedural.php", true, 303);
                        exit();
                    }
                    else {
                        setcookie("client_id", $provided_client_id);
                        header('Location: timesheet.php');
                        exit();
                    }
                }

                validateClientId($_POST['client_id']);
            }
            elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
                if (isset($_SESSION["client_id_error"])) {echo $_SESSION["client_id_error"];}
            }

            include("database.php");
            $stmt = $DBConnect->prepare("SELECT COUNT(client_id) AS employment_number FROM employments WHERE username = ?");
            $stmt->bind_param("s", $GLOBALS['username']);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($retrieved_employment_number);
            $stmt->fetch();
            $stmt->close();
            $DBConnect->close();

            if ($retrieved_employment_number == 0) {
                echo 
                "<p class=\"error\">This user is not employed with any clients.</p>
                <p class=\"error\">Note: A user must be employed to have timesheets.</p>";
            }
            elseif ($retrieved_employment_number == 1) {
                include("database.php");
                $stmt = $DBConnect->prepare("SELECT client_id FROM employments WHERE username = ?");
                $stmt->bind_param("s", $GLOBALS['username']);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($retrieved_client_id);
                $stmt->fetch();
                $stmt->close();
                $DBConnect->close();
        
                setcookie("client_id", $retrieved_client_id);
                header('Location: timesheet.php');
                exit();
            }
            else {
                echo
                "<form method=\"post\" action="; echo htmlspecialchars($_SERVER["PHP_SELF"]); echo ">
                    <label for=\"client_id\">Client:</label>
                    <select id=\"client_id\" name=\"client_id\" required>
                        <option value=\"\" "; if (!isset($_POST['select_client_submit'])) {echo "selected";} echo " disabled>Select Client</option>";
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
                    echo "</select><br><br>
                    <input type=\"submit\" name=\"select_client_submit\" value=\"Select Client\">
                </form>";
                $stmt->close();
                $DBConnect->close();
            }
        ?>
    </body>
</html>
<?php
    if (isset($_SESSION["client_id_error"])) {unset($_SESSION["client_id_error"]);}
?>
