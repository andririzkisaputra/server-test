<?php

/**
 *
 */
class Notifikasi_model extends CI_Model {

  protected $table       = 'notifikasi';
  protected $table_key   = 'notifikasi_id';
  protected $page        = 0;
  protected $perpage     = 15;
  protected $data        = array();
  protected $where       = array();
  protected $result      = array(
    'success'   => true,
    'data'      => [],
    'totaldata' => 0,
    'message'   => '-',
  );


  public function _pre($key, $table, $table_key, $page, $perpage, $data, $where) {
    // $this->table     = $table;
    // $this->table_key = $table_key;
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

  public function get_notif_count() {
    $user_id = !empty($this->data['user_id']) ? $this->data['user_id'] : '0';

    $update       = $this->get_notifikasi($user_id);
    $penjualan    = $this->get_transaksi('penjual_id', $user_id);
    $pesanan      = $this->get_transaksi('created_by', $user_id);
    $tagihan      = $this->get_tagihan($user_id);
    $keranjang    = $this->get_keranjang($user_id);
    $chat         = $this->_getUnreadChatCount($user_id);

    $notif_count = array(
      'update'    => (int) $update,
      'penjualan' => (int) $penjualan,
      'pesanan'   => (int) $pesanan,
      'tagihan'   => (int) $tagihan,
      'keranjang' => (int) $keranjang,
      'chat'      => !empty($chat->count) ? (int) $chat->count : 0,
    );

    $total_count          = array_sum($notif_count);
    $notif_count['total'] = $total_count;

    $this->result['data']      = $notif_count;
    $this->result['totaldata'] = ($this->result['data']) ? $total_count : 0;
    return $this->result;
  }

  public function _getUnreadChatCount($user_id) {
    $query = "
      SELECT SUM(chat.count) AS count
      FROM (
        SELECT from_user_id, COUNT(chat_id) AS COUNT
        FROM chat
        WHERE from_user_id != {$user_id} AND to_user_id = {$user_id} AND is_read = '0'
        GROUP BY from_user_id
      ) AS chat
    ";

    $count = $this->db->query($query)->row();
    return $count;
  }

  public function get_notifikasi($user_id) {
    return $this->db->from('notifikasi')
              ->where(array(
                'to_id' => $user_id,
                'new'   => '1'
              ))
              ->get()->num_rows();
  }

  public function get_transaksi($field, $user_id) {
    return $this->db->from('transaksi')
              ->where($field, $user_id)
              ->where_in('status_pesanan', ['1', '2', '3', '4', '5', '9'])
              ->get()->num_rows();
  }

  public function get_tagihan($user_id) {
    return $this->db->from('tagihan')
              ->where('created_by', $user_id)
              ->where_in('status_pembayaran', ['1','5'])
              ->get()->num_rows();
  }

  public function get_keranjang($user_id) {
    $transaksi = $this->db->from('transaksi')
              ->where('created_by', $user_id)
              ->where_in('status_pesanan', ['0'])
              ->get()->num_rows();
    // $keranjang = 0;
    // if ($transaksi) {
    //   foreach ($transaksi as $key => $value) {
    //     $keranjang += $this->db->from('keranjang')->where(['transaksi_id' => $value->transaksi_id])->get()->num_rows();
    //   }
    // }

    return $transaksi;
  }

  public function removeNotif() {
    $this->result['success'] = $this->db->where($this->where)->update('notifikasi', ['new' => '0']);
    return $this->result;
  }

  public function get_last_query() {
    return $this->db->last_query();
  }

  public function find_all_data_limit() {
    $data  = $this->get_find_all_data_limit('result');
    $count = $this->get_find_all_data_limit('num_rows');
    $this->db->where($this->where)->update($this->table, [
        'is_read' => '1'
    ]);
    $this->result['data']      = $data;
    $this->result['success']   = true;
    $this->result['totaldata'] = $count;
    return $this->result;
  }

  public function get_find_all_data_limit($get) {
    $this->db->from($this->table);
    $this->db->where($this->where);
    $this->db->order_by('created_on DESC');

    if ($get == 'result') {
      $this->db->limit($this->perpage, $this->page);
    }

    $data  = $this->db->get()->$get();

    if ($get == 'result') {
      foreach ($data as $key => $value) {
        $value->pesan      = $this->emoji->Decode($value->pesan);
        $value->created_on = $this->all_library->dateDiff($value->created_on);
      }
    }

    return $data;
  }

  public function delete_all() {
    $this->result['success']   = $this->db->delete('notifikasi', $this->where);
    return $this->result;
    //log All
    $this->log_library->create_log_master($this->get_last_query(), array(), 'notifikasi');
  }

}

 ?>
