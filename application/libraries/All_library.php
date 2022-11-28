<?php

/**
 *
 */
class All_library {

  public function __construct() {
    $this->ci =& get_instance();
    $this->ci->load->model('all_model');
    $this->ci->load->library('encryption');
    $this->key      = '488f2677480d21d3feca2c49e496dadc';
    $this->token_wa = 'fpGKl5FOWY6FnwhOmBHJ23XFwmDb8A';
    $this->config   = ['cipher' => 'aes-256', 'mode' => 'ctr', 'key' => $this->key];
  }

  public function randomCode($digits) {
    $digits     = (int)$digits;
    $randomCode = rand(pow(10, $digits-1), pow(10, $digits)-1);
    return $randomCode;
  }

  public function randomPassword($length = 8, $encode = true) {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $length; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return ($encode) ? $this->encodeString(implode($pass)) : implode($pass);
  }

  public function encodeString($string) {
    $this->ci->encryption->initialize($this->config);
    return $this->ci->encryption->encrypt($string);
  }

  public function decodeString($string) {
    $this->ci->encryption->initialize($this->config);
    return $this->ci->encryption->decrypt($string);
  }

  public function checkPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if(strpos($phone, '620') !== false) {
      return str_replace('620', '0', substr($phone, 0, 4)).substr($phone, 4, strlen($phone));
    }

    if (strpos($phone, '62') !== false) {
      return str_replace('62', '0', substr($phone, 0, 3)).substr($phone, 3, strlen($phone));
    }

    return $phone;
  }

  public function format_date($date, $isShort = true, $withDay = true, $time = true) {
    $day    = $this->day(date('N', strtotime($date)));
    $_date  = date('d', strtotime($date));
    $month  = $this->bulan(date('n', strtotime($date)));
    $year   = date(($isShort ? 'y' : 'Y'), strtotime($date));
    return ($withDay ? ($isShort ? substr($day, 0, 3) : $day).', ' : '').$_date.' '.($isShort ? substr($month, 0, 3) : $month).' '.$year.($time ?  ' '.date('H:i', strtotime($date)) : '');
  }

  public function format_notelp($notelp) {
    $notelp              = str_replace(" ","",$notelp);
    $notelp              = str_replace("(","",$notelp);
    $notelp              = str_replace(")","",$notelp);
    $notelp              = str_replace(".","",$notelp);
    if(!preg_match('/[^+0-9]/',trim($notelp))){
        if(substr(trim($notelp), 0, 3)=='+62'){
          $notelp = '0'.substr(trim($notelp), 3);
        }
        elseif(substr(trim($notelp), 0, 2)=='62'){
          $notelp = '0'.substr(trim($notelp), 2);
        }
        elseif(substr(trim($notelp), 0, 1)!='0'){
          $notelp = '0'.substr(trim($notelp), 0);
        }
    }
    return $notelp;
  }

  public function format_date_pembayaran($date) {
    $day    = $this->day(date('N', strtotime($date)));
    $_date  = date('d', strtotime($date));
    $month  = $this->bulan(date('n', strtotime($date)));
    $year   = date('Y', strtotime($date));
    $time   = date('H:i', strtotime($date));
    return $day.', '.$_date.' '.$month.' '.$year.', Pukul '.$time;
  }

  // date
  public function date($data, $isSort = false) {
    $_data = strtotime($data);
    $day = date('d', $_data);
    $bulan = $this->bulan(date('n', $_data));
    $tanggal = $day.' '. (($isSort) ? substr($bulan, 0, 3) : $bulan) .' '.date('Y', $_data);
    return $tanggal;
  }

  public function birthday($data) {
    $tanggal = new DateTime($data);
    $today = new DateTime('today');
    $tahun = $today->diff($tanggal)->y;
    return $tahun;
  }

  public function datetime($date) {
    $day    = $this->day(date('N', strtotime($date)));
    $_date  = date('d', strtotime($date));
    $month  = $this->bulan(date('n', strtotime($date)));
    $year   = date('Y', strtotime($date));
    $time   = date('H:i', strtotime($date));
    return $_date.' '.$month.' '.$year.', '.$time;
  }

  public function dateDiff($data, $isChat = false) {
		if(!empty($data)){
		$date_now = date('Y-m-d');
		$date_time = date('Y-m-d', strtotime($data));

    	if($date_time == $date_now){
				// $date = $this->datetime($data);
        //$date = $d.' '.$m;
        $time_now = new DateTime(date('H:i:s'));
        $time_db = new DateTime(date('H:i:s', strtotime($data)));
        $diff = $time_now->diff($time_db);

        if ($diff->i == 0) {
          $date = ($isChat) ? date('H:i', strtotime($data)) : 'baru saja';
        } else if ($diff->h > 1) {
          $date = 'hari ini, '.date('H:i', strtotime($data));
        } else {
          if ($diff->i > 15) {
            $date = date('H:i', strtotime($data));
          }else {
            $date = ($isChat) ? date('H:i', strtotime($data)) : $diff->i.' menit lalu';
          }
        }

			}else{
				$date1 = $date_now;
			  $date2 = $date_time;
			  $datetime1 = new DateTime($date1);
			  $datetime2 = new DateTime($date2);
			  $difference = $datetime1->diff($datetime2);
			  $data_date = $difference->days;
        if ($data_date == 1) {
          $date = 'kemarin, '.date('H:i', strtotime($data));
        }elseif ($data_date >= 2 && $data_date <= 7){
					$date = $data_date.' hari lalu'.($isChat ? ', '.date('H:i', strtotime($data)) : '');
				}else {
          $d = date('d', strtotime($data));
          $y = date('Y', strtotime($data));
          $dt_m = date('m', strtotime($data));
          $m = $this->bulan((int)$dt_m);
					$date = $d.' '.$m.' '.$y;
          if ($isChat) {
            $date = $d.'/'.$dt_m.'/'.date('y', strtotime($data)).' '.date('H:i', strtotime($data));
          }
				}
			}
			return $date;
		}
	}

  public function day($d, $short = false) {
    $hari = array(
      1 => 'Senin',
      2 => 'Selasa',
      3 => 'Rabu',
      4 => 'Kamis',
      5 => 'Jum\'at',
      6 => 'Sabtu',
      7 => 'Minggu'
    );

    return ($d) ? ($short ? substr($hari[$d], 0, 3) : $hari[$d]) : $hari;
  }

  public function bulan($m, $short = false) {
    $data = array(
      1 => 'Januari',
      2 => 'Febuari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember'
    );

    return ($m) ? ($short ? substr($data[$m], 0, 3) : $data[$m]) : $data;
  }

  public function status_akun_game($status) {
    $data = array(
      1 => 'Aktif',
      2 => 'Proses Verifikasi',
      3 => 'Dihapus'
    );

    return $data[$status];
  }

  public function format_harga($val, $prefix = 'Rp ') {
    if(!empty($val)){
      $_val = $prefix.number_format((float)$val,0,',','.');
      return $_val;
    }
  }

  public function get_session_id() {
    return time().rand(000,999);
  }

  public function kode_transaksi($transaksi_id = 1) {
    $kode  = 'ORD';
    $kode .= date('ymd').($this->ci->all_model->get_by('transaksi', [], 'num_rows')+ 1).$transaksi_id;
    return $kode;
  }

  public function kode_tagihan($tagihan_id = 1) {
    $kode  = 'INV';
    $kode .= date('ymd').($this->ci->all_model->get_by('tagihan', [], 'num_rows') + 1).$tagihan_id;
    return $kode;
  }

  public function status_sewa($type) {
    $data = array(
      '0'  => 'Belum Menyewa',
      '1'  => 'Menunggu Pembayaran',
      '2'  => 'Menunggu Konfirmasi',
      '3'  => 'Check In',
      '4'  => 'Menunggu Konfirmasi Check Out',
      '5'  => 'Check Out',
      '6'  => 'Batal',
      '7'  => 'Selesai',
      '8'  => 'Telat Bayar',
    );
    return $data[$type];
  }

  public function histori_sewa($type) {
    $data = array(
      '0' => 'Diproses',
      '1' => 'Dibayar',
      '2' => 'Diterima',
      '3' => 'Check Out',
      '4' => 'Selesai',
      '5' => 'Batal',
      '6' => 'Telat Bayar',
    );
    return $data[$type];
  }

  public function status_tagihan($type) {
    $data = array(
      '0'  => 'Menunggu Pembayaran',
      '1'  => 'Menunggu Konfirmasi',
      '2'  => 'Terbayar',
      '3'  => 'Batal',
      '4'  => 'Telat Bayar',
    );
    return $data[$type];
  }

  public function status_order_info($type) {
    $data = array(
      '0' => 'Proses sistem',
      '1' => 'Order menunggu pembayaran',
      '2' => 'Pembayaran telah diterima',
      '3' => 'Order sedang dalam proses',
      '4' => 'Order selesai',
      '4' => 'Order dibatalkan',
    );
    return $data[$type];
  }

  public function status_order_histori($type) {
    $data = array(
      '0' => 'Proses sistem : ',
      '1' => 'Diorder : ',
      '2' => 'Pembayaran Diterima : ',
      '3' => 'Diproses : ',
      '4' => 'Selesai : ',
      '5' => 'Dibatalkan : ',
    );
    return $data[$type];
  }

  public function status_pembayaran($type) {
    $data = array(
      '0' => 'Tidak Dilist',
      '1' => 'Menunggu Pembayaran',
      '2' => 'Dibayar',
      '3' => 'Batal',
      '4' => 'Refund',
      '5' => 'Menunggu Verifikasi',
    );
    return $data[$type];
  }

  public function status_parkir($type) {
    $data = array(
      '0' => 'Tidak Menggunakan Parkir',
      '1' => 'Parkir Motor',
      '2' => 'Parkir Mobil',
      '3' => 'Parkir Motor dan Mobil'
    );
    return $data[$type];
  }

  public function status_penarikan($type) {
    $data = array(
      '0' => 'Menunggu Validasi',
      '1' => 'Diterima',
      '2' => 'Ditolak',
    );
    return $data[$type];
  }

  public function status_akun($type) {
    $data = array(
      '1' => 'Belum Aktif',
      '2' => 'Aktif',
      '3' => 'Suspend',
      '4' => 'Delete'
    );
    return $data[$type];
  }


  public function lama_sewa($type) {
    $data = array(
      '0' => 'Long Term',
      '1' => '1 Bulan',
      '2' => '2 Bulan',
      '3' => '3 Bulan'
    );
    return $data[$type];
  }

  public function kode_transaksi_koin($type) {
    $data = array(
      'S01' => 'Pendapatan',
      'S02' => 'Pembayaran',
      'S03' => 'Pengembalian',
      'S04' => 'Topup',
    );
    return $data[$type];
  }

  public function kode_transaksi_koin_info($type) {
    $data = array(
      'S01' => 'Pendapatan poin dari transaksi',
      'S02' => 'Pembayaran transaksi menggunakan poin',
      'S03' => 'Pengembalian poin dari transaksi',
      'S04' => 'Topup poin dari transaksi',
    );
    return $data[$type];
  }

  function get_ip() {
      $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
      foreach ($ip_keys as $key) {
          if (array_key_exists($key, $_SERVER) === true) {
              foreach (explode(',', $_SERVER[$key]) as $ip) {
                  $ip = trim($ip);
                  if ($this->validate_ip($ip)) {
                      return $ip;
                  }
              }
          }
      }

      return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
  }

  function validate_ip($ip){
      if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
          return false;
      }
      return true;
  }

  public function format_statistik($angka, $floor = false) {
    if ($floor) {
      if($angka > '1000000000000') {
        $new = number_format(floor($angka/1000000000000),0).'T';
      } elseif($angka > '1000000000'){
        $new = number_format(floor($angka/1000000000),0).'B';
      } elseif($angka > '1000000') {
        $new = number_format(floor($angka/1000000),0).'M';
      } elseif($angka > '1000') {
        $new = number_format(floor($angka/1000),0).'K';
      }else {
        $new =  number_format($angka);
      }
    }else {
      if($angka > '1000000000000') {
        $new = number_format(round(($angka/1000000000000),1),0).'T';
      } elseif($angka > '1000000000'){
        $new = number_format(round(($angka/1000000000),1),0).'B';
      } elseif($angka > '1000000') {
        $new = number_format(round(($angka/1000000),1),0).'M';
      } elseif($angka > '1000') {
        $new = number_format(round(($angka/1000),1),0).'K';
      }else {
        $new =  number_format($angka);
      }
    }

		return $new;
	}

  public function format_singkat_angka($new, $presisi = 1) {
    if ($new < 900) {
  		$format_angka = number_format($new, $presisi);
  		$simbol = '';
  	} else if ($new < 900000) {
  		$format_angka = number_format($new / 1000, $presisi);
  		$simbol = 'rb';
  	} else if ($new < 900000000) {
  		$format_angka = number_format($new / 1000000, $presisi);
  		$simbol = 'jt';
  	} else if ($new < 900000000000) {
  		$format_angka = number_format($new / 1000000000, $presisi);
  		$simbol = 'M';
  	} else {
  		$format_angka = number_format($new / 1000000000000, $presisi);
  		$simbol = 'T';
  	}

  	if ( $presisi > 0 ) {
  		$pisah = '.' . str_repeat( '0', $presisi );
  		$format_angka = str_replace( $pisah, '', $format_angka );
  	}

  	return $format_angka.$simbol;
	}

  public function setting($name = null) {
    if ($name) {
      $dataSetting = $this->ci->all_model->native_find_by('app_config', ['app_config_key' => $name]);
      return ($dataSetting) ? $dataSetting->app_config_value : '';
    }else {
      $responseSetting  = [];
      $dataSetting      = $this->ci->all_model->native_find_all_by('app_config', []);
      if ($dataSetting) {
        foreach ($dataSetting as $key => $value) {
          $responseSetting[$value->app_config_key] = $value->app_config_value;
        }
      }
      return $responseSetting;
    }
  }

  public function mail($to, $title, $pre_title, $msg_title, $content, $button = '', $attach = null, $is_sent = '0') {
    return $this->ci->all_model->insert('email_sent', array(
      'email_to'    => $to,
      'title'       => $title,
      'pre_title'   => $pre_title,
      'msg_title'   => $msg_title,
      'content'     => $content,
      'button'      => ($button) ? $button : null,
      'attach'      => ($attach) ? $attach : null,
      'is_sent'     => $is_sent,
      'created_by'  => '1',
    ));
  }


  public function status_kamar($status) {
    $data = array(
      0 => 'Dalam Perbaikan',
      1 => 'Tersedia'
    );

    return $data[$status];
  }

  public function wa($data) {
    $data['device_key'] = $this->get_user();
    $server   = 'https://quods.id/api/whatsapp/send';
    $curl     = curl_init();
    curl_setopt($curl, CURLOPT_HTTPHEADER,
        array(
            "Authorization:Bearer {$this->token_wa}",
        )
    );
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_URL, $server);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    $result = curl_exec($curl);
    curl_close($curl);
  }

  public function get_user() {
    $server   = 'https://quods.id/api/user';
    $curl     = curl_init();
    curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization:Bearer {$this->token_wa}"]);
    curl_setopt($curl, CURLOPT_URL, $server);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    $output = json_decode($output);
    $result = '';
    if (isset($output->status) && $output->status) {
      $result = (isset($output->devices[0])) ? $output->devices[0]->device_key : '';
    }

    return $result;
  }

}

 ?>
