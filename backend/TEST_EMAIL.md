# How to Test Email Configuration

## Option 1: Browser Console (Easiest)

1. Open your browser's Developer Console:
   - Press `F12` or `Right-click → Inspect`
   - Go to the "Console" tab

2. Paste and run this code (replace with your actual API URL and email):

```javascript
fetch('http://127.0.0.1:8000/api/test-email', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'your-email@gmail.com'
  })
})
.then(response => response.json())
.then(data => {
  console.log('Response:', data);
  if (data.status === 'success') {
    alert('Email sent successfully! Check your inbox.');
  } else {
    alert('Error: ' + data.message);
  }
})
.catch(error => {
  console.error('Error:', error);
  alert('Failed to send test email: ' + error.message);
});
```

**Important:** Replace `http://127.0.0.1:8000` with your actual backend URL (check your `.env` file for `VITE_API_BASE_URL` or use the URL you use in your frontend).

## Option 2: Using curl (Terminal/Command Prompt)

Open your terminal/command prompt and run:

```bash
curl -X POST http://127.0.0.1:8000/api/test-email \
  -H "Content-Type: application/json" \
  -d '{"email":"your-email@gmail.com"}'
```

Replace `http://127.0.0.1:8000` with your backend URL.

## Option 3: Using Postman

1. Download and install Postman: https://www.postman.com/downloads/
2. Create a new request:
   - Method: `POST`
   - URL: `http://127.0.0.1:8000/api/test-email`
   - Headers: Add `Content-Type: application/json`
   - Body: Select "raw" and "JSON", then paste:
     ```json
     {
       "email": "your-email@gmail.com"
     }
     ```
3. Click "Send"

## Option 4: Create a Simple Test Page

Create a file `test-email.html` in your backend `public` folder:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Test Email</title>
</head>
<body>
    <h1>Test Email Configuration</h1>
    <input type="email" id="email" placeholder="Enter your email" />
    <button onclick="testEmail()">Send Test Email</button>
    <div id="result"></div>

    <script>
        async function testEmail() {
            const email = document.getElementById('email').value;
            const resultDiv = document.getElementById('result');
            
            resultDiv.innerHTML = 'Sending...';
            
            try {
                const response = await fetch('/api/test-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email: email })
                });
                
                const data = await response.json();
                resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } catch (error) {
                resultDiv.innerHTML = 'Error: ' + error.message;
            }
        }
    </script>
</body>
</html>
```

Then visit: `http://127.0.0.1:8000/test-email.html`

## What to Look For

### Success Response:
```json
{
  "status": "success",
  "message": "Test email sent successfully!",
  "mail_config": {
    "driver": "smtp",
    "host": "smtp.gmail.com",
    "port": "587",
    "from_address": "judeandrei.f.pasicolan@gmail.com",
    "from_name": "Pathfinder"
  }
}
```

### Error Response:
```json
{
  "status": "error",
  "message": "Failed to send test email",
  "error": "Connection timeout",
  "mail_config": { ... },
  "suggestion": "For testing, set MAIL_MAILER=log in your .env file..."
}
```

## Quick Test After Registration

After registering a new user, check the registration response. It now includes:
- `email_sent`: `true` or `false`
- `email_error`: Error message if sending failed
- `verification_url`: The verification link (if email failed, you can use this manually)

