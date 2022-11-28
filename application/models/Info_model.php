<?php
/**
 *
 */
class Info_model extends CI_Model {

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

  public function info() {
    $info = [];
    if ($this->where['role'] == '1') {
      $info = $this->db->from('info')->where(['created_by' => $this->where['user_id']])->order_by('created_on', 'DESC')->get()->result();
    } elseif ($this->where['role'] == '2') {
      $sewa = $this->db->from('sewa')->where(['created_by' => $this->where['user_id'], 'status_sewa >=' => '3', 'status_sewa <=' => '4'])->get()->row();
      if ($sewa) {
        $info = $this->db->from('info')->where('created_by', $sewa->pemilik_id)->order_by('created_on', 'DESC')->get()->result();
      }
    } elseif ($this->where['role'] == '3') {
      $this->db->from('management_kos');
      $this->db->join('properti', 'properti.properti_id = management_kos.properti_id');
      $properti = $this->db->where(['management_kos.created_by' => $this->where['user_id']])->get()->row();
      if ($properti) {
        $info = $this->db->from('info')->where('created_by', $properti->created_by)->order_by('created_on', 'DESC')->get()->result();
      }
    }

    foreach ($info as $key => $value) {
      $value->created_on_f = $this->all_library->format_date($value->created_on);
    }
    $this->result['data']    = $info;
    $this->result['success'] = ($info) ? true : false;
    return $this->result;
  }

  public function simpanInfo() {
    $data = [
      'judul'      => $this->data['judul'],
      'isi'        => $this->data['isi'],
      'created_by' => $this->data['created_by'],
      'created_on' => date('Y-m-d H:i:s'),
    ];
    $info = $this->db->insert('info', $data);
    $this->result['data']    = $info;
    $this->result['success'] = ($info) ? true : false;
    return $this->result;
  }

  public function deleteInfo() {
    $this->db->where(['info_id' => $this->where['info_id']]);
    $info = $this->db->delete('info');
    $this->result['data']    = $info;
    $this->result['success'] = ($info) ? true : false;
    return $this->result;
  }

  public function getByInfo() {
    $data = $this->db->from('info')->where('info_id', $this->where['info_id'])->get()->row();
    $this->result['data']    = $data;
    $this->result['success'] = ($data) ? true : false;
    return $this->result;
  }

  public function editInfo() {
    $data = [
      'judul'      => $this->data['judul'],
      'isi'        => $this->data['isi'],
      'created_by' => $this->data['created_by'],
      'created_on' => date('Y-m-d H:i:s'),
    ];
    $this->db->where(['info_id' => $this->data['info_id']]);
    $info = $this->db->update('info', $data);
    $this->result['success'] = ($info) ? true : false;
    return $this->result;
  }

}

?>
