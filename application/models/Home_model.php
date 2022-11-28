<?php
/**
 *
 */
class Home_model extends CI_Model {

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

  public function get_sewa() {
    if ($this->where['role'] == '2') {
      $this->db->from('sewa');
      $this->db->join('properti', 'properti.properti_id = sewa.properti_id', 'LEFT');
      $this->db->join('lantai', 'lantai.lantai_id = sewa.lantai_id', 'LEFT');
      $this->db->join('kamar', 'kamar.kamar_id = sewa.kamar_id', 'LEFT');
      $this->db->join('file_upload', 'file_upload.session_upload_id = properti.session_upload_id', 'RIGHT');
      $this->db->where('sewa.created_by = ', $this->where['user_id'], FALSE)
              ->where('(sewa.status_sewa =', '0', FALSE)
              ->or_where("sewa.status_sewa = '1'", NULL, FALSE)
              ->or_where("sewa.status_sewa = '2'", NULL, FALSE)
              ->or_where("sewa.status_sewa = '3'", NULL, FALSE)
              ->or_where("sewa.status_sewa = '4'", NULL, FALSE)
              ->or_where("sewa.status_sewa = '8')", NULL, FALSE);
    }elseif ($this->where['role'] == '3') {
      $this->db->select('*, properti.created_by AS pemilik'); 
      $this->db->from('management_kos');
      $this->db->join('properti', 'properti.properti_id = management_kos.properti_id', 'RIGHT');
      $this->db->join('file_upload', 'file_upload.session_upload_id = properti.session_upload_id', 'RIGHT');
      $this->db->where(['management_kos.created_by' => $this->where['user_id'], 'properti.is_deleted' => '0']);
    }else {
      $this->db->from('properti');
      $this->db->join('file_upload', 'file_upload.session_upload_id = properti.session_upload_id', 'RIGHT');
      $this->db->where(['properti.created_by' => $this->where['user_id'], 'is_deleted' => '0']);
    }
    $data = $this->db->get()->row();
    if ($data) {
      if ($this->where['role'] != '2') {
        $lantai     = 0;
        $lantai_id  = [];
        $dlantai    = $this->db->from('lantai')->where('properti_id', $data->properti_id)->get()->result();
        if ($dlantai) {
          foreach ($dlantai as $k => $v) {
            $lantai += 1;
            $lantai_id[] = $v->lantai_id;
          }
        }
        if (isset($data->pemilik)) {
            $where = [
                'kamar.created_by' => $data->pemilik,
                'status_sewa >='   => '1',
                'status_sewa <='   => '4'
            ];    
            $jatuh_tempo   = $this->db->from('sewa')->where([
              'pemilik_id' => $data->pemilik,
              'status_sewa' => '8'
            ])->get()->num_rows();
        } else {
            $where = [
                'kamar.created_by' => $this->where['user_id'],
                'status_sewa >='   => '1',
                'status_sewa <='   => '4'
            ];    
            $jatuh_tempo   = $this->db->from('sewa')->where([
              'pemilik_id' => $this->where['user_id'],
              'status_sewa' => '8'
            ])->get()->num_rows();
        }
        $kamar        = $this->db->from('kamar')->where('is_deleted', '0')->where_in('lantai_id', $lantai_id)->get()->num_rows();
        $sewa         = $this->db->from('kamar')->join('sewa', 'sewa.kamar_id = kamar.kamar_id')->where($where)->group_by('sewa.kamar_id')->get()->num_rows();
        $kamar_aktif  = $this->db->from('kamar')->where(['is_deleted' => '0', 'status' => '1'])->where_in('lantai_id', $lantai_id)->get()->num_rows();
        $jumlah       = ($sewa) ? $kamar_aktif-$sewa : $kamar_aktif;
        $jumlah       = ($jatuh_tempo) ? $jumlah-$jatuh_tempo : $jumlah;
        $sewa         = ($jatuh_tempo) ? $sewa+$jatuh_tempo : $sewa;
        $data->jumlah_lantai = $lantai;
        $data->jumlah_kamar  = ($kamar) ? $kamar : '';
        $data->kamar_tersewa = ($sewa) ? $sewa : '0';
        $data->kamar_kosong  = $jumlah;
      }elseif ($this->where['role'] == '2') {

        $data->tanggal_selesai_f = $this->all_library->format_date($data->tanggal_selesai_sewa, false, false, false);
        $data->status_sewa_f     = $this->all_library->status_sewa($data->status_sewa);
      }
      $data->gambar_link = URL_PROPERTI.'/thumb_'.$data->file_name;
    }

    $komplain = [];
    $info     = [];

    if ($this->where['role'] == '2') {
      $sewa = $this->db->from('sewa')->where(['created_by' => $this->where['user_id'], 'status_sewa >=' => '3', 'status_sewa <=' => '4'])->get()->row();
      $komplain = $this->db->from('komplain')->where('created_by', $this->where['user_id'])->order_by('created_on', 'DESC')->get()->row();
      if (isset($komplain)) {
        $komplain->created_on_f = $this->all_library->format_date($komplain->created_on, false, false, false);
      }
      if ($sewa) {
        $info     = $this->db->from('info')->where('created_by', $sewa->pemilik_id)->order_by('created_on', 'DESC')->get()->row();
      }
    }elseif ($this->where['role'] == '1') {
      $properti = $this->db->from('properti')->where(['created_by' => $this->where['user_id'], 'is_deleted' => '0'])->get()->row();
      $komplain = $this->db->from('komplain')->where('properti_id', ($properti) ? $properti->properti_id : null)->order_by('created_on', 'DESC')->get()->row();
      if (isset($komplain)) {
        $komplain->created_on_f = $this->all_library->format_date($komplain->created_on, false, false, false);
      }
      $info = $this->db->from('info')->where('created_by', $this->where['user_id'])->order_by('created_on', 'DESC')->get()->row();
    }elseif ($this->where['role'] == '3') {
      $this->db->from('management_kos');
      $this->db->join('properti', 'properti.properti_id = management_kos.properti_id');
      $properti = $this->db->where(['management_kos.created_by' => $this->where['user_id']])->get()->row();
      $komplain = $this->db->from('komplain')->where('properti_id', ($properti) ? $properti->properti_id : null)->order_by('komplain.created_on', 'DESC')->get()->row();
      if (isset($komplain)) {
        $komplain->created_on_f = $this->all_library->format_date($komplain->created_on, false, false, false);
      }
      if ($properti) {
        $info = $this->db->from('info')->where('created_by', $properti->created_by)->order_by('created_on', 'DESC')->get()->row();
      }
    }

    $this->result['data'] = array(
      'lastKomplain' => $komplain,
      'lastInfo'     => $info,
      'lastKamar'    => $data,
      'isSewa'       => ($data) ? true : false
    );
    $this->result['success']   = true;
    return $this->result;
  }

  public function home() {

    $data['notif']    = $this->all_notif();
    $data['profile']  = $this->profil();
    $data['properti'] = $this->terProperti();
    $data['favorite'] = $this->terFavorit();

    $this->result['data']      = $data;
    $this->result['totaldata'] = count($data['properti']);
    $this->result['success']   = true;
    return $this->result;
  }

  public function all_notif() {
    $user        = $this->db->from('user')->where(['user_id' => $this->where['created_by']])->get()->row();
    $notif       = ($user) ? $this->db->from('notifikasi')->where(['is_read' => '0', 'to_id' => $user->user_id])->get()->num_rows() : 0;
    $bukti_bayar = ($user) ? $this->db->from('sewa')->where(($user->role == '1' ? 'pemilik_id' : 'created_by'), $user->user_id)->where_in('status_sewa', ['1','2'])->get()->num_rows() : 0;
    $check_out   = ($user) ? $this->db->from('sewa')->where(($user->role == '1' ? 'pemilik_id' : 'created_by'), $user->user_id)->where_in('status_sewa', ['4'])->get()->num_rows() : 0;
    $data_notif  = array(
      'notif'       => ($notif > 0 ? true : false),
      'bukti_bayar' => ($bukti_bayar > 0 ? true : false),
      'check_out'   => ($check_out > 0 ? true : false)
    );

    return $data_notif;
  }

  public function profil() {
    $this->db->from('user');
    $this->db->where(['user_id' => $this->where['created_by']]);
    $data_profile = $this->db->get()->row();
    $link_profile = URL_PROFILE.'/thumb_';

    if (isset($data_profile->session_upload_id)) {
      $file_name                 = $this->db->select('CONCAT("'.$link_profile.'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $data_profile->session_upload_id])->get()->row();
      $data_profile->gambar_link = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
    }

    return $data_profile;
  }

  public function terProperti() {
    $this->db->select('*, properti.session_upload_id AS properti_session_id, MAX(kamar.harga) AS harga_max, MIN(kamar.harga) AS harga_min');
    $this->db->from('properti');
    $this->db->join('lantai', 'lantai.properti_id = properti.properti_id');
    $this->db->join('kamar', 'kamar.lantai_id = lantai.lantai_id');
    $data_properti = $this->db->where(['properti.is_deleted' => '0'])->order_by('kamar.harga', 'ASC')->group_by('properti.properti_id')->limit(5)->get()->result();
    $link_properti = URL_PROPERTI.'/thumb_';

    foreach ($data_properti as $key => $value) {
      $ulasan             = $this->db->select('AVG(bintang) AS bintang')->from('ulasan')->where(['properti_id' => $value->properti_id])->get()->row();
      $value->bintang     = (isset($ulasan)) ? round($ulasan->bintang, 1) : '';
      $file_name          = $this->db->select('CONCAT("'.$link_properti .'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->properti_session_id])->get()->row();
      $value->gambar_link = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
      $value->harga_f     = $this->all_library->format_singkat_angka($value->harga);
      $value->harga_max_f = $this->all_library->format_singkat_angka($value->harga_max);
      $value->harga_min_f = $this->all_library->format_singkat_angka($value->harga_min);

      $lantai     = 0;
      $lantai_id  = [];
      $dlantai    = $this->db->from('lantai')->where('properti_id', $value->properti_id)->get()->result();
      if ($dlantai) {
        foreach ($dlantai as $k => $v) {
          $lantai += 1;
          $lantai_id[] = $v->lantai_id;
        }
      }

      $where = [
        'pemilik_id' => $value->created_by,
        'status_sewa >=' => '1',
        'status_sewa <' => '4'
      ]; 

      $kamar  = $this->db->from('kamar')->where(['is_deleted' => '0', 'status' => '1'])->where_in('lantai_id', $lantai_id)->get()->num_rows();
      $sewa   = $this->db->from('sewa')->where($where)->get()->num_rows();
      $jatuh_tempo   = $this->db->from('sewa')->where([
        'pemilik_id' => $value->created_by,
        'status_sewa' => '8'
      ])->get()->num_rows();
      $jumlah = ($sewa) ? $kamar-$sewa : $kamar;
      $jumlah = ($jatuh_tempo) ? $jumlah-$jatuh_tempo : $jumlah;
      $value->jumlah_lantai = $lantai;
      $value->jumlah_kamar  = ($kamar) ? $jumlah : '';
    }

    return $data_properti;
  }

  public function terFavorit() {
    $this->db->select('*, properti.session_upload_id AS properti_session_id, MAX(kamar.harga) AS harga_max, MIN(kamar.harga) AS harga_min');
    $this->db->from('properti');
    $this->db->join('favorite', 'favorite.properti_id = properti.properti_id');
    $this->db->join('lantai', 'lantai.properti_id = properti.properti_id');
    $this->db->join('kamar', 'kamar.lantai_id = lantai.lantai_id');
    $data_favorite = $this->db->where(['properti.is_deleted' => '0', 'favorite.created_by' => $this->where['created_by']])->order_by('kamar.harga', 'ASC')->group_by('properti.properti_id')->limit(5)->get()->result();
    $link_properti = URL_PROPERTI.'/thumb_';

    foreach ($data_favorite as $key => $value) {
      $ulasan             = $this->db->select('AVG(bintang) AS bintang')->from('ulasan')->where(['properti_id' => $value->properti_id])->get()->row();
      $value->bintang     = (isset($ulasan)) ? round($ulasan->bintang, 1) : '';
      $file_name          = $this->db->select('CONCAT("'.$link_properti .'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->properti_session_id])->get()->row();
      $value->gambar_link = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
      $value->harga_f     = $this->all_library->format_singkat_angka($value->harga);
      $value->harga_max_f = $this->all_library->format_singkat_angka($value->harga_max);
      $value->harga_min_f = $this->all_library->format_singkat_angka($value->harga_min);
    }

    return $data_favorite;
  }

  public function add_favorite() {
    $cek_favorite = $this->db->from('favorite')->where(['properti_id' => $this->where['properti_id'], 'created_by' => $this->where['created_by']])->get()->num_rows();
    if ($cek_favorite) {
      $add_favorite = $this->db->delete('favorite', ['properti_id' => $this->where['properti_id'], 'created_by' => $this->where['created_by']]);
    } else {
      $data = array(
        'properti_id' => $this->where['properti_id'],
        'created_by'  => $this->where['created_by'],
        'created_on'  => date('Y-m-d H:i:s')
      );
      $add_favorite = $this->db->insert('favorite', $data);
    }

    $this->result['message'] = ($cek_favorite) ? 'Favorit' : 'Unfavorit';
    $this->result['success'] = true;
    return $this->result;

  }

}

?>
