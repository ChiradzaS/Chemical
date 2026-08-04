<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Connection Error</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .error-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            text-align: center;
        }
        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
            color: #dc3545;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #dc3545;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .troubleshooting {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 16px;
            text-align: left;
            margin-bottom: 24px;
        }
        .troubleshooting h2 {
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 12px;
        }
        .troubleshooting ul {
            margin: 0;
            padding-left: 20px;
        }
        .troubleshooting li {
            margin-bottom: 8px;
        }
        .btn {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.2s;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0b5ed7;
        }
        .btn-outline {
            background-color: transparent;
            border: 1px solid #0d6efd;
            color: #0d6efd;
            margin-left: 12px;
        }
        .btn-outline:hover {
            background-color: #f1f8ff;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
            </svg>
        </div>
        <h1>Network Connection Error</h1>
        <p>We're having trouble connecting to our servers. This could be due to a network issue or a temporary server problem.</p>
        
        <div class="troubleshooting">
            <h2>Troubleshooting Steps:</h2>
            <ul>
                <li>Check your internet connection</li>
                <li>Make sure you're not in airplane mode</li>
                <li>Try connecting to a different network</li>
                <li>Restart your router or modem</li>
                <li>Clear your browser cache and cookies</li>
            </ul>
        </div>
        
        <p>If you continue to experience issues, please contact our support team.</p>
        
        <!-- <button class="btn" onclick="window.location.reload();">Try Again</button> -->
        <!-- <a href="/" class="btn btn-outline">Return to Home</a> -->
    </div>

    <script>
        // Auto-retry logic after 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>