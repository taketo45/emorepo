-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost
-- 生成日時: 2024 年 12 月 08 日 00:49
-- サーバのバージョン： 10.4.28-MariaDB
-- PHP のバージョン: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `tn_emorepo`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `tn_report_table`
--

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




--
-- テーブルのインデックス `tn_report_table`
--
ALTER TABLE `tn_report_table`
  ADD PRIMARY KEY (`report_id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `tn_report_table`
--
ALTER TABLE `tn_report_table`
  MODIFY `report_id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
