<?php
session_start();
header('Content-Type: application/json');

$lastCheck = isset($_GET['last']) ? (int)$_GET['last'] : 0;

$newNotifications = [];
if(isset($_SESSION['notifications']) && !empty($_SESSION['notifications'])) {
    foreach($_SESSION['notifications'] as $key => $notif) {
        $timestamp = strtotime($notif['date']);
        if($timestamp > $lastCheck && (!isset($notif['lu']) || !$notif['lu'])) {
            $newNotifications[] = [
                'key' => $key,
                'message' => $notif['message'],
                'date' => $notif['date']
            ];
        }
    }
}

echo json_encode([
    'new' => $newNotifications,
    'timestamp' => time()
]);
?>