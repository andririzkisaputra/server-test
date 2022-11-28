<?php
/**
 *
 */
class Sewa_model extends CI_Model {

  protected $table       = '-';
  protected $table_key   = '-';
  protected $page        = 0;
  protected $perpage     = 15;
  protected $data        = array();
  protected $where       = array();
  protected $result      = array(
    'success'   => true,
    'data'      => [],
    'totaldata' => 0,
    'message'   => '',
  );

  public function _pre($key, $table, $table_key, $page, $perpage, $data, $where) {
    $this->table     = $table;
    $this->table_key = $table_key;
    $this->where     = $where;
    $this->page      = $page;
    $this->perpage   = $perpage;
    $this->data      = $data;

    if (!method_exists($this, $key)) {
      $this->result['success'] = false;
      $this->result['message'] = 'FUNC_NOT_FOUND';

      return $this->result;
    }

    return $this->$key();
  }

  public function preProcess() {
    $properti_id = $this->data['properti_id'];
    $user_id     = $this->data['created_by'];
    $pemilik_id  = $this->data['pemilik_id'];
    $where       = 'properti_id = '.$properti_id.' AND created_by = '.$user_id.' AND status_sewa = "0"';
    $sewa        = $this->db->select('sewa_id')->from('sewa')->where($where)->get()->row();
    if (isset($sewa)) {
        $sewa_id  = $sewa->sewa_id;
        $properti = $this->db->from('properti')->where(['properti_id' => $properti_id])->get()->row();
        $data = [
            'harga_parkir_motor' => $properti->harga_parkir_motor,
            'harga_parkir_mobil' => $properti->harga_parkir_mobil,
            'deposit' => $properti->deposit,
            'tambahan_biaya' => $properti->tambahan_biaya,
            'created_on' => date("Y-m-d H:i:s"),
        ];
        $this->db->where(['sewa_id' => $sewa_id]);
        $update = $this->db->update('sewa', $data);
    } else {
        $properti = $this->db->from('properti')->where(['properti_id' => $properti_id])->get()->row();
        $this->db->insert('sewa', [
            'created_by' => $user_id,
            'harga_parkir_motor' => $properti->harga_parkir_motor,
            'harga_parkir_mobil' => $properti->harga_parkir_mobil,
            'deposit' => $properti->deposit,
            'tambahan_biaya' => $properti->tambahan_biaya,
            'properti_id' => $properti_id,
            'pemilik_id' => $pemilik_id,
            'created_on' => date("Y-m-d H:i:s"),
        ]);
        $sewa_id = $this->db->insert_id();
    }

    $this->result['data']    = ($sewa_id) ? $sewa_id : '';
    $this->result['success'] = ($sewa_id) ? true : false;
    return $this->result;
  }

  public function afterProcess() {
    $link_properti = URL_PROPERTI.'/thumb_';
    $link_profile = URL_PROFILE.'/thumb_';
    $sewa_id = $this->where['sewa_id'];
    $created_by = $this->where['created_by'];
    $data = $this->db->from('sewa')->where(['sewa_id' => $sewa_id])->get()->row();
    $data->properti = $this->db->select('properti.*, properti.session_upload_id AS properti_upload')->from('properti')->join('user', 'user.user_id = properti.created_by')->where(['properti_id' => $data->properti_id])->get()->row();
    $file_name = $this->db->select('CONCAT("'.$link_properti.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $data->properti->properti_upload])->get()->row();
    $data->properti->gambar_link = (isset($file_name->gambar_link)) ? $file_name->gambar_link : '';

    // $this->db->from('lantai');
    // $this->db->where(['properti_id' => $data->properti_id]);
    // $data->lantai = $this->db->get()->result();
    // foreach ($data->lantai as $key => $value) {
    //   if ($data->lantai_id == $value->lantai_id) {
    //     $data->value_lantai = 'Lantai '.$value->lantai;
    //   }
    //   $value->value_lantai = 'Lantai '.$value->lantai;
    // }

    if ($data->is_parkir == '3') {
      $total_parkir       = ($data->harga_parkir_motor+$data->harga_parkir_mobil);
      $data->total_bayar  = ($data->harga_sewa+$total_parkir);
      $data->value_parkir = $this->all_library->status_parkir($data->is_parkir);
    } elseif ($data->is_parkir == '1') {
      $data->total_bayar  = ($data->harga_sewa+$data->harga_parkir_motor);
      $data->value_parkir = $this->all_library->status_parkir($data->is_parkir);
    } elseif ($data->is_parkir == '2') {
      $data->total_bayar  = ($data->harga_sewa+$data->harga_parkir_mobil);
      $data->value_parkir = $this->all_library->status_parkir($data->is_parkir);
    } else {
      $data->value_parkir = $this->all_library->status_parkir($data->is_parkir);
      $data->total_bayar  = $data->harga_sewa;
    }
    if ($data->kapasitas > '1') {
        $data->total_bayar  = $data->total_bayar+$data->tambahan_biaya;
    }
    $data->data_parkir = [];
    if ($data->harga_parkir_motor && $data->harga_parkir_mobil) {
      $data->data_parkir = [
        [
          'is_parkir'    => '0',
          'value_parkir' => 'Tidak Menggunakan Parkir',
        ],
        [
          'is_parkir'    => '1',
          'value_parkir' => 'Parkir Motor',
        ],
        [
          'is_parkir'    => '2',
          'value_parkir' => 'Parkir Mobil',
        ],
        [
          'is_parkir'    => '3',
          'value_parkir' => 'Parkir Motor dan Mobil',
        ],
      ];
    } elseif ($data->harga_parkir_motor) {
      $data->data_parkir = [
        [
          'is_parkir'    => '0',
          'value_parkir' => 'Tidak Menggunakan Parkir',
        ],
        [
          'is_parkir'    => '1',
          'value_parkir' => 'Parkir Motor',
        ],
      ];
    } elseif ($data->harga_parkir_mobil) {
      $data->data_parkir = [
        [
          'is_parkir'    => '0',
          'value_parkir' => 'Tidak Menggunakan Parkir',
        ],
        [
          'is_parkir'    => '2',
          'value_parkir' => 'Parkir Mobil',
        ],
      ];
    }
    $data->data_lama_sewa = [
        [
            'id'    => '1', 
            'value' => '1 Bulan', 
        ],
        [
            'id'    => '2', 
            'value' => '2 Bulan', 
        ],
        [
            'id'    => '3', 
            'value' => '3 Bulan', 
        ],
        [
            'id'    => '0', 
            'value' => 'Long Term'
        ],
    ];
    $data->data_penghuni = ['Pasutri', 'Teman', 'Saudara'];
    if ($data->lama_sewa != '0') {
        $data->total_bayar = $data->total_bayar*(int)$data->lama_sewa;
        $data->harga_sewa  = $data->harga_sewa*(int)$data->lama_sewa;
        // $update = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
        //     'harga_sewa' => $data->harga_sewa,
        // ]);
    }

    if ($data->deposit) {
        $data->total_bayar = $data->total_bayar+(int)$data->deposit;
    }
    
    $data->value_lama_sewa      = $this->all_library->lama_sewa($data->lama_sewa);
    $data->tambahan_biaya_f     = $this->all_library->format_harga($data->tambahan_biaya);
    $data->harga_sewa_f         = $this->all_library->format_harga($data->harga_sewa);
    $data->deposit_f            = $this->all_library->format_harga($data->deposit);
    $data->harga_parkir_motor_f = $this->all_library->format_harga($data->harga_parkir_motor);
    $data->harga_parkir_mobil_f = $this->all_library->format_harga($data->harga_parkir_mobil);
    $data->total_bayar_f        = $this->all_library->format_harga($data->total_bayar);
    $data->tanggal_sewa         = ($data->tanggal_sewa) ? $data->tanggal_sewa : date('Y-m-d');
    $data->tipe_kamar           = $this->db->from('tipe_kamar')->join('kamar', 'kamar.tipe_kamar_id = tipe_kamar.tipe_kamar_id')->where([
      // 'kamar.lantai_id'  => $data->lantai_id,
      'kamar.created_by' => $data->pemilik_id,
      'kamar.is_deleted' => '0'
    ])->group_by('kamar.tipe_kamar_id')->get()->result();
    $value_tipe_kamar                     = $this->db->select('tipe_kamar')->from('tipe_kamar')->where(['tipe_kamar_id' => $data->tipe_kamar_id])->get()->row();
    $data->value_tipe_kamar               = (isset($value_tipe_kamar->tipe_kamar)) ? $value_tipe_kamar->tipe_kamar : null;
    if ($data->tipe_kamar_id) {
      $data->data_kamar = $this->getKamar($data->tipe_kamar_id, $data->pemilik_id, $data->properti->properti_id);
    }
    $this->result['data']    = ($data) ? $data : '';
    $this->result['success'] = ($data) ? true : false;
    return $this->result;
  }

  public function data_diri($created_by) {
    $this->db->from('user');
    $this->db->where(['user_id' => $created_by]);
    $data_profile = $this->db->get()->row();
    $link_profile = URL_PROFILE.'/thumb_';

    if (isset($data_profile->session_upload_id)) {
      $file_name                 = $this->db->select('CONCAT("'.$link_profile.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $data_profile->session_upload_id])->get()->row();
      $data_profile->gambar_link = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
    }

    return $data_profile;
  }

  public function setParkir() {
    $update = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'is_parkir' => $this->data['parkir'],
    ]);
    $this->result['success'] = $update;
    return $this->result;
  }

  public function setLamaSewa() {
    $update = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'lama_sewa' => $this->data['lama_sewa'],
    ]);
    $this->result['success'] = $update;
    return $this->result;
  }

  public function setPenghuni() {
    $update = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'penghuni' => $this->data['penghuni'],
    ]);
    $this->result['success'] = $update;
    return $this->result;
  }

  public function setDeposit() {
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'deposit' => $this->data['deposit'],
    ]);
    return $this->result;
  }

  public function setHargaKamar() {
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'harga_sewa' => $this->data['harga_kamar'],
    ]);
    return $this->result;
  }

  public function setHargaSewa() {
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'harga_sewa' => $this->data['harga_kamar'],
    ]);
    $this->updateTagihan($this->where['sewa_id']);
    return $this->result;
  }

  public function setHargaMotor() {
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'harga_parkir_motor' => $this->data['harga_parkir_motor'],
    ]);
    $this->updateTagihan($this->where['sewa_id']);
    return $this->result;
  }

  public function setHargaMobil() {
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'harga_parkir_mobil' => $this->data['harga_parkir_mobil'],
    ]);
    $this->updateTagihan($this->where['sewa_id']);
    return $this->result;
  }

  public function setHargaTambahan() {
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'tambahan_biaya' => $this->data['tambahan_biaya'],
    ]);
    $this->updateTagihan($this->where['sewa_id']);
    return $this->result;
  }

  public function setHargaDeposit() {
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'deposit' => $this->data['deposit'],
    ]);
    $this->updateTagihan($this->where['sewa_id']);
    return $this->result;
  }

  public function setKapasitas() {
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'kapasitas' => $this->data['kapasitas'],
    ]);
    return $this->result;
  }

  public function setLantai() {
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'lantai_id'     => $this->data['lantai_id'],
      'tipe_kamar_id' => null,
      'kamar_id'      => null
    ]);
    return $this->result;
  }

  public function checkOutOwner() {
    // $data_sewa = $this->db->from('sewa')->where([
    //   'properti_id' => $this->where['properti_id'],
    //   'created_by'  => $this->where['created_by'],
    //   'pemilik_id'  => $this->where['pemilik_id'],
    //   'status_sewa' => '3',
    // ])->get()->row();
    
    $tagihan = $this->db->from('tagihan')->where(['sewa_id' => $this->where['sewa_id']])->get()->row();
    $updateTagihan = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('tagihan', [
        'total_harga'         => (string)((int)$tagihan->total_harga-(int)$this->where['refund']),
        'gambar_pengembalian' => (isset($this->where['gambar'])) ? $this->where['gambar'] : NULL,
        'modified_on'         => date('Y-m-d H:i:s'),
    ]);
    if (isset($this->where['is_owner'])) {
        $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
          'refund'               => $this->where['refund'],
          'note'                 => $this->where['note'],
          'deposit'              => (string)((int)$this->where['deposit']-(int)$this->where['refund']),
          'status_sewa'          => '5',
          'tanggal_selesai_sewa' => $this->where['tanggal_sewa'],
          'modified_on'          => date('Y-m-d H:i:s')
        ]);
    } else {
        $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
            'status_sewa' => '4',
            'modified_on' => date('Y-m-d H:i:s')
        ]);
    }
    // kirim notifikasi
    $owner_wa      = '';
    $sewa          = $this->db->from('sewa')->where(['sewa_id' => $this->where['sewa_id']])->get()->row();
    $user          = $this->db->from('user')->where(['user_id' => $sewa->created_by])->get()->row();
    $owner         = $this->db->from('user')->where(['user_id' => $sewa->pemilik_id])->get()->row();
    $user_wa       = ($user) ? $user->notelp : '';
    $message       = 'Kode Tagihan '.$sewa->kode_tagihan.', *'.($user ? $user->nama : '').'* Owner sudah melakukan check out kosan, silahkan hubungi owner untuk pengembalian dana.';
    $message_notif = 'Kode Tagihan '.$sewa->kode_tagihan.', '.($user ? $user->nama : '').' Owner sudah melakukan check out kosan, silahkan hubungi owner untuk pengembalian dana.';
    $this->notifikasi->send(array(
      'to'    => $user->user_id,
      'from'  => $owner->user_id,
      'title' => 'Check Out',
      'msg'   => $message_notif,
      'params'=> json_encode(['sewa_id' => $this->where['sewa_id'], 'isOwner' => true]),
    ));

    // kirim wa
    $this->all_library->wa(array(
      'phone'   => $user_wa,
      'message' => $message
    ));

    // insert histori sewa
    $data_histori = [
        'sewa_id'     => $this->where['sewa_id'],
        'text'        => 'Check Out',
        'status'      => '3',
        'created_by'  => $sewa->created_by,
        'created_on'  => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('histori_sewa', $data_histori);

    return $this->result;
  }

  public function setPengembalianDana() {
    
    $tagihan = $this->db->from('tagihan')->where(['sewa_id' => $this->where['sewa_id']])->get()->row();
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('tagihan', [
        'total_harga'         => (string)((int)$tagihan->total_harga-(int)$this->where['refund']),
        'gambar_pengembalian' => (isset($this->where['gambar'])) ? $this->where['gambar'] : NULL,
        'modified_on'         => date('Y-m-d H:i:s'),
    ]);
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
        'refund'      => $this->where['refund'],
        'note'        => $this->where['note'],
        'deposit'     => (string)((int)$this->where['deposit']-(int)$this->where['refund']),
        'modified_on' => date('Y-m-d H:i:s')
    ]);

    // kirim notifikasi
    $owner_wa      = '';
    $sewa          = $this->db->from('sewa')->where(['sewa_id' => $this->where['sewa_id']])->get()->row();
    $user          = $this->db->from('user')->where(['user_id' => $sewa->created_by])->get()->row();
    $owner         = $this->db->from('user')->where(['user_id' => $sewa->pemilik_id])->get()->row();
    $user_wa       = ($user) ? $user->notelp : '';
    $message       = 'Kode Tagihan '.$sewa->kode_tagihan.', *'.($user ? $user->nama : '').'* Owner sudah mengambalikan dana deposit mu, silahkan cek detail tagihan.';
    $message_notif = 'Kode Tagihan '.$sewa->kode_tagihan.', '.($user ? $user->nama : '').' Owner sudah mengambalikan dana deposit mu, silahkan cek detail tagihan.';
    $this->notifikasi->send(array(
      'to'    => $user->user_id,
      'from'  => $owner->user_id,
      'title' => 'Check Out',
      'msg'   => $message_notif,
      'params'=> json_encode(['sewa_id' => $this->where['sewa_id'], 'isOwner' => true]),
    ));

    // kirim wa
    $this->all_library->wa(array(
      'phone'   => $user_wa,
      'message' => $message
    ));

    // insert histori sewa
    $data_histori = [
        'sewa_id'     => $this->where['sewa_id'],
        'text'        => 'Check Out',
        'status'      => '3',
        'created_by'  => $sewa->created_by,
        'created_on'  => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('histori_sewa', $data_histori);

    return $this->result;
  }

  public function checkOut() {
    $data_sewa = $this->db->from('sewa')->where([
      'properti_id' => $this->where['properti_id'],
      'created_by'  => $this->where['created_by'],
      'pemilik_id'  => $this->where['pemilik_id'],
      'status_sewa' => '3',
    ])->get()->row();
    // $updateTagihan = $this->db->where(['sewa_id' => $data_sewa->sewa_id])->update('tagihan', [
    //     'gambar_pengembalian' => (isset($this->where['gambar'])) ? $this->where['gambar'] : NULL,
    //     'modified_on'         => date('Y-m-d H:i:s'),
    // ]);
    if (isset($this->where['is_owner'])) {
        $this->result['success'] = $this->db->where(['sewa_id' => $data_sewa->sewa_id])->update('sewa', [
          'status_sewa' => '5',
          'modified_on' => date('Y-m-d H:i:s')
        ]);
    } else {
        $this->result['success'] = $this->db->where(['sewa_id' => $data_sewa->sewa_id])->update('sewa', [
            'status_sewa' => '4',
            'modified_on' => date('Y-m-d H:i:s')
        ]);
    }
    // kirim notifikasi
    $owner_wa      = '';
    $sewa          = $this->db->from('sewa')->where(['sewa_id' => $data_sewa->sewa_id])->get()->row();
    $user          = $this->db->from('user')->where(['user_id' => $sewa->created_by])->get()->row();
    $owner         = $this->db->from('user')->where(['user_id' => $sewa->pemilik_id])->get()->row();
    $owner_wa      = ($owner) ? $owner->notelp : '';
    $message       = 'Kode Tagihan '.$sewa->kode_tagihan.', *'.($user ? $user->nama : '').'* mengajukan check out, silahkan lakukan konfirmasi';
    $message_notif = 'Kode Tagihan '.$sewa->kode_tagihan.', '.($user ? $user->nama : '').' mengajukan check out, silahkan lakukan konfirmasi';
    $this->notifikasi->send(array(
      'to'    => $owner->user_id,
      'from'  => $user->user_id,
      'title' => 'Check Out',
      'msg'   => $message_notif,
      'params'=> json_encode(['sewa_id' => $data_sewa->sewa_id, 'isOwner' => true]),
    ));

    // kirim wa
    $this->all_library->wa(array(
      'phone'   => $owner_wa,
      'message' => $message
    ));

    // insert histori sewa
    $data_histori = [
        'sewa_id'     => $data_sewa->sewa_id,
        'text'        => 'Check Out',
        'status'      => '3',
        'created_by'  => $sewa->created_by,
        'created_on'  => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('histori_sewa', $data_histori);

    return $this->result;
  }

  public function terimaCheckOut() {
    $sewa    = $this->db->from('sewa')->where(['sewa_id' => $this->where['sewa_id']])->get()->row();
    $tagihan = $this->db->from('tagihan')->where(['sewa_id' => $this->where['sewa_id']])->get()->row();
    if ($sewa->status_sewa == '4') {
        $updateTagihan = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('tagihan', [
            'total_harga'         => (string)((int)$tagihan->total_harga-(int)$this->where['refund']),
            'gambar_pengembalian' => (isset($this->where['gambar'])) ? $this->where['gambar'] : NULL,
            'modified_on'         => date('Y-m-d H:i:s'),
        ]);
        $this->result['success'] = $this->db->where(['created_by' => $this->where['created_by'], 'status_sewa' => '4'])->update('sewa', [
          'refund'               => $this->where['refund'],
          'note'                 => $this->where['note'],
          'deposit'              => (string)((int)$this->where['deposit']-(int)$this->where['refund']),
          'status_sewa'          => '5',
          'tanggal_selesai_sewa' => $this->where['tanggal_sewa'],
          'modified_on'          => date('Y-m-d H:i:s')
        ]);
    } else {
        $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
          'status_sewa'          => '6',
          'tanggal_selesai_sewa' => date('Y-m-d'),
          'modified_on'          => date('Y-m-d H:i:s')
        ]);
        $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('tagihan', [
          'status_tagihan'       => '3',
          'modified_on'          => date('Y-m-d H:i:s')
        ]);
    }
    // kirim notifikasi
    $user     = $this->db->from('user')->where(['user_id' => $sewa->created_by])->get()->row();
    $owner    = $this->db->from('user')->where(['user_id' => $sewa->pemilik_id])->get()->row();
    $message  = 'Kode Tagihan '.$sewa->kode_tagihan.', Owner menerima pengajuan check out';
    $this->notifikasi->send(array(
      'to'    => $user->user_id,
      'from'  => $owner->user_id,
      'title' => 'Check Out',
      'msg'   => $message,
      'params'=> json_encode(['sewa_id' => $this->where['sewa_id'], 'isOwner' => false]),
    ));

    // management
    $properti    = $this->db->from('properti')->where(['created_by' => $sewa->pemilik_id, 'is_deleted' => '0'])->get()->row();
    $checkManage = $this->db->from('management_kos')->where('properti_id', $properti->properti_id)->get()->result();
    if ($checkManage) {
      foreach ($checkManage as $key => $value) {
        $message       = 'Kode Tagihan '.$sewa->kode_tagihan.', *'.$user->nama.'* melakukan check out';
        $message_notif = 'Kode Tagihan '.$sewa->kode_tagihan.', '.$user->nama.' melakukan check out';
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
          'params'=> json_encode(['sewa_id' => $sewa->sewa_id, 'isOwner' => false]),
        ));
      }
    }

    // insert histori sewa
    $data_histori = [
      'sewa_id'     => $this->where['sewa_id'],
      'text'        => 'Check Out',
      'status'      => '3',
      'created_by'  => $sewa->created_by,
      'created_on'  => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('histori_sewa', $data_histori);

    return $this->result;
  }

  public function batalCheckOut() {
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'status_sewa' => '3',
      'modified_on' => date('Y-m-d H:i:s')
    ]);
    // kirim notifikasi
    $sewa  = $this->db->from('sewa')->where(['sewa_id' => $this->where['sewa_id']])->get()->row();
    $user  = $this->db->from('user')->where(['user_id' => $sewa->created_by])->get()->row();
    $owner = $this->db->from('user')->where(['user_id' => $sewa->pemilik_id])->get()->row();
    $message  = 'Kode Tagihan '.$sewa->kode_tagihan.', Owner membatalkan pengajuan check out';
    $this->notifikasi->send(array(
      'to'    => $user->user_id,
      'from'  => $owner->user_id,
      'title' => 'Check Out',
      'msg'   => $message,
      'params'=> json_encode(['sewa_id' => $this->where['sewa_id'], 'isOwner' => false]),
    ));

    return $this->result;
  }

  public function setTipeKamar() {
    $this->result['success'] = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', [
      'tipe_kamar_id' => $this->data['tipe_kamar_id'],
      'kamar_id'      => null
    ]);
    return $this->result;
  }

  public function getKamar($tipe_kamar_id, $pemilik_id, $properti_id) {
    $link_kamar = URL_KAMAR.'/thumb_';

    $this->db->from('kamar');
    $this->db->join('lantai', 'lantai.lantai_id = kamar.lantai_id');
    $data = $this->db->where([
      'tipe_kamar_id'    => $tipe_kamar_id,
      'kamar.created_by' => $pemilik_id,
      'is_deleted'       => '0'
    ])->get()->result();

    foreach ($data as $key => $value) {
        $sewa = $this->db->from('sewa')->where(['sewa_id' => $this->where['sewa_id']])->get()->row();
        $this->db->from('sewa');
        $this->db->where('properti_id = ', $properti_id, FALSE)
                ->where('kamar_id = ', $value->kamar_id, FALSE)
                ->where('(status_sewa =', '0', FALSE)
                ->or_where("status_sewa = '1'", NULL, FALSE)
                ->or_where("status_sewa = '2'", NULL, FALSE)
                ->or_where("status_sewa = '3'", NULL, FALSE)
                ->or_where("status_sewa = '4'", NULL, FALSE)
                ->or_where("status_sewa = '8')", NULL, FALSE);
        $sewaAktive = $this->db->get()->row();
        $lantai = $this->db->from('lantai')->where([
            'lantai_id'   => $value->lantai_id,
            'properti_id' => $properti_id
        ])->get()->row();
        $file_name          = $this->db->select('CONCAT("'.$link_kamar.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->session_upload_id])->get()->row();
        $value->gambar_link = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
        $value->harga_f     = $this->all_library->format_harga($value->harga);
        $value->lantai_f    = ($lantai ? 'Lantai '.$lantai->lantai : '');
        $value->isSelect    = ($sewa->kamar_id == $value->kamar_id) ? true : false;
        $value->isActive    = (($sewaAktive || !$lantai) ? true : (!$value->status ? true : false));
    }
    // if ($this->where['isSelect']) {
    //   $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', ['lantai_id' => $this->where['lantai_id'], 'kamar_id' => NULL]);
    // } else {
    //   $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', ['lantai_id' => $this->where['lantai_id']]);
    // }

    return $data;
  }

  public function setKamar() { 

    $this->db->from('kamar');
    $data = $this->db->where(['kamar_id' => $this->where['kamar_id']])->get()->row();

    $update = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', ['kamar_id' => $data->kamar_id, 'harga_sewa' => $data->harga]);

    $this->result['success'] = $update;
    return $this->result;
  }

  public function setTanggal() {
    $update = $this->db->where(['sewa_id' => $this->where['sewa_id']])->update('sewa', ['tanggal_sewa' => $this->where['tanggal_sewa']]);
    $this->result['success'] = $update;
    return $this->result;
  }

  public function updateTanggal() {
    $hari   = cal_days_in_month(CAL_GREGORIAN, date('m', strtotime($this->data['tanggal_sewa'])), date('Y', strtotime($this->data['tanggal_selesai_sewa'])));
    $tgl1   = strtotime($this->data['tanggal_sewa']); 
    $tgl2   = strtotime($this->data['tanggal_selesai_sewa']); 
    $jarak  = $tgl2-$tgl1;
    $selisi = $jarak/60/60/24;

    $kamar  = $this->db->from('kamar')->where([
      'kamar_id' => $this->where['sewa']['kamar_id']
    ])->get()->row();

    $properti  = $this->db->from('properti')->where([
      'properti_id' => $this->where['sewa']['properti_id']
    ])->get()->row();

    $total_harga        = 0;
    $harga_parkir_motor = (int)$properti->harga_parkir_motor;
    $harga_parkir_mobil = (int)$properti->harga_parkir_mobil;
    $kapasitas          = (int)$this->where['sewa']['kapasitas'];
    $tambahan_biaya     = (int)$properti->tambahan_biaya;

    if ($this->where['sewa']['is_parkir'] == '1') {
      $harga_parkir_motor = (int)($selisi/$hari*$harga_parkir_motor);
      $total_harga = (int)($harga_parkir_motor);
    } elseif ($this->where['sewa']['is_parkir'] == '2') {
      $harga_parkir_mobil = (int)($selisi/$hari*$harga_parkir_mobil);
      $total_harga = (int)($harga_parkir_mobil);
    } elseif ($this->where['sewa']['is_parkir'] == '3') {
      $total_harga = (int)($this->where['sewa']['harga_parkir_motor']+$this->where['sewa']['harga_parkir_mobil']);
    }

    if ($kapasitas == 2) {
      $tambahan_biaya = (int)($selisi/$hari*$tambahan_biaya);
      $total_harga    = (int)$total_harga+(int)$tambahan_biaya;
    }
    
    $total_harga    = (int)$total_harga+(int)$this->where['sewa']['deposit'];

    if ($hari != $selisi) {
      $harga_sewa = (int)($selisi/$hari*$kamar->harga);
      $update = $this->db->where(['sewa_id' => $this->where['sewa']['sewa_id']])->update('sewa', [
        'tanggal_sewa'         => $this->data['tanggal_sewa'],
        'tanggal_selesai_sewa' => $this->data['tanggal_selesai_sewa'],
        'harga_parkir_motor'   => $harga_parkir_motor,
        'harga_parkir_mobil'   => $harga_parkir_mobil,
        'tambahan_biaya'       => $tambahan_biaya,
        'harga_sewa'           => $harga_sewa,
      ]);
    } else {
      $harga_sewa = $kamar->harga;
      $update = $this->db->where(['sewa_id' => $this->where['sewa']['sewa_id']])->update('sewa', [
        'tanggal_sewa'         => $this->data['tanggal_sewa'],
        'tanggal_selesai_sewa' => $this->data['tanggal_selesai_sewa'],
        'harga_parkir_motor'   => $properti->harga_parkir_motor,
        'harga_parkir_mobil'   => $properti->harga_parkir_mobil,
        'tambahan_biaya'       => $properti->tambahan_biaya,
        'harga_sewa'           => $harga_sewa
      ]);
    }
    
    $this->db->where(['sewa_id' => $this->where['sewa']['sewa_id']])->update('tagihan', [
      'total_harga' => (int)$total_harga+(int)($harga_sewa)
    ]);

    $this->result['success'] = $update;
    return $this->result;
  }

  public function setTagihan() {
    $harga_sewa             = $this->data['tagihan']['harga_sewa'];
    $tanggal_sewa           = $this->data['tagihan']['tanggal_sewa'];
    $tanggal_selesai_sewa   = date('Y-m-d', strtotime('+1 month', strtotime($tanggal_sewa)));
    $data['sewa_id']        = $this->data['tagihan']['sewa_id'];
    $data['kode_tagihan']   = 'TZY'.rand(1000000, 9999999);
    $data['total_harga']    = $this->data['tagihan']['total_harga'];
    $data['created_by']     = $this->data['tagihan']['created_by'];
    $data['rekening_id']    = '1';
    $data['status_tagihan'] = '0';
    $data['created_on']     = date('Y-m-d H:i:s');
    $this->db->insert('tagihan', $data);
    $tagihan_id = $this->db->insert_id();

    if ($this->data['tagihan']['is_parkir'] == '0') {
        $data_sewa['harga_parkir_motor'] = '0';
        $data_sewa['harga_parkir_mobil'] = '0';
    } elseif ($this->data['tagihan']['is_parkir'] == '1') {
        $data_sewa['harga_parkir_mobil'] = '0';
    } elseif ($this->data['tagihan']['is_parkir'] == '2') {
        $data_sewa['harga_parkir_motor'] = '0';
    }

    if (isset($this->data['tagihan']['kapasitas']) && $this->data['tagihan']['kapasitas'] == '1') {
        $data_sewa['tambahan_biaya'] = '0';
        $data_sewa['penghuni']       = NULL;
    }

    $data_sewa['kode_tagihan']         = $data['kode_tagihan'];
    $data_sewa['status_sewa']          = '1'; 
    $data_sewa['tanggal_sewa']         = $tanggal_sewa;
    $data_sewa['tanggal_selesai_sewa'] = $tanggal_selesai_sewa;
    $data_sewa['modified_on']          = date('Y-m-d H:i:s'); 

    $update = $this->db->where(['sewa_id' => $this->data['tagihan']['sewa_id']])->update('sewa', $data_sewa);
    // insert histori sewa
    $data_histori = [
      'sewa_id'     => $this->data['tagihan']['sewa_id'],
      'text'        => 'Diproses',
      'status'      => '0',
      'created_by'  => $this->data['tagihan']['created_by'],
      'created_on'  => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('histori_sewa', $data_histori);
    // kirim wa
    $owner_wa      = '';
    $owner         = $this->db->from('user')->where('user_id', $this->data['tagihan']['pemilik_id'])->get()->row();
    $user          = $this->db->from('user')->where('user_id', $this->data['tagihan']['created_by'])->get()->row();
    $owner_wa      = ($owner) ? $owner->notelp : '';
    $message       = 'Kode Tagihan '.$data['kode_tagihan'].', *'.($user ? $user->nama : '').'* melakukan checkin, silahkan konfirmasi apabila pembayaran sudah diterima.';
    $message_notif = 'Kode Tagihan '.$data['kode_tagihan'].', '.($user ? $user->nama : '').' melakukan checkin, silahkan konfirmasi apabila pembayaran sudah diterima.';

    $this->all_library->wa(array(
      'phone'   => $owner_wa,
      'message' => $message
    ));
    // kirim notifikasi
    $this->notifikasi->send(array(
      'to'    => $owner->user_id,
      'from'  => $user->user_id,
      'title' => 'Sewa Kost',
      'msg'   => $message_notif,
      'params'=> json_encode(['sewa_id' => $this->data['tagihan']['sewa_id'], 'isOwner' => true]),
    ));


    // management
    $properti    = $this->db->from('properti')->where(['created_by' => $this->data['tagihan']['pemilik_id'], 'is_deleted' => '0'])->get()->row();
    $checkManage = $this->db->from('management_kos')->where('properti_id', $properti->properti_id)->get()->result();
    if ($checkManage) {
      $message       = 'Kode Tagihan '.$data['kode_tagihan'].', *'.($user ? $user->nama : '').'* melakukan checkin, menunggu konfirmasi owner.';
      $message_notif = 'Kode Tagihan '.$data['kode_tagihan'].', '.($user ? $user->nama : '').' melakukan checkin, menunggu konfirmasi owner.';
      foreach ($checkManage as $key => $value) {
        $manage_wa = $this->db->from('user')->where('user_id', $value->created_by)->get()->row();
        $this->all_library->wa(array(
          'phone'   => ($manage_wa) ? $manage_wa->notelp : '',
          'message' => $message
        ));
        // kirim notifikasi
        $this->notifikasi->send(array(
          'to'    => $manage_wa->user_id,
          'from'  => $user->user_id,
          'title' => 'Sewa Kost',
          'msg'   => $message_notif,
          'params'=> json_encode(['sewa_id' => $this->data['tagihan']['sewa_id'], 'isOwner' => false]),
        ));
      }
    }

    $this->db->where([
        'kamar_id'    => $this->data['tagihan']['kamar_id'],
        'status_sewa' => '0',
    ]);
    $this->db->delete('sewa');
    
    $this->result['data']    = $tagihan_id;
    $this->result['success'] = $update;
    return $this->result;
  }

  public function getSewa() {
    $link_kamar    = URL_KAMAR.'/thumb_';
    $link_properti = URL_PROPERTI.'/thumb_';
    $keyword       = $this->where['keyword'];
    if (isset($this->where['pemilik_id'])) {
      if ($this->where['role'] == '3') {
            $this->db->from('management_kos');
            $this->db->join('properti', 'properti.properti_id = management_kos.properti_id');
            $user = $this->db->where('management_kos.created_by', $this->where['pemilik_id'])->get()->row();
            $user_id = (isset($user->created_by)) ? $user->created_by : '';
        } else {
            $user_id = $this->where['pemilik_id'];
        }
        if ($keyword) {
            $where = 'sewa.pemilik_id = "'.$this->where['pemilik_id'].'" AND
            (sewa.kode_tagihan LIKE "%'.$keyword.'%"
            OR kamar.nomor_kamar LIKE "%'.$keyword.'%"
            OR properti.nama_properti LIKE "%'.$keyword.'%"
            OR sewa.harga_sewa LIKE "%'.$keyword.'%")';
        } else {
            $where = 'sewa.pemilik_id = "'.$user_id.'"';
        }
    } else {
        if ($keyword) {
            $where = 'sewa.created_by = "'.$this->where['created_by'].'" AND
            (sewa.kode_tagihan LIKE "%'.$keyword.'%"
            OR kamar.nomor_kamar LIKE "%'.$keyword.'%"
            OR properti.nama_properti LIKE "%'.$keyword.'%"
            OR sewa.harga_sewa LIKE "%'.$keyword.'%")';
        } else {
            $where = 'sewa.created_by = "'.$this->where['created_by'].'"';
        }
    }
    $this->db->select('*, properti.session_upload_id AS properti_upload, kamar.session_upload_id AS kamar_upload, sewa.deposit AS sewa_deposit');
    $this->db->from('sewa');
    $this->db->join('properti', 'properti.properti_id = sewa.properti_id');
    $this->db->join('kamar', 'kamar.kamar_id = sewa.kamar_id');
    $this->db->join('tipe_kamar', 'tipe_kamar.tipe_kamar_id = kamar.tipe_kamar_id');
    $this->db->where($this->where['filter']);
    $data = $this->db->where($where)->order_by('sewa.created_on DESC')->get()->result();
    foreach ($data as $key => $value) {
      $total_bayar                 = 0;
      $file_name                   = $this->db->select('CONCAT("'.$link_properti.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->properti_upload])->get()->row();
      $value->gambar_properti      = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
      $file_name                   = $this->db->select('CONCAT("'.$link_kamar.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->kamar_upload])->get()->row();
      $value->gambar_kamar         = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
      $value->harga_f              = $this->all_library->format_harga($value->harga);
      $value->harga_parkir_motor_f = $this->all_library->format_harga($value->harga_parkir_motor);
      $value->harga_parkir_mobil_f = $this->all_library->format_harga($value->harga_parkir_mobil);
      if ($value->is_parkir == '1') {
          $total_bayar = $value->harga_parkir_motor;
      } elseif ($value->is_parkir == '2') {
          $total_bayar = $value->harga_parkir_mobil;
      } elseif ($value->is_parkir == '3') {
          $total_bayar = $value->harga_parkir_motor+$value->harga_parkir_mobil;
      }
      if ($value->kapasitas == '2') {
          $total_bayar = $total_bayar+$value->tambahan_biaya;
      }
      $total_bayar                 = $total_bayar+$value->harga_sewa+$value->sewa_deposit;
      $value->total_bayar_f        = $this->all_library->format_harga($total_bayar);
      $value->status_sewa_f        = $this->all_library->status_sewa($value->status_sewa);
      $value->checkin              = $this->all_library->date($value->tanggal_sewa, true);
      $value->checkout             = $this->all_library->date($value->tanggal_selesai_sewa, true);
      $value->priode               = $this->all_library->lama_sewa($value->lama_sewa);
      $histori_sewa                = $this->db->from('histori_sewa')->where(['sewa_id' => $value->sewa_id, 'status' => '1'])->get()->row();
      $value->tanggal_bayar        = (isset($histori_sewa)) ? $this->all_library->format_date($histori_sewa->created_on, true) : NULL;
      $value->isStatus             = ($value->status_sewa == '1' OR $value->status_sewa == '2' OR $value->status_sewa == '4') ? true : false;

    }
    $this->result['data']      = $data;
    $this->result['totaldata'] = count($data);
    return $this->result;
  }

  public function getDataProses() {
    if (isset($this->where['pemilik_id'])) {
        $data = $this->db->select('*, user.nama_pemilik AS pemilik')
              ->from('user')
              ->join('nomor_rekening', 'nomor_rekening.nama_bank = user.bank')
              ->where(['user.user_id' => $this->where['pemilik_id']])->get()->row();
        if (!isset($data)) {
            $data = $this->db->from('nomor_rekening')->where(['role' => '1'])->get()->row();
        }
    } else {
        $data = $this->db->from('nomor_rekening')->where(['role' => '1'])->get()->row();
    }

    $data->gambar_link = $data->gambar;
    $data->rekening    = $this->db->from('nomor_rekening')->where(['role' => '2'])->get()->result();
    $this->result['data'] = $data;
    return $this->result;
  }

  public function simpanPembayaran() {
    $dataTagihan = [
      'nama_pengirim'     => $this->data['nama_pengirim'],
      'rekening_id'       => $this->data['rekening_id'],
      'gambar_konfirmasi' => $this->data['gambar'],
      'status_tagihan'    => '1',
      'modified_on'       => date('Y-m-d H:i:s'),
    ];
    $this->db->where(['sewa_id' => $this->data['sewa_id']]);
    $updateTagihan = $this->db->update('tagihan', [
      'nama_pengirim'     => $this->data['nama_pengirim'],
      'rekening_id'       => $this->data['rekening_id'],
      'gambar_konfirmasi' => $this->data['gambar'],
      'status_tagihan'    => '2',
      'modified_on'       => date('Y-m-d H:i:s'),
    ]);
    $this->db->where(['sewa_id' => $this->data['sewa_id']]);
    $updateSewa = $this->db->update('sewa', [
      'status_sewa'       => '2',
      'modified_on'       => date('Y-m-d H:i:s'),
    ]);
    // kirim notifikasi
    $owner_wa       = '';
    $sewa           = $this->db->from('sewa')->where(['sewa_id' => $this->data['sewa_id']])->get()->row();
    $user           = $this->db->from('user')->where(['user_id' => $sewa->created_by])->get()->row();
    $owner          = $this->db->from('user')->where(['user_id' => $sewa->pemilik_id])->get()->row();
    $owner_wa       = ($owner) ? $owner->notelp : '';
    $message        = 'Kode Tagihan '.$sewa->kode_tagihan.', *'.($user ? $user->nama : '').'* sudah melakukan pembayaran, silahkan konfirmasi pembayaran.';
    $message_notif  = 'Kode Tagihan '.$sewa->kode_tagihan.', '.($user ? $user->nama : '').' sudah melakukan pembayaran, silahkan konfirmasi pembayaran.';
    $this->notifikasi->send(array(
      'to'    => $owner->user_id,
      'from'  => $user->user_id,
      'title' => 'Pembayaran',
      'msg'   => $message_notif,
      'params'=> json_encode(['sewa_id' => $this->data['sewa_id'], 'isOwner' => true]),
    ));
    // kirim wa
    $this->all_library->wa(array(
      'phone'   => $owner_wa,
      'message' => $message
    ));

    // insert histori sewa
    $data_histori = [
      'sewa_id'     => $this->data['sewa_id'],
      'text'        => 'Dibayar',
      'status'      => '1',
      'created_by'  => $sewa->created_by,
      'created_on'  => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('histori_sewa', $data_histori);

    $this->result['success'] = true;
    return $this->result;
  }

  public function simpanKonfirmasi() {
    $this->db->where(['sewa_id' => $this->data['sewa_id']]);
    $updateTagihan = $this->db->update('tagihan', [
      'status_tagihan'    => '2',
      'modified_on'    => date('Y-m-d H:i:s'),
    ]);
    $this->db->where(['sewa_id' => $this->data['sewa_id']]);
    $updateSewa = $this->db->update('sewa', [
      'status_sewa'       => '3',
      'modified_on' => date('Y-m-d H:i:s'),
    ]);
    // kirim notifikasi
    $sewa  = $this->db->from('sewa')->where(['sewa_id' => $this->data['sewa_id']])->get()->row();
    $user  = $this->db->from('user')->where(['user_id' => $sewa->created_by])->get()->row();
    $owner = $this->db->from('user')->where(['user_id' => $sewa->pemilik_id])->get()->row();
    $message  = 'Kode Tagihan '.$sewa->kode_tagihan.', Owner sudah menerima pembayaran kamu';
    $this->notifikasi->send(array(
      'to'    => $user->user_id,
      'from'  => $owner->user_id,
      'title' => 'Check In',
      'msg'   => $message,
      'params'=> json_encode(['sewa_id' => $this->data['sewa_id'], 'isOwner' => false]),
    ));

    // insert histori sewa
    $data_histori = [
      'sewa_id'     => $this->data['sewa_id'],
      'text'        => 'Check In',
      'status'      => '2',
      'created_by'  => $sewa->created_by,
      'created_on'  => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('histori_sewa', $data_histori);

    $this->result['success'] = true;
    return $this->result;
  }

  public function batalSewaOwner() {
    $this->db->where(['sewa_id' => $this->data['sewa_id']]);
    $updateTagihan = $this->db->update('tagihan', [
      'status_tagihan' => '3',
      'modified_on'    => date('Y-m-d H:i:s'),
    ]);
    $this->db->where(['sewa_id' => $this->data['sewa_id']]);
    $updateSewa = $this->db->update('sewa', [
      'status_sewa' => '6',
      'modified_on' => date('Y-m-d H:i:s'),
    ]);
    // kirim notifikasi
    $sewa  = $this->db->from('sewa')->where(['sewa_id' => $this->data['sewa_id']])->get()->row();
    $user  = $this->db->from('user')->where(['user_id' => $sewa->created_by])->get()->row();
    $owner = $this->db->from('user')->where(['user_id' => $sewa->pemilik_id])->get()->row();
    $message        = 'Kode Tagihan '.$sewa->kode_tagihan.', *'.($user ? $user->nama : '').'*, owner telah membatalkan sewa.';
    $message_notif  = 'Kode Tagihan '.$sewa->kode_tagihan.', '.($user ? $user->nama : '').', owner telah membatalkan sewa.';
    $this->notifikasi->send(array(
      'to'    => $user->user_id,
      'from'  => $owner->user_id,
      'title' => 'Pembatalan Check In',
      'msg'   => $message_notif,
      'params'=> json_encode(['sewa_id' => $this->data['sewa_id'], 'isOwner' => false]),
    ));

    // management
    $properti    = $this->db->from('properti')->where(['created_by' => $sewa->pemilik_id, 'is_deleted' => '0'])->get()->row();
    $checkManage = $this->db->from('management_kos')->where('properti_id', $properti->properti_id)->get()->result();
    if ($checkManage) {
      foreach ($checkManage as $key => $value) {
        $manage_wa = $this->db->from('user')->where('user_id', $value->created_by)->get()->row();
        $this->all_library->wa(array(
          'phone'   => ($manage_wa) ? $manage_wa->notelp : '',
          'message' => $message
        ));
        // kirim notifikasi
        $this->notifikasi->send(array(
          'to'    => $manage_wa->user_id,
          'from'  => $owner->user_id,
          'title' => 'Pembatalan Check In',
          'msg'   => $message_notif,
          'params'=> json_encode(['sewa_id' => $sewa->sewa_id, 'isOwner' => false]),
        ));
      }
    }

    // insert histori sewa
    $data_histori = [
      'sewa_id'     => $this->data['sewa_id'],
      'text'        => 'Batal',
      'status'      => '5',
      'created_by'  => $sewa->created_by,
      'created_on'  => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('histori_sewa', $data_histori);

    $this->result['success'] = true;
    return $this->result;
  }

  public function batalSewa() {
    $this->db->where(['sewa_id' => $this->data['sewa_id']]);
    $updateTagihan = $this->db->update('tagihan', [
      'status_tagihan' => '3',
      'modified_on'    => date('Y-m-d H:i:s'),
    ]);
    $this->db->where(['sewa_id' => $this->data['sewa_id']]);
    $updateSewa = $this->db->update('sewa', [
      'status_sewa' => '6',
      'modified_on' => date('Y-m-d H:i:s'),
    ]);
    // kirim notifikasi
    $sewa  = $this->db->from('sewa')->where(['sewa_id' => $this->data['sewa_id']])->get()->row();
    $user  = $this->db->from('user')->where(['user_id' => $sewa->created_by])->get()->row();
    $owner = $this->db->from('user')->where(['user_id' => $sewa->pemilik_id])->get()->row();
    $message        = 'Kode Tagihan '.$sewa->kode_tagihan.', *'.($user ? $user->nama : '').'* membatalkan sewa.';
    $message_notif  = 'Kode Tagihan '.$sewa->kode_tagihan.', '.($user ? $user->nama : '').' membatalkan sewa.';
    $this->notifikasi->send(array(
      'to'    => $owner->user_id,
      'from'  => $user->user_id,
      'title' => 'Pembatalan Check In',
      'msg'   => $message_notif,
      'params'=> json_encode(['sewa_id' => $this->data['sewa_id'], 'isOwner' => false]),
    ));

    // management
    $properti    = $this->db->from('properti')->where(['created_by' => $sewa->pemilik_id, 'is_deleted' => '0'])->get()->row();
    $checkManage = $this->db->from('management_kos')->where('properti_id', $properti->properti_id)->get()->result();
    if ($checkManage) {
      foreach ($checkManage as $key => $value) {
        $manage_wa = $this->db->from('user')->where('user_id', $value->created_by)->get()->row();
        $this->all_library->wa(array(
          'phone'   => ($manage_wa) ? $manage_wa->notelp : '',
          'message' => $message
        ));
        // kirim notifikasi
        $this->notifikasi->send(array(
          'to'    => $manage_wa->user_id,
          'from'  => $user->user_id,
          'title' => 'Pembatalan Check In',
          'msg'   => $message_notif,
          'params'=> json_encode(['sewa_id' => $sewa->sewa_id, 'isOwner' => false]),
        ));
      }
    }

    // insert histori sewa
    $data_histori = [
      'sewa_id'     => $this->data['sewa_id'],
      'text'        => 'Batal',
      'status'      => '5',
      'created_by'  => $sewa->created_by,
      'created_on'  => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('histori_sewa', $data_histori);

    $this->result['success'] = true;
    return $this->result;
  }
  
  public function batalKonfirmasi() {
    $tagihan = $this->db->from('tagihan')->where(['sewa_id' => $this->data['sewa_id']])->get()->row();
    $this->db->where(['sewa_id' => $this->data['sewa_id']]);
    $updateTagihan = $this->db->update('tagihan', [
      'total_harga'         => (string)((int)$tagihan->total_harga-(int)$this->data['refund']),
      'status_tagihan'      => '3',
      'gambar_pengembalian' => (isset($this->data['gambar'])) ? $this->data['gambar'] : NULL,
      'modified_on'         => date('Y-m-d H:i:s'),
    ]);
    $this->db->where(['sewa_id' => $this->data['sewa_id']]);
    $updateSewa = $this->db->update('sewa', [
      'refund'               => $this->data['refund'],
      'note'                 => $this->data['note'],
      'deposit'              => (string)((int)$this->data['deposit']-(int)$this->data['refund']),
      'status_sewa'          => '6',
      'tanggal_selesai_sewa' => date('Y-m-d H:i:s'),
      'modified_on'          => date('Y-m-d H:i:s'),
    ]);
    // kirim notifikasi
    $sewa  = $this->db->from('sewa')->where(['sewa_id' => $this->data['sewa_id']])->get()->row();
    $user  = $this->db->from('user')->where(['user_id' => $sewa->created_by])->get()->row();
    $owner = $this->db->from('user')->where(['user_id' => $sewa->pemilik_id])->get()->row();
    $message  = 'Kode Tagihan '.$sewa->kode_tagihan.', Owner sudah membatalkan check in kosan, silahkan hubungi owner untuk pengembalian dana.';
    $this->notifikasi->send(array(
      'to'    => $user->user_id,
      'from'  => $owner->user_id,
      'title' => 'Pembatalan Check In',
      'msg'   => $message,
      'params'=> json_encode(['sewa_id' => $this->data['sewa_id'], 'isOwner' => false]),
    ));

    // management
    $properti    = $this->db->from('properti')->where(['created_by' => $sewa->pemilik_id, 'is_deleted' => '0'])->get()->row();
    $checkManage = $this->db->from('management_kos')->where('properti_id', $properti->properti_id)->get()->result();
    if ($checkManage) {
      foreach ($checkManage as $key => $value) {
        $message   = 'Kode Tagihan '.$sewa->kode_tagihan.', Owner membatalkan check in';
        $manage_wa = $this->db->from('user')->where('user_id', $value->created_by)->get()->row();
        $this->all_library->wa(array(
          'phone'   => ($manage_wa) ? $manage_wa->notelp : '',
          'message' => $message
        ));
        // kirim notifikasi
        $this->notifikasi->send(array(
          'to'    => $manage_wa->user_id,
          'from'  => $owner->user_id,
          'title' => 'Pembatalan Check In',
          'msg'   => $message,
          'params'=> json_encode(['sewa_id' => $sewa->sewa_id, 'isOwner' => false]),
        ));
      }
    }

    // insert histori sewa
    $data_histori = [
      'sewa_id'     => $this->data['sewa_id'],
      'text'        => 'Batal',
      'status'      => '5',
      'created_by'  => $sewa->created_by,
      'created_on'  => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('histori_sewa', $data_histori);

    $this->result['success'] = true;
    return $this->result;
  }

  public function detailSewa() {
    $link_kamar    = URL_KAMAR.'/thumb_';
    $link_properti = URL_PROPERTI.'/thumb_';
    $link_profile  = URL_PROFILE.'/thumb_';
    $data['sewa']  = $this->db->from('sewa')
                    ->join('tagihan', 'tagihan.sewa_id = sewa.sewa_id')
                    ->where(['sewa.sewa_id' => $this->where['sewa_id']])->get()->row();
    $data['sewa']->tipe_kamar    = $this->db->from('tipe_kamar')->where(['tipe_kamar_id' => $data['sewa']->tipe_kamar_id])->get()->row()->tipe_kamar;
    $data['sewa']->status_sewa_f = $this->all_library->status_sewa($data['sewa']->status_sewa);
    $data['sewa']->gambar_link   = ($data['sewa']->gambar_konfirmasi) ? URL_TAGIHAN.'/thumb_'.$data['sewa']->gambar_konfirmasi : null;
    $data['sewa']->gambar_link_dana = ($data['sewa']->gambar_pengembalian) ? URL_TAGIHAN.'/thumb_'.$data['sewa']->gambar_pengembalian : null;
    $data['sewa']->gambar        = 'tagihan/thumb_'.$data['sewa']->gambar_konfirmasi;
    $data['sewa']->gambar_dana   = 'tagihan/thumb_'.$data['sewa']->gambar_pengembalian;
    $data['sewa']->mulai         = $this->all_library->format_date($data['sewa']->tanggal_sewa, true, true, false);
    $data['sewa']->selesai       = $this->all_library->format_date($data['sewa']->tanggal_selesai_sewa, true, true, false);
    $data['sewa']->range         = [
        'startDate' => date_format(date_create("2013-03-15") ,DATE_ATOM),
        'endDate'   => date_format(date_create("2013-03-15") ,DATE_ATOM)
    ];
    $data['sewa']->deposit_f     = $this->all_library->format_harga($data['sewa']->deposit);
    $data['sewa']->priode        = $this->all_library->lama_sewa($data['sewa']->lama_sewa);
    $data['sewa']->isSewa        = $this->db->from('sewa')->where(['kamar_id' => $data['sewa']->kamar_id, 'created_by' => $data['sewa']->created_by])->where('(status_sewa = "3" OR status_sewa = "8")')->get()->num_rows();
    $data['sewa']->isCheckOut    = $this->db->from('sewa')->where(['kamar_id' => $data['sewa']->kamar_id, 'status_sewa' => '4', 'created_by' => $data['sewa']->created_by])->get()->num_rows();
    $data['sewa']->harga_sewa_f  = $this->all_library->format_harga($data['sewa']->harga_sewa);
    $this->db->select('*, kamar.session_upload_id AS kamar_upload_id, properti.session_upload_id AS properti_upload_id');
    $this->db->from('properti');
    $this->db->join('lantai', 'lantai.properti_id = properti.properti_id');
    $this->db->join('kamar',  'kamar.lantai_id    = lantai.lantai_id');
    $data['properti']                = $this->db->where(['kamar.kamar_id' => $data['sewa']->kamar_id])->get()->row();
    $file_name                       = $this->db->select('CONCAT("'.$link_kamar.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $data['properti']->kamar_upload_id])->get()->row();
    $data['properti']->gambar_link   = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
    $file_name                       = $this->db->select('CONCAT("'.$link_properti.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $data['properti']->properti_upload_id])->get()->row();
    $data['properti']->gambar_kamar  = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
    $data['properti']->harga_f       = $this->all_library->format_harga($data['properti']->harga);
    $data['properti']->modified_on_f = $this->all_library->format_date($data['properti']->modified_on, false);
    $data['user']                    = $this->db->from('user')->where(['user_id' => $data['sewa']->created_by])->get()->row();
    $data['user']->gambar_link       = ($data['user']->user_img) ? $link_profile.$data['user']->user_img : null;
    $data['histori_sewa']            = $this->db->from('histori_sewa')->where(['sewa_id' => $this->where['sewa_id']])->order_by('created_on', 'DESC')->get()->result();
    foreach ($data['histori_sewa'] as $key => $value) {
      $value->created_on_f = $this->all_library->format_date($value->created_on, false);
      $value->status_f     = $this->all_library->histori_sewa($value->status);
    }
    $this->result['data'] = $data;
    return $this->result;
  }

  public function getTagihan() {
    $link_kamar                 = URL_KAMAR.'/thumb_';
    $data                       = $this->db->from('tagihan')->join('sewa', 'sewa.sewa_id = tagihan.sewa_id')->where(['tagihan.tagihan_id' => $this->where['tagihan_id']])->get()->row();
    $data->pembayaran           = $this->db->select('*, user.nama_pemilik AS pemilik')
                                    ->from('user')
                                    ->join('nomor_rekening', 'nomor_rekening.nama_bank = user.bank')
                                    ->where(['user.user_id' => $data->pemilik_id])->get()->row();
    if (!isset($data->pembayaran)) {
        $data->pembayaran = $this->db->from('nomor_rekening')->where(['role' => '1'])->get()->row();
    }
    $data->harga_sewa_f         = ($data->harga_sewa) ? $this->all_library->format_harga($data->harga_sewa) : '-';
    $data->deposit_f            = ($data->deposit) ? $this->all_library->format_harga($data->deposit) : '-';
    $data->harga_parkir_motor_f = '-';
    if ($data->harga_parkir_motor && ($data->is_parkir == '1' || $data->is_parkir == '3')) {
      $data->harga_parkir_motor_f = $this->all_library->format_harga($data->harga_parkir_motor);
    }
    $data->harga_parkir_mobil_f = '-';
    if ($data->harga_parkir_mobil && ($data->is_parkir == '2' || $data->is_parkir == '3')) {
        $data->harga_parkir_mobil_f = $this->all_library->format_harga($data->harga_parkir_mobil);
    }
    $data->tambahan_biaya_f = '-';
    if ($data->tambahan_biaya && $data->kapasitas == '2') {
        $data->tambahan_biaya_f = $this->all_library->format_harga($data->tambahan_biaya);
    }
    
    $data->total_harga_f           = ($data->total_harga) ? $this->all_library->format_harga($data->total_harga) : '-';
    $data->pembayaran->gambar_link = $data->pembayaran->gambar;
    $this->result['data'] = $data;
    return $this->result;
  }

  public function updateTagihan($sewa_id) {
    $data = $this->db->from('tagihan')->join('sewa', 'sewa.sewa_id = tagihan.sewa_id')->where(['tagihan.sewa_id' => $sewa_id])->get()->row();
    $data->pembayaran           = $this->db->from('nomor_rekening')->where(['rekening_id' => $data->rekening_id])->get()->row();
    $data->harga_sewa_f         = $this->all_library->format_harga($data->harga_sewa);
    $total_harga                = 0;
    
    if ($data->deposit) {
        $total_harga = $total_harga+(int)$data->deposit;
    }

    if ($data->harga_parkir_motor && ($data->is_parkir == '1' || $data->is_parkir == '3')) {
        $total_harga = $total_harga+(int)$data->harga_parkir_motor;
    }

    if ($data->harga_parkir_mobil && ($data->is_parkir == '2' || $data->is_parkir == '3')) {
        $total_harga = $total_harga+(int)$data->harga_parkir_mobil;
    }

    if ($data->kapasitas > '1') {
        $total_harga = $total_harga+(int)$data->tambahan_biaya;
    }

    $total_harga = $total_harga+(int)$data->harga_sewa;
    
    $update = $this->db->where([
        'sewa_id' => $sewa_id
    ])->update('tagihan', [
        'total_harga' => $total_harga
    ]);

    return $update;
  }

  public function detailTagihan() {
    $link_kamar    = URL_KAMAR.'/thumb_';
    $data = $this->db->select('
        *, sewa.deposit, 
        sewa.harga_parkir_mobil, 
        sewa.harga_parkir_motor,
        sewa.tambahan_biaya,
    ')
    ->from('tagihan')
    ->join('sewa', 'sewa.sewa_id = tagihan.sewa_id')
    ->join('properti', 'properti.properti_id = sewa.properti_id')
    ->join('tipe_kamar', 'tipe_kamar.tipe_kamar_id = sewa.tipe_kamar_id')
    ->join('kamar', 'kamar.kamar_id = sewa.kamar_id')
    ->where(['tagihan.tagihan_id' => $this->where['tagihan_id']])->get()->row();
                            
    $data->pembayaran = $this->db->select('*, user.nama_pemilik AS pemilik')
                      ->from('user')
                      ->join('nomor_rekening', 'nomor_rekening.nama_bank = user.bank')
                      ->where(['user.user_id' => $data->pemilik_id])->get()->row();

    // $data->pembayaran       = $this->db->from('nomor_rekening')->where(['role' => '1'])->get()->row();
    $data->status_tagihan_f = $this->all_library->status_tagihan($data->status_tagihan);
    $history                = $this->db->from('histori_sewa')->where([
        'sewa_id' => $data->sewa_id,
        'status' => '1'
    ])->get()->row();
    $data->tanggal_bayar        = (isset($history)) ? $this->all_library->format_date($history->created_on, true) : false;
    $data->harga_parkir_motor_f = '-';
    $data->harga_parkir_mobil_f = '-';
    if ($data->is_parkir == '1') {
      $data->harga_parkir_motor_f = $this->all_library->format_harga($data->harga_parkir_motor);
      $data->harga_parkir_mobil   = '0';
    } elseif ($data->is_parkir == '2') {
        $data->harga_parkir_mobil_f = $this->all_library->format_harga($data->harga_parkir_mobil);
        $data->harga_parkir_motor   = '0';
    } elseif ($data->is_parkir == '3') {
        $data->harga_parkir_mobil_f = $this->all_library->format_harga($data->harga_parkir_mobil);
        $data->harga_parkir_motor_f = $this->all_library->format_harga($data->harga_parkir_motor);
    } else{
        $data->harga_parkir_mobil   = '0';
        $data->harga_parkir_motor   = '0';
    }
    $data->tambahan_biaya_f = '-';
    if ($data->kapasitas > '1') {
        $data->tambahan_biaya_f = $this->all_library->format_harga($data->tambahan_biaya);
    }

    $data->deposit_f              = ($data->deposit != '0') ? $this->all_library->format_harga($data->deposit) : '-';
    $data->refund_f               = ($data->refund != '0') ? '- '.$this->all_library->format_harga($data->refund) : '-';
    $data->tanggal_sewa_f         = $this->all_library->format_date($data->tanggal_sewa, true, false, false);
    $data->tanggal_selesai_sewa_f = $this->all_library->format_date($data->tanggal_selesai_sewa, true, false, false);
    $data->total_harga_sewa_f     = $this->all_library->format_harga($data->harga_sewa);
    $data->total_harga_f          = $this->all_library->format_harga($data->total_harga);
    $this->result['data'] = $data;
    return $this->result;
  }

  public function listKamar() {
    $link_kamar = URL_KAMAR.'/thumb_';

    $this->db->select('*, kamar.session_upload_id AS kamar_upload_id');
    $this->db->from('properti');
    $this->db->join('lantai', 'lantai.properti_id = properti.properti_id');
    $this->db->join('kamar', 'kamar.lantai_id = lantai.lantai_id');
    $this->db->join('tipe_kamar', 'tipe_kamar.tipe_kamar_id = kamar.tipe_kamar_id');
    $data_kamar = $this->db->where([
        'properti.properti_id' => $this->where['properti_id'],
        'properti.created_by'  => $this->where['pemilik_id'],
        'kamar.status'         => '1'
    ])->order_by('tipe_kamar.tipe_kamar_id ASC, kamar.nomor_kamar ASC')->get()->result();
    foreach ($data_kamar as $key => $value) {
        $file_upload        = $this->db->select('CONCAT("'.$link_kamar.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->kamar_upload_id])->get()->row();
        $value->img_link    = (isset($file_upload)) ? $file_upload->gambar_link : '';
        $value->harga_f     = $this->all_library->format_harga($value->harga);
        $value->is_selected = ($value->kamar_id == $this->where['kamar_id']) ? true : false;
        $sewa               = $this->db->from('sewa')->where([
            'kamar_id'       => $value->kamar_id,
            'status_sewa >=' => '1',
            'status_sewa <=' => '4',
        ])->get()->row();
        $value->isActive    = (isset($sewa) && $value->kamar_id != $this->where['kamar_id']) ? false : true;
    }
    $this->result['data'] = $data_kamar;
    return $this->result;
  }

  public function gantiKamar() {
    $this->db->from('kamar');
    $kamar = $this->db->where([
        'kamar_id' => $this->where['kamar_id'],
    ])->get()->row();
    $this->db->from('sewa');
    $this->db->where('sewa_id = ', $this->where['sewa_id'], FALSE)
            ->where('(status_sewa =', '0', FALSE)
            ->or_where("status_sewa = '1'", NULL, FALSE)
            ->or_where("status_sewa = '2'", NULL, FALSE)
            ->or_where("status_sewa = '3'", NULL, FALSE)
            ->or_where("status_sewa = '4'", NULL, FALSE)
            ->or_where("status_sewa = '8')", NULL, FALSE);
    $sewa = $this->db->get()->row();
    $parkir = $sewa->harga_parkir_motor+$sewa->harga_parkir_mobil;
    $data_sewa = [
        'harga_sewa' => $kamar->harga,
        'kamar_id'   => $this->where['kamar_id']
    ];

    $data_tagihan = [
        'total_harga' => $kamar->harga+$parkir+$sewa->tambahan_biaya+$sewa->deposit,
    ];
    $this->db->where([
        'sewa_id' => $this->where['sewa_id'],
    ]);
    $update_sewa = $this->db->update('sewa', $data_sewa);

    $this->db->where([
        'sewa_id' => $this->where['sewa_id'],
    ]);
    $update_tagihan = $this->db->update('tagihan', $data_tagihan);
    
    $this->db->where([
        'kamar_id'    => $this->where['kamar_id'],
        'status_sewa' => '0',
    ]);
    $this->db->delete('sewa');
    
    $this->result['success'] = ($update_sewa == $update_tagihan) ? true : false;
    return $this->result;
  }

  public function listTransaksi () {
    $bulan   = $this->where['bulan'];
    $tahun   = $this->where['tahun'];
    $user_id = $this->where['user_id'];

    $this->db->from('sewa');
    $data['sewa'] = $this->db->join('tagihan', 'tagihan.sewa_id = sewa.sewa_id')->where([
        'sewa.pemilik_id'     => $user_id,
        'sewa.status_sewa >=' => '3',
        'sewa.status_sewa <=' => '5',
        'MONTH(sewa.tanggal_sewa)' => $bulan,
        'YEAR(sewa.tanggal_sewa)'  => $tahun,
    ])->get()->result();

    foreach ($data['sewa'] as $key => $value) {
        $value->created_on_f     = $this->all_library->format_date($value->created_on, true); 
        $value->total_harga_f    = $this->all_library->format_harga($value->total_harga); 
        $value->harga_sewa_f     = $this->all_library->format_harga($value->harga_sewa); 
        $value->deposit_f        = $this->all_library->format_harga($value->deposit); 
        $value->parking          = $value->harga_parkir_motor+$value->harga_parkir_mobil; 
        $value->parking_f        = $this->all_library->format_harga($value->parking); 
        $value->tambahan_biaya_f = $this->all_library->format_harga($value->tambahan_biaya); 
    }
    
    $data['pengeluaran'] = $this->db->from('pengeluaran')->where([
        'created_by'     => $user_id,
        'MONTH(tanggal)' => $bulan,
        'YEAR(tanggal)'  => $tahun,
    ])->get()->result();

    foreach ($data['pengeluaran'] as $key => $value) {
        $kategori_pengeluaran = $this->db->from('kategori_pengeluaran')->where([
            'kategori_pengeluaran_id' => $value->kategori_id,
        ])->get()->row();

        $value->kategori_pengeluaran = (isset($kategori_pengeluaran)) ? $kategori_pengeluaran->kategori_pengeluaran : '';
        $value->jumlah_f             = $this->all_library->format_harga($value->jumlah);
        $value->total_f              = '- '.$this->all_library->format_harga($value->total);
        $value->tanggal_f            = $this->all_library->format_date($value->tanggal, true, true, false);
    }

    $data['adjustment'] = $this->db->from('adjustment')->where([
        'created_by'     => $user_id,
        'MONTH(tanggal)' => $bulan,
        'YEAR(tanggal)'  => $tahun,
    ])->get()->result();
    
    foreach ($data['adjustment'] as $key => $value) {
        $kategori_pemasukan = $this->db->from('kategori_pemasukan')->where([
            'kategori_pemasukan_id' => $value->kategori_id,
        ])->get()->row();

        $value->kategori_pemasukan = (isset($kategori_pemasukan)) ? $kategori_pemasukan->kategori_pemasukan : '';
        $value->total_f              = (($value->is_min == '1') ? '- ': '').$this->all_library->format_harga($value->total);
        $value->tanggal_f            = $this->all_library->format_date($value->tanggal, true, true, false);
    }

    $this->result['data'] = $data;
    return $this->result;  
  }
  
}

?>
