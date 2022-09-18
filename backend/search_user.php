<?php
    session_start();
    if (!isset($_SESSION['login_status'])) {
        header('Location: login.php');
    }
    if ($_SESSION['user_role'] != "administrator") {
        header('Location: home.php');
    }
    if ($_SESSION['password_expiration'] == 0) {
        header('Location: change_password.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Search User</title>
        <script>
            function enableButton() {
                document.getElementById("select_user_submit").disabled = false;
            }
            function createCookie(row_array) {
                var username = row_array[0];
                var password_expiration = row_array[1];
                var user_role = row_array[2];
                var first_name = row_array[3];
                var middle_name = row_array[4];
                var last_name = row_array[5];
                var email = row_array[6];
                var phone = row_array[7];
                var birth_date = row_array[8];
                document.cookie = "username="+username+"";
                document.cookie = "password_expiration="+password_expiration+"";
                document.cookie = "user_role="+user_role+"";
                document.cookie = "first_name="+first_name+"";
                document.cookie = "middle_name="+middle_name+"";
                document.cookie = "last_name="+last_name+"";
                document.cookie = "email="+email+"";
                document.cookie = "phone="+phone+"";
                document.cookie = "birth_date="+birth_date+"";
            }
            function getRowValues(row_id) {
                var tr = document.getElementById(row_id);
                var td = tr.getElementsByTagName("td");
                const row_values = [];
                for (var i = 0; i<td.length; i++) {
                    var element = (td[i].innerHTML);
                    row_values.push(element);
                }
                return row_values;
            }
        </script>
        <style>
            table, th, td {
                border:1px solid black;
            }
        </style>
    </head>
    <body>
        <a href="home.php">Home</a><br /><br />
        <form method="post" action="#">
            <input type="submit" name="logout" value="Logout" />
        </form>
        <?php
            unset($_SESSION['search_user']);
            unset($_SESSION['edit_user_authentication']);
            if (isset($_POST['logout'])) {
                include("logout.php");
                logout();
            }
        ?>
        <h2>Search User</h2>
        <p>Please fill in one or all of the following:</p>
        <form method="post" action="#">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="username" pattern="[a-zA-Z0-9]+" title="Please ensure that your username is alphanumeric" /><br /><br />
            <label for="first_name">First name:</label>
            <input type="text" id="first_name" name="first_name" placeholder="first name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your first name has letters, dashes, apostrophes and whitespaces only" /><br /><br />
            <label for="middle_name">Middle name:</label>
            <input type="text" id="middle_name" name="middle_name" placeholder="middle name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only" /><br /><br />
            <label for="last_name">Last name:</label>
            <input type="text" id="last_name" name="last_name" placeholder="last name" pattern="[a-zA-Z-'\s]*$" title="Please ensure that your last name has letters, dashes, apostrophes and whitespaces only" /><br /><br />
            <input type="submit" name="search_user_submit" value="Search User" />
        </form> 
        <?php
            if (isset($_POST['select_user_submit'])) {
                $_SESSION['search_user'] = 1;
                header("Location: edit_user.php");
            }
            if (isset($_POST['search_user_submit'])) {
                if (empty($_POST['username']) && empty($_POST['first_name']) && empty($_POST['middle_name']) && empty($_POST['last_name'])) {
                    echo "<p>Please fill in at least one of the input fields.</p>";
                }
                else {
                    if (!empty($_POST['username'])) {
                        if (!ctype_alnum($_POST['username'])) {
                            echo "<p>Please ensure that your username is alphanumeric.</p>";
                        }
                        else {
                            $username = $_POST['username'];
                        }    
                    }
                    else {
                        $username = NULL;
                    }
                    if (!empty($_POST['first_name'])) {
                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['first_name'])) {
                            echo "<p>Please ensure that your first name has letters, dashes, apostrophes and whitespaces only.</p>";
                        }
                        else {
                            $first_name = $_POST['first_name'];
                        }      
                    }
                    else {
                        $first_name = NULL;
                    }
                    if (!empty($_POST['middle_name'])) {
                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['middle_name'])) {
                            echo "<p>Please ensure that your middle name has letters, dashes, apostrophes and whitespaces only.</p>";
                        }
                        else {
                            $middle_name = $_POST['middle_name'];
                        }
                    }
                    else {
                        $middle_name = NULL;
                    }
                    if (!empty($_POST['last_name'])) {
                        if (!preg_match("/^[a-zA-Z-' ]*$/",$_POST['last_name'])) {
                            echo "<p>Please ensure that your last name has letters, dashes, apostrophes and whitespaces only.</p>";
                        }
                        else {
                            $last_name = $_POST['last_name'];
                        }
                    }
                    else {
                        $last_name = NULL;
                    }
                }
                
                include("database.php");
                $stmt = $DBConnect->prepare("SELECT username, password_expiration, user_role, user_first_name, user_middle_name, user_last_name, user_email, user_phone, user_birth_date FROM users WHERE username LIKE CONCAT(?, '%') OR user_first_name LIKE CONCAT(?, '%') OR user_middle_name LIKE CONCAT(?, '%') OR user_last_name LIKE CONCAT(?, '%')");
                $stmt->bind_param("ssss", $username, $first_name, $middle_name, $last_name); 
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($retrieved_username, $retrieved_password_expiration, $retrieved_user_role, $retrieved_first_name, $retrieved_middle_name, $retrieved_last_name, $retrieved_email, $retrieved_phone_number, $retrieved_birth_date);
                
                if ($stmt->num_rows > 0) {
                    $table_row_count = 1;
                    echo
                    "<br />
                    <table>
                        <tr>
                            <th>Select</th>
                            <th>Username</th>
                            <th>Password expiration</th>
                            <th>User role</th>
                            <th>First name</th>
                            <th>Middle name</th>
                            <th>Last name</th>
                            <th>Email</th>
                            <th>Phone number</th>
                            <th>Birth date</th>
                        </tr>";
                    while($stmt->fetch()) {
                        echo
                        "<tr id=\"$table_row_count\">
                            <th><input type=\"radio\" name=\"select_user\" onclick=\"createCookie(getRowValues('$table_row_count')); enableButton();\" /></th>
                            <td>$retrieved_username</td>
                            <td>$retrieved_password_expiration</td>
                            <td>$retrieved_user_role</td>
                            <td>$retrieved_first_name</td>
                            <td>$retrieved_middle_name</td>
                            <td>$retrieved_last_name</td>
                            <td>$retrieved_email</td>
                            <td>$retrieved_phone_number</td>
                            <td>$retrieved_birth_date</td>
                        </tr>";
                        $table_row_count++;
                    }
                    echo "</table>
                    <br />
                    <form method=\"post\" action=\"#\">
                        <input type=\"submit\" id=\"select_user_submit\" name=\"select_user_submit\" value=\"Edit User Information\" disabled />
                    </form>";
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

