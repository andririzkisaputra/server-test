<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2022-09-19 00:15:46 --> Severity: error --> Exception: /home2/kos37398/app.kostzy.com/application/models/Home_model.php exists, but doesn't declare class Home_model /home2/kos37398/app.kostzy.com/system/core/Loader.php 340
ERROR - 2022-09-19 00:18:17 --> Query error: Table 'kos37398_kostzy.properti2' doesn't exist - Invalid query: SELECT *
FROM `properti2`
RIGHT JOIN `file_upload` ON `file_upload`.`session_upload_id` = `properti`.`session_upload_id`
WHERE `properti`.`created_by` = '60'
AND `is_deleted` = '0'
ERROR - 2022-09-19 00:19:26 --> Query error: Unknown column 'status_sewa2' in 'where clause' - Invalid query: SELECT *
FROM `kamar`
JOIN `sewa` ON `sewa`.`kamar_id` = `kamar`.`kamar_id`
WHERE `kamar`.`created_by` = '60'
AND `status_sewa2` >= '1'
AND `status_sewa` <= '4'
GROUP BY `sewa`.`kamar_id`
ERROR - 2022-09-19 00:22:33 --> Severity: Notice --> Undefined property: stdClass::$pemilik /home2/kos37398/app.kostzy.com/application/models/Home_model.php 76
ERROR - 2022-09-19 00:25:15 --> Severity: error --> Exception: /home2/kos37398/app.kostzy.com/application/models/Home_model.php exists, but doesn't declare class Home_model /home2/kos37398/app.kostzy.com/system/core/Loader.php 340
