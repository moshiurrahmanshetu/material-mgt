<?php
// Lock file check - prevents re-running installer on already installed site
$lock_file = __DIR__ . '/installed.lock';
$config_file = __DIR__ . '/../config/db.php';

if (file_exists($lock_file)) {
    // Check if config file also exists - if not, allow installer to run (inconsistent state)
    if (!file_exists($config_file)) {
        // Lock exists but config missing - inconsistent state, allow installer to fix it
        return;
    }
    
    // Application is already installed
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Already Installed</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                background: #f5f5f5;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }
            .message {
                background: white;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 500px;
            }
            .message h1 {
                color: #dc3545;
                margin-top: 0;
            }
            .message p {
                color: #666;
                line-height: 1.6;
            }
            .message a {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 4px;
            }
            .message a:hover {
                background: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="message">
            <h1>Already Installed</h1>
            <p>This application is already installed. If you need to reinstall, please delete the <code>/install/installed.lock</code> file first.</p>
            <a href="../index.php">Go to Application</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}
