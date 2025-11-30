<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congratulations! You've Been Hired</title>
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
        .success-banner {
            background-color: #28a745;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .success-banner h2 {
            margin: 0;
            font-size: 24px;
        }
        .position-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .position-details h3 {
            margin-top: 0;
            color: #44576D;
        }
        .detail-item {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-item:last-child {
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
        .custom-message {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff;
            border-left: 3px solid #28a745;
            white-space: pre-wrap;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pathfinder</h1>
        </div>
        
        <div class="content">
            <p>Hello {{ $applicantName }},</p>
            
            <div class="success-banner">
                <h2>🎉 Congratulations! 🎉</h2>
            </div>
            
            <p>We are thrilled to inform you that you have been <strong>hired</strong> for the position of <strong>{{ $positionTitle }}</strong> at <strong>{{ $organizationName }}</strong>!</p>
            
            <div class="position-details">
                <h3>Position Details</h3>
                
                <div class="detail-item">
                    <span class="detail-label">Position:</span>
                    <span class="detail-value">{{ $positionTitle }}</span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Organization:</span>
                    <span class="detail-value">{{ $organizationName }}</span>
                </div>
            </div>
            
            @if($customBody)
            <div class="custom-message">
                {{ $customBody }}
            </div>
            @endif
            
            <p>We are excited to have you join our team! You will be contacted shortly with further details regarding your onboarding process, start date, and other important information.</p>
            
            <p>If you have any questions, please don't hesitate to reach out to us.</p>
            
            <p>Welcome aboard!</p>
            
            <p>Best regards,<br>{{ $organizationName }}</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Pathfinder. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

