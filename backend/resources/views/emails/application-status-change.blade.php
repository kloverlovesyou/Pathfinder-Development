<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status Update</title>
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
        .status-banner {
            background-color: #44576D;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .status-banner h2 {
            margin: 0;
            font-size: 24px;
            text-transform: capitalize;
        }
        .position-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #44576D;
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
            border-left: 3px solid #44576D;
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
            
            <p>We would like to inform you that your application status for the position of <strong>{{ $positionTitle }}</strong> at <strong>{{ $organizationName }}</strong> has been updated.</p>
            
            <div class="status-banner">
                <h2>Status: {{ $status }}</h2>
            </div>
            
            <div class="position-details">
                <h3>Application Details</h3>
                
                <div class="detail-item">
                    <span class="detail-label">Position:</span>
                    <span class="detail-value">{{ $positionTitle }}</span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Organization:</span>
                    <span class="detail-value">{{ $organizationName }}</span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">New Status:</span>
                    <span class="detail-value">{{ $status }}</span>
                </div>
            </div>
            
            @if($customBody)
            <div class="custom-message">
                {{ $customBody }}
            </div>
            @endif
            
            <p>Please log in to your Pathfinder account to view more details about your application.</p>
            
            <p>If you have any questions, please don't hesitate to reach out to us.</p>
            
            <p>Best regards,<br>{{ $organizationName }}</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Pathfinder. All rights reserved.</p>
            <p style="margin-top: 10px; color: #999; font-size: 11px;">This is a no-reply email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>

