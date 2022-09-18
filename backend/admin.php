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
    <p id="body">Welcome to Admin Dashboard</p>
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
