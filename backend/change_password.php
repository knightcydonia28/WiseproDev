<?php
    session_start();
    if (!isset($_SESSION['login_status'])) {
        header('Location: login.php');
    }
    if ($_SESSION['password_expiration'] == 1) {
        header('Location: home.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Change Password</title>
        <script>
            function passwordVisibility(input_id) {
              var x = document.getElementById(input_id);
              if (x.type === "password") {
                x.type = "text";
              } else {
                x.type = "password";
              }
            }
        </script>
    </head>
    <body>
        <form method="post" action="#">
            <input type="submit" name="logout" value="Logout" />
        </form>
        <h3>Change Password</h3>
        <p>Password Complexity Requirements:</p>
        <ul>
            <li>Password must have at least 1 uppercase character (A-Z)</li>
            <li>Password must have at least 1 lowercase character (a-z)</li>
            <li>Password must have at least 1 digit (0-9)</li>
            <li>Password must have at least 1 special character (between double quotes [includes whitespace]): " !\"#$%&'()*+,-./:;&lt;=&gt;?@[\]^_`{|}~"</li>
            <li>Password must be at least 10 characters</li>
            <li>Password must be at most 128 characters</li>
            <li>Password must not have more than 2 identical characters in a row (e.g., aaa is not allowed)</li>
        </ul>
        <?php
            unset($_SESSION['search_user']);
            unset($_SESSION['edit_user_authentication']);  
            if (isset($_POST['logout'])) {
                include("logout.php");
                logout();
            }
            if(isset($_GET['change_password_expiration'])){
                function changePasswordExpiration() {
                    unset($_SESSION['disabled']);
                    $_SESSION['password_expiration'] = 1;
                    header('Location: home.php');
                }
                changePasswordExpiration();
            }
            if (isset($_POST['change_password_submit'])) {
                $old_password = $_POST['old_password'];
                $new_password = $_POST['new_password'];
                $repeated_new_password = $_POST['repeated_new_password'];
                $username = $_SESSION['username'];
                include("database.php");
                $stmt = $DBConnect->prepare("SELECT password FROM users WHERE username = ?");
                $stmt->bind_param("s", $username); 
                $stmt->execute();
                $stmt->bind_result($retrieved_password);
                $stmt->fetch();
                if (password_verify($old_password, $retrieved_password)) {
                    if (strcmp($old_password, $new_password) == 0) {
                        echo "<p>Please ensure that your new password is different from your old password.</p>";
                    }
                    else {
                        if (strcmp($new_password, $repeated_new_password) !== 0) {
                            echo "<p>Both of the new passwords do not match. Please type both new passwords again.</p>";
                        }
                        else {
                            if (strlen($new_password) >= 10) {
                                if (strlen($new_password) <= 128) {
                                    if(preg_match('/[A-Z]/', $new_password)) {
                                        if(preg_match('/[a-z]/', $new_password)) {
                                            if(preg_match('/[0-9]/', $new_password)) {
                                                if(preg_match('/\W/', $new_password)) {
                                                    if(preg_match('/(.)\\1{2}/', $new_password)) {
                                                        echo "<p>Password must not have more than 2 identical characters in a row (e.g., aaa is not allowed)</p>";
                                                    }
                                                    else {
                                                        include("database.php");
                                                        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                                                        $password_expiration = 1;
                                                        $stmt = $DBConnect->prepare("UPDATE users SET password_expiration = ?, password = ? WHERE username = ?");
                                                        $stmt->bind_param("iss", $password_expiration, $hashed_new_password, $username);
                                                        if ($stmt->execute()) {
                                                            $_SESSION['disabled'] = 1;
                                                            echo "<p>Password was successfully changed. Please <a href=\"?change_password_expiration=true\">Click Here</a> to return to the home page or logout to exit this page.</p>";
                                                        }
                                                        else {
                                                            echo "<p>Password was not successfully changed.</p>";
                                                        }
                                                    }
                                                }
                                                else {
                                                    echo "<p>Password must have at least 1 special character (between double quotes [includes whitespace]): \" !\"#$%&'()*+,-./:;<=>?@[\]^_`{|}~\"</p>";
                                                }
                                            }
                                            else {
                                                echo "<p>Password must have at least 1 digit (0-9)</p>";
                                            }
                                        }
                                        else {
                                            echo "<p>Password must have at least 1 lowercase character (a-z)</p>";
                                        }
                                    }
                                    else {
                                        echo "<p>Password must have at least 1 uppercase character (A-Z)</p>";
                                    }
                                }
                                else {
                                    echo "<p>Password must be at most 128 characters</p>";
                                }
                            }
                            else {
                                echo "<p>Password must be at least 10 characters</p>";
                            }
                        }
                    }
                }
                else {
                    echo "<p>Your old password is invalid.</p>";
                }
                $stmt->close();
                $DBConnect->close();
            } 
        ?>
        <form method="post" action="#">
            <label for="old_password">Enter your old password:</label><br /><br />
            <input type="password" id="old_password" name="old_password" placeholder="old password" required <?php if (isset($_SESSION['disabled'])) {echo "disabled";} ?>/>
            <input type="checkbox" id="checkbox1" onclick="passwordVisibility('old_password')" <?php if (isset($_SESSION['disabled'])) {echo "disabled";} ?>/>Show Password <br /><br />
            <label for="new_password">Enter your new password:</label><br /><br />
            <input type="password" id="new_password" name="new_password" placeholder="new password" required <?php if (isset($_SESSION['disabled'])) {echo "disabled";} ?>/>
            <input type="checkbox" id="checkbox2" onclick="passwordVisibility('new_password')" <?php if (isset($_SESSION['disabled'])) {echo "disabled";} ?>/>Show Password <br /><br />
            <label for="repeated_new_password">Enter your new password again:</label><br /><br />
            <input type="password" id="repeated_new_password" name="repeated_new_password" placeholder="new password" required <?php if (isset($_SESSION['disabled'])) {echo "disabled";} ?>/>
            <input type="checkbox" id="checkbox3" onclick="passwordVisibility('repeated_new_password')" <?php if (isset($_SESSION['disabled'])) {echo "disabled";} ?>/>Show Password <br /><br />
            <input type="submit" id="change_password_submit" name="change_password_submit" value="Change Password" <?php if (isset($_SESSION['disabled'])) {echo "disabled";} ?>/>
        </form>
    </body>
</html>