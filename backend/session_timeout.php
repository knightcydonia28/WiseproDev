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
            if (isset($_SERVER['HTTP_COOKIE'])) {
                $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
                foreach($cookies as $cookie) {
                    $parts = explode('=', $cookie);
                    $name = trim($parts[0]);
                    setcookie($name, '', time()-1000);
                    setcookie($name, '', time()-1000, '/');
                }
            }
        }
        destroySession();
        echo 
        "<script>
            alert(\"Your session has expired.\");
            window.location.replace(\"http://wisepro.com/testing6/login.php\");
        </script>";
    }
    elseif (time() - $_SESSION['login_time'] < 900) {
        $added_time = time() - $_SESSION['login_time'];
        $_SESSION['login_time'] += $added_time;
    }
?>