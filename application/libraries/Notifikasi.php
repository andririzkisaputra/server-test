<?php

/**
 *
 */
class Notifikasi {

  public function __construct() {
    $this->ci =& get_instance();
    $this->ci->load->model('all_model');
    $this->appID  = '703e8137-13ea-4623-97d5-63f447ea4320';
    $this->appKEY = 'ODJjYWM0MzgtZTRlOS00Y2E4LTlkN2UtM2QwNmQyZGFlZmYz';
    $this->appURL = 'https://onesignal.com/api/v1/notifications';
    $this->ci->load->library('emoji');
  }

  public function send($data) {
    $roles_to  = $this->ci->all_model->native_find_by('user', ['user_id' => $data['to']]);
    if ($data['from'] != '1') {
      $roles_fr       = $this->ci->all_model->native_find_by('user', ['user_id' => $data['from']]);
      $data['msg']    = str_replace('{pengirim}', $roles_fr->nama, $data['msg']);
      $data['title']  = str_replace('{pengirim}', $roles_fr->nama, $data['title']);
    }

    $data['msg'] = str_replace('{penerima}', $roles_to->nama, $data['msg']);

    $title = (isset($data['title'])) ? $data['title'] : 'Kostzy';
    $title = str_replace('{penerima}', $roles_to->nama, $title);

    $content = array(
       "en" => strip_tags($data['msg'])
    );

    $heading = array(
       "en" => $title,
    );

    $filters_new = array(
      array(
        'field'    => 'tag',
        'key'      => 'is_login',
        'relation' => '=',
        'value'    => '1',
      )
    );

    $oldFilter = array(
      'field'    => 'tag',
      'key'      => 'user_id',
      'relation' => '=',
      'value'    => $data['to']
    );

    if (is_array($oldFilter) && !empty($oldFilter)) {
      array_push($filters_new, $oldFilter);
    }

    $fields = array(
      'app_id'                => $this->appID,
      'filters'               => $filters_new,
      'android_group'         => $data['to'],
      'android_group_message' => array(
        'en' => 'Kamu punya $[notif_count] pemberitahuan baru'
      ),
      'contents'              => $content,
      'headings'              => $heading
    );

    if (is_array($data) && !empty($data)) {
      $fields['data'] = $data;
    }

    $fields = json_encode($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->appURL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                           'Authorization: Basic '.$this->appKEY));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);

    // tambah ke db
    $this->ci->all_model->insert('notifikasi', array(
      'to_id'       => $data['to'],
      'pesan'       => $this->ci->emoji->Encode($data['msg']),
      'judul'       => $this->ci->emoji->Encode($data['title']),
      'params'      => $data['params'],
      'created_by'  => $data['from'],
    ));

    // return $response;
    return true;
  }
}
?>
