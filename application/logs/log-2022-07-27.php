<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2022-07-27 20:22:34 --> Severity: error --> Exception: /home/perisaib/public_html/kostzy/application/models/Home_model.php exists, but doesn't declare class Home_model /home/perisaib/public_html/kostzy/system/core/Loader.php 340
ERROR - 2022-07-27 20:22:37 --> Severity: error --> Exception: /home/perisaib/public_html/kostzy/application/models/Home_model.php exists, but doesn't declare class Home_model /home/perisaib/public_html/kostzy/system/core/Loader.php 340
ERROR - 2022-07-27 20:22:46 --> Severity: error --> Exception: /home/perisaib/public_html/kostzy/application/models/Home_model.php exists, but doesn't declare class Home_model /home/perisaib/public_html/kostzy/system/core/Loader.php 340
ERROR - 2022-07-27 20:23:15 --> Severity: error --> Exception: /home/perisaib/public_html/kostzy/application/models/Home_model.php exists, but doesn't declare class Home_model /home/perisaib/public_html/kostzy/system/core/Loader.php 340
ERROR - 2022-07-27 20:29:32 --> Severity: error --> Exception: /home/perisaib/public_html/kostzy/application/models/Home_model.php exists, but doesn't declare class Home_model /home/perisaib/public_html/kostzy/system/core/Loader.php 340
ERROR - 2022-07-27 20:30:58 --> Severity: error --> Exception: /home/perisaib/public_html/kostzy/application/models/Home_model.php exists, but doesn't declare class Home_model /home/perisaib/public_html/kostzy/system/core/Loader.php 340
ERROR - 2022-07-27 20:31:24 --> Severity: error --> Exception: /home/perisaib/public_html/kostzy/application/models/Home_model.php exists, but doesn't declare class Home_model /home/perisaib/public_html/kostzy/system/core/Loader.php 340
ERROR - 2022-07-27 20:50:29 --> Severity: error --> Exception: /home/perisaib/public_html/kostzy/application/models/Home_model.php exists, but doesn't declare class Home_model /home/perisaib/public_html/kostzy/system/core/Loader.php 340
ERROR - 2022-07-27 20:53:06 --> Severity: error --> Exception: /home/perisaib/public_html/kostzy/application/models/Home_model.php exists, but doesn't declare class Home_model /home/perisaib/public_html/kostzy/system/core/Loader.php 340
ERROR - 2022-07-27 20:53:21 --> Severity: error --> Exception: /home/perisaib/public_html/kostzy/application/models/Home_model.php exists, but doesn't declare class Home_model /home/perisaib/public_html/kostzy/system/core/Loader.php 340
ERROR - 2022-07-27 20:55:38 --> Severity: error --> Exception: /home/perisaib/public_html/kostzy/application/models/Home_model.php exists, but doesn't declare class Home_model /home/perisaib/public_html/kostzy/system/core/Loader.php 340
ERROR - 2022-07-27 21:11:27 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '`sewa`.`status_sewa` > '7'
AND `sewa`.`created_by` = '59'' at line 9 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `sewa`.`status_sewa` >= '1'
AND `sewa`.`status_sewa` <= '4'
AND `OR` `sewa`.`status_sewa` > '7'
AND `sewa`.`created_by` = '59'
ERROR - 2022-07-27 21:12:35 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `sewa`.`status_sewa1` = '8'
AND `sewa`.`status_sewa` >= '1'
AND `sewa`.`status_sewa` <= '4'
AND `sewa`.`created_by` = '59'
ERROR - 2022-07-27 21:13:22 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `sewa`.`status_sewa` >= '1'
AND `sewa`.`status_sewa` <= '4'
AND `sewa`.`created_by` = '59'
OR `sewa`.`status_sewa1` = '8'
ERROR - 2022-07-27 21:14:18 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `sewa`.`status_sewa` >= '1'
AND `sewa`.`status_sewa` <= '4'
OR `sewa`.`status_sewa1` = '8'
AND `sewa`.`created_by` = '59'
ERROR - 2022-07-27 21:14:19 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `sewa`.`status_sewa` >= '1'
AND `sewa`.`status_sewa` <= '4'
OR `sewa`.`status_sewa1` = '8'
AND `sewa`.`created_by` = '59'
ERROR - 2022-07-27 21:15:02 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '' at line 10 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE (`sewa`.`status_sewa` >= '1'
AND `sewa`.`status_sewa` <= '4)'
OR `sewa`.`status_sewa` = '8'
AND `sewa`.`created_by` = '59'
ERROR - 2022-07-27 21:16:57 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE 
        ((sewa.status_sewa1 >= 1 AND `sewa`.`status_sewa` <= 4) OR sewa.status_sewa = 8)
      
ERROR - 2022-07-27 21:17:16 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE ((sewa.status_sewa1 >= 1 AND `sewa`.`status_sewa` <= 4) OR `sewa`.`status_sewa` = 8)
ERROR - 2022-07-27 21:18:00 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '59' at line 7 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE ((sewa.status_sewa1 >= 1 AND `sewa`.`status_sewa` <= 4) OR `sewa`.`status_sewa` = 8) AND sewa.created_by 59
ERROR - 2022-07-27 21:18:22 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '59' at line 7 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE ((sewa.status_sewa >= 1 AND `sewa`.`status_sewa` <= 4) OR `sewa`.`status_sewa` = 8) AND sewa.created_by 59
ERROR - 2022-07-27 21:19:00 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '59' at line 7 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE ((sewa.status_sewa >= 1 AND `sewa`.`status_sewa` <= 4) OR `sewa`.`status_sewa` = 8) AND sewa.created_by 59
ERROR - 2022-07-27 21:19:04 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '59' at line 7 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE ((sewa.status_sewa >= 1 AND `sewa`.`status_sewa` <= 4) OR `sewa`.`status_sewa` = 8) AND sewa.created_by 59
ERROR - 2022-07-27 21:21:48 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE (
          sewa.status_sewa = 1 OR 
          `sewa`.`status_sewa` = 2 OR 
          `sewa`.`status_sewa` = 3 OR 
          `sewa`.`status_sewa1` = 4 OR 
          sewa.status_sewa = 8
        ) AND `sewa`.`created_by` = 59
ERROR - 2022-07-27 21:22:30 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE (`sewa`.`status_sewa` = 1 OR `sewa`.`status_sewa` = 2 OR `sewa`.`status_sewa` = 3 OR `sewa`.`status_sewa1` = 4 OR `sewa`.`status_sewa` = 8) AND `sewa`.`created_by` = 59
ERROR - 2022-07-27 21:24:05 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `sewa`.`status_sewa` != '6'
AND `sewa`.`status_sewa1` != '7'
AND `sewa`.`created_by` = '59'
ERROR - 2022-07-27 21:24:29 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `sewa`.`status_sewa1` != '5'
AND `sewa`.`status_sewa` != '7'
AND `sewa`.`created_by` = '59'
ERROR - 2022-07-27 21:24:30 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `sewa`.`status_sewa1` != '5'
AND `sewa`.`status_sewa` != '7'
AND `sewa`.`created_by` = '59'
ERROR - 2022-07-27 21:25:45 --> Query error: Unknown column 'sewa.status_sew1a' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `sewa`.`status_sewa` != 5 AND 
          `sewa`.`status_sewa` != 6 AND 
          `sewa`.`status_sew1a` != 7 AND 
          `sewa`.`created_by` = 59
ERROR - 2022-07-27 21:28:28 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE 
          sewa.status_sewa1 != 5 AND 
          `sewa`.`status_sewa` != 6 AND 
          `sewa`.`status_sewa` != 7 AND 
          sewa.created_by = 59
      
ERROR - 2022-07-27 21:31:48 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `sewa`.`status_sewa1` != 6 AND `sewa`.`created_by` = 59
ERROR - 2022-07-27 21:32:04 --> Query error: Unknown column '6' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `sewa`.`status_sewa` != `6` AND `sewa`.`created_by` = 59
ERROR - 2022-07-27 21:35:50 --> Query error: Unknown column 'sewa.created_by1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE 
        sewa.status_sewa != "5" AND
        `sewa`.`status_sewa` != "6" AND
        `sewa`.`status_sewa` != "7" AND
        sewa.created_by1 = 59
      
ERROR - 2022-07-27 21:39:55 --> Query error: Unknown column 'sewa.status_sewa1' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `sewa`.`status_sewa` = '4'
OR `sewa`.`status_sewa1` = '8'
ERROR - 2022-07-27 21:40:47 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '21:40:47' at line 7 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE (library.available_until >= 2022-07-27 21:40:47
ERROR - 2022-07-27 21:40:49 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '21:40:49' at line 7 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE (library.available_until >= 2022-07-27 21:40:49
ERROR - 2022-07-27 21:41:25 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '21:41:25
OR library.available_until = '00-00-00 00:00:00')' at line 7 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE (library.available_until >= 2022-07-27 21:41:25
OR library.available_until = '00-00-00 00:00:00')
ERROR - 2022-07-27 21:43:25 --> Query error: Unknown column '1sewa.status_sewa' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE (1sewa.status_sewa = 1
OR sewa.status_sewa = '2'
OR sewa.status_sewa = '3'
OR sewa.status_sewa = '4'
OR sewa.status_sewa = '8')
ERROR - 2022-07-27 21:45:01 --> Query error: Unknown column 'sewa.2status_sewa' in 'where clause' - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE (sewa.status_sewa = 1
OR sewa.status_sewa = '2'
OR sewa.status_sewa = '3'
OR sewa.status_sewa = '4'
OR sewa.2status_sewa = '8')
AND sewa.created_by = 59
ERROR - 2022-07-27 21:45:35 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '' at line 11 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE (sewa.status_sewa = 1
OR sewa.status_sewa = '2'
OR sewa.status_sewa = '3'
OR sewa.status_sewa = '4'
AND sewa.created_by = 59
ERROR - 2022-07-27 21:45:39 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '' at line 11 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE (sewa.status_sewa = 1
OR sewa.status_sewa = '2'
OR sewa.status_sewa = '3'
OR sewa.status_sewa = '4'
AND sewa.created_by = 59
ERROR - 2022-07-27 21:45:45 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '' at line 11 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE (sewa.status_sewa = 1
OR sewa.status_sewa = '2'
OR sewa.status_sewa = '3'
OR sewa.status_sewa = '4'
AND sewa.created_by = 59
ERROR - 2022-07-27 21:45:58 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '' at line 11 - Invalid query: SELECT *
FROM `sewa`
LEFT JOIN `properti` ON `properti`.`properti_id` = `sewa`.`properti_id`
LEFT JOIN `lantai` ON `lantai`.`lantai_id` = `sewa`.`lantai_id`
LEFT JOIN `kamar` ON `kamar`.`kamar_id` = `sewa`.`kamar_id`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE (sewa.status_sewa = 1
OR sewa.status_sewa = '2'
OR sewa.status_sewa = '3'
OR sewa.status_sewa = '4'
AND sewa.created_by = 59
ERROR - 2022-07-27 21:47:17 --> Severity: error --> Exception: syntax error, unexpected '->' (T_OBJECT_OPERATOR) /home/perisaib/public_html/kostzy/application/models/Home_model.php 58
