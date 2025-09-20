<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: url('../assets/img/magbay.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: inherit;
            background-size: cover; 
            background-position: center;
            filter: blur(8px);
            z-index: -1;
            background-color: rgba(30, 64, 175, 0.25);
        }
        
        .unauthorized-container {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            text-align: center;
            max-width: 450px;
            width: 90%;
            animation: fadeIn 0.5s ease-out;
        }
        
        .logo {
            width: 90px;
            height: 90px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 20px;
            box-shadow: 0 4px 15px rgba(67, 56, 202, 0.15);
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        
        .icon-container {
            margin-bottom: 20px;
        }
        
        .icon-container i {
            font-size: 64px;
            color: #f44336;
            background: rgba(244, 67, 54, 0.1);
            width: 100px;
            height: 100px;
            line-height: 100px;
            border-radius: 50%;
            display: inline-block;
        }
        
        h2 {
            color: #f44336;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            background: #6665ee;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 10px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(102, 101, 238, 0.3);
        }
        
        .btn:hover {
            background: #5757d1;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(102, 101, 238, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .support-text {
            font-size: 14px;
            margin-top: 25px;
            color: #888;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .shake {
            animation: shake 0.6s ease-in-out;
        }
        
        @media (max-width: 480px) {
            .unauthorized-container {
                padding: 30px 20px;
            }
            
            .icon-container i {
                font-size: 54px;
                width: 80px;
                height: 80px;
                line-height: 80px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="unauthorized-container">
        <div class="icon-container">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h2>Unauthorized Access</h2>
        <p>You do not have permission to access this page. Please check your credentials and try again.</p>
        <p>If you believe this is an error, please contact your system administrator.</p>
        <a href="/Capstone/login/login-user.php" class="btn">Return to Login</a>
        <p class="support-text">Need help? Contact support@example.com</p>
    </div>

    <script>
        // Add subtle shake effect to the container for attention
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.unauthorized-container');
            setTimeout(function() {
                container.classList.add('shake');
            }, 500);
        });
    </script>
</body>
</html>