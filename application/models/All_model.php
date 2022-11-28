<?php

/**
 *
 */
class All_model extends CI_Model {

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

  public function get_laporan() {
    $owner = $this->db->from('user')->where(['user_id' => $this->where['user_id']])->get()->row();
    if ($owner->role == '3') {
        $management_kos = $this->db->select('properti.created_by AS pemilik_id')->from('management_kos')->join('properti', 'properti.properti_id = management_kos.properti_id')->where(['management_kos.created_by' => $this->where['user_id']])->get()->row();
        if ($management_kos) {
            $owner = $this->db->from('user')->where(['user_id' => $management_kos->pemilik_id])->get()->row();
        }
    }
    $bulan       = $this->where['bulan'];
    $tahun       = $this->where['tahun'];
    $masuk       = $this->db->select('kategori_pemasukan as tipe, kategori_pemasukan_id as id')->from('kategori_pemasukan')->get()->result();
    $keluar      = $this->db->select('kategori_pengeluaran as tipe, kategori_pengeluaran_id as id')->from('kategori_pengeluaran')->get()->result();
    $netIncome   = 0;
    $netOutcome  = 0;

    $this->db->select('SUM(deposit) as nilai_deposit')->from('sewa');
    $this->db->where('pemilik_id =', $owner->user_id, FALSE);
    $this->db->where('MONTH(tanggal_sewa) =', $bulan, FALSE);
    $this->db->where('YEAR(tanggal_sewa) =', $tahun, FALSE);
    $this->db->where("(status_sewa = '2'", NULL, FALSE);
    $this->db->or_where("status_sewa = '3'", NULL, FALSE);
    $this->db->or_where("status_sewa = '4'", NULL, FALSE);
    $this->db->or_where("status_sewa = '5'", NULL, FALSE);
    $this->db->or_where("status_sewa = '7')", NULL, FALSE);
    $dataDeposit = $this->db->get()->row();
    $deposit     = ($dataDeposit) ? $dataDeposit->nilai_deposit : 0;
    if ($masuk) {
      $total    = (object) ['tipe' => 'Total Revenues', 'id' => '100', 'is_total' => true];
      array_push($masuk, $total);
      $totalRev = 0;
      foreach ($masuk as $key => $value) {
        $nominal  = 0;
        if (strpos(strtolower($value->tipe), 'rent') !== false) {
          $this->db->select('SUM(harga_sewa) as nilai_sewa')->from('sewa');          
          $this->db->where('pemilik_id =', $owner->user_id, FALSE);
          $this->db->where('MONTH(tanggal_sewa) =', $bulan, FALSE);
          $this->db->where('YEAR(tanggal_sewa) =', $tahun, FALSE);
          $this->db->where("(status_sewa = '2'", NULL, FALSE);
          $this->db->or_where("status_sewa = '3'", NULL, FALSE);
          $this->db->or_where("status_sewa = '4'", NULL, FALSE);
          $this->db->or_where("status_sewa = '5'", NULL, FALSE);
          $this->db->or_where("status_sewa = '7')", NULL, FALSE);
          $sewa = $this->db->get()->row();
          $adjustment_sum = $this->db->select('SUM(total) as sum_total_rent')->from('adjustment')->where(['created_by' => $owner->user_id, 'kategori_id' => $value->id,'is_min' => '0', 'MONTH(tanggal)' => $bulan, 'YEAR(tanggal)' => $tahun])->get()->row();
          $adjustment_min = $this->db->select('SUM(total) as min_total_rent')->from('adjustment')->where(['created_by' => $owner->user_id, 'kategori_id' => $value->id,'is_min' => '1', 'MONTH(tanggal)' => $bulan, 'YEAR(tanggal)' => $tahun])->get()->row();
          $nominal = ($sewa) ? $sewa->nilai_sewa : 0;
          $nominal = ($adjustment_sum) ? $nominal+$adjustment_sum->sum_total_rent : $nominal+0;
          $nominal = ($adjustment_min) ? $nominal-$adjustment_min->min_total_rent : $nominal-0;
          $totalRev+= $nominal;
        }

        if (strpos(strtolower($value->tipe), 'parking') !== false) {
          $this->db->select('SUM(harga_parkir_motor+harga_parkir_mobil) as nilai_sewa')->from('sewa');          
          $this->db->where('pemilik_id =', $owner->user_id, FALSE);
          $this->db->where('MONTH(tanggal_sewa) =', $bulan, FALSE);
          $this->db->where('YEAR(tanggal_sewa) =', $tahun, FALSE);
          $this->db->where("(status_sewa = '2'", NULL, FALSE);
          $this->db->or_where("status_sewa = '3'", NULL, FALSE);
          $this->db->or_where("status_sewa = '4'", NULL, FALSE);
          $this->db->or_where("status_sewa = '5'", NULL, FALSE);
          $this->db->or_where("status_sewa = '7')", NULL, FALSE);
          $sewa = $this->db->get()->row();
          $adjustment_sum = $this->db->select('SUM(total) as sum_total_rent')->from('adjustment')->where(['created_by' => $owner->user_id, 'kategori_id' => $value->id,'is_min' => '0', 'MONTH(tanggal)' => $bulan, 'YEAR(tanggal)' => $tahun])->get()->row();
          $adjustment_min = $this->db->select('SUM(total) as min_total_rent')->from('adjustment')->where(['created_by' => $owner->user_id, 'kategori_id' => $value->id,'is_min' => '1', 'MONTH(tanggal)' => $bulan, 'YEAR(tanggal)' => $tahun])->get()->row();
          $nominal = ($sewa) ? $sewa->nilai_sewa : 0;
          $nominal = ($adjustment_sum) ? $nominal+$adjustment_sum->sum_total_rent : $nominal+0;
          $nominal = ($adjustment_min) ? $nominal-$adjustment_min->min_total_rent : $nominal-0;
          $totalRev+= $nominal;
        }

        if (strpos(strtolower($value->tipe), 'deposit') !== false) {
          $this->db->select('SUM(deposit) as nilai_sewa')->from('sewa');          
          $this->db->where('pemilik_id =', $owner->user_id, FALSE);
          $this->db->where('MONTH(tanggal_sewa) =', $bulan, FALSE);
          $this->db->where('YEAR(tanggal_sewa) =', $tahun, FALSE);
          $this->db->where("(status_sewa = '2'", NULL, FALSE);
          $this->db->or_where("status_sewa = '3'", NULL, FALSE);
          $this->db->or_where("status_sewa = '4'", NULL, FALSE);
          $this->db->or_where("status_sewa = '5'", NULL, FALSE);
          $this->db->or_where("status_sewa = '7')", NULL, FALSE);
          $sewa = $this->db->get()->row();
          $adjustment_sum = $this->db->select('SUM(total) as sum_total_rent')->from('adjustment')->where(['created_by' => $owner->user_id, 'kategori_id' => $value->id,'is_min' => '0', 'MONTH(tanggal)' => $bulan, 'YEAR(tanggal)' => $tahun])->get()->row();
          $adjustment_min = $this->db->select('SUM(total) as min_total_rent')->from('adjustment')->where(['created_by' => $owner->user_id, 'kategori_id' => $value->id,'is_min' => '1', 'MONTH(tanggal)' => $bulan, 'YEAR(tanggal)' => $tahun])->get()->row();
          $nominal = ($sewa) ? $sewa->nilai_sewa : 0;
          $nominal = ($adjustment_sum) ? $nominal+$adjustment_sum->sum_total_rent : $nominal+0;
          $nominal = ($adjustment_min) ? $nominal-$adjustment_min->min_total_rent : $nominal-0;
          $totalRev+= $nominal;
        }

        if (strpos(strtolower($value->tipe), 'other') !== false) {
          $this->db->select('SUM(tambahan_biaya) as nilai_sewa')->from('sewa');          
          $this->db->where('pemilik_id =', $owner->user_id, FALSE);
          $this->db->where('MONTH(tanggal_sewa) =', $bulan, FALSE);
          $this->db->where('YEAR(tanggal_sewa) =', $tahun, FALSE);
          $this->db->where("(status_sewa = '2'", NULL, FALSE);
          $this->db->or_where("status_sewa = '3'", NULL, FALSE);
          $this->db->or_where("status_sewa = '4'", NULL, FALSE);
          $this->db->or_where("status_sewa = '5'", NULL, FALSE);
          $this->db->or_where("status_sewa = '7')", NULL, FALSE);
          $sewa = $this->db->get()->row();
          // $sewa = $this->db->select('SUM(tambahan_biaya) as nilai_sewa')->from('sewa')->where(['pemilik_id' => $owner->user_id, 'status_sewa >=' => '2', 'status_sewa <=' => '5', 'MONTH(tanggal_sewa)' => $bulan, 'YEAR(tanggal_sewa)' => $tahun])->get()->row();
          $adjustment_sum = $this->db->select('SUM(total) as sum_total_rent')->from('adjustment')->where(['created_by' => $owner->user_id, 'kategori_id' => $value->id,'is_min' => '0', 'MONTH(tanggal)' => $bulan, 'YEAR(tanggal)' => $tahun])->get()->row();
          $adjustment_min = $this->db->select('SUM(total) as min_total_rent')->from('adjustment')->where(['created_by' => $owner->user_id, 'kategori_id' => $value->id,'is_min' => '1', 'MONTH(tanggal)' => $bulan, 'YEAR(tanggal)' => $tahun])->get()->row();
          $nominal = ($sewa) ? $sewa->nilai_sewa : 0;
          $nominal = ($adjustment_sum) ? $nominal+$adjustment_sum->sum_total_rent : $nominal+0;
          $nominal = ($adjustment_min) ? $nominal-$adjustment_min->min_total_rent : $nominal-0;
          $totalRev+= $nominal;
        }

        if (strpos(strtolower($value->tipe), 'total') !== false) {
          $nominal    = $totalRev;
          $netIncome += $nominal;
        }
        $value->nominal  = ($nominal) ? $this->all_library->format_harga($nominal) : 'Rp 0';
        $value->is_total = (isset($value->is_total)) ? true : false;

      }
    }

    if ($keluar) {
      $total    = (object) ['tipe' => 'Total Expenses', 'id' => '100', 'is_total' => true];
      array_push($keluar, $total);
      $totalExp = 0;
      foreach ($keluar as $key => $value) {
        $pengeluaran     = $this->db->select('SUM(total) as nilai')->from('pengeluaran')->where(['created_by' => $owner->user_id, 'kategori_id' => $value->id, 'MONTH(tanggal)' => $bulan, 'YEAR(tanggal)' => $tahun])->get()->row();
        $nominal         = ($pengeluaran) ? $pengeluaran->nilai : 0;
        $totalExp       += $nominal;

        if (strpos(strtolower($value->tipe), 'other') !== false) {
            $sewa      = $this->db->select('SUM(refund) as refund')->from('sewa')->where(['pemilik_id' => $owner->user_id, 'status_sewa >=' => '2', 'status_sewa <=' => '5', 'MONTH(tanggal_sewa)' => $bulan, 'YEAR(tanggal_sewa)' => $tahun])->get()->row();
            $nominal   = ($sewa) ? $sewa->refund : 0;
            $totalExp += $nominal;
        }

        if (strpos(strtolower($value->tipe), 'total') !== false) {
          $nominal     = $totalExp;
          $netOutcome += $nominal;
        }

        $value->nominal  = ($nominal) ? $this->all_library->format_harga($nominal) : 'Rp 0';
        $value->is_total = (isset($value->is_total)) ? true : false;
      }
    }

    $totalNetIncome = $netIncome - $netOutcome;
    $this->result['data'] = array(
      0 => [
        'title'   => 'Revenues',
        'isSingle'=> false,
        'data'    => $masuk
      ],
      1 => [
        'title'   => 'Expenses',
        'isSingle'=> false,
        'data'    => $keluar
      ],
      2 => [
        'title'   => 'Net Income',
        'isSingle'=> true,
        'data'    => ($totalNetIncome) ? $this->all_library->format_harga($totalNetIncome) : 'Rp 0',
      ],
    //   3 => [
    //     'title'   => 'Total Deposit',
    //     'isSingle'=> true,
    //     'data'    => ($deposit) ? $this->all_library->format_harga($deposit) : 'Rp 0'
    //   ],
    );
    $owner->gambar_link      = URL_PROFILE.'/'.$owner->user_img;
    $this->result['owner']   = ($owner) ? $owner : null;
    $this->result['success'] = true;
    return $this->result;
  }

  public function get_by($table, $where, $return = 'row') {
    return $this->db->from($table)->where($where)->get()->$return();
  }

  public function update($where, $table, $data) {
    return $this->db->where($where)->update($table, $data);
  }

  public function get_notif() {
    $this->db->from('notifikasi');
    $this->db->join('user', 'user.user_id = notifikasi.to_id', 'LEFT');
    $this->db->where('notifikasi.to_id', $this->where['created_by']);
    $this->db->order_by('notifikasi.created_on', 'DESC');
    $data = $this->db->get()->result();
    if ($data) {
      foreach ($data as $key => $value) {
        $value->pesan_short  = (strlen($value->pesan) > 100) ? substr($value->pesan, 0, 100).'...' : $value->pesan;
        $value->created_on_f = $this->all_library->format_date($value->created_on);
      }
    }

    $this->result['data']      = $data;
    $this->result['success']   = true;
    return $this->result;
  }

  public function get_pengeluaran() {
    if ($this->where['role'] == '3') {
      $this->db->from('management_kos');
      $this->db->join('properti', 'properti.properti_id = management_kos.properti_id');
      $user = $this->db->where('management_kos.created_by', $this->where['created_by'])->get()->row();
      $user_id = $user->created_by;
    } else {
      $user_id = $this->where['created_by'];
    }
    $this->db->from('pengeluaran');
    $this->db->where('created_by', $user_id);
    $this->db->order_by('created_on', 'DESC');
    $data = $this->db->get()->result();
    if ($data) {
      foreach ($data as $key => $value) {
        $kategori            = $this->db->from('kategori_pengeluaran')->where('kategori_pengeluaran_id', $value->kategori_id)->get()->row();
        $value->kategori     = ($kategori) ? $kategori : [];
        $value->jumlah_f     = $this->all_library->format_harga($value->jumlah);
        $value->total_f      = $this->all_library->format_harga($value->total);
        $value->tanggal_f    = $this->all_library->date($value->tanggal);
        $value->created_on_f = $this->all_library->format_date($value->created_on);
      }
    }

    $this->result['data']      = $data;
    $this->result['success']   = true;
    return $this->result;
  }

  public function get_adjustment() {
    if ($this->where['role'] == '3') {
      $this->db->from('management_kos');
      $this->db->join('properti', 'properti.properti_id = management_kos.properti_id');
      $user = $this->db->where('management_kos.created_by', $this->where['created_by'])->get()->row();
      $user_id = $user->created_by;
    } else {
      $user_id = $this->where['created_by'];
    }
    $this->db->from('adjustment');
    $this->db->where('created_by', $user_id);
    $this->db->order_by('created_on', 'DESC');
    $data = $this->db->get()->result();
    if ($data) {
      foreach ($data as $key => $value) {
        $kategori            = $this->db->from('kategori_pemasukan')->where('kategori_pemasukan_id', $value->kategori_id)->get()->row();
        $value->kategori     = ($kategori) ? $kategori : [];
        $value->total_f      = $this->all_library->format_harga($value->total);
        $value->tanggal_f    = $this->all_library->date($value->tanggal);
        $value->created_on_f = $this->all_library->format_date($value->created_on);
      }
    }

    $this->result['data']      = $data;
    $this->result['success']   = true;
    return $this->result;
  }

  public function get_pemasukan() {
    if ($this->where['role'] == '3') {
      $this->db->from('management_kos');
      $this->db->join('properti', 'properti.properti_id = management_kos.properti_id');
      $user = $this->db->where('management_kos.created_by', $this->where['created_by'])->get()->row();
      $user_id = $user->created_by;
    } else {
      $user_id = $this->where['created_by'];
    }
    $where = "pemilik_id = {$user_id} AND ((status_sewa >= '3' AND status_sewa <= '5') OR (status_sewa = '7'))";
    $this->db->from('sewa');
    $this->db->where($where);
    $this->db->order_by('created_on', 'DESC');
    $data = $this->db->get()->result();
    if ($data) {
      foreach ($data as $key => $value) {
        $kategori = $this->db->select('kategori_pemasukan')->from('kategori_pemasukan')->get()->result();
        $kategori = ($kategori) ? $kategori : [];
        foreach ($kategori as $k => $v) {
          if ($k == 0) {
            $value->kategori['kategori_pemasukan'][] = $v->kategori_pemasukan;
            $value->kategori['harga_f'][]            = ($value->harga_sewa) ? $this->all_library->format_harga($value->harga_sewa) : 'Rp 0';
          } elseif ($k == 1) {
            $value->kategori['kategori_pemasukan'][] = $v->kategori_pemasukan;
            $harga_parkir                            = $value->harga_parkir_motor+$value->harga_parkir_mobil;
            $value->kategori['harga_f'][]            = ($harga_parkir) ? $this->all_library->format_harga($harga_parkir) : 'Rp 0';
          } else {
            $value->kategori['kategori_pemasukan'][] = $v->kategori_pemasukan;
            $value->kategori['harga_f'][]            = 'Rp 0';
          }
        }
        $value->tanggal_sewa_f = $this->all_library->format_date($value->tanggal_sewa, true, true, false);
        $value->created_on_f   = $this->all_library->format_date($value->created_on);
      }
    }

    $this->result['data']      = $data;
    $this->result['success']   = true;
    return $this->result;
  }

  public function get_pengeluaran_need() {
    $data['kategori_pengeluaran'] = $this->db->from('kategori_pengeluaran')->get()->result();

    $this->result['data']      = $data;
    $this->result['success']   = true;
    return $this->result;
  }

  public function get_pemasukan_need() {
    $data['kategori_pemasukan'] = $this->db->from('kategori_pemasukan')->get()->result();

    $this->result['data']      = $data;
    $this->result['success']   = true;
    return $this->result;
  }

  public function add_pengeluaran() {
    $data = array(
      'qty'            => $this->data['qty'],
      'kategori_id'    => $this->data['kategori_id'],
      'total'          => $this->data['total'],
      'jumlah'         => $this->data['jumlah'],
      'keterangan'     => $this->data['keterangan'],
      'tanggal'        => date('Y-m-d', strtotime($this->data['tanggal'])),
      'created_by'     => $this->data['created_by'],
    );

    if (isset($this->data['pengeluaran_id']) && $this->data['pengeluaran_id']) {
      $proses = $this->db->where('pengeluaran_id', $this->data['pengeluaran_id'])->update('pengeluaran', $data);
    }else {
      $proses = $this->db->insert('pengeluaran', $data);
    }

    $this->result['data']      = $data;
    $this->result['success']   = true;
    return $this->result;
  }

  

  public function add_adjustment() {
    $data = array(
      'kategori_id' => $this->data['kategori_id'],
      'total'       => $this->data['total'],
      'keterangan'  => $this->data['keterangan'],
      'is_min'      => $this->data['is_min'],
      'tanggal'     => date('Y-m-d', strtotime($this->data['tanggal'])),
      'created_by'  => $this->data['created_by'],
    );

    if (isset($this->data['adjustment_id']) && $this->data['adjustment_id']) {
      $proses = $this->db->where('adjustment_id', $this->data['adjustment_id'])->update('adjustment', $data);
    }else {
      $proses = $this->db->insert('adjustment', $data);
    }

    $this->result['data']      = $data;
    $this->result['success']   = true;
    return $this->result;
  }

  public function del_adjustment() {
    $proses                    = $this->db->where('adjustment_id', $this->data['adjustment_id'])->delete('adjustment');
    $this->result['success']   = ($proses) ? true : false;
    return $this->result;
  }

  public function del_pengeluaran() {
    $proses                    = $this->db->where('pengeluaran_id', $this->data['pengeluaran_id'])->delete('pengeluaran');
    $this->result['success']   = ($proses) ? true : false;
    return $this->result;
  }

  public function get_kamar() {
    $this->db->from('sewa');
    $this->db->join('properti', 'properti.properti_id = sewa.properti_id', 'LEFT');
    $this->db->join('kamar', 'kamar.kamar_id = sewa.kamar_id', 'LEFT');
    $this->db->join('lantai', 'lantai.lantai_id = kamar.lantai_id', 'LEFT');
    $this->db->join('file_upload', 'file_upload.session_upload_id = properti.session_upload_id', 'LEFT');
    $this->db->where(['sewa.status_sewa >=' => '3', 'sewa.status_sewa <=' => '5', 'sewa.created_by' => $this->where['user_id']]);
    $this->db->group_by('sewa.kamar_id');
    $data = $this->db->get()->result();
    if ($data) {
      foreach ($data as $key => $value) {
        $value->gambar_link = URL_PROPERTI.'/thumb_'.$value->file_name;
      }
    }

    $this->result['data']      = $data;
    $this->result['success']   = true;
    return $this->result;
  }

  public function get_komplain() {
    if ($this->data['role'] == '2') {
      $this->db->select('komplain.*, komplain.created_on as con, komplain.created_by as cby, sewa.*,properti.*,lantai.*,kamar.*');
      $this->db->from('komplain');
      $this->db->join('sewa', 'sewa.sewa_id = komplain.sewa_id', 'LEFT');
      $this->db->join('properti', 'properti.properti_id = komplain.properti_id', 'LEFT');
      $this->db->join('kamar', 'kamar.kamar_id = sewa.kamar_id', 'LEFT');
      $this->db->join('lantai', 'lantai.lantai_id = kamar.lantai_id', 'LEFT');
      $data = $this->db->where('komplain.created_by', $this->where['user_id'])->order_by('komplain.created_on', 'DESC')->get()->result();
    }elseif ($this->data['role'] == '3') {
      $this->db->select('komplain.*, komplain.created_on as con, komplain.created_by as cby, sewa.*,properti.*,lantai.*,kamar.*,user.*,management_kos.*');
      $this->db->from('komplain');
      $this->db->join('sewa', 'sewa.sewa_id = komplain.sewa_id', 'LEFT');
      $this->db->join('properti', 'properti.properti_id = komplain.properti_id', 'LEFT');
      $this->db->join('management_kos', 'management_kos.properti_id = komplain.properti_id', 'LEFT');
      $this->db->join('kamar', 'kamar.kamar_id = sewa.kamar_id', 'LEFT');
      $this->db->join('lantai', 'lantai.lantai_id = kamar.lantai_id', 'LEFT');
      $this->db->join('user', 'user.user_id = komplain.created_by', 'LEFT');
      $data = $this->db->where('management_kos.created_by', $this->where['user_id'])->order_by('komplain.created_on', 'DESC')->get()->result();
    }else {
      $this->db->select('komplain.*, komplain.created_on as con, komplain.created_by as cby, sewa.*,properti.*,lantai.*,kamar.*,user.*');
      $this->db->from('komplain');
      $this->db->join('sewa', 'sewa.sewa_id = komplain.sewa_id', 'LEFT');
      $this->db->join('properti', 'properti.properti_id = komplain.properti_id', 'LEFT');
      $this->db->join('kamar', 'kamar.kamar_id = sewa.kamar_id', 'LEFT');
      $this->db->join('lantai', 'lantai.lantai_id = kamar.lantai_id', 'LEFT');
      $this->db->join('user', 'user.user_id = komplain.created_by', 'LEFT');
      $data = $this->db->where('properti.created_by', $this->where['user_id'])->order_by('komplain.created_on', 'DESC')->get()->result();
    }
    if ($data) {
      foreach ($data as $key => $value) {
        $value->created_on_f = $this->all_library->format_date($value->con);
      }
    }
    $this->result['data']      = $data;
    $this->result['success']   = true;
    return $this->result;
  }

  public function add_komplain() {
    $data = array(
      'sewa_id'     => $this->data['sewa_id'],
      'keterangan'  => $this->data['keterangan'],
      'properti_id' => $this->data['properti_id'],
      'tanggapan'   => isset($this->data['tanggapan']) ? $this->data['tanggapan'] : null,
      'created_by'  => $this->data['user_id'],
    );

    $proses = true;
    if (isset($this->data['komplain_id']) && $this->data['komplain_id']) {
      $proses = $this->db->where('komplain_id', $this->data['komplain_id'])->update('komplain', $data);
      if ($data['tanggapan']) {
        // kirim notifikasi
        $this->notifikasi->send(array(
          'to'    => $data['created_by'],
          'from'  => '1',
          'title' => 'Tanggapan Komplain',
          'msg'   => 'Tanggapan atas komplain anda : '.$data['tanggapan'],
          'params'=> json_encode(['isKomplain' => true]),
        ));
      }
    }else{
      // kirim wa
      $proses    = $this->db->insert('komplain', $data);
      $nama_user = '';
      $user      = $this->db->from('user')->where('user_id', $this->data['user_id'])->get()->row();
      $nama_user = ($user) ? $user->nama : '';
      $no_user   = ($user) ? $user->notelp : '';
      $message   = '*'.$nama_user.'* mengirimkan komplain *"'.$this->data['keterangan'].'"*, kirim whatsapp ke nomor berikut '.$no_user.' untuk merespon.';
      $message_f = $nama_user.' mengirimkan komplain "'.$this->data['keterangan'].'"';
      $owner_wa  = '';
      $properti  = $this->db->from('properti')->join('user', 'user.user_id = properti.created_by', 'LEFT')->where('properti.properti_id', $this->data['properti_id'])->get()->row();
      $owner_wa  = ($properti) ? $properti->notelp : '';
      // kirim wa
      $this->all_library->wa(array(
        'phone'   => $owner_wa,
        'message' => $message
      ));
      // kirim notifikasi
      $this->notifikasi->send(array(
        'to'    => $properti->user_id,
        'from'  => '1',
        'title' => 'Komplain',
        'msg'   => $message_f,
        'params'=> json_encode(['isKomplain' => true]),
      ));

      // management
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
            'from'  => '1',
            'title' => 'Komplain',
            'msg'   => $message_f,
            'params'=> json_encode(['isKomplain' => true]),
          ));
        }
      }
    }

    $this->result['success']   = ($proses) ? true : false;
    return $this->result;
  }

  public function find_by() {
    $this->result['data']      = $this->db->from($this->table)->where($this->where)->get()->row();
    $this->result['totaldata'] = ($this->result['data']) ? 1 : 0;
    return $this->result;
  }

  public function find_all_by() {
    $this->result['data']      = $this->db->from($this->table)->where($this->where)->get()->result();
    $this->result['totaldata'] = ($this->result['data']) ? count($this->result['data']) : 0;
    return $this->result;
  }

  public function find_all_by_order_by() {
    $this->db->from($this->table['table_1']);
    $this->db->join($this->table['table_2'], $this->data['join'], 'LEFT');
    $this->db->select($this->data['select']);
    $this->db->where($this->where);
    $this->db->order_by($this->data['order_by']);
    return $this->db->get()->result();
  }

  public function count_by() {
    $this->result['data']      = $this->db->from($this->table)->where($this->where)->get()->num_rows();
    $this->result['totaldata'] = ($this->result['data']) ? 1 : 0;
    return $this->result;
  }

  public function delete() {
    $delete = $this->db->delete($this->table, $this->where);
    $this->result['success'] = ($delete) ? true : false;
    return $this->result;
  }

  public function native_find_by($table, $where, $return = 'row') {
    return $this->db->from($table)->where($where)->get()->$return();
  }

  public function native_find_all_by($table, $where, $order = null) {
    $this->db->from($table);
    $this->db->where($where);
    if ($order) {
      $this->db->order_by($order);
    }
    return $this->db->get()->result();
  }

  public function native_count_by($table, $where) {
    return $this->db->from($table)->where($where)->get()->num_rows();
  }

  public function native_update($where, $table, $data) {
    return $this->db->where($where)->update($table, $data);
  }

  public function native_insert_batch($table, $data) {
    return $this->db->insert_batch($table, $data);
  }

  public function native_insert($table, $data) {
    return $this->db->insert_id($table, $data);
  }

  public function insert($table, $data) {
    return $this->db->insert($table, $data);
  }

  public function native_delete($table, $where) {
    return $this->db->delete($table, $where);
  }

  public function get_last_query() {
    return $this->db->last_query();
  }

  public function get_all_by($table, $where, $order = null) {
    $this->db->from($table);
    $this->db->where($where);
    if ($order) {
      $this->db->order_by($order);
    }
    return $this->db->get()->result();
  }

  public function removeImg() {
    $dataFilename = explode('/', $this->where['file_name']);
    $file_name    = str_replace('thumb_', '', $dataFilename[(count($dataFilename)-1)]);
    $delete = $this->db->delete('file_upload', array(
      'file_name'         => $file_name,
      'session_upload_id' => $this->where['session_upload_id'],
      // 'created_by'  => $this->where['user_id'],
    ));

    $path       = '/'.(($this->table) ? $this->table.'/' : '/');
    $loc        = DIR_UPLOAD.$path.$file_name;
    $thumb_loc  = DIR_UPLOAD.$path.'thumb_'.$file_name;
    // if ($delete && file_exists($loc) || file_exists($thumb_loc)) {
    //   unlink($loc);
    //   unlink($thumb_loc);
    // }

    return $this->result;
  }

  public function decodeString() {
    // $encodeString = $this->all_library->encodeString($this->data['password']);
    $decodeString = $this->all_library->decodeString($this->data['password']);
    return [
        // 'encodeString' => $encodeString,
        'decodeString' => $decodeString,
    ];
  }

  
  public function detailProfile() {
    $data_user = $this->db->from('user')->where(['user_id' => $this->where['user_id']])->get()->row();
    $data_user->gambar_link = URL_PROFILE.'/'.$data_user->user_img;
    $data_user->birthday_f = $this->all_library->format_date($data_user->birthday, false, false, false);
    $data_user->umur = $this->all_library->birthday($data_user->birthday);
    $data_user->file_upload = $this->db->from('file_upload')->where(['session_upload_id' => $data_user->session_upload_id])->get()->result();
    if (count($data_user->file_upload) > 0) {
      foreach ($data_user->file_upload as $key => $value) {
        $value->gambar_link = URL_PROFILE.'/'.$value->file_name;
      }
    } else {
      $data_user->file_upload = [];
    }
    
    $this->result['data'] = $data_user;
    return $this->result;
  }
 
  public function infoPenyewa() {
    $data_user = $this->db->from('user')->where(['user_id' => $this->where['user_id']])->get()->row();
    $data_user->gambar_link = URL_PROFILE.'/'.$data_user->user_img;
    $data_user->birthday_f = $this->all_library->format_date($data_user->birthday, false, false, false);
    $data_user->umur = $this->all_library->birthday($data_user->birthday);
    $data_user->history = $this->db->from('sewa')
                        ->join('kamar', 'kamar.kamar_id = sewa.kamar_id')
                        ->join('tipe_kamar', 'tipe_kamar.tipe_kamar_id = sewa.tipe_kamar_id')
                        ->where(['sewa.kamar_id' => $this->where['kamar_id']])->order_by('sewa.modified_on', 'DESC')->get()->result();
    foreach ($data_user->history as $key => $value) {
      $file_upload = $this->db->from('file_upload')->where(['session_upload_id' => $value->session_upload_id])->get()->row();
      $value->gambar_kamar  = (isset($file_upload)) ? URL_KAMAR.'/'.$file_upload->file_name : '';
      $value->status_sewa_f = $this->all_library->status_sewa($value->status_sewa);
      $history              = $this->db->from('histori_sewa')->where([
        'sewa_id' => $value->sewa_id,
        'status' => '1'
      ])->get()->row();
      $value->tanggal_bayar = (isset($history)) ? $this->all_library->format_date($history->created_on, true) : false;
      $value->checkin       = $this->all_library->date($value->tanggal_sewa, true);
      $value->checkout      = $this->all_library->date($value->tanggal_selesai_sewa, true);
      $value->isStatus      = ($value->status_sewa == '1' OR $value->status_sewa == '2' OR $value->status_sewa == '4') ? true : false;
      
    }
    $this->result['data'] = $data_user;
    return $this->result;
  }
  
  public function get_pdf_laporan() {
    $data_user = $this->db->from('user')->where(['user_id' => $this->where['user_id']])->get()->row();
    $properti = $this->db->from('properti')->where(['created_by' => $this->where['user_id']])->get()->row();
    $data_user->gambar_link = URL_PROFILE.'/'.$data_user->user_img;
    $pdfFilePath = $_SERVER['DOCUMENT_ROOT'].'/uploads/laporan/'.$this->where['fileName'];
    $pdf = new FPDF('P', 'cm','Letter');
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',10);                     
    $pdf->SetX(2);
    $pdf->MultiCell(10.5,0.5,strtoupper($properti->nama_properti),10,'L');
    $pdf->SetX(2);
    $pdf->MultiCell(15,0.5,strtoupper($properti->alamat),0,'L');
    $pdf->SetX(2);
    $pdf->MultiCell(19.5,0.5,'HP. '.$data_user->notelp,0,'L');
    if ($data_user->user_img) {
        $pdf->Image($data_user->gambar_link,17.9,1,2.6,2.6);
    }
    $pdf->SetLineWidth(0.1);
    $pdf->Ln(2);
    $pdf->Line(1,4,20.5,4);
    $pdf->MultiCell(19.5,0.5,strtoupper($properti->nama_properti),10,'C');
    $pdf->MultiCell(19.5,0.5,'INCOME STATEMENT',10,'C');
    $pdf->MultiCell(19.5,0.5,$this->where['period'],10,'C');
    $pdf->SetLineWidth(0.05);
    
    $pdf->Line(2,6,19.5,6);
    $pdf->SetLineWidth(0.05);
    $pdf->Line(2,6.1,19.5,6.1);
    $pdf->Ln(2);

    $pdf->SetX(2);
    $pdf->MultiCell(19.5,0.5,'REVENUES',10,'L');
    
    $pdf->SetFont('Arial','',10);

    $pdf->SetX(2);
    $pdf->Cell(9.5,0.5,'RENT REVENUES',0, 0,'L');
    $pdf->Cell(29.5,0.5,$this->where['data'][0]['data'][0]['nominal'],0, 0,'L');
    
    $pdf->SetX(2);
    $pdf->Cell(9.5,1.5,'PARKING REVENUES',0, 0,'L');
    $pdf->Cell(29.5,1.5,$this->where['data'][0]['data'][1]['nominal'],0, 0,'L');
     
    $pdf->SetX(2);
    $pdf->Cell(9.5,2.5,'DEPOSIT REVENUES',0, 0,'L');
    $pdf->Cell(29.5,2.5,$this->where['data'][0]['data'][2]['nominal'],0, 0,'L');
       
    $pdf->SetX(2);
    $pdf->Cell(9.5,3.5,'OTHER REVENUES',0, 0,'L');
    $pdf->Cell(29.5,3.5,$this->where['data'][0]['data'][3]['nominal'],0, 0,'L');

    $pdf->SetLineWidth(0.05);
    $pdf->Line(11.4,10,15.5,10);

    $pdf->SetX(4);
    $pdf->SetTextColor(27,74,110);
    $pdf->Cell(9.5,4.5,'TOTAL REVENUES',0, 0,'L');
    $pdf->SetX(15.5);
    $pdf->Cell(29.5,4.5,$this->where['data'][0]['data'][4]['nominal'],0, 0,'L');
    
    $pdf->Ln(2.7);
    $pdf->SetFont('Arial','B',10);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetX(2);
    $pdf->MultiCell(0,0,'EXPENSES',0,'L');
    
    $pdf->SetFont('Arial','',10);
    
    $pdf->SetX(2);
    $pdf->Cell(9.5,1,'OPERATING EXPENSES',0, 0,'L');
    $pdf->Cell(29.5,1,$this->where['data'][1]['data'][0]['nominal'],0, 0,'L');

    $pdf->SetX(2);
    $pdf->Cell(9.5,2,'ENVIRONTMENT EXPENSES',0, 0,'L');
    $pdf->Cell(29.5,2,$this->where['data'][1]['data'][1]['nominal'],0, 0,'L');
 
    $pdf->SetX(2);
    $pdf->Cell(9.5,3,'SALARIES & WAGES EXPENSES',0, 0,'L');
    $pdf->Cell(29.5,3,$this->where['data'][1]['data'][2]['nominal'],0, 0,'L');
 
    $pdf->SetX(2);
    $pdf->Cell(9.5,4,'MAINTENANCE & REPAIRMENT EXPENSES',0, 0,'L');
    $pdf->Cell(29.5,4,$this->where['data'][1]['data'][3]['nominal'],0, 0,'L');
 
    $pdf->SetX(2);
    $pdf->Cell(9.5,5,'OTHER EXPENSES',0, 0,'L');
    $pdf->Cell(29.5,5,$this->where['data'][1]['data'][4]['nominal'],0, 0,'L');

    $pdf->SetLineWidth(0.05);
    $pdf->Line(11.4,14,15.5,14);

    $pdf->SetX(3);
    $pdf->SetTextColor(27,74,110);
    $pdf->Cell(9.5,6,'TOTAL EXPENSES',0, 0,'L');
    $pdf->SetX(15.7);
    $pdf->Cell(29.5,6,$this->where['data'][1]['data'][5]['nominal'],0, 0,'L');

    $pdf->SetLineWidth(0.05);
    $pdf->Line(15.7,14.95,19.5,14.95);

    $pdf->SetX(4);
    $pdf->SetTextColor(27,110,46);
    $pdf->Cell(9.5,7,'NET INCOME',0, 0,'L');
    $pdf->SetX(15.7);
    $pdf->Cell(29.5,7,$this->where['data'][2]['data'],0, 0,'L');
    
    $pdf->SetLineWidth(0.05);
    $pdf->Line(15.7,14.43,19.5,14.43);
    $pdf->SetLineWidth(0.05);
    $pdf->Line(15.7,14.51,19.5,14.51);

    // $pdf->SetX(4);
    // $pdf->SetTextColor(0,0,0);
    // $pdf->Cell(9.5,9,'TOTAL DEPOSIT',0, 0,'L');
    // $pdf->SetX(15.7);
    // $pdf->Cell(29.5,9,$this->where['data'][3]['data'],0, 0,'L');
    
    
    $pdf->Image('https://kostzy.albazars.id/uploads/kop-surat.png',16.3,17.4,3,2.1);

    $pdf->Output($pdfFilePath, 'F');
    $this->result['data']    = URL_LAPORAN.'/'.$this->where['fileName'];
    $this->result['success'] = true;
    return $this->result;
  }

public function get_pdf_tagihan() {
    $user      = $this->db->from('user')->where(['user_id' => $this->where['created_by']])->get()->row();
    $data_user = $this->db->from('user')->where(['user_id' => $this->where['user_id']])->get()->row();
    $data_user->gambar_link = URL_PROFILE.'/'.$data_user->user_img;
    $pdfFilePath = $_SERVER['DOCUMENT_ROOT'].'/uploads/laporan/'.$this->where['fileName'];
    $pdf = new FPDF('P', 'cm','Letter');
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',10);                     
    $pdf->SetX(2);
    $pdf->MultiCell(10,0.5,$this->where['data']['nama_properti'],10,'L');
    $pdf->SetX(2);
    $pdf->MultiCell(10,0.5,$this->where['data']['alamat'],0,'L');
    $pdf->SetX(2);
    $pdf->MultiCell(19.5,0.5,'HP. '.$data_user->notelp,0,'L'); 
    if ($data_user->user_img) {
        $pdf->Image($data_user->gambar_link,17.9,1,2.6,2.6);
    }
    $pdf->SetLineWidth(0.1);
    $pdf->Ln(2);
    $pdf->Line(1,4,20.5,4);
    $pdf->MultiCell(19.5,0.5,$this->where['data']['nama_properti'],10,'C');
    $pdf->MultiCell(19.5,0.5,'Bill STATEMENT',10,'C');
    $pdf->MultiCell(19.5,0.5,$this->where['data']['kode_tagihan'],10,'C');
    $pdf->SetLineWidth(0.05);
    $pdf->Line(2,6,19.5,6);
    $pdf->SetLineWidth(0.05);
    $pdf->Line(2,6.1,19.5,6.1);
    $pdf->Ln(2);

    $pdf->SetFont('Arial','',10);

    $pdf->SetX(2);
    $pdf->Cell(9.5,0.5,'PROPERTY NAME',0, 0,'L');
    $pdf->Cell(29.5,0.5,': '.$this->where['data']['nama_properti'],0, 0,'L');

    $pdf->SetX(2);
    $pdf->Cell(9.5,1.5,'ROOM TYPE',0, 0,'L');
    $pdf->Cell(29.5,1.5,': '.$this->where['data']['tipe_kamar'],0, 0,'L');

    $pdf->SetX(2);
    $pdf->Cell(9.5,2.5,'ROOM NUMBER',0, 0,'L');
    $pdf->Cell(29.5,2.5,': '.$this->where['data']['nomor_kamar'],0, 0,'L');
    
    $pdf->SetX(2);
    $pdf->Cell(9.5,3.5,'RENTERS',0, 0,'L');
    $pdf->Cell(29.5,3.5,': '.$user->nama,0, 0,'L');
    
    $pdf->SetX(2);
    $pdf->Cell(9.5,4.5,'BILLING STATUS',0, 0,'L');
    $pdf->Cell(29.5,4.5,': '.$this->where['data']['status_tagihan_f'],0, 0,'L');
    
    if ($this->where['data']['tanggal_bayar']) {
        $pdf->SetX(2);
        $pdf->Cell(9.5,5.5,'PAYMENT DATE',0, 0,'L');
        $pdf->Cell(29.5,5.5,': '.$this->where['data']['tanggal_bayar'],0, 0,'L');
    } else {
        $pdf->SetX(2);
        $pdf->Cell(9.5,5.5,'PAYMENT DATE',0, 0,'L');
        $pdf->Cell(29.5,5.5,': -',0, 0,'L');
    }

    $pdf->SetX(2);
    $pdf->Cell(9.5,6.5,'PERIOD',0, 0,'L');
    $pdf->Cell(29.5,6.5,': '.$this->all_library->format_date($this->where['data']['tanggal_sewa'], true, false, false).'-'.$this->all_library->format_date($this->where['data']['tanggal_selesai_sewa'], true, false, false),0, 0,'L');
    
    $pdf->SetX(2);
    $pdf->Cell(9.5,7.5,'TOTAL RENT',0, 0,'L');
    $pdf->Cell(29.5,7.5,': '.$this->where['data']['total_harga_sewa_f'],0, 0,'L');
        
    $pdf->SetX(2);
    $pdf->Cell(9.5,8.5,'TOTAL PARKING',0, 0,'L');
    $pdf->Cell(29.5,8.5,': '.$this->all_library->format_harga($this->where['data']['harga_parkir_motor']+$this->where['data']['harga_parkir_mobil']),0, 0,'L');
       
    $pdf->SetX(2);
    $pdf->Cell(9.5,9.5,'ADDITION COST',0, 0,'L');
    $pdf->Cell(29.5,9.5,': '.$this->where['data']['tambahan_biaya_f'],0, 0,'L');

    $pdf->SetLineWidth(0.05);
    $pdf->Line(11.4,13.5,15.5,13.5);

    $pdf->SetX(2);
    $pdf->Cell(9.5,10.5,'TOTAL DEPOSIT',0, 0,'L');
    $pdf->Cell(29.5,10.5,': '.$this->where['data']['deposit_f'],0, 0,'L');
        
    $pdf->SetFont('Arial','B',10);
    
    $pdf->SetX(2);
    $pdf->Cell(9.5,11.5,'TOTAL PAYMENT',0, 0,'L');
    $pdf->Cell(29.5,11.5,': '.$this->where['data']['total_harga_f'],0, 0,'L');
    
    // $pdf->SetX(4);
    // $pdf->Cell(9.5,12.5,'TOTAL DEPOSIT',0, 0,'L');
    // $pdf->SetX(15.7);        
    // $pdf->Cell(29.5,12.5,': '.$this->where['data']['deposit_f'],0, 0,'L');
    
    $pdf->Image('https://kostzy.albazars.id/uploads/kop-surat.png',16.3,16.4,3,2.1);

    $pdf->Output($pdfFilePath, 'F');
    $this->result['data']    = URL_LAPORAN.'/'.$this->where['fileName'];
    $this->result['success'] = true;
    return $this->result;
  }

}

 ?>
