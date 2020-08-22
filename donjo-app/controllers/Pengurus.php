<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pengurus extends Admin_Controller {

	private $_header;
	private $_set_page;
	private $_list_session;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['header_model', 'pamong_model', 'penduduk_model', 'config_model', 'referensi_model', 'wilayah_model']);
		$this->modul_ini = 200;
		$this->sub_modul_ini = 18;
		$this->_set_page = ['20', '50', '100'];
		$this->_list_session = ['status', 'cari'];
		// TODO: Hapus header_model jika sudah dibuatkan librari tempalte admin
		$this->_header = $this->header_model->get_data();
	}

	public function clear()
	{
		$this->session->unset_userdata($this->_list_session);
		$this->session->per_page = $this->_set_page[0];
		redirect('pengurus');
	}

	public function index($p = 1)
	{
		foreach ($this->_list_session as $list)
		{
				$data[$list] = $this->session->$list ?: '';
		}

		$per_page = $this->input->post('per_page');
		if (isset($per_page))
			$this->session->per_page = $per_page;

		$data['func'] = 'index';
		$data['set_page'] = $this->_set_page;
		$data['per_page'] = $this->session->per_page;
		$data['paging'] = $this->pamong_model->paging($p);
		$data['main'] = $this->pamong_model->list_data($data['paging']->offset, $data['paging']->per_page);
		$data['keyword'] = $this->pamong_model->autocomplete();
		$this->_header['minsidebar'] = 1;

		$this->load->view('header', $this->_header);
		$this->load->view('nav');
		$this->load->view('home/pengurus', $data);
		$this->load->view('footer');
	}

	public function form($id = 0)
	{
		$id_pend = $this->input->post('id_pend');

		if ($id)
		{
			$data['pamong'] = $this->pamong_model->get_data($id);
			if (!isset($id_pend)) $id_pend = $data['pamong']['id_pend'];
			$data['form_action'] = site_url("pengurus/update/$id");
		}
		else
		{
			$data['pamong'] = NULL;
			$data['form_action'] = site_url("pengurus/insert");
		}

		$data['penduduk'] = $this->pamong_model->list_penduduk();
		$data['pendidikan_kk'] = $this->referensi_model->list_data('tweb_penduduk_pendidikan_kk');
		$data['agama'] = $this->referensi_model->list_data('tweb_penduduk_agama');

		if (!empty($id_pend))
			$data['individu'] = $this->penduduk_model->get_penduduk($id_pend);
		else
			$data['individu'] = NULL;

		$this->load->view('header', $this->_header);
		$this->load->view('nav');
		$this->load->view('home/pengurus_form', $data);
		$this->load->view('footer');
	}

	public function filter($filter)
	{
		$value = $this->input->post($filter);
		if ($value != '')
			$this->session->$filter = $value;
		else $this->session->unset_userdata($filter);
		redirect('pengurus');
	}

	public function insert()
	{
		$this->pamong_model->insert();
		redirect('pengurus');
	}

	public function update($id = 0)
	{
		$this->pamong_model->update($id);
		redirect('pengurus');
	}

	public function delete($id = 0)
	{
		$this->redirect_hak_akses('h', 'pengurus');
		$outp = $this->pamong_model->delete($id);
		redirect('pengurus');
	}

	public function delete_all()
	{
		$this->redirect_hak_akses('h', 'pengurus');
		$this->pamong_model->delete_all();
		redirect('pengurus');
	}

	public function ttd($id = 0, $val = 0)
	{
		$this->pamong_model->ttd('pamong_ttd', $id, $val);
		redirect('pengurus');
	}

	public function ub($id = 0, $val = 0)
	{
		$this->pamong_model->ttd('pamong_ub', $id, $val);
		redirect('pengurus');
	}

	public function urut($p = 1, $id = 0, $arah = 0)
	{
		$this->pamong_model->urut($id, $arah);
		redirect("pengurus/index/$p");
	}

	public function lock($id = 0, $val = 1)
	{
		$this->pamong_model->lock($id, $val);
		redirect("pengurus");
	}

	/*
	 * $aksi = cetak/unduh
	 */
	public function dialog($aksi = 'cetak')
	{
		$data['aksi'] = $aksi;
		$data['pamong'] = $this->pamong_model->list_data();
		$data['form_action'] = site_url("pengurus/daftar/$aksi");
		$this->load->view('global/ttd_pamong', $data);
	}

	/*
	 * $aksi = cetak/unduh
	 */
	public function daftar($aksi = 'cetak')
	{
		$data['pamong_ttd'] = $this->pamong_model->get_data($this->input->post('pamong_ttd'));
		$data['pamong_ketahui'] = $this->pamong_model->get_data($this->input->post('pamong_ketahui'));
		$data['desa'] = $this->config_model->get_data();
		$data['main'] = $this->pamong_model->list_data();

		$this->load->view('home/'.$aksi, $data);
	}

	public function bagan()
	{
		$data['desa'] = $this->config_model->get_data();
		$data['bagan'] = $this->pamong_model->list_bagan();
		$this->load->view('header', $this->_header);
		$this->load->view('nav');
		$this->load->view('home/bagan', $data);
		$this->load->view('footer');
	}
}
