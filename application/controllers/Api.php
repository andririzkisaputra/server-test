<?php

/**
 *
 */
class Api extends CI_Controller {

  public function __construct() {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
      header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { // Access-Control headers are received during OPTIONS requests
      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
      header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
      exit(0);
    }
    parent::__construct();
  }

  public function index() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $postdata = file_get_contents("php://input");
      if(isset($postdata)) {
        $r     = json_decode($postdata, TRUE);
        $model = (isset($r['model'])) ? $r['model'] : DEFAULT_MODEL;
        print_r($this->pdata($model, $r['key'], (isset($r['table']) ? $r['table'] : ''), (isset($r['table_key']) ? $r['table_key'] : ''), (isset($r['page']) ? $r['page'] : 0), (isset($r['perpage']) ? $r['perpage'] : 0), (isset($r['data']) ? $r['data'] : ''), (isset($r['where']) ? $r['where'] : '')));
      }
    }elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
      $model = ($this->input->get('model')) ? $this->input->get('model') : DEFAULT_MODEL;
      print_r($this->pdata($model, $this->input->get('key'), $this->input->get('table'), $this->input->get('table_key'), $this->input->get('page'), $this->input->get('perpage'), json_decode($this->input->get('data'), true), json_decode($this->input->get('where'), true)));
    }
  }

  public function pdata($model, $key, $table = null, $table_key = null, $page = 0, $perpage = 10, $data = array(), $where = array()) {
    if (empty($key)) {
      $data = array(
        'success' => false,
        'data'    => '',
        'message' => 'ACTION_NOT_SET',
      );
    }elseif (empty($table)) {
      $data = array(
        'success' => false,
        'data'    => '',
        'message' => 'TABLE_NOT_SET',
      );
    }else {
      if ($model != DEFAULT_MODEL) {
        $this->load->model($model);
      }
      $data = $this->$model->_pre($key, $table, $table_key, $page, $perpage, $data, $where);
    }
    return json_encode($data);
  }

  public function upload() {
    $key            = $this->input->post('key');
    $result         = (object) array(
        'filename' => '',
        'preview'  => '',
        'success'  => '',
        'why'      => ''
    );
    switch ($key) {
      case 'all':
        $user_id = $this->input->post('user_id');
        $path    = '/'.(($this->input->post('path')) ? $this->input->post('path').'/' : '/');

        $config['upload_path']   = DIR_UPLOAD.$path;
        $config['allowed_types'] = ALLOW_IMG;
        $config['overwrite']     = FALSE;
        $config['encrypt_name']  = TRUE;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('files')) {
          $error = array('error' => $this->upload->display_errors());
          $result->why      = strip_tags($error['error']);
          $result->success  = false;
        }else {
          $dataimg    = array('upload_data' => $this->upload->data());
          $file_thumb = $this->resize($dataimg);
          $update     = true;
          if ($this->input->post('session_upload_id')) {
            $checkFileUpdate = $this->all_model->native_find_by('file_upload', ['session_upload_id' => $this->input->post('session_upload_id')]);
            $data            = array(
              'session_upload_id' => $this->input->post('session_upload_id'),
              'file_name'         => $dataimg['upload_data']['file_name'],
              'created_by'        => $user_id
            );
            // if ($checkFileUpdate) {
            //   unset($data['created_by']);
            //   $update = $this->all_model->native_update(['file_upload_id' => $checkFileUpdate->file_upload_id], 'file_upload', $data);
            // }else {
              $update = $this->all_model->insert('file_upload', $data);
            // }
          }
          if ($update) {
            $result->success  = true;
            $result->why      = 'image uploaded';
            $result->filename = $dataimg['upload_data']['file_name'];
            $result->preview  = base_url('uploads'.$path.$file_thumb);
          }else {
            $result->success  = false;
            $result->why      = 'failed uploading image';
            $result->filename = '';
            $result->preview  = '';
          }
        }

        print_r(json_encode($result));

        break;
      case 'profile':
        $user_id           = $this->input->post('user_id');
        $session_upload_id = $this->input->post('session_upload_id');
        $file_name         = $this->input->post('file_name');
        $path              = '/profile/';

        $config['upload_path']   = DIR_UPLOAD.$path;
        $config['allowed_types'] = ALLOW_IMG;
        $config['overwrite']     = FALSE;
        $config['encrypt_name']  = TRUE;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('files')) {
          $error = array('error' => $this->upload->display_errors());
          $result->why      = strip_tags($error['error']);
          $result->success  = false;
        }else {
          $dataimg    = array('upload_data' => $this->upload->data());
          $file_thumb = $this->resize($dataimg);
          $update     = true;
          if ($session_upload_id) {
            $checkFileUpdate = $this->all_model->native_find_by('file_upload', ['session_upload_id' => $session_upload_id, 'file_name' => $file_name]);
            $data            = array(
              'session_upload_id' => $this->input->post('session_upload_id'),
              'file_name'         => $dataimg['upload_data']['file_name'],
              'created_by'        => $user_id
            );
            // if ($checkFileUpdate) {
            //   unset($data['created_by']);
            //   $update = $this->all_model->native_update(['file_upload_id' => $checkFileUpdate->file_upload_id], 'file_upload', $data);
            // }else {
              $update = $this->all_model->insert('file_upload', $data);
            // }
          } else {
            $update = $this->all_model->native_update(['user_id' => $user_id], 'user', ['user_img' => $dataimg['upload_data']['file_name']]);
          }
          if ($update) {
            $result->success  = true;
            $result->why      = 'image uploaded';
            $result->filename = $file_thumb;
            $result->preview  = base_url('uploads'.$path.$file_thumb);
          }else {
            $result->success  = false;
            $result->why      = 'failed uploading image';
            $result->filename = '';
            $result->preview  = '';
          }

        }

        print_r(json_encode($result));

        break;
    }
  }

  public function resize($data) {
    $file_name                = 'thumb_'.$data['upload_data']['file_name'];
    $config['image_library']  = 'gd2';
    $config['source_image']   = $data['upload_data']['full_path'];
    $config['overwrite']      = TRUE;
    $config['maintain_ratio'] = TRUE;
    $config['width']          = 800;
    $config['height']         = 800;
    $this->load->library('image_lib');
    $this->image_lib->initialize($config);
    if (!$this->image_lib->resize()) {
      echo $this->image_lib->display_errors();
    }
    $this->image_lib->clear();

    $config2['image_library']  = 'gd2';
    $config2['source_image']   = $data['upload_data']['full_path'];
    $config2['new_image']      = $data['upload_data']['file_path'].$file_name;
    $config2['maintain_ratio'] = TRUE;
    $config2['width']          = 280;
    $config2['height']         = 280;
    $this->load->library('image_lib');
    $this->image_lib->initialize($config2);
    if (!$this->image_lib->resize()) {
      echo $this->image_lib->display_errors();
    }
    $this->image_lib->clear();

    return $file_name;

  }

  public function mail_verif() {
    $code   = $this->input->get('code');
    $data['alertClass'] = 'danger';
    $data['message']    = 'Ooppss... link expired.';
    if ($code) {
      $checkUser = $this->all_model->get_by('user', ['code' => $code]);
      if ($checkUser) {
        $this->all_model->update(['user_id' => $checkUser->user_id], 'user', ['code' => null, 'is_verif' => '1']);
        // notif ke user
        $this->notifikasi->send(array(
          'to'    => $checkUser->user_id,
          'from'  => '1',
          'title' => 'Status Verifikasi Akun',
          'msg'   => 'Yay... akun kamu berhasil diverifikasi, sekarang akun kamu sudah aktif.',
          'params'=> null
        ));
        $data['alertClass'] = 'success';
        $data['message']    = 'Terima kasih, verifikasi akun kamu berhasil dilakukan.';
      }
    }

    $this->load->view('verif_email/index', $data);
  }

  public function requestVerifEmail() {
    $user_id = '44';
    $email   = 'andri.rizki007@gmail.com';

    // email
    $user      = $this->db->from('user')->where('user_id', $user_id)->get()->row();
    $title     = 'Verifikasi Email';
    $pre_title = 'Verifikasi email kamu tambahkan di KosTzy';
    $msg_title = 'Hai '.$user->nama;
    $checkMail = $this->db->from('email_sent')->where(array(
      'email_to'  => $email,
      'title'     => $title,
      'pre_title' => $pre_title,
      'msg_title' => $msg_title,
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

        $newCode   = rand(100000,999999);
        $content   = '<p>Dibawah ini terdapat tombol untuk melakukan verifikasi email kamu di KosTzy, abaikan email ini jika kamu tidak meminta verifikasi email ya.</p>';
        $content   .= '
                      <a style="border-radius: 3px; background-color: #1db7b7; color: white; padding: 2px 12px; text-decoration: none;" href="'.base_url('mail-verif?code='.$newCode).'" target="_blank">
                        Verifikasi Email
                      </a>';


        $this->load->library('email', $config);
        $this->email->from('no-reply@gmail.com', 'Kostzy');
        $this->email->to($email);
        $this->email->subject($title);
        $this->email->message($content);
        
        if ($this->email->send()) {
            echo 'Sukses! email berhasil dikirim.';
            // $this->all_library->mail($email, $title, $pre_title, $msg_title, $content, $button);
        } else {
            show_error($this->email->print_debugger());
        }
    }

    $this->result['success']   = true;
    return $this->result;
  }

}

?>
