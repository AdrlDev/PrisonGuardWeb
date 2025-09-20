<?php 
// Skip session_start() if a session is already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../database/connection.php";
$email = "";
$name = "";
$errors = array();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//if user signup button
if(isset($_POST['signup'])){
    // Check if the form fields are set, if not use empty string
    $first_name = isset($_POST['firstName']) ? mysqli_real_escape_string($con_admin, $_POST['firstName']) : '';
    $last_name = isset($_POST['lastName']) ? mysqli_real_escape_string($con_admin, $_POST['lastName']) : '';
    $middle_name = isset($_POST['middleName']) ? mysqli_real_escape_string($con_admin, $_POST['middleName']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($con_admin, $_POST['email']) : $_SESSION['key_email']; // Use email from session if not in POST
    $password = mysqli_real_escape_string($con_admin, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con_admin, $_POST['cpassword']);
    if($password !== $cpassword){
        $errors['password'] = "Confirm password not matched!";
    }

    $email_check = "SELECT * FROM users WHERE email = '$email'";
    $res = mysqli_query($con_admin, $email_check);
    if(mysqli_num_rows($res) > 0){
        $errors['email'] = "Email that you have entered already exists!";
    }
 
    if(count($errors) === 0){
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $code = rand(111111, 999999); // Corrected range for 6-digit code
        $gender = isset($_POST['gender']) ? mysqli_real_escape_string($con_admin, $_POST['gender']) : '';
        $phone_number = isset($_POST['phoneNumber']) ? mysqli_real_escape_string($con_admin, $_POST['phoneNumber']) : '';
        $birthday = isset($_POST['birthday']) ? mysqli_real_escape_string($con_admin, $_POST['birthday']) : '';
        $age = isset($_POST['age']) ? mysqli_real_escape_string($con_admin, $_POST['age']) : '';
        
        // Get role from session
        $role_type = isset($_SESSION['key_role']) ? $_SESSION['key_role'] : '';
        error_log("Role type from session: " . $role_type); // Debug log
        
        // Convert role type to database format
        if ($role_type === 'prison_guard') {
            $role = 'prison_guard';
        } elseif ($role_type === 'warden') {
            $role = 'warden';
        } else {
            error_log("Invalid role type: " . $role_type);
            $errors['role'] = "Invalid role type";
            return;
        }
        
        error_log("Final role for database: " . $role); // Debug log
        
        // First insert the user
        $insert_data = "INSERT INTO users (first_name, last_name, middle_name, email, password, role, gender, phone_number, birthday, age, signup_key) 
                        VALUES ('$first_name', '$last_name', '$middle_name', '$email', '$encpass', '$role', '$gender', '$phone_number', '$birthday', '$age', '{$_SESSION['verified_key']}')";
        error_log("SQL Insert Query: " . $insert_data); // Debug log
        $data_check = mysqli_query($con_admin, $insert_data);
        
        if($data_check){
            // Update the registration key as used
            $update_key = "UPDATE registration_keys SET usage_used = usage_used + 1 WHERE key_code = '{$_SESSION['verified_key']}'";
            mysqli_query($con_admin, $update_key);

            $subject = "Account Created Successfully";
            $message = "Your account has been created successfully. You can now log in to the system.";
            
            $_SESSION['registration_success'] = true;
            // Create a more detailed success message based on the role
            if ($role === 'prison_guard') {
                $_SESSION['success_message'] = "Prison Guard account created successfully! You can now log in.";
            } else {
                $_SESSION['success_message'] = "Warden account created successfully! You can now log in to manage the system.";
            }
            
            // Set status for bootstrap alert styling
            $_SESSION['alert_type'] = 'success';
            
            // Update the registration key status
            $update_key = "UPDATE registration_keys SET usage_used = usage_used + 1 WHERE key_code = '{$_SESSION['verified_key']}'";
            mysqli_query($con_admin, $update_key);
            
            // Clear sensitive session data
            unset($_SESSION['verified_key'], $_SESSION['key_role'], $_SESSION['key_email']);
            
            // Redirect to login page with success message
            header('Location: ../login/login-user.php');
            exit();
        }else{
            $errors['db-error'] = "Failed while inserting data into database!";
        }
    }

}
    //if user click verification code submit button
    if(isset($_POST['check'])){
        $_SESSION['info'] = "";
        $otp_code = mysqli_real_escape_string($con_admin, $_POST['otp']);
        $check_code = "SELECT * FROM users WHERE email = '{$_SESSION['email']}'";
        $code_res = mysqli_query($con_admin, $check_code);
        if(mysqli_num_rows($code_res) > 0){
            $fetch_data = mysqli_fetch_assoc($code_res);
            $email = $fetch_data['email'];
            $_SESSION['email'] = $email;
            $_SESSION['first_name'] = $fetch_data['first_name'];
            $_SESSION['last_name'] = $fetch_data['last_name'];
            $_SESSION['role'] = $fetch_data['role'];

            // Redirect based on user role
            if($fetch_data['role'] == 'warden'){
                header('location: ../modules/Warden/WARDEN_DASHBOARD.php');
            } else if($fetch_data['role'] == 'prison_guard'){
                header('location: ../modules/PG/PG_DASHBOARD.php');
            } else {
                $errors['email'] = "Invalid user role!";
            }
            exit();
        }else{
            $errors['otp-error'] = "You've entered incorrect code!";
        }
    }

    //if user click login button
    if(isset($_POST['login'])){
        $email = mysqli_real_escape_string($con_admin, $_POST['email']);
        $password = mysqli_real_escape_string($con_admin, $_POST['password']);
        
        // Debug log
        error_log("Login attempt for email: " . $email);
        
        $check_email = "SELECT * FROM users WHERE email = ?";
        $stmt = $con_admin->prepare($check_email);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if($res && mysqli_num_rows($res) > 0){
            $fetch = mysqli_fetch_assoc($res);
            $fetch_pass = $fetch['password'];
            
            if(password_verify($password, $fetch_pass)){
                // Set all session variables
                $_SESSION['email'] = $email;
                $_SESSION['first_name'] = $fetch['first_name'];
                $_SESSION['last_name'] = $fetch['last_name'];
                $_SESSION['role'] = $fetch['role'];
                $_SESSION['id'] = $fetch['id'];
                $_SESSION['logged_in'] = true;

                // Debug log
                error_log("User role: " . $fetch['role']);
                
                // Clean output buffer before redirect
                if (ob_get_length()) ob_clean();
                
                // Redirect based on user role
                if($fetch['role'] == 'warden'){
                    error_log("Redirecting to warden dashboard");
                    header('Location: ../modules/Warden/WARDEN_DASHBOARD.php');
                    exit();
                } else if($fetch['role'] == 'prison_guard'){
                    error_log("Redirecting to prison guard dashboard");
                    header('Location: ../modules/PG/PG_DASHBOARD.php');
                    exit();
                } else {
                    $errors['email'] = "Invalid user role!";
                }
            } else {
                error_log("Password verification failed for user: " . $email);
                $errors['login'] = "Invalid email or password.";
            }
        } else {
            error_log("No user found with email: " . $email);
            $errors['login'] = "No account found with this email address.";
        }
        
        if (!empty($errors)) {
            $_SESSION['login_errors'] = $errors;
            header('Location: login-user.php');
            exit();
        }
    }

    //if user click continue button in forgot password form
    if(isset($_POST['check-email'])){
    $email = mysqli_real_escape_string($con_admin, $_POST['email']);
    $check_email = "SELECT * FROM users WHERE email='$email'";
    $run_sql = mysqli_query($con_admin, $check_email);
    if(mysqli_num_rows($run_sql) > 0){
        $code = rand(111111, 999999); // Corrected range for 6-digit code
        $insert_code = "UPDATE users SET code = $code WHERE email = '$email'";
        $run_query =  mysqli_query($con, $insert_code);
        if($run_query){
            $subject = "Password Reset Code";
            $message = "Your password reset code is $code";
            $sender = "From: catherinemaemauricio.bsit@gmail.com";
            // Load Composer's autoloader
            require_once dirname(__DIR__) . '/vendor/autoload.php';
            // Instantiation and passing `true` enables exceptions
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'catherinemaemauricio.bsit@gmail.com';
                $mail->Password   = 'togd lnqm uyvz trwc';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                //Recipients
                $mail->setFrom('your_gmail@gmail.com', 'Your Name');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $message;

                $mail->send();
                $info = "We've sent a password reset otp to your email - $email";
                $_SESSION['info'] = $info;
                $_SESSION['email'] = $email;
                header('location: reset-code.php');
                exit();
            } catch (Exception $e) {
                $errors['otp-error'] = "Failed while sending code! Mailer Error: {$mail->ErrorInfo}";
            }
        }else{
            $errors['db-error'] = "Something went wrong!";
        }
    }else{
        $errors['email'] = "This email address does not exist!";
    }
}

    //if user click check reset otp button
    if(isset($_POST['check-reset-otp'])){
        $_SESSION['info'] = "";
        $otp_code = mysqli_real_escape_string($con_admin, $_POST['otp']);
        $check_code = "SELECT * FROM users WHERE code = $otp_code";
        $code_res = mysqli_query($con_admin, $check_code);
        if(mysqli_num_rows($code_res) > 0){
            $fetch_data = mysqli_fetch_assoc($code_res);
            $email = $fetch_data['email'];
            $_SESSION['email'] = $email;
            $info = "Please create a new password that you don't use on any other site.";
            $_SESSION['info'] = $info;
            header('location: new-password.php');
            exit();
        }else{
            $errors['otp-error'] = "You've entered incorrect code!";
        }
    }

    //if user click change password button
    if(isset($_POST['change-password'])){
        $_SESSION['info'] = "";
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
        if($password !== $cpassword){
            $errors['password'] = "Confirm password not matched!";
        }else{
            $code = 0;
            $email = $_SESSION['email']; //getting this email using session
            $encpass = password_hash($password, PASSWORD_BCRYPT);
            $update_pass = "UPDATE users SET code = $code, password = '$encpass' WHERE email = '$email'";
            $run_query = mysqli_query($con_admin, $update_pass);
            if($run_query){
                $info = "Your password changed. Now you can login with your new password.";
                $_SESSION['info'] = $info;
                header('Location: password-changed.php');
            }else{
                $errors['db-error'] = "Failed to change your password!";
            }
        }
    }
    
   //if login now button click
    if(isset($_POST['login-now'])){
        header('Location: login-user.php');
    }
?>