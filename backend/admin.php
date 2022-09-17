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