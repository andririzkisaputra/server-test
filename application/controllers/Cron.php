<?php

/**
 *
 */
class Cron extends CI_Controller {

  function __construct() {
    parent::__construct();
  }

  // public function email_sent() {
  //   $config   = array(
  //     'protocol' => 'smtp',
  //     'smtp_host' => $this->all_library->setting('smtp_host'),
  //     'smtp_port' => $this->all_library->setting('smtp_port'),
  //     'smtp_user' => $this->all_library->setting('smtp_user'),
  //     'smtp_pass' => $this->all_library->setting('smtp_pass'),
  //     'mailtype'  => 'html',
  //     'charset'   => 'iso-8859-1'
  //   );
  //   $this->load->library('email', $config);
  //   $this->email->set_newline("\r\n");
  //   $this->email->from($this->all_library->setting('mail_sender'), $this->all_library->setting('mail_sender_name'));
  //   $data_email = $this->all_model->get_all_by('email_sent', ['is_sent' => '0'], 'created_on ASC');
  //   // print_r($this->email);
  //   // exit;
  //   if ($data_email) {
  //     foreach ($data_email as $key => $value) {
  //       $this->email->to($value->email_to);
  //       $this->email->subject($value->title);
  //       $dataEmail  = array(
  //         'title'     => $value->title,
  //         'pre_title' => $value->pre_title,
  //         'msg_title' => $value->msg_title,
  //         'content'   => $value->content,
  //       );
  //       if ($value->button) {
  //         $dataEmail['button'] = $value->button;
  //       }
  //       $message = $this->load->view('email/index', $dataEmail, TRUE);
  //       $this->email->message($message);
  //       if ($value->attach) {
  //         $this->email->attach($value->attach);
  //       }
  //
  //       if ($this->email->send()) {
  //         $this->all_model->update(['email_sent_id' => $value->email_sent_id], 'email_sent', ['is_sent' => '1']);
  //         $this->email->clear();
  //       }else {
  //           show_error($this->email->print_debugger());
  //       }
  //     }
  //   }
  // }

  public function check_out() {
    $sewa = $this->db->from('sewa')->where(['status_sewa' => '3'])->get()->result();
    foreach ($sewa as $key => $value) {
        $tanggal_selesai_sewa = date('Y-m-d', strtotime('+1 days', strtotime($value->tanggal_selesai_sewa)));
      if (date('Y-m-d') >= $tanggal_selesai_sewa) {
          $sewa_count = $this->db->from('sewa')->where(['status_sewa' => '3', 'created_by' => $value->created_by])->get()->num_rows();
        if ($sewa_count >= 1) {
          $this->db->where(['sewa_id' => $value->sewa_id])->update('sewa', [
            'status_sewa' => '7'
          ]);
          // insert histori sewa
          $data_histori = [
            'sewa_id'     => $value->sewa_id,
            'text'        => 'Selesai',
            'status'      => '4',
            'created_by'  => $value->created_by,
            'created_on'  => date('Y-m-d H:i:s'),
          ];
          $this->db->insert('histori_sewa', $data_histori);
        } else {
          $this->db->where(['sewa_id' => $value->sewa_id])->update('sewa', [
            'status_sewa' => '5'
          ]);
          // insert histori sewa
          $data_histori = [
            'sewa_id'     => $value->sewa_id,
            'text'        => 'CheckOut',
            'status'      => '3',
            'created_by'  => $value->created_by,
            'created_on'  => date('Y-m-d H:i:s'),
          ];
          $this->db->insert('histori_sewa', $data_histori);
          // kirim notifikasi
          $da_sewa  = $this->db->from('sewa')->where(['sewa_id' => $value->sewa_id])->get()->row();
          $user     = $this->db->from('user')->where(['user_id' => $da_sewa->created_by])->get()->row();
          $owner    = $this->db->from('user')->where(['user_id' => $da_sewa->pemilik_id])->get()->row();
          $message  = 'Check out otomatis dari sistem';
          $this->notifikasi->send(array(
            'to'    => $user->user_id,
            'from'  => $owner->user_id,
            'title' => 'Check Out',
            'msg'   => $message,
            'params'=> json_encode(['sewa_id' => $value->sewa_id, 'isOwner' => false]),
          ));

          // management
          $properti    = $this->db->from('properti')->where(['created_by' => $da_sewa->pemilik_id, 'is_deleted' => '0'])->get()->row();
          $checkManage = $this->db->from('management_kos')->where('properti_id', $properti->properti_id)->get()->result();
          if ($checkManage) {
            foreach ($checkManage as $key => $value) {
              $message       = '*'.$user->nama.'* melakukan check out otomatis oleh sistem';
              $message_notif = $user->nama.' melakukan check out otomatis oleh sistem';
              $manage_wa = $this->db->from('user')->where('user_id', $value->created_by)->get()->row();
              $this->all_library->wa(array(
                'phone'   => ($manage_wa) ? $manage_wa->notelp : '',
                'message' => $message
              ));
              // kirim notifikasi
              $this->notifikasi->send(array(
                'to'    => $manage_wa->user_id,
                'from'  => $owner->user_id,
                'title' => 'Check Out',
                'msg'   => $message_notif,
                'params'=> json_encode(['sewa_id' => $value->sewa_id, 'isOwner' => false]),
              ));
            }
          }
        }
      }
    }
    return true;
  }

  public function menunggu_pembayaran() {
    $sewa = $this->db->from('sewa')->where(['status_sewa' => '1'])->get()->result();
    foreach ($sewa as $key => $value) {
      $jatuh_tempo = $this->db->from('sewa')->where([
        'status_sewa' => '3',
        'created_by' => $value->created_by
      ])->get()->row();
      if (date('Y-m-d') > $value->tanggal_sewa && !$jatuh_tempo) {
        $this->db->where(['sewa_id' => $value->sewa_id])->update('sewa', [
          'status_sewa' => '6'
        ]);
        $this->db->where(['sewa_id' => $value->sewa_id])->update('tagihan', [
          'status_tagihan' => '3'
        ]);
        // insert histori sewa
        $data_histori = [
          'sewa_id'     => $value->sewa_id,
          'text'        => 'CheckOut',
          'status'      => '5',
          'created_by'  => $value->created_by,
          'created_on'  => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('histori_sewa', $data_histori);
        // kirim notifikasi
        $user     = $this->db->from('user')->where(['user_id' => $value->created_by])->get()->row();
        $owner    = $this->db->from('user')->where(['user_id' => $value->pemilik_id])->get()->row();
        $message  = 'Sistem membatalkan check in secara otomatis karna belum melakukan pembayaran';
        $this->notifikasi->send(array(
          'to'    => $user->user_id,
          'from'  => $owner->user_id,
          'title' => 'Pembayaran',
          'msg'   => $message,
          'params'=> json_encode(['sewa_id' => $value->sewa_id, 'isOwner' => false]),
        ));
        $this->notifikasi->send(array(
          'to'    => $owner->user_id,
          'from'  => $user->user_id,
          'title' => 'Pembayaran',
          'msg'   => $message,
          'params'=> json_encode(['sewa_id' => $value->sewa_id, 'isOwner' => true]),
        ));
      } elseif (date('Y-m-d') >= $value->tanggal_sewa && $jatuh_tempo) {
        $this->db->where(['sewa_id' => $value->sewa_id])->update('sewa', [
          'status_sewa' => '8'
        ]);
        $this->db->where(['sewa_id' => $value->sewa_id])->update('tagihan', [
          'status_tagihan' => '4'
        ]);
        // insert histori sewa
        $data_histori = [
          'sewa_id'     => $value->sewa_id,
          'text'        => 'Telat Bayar',
          'status'      => '6',
          'created_by'  => $value->created_by,
          'created_on'  => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('histori_sewa', $data_histori);
        // kirim notifikasi
        $user     = $this->db->from('user')->where(['user_id' => $value->created_by])->get()->row();
        $owner    = $this->db->from('user')->where(['user_id' => $value->pemilik_id])->get()->row();
        $message  = 'Silahkan lakukan pembayaran';
        $this->notifikasi->send(array(
          'to'    => $user->user_id,
          'from'  => $owner->user_id,
          'title' => 'Telat Pembayaran',
          'msg'   => $message,
          'params'=> json_encode(['sewa_id' => $value->sewa_id, 'isOwner' => false]),
        ));
        $message  = $user->nama.' telat melakukan pembayaran';
        $this->notifikasi->send(array(
          'to'    => $owner->user_id,
          'from'  => $user->user_id,
          'title' => 'Telat Pembayaran',
          'msg'   => $message,
          'params'=> json_encode(['sewa_id' => $value->sewa_id, 'isOwner' => true]),
        ));
      }
    }
    return true;
  }

  public function menunggu_konfirmasi() {
    $sewa = $this->db->from('sewa')->where(['status_sewa' => '2'])->get()->result();
    foreach ($sewa as $key => $value) {
      if (date('Y-m-d') > $value->tanggal_sewa) {
        $this->db->where(['sewa_id' => $value->sewa_id])->update('sewa', [
          'status_sewa' => '3'
        ]);
        // insert histori sewa
        $data_histori = [
          'sewa_id'     => $value->sewa_id,
          'text'        => 'Diterima',
          'status'      => '2',
          'created_by'  => $value->created_by,
          'created_on'  => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('histori_sewa', $data_histori);
        // kirim notifikasi
        $user     = $this->db->from('user')->where(['user_id' => $value->created_by])->get()->row();
        $owner    = $this->db->from('user')->where(['user_id' => $value->pemilik_id])->get()->row();
        $message  = 'Sistem mengkonfirmasi check in secara otomatis';
        $this->notifikasi->send(array(
          'to'    => $user->user_id,
          'from'  => $owner->user_id,
          'title' => 'Pembayaran',
          'msg'   => $message,
          'params'=> json_encode(['sewa_id' => $value->sewa_id, 'isOwner' => false]),
        ));
        $this->notifikasi->send(array(
          'to'    => $owner->user_id,
          'from'  => $user->user_id,
          'title' => 'Pembayaran',
          'msg'   => $message,
          'params'=> json_encode(['sewa_id' => $value->sewa_id, 'isOwner' => true]),
        ));
      }
    }
    return true;
  }

  public function tambah_sewa() {
    $sewa = $this->db->from('sewa')->where(['status_sewa' => '3'])->get()->result();
    foreach ($sewa as $key => $value) {
      $check_sewa = $this->db->from('sewa')->where(['status_sewa' => '1', 'created_by' => $value->created_by])->get()->row();
      if ($value->is_checkout == '0' && !$check_sewa && date('Y-m-d') >= date('Y-m-d', strtotime('-7 days', strtotime($value->tanggal_selesai_sewa)))) {
        $this->db->where(['sewa_id' => $value->sewa_id])->update('sewa', [
            'is_checkout' => '1'
        ]);
        $kode_tagihan = 'TZY'.rand(1000000, 9999999);
        $total        = (int)$value->tambahan_biaya+(int)$value->harga_parkir_motor+(int)$value->harga_parkir_mobil+(int)$value->harga_sewa;
        $data = [
          'kode_tagihan'         => $kode_tagihan,
          'properti_id'          => $value->properti_id,
          'pemilik_id'           => $value->pemilik_id,
          'lantai_id'            => $value->lantai_id,
          'tipe_kamar_id'        => $value->tipe_kamar_id,
          'kamar_id'             => $value->kamar_id,
          'tanggal_sewa'         => $value->tanggal_selesai_sewa,
          'tanggal_selesai_sewa' => date('Y-m-d', strtotime('+1 month', strtotime($value->tanggal_selesai_sewa))),
          'status_sewa'          => '1',
          'is_parkir'            => $value->is_parkir,
          'harga_parkir_motor'   => $value->harga_parkir_motor,
          'harga_parkir_mobil'   => $value->harga_parkir_mobil,
          'tambahan_biaya'       => $value->tambahan_biaya,
          'deposit'              => '0',
          'harga_sewa'           => $value->harga_sewa,
          'created_by'           => $value->created_by,
          'created_on'           => date('Y-m-d H:i:s')
        ];
        $this->db->insert('sewa', $data);
        $sewa_id = $this->db->insert_id();
        $data_tagihan = [
          'sewa_id'        => $sewa_id,
          'kode_tagihan'   => $kode_tagihan,
          'total_harga'    => $total,
          'created_by'     => $value->created_by,
          'rekening_id'    => '1',
          'status_tagihan' => '0',
          'created_on'     => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('tagihan', $data_tagihan);
        // insert histori sewa
        $data_histori = [
          'sewa_id'     => $sewa_id,
          'text'        => 'Diproses',
          'status'      => '0',
          'created_by'  => $value->created_by,
          'created_on'  => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('histori_sewa', $data_histori);
        // kirim wa
        $owner_wa = '';
        $owner    = $this->db->from('user')->where('user_id', $value->pemilik_id)->get()->row();
        $user     = $this->db->from('user')->where('user_id', $value->created_by)->get()->row();
        $owner_wa = ($owner) ? $owner->notelp : '';
        $user_wa  = ($user) ? $user->notelp : '';
        $message  = 'Kode Tagihan '.$kode_tagihan.'*'.($user ? $user->nama : '').'* melakukan perpanjang sewa, silahkan konfirmasi apabila pembayaran sudah diterima.';
        
        $this->all_library->wa(array(
            'phone'   => $owner_wa,
            'message' => $message
        ));

        $message  = ($user ? $user->nama : '').' melakukan perpanjang sewa, silahkan konfirmasi apabila pembayaran sudah diterima.';
        // kirim notifikasi owner
        $this->notifikasi->send(array(
          'to'    => $owner->user_id,
          'from'  => $user->user_id,
          'title' => 'Sewa Kost',
          'msg'   => $message,
          'params'=> json_encode(['sewa_id' => $sewa_id, 'isOwner' => true]),
        ));
        // management
        $properti    = $this->db->from('properti')->where(['created_by' => $value->pemilik_id, 'is_deleted' => '0'])->get()->row();
        $checkManage = $this->db->from('management_kos')->where('properti_id', $properti->properti_id)->get()->result();
        if ($checkManage) {
          foreach ($checkManage as $k => $val) {
            $manage_wa = $this->db->from('user')->where('user_id', $val->created_by)->get()->row();
            $this->all_library->wa(array(
              'phone'   => ($manage_wa) ? $manage_wa->notelp : '',
              'message' => $message
            ));
            // kirim notifikasi
            $this->notifikasi->send(array(
              'to'    => $manage_wa->user_id,
              'from'  => $user->user_id,
              'title' => 'Sewa Kost',
              'msg'   => $message,
              'params'=> json_encode(['sewa_id' => $sewa_id, 'isOwner' => true]),
            ));
          }
        }
      }
    }
    return true;
  }

  public function notif_pembayaran () {
    $notif = '';
    $tanggal = date('Y-m-d', strtotime('-1 days', strtotime('Y-m-d')));
    $where   = '(created_on LIKE "%'.$tanggal.'%" OR created_on LIKE "%'.date('Y-m-d').'%") AND judul = "Lakukan Pembayaran"';
    $sewa    = $this->db->from('sewa')->where(['status_sewa' => '1'])->get()->result();
    foreach ($sewa as $key => $value) {
        $notif = $this->db->from('notifikasi')->where_in(['params' => $value->sewa_id])->where($where)->get()->row();
        if (!isset($notif)) {
            $owner    = $this->db->from('user')->where('user_id', $value->pemilik_id)->get()->row();
            $user     = $this->db->from('user')->where('user_id', $value->created_by)->get()->row();
            $user_wa  = ($user) ? $user->notelp : '';
            
            $message  = 'Hay! *'.$user->nama.'* lakukan pembayaran dengan kode tagihan '.$value->kode_tagihan;
            $this->all_library->wa(array(
                'phone'   => $user_wa,
                'message' => $message
            ));
    
            $message  = 'Hay! '.$user->nama.' lakukan pembayaran dengan kode tagihan '.$value->kode_tagihan;
            $this->notifikasi->send(array(
                'to'    => $user->user_id,
                'from'  => $owner->user_id,
                'title' => 'Lakukan Pembayaran',
                'msg'   => $message,
                'params'=> json_encode(['sewa_id' => $value->sewa_id, 'isOwner' => false]),
            ));
        }
    }
    return true;
  }

}
?>
