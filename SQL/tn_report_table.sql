DROP TABLE IF EXISTS `tn_report_table`;

CREATE TABLE `tn_report_table` (
  `report_id` int(12) NOT NULL,
  `user_id` int(12) NOT NULL,
  `created_time` datetime DEFAULT NULL COMMENT '作成日時',
  `speech_text` text DEFAULT NULL COMMENT '音声テキスト',
  `audio_url` varchar(2083) DEFAULT NULL COMMENT '音声ファイルURL',
  `image_url` varchar(255) NOT NULL DEFAULT '' COMMENT '画像ファイルURL',
  `video_url` varchar(2083) DEFAULT NULL COMMENT '動画ファイルURL',
  `gemini_report` text DEFAULT NULL COMMENT 'Geminiレポート',
  `user_report` text DEFAULT NULL COMMENT 'ユーザーレポート',
  `ai_text_emotion` text DEFAULT NULL COMMENT 'AIテキスト感情分析',
  `sys_text_emotion` text DEFAULT NULL COMMENT 'システムテキスト感情分析',
  `image_emotion` text DEFAULT NULL COMMENT '画像感情分析',
  `video_emotion` text DEFAULT NULL COMMENT '動画感情分析',
  `report_summary` text DEFAULT NULL COMMENT 'レポート要約',
  `document_score` int(11) DEFAULT NULL COMMENT 'ドキュメントスコア',
  `terminology_score` int(11) DEFAULT NULL COMMENT '用語スコア',
  `emotion_score` int(11) DEFAULT NULL COMMENT '感情スコア'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tn_report_table`
  ADD PRIMARY KEY (`report_id`);

ALTER TABLE `tn_report_table`
  MODIFY `report_id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;