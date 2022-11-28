<?php
/**
 *
 */
class Properti_model extends CI_Model {

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

  public function insert_account() {
    $data = array(
      'session_upload_id' => $this->data['session_upload_id'],
      'kategori_id'       => $this->data['kategori_id'],
      'skill_id'          => $this->data['skill_id'],
      'nickname'          => $this->data['nickname'],
      'deskripsi'         => $this->data['deskripsi'],
      'status_account'    => '0',
      'koin'              => $this->data['koin'],
      'created_by'        => $this->data['created_by'],
      'created_on'        => date('Y-m-d H:i:s'),
    );
    $insert = $this->db->insert('account', $data);
    $this->result['success'] = ($insert) ? true : false;
    return $this->result;
  }

  public function list_properti() {
    $this->db->from('properti');
    $this->db->join('lantai', 'lantai.properti_id = properti.properti_id');
    $this->db->join('kamar', 'kamar.lantai_id = lantai.lantai_id');
    $data_properti = $this->db->order_by('properti.created_on', 'DESC')->group_by('properti.properti_id')->limit(5)->get()->result();

    foreach ($data_properti as $key => $value) {
      $value->gambar_link = URL_PROPERTI.'/'.$value->gambar;
    }

    $data_favorite = $this->list_favorit();

    $this->result['favorite'] = ($data_favorite) ? $data_favorite : '';
    $this->result['data']    = ($data_properti) ? $data_properti : '';
    $this->result['success'] = ($data_properti) ? true           : false;
    return $this->result;
  }

  public function list_favorit() {
    $this->db->from('favorite');
    $this->db->join('properti', 'properti.properti_id = favorite.properti_id');
    $this->db->join('lantai', 'lantai.lantai_id = favorite.lantai_id');
    $this->db->join('kamar', 'kamar.kamar_id = favorite.kamar_id');
    $data_favorite = $this->db->where(['favorite.created_by' => $this->where['created_by']])->order_by('favorite.created_on', 'DESC')->get()->result();

    return $data_favorite;
  }

  public function get_pengaturan_kos() {
    $this->db->from('properti');
    $data = $this->db->where(['properti.properti_id' => $this->where['properti_id']])->get()->row();

    $this->result['data'] = $data;
    return $this->result;
  }

  public function properti_by() {
    $link_properti = URL_PROPERTI.'/thumb_';
    $link_kamar    = URL_KAMAR.'/thumb_';
    if ($this->where['role'] == '3') {
      $this->db->from('lantai');
      $this->db->join('properti', 'properti.properti_id = lantai.properti_id');
      $this->db->join('management_kos', 'management_kos.properti_id = properti.properti_id');
      $this->db->where([
        'management_kos.created_by'   => $this->where['created_by'],
        'properti.is_deleted' => '0'
      ]);
      $data = $this->db->order_by('lantai.created_on', 'DESC')->get()->result();
    } else {
      $this->db->from('lantai');
      $this->db->join('properti', 'properti.properti_id = lantai.properti_id');
      $this->db->where([
        'lantai.created_by'   => $this->where['created_by'],
        'properti.is_deleted' => '0'
      ]);
      $data = $this->db->order_by('lantai.created_on', 'DESC')->get()->result();
    }
    foreach ($data as $key => $value) {
      $value->value_lantai = 'Lantai '.$value->lantai;

      $this->db->from('kamar');
      $this->db->join('tipe_kamar', 'tipe_kamar.tipe_kamar_id = kamar.tipe_kamar_id');
      $this->db->join('lantai', 'lantai.lantai_id = kamar.lantai_id');
      $this->db->where([
        'kamar.lantai_id'  => $value->lantai_id,
        'kamar.is_deleted' => '0',
      ]);
      $this->db->order_by('kamar.tipe_kamar_id ASC, kamar.nomor_kamar ASC');
      $value->data    = $this->db->get()->result();
      $value->isKamar = (count($value->data) > 0) ? false : true;
      foreach ($value->data as $k => $v) {
        $v->value_lantai  = 'Lantai '.$v->lantai;
        $gambar_link          = [];
        $file_name            = $this->db->select('CONCAT("'.$link_kamar.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $v->session_upload_id])->get()->result();
        foreach ($file_name as $vl) {
          $gambar_link[]      = (isset($vl->gambar_link)) ? $vl->gambar_link : NULL;
        }
        $v->gambar_link       = $gambar_link;
        $v->harga_f           = $this->all_library->format_singkat_angka($v->harga);
        $v->fasilitas_kamar   = $this->db->select('master_fasilitas_kamar_id, fasilitas_kamar')->from('master_fasilitas_kamar')->get()->result();
        $data_fasilitas_kamar = $this->db->from('fasilitas_kamar')->where(['kamar_id' => $v->kamar_id])->get()->result();
        foreach ($v->fasilitas_kamar as $vy) {
          $check = [];
          foreach ($data_fasilitas_kamar as $vl) {
            $check[] = ($vy->master_fasilitas_kamar_id == $vl->master_fasilitas_kamar_id) ? '1' : '2';
          }
          $vy->check = (in_array('1', $check)) ? true : false;
        }
        $this->db->from('sewa');
        $this->db->join('user', 'user.user_id = sewa.created_by');
        $this->db->where('kamar_id = ', $v->kamar_id, FALSE)
                ->where('(status_sewa =', '0', FALSE)
                ->or_where("status_sewa = '1'", NULL, FALSE)
                ->or_where("status_sewa = '2'", NULL, FALSE)
                ->or_where("status_sewa = '3'", NULL, FALSE)
                ->or_where("status_sewa = '4'", NULL, FALSE)
                ->or_where("status_sewa = '8')", NULL, FALSE);

        $v->sewa = $this->db->get()->row();
        $this->db->from('komplain');
        $this->db->join('sewa', 'sewa.sewa_id = komplain.sewa_id');
        $this->db->where([
          'sewa.kamar_id'      => $v->kamar_id,
          'sewa.status_sewa'   => '3',
          'komplain.tanggapan' => null
        ]);
        $v->komplain = $this->db->get()->row();
      }
    }
    // foreach ($data as $key => $value) {
    //   $file_name           = $this->db->select('CONCAT("'.$link_properti.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->session_upload_id])->get()->row();
    //   $value->gambar_link  = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
    //   $this->db->from('lantai');
    //   $this->db->join('kamar', 'kamar.lantai_id = lantai.lantai_id');
    //   $this->db->join('tipe_kamar', 'tipe_kamar.tipe_kamar_id = kamar.tipe_kamar_id');
    //   $this->db->where(['lantai.properti_id' => $value->properti_id, 'kamar.is_deleted' => '0']);
    //   $value->data = $this->db->order_by('lantai.lantai', 'ASC')->get()->result();
    //   foreach ($value->data as $k => $v) {
    //     $gambar_link          = [];
    //     $file_name            = $this->db->select('CONCAT("'.$link_kamar.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $v->session_upload_id])->get()->result();
    //     foreach ($file_name as $vl) {
    //       $gambar_link[]      = (isset($vl->gambar_link)) ? $vl->gambar_link : NULL;
    //     }
    //     $v->gambar_link       = $gambar_link;
    //     $v->harga_f           = $this->all_library->format_singkat_angka($v->harga);
    //     $v->value_lantai      = 'Lantai '.$v->lantai;
    //     $v->jumlah_lantai     = $this->db->from('lantai')->where(['properti_id' => $value->properti_id])->get()->num_rows();
    //     $v->fasilitas_kamar   = $this->db->select('master_fasilitas_kamar_id, fasilitas_kamar')->from('master_fasilitas_kamar')->get()->result();
    //     $data_fasilitas_kamar = $this->db->from('fasilitas_kamar')->where(['kamar_id' => $v->kamar_id])->get()->result();
    //     foreach ($v->fasilitas_kamar as $vy) {
    //       $check = [];
    //       foreach ($data_fasilitas_kamar as $vl) {
    //         $check[] = ($vy->master_fasilitas_kamar_id == $vl->master_fasilitas_kamar_id) ? '1' : '2';
    //       }
    //       $vy->check = (in_array('1', $check)) ? true : false;
    //     }
    //   }
    // }

    $this->result['data'] = ($data) ? $data : [];
    return $this->result;
  }

  public function get_add_needed() {
    if (isset($this->where['properti_id'])) {
        $link_properti  = URL_PROPERTI.'/thumb_';
        $link_kamar     = URL_KAMAR.'/thumb_';
        $properti       = $this->db->from('properti')->where(['properti_id' => $this->where['properti_id']])->get()->row();
        $data_umum       = $this->db->from('fasilitas_umum')->where(['properti_id' => $this->where['properti_id']])->get()->result();

        $fasilitas_umum  = $this->db->from('master_fasilitas_umum')->get()->result();
        foreach ($fasilitas_umum as $ke => $val) {
          $check = [];
          foreach ($data_umum as $k => $v) {
            $check[] = ($val->master_fasilitas_umum_id == $v->master_fasilitas_umum_id) ? '1' : '2';
          }
          $val->check    = (in_array('1', $check)) ? true : false;
          $isParkirMotor[] = (in_array('1', $check) && $val->master_fasilitas_umum_id == '8') ? '1' : '2';
          $isParkirMobil[] = (in_array('1', $check) && $val->master_fasilitas_umum_id == '9') ? '1' : '2';
        }

        $properti->isParkirMotor = (in_array('1', $isParkirMotor)) ? true : false;
        $properti->isParkirMobil = (in_array('1', $isParkirMobil)) ? true : false;

       $this->db->from('lantai');
       $this->db->where(['properti_id' => $this->where['properti_id']]);
       $lantai = $this->db->get()->result();
       foreach ($lantai as $key => $value) {
         $data_kamar      = $this->db->from('kamar')->where(['lantai_id' => $value->lantai_id, 'is_deleted' => '0'])->get()->result();
         foreach ($data_kamar as $ke => $val) {
           $gambar_link     = [];
           $file_name       = $this->db->select('CONCAT("'.$link_kamar.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $val->session_upload_id])->get()->result();
           $tipe_kamar      = $this->db->from('tipe_kamar')->where(['tipe_kamar_id' => $val->tipe_kamar_id])->get()->row();
           foreach ($file_name as $k => $v) {
             $gambar_link[] = $v->gambar_link;
           }
           $fasilitas_kamar      = $this->db->select('master_fasilitas_kamar_id, fasilitas_kamar')->from('master_fasilitas_kamar')->get()->result();
           $data_fasilitas_kamar = $this->db->from('fasilitas_kamar')->where(['kamar_id' => $val->kamar_id])->get()->result();
           foreach ($fasilitas_kamar as $k => $v) {
             $check = [];
             foreach ($data_fasilitas_kamar as $ky => $vl) {
               $check[] = ($v->master_fasilitas_kamar_id == $vl->master_fasilitas_kamar_id) ? '1' : '2';
             }
             $v->check = (in_array('1', $check)) ? true : false;
           }
           $dataKamar[] = [
             'kamar_id'          => $val->kamar_id,
             'status'            => ['row' => $val->status],
             'session_upload_id' => $val->session_upload_id,
             'lantai'            => ['row' => ($value->lantai-1)],
             'value_lantai'      => 'Lantai '.$value->lantai,
             'harga_kamar'       => $val->harga,
             'fasilitas_kamar'   => $fasilitas_kamar,
             'value_kamar'       => ($tipe_kamar) ? $tipe_kamar->tipe_kamar : NULL,
             'tipe_kamar'        => ($tipe_kamar) ? ['row' => $tipe_kamar->tipe_kamar_id-1] : NULL,
             'gambar_link'       => ($gambar_link) ? $gambar_link : [],
             'nomor_kamar'       => $val->nomor_kamar
           ];
         }
          $lantai_id[] = $value->lantai_id;
       }
       $file_name                   = $this->db->select('CONCAT("'.$link_properti.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $properti->session_upload_id])->get()->result();
       foreach ($file_name as $k => $v) {
         $gambar_properti[] = $v->gambar_link;
       }
       $properti->gambar_link       = ($gambar_properti) ? $gambar_properti : [];
       $properti->dataKamar         = $dataKamar;
       $properti->fasilitas_umum    = $fasilitas_umum;
       $properti->lantai_id         = $lantai_id;
       $properti->session_upload_id = $properti->session_upload_id;
       $data['properti']            = $properti;
    } else {
      $fasilitas_kamar = $this->db->select('master_fasilitas_kamar_id, fasilitas_kamar')->from('master_fasilitas_kamar')->get()->result();
      $fasilitas_umum  = $this->db->select('master_fasilitas_umum_id, fasilitas_umum')->from('master_fasilitas_umum')->get()->result();
      foreach ($fasilitas_kamar as $key => $value) {
        $value->check = false;
      }
      foreach ($fasilitas_umum as $key => $value) {
        $value->check = false;
      }
      $data['fasilitas_kamar'] = $fasilitas_kamar;
    }
    $data_tipe_kamar                 = $this->db->select('tipe_kamar_id, tipe_kamar')->from('tipe_kamar')->get()->result();
    $data['data_tipe_kamar']         = $data_tipe_kamar;
    $data['fasilitas_umum']          = $fasilitas_umum;
    $data['session_upload_properti'] = (string)$this->all_library->get_session_id();
    $data['session_upload_id']       = (string)$this->all_library->get_session_id();
    $this->result['data']            = $data;
    $this->result['success']         = true;
    $this->result['totaldata']       = 1;
    return $this->result;
  }

  public function ubah_kamar() {
    $lantai = $this->db->from('lantai')->where([
      'properti_id' => $this->data['kamar']['properti_id'],
      'lantai'      => $this->data['kamar']['lantai'],
      'created_by'  => $this->data['kamar']['created_by'],
    ])->get()->row();
    $data = [
      'tipe_kamar_id' => $this->data['kamar']['tipe_kamar'],
      'nomor_kamar'   => $this->data['kamar']['nomor_kamar'],
      'harga'         => $this->data['kamar']['harga_kamar'],
      'status'        => $this->data['kamar']['status'],
      'lantai_id'     => $lantai->lantai_id,
      'modified_on'   => date('Y-m-d H:i:s'),
    ];
    $this->db->where(['kamar_id' => $this->data['kamar']['kamar_id'], 'created_by' => $this->data['kamar']['created_by']]);
    $this->db->update('kamar', $data);

    $this->db->where(['kamar_id' => $this->data['kamar']['kamar_id'], 'created_by' => $this->data['kamar']['created_by']]);
    $this->db->delete('fasilitas_kamar');

    $this->db->insert_batch('fasilitas_kamar', $this->data['kamar']['fasilitas_kamar']);
    $this->result['success'] = true;
    return $this->result;
  }

  public function simpan_properti() {
    if (isset($this->data['properti']['properti_id'])) {
      return $this->proses_edit();
    }else {
      $this->proses_simpan();
    }
    $this->result['success'] = true;
    return $this->result;
  }

  public function simpan_peraturan() {
    $data = array(
      'peraturan' => $this->data['peraturan'],
      'modified_on'  => date('Y-m-d H:i:s'),
    );
    $this->db->where($this->where);
    $update = $this->db->update('properti', $data);
    $this->result['success'] = true;
    return $this->result;
  }

  public function simpan_pembersihan() {
    $data = array(
      'waktu_bersih' => $this->data['waktu_bersih'],
      'ganti_sprei'  => $this->data['ganti_sprei'],
      'modified_on'  => date('Y-m-d H:i:s'),
    );
    
    // $this->db->join('lantai', 'lantai.properti_id = properti.properti_id');
    // $this->db->join('kamar', 'kamar.lantai_id = lantai.lantai_id');
    // $this->db->where(['lantai.properti_id' => $this->where['properti_id']]);
    $this->db->where(['properti_id' => $this->where['properti_id']]);
    $update = $this->db->update('properti', $data);
    $this->result['success'] = $update;
    return $this->result;
  }

  public function proses_edit() {
    $properti_id = $this->data['properti']['properti_id'];
    $lantai_id   = $this->data['properti']['lantai_id'];
    $simpan_properti = array(
      'nama_properti'       => $this->data['properti']['nama_properti'],
      'session_upload_id'   => $this->data['properti']['session_upload_id'],
      'harga_parkir_mobil'  => ($this->data['properti']['harga_parkir_mobil']) ? $this->data['properti']['harga_parkir_mobil'] : NULL,
      'harga_parkir_motor'  => ($this->data['properti']['harga_parkir_motor']) ? $this->data['properti']['harga_parkir_motor'] : NULL,
      // 'waktu_pembersihan'   => $this->data['properti']['bersih'],
      // 'tanggal_ganti_sprei' => $this->data['properti']['sprei'],
      'kota'                => ($this->data['properti']['kota']) ? $this->data['properti']['kota'] : null,
      'alamat'              => ($this->data['properti']['alamat']) ? $this->data['properti']['alamat'] : null,
      'tambahan_biaya'      => ($this->data['properti']['tambahan_biaya']) ? $this->data['properti']['tambahan_biaya'] : null,
      'deposit'             => $this->data['properti']['deposit'],
      'is_deleted'          => '0',
      'modified_on'         => date('Y-m-d H:i:s'),
    );

    $this->db->where(['properti_id' => $properti_id, 'created_by' => $this->data['properti']['created_by']]);
    $this->db->update('properti', $simpan_properti);

    $this->db->where(['properti_id' => $properti_id, 'created_by' => $this->data['properti']['created_by']]);
    $this->db->delete('fasilitas_umum');

    for ($i=0; $i < count($this->data['properti']['fasilitas_umum']); $i++) {
      if ($this->data['properti']['fasilitas_umum'][$i]['check']) {
        $simpan_fasilitas_umum = array(
          'master_fasilitas_umum_id' => $this->data['properti']['fasilitas_umum'][$i]['master_fasilitas_umum_id'],
          'properti_id'              => $this->data['properti']['properti_id'],
          'created_by'               => $this->data['properti']['created_by'],
          'created_on'               => date('Y-m-d H:i:s'),
        );
        $this->db->insert('fasilitas_umum', $simpan_fasilitas_umum);
      }
    }
    for ($i=0; $i < count($this->data['properti']['dataKamar']) ; $i++) {
      $kamar_id   = (isset($this->data['properti']['dataKamar'][$i]['kamar_id'])) ? $this->data['properti']['dataKamar'][$i]['kamar_id'] : NULL;
      $data_kamar = $this->db->from('kamar')->where(['kamar_id' => $kamar_id])->get()->row();

      $this->db->where(['kamar_id' => $kamar_id]);
      $this->db->delete('fasilitas_kamar');
      for ($j=0; $j < count($this->data['properti']['dataKamar'][$i]['fasilitas_kamar']); $j++) {
        if ($this->data['properti']['dataKamar'][$i]['fasilitas_kamar'][$j]['check']) {
          $simpan_fasilitas_kamar = array(
            'master_fasilitas_kamar_id' => $this->data['properti']['dataKamar'][$i]['fasilitas_kamar'][$j]['master_fasilitas_kamar_id'],
            'kamar_id'                  => $kamar_id,
            'created_by'                => $this->data['properti']['created_by'],
            'created_on'                => date('Y-m-d H:i:s'),
          );
          $this->db->insert('fasilitas_kamar', $simpan_fasilitas_kamar);
        }
      }
      if ($data_kamar) {
        $ubah_kamar = array(
          'session_upload_id' => $this->data['properti']['dataKamar'][$i]['session_upload_id'],
          'lantai_id'         => $lantai_id[$this->data['properti']['dataKamar'][$i]['lantai']['row']],
          'nomor_kamar'       => $this->data['properti']['dataKamar'][$i]['nomor_kamar'],
          'tipe_kamar_id'     => $this->data['properti']['dataKamar'][$i]['tipe_kamar']['row']+1,
          'harga'             => $this->data['properti']['dataKamar'][$i]['harga_kamar'],
          'status'            => (string)$this->data['properti']['dataKamar'][$i]['status']['row'],
          'created_by'        => $this->data['properti']['created_by'],
          'modified_on'       => date('Y-m-d H:i:s'),
        );
        $this->db->where(['kamar_id' => $kamar_id]);
        $this->db->update('kamar', $ubah_kamar);
      } else {
        $simpan_kamar = array(
          'session_upload_id' => $this->data['properti']['dataKamar'][$i]['session_upload_id'],
          'lantai_id'         => $lantai_id[$this->data['properti']['dataKamar'][$i]['lantai']['row']],
          'nomor_kamar'       => $this->data['properti']['dataKamar'][$i]['nomor_kamar'],
          'tipe_kamar_id'     => $this->data['properti']['dataKamar'][$i]['tipe_kamar']['row']+1,
          'harga'             => $this->data['properti']['dataKamar'][$i]['harga_kamar'],
          'created_by'        => $this->data['properti']['created_by'],
          'created_on'        => date('Y-m-d H:i:s'),
        );
        $this->db->insert('kamar', $simpan_kamar);
      }
    }
    for ($i=0; $i < count($this->data['properti']['hapus_kamar']); $i++) {
      $hapus_kamar = array(
        'is_deleted'  => '1',
        'created_by'  => $this->data['properti']['created_by'],
        'modified_on' => date('Y-m-d H:i:s'),
      );
      $this->db->where(['kamar_id' => $this->data['properti']['hapus_kamar'][$i]]);
      $this->db->update('kamar', $hapus_kamar);
    }
  }

  public function proses_simpan() {
    $simpan_properti = array(
      'nama_properti'      => $this->data['properti']['nama_properti'],
      'session_upload_id'  => $this->data['properti']['session_upload_id'],
      'harga_parkir_mobil' => ($this->data['properti']['harga_parkir_mobil']) ? $this->data['properti']['harga_parkir_mobil'] : NULL,
      'harga_parkir_motor' => ($this->data['properti']['harga_parkir_motor']) ? $this->data['properti']['harga_parkir_motor'] : NULL,
      'deposit'            => $this->data['properti']['deposit'],
      'kota'               => ($this->data['properti']['kota']) ? $this->data['properti']['kota'] : null,
      'alamat'             => ($this->data['properti']['alamat']) ? $this->data['properti']['alamat'] : null,
      'tambahan_biaya'     => ($this->data['properti']['tambahan_biaya']) ? $this->data['properti']['tambahan_biaya'] : null,
      'is_deleted'         => '0',
      'created_by'         => $this->data['properti']['created_by'],
      'created_on'         => date('Y-m-d H:i:s'),
    );
    $this->db->insert('properti', $simpan_properti);
    $properti_id = $this->db->insert_id();
    for ($i=0; $i < count($this->data['properti']['fasilitas_umum']); $i++) {
      if ($this->data['properti']['fasilitas_umum'][$i]['check']) {
        $simpan_fasilitas_umum = array(
          'master_fasilitas_umum_id' => $this->data['properti']['fasilitas_umum'][$i]['master_fasilitas_umum_id'],
          'properti_id'              => $properti_id,
          'created_by'               => $this->data['properti']['created_by'],
          'created_on'               => date('Y-m-d H:i:s'),
        );
        $this->db->insert('fasilitas_umum', $simpan_fasilitas_umum);
      }
    }
    for ($i=0; $i < $this->data['properti']['jumlah_lantai']; $i++) {
      $simpan_lantai = array(
        'properti_id' => $properti_id,
        'lantai'      => $i+1,
        'created_by'  => $this->data['properti']['created_by'],
        'created_on'  => date('Y-m-d H:i:s'),
      );
      $this->db->insert('lantai', $simpan_lantai);
      $lantai_id[] = $this->db->insert_id();
      $key       = 0;
    }
    for ($i=0; $i < count($this->data['properti']['dataKamar']); $i++) {
      $simpan_kamar = array(
        'session_upload_id' => $this->data['properti']['dataKamar'][$i]['session_upload_id'],
        'lantai_id'         => $lantai_id[$this->data['properti']['dataKamar'][$i]['lantai']['row']],
        'nomor_kamar'       => $this->data['properti']['dataKamar'][$i]['nomor_kamar'],
        'tipe_kamar_id'     => $this->data['properti']['dataKamar'][$i]['tipe_kamar']['row']+1,
        'harga'             => $this->data['properti']['dataKamar'][$i]['harga_kamar'],
        'created_by'        => $this->data['properti']['created_by'],
        'created_on'        => date('Y-m-d H:i:s'),
      );
      $this->db->insert('kamar', $simpan_kamar);
      $kamar_id = $this->db->insert_id();
      for ($j=0; $j < count($this->data['properti']['dataKamar'][$i]['fasilitas_kamar']); $j++) {
        if ($this->data['properti']['dataKamar'][$i]['fasilitas_kamar'][$j]['check']) {
          $simpan_fasilitas_kamar = array(
            'master_fasilitas_kamar_id' => $this->data['properti']['dataKamar'][$i]['fasilitas_kamar'][$j]['master_fasilitas_kamar_id'],
            'kamar_id'                  => $kamar_id,
            'created_by'                => $this->data['properti']['created_by'],
            'created_on'                => date('Y-m-d H:i:s'),
          );
          $this->db->insert('fasilitas_kamar', $simpan_fasilitas_kamar);
        }
      }
    }
  }

  public function delete_properti() {
    $update = array(
      'is_deleted'  => '1',
      'modified_on' => date('Y-m-d H:i:s'),
    );
    $this->db->where(['properti_id' => $this->where['properti_id'], 'created_by' => $this->where['created_by']]);
    $this->db->update('properti', $update);
    $this->result['success'] = true;
    return $this->result;
  }

  public function delete_kamar() {
    $update = array(
      'is_deleted'  => '1',
      'modified_on' => date('Y-m-d H:i:s'),
    );
    $this->db->where(['kamar_id' => $this->where['kamar_id'], 'created_by' => $this->where['created_by']]);
    $this->db->update('kamar', $update);
    $this->result['success'] = true;
    return $this->result;
  }

  public function detail_kamar() {
    $data              = $this->db->from('kamar')->join('tipe_kamar', 'tipe_kamar.tipe_kamar_id = kamar.tipe_kamar_id')->where(['kamar.kamar_id' => $this->where['kamar_id']])->get()->row();
    $check_out         = $this->db->from('sewa')->where(['kamar_id' => $this->where['kamar_id'], 'tanggal_selesai_sewa <=' => date('Y-m-d')])->group_by('kamar_id')->order_by('tanggal_selesai_sewa', 'DESC')->get()->row();
    $data->check_out   = ($check_out) ? $this->all_library->date($check_out->tanggal_selesai_sewa) : '-';
    $data->data_gambar = $this->db->select('CONCAT("'.URL_KAMAR.'/thumb_", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $data->session_upload_id])->get()->result();
    $data->harga_f     = $this->all_library->format_singkat_angka($data->harga);
    $data->status_f    = $this->all_library->status_kamar($data->status);
    $sewa              = $this->db->from('sewa')->where([
        'kamar_id'       => $data->kamar_id,
        'status_sewa >=' => '1',
        'status_sewa <=' => '4'
    ])->get()->row();
    if ($sewa) {
      $data->checkin  = $this->all_library->date($sewa->tanggal_sewa, true);
      $data->checkout = $this->all_library->date($sewa->tanggal_selesai_sewa, true);
      $data->priode   = '1 Bulan';
    }
    $data->isSewa      = ($sewa) ? true : false;

    $this->result['data']    = $data;
    $this->result['success'] = true;
    return $this->result;
  }

  public function properti_detail() {
    $link_kamar    = URL_KAMAR.'/thumb_';
    $link_properti = URL_PROPERTI.'/thumb_';
    $link_profile  = URL_PROFILE.'/thumb_';
    $this->db->select('*, properti.session_upload_id AS properti_session_id, COUNT(kamar.kamar_id) AS jumlah_kamar, user.session_upload_id AS user_session_id, MAX(kamar.harga) AS harga_max, MIN(kamar.harga) AS harga_min');
    $this->db->from('properti');
    $this->db->join('user', 'user.user_id = properti.created_by');
    $this->db->join('lantai', 'lantai.properti_id = properti.properti_id');
    $this->db->join('kamar', 'kamar.lantai_id = lantai.lantai_id');
    $data                 = $this->db->where(['properti.properti_id' => $this->where['properti_id'], 'properti.is_deleted' => '0', 'kamar.is_deleted' => '0', 'kamar.status' => '1'])->group_by('properti.properti_id')->get()->row();
    $sewa                 = $this->db->select('COUNT(sewa_id) as jumlah_sewa')->from('sewa')->where([
        'properti_id'    => $this->where['properti_id'],
        'status_sewa >=' => '1',
        'status_sewa <=' => '4'
    ])->get()->row();
    $jatuh_tempo         = $this->db->select('COUNT(sewa_id) as jumlah_sewa')->from('sewa')->where([
        'properti_id'    => $this->where['properti_id'],
        'status_sewa'    => '8'
    ])->get()->row();
    $data->jumlah_kamar   = (int)$data->jumlah_kamar-(int)$sewa->jumlah_sewa;
    $data->jumlah_kamar   = (int)$data->jumlah_kamar-(int)$jatuh_tempo->jumlah_sewa;
    $data->harga_min_f    = $this->all_library->format_singkat_angka($data->harga_min);
    $data->harga_max_f    = $this->all_library->format_singkat_angka($data->harga_max);
    $data->harga_f        = $this->all_library->format_singkat_angka($data->harga);
    $data->harga_parkir_mobil_f = $this->all_library->format_singkat_angka($data->harga_parkir_mobil);
    $data->harga_parkir_motor_f = $this->all_library->format_singkat_angka($data->harga_parkir_motor);
    $data->status_f       = $this->all_library->status_kamar($data->status);
    $data->created_on_f   = $this->all_library->format_date($data->created_on, false);
    $gambar               = $this->db->select('CONCAT("'.$link_profile.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $data->user_session_id])->get()->row();
    $data->gambar_profile = ($gambar) ? $gambar->gambar_link : '';
    $data->data_gambar    = $this->db->select('CONCAT("'.$link_properti.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $data->properti_session_id])->get()->result();
    $ulasan               = $this->db->select('AVG(bintang) AS bintang')->from('ulasan')->where(['properti_id' => $data->properti_id])->get()->row();
    $data->bintang        = (isset($ulasan)) ? round($ulasan->bintang,1) : null;
    $favorite             = $this->db->from('favorite')->where(['properti_id' => $data->properti_id, 'created_by' => $data->created_by])->get()->num_rows();
    $this->db->select('*, kamar.session_upload_id');
    $this->db->from('properti');
    $this->db->join('lantai', 'lantai.properti_id = properti.properti_id');
    $this->db->join('kamar', 'kamar.lantai_id = lantai.lantai_id');
    $data->data_kamar  = $this->db->where(['lantai.properti_id' => $this->where['properti_id'], 'kamar.is_deleted' => '0'])->get()->result();
    foreach ($data->data_kamar as $key => $value) {
        $gambar_kamar       = $this->db->select('CONCAT("'.$link_kamar.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->session_upload_id])->get()->row();
        $value->gambar_link = ($gambar_kamar) ? $gambar_kamar->gambar_link : '';
    }
    $this->db->select('tipe_kamar.*, properti.properti_id, kamar.created_by as user_id');
    $this->db->from('properti');
    $this->db->join('lantai', 'lantai.properti_id = properti.properti_id');
    $this->db->join('kamar', 'kamar.lantai_id = lantai.lantai_id');
    $this->db->join('tipe_kamar', 'tipe_kamar.tipe_kamar_id = kamar.tipe_kamar_id');
    $data->data_tipe_kamar = $this->db->where(['lantai.properti_id' => $this->where['properti_id'], 'kamar.is_deleted' => '0'])->group_by('kamar.tipe_kamar_id')->get()->result();
    $data->sewa        = $this->db->from('sewa')->where([
        'created_by'     => $data->created_by,
        'status_sewa >=' => '1',
        'status_sewa <=' => '4'
    ])->get()->row();
    $data->isActive    = ($data->sewa) ? true : false;
    $data->ulasan      = $this->ulasan();
    $data->menarik     = $this->menarik();
    $this->db->select('master_fasilitas_umum.fasilitas_umum');
    $this->db->from('fasilitas_umum');
    $this->db->join('master_fasilitas_umum', 'master_fasilitas_umum.master_fasilitas_umum_id = fasilitas_umum.master_fasilitas_umum_id');
    $data->fasilitas_umum    = $this->db->where(['fasilitas_umum.properti_id' => $this->where['properti_id']])->get()->result();
    $array = [];
    foreach ($data->fasilitas_umum as $key => $value) {
        $array[] = $value->fasilitas_umum; 
    }
    $data->fasilitas_string  = implode(", ",$array);
    $this->result['data']    = $data;
    $this->result['message'] = ($favorite) ? 'Unfavorit' : 'Favorit';
    $this->result['success'] = ($data) ? true : false;
    return $this->result;
  }


  public function ulasan() {
    $this->db->from('ulasan');
    $data_ulasan   = $this->db->where(['ulasan.properti_id' => $this->where['properti_id']])->limit(2)->get()->result();
    $link_profile  = URL_PROFILE.'/thumb_';

    foreach ($data_ulasan as $key => $value) {
      $value->user        = $this->db->from('user')->where(['user_id' => $value->created_by])->get()->row();
      $file_name          = $this->db->select('CONCAT("'.$link_profile .'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->user->session_upload_id])->get()->row();
      $value->gambar_link = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
    }

    return $data_ulasan;
  }


  public function menarik() {
    $this->db->select('*, properti.session_upload_id AS properti_session_id');
    $this->db->from('properti');
    $this->db->join('lantai', 'lantai.properti_id = properti.properti_id');
    $this->db->join('kamar', 'kamar.lantai_id = lantai.lantai_id');
    $data_properti = $this->db->where(['properti.is_deleted' => '0', 'properti.properti_id !=' => $this->where['properti_id']])->order_by('kamar.harga', 'ASC')->group_by('properti.properti_id')->limit(5)->get()->result();
    $link_properti = URL_PROPERTI.'/thumb_';

    foreach ($data_properti as $key => $value) {
      $ulasan             = $this->db->select('AVG(bintang) AS bintang')->from('ulasan')->where(['properti_id' => $value->properti_id])->get()->row();
      $value->bintang     = (isset($ulasan)) ? round($ulasan->bintang,1) : null;
      $file_name          = $this->db->select('CONCAT("'.$link_properti .'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->properti_session_id])->get()->row();
      $value->gambar_link = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
      $value->harga_f     = $this->all_library->format_singkat_angka($value->harga);
    }

    return $data_properti;
  }

  public function managementKos() {
    $user = $this->db->from('user')->where(['role' => '3'])->get()->result();
    $link_properti = URL_PROFILE.'/thumb_';

    foreach ($user as $key => $value) {
      $management_kos      = $this->db->select('created_by')->from('management_kos')->where([
        'properti_id' => $this->where['properti_id'],
        'created_by'  => $value->user_id
      ])->get()->row();
      $cek_penjaga         = $this->db->from('management_kos')->where(['created_by' => $value->user_id])->get()->num_rows();
      $file_name           = $this->db->select('CONCAT("'.$link_properti .'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->session_upload_id])->get()->row();
      $value->gambar_link  = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
      $value->birthday_f   = $this->all_library->birthday($value->birthday);
      $value->created_on_f = $this->all_library->date($value->created_on);
      $value->is_selected  = (isset($management_kos) && $management_kos->created_by == $value->user_id) ? true : false;
      $value->is_active    = ((isset($management_kos) && $management_kos->created_by == $value->user_id) ? false : ($cek_penjaga ? true : false));
    }

    $this->result['data'] = $user;
    return $this->result;
  }

  public function simpanManagementKos() {
      $simpan = true;
    foreach ($this->data['user'] as $key => $value) {
        if ($value['is_selected']) {
            $cek_penjaga = $this->db->from('management_kos')->where(['created_by' => $value['user_id']])->get()->num_rows();
            if ($cek_penjaga == 0) {
                $data = array(
                    'properti_id' => $this->data['properti_id'],
                    'created_by'  => $value['user_id'],
                    'created_on'  => date('Y-m-d H:i:s')
                );
                $simpan = $this->db->insert('management_kos', $data);    
            }
        } else {
            $simpan = $this->db->where([
                'properti_id' => $this->data['properti_id'],
                'created_by'  => $value['user_id'],
            ])->delete('management_kos');
        }
    }
    $this->result['success'] = ($simpan) ? true : false;
    return $this->result;
  }

  
  public function selectTipeKamar() {
    $link_kamar = URL_KAMAR.'/thumb_';
    $kamar      = $this->db->from('kamar')->where($this->where)->get()->result();
    foreach ($kamar as $key => $value) {
        $gambar_kamar       = $this->db->select('CONCAT("'.$link_kamar.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->session_upload_id])->get()->row();
        $value->gambar_link = ($gambar_kamar) ? $gambar_kamar->gambar_link : '';
    }
    $this->result['data']    = $kamar;
    $this->result['success'] = ($kamar) ? true   : false;
    return $this->result;
  }

  

}

?>
