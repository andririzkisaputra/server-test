<?php
/**
 *
 */
class Auth_model extends CI_Model {

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

  public function session_upload_id() {
    $data['data_rekening']     = $this->db->from('nomor_rekening')->where(['role' => '2'])->get()->result();
    $data['session_upload_id'] = (string)$this->all_library->get_session_id();
    for ($i=0; $i < 2; $i++) {
      $gambar[]      = '';
      $gambar_link[] = '';
    }
    $data['gambar']          = $gambar;
    $data['gambar_link']     = $gambar_link;
    $this->result['data']    = $data;
    $this->result['success'] = true;
    return $this->result;
  }

  public function get_data() {
    $this->result['data']    = $this->db->from('user')->where($this->where)->get()->row();
    if ($this->result['data']) {
      $this->result['data']->gambar        = [];
      $this->result['data']->gambar_link   = [];
      $this->result['data']->data_rekening = $this->db->from('nomor_rekening')->where(['role' => '2'])->get()->result();
      $dataGambar = $this->db->from('file_upload')->where('session_upload_id', $this->result['data']->session_upload_id)->get()->result();
      foreach ($dataGambar as $value) {
        $this->result['data']->gambar[]      = ($value->file_name) ? $value->file_name : null;
        $this->result['data']->gambar_link[] = URL_PROFILE.'/'.$value->file_name;
      }
      $this->result['data']->password    = $this->all_library->decodeString($this->result['data']->password);
    }
    $this->result['success'] = true;
    return $this->result;
  }

  public function login() {
    $isLogin   = false;
    $update    = false;
    $email     = $this->where['email'];
    $password  = $this->where['password'];

    $cek_user  = $this->db->from('user')->where(['email' => $email])->get()->row();
    if ($cek_user) {
      $cek_user->is_verif_email = $cek_user->is_verif;
      $data_password            = ($cek_user) ? $this->all_library->decodeString($cek_user->password) : '';
      if ($password == $data_password) {
        $status  = 'Login Behasil';
        $update  = $this->db->where(['email' => $email])->update('user', ['is_active' => '1']);
        $isLogin = true;
        $cek_user->user_img_preview = ($cek_user->user_img) ? URL_PROFILE.'/thumb_'.$cek_user->user_img : '';
        if ($cek_user->is_verif == '0') {
            $code   = ($cek_user->code) ? $cek_user->code : rand(100000,999999);
            $this->db->where(['email' => $email])->update('user', ['code' => $code]);
            if ($cek_user->role == '2') {
                $status  = 'Akun belum diverifikasi, cek email atau whatsapp anda';
                $message = '*'.$cek_user->nama.'*'.' Klik link untuk melakukan verifikasi akun kamu di KosTzy, abaikan pesan ini jika kamu tidak meminta verifikasi akun ya.';
                $message .= ' https://kostzy.albazars.id/mail-verif?code='.$code;
                $this->requestVerifWa($cek_user->notelp, $message);

                $title     = 'Verifikasi Email';
                $pre_title = 'Hi '.$cek_user->nama.' verifikasi email management di KosTzy';
                $message   = '<p> <b>'.$cek_user->nama.'</b> Klik link untuk melakukan verifikasi akun management di KosTzy, abaikan pesan ini jika management tidak meminta verifikasi akun ya.</p>';
                $message  .= '<a style="border-radius: 3px; background-color: #1db7b7; color: white; padding: 2px 12px; text-decoration: none;" href="'.base_url('mail-verif?code='.$code).'" target="_blank">
                                Verifikasi Email
                              </a>';
                $isVerif = $this->requestVerifEmail($cek_user->user_id, $cek_user->email, $code, $title, $pre_title, $message);
            } else if ($cek_user->role == '3') {
                $status = 'Akun belum diverifikasi, menunggu owner melakukan verifikasi akun anda';
                $owner  = $this->db->from('user')->where(['role' => '1'])->get()->result();
                foreach ($owner as $key => $value) {
                  $noWa    = ($value->notelp) ? $value->notelp : '';
                  $message = '*'.$cek_user->nama.'*'.' Melakukan pendaftaran klik link untuk verifikasi akun management kos kamu di KosTzy, abaikan pesan ini jika kamu tidak ingin verifikasi akun ya.';
                  $message .= ' https://kostzy.albazars.id/mail-verif?code='.$code;
                  $this->requestVerifWa($noWa, $message);

                  $title     = 'Verifikasi Email';
                  $pre_title = 'Hi '.$value->nama.' verifikasi email management di KosTzy';
                  $message   = '<p> <b>'.$value->nama.'</b> Klik link untuk melakukan verifikasi akun management di KosTzy, abaikan pesan ini jika management tidak meminta verifikasi akun ya.</p>';
                  $message  .= '<a style="border-radius: 3px; background-color: #1db7b7; color: white; padding: 2px 12px; text-decoration: none;" href="'.base_url('mail-verif?code='.$code).'" target="_blank">
                                  Verifikasi Email
                                </a>';
                  $isVerif = $this->requestVerifEmail($value->user_id, $value->email, $code, $title, $pre_title, $message);
                }
            }
        }
      }else {
        $update  = false;
        $isLogin = false;
      }
    }

    $this->result['data']    = ($update) ? $cek_user : '';
    $this->result['message'] = ($update) ? $status : 'Akun tidak ditemukan.';
    $this->result['success'] = ($update) ? true : false;
    return $this->result;
  }

  public function register() {
    $isVerif             = true;
    $data                = [];
    $nama                = $this->data['nama'];
    $password            = $this->all_library->encodeString($this->data['password']);
    $email               = $this->data['email'];
    $birthday            = date('Y-m-d', strtotime($this->data['birthday']));
    $alamat              = $this->data['alamat'];
    $gender              = $this->data['gender'];
    $role                = $this->data['role'];
    $pekerjaan           = $this->data['pekerjaan'];
    $no_rekening         = $this->data['no_rekening'];
    $bank                = $this->data['bank'];
    $nama_pemilik        = $this->data['nama_pemilik'];
    $nama_darurat_satu   = $this->data['nama_darurat_satu'];
    $notelp_darurat_satu = $this->all_library->format_notelp($this->data['notelp_darurat_satu']);
    $nama_darurat_dua    = $this->data['nama_darurat_dua'];
    $notelp_darurat_dua  = $this->all_library->format_notelp($this->data['notelp_darurat_dua']);
    $session_upload_id   = $this->data['session_upload_id'];
    $notelp              = $this->all_library->format_notelp($this->data['notelp']);
    $user_id             = isset($this->data['user_id']) ? $this->data['user_id'] : '';

    $cek_user = $this->db->from('user')->where([
        'email'  => $email,
        'notelp' => $notelp
    ])->get()->row();

    if (!$cek_user || $user_id) {
      $data = array(
        'nama'                => $nama,
        'password'            => $password,
        'email'               => $email,
        'no_rekening'         => $no_rekening,
        'bank'                => $bank,
        'nama_pemilik'        => $nama_pemilik,
        'birthday'            => $birthday,
        'alamat'              => $alamat,
        'gender'              => $gender,
        'role'                => $role,
        'pekerjaan'           => $pekerjaan,
        'nama_darurat_satu'   => $nama_darurat_satu,
        'notelp_darurat_satu' => $notelp_darurat_satu,
        'nama_darurat_dua'    => $nama_darurat_dua,
        'notelp_darurat_dua'  => $notelp_darurat_dua,
        'session_upload_id'   => $session_upload_id,
        'notelp'              => $notelp,
        'code'                => rand(100000,999999),
        'is_active'           => '0',
        'created_on'          => date('Y-m-d H:i:s')
      );

      if ($user_id) {
        unset($data['is_active']);
        unset($data['created_on']);
        $this->db->where(['user_id' => $user_id])->update('user', $data);
        $data['user_id'] = $user_id;
        $isVerif         = true;
      }else {
        $data['is_verif'] = ($data['role'] == '1') ? '1' : '0';
        $this->db->insert('user', $data);
        $insert_user = $this->db->insert_id();
        $this->db->where(['session_upload_id' => $session_upload_id]);
        $this->db->update('file_upload', ['created_by' => $insert_user]);
        if ($data['role'] == '2') {
          $message = '*'.$data['nama'].'*'.' Klik link untuk melakukan verifikasi akun kamu di KosTzy, abaikan pesan ini jika kamu tidak meminta verifikasi akun ya.';
          $message .= ' https://kostzy.albazars.id/mail-verif?code='.$data['code'];
          $this->requestVerifWa($data['notelp'], $message);

          $title     = 'Verifikasi Email';
          $pre_title = 'Verifikasi email kamu tambahkan di KosTzy';
          $message   = '<p> <b>'.$user->nama.'</b> Klik link untuk melakukan verifikasi akun kamu di KosTzy, abaikan pesan ini jika kamu tidak meminta verifikasi akun ya.</p>';
          $message  .= '<a style="border-radius: 3px; background-color: #1db7b7; color: white; padding: 2px 12px; text-decoration: none;" href="'.base_url('mail-verif?code='.$code).'" target="_blank">
                          Verifikasi Email
                        </a>';
          $isVerif = $this->requestVerifEmail($data['user_id'], $data['email'], $data['code'], $title, $pre_title, $message);
        } elseif ($data['role'] == '3') {
          $owner = $this->db->from('user')->where(['role' => '1'])->get()->result();
          foreach ($owner as $key => $value) {
            $noWa    = ($value->notelp) ? $value->notelp : '';
            $message = '*'.$data['nama'].'*'.' Melakukan pendaftaran klik link untuk verifikasi akun management kos kamu di KosTzy, abaikan pesan ini jika kamu tidak ingin verifikasi akun ya.';
            $message .= ' https://kostzy.albazars.id/mail-verif?code='.$data['code'];
            $this->requestVerifWa($noWa, $message);

            $title     = 'Verifikasi Email';
            $pre_title = 'Hi '.$value->nama.' verifikasi email management di KosTzy';
            $message   = '<p> <b>'.$value->nama.'</b> Klik link untuk melakukan verifikasi akun management di KosTzy, abaikan pesan ini jika management tidak meminta verifikasi akun ya.</p>';
            $message  .= '<a style="border-radius: 3px; background-color: #1db7b7; color: white; padding: 2px 12px; text-decoration: none;" href="'.base_url('mail-verif?code='.$data['code']).'" target="_blank">
                            Verifikasi Email
                          </a>';
            $isVerif = $this->requestVerifEmail($value->user_id, $value->email, $data['code'], $title, $pre_title, $message);
          }
        }
      }
    }

    $data = ($user_id) ? $this->db->from('user')->where(['user_id' => $user_id])->get()->row() : $data;
    if ($user_id) {
      $isVerif = true;
      $data->user_img_preview = ($data->user_img) ? URL_PROFILE.'/thumb_'.$data->user_img : '';
    }
    $this->result['data']    = ((!$cek_user) ? (($isVerif) ? $data : NULL)  : NULL);
    $this->result['success'] = ((!$cek_user) ? (($isVerif) ? true   : false) : (($user_id) ? true : false));
    $this->result['message'] = (($cek_user)  ? 'Akun Sudah Terdaftar'  : (($isVerif) ? 'Cek WA untuk Verifikasi Akun' : 'Verifikasi Akun Gagal'));
    return $this->result;
  }

  public function requestVerifWa($wa, $message) {
    $wa = $this->all_library->wa(array(
      'phone'   => $wa,
      'message' => $message
    ));
    return true;
  }

  public function requestVerifEmail($user_id, $email, $code, $title, $pre_title, $content) {
    // email
    $user      = $this->db->from('user')->where('user_id', $user_id)->get()->row();
    $msg_title = 'Hai '.$user->nama;
    $checkMail = $this->db->from('email_sent')->where(array(
      'email_to'  => $email,
      'title'     => $title,
      'pre_title' => $pre_title,
      'msg_title' => $msg_title,
      'content'   => $content,
      'is_sent'   => '0',
      'created_on <=' => date('Y-m-d H:i:s')
    ))->get()->row();

    if (!$checkMail) {
      $config['protocol']    = 'smtp';
      $config['smtp_host']   = 'smtp.googlemail.com';
      $config['smtp_user']   = 'ars.studio.indo@gmail.com';
      $config['smtp_pass']   = 'Andririzki12345';
      $config['smtp_port']   = 465;
      $config['smtp_crypto'] = 'ssl';
      $config['charset']     = 'utf-8';
      $config['mailtype']    = 'html';
      $config['newline']     = "\r\n";
      $config['crlf']        = "\r\n";

      $this->load->library('email', $config);
      $this->email->from('no-reply@gmail.com', 'Kostzy');
      $this->email->to($email);
      $this->email->subject($title);
      $this->email->message($content);
      
      if ($this->email->send()) {
          return $this->all_library->mail($email, $title, $pre_title, $msg_title, $content);
      } else {
          return false;
      }
    }
  }

  public function setCode () {
    $data = $this->db->from('user')->where(['notelp' => $this->where['notelp']])->get()->row();
    if ($data) {
        $newCode = $data->code;
        if (!$newCode) {
          $newCode = rand(100000,999999);
          $this->db->where(['user_id' => $data->user_id]);
          $update = $this->db->update('user', ['code' => $newCode]);
        }
        $message = 'Kode OTP Kostzy anda *'.$newCode.'*, Abaikan chat ini jika anda tidak meminta kode OTP';
        $wa = $this->all_library->wa(array(
            'phone'   => $this->where['notelp'],
            'message' => $message
        ));

        
        $title     = 'Kode OTP';
        $pre_title = 'Kode OTP di KosTzy';
        $message   = '<p><b>'.$data->nama.'</b> Kode OTP Kostzy anda <b>'.$newCode.'</b>, Abaikan chat ini jika anda tidak meminta kode OTP.</p>';
        $this->requestVerifEmail($data->user_id, $data->email, $data->code, $title, $pre_title, $message);
    }
      

    $this->result['data']    = ($data) ? $data : false;
    $this->result['success'] = ($data) ? true : false;
    return $this->result;
  }

  public function checkCode () {
    $data = $this->db->from('user')->where([
        'user_id' => $this->where['user_id'],
        'code' => $this->where['code']
    ])->get()->row();
    if ($data) {
        $this->db->where(['user_id' => $data->user_id]);
        $update = $this->db->update('user', ['code' => NULL]);
    }
      

    $this->result['success']   = ($data) ? true : false;
    return $this->result;
  }

  public function setPasswordBaru () {
    $password = $this->all_library->encodeString($this->data['password']);
    $this->db->where(['user_id' => $this->where['user_id']]);
    $update = $this->db->update('user', [
        'password' => $password
    ]);      

    $this->result['success']   = ($update) ? true : false;
    return $this->result;
  }

}

?>
