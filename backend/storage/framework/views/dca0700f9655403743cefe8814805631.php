<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Schedule Notification</title>
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
        .schedule-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #44576D;
        }
        .schedule-details h3 {
            margin-top: 0;
            color: #44576D;
        }
        .schedule-item {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .schedule-item:last-child {
            border-bottom: none;
        }
        .schedule-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            min-width: 120px;
        }
        .schedule-value {
            color: #333;
        }
        .custom-message {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff;
            border-left: 3px solid #44576D;
            white-space: pre-wrap;
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
            text-decoration: none;
        }
        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pathfinder</h1>
        </div>
        
        <div class="content">
            <p>Hello <?php echo e($applicantName); ?>,</p>
            
            <p>We are pleased to inform you that you have been selected for an interview for the position of <strong><?php echo e($positionTitle); ?></strong> at <strong><?php echo e($organizationName); ?></strong>.</p>
            
            <div class="schedule-details">
                <h3>Interview Details</h3>
                
                <div class="schedule-item">
                    <span class="schedule-label">Date & Time:</span>
                    <span class="schedule-value"><?php echo e($interviewDate); ?></span>
                </div>
                
                <div class="schedule-item">
                    <span class="schedule-label">Mode:</span>
                    <span class="schedule-value"><?php echo e($interviewMode); ?></span>
                </div>
                
                <?php if($interviewMode === 'On-Site' && $interviewLocation): ?>
                <div class="schedule-item">
                    <span class="schedule-label">Location:</span>
                    <span class="schedule-value"><?php echo e($interviewLocation); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if($interviewMode === 'Online' && $interviewLink): ?>
                <div class="schedule-item">
                    <span class="schedule-label">Meeting Link:</span>
                    <span class="schedule-value">
                        <a href="<?php echo e($interviewLink); ?>" class="link" target="_blank"><?php echo e($interviewLink); ?></a>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if($customBody): ?>
            <div class="custom-message">
                <?php echo e($customBody); ?>

            </div>
            <?php endif; ?>
            
            <p>Please confirm your attendance and let us know if you have any questions or need to reschedule.</p>
            
            <p>We look forward to meeting you!</p>
            
            <p>Best regards,<br><?php echo e($organizationName); ?></p>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo e(date('Y')); ?> Pathfinder. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

<?php /**PATH C:\PathFinder\Pathfinder-Development\backend\resources\views/emails/interview-schedule.blade.php ENDPATH**/ ?>