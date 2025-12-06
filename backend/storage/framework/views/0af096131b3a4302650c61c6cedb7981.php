<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Details Updated</title>
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
        .training-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #44576D;
        }
        .training-details h3 {
            margin-top: 0;
            color: #44576D;
        }
        .schedule-item {
            margin: 15px 0;
            padding: 15px;
            background-color: white;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .schedule-item h4 {
            margin-top: 0;
            color: #44576D;
            border-bottom: 2px solid #44576D;
            padding-bottom: 8px;
        }
        .detail-row {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            min-width: 120px;
        }
        .detail-value {
            color: #333;
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
        .description {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            color: #555;
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
            
            <p>We would like to inform you that the training details for <strong><?php echo e($trainingTitle); ?></strong> by <strong><?php echo e($organizationName); ?></strong> have been updated.</p>
            
            <div class="training-details">
                <h3>Updated Training Information</h3>
                
                <div class="description">
                    <strong>Description:</strong><br>
                    <?php echo e($trainingDescription); ?>

                </div>
                
                <?php if(count($schedules) > 0): ?>
                <h4 style="margin-top: 20px; margin-bottom: 15px;">Schedule(s):</h4>
                
                <?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="schedule-item">
                    <h4>Schedule <?php echo e($loop->iteration); ?></h4>
                    
                    <div class="detail-row">
                        <span class="detail-label">Start Date & Time:</span>
                        <span class="detail-value"><?php echo e($schedule['start_time']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">End Date & Time:</span>
                        <span class="detail-value"><?php echo e($schedule['end_time']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Mode:</span>
                        <span class="detail-value"><?php echo e($schedule['mode']); ?></span>
                    </div>
                    
                    <?php if($schedule['mode'] === 'On-Site' && isset($schedule['location']) && $schedule['location']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Location:</span>
                        <span class="detail-value"><?php echo e($schedule['location']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($schedule['mode'] === 'Online' && isset($schedule['training_link']) && $schedule['training_link']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Meeting Link:</span>
                        <span class="detail-value">
                            <a href="<?php echo e($schedule['training_link']); ?>" class="link" target="_blank"><?php echo e($schedule['training_link']); ?></a>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
            
            <p>Please review the updated details above. If you have any questions or concerns, please contact the organization directly.</p>
            
            <p>Thank you for your continued interest in this training.</p>
            
            <p>Best regards,<br><?php echo e($organizationName); ?></p>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo e(date('Y')); ?> Pathfinder. All rights reserved.</p>
            <p style="margin-top: 10px; color: #999; font-size: 11px;">This is a no-reply email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>

<?php /**PATH C:\PathFinder\Pathfinder-Development\backend\resources\views/emails/training-updated.blade.php ENDPATH**/ ?>