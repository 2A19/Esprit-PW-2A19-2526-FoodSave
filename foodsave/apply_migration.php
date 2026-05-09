<?php
/**
 * Apply Profile Photo Migration
 * Visit this file in browser: http://localhost/foodsave/apply_migration.php
 */

// Security: Only allow from CLI or localhost
$is_cli = php_sapi_name() === 'cli';
$is_localhost = !isset($_SERVER['HTTP_HOST']) || in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:80', 'localhost:8080']);

if (!$is_cli && !$is_localhost) {
    http_response_code(403);
    die('Access denied. This script can only be run locally.');
}

include __DIR__ . '/config/config.php';

$result = [];

try {
    $db = config::getConnexion();
    
    // Check if column already exists
    $check = $db->query("SHOW COLUMNS FROM user LIKE 'profile_photo'");
    $columnExists = $check->rowCount() > 0;
    
    if ($columnExists) {
        $result['status'] = 'success';
        $result['message'] = '✓ profile_photo column already exists in user table.';
        $result['action'] = 'none';
    } else {
        // Add the column
        $db->exec("ALTER TABLE `user` ADD COLUMN `profile_photo` VARCHAR(255) NULL AFTER `date_naissance`");
        $result['status'] = 'success';
        $result['message'] = '✓ Successfully added profile_photo column to user table.';
        $result['action'] = 'applied';
    }
    
} catch (Exception $e) {
    $result['status'] = 'error';
    $result['message'] = '✗ Error: ' . $e->getMessage();
    $result['action'] = 'failed';
}

// Output based on request type
if ($is_cli) {
    echo $result['message'] . "\n";
    exit($result['action'] === 'failed' ? 1 : 0);
} else {
    // HTML response for browser
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>FoodSave - Database Migration</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; }
            .container { background: #f5f5f5; border-radius: 8px; padding: 30px; }
            .success { color: #4CAF50; background: #e8f5e9; border: 1px solid #4CAF50; padding: 15px; border-radius: 6px; margin: 20px 0; }
            .error { color: #f44336; background: #ffebee; border: 1px solid #f44336; padding: 15px; border-radius: 6px; margin: 20px 0; }
            .info { color: #2196F3; background: #e3f2fd; border: 1px solid #2196F3; padding: 15px; border-radius: 6px; margin: 20px 0; }
            h1 { color: #333; }
            .code { background: #fff; padding: 15px; border-radius: 6px; font-family: monospace; overflow-x: auto; margin: 15px 0; border: 1px solid #ddd; }
            button { background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 16px; }
            button:hover { background: #45a049; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🍽️ FoodSave Database Migration</h1>
            
            <?php if ($result['status'] === 'success'): ?>
                <div class="success">
                    <strong>✓ Migration Status: Success</strong>
                    <p><?php echo $result['message']; ?></p>
                    <?php if ($result['action'] === 'applied'): ?>
                        <p><strong>The profile_photo column has been successfully added to the user table.</strong></p>
                        <p>You can now start uploading profile photos!</p>
                    <?php endif; ?>
                </div>
                <div class="info">
                    <strong>Next Steps:</strong>
                    <ol>
                        <li>Go to the user's profile edit page</li>
                        <li>Upload a profile photo</li>
                        <li>The photo will appear across the application</li>
                    </ol>
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>✗ Migration Status: Error</strong>
                    <p><?php echo $result['message']; ?></p>
                </div>
                <div class="info">
                    <strong>Troubleshooting:</strong>
                    <p>If the error persists, try running this SQL command directly in phpMyAdmin:</p>
                    <div class="code">
ALTER TABLE `user` ADD COLUMN `profile_photo` VARCHAR(255) NULL AFTER `date_naissance`;
                    </div>
                </div>
            <?php endif; ?>
            
            <hr style="margin: 30px 0;">
            
            <h2>About This Migration</h2>
            <p>This migration adds support for user profile photos to the FoodSave application.</p>
            <p><strong>What's being added:</strong></p>
            <ul>
                <li>New <code>profile_photo</code> column in the <code>user</code> table</li>
                <li>Stores the filename of the user's uploaded photo</li>
                <li>Allows users to upload JPG, PNG, GIF, or WebP images (max 5MB)</li>
            </ul>
            
            <p style="margin-top: 30px; text-align: center; color: #999; font-size: 12px;">
                <a href="index.php" style="color: #2196F3; text-decoration: none;">← Back to FoodSave</a>
            </p>
        </div>
    </body>
    </html>
    <?php
}
?>
