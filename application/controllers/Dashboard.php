<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data['username'] = $this->session->userdata('admin_username');
        $data['content_view'] = 'dashboard/index_view';
        $data['content_data'] = $data;
        $data['page_title'] = 'Dashboard';
        $this->load->view('layouts/admin_layout', $data);
    }
}
