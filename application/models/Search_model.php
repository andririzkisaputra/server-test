<?php
/**
 *
 */
class Search_model extends CI_Model {

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

  public function search() {

    $keyword  = (isset($this->where['keyword'])) ? $this->where['keyword'] : '';
    $keyword  = preg_replace("/[^a-zA-Z0-9 ]/", "", strtolower($keyword));
    $sort     = isset($this->where['sort']) && !empty($this->where['sort']) ? $this->where['sort'] : '';

    $properti = $this->_getProperti($this->page, $this->perpage, $sort, false, $keyword, false);
    $this->result['data'] = $properti;
    $this->result['totaldata'] = $this->_getProperti($this->page, $this->perpage, $sort, true, $keyword, false);
    return $this->result;
  }

  public function _getProperti($page = 0, $perpage = 16, $sort = false, $count = false, $keyword = '', $isJaro = false) {
    $link_properti = URL_PROPERTI.'/thumb_';
    $this->db->select('*, properti.session_upload_id AS properti_session_id, MAX(kamar.harga) AS harga_max, MIN(kamar.harga) AS harga_min');
    $this->db->from('properti');
    $this->db->join('lantai', 'lantai.properti_id = properti.properti_id');
    $this->db->join('kamar', 'kamar.lantai_id = lantai.lantai_id');
    $where = "properti.is_deleted = '0'";
    if ($keyword) {
      $where .= " AND properti.nama_properti LIKE '%{$keyword}%'";
    }
    $this->db->where($where);
    $this->db->order_by($sort)->group_by('properti.properti_id');
    if (!$count) {
      $data_properti = $this->db->limit(5)->get()->result();

      foreach ($data_properti as $key => $value) {
        $ulasan             = $this->db->select('AVG(bintang) AS bintang')->from('ulasan')->where(['properti_id' => $value->properti_id])->get()->row();
        $value->bintang     = (isset($ulasan)) ? round($ulasan->bintang, 1) : '';
        $file_name          = $this->db->select('CONCAT("'.$link_properti .'", file_name) AS gambar_link')->from('file_upload')->where(['session_upload_id' => $value->properti_session_id])->get()->row();
        $value->gambar_link = (isset($file_name->gambar_link)) ? $file_name->gambar_link : NULL;
        $value->harga_f     = $this->all_library->format_singkat_angka($value->harga);
        $value->harga_max_f = $this->all_library->format_singkat_angka($value->harga_max);
        $value->harga_min_f = $this->all_library->format_singkat_angka($value->harga_min);
      }
    } else {
      $data_properti = $this->db->get()->num_rows();
    }

    return $data_properti;
  }


}

?>
