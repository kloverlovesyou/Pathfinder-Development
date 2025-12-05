<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #e0e0e0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #44576D;
            margin: 0;
        }
        .content {
            background-color: white;
            padding: 25px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #44576D;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #374151;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 20px;
        }
        .link {
            word-break: break-all;
            color: #44576D;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pathfinder</h1>
        </div>
        
        <div class="content">
            <p>Hello <?php echo e($userName); ?>,</p>
            
            <p>We received a request to reset your password for your Pathfinder account. Click the button below to reset your password:</p>
            
            <div style="text-align: center;">
                <a href="<?php echo e($resetUrl); ?>" class="button">Reset Password</a>
            </div>
            
            <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
            <p class="link"><?php echo e($resetUrl); ?></p>
            
            <p>This password reset link will expire in 1 hour.</p>
            
            <p>If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo e(date('Y')); ?> Pathfinder. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

<?php /**PATH C:\PathFinder\Pathfinder-Development\backend\resources\views/emails/password-reset.blade.php ENDPATH**/ ?>