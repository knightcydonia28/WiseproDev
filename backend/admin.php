<!-- Admin home page for admin access only! Admin only pages will be checked for admin rights before allowing access-->
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
/*
?>
<!doctype html>
<html>
    <head></head>
    <body>
        <div class="header">
            <h1>Admin Panel</h1>
            <p> id = <?php echo $_SESSION['id'] ?> </p>
        </div>
        <div class="nav">
            <a href="usermenu.php">User Menu</a>
            <a href="timesheetmenu.php">Timesheet Menu</a>
        </div>
        <form method='post' action="">
            <input type="submit" value="Logout" name="but_logout">
        </form>
    </body>
</html>
*/

?>
<!doctype html>
<html>
<head>
<style>
    .collapsible {
    background-color: #777;
    color: white;
    cursor: pointer;
    padding: 18px;
    width: 100%;
    border: none;
    text-align: left;
    outline: none;
    font-size: 15px;
    }

    .active, .collapsible:hover {
    background-color: #555;
    }

    .collapsible:after {
    content: '\002B';
    color: white;
    font-weight: bold;
    float: right;
    margin-left: 5px;
    }

    .active:after {
    content: "\2212";
    }

    .content {
    padding: 0 18px;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.2s ease-out;
    background-color: #f1f1f1;
    }

    #header{
        text-align: center;

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
        background-color: #111;
        overflow-x: hidden;
        transition: 0.5s;
        padding-top: 60px;
    }
    .sidenav a {
        padding: 8px 8px 8px 32px;
        text-decoration: none;
        font-size: 25px;
        color: #818181;
        display: block;
        transition: 0.3s;
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

</style>
</head>
<body>
    <span id="icon" onclick="openNav()">&#9776;</span>
    <h1 id="header">Admin Panel</h1>
    
    <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closenavbtn" onclick="closesideNav()">&times;</a>
        <a href="usermenu.php">User Menu</a>
        <a href="timesheetmenu.php">Timesheet Menu</a>
        <a href="#">Create New Job</a>
        <a href="#">Timesheet Edit</a>
        <a href="#">Document Repository</a>
    </div>

    <form method='post' action="">
        <input type="submit" value="Logout" name="but_logout">
    </form>

    <div class='menu_area'>
        <button class="collapsible">Search User</button>
            <div class="content">
                <form method='post' action="#">
                    <label for="search_id">ID: </label>
                    <input name="search_id" type="text"></input> <br />
                    <label for="search_first">First Name: </label>
                    <input name="search_first" type="text"></input> <br />
                    <label for="search_last">Last Name: </label>
                    <input name="search_last" type="text"></input> <br />
                    <input type="submit" value="Submit" name="search_submit">
                </form>
            </div>

        <button class="collapsible">Create User</button>
            <div class="content">
                <form method='post' action="#">
                    <label for="create_un">Username: </label>
                    <input name="create_un" type="text" required="required"></input> <br />
                    <label for="create_first">First Name: </label>
                    <input name="create_first" type="text" required="required"></input> <br />
                    <label for="create_last">Last Name: </label>
                    <input name="create_last" type="text" required="required"></input> <br />
                    <input type="submit" value="Submit" name="create_submit">
                </form>
            </div>

        <button class="collapsible">Modify User</button>
            <div class="content">
                <form method='post' action="#">
                    <label for="modify_id">Enter ID to Modify: </label>
                    <input name="modify_id" type="text" required="required"></input> <br />
                    <input type="submit" value="Submit" name="modify_submit">
                </form>
            </div>

        <?php
            function rand_string($length) {
                $chars = "abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ0123456789!?/";
                return substr(str_shuffle($chars),0,$length);
            }
            if (isset($_POST['search_submit'])) {
                if (isset($_POST['search_id'])){
                    $id = $_POST['search_id'];
                } else {$id = null;}
                if (isset($_POST['search_first'])){
                    $first = $_POST['search_first'];
                } else {$first = null;}
                if (isset($_POST['search_last'])){
                    $last = $_POST['search_last'];
                } else {$last = null;}

                $sql = "SELECT `id`, `username`, `name`, `last` FROM `users` WHERE ";
                if ($id != null){$sql .= "id LIKE '".$id."'";}
                if ($id != null && $first != null){$sql .= " or ";}
                if ($first != null){$sql .= "name LIKE '".$first."%'";}
                if ($id != null && $last != null || $first != null && $last != null){$sql .= " or ";}
                if ($last != null){$sql .= "last LIKE '".$last."%'";}
                
                $result = mysqli_query($DBConnect, $sql);
                if (mysqli_num_rows($result)) {
                    echo '<table>
                        <th>ID</th>
                        <th>Username</th>
                        <th>First Name</th>
                        <th>Last Name</th>';
                    while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                <td>".$row['id']."</td>
                                <td>".$row['username']."</td>
                                <td>".$row['name']."</td>
                                <td>".$row['last']."</td></tr>";
                        }
                    echo "</table>";
                } else {
                    echo "No search results found";
                }
            }
            
            if (isset($_POST['create_submit'])) {
                include 'pdo.php';
                $username = $_POST['create_un']; $first = $_POST['create_first']; $last = $_POST['create_last'];
                $un_password = rand_string(8); $password = password_hash($un_password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (id, username, name, last, password, admin) VALUES (null,'".$username."','".$first."','".$last."','".$password."',0)";
                $result = mysqli_query($DBConnect, $sql);
                echo "User ".$username." created! Password = ".$un_password;
            }
            if (isset($_POST['modify_submit'])) {
                $id = $_POST['modify_id'];
                $_SESSION['modify_id'] = $_POST['modify_id'];
                echo '<form method="post" action="#">
                    <label for="modify_id2">ID: </label>
                    <input name="modify_id2" type="text" disabled="disabled" value='.$id.'></input> <br />
                    <label for="modify_uname2">Username: </label>
                    <input name="modify_uname2" type="text"></input> <br />
                    <label for="modify_first2">First Name: </label>
                    <input name="modify_first2" type="text"></input> <br />
                    <label for="modify_last2">Last Name: </label>
                    <input name="modify_last2" type="text"></input> <br />
                    <input type="submit" value="Submit" name="modify_submit2"> <br /> <br />
                </form>
                <form method="post" action "#">
                    <input type="submit" value="Change Password" name="change_pass">
                </form>';
            }
            if (isset($_POST['modify_submit2'])) {
                $id = $_SESSION['modify_id'];
                echo "ID ".$_SESSION["modify_id"]." modified! <br />";
                if ($_POST['modify_uname2'] != null){
                    $sql = "UPDATE `users` SET `username`='".$_POST['modify_uname2']."' WHERE id=".$id;
                    $result = mysqli_query($DBConnect, $sql);
                    echo "Username modified: ".$_POST['modify_uname2']."<br />";
                }
                if ($_POST['modify_first2'] != null){
                    $sql = "UPDATE `users` SET `name`='".$_POST['modify_first2']."' WHERE id=".$id;
                    $result = mysqli_query($DBConnect, $sql);
                    echo "First name modified: ".$_POST['modify_first2']."<br />";
                }
                if ($_POST['modify_last2'] != null){
                    $sql = "UPDATE `users` SET `last`='".$_POST['modify_last2']."' WHERE id=".$id;
                    $result = mysqli_query($DBConnect, $sql);
                    echo "Last name modified: ".$_POST['modify_last2']."<br />";
                }
                unset($_SESSION["modify_id"]);
            }
            if (isset($_POST['change_pass'])) {
                $id = $_SESSION['modify_id']; $new_password = rand_string(8);
                $sql = "UPDATE `users` SET `password`='".$new_password."' WHERE id=".$id;
                $result = mysqli_query($DBConnect, $sql);
                echo "Password changed for User ".$id.": ".$new_password."<br />";
                unset($_SESSION["modify_id"]);
            }
        ?>
        <script>
            var coll = document.getElementsByClassName("collapsible");
            var i;
            for (i = 0; i < coll.length; i++) {
                coll[i].addEventListener("click", function() {
                    this.classList.toggle("active");
                    var content = this.nextElementSibling;
                    if (content.style.maxHeight){
                    content.style.maxHeight = null;
                    } else {
                        content.style.maxHeight = content.scrollHeight + "px";
                    } 
                });
            }
        </script>
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