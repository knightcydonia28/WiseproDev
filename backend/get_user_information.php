<?php
    include("database.php");
    $stmt = $DBConnect->prepare("SELECT username, password_expiration, user_role, user_first_name, user_middle_name, user_last_name, user_email, user_phone, user_birth_date, user_status, secret_key FROM users WHERE username = ?");
    $stmt->bind_param("s", $_GET['q']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($retrieved_username, $retrieved_password_expiration, $retrieved_user_role, $retrieved_user_first_name, $retrieved_user_middle_name, $retrieved_user_last_name, $retrieved_user_email, $retrieved_user_phone, $retrieved_user_birth_date, $retrieved_user_status, $retrieved_secret_key);
    $stmt->fetch();

    setcookie("username", $retrieved_username);
    setcookie("password_expiration", $retrieved_password_expiration);
    setcookie("user_role", $retrieved_user_role);
    setcookie("user_first_name", $retrieved_user_first_name);
    if ($retrieved_user_middle_name == NULL) {
        setcookie("user_middle_name", 0);
    }
    else {
        setcookie("user_middle_name", $retrieved_user_middle_name);
    }
    setcookie("user_last_name", $retrieved_user_last_name);
    setcookie("user_email", $retrieved_user_email);
    setcookie("user_phone", $retrieved_user_phone);
    setcookie("user_birth_date", $retrieved_user_birth_date);
    setcookie("user_status", $retrieved_user_status);
    if ($retrieved_secret_key == NULL) {
        $retrieved_secret_key = 0;
    }
    else {
        $retrieved_secret_key = 1;
    }
    setcookie("secret_key", $retrieved_secret_key);
    
    $stmt->close();
    $DBConnect->close();
?>