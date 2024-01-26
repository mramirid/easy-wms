<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller List Supplier
 */
class Suppliers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $is_login = $this->session->userdata('is_login');

        if (!$is_login) {
            $this->session->set_flashdata('warning', 'Anda belum login');
            redirect(base_url('login'));
            return;
        }
    }

    public function index($page = null)
    {
        $this->session->unset_userdata('keyword');

        $data['title']              = 'Easy WMS - List Supplier';
        $data['breadcrumb_title']   = 'List Supplier';
        $data['breadcrumb_path']    = 'Manajemen Supplier / List Supplier';
        $data['content']            = $this->suppliers->paginate($page)->get();
        $data['total_rows']         = $this->suppliers->count();
        $data['pagination']         = $this->suppliers->makePagination(base_url('suppliers'), 2, $data['total_rows']);
        $data['page']               = 'pages/suppliers/index';

        $this->view($data);
    }

    public function search($page = null)
    {
        if (isset($_POST['keyword'])) {
            $this->session->set_userdata('keyword', $this->input->post('keyword'));
        }

        $keyword = $this->session->userdata('keyword');

        if (empty($keyword)) {
            redirect(base_url('suppliers'));
        }

        $keyword = $this->session->userdata('keyword');

        $data['title']              = 'Easy WMS - Cari Supplier';
        $data['breadcrumb_title']   = "List Supplier";
        $data['breadcrumb_path']    = "List Supplier / Cari / $keyword";
        $data['content']            = $this->suppliers->paginate($page)
                                        ->like('nama', $keyword)
                                        ->orLike('email', $keyword)
                                        ->paginate($page)
                                        ->get();
        $data['total_rows']         = $this->suppliers->like('nama', $keyword)
                                        ->orLike('email', $keyword)
                                        ->count();
        $data['pagination']         = $this->suppliers->makePagination(base_url('suppliers/search'), 3, $data['total_rows']);
        $data['page']               = 'pages/suppliers/index';

        $this->view($data);
    }

    /**
     * Edit data supplier
     */
    public function edit($id)
    {
        if ($this->session->userdata('id_user') != 'id_user' && $this->session->userdata('role') != 'admin') {
            $this->session->set_flashdata('error', 'Akses edit ditolak!');
            redirect(base_url('home'));
        }

        $data['content'] = $this->suppliers->where('id', $id)->first();

        if (!$data['content']) {
            $this->session->set_flashdata('warning', 'Maaf data tidak ditemukan');
            redirect(base_url('suppliers'));
        }

        if (!$_POST) {
            $data['input'] = $data['content'];
        } else {
            $data['input'] = (object) $this->input->post(null, true);
        }

        if (!$this->suppliers->validate()) {
            $data['title']              = 'Easy WMS - Edit Supplier';
            $data['page']               = 'pages/suppliers/edit';
            $data['breadcrumb_title']   = 'Edit Data Supplier';
            $data['breadcrumb_path']    = "Manajemen Supplier / Edit Data Supplier / " . $data['input']->nama;

            return $this->view($data);
        }

        if ($this->suppliers->where('id', $id)->update($data['input'])) {   // Update data
            $this->session->set_flashdata('success', 'Data berhasil diubah');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi suatu kesalahan');
        }

        redirect(base_url('suppliers'));
    }

    public function delete(string $id) {
        $isDeleted = $this->suppliers->where('id', $id)->delete();
        if ($isDeleted) {
            $this->session->set_flashdata('success', 'Supplier berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Oops, terjadi suatu kesalahan');
        }

        redirect(base_url('suppliers'));
    }

    public function unique_email()
    {
        $email      = $this->input->post('email');
        $id         = $this->input->post('id');
        $supplier   = $this->suppliers->where('email', $email)->first(); // Akan terisi jika terdapat email yang sama

        if ($supplier) {
            if ($id == $supplier->id) {  // Keperluan edit tidak perlu ganti email, jadi tidak masalah
                return true;
            }

            // Jika terdapat suatu nilai pada $user, berikan pesan error pertanda email sudah ada di db
            $this->load->library('form_validation');
            $this->form_validation->set_message('unique_email', '%s sudah digunakan');
            return false;
        }

        return true;
    }
}
    
    /* End of file Supplier.php */
