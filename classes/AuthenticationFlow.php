<?php
class AuthenticationFlow {
    private static $allowedPages = [
        'KEY.php' => true,            
        'signup-user.php' => ['verified_key', 'key_role', 'key_email'],  
        'login-user.php' => true,     
        'forgot-password.php' => true, 
        'reset-code.php' => ['email'], 
        'new-password.php' => ['email'], 
        'user-otp.php' => ['email'],  
        'unauthorized.php' => true,    
        'index.php' => true           
    ];

    public static function checkAccess($currentFile) {
        $pageName = basename($currentFile);
        $dirName  = basename(dirname($currentFile)); // parent folder (e.g., Warden, PG)

        // 1. Allow if page is explicitly whitelisted
        if (isset(self::$allowedPages[$pageName])) {
            // Always accessible
            if (self::$allowedPages[$pageName] === true) {
                return true;
            }

            // Needs session variables
            foreach (self::$allowedPages[$pageName] as $requiredVar) {
                if (!isset($_SESSION[$requiredVar])) {
                    header('Location: ../login/login-user.php');
                    exit();
                }
            }
            return true;
        }

        // 2. Auto-protect directories by role
        if ($dirName === 'Warden') {
            if (!isset($_SESSION['logged_in'])) {
                header('Location: ../../login/login-user.php');
                exit();
            }
            if ($_SESSION['role'] !== 'warden') {
                header('Location: ../../login/unauthorized.php');
                exit();
            }
            return true;
        }

        if ($dirName === 'PG') {
            if (!isset($_SESSION['logged_in'])) {
                header('Location: ../../login/login-user.php');
                exit();
            }
            if ($_SESSION['role'] !== 'prison_guard') {
                header('Location: ../../login/unauthorized.php');
                exit();
            }
            return true;
        }

        // 3. If not matched, deny access
        header('Location: ../login/login-user.php');
        exit();
    }
}
