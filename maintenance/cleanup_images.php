<?php
require_once('../lib/DatabaseInfo.php');
require_once('../lib/DatabaseController.php');

// 30日以上経過した画像を削除
$threshold = date('Y-m-d', strtotime('-30 days'));

$db = new DatabaseController(new DatabaseInfo());
// $sql = "SELECT ImageData_URL FROM tn_report_table WHERE CreatedTime < :threshold";
$sql = "SELECT image_url 
FROM tn_report_table 
WHERE created_time < DATE_SUB(NOW(), INTERVAL :threshold DAY)";
$oldImages = $db->select($sql, [':threshold' => $threshold]);

foreach ($oldImages as $image) {
    if (!empty($image['ImageData_URL'])) {
        $fullPath = dirname(__DIR__) . '/' . $image['ImageData_URL'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
} 