<?php
session_start();
include("lib/funcs.php");
header('Content-Type: application/json');

if (!isset($_SESSION["chk_ssid"]) || $_SESSION["chk_ssid"] != session_id()) {
    echo json_encode(['success' => false, 'error' => 'Session expired']);
    exit();
}

require_once('../../emorepoconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');

try {
    $dbinfo = new DatabaseInfo();
    $db = new DatabaseController($dbinfo, IS_DEBUG);

    $sql = "SELECT 
        report_id,
        created_time,
        ai_text_emotion,
        image_emotion,
        document_score,
        terminology_score,
        emotion_score
    FROM tn_report_table 
    WHERE user_id = :user_id 
    ORDER BY created_time DESC 
    LIMIT :limit";
    
    $reports = $db->select($sql, ['user_id' => $_SESSION['lid']]);

    $totals = ['joy' => 0, 'anger' => 0, 'sorrow' => 0, 'surprise' => 0];
    $count = 0;

    foreach ($reports as $report) {
        $data = json_decode($report['ImageEmotionJudge'], true);
        if ($data && isset($data['responses'][0]['faceAnnotations'][0])) {
            $face = $data['responses'][0]['faceAnnotations'][0];
            
            $scores = [
                'VERY_UNLIKELY' => 0,
                'UNLIKELY' => 25,
                'POSSIBLE' => 50,
                'LIKELY' => 75,
                'VERY_LIKELY' => 100
            ];

            $totals['joy'] += $scores[$face['joyLikelihood']] ?? 0;
            $totals['anger'] += $scores[$face['angerLikelihood']] ?? 0;
            $totals['sorrow'] += $scores[$face['sorrowLikelihood']] ?? 0;
            $totals['surprise'] += $scores[$face['surpriseLikelihood']] ?? 0;
            $count++;
        }
    }

    if ($count > 0) {
        array_walk($totals, function(&$value) use ($count) {
            $value = $value / $count;
        });
    }

    echo json_encode([
        'success' => true,
        'emotions' => $totals
    ]);

} catch (Exception $e) {
    error_log("Error in getHistoricalEmotions: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}