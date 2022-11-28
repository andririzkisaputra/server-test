<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2022-09-04 00:20:29 --> Severity: error --> Exception: syntax error, unexpected '$this' (T_VARIABLE) /home2/kos37398/app.kostzy.com/application/models/All_model.php 60
ERROR - 2022-09-04 00:24:37 --> Query error: Unknown column 'status_sewa1' in 'where clause' - Invalid query: SELECT SUM(harga_sewa) as nilai_sewa
FROM `sewa`
WHERE pemilik_id = 108
AND MONTH(tanggal_sewa) = 7
AND YEAR(tanggal_sewa) = 2022
AND (status_sewa1 = 0
OR status_sewa = '2'
OR status_sewa = '3'
OR status_sewa = '4'
OR status_sewa = '5'
OR status_sewa = '7')
