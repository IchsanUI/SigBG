<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admin_model');
        $this->load->model('Login_attempts_model');
        $this->load->library('form_validation');
        $this->load->helper(['form', 'url']);

        // Allow POST through CI3 CSRF check (it auto-validates)
        // form_validation->run() called only after csrf is verified
    }

    /**
     * Login page / handler
     */
    public function index()
    {
        // Already logged in -> dashboard
        if ($this->session->userdata('admin_logged_in')) {
            redirect('dashboard');
        }

        $ip = $this->input->ip_address();

        // Cleanup old attempt records opportunistically
        $this->login_attempts_model->cleanup_old_attempts();

        // Lockout check
        if ($this->login_attempts_model->is_locked($ip)) {
            $this->load->view('auth/login_view', [
                'locked'      => TRUE,
                'locked_secs' => $this->login_attempts_model->get_lockout_remaining($ip),
            ]);
            return;
        }

        if ($this->input->method() === 'post') {
            $this->handle_login();
            return;
        }

        $this->load->view('auth/login_view');
    }

    /**
     * Process login form (POST)
     */
    private function handle_login()
    {
        $this->form_validation->set_rules('username', 'Username', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/login_view');
            return;
        }

        $ip = $this->input->ip_address();

        // Re-check lockout (race condition safety)
        if ($this->login_attempts_model->is_locked($ip)) {
            $this->load->view('auth/login_view', [
                'error'       => 'Too many login attempts. Try again later.',
                'locked'      => TRUE,
                'locked_secs' => $this->login_attempts_model->get_lockout_remaining($ip),
            ]);
            return;
        }

        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password');

        $user = $this->admin_model->verify_password($username, $password);

        if (!$user) {
            $this->login_attempts_model->record_failed($ip);
            // Regenerate session to prevent fixation
            $this->session->sess_regenerate(FALSE);

            $this->load->view('auth/login_view', [
                'error' => 'Invalid username or password.',
            ]);
            return;
        }

        // Success: clear attempts, regenerate session id (full)
        $this->login_attempts_model->clear_attempts($ip);
        $this->session->sess_regenerate(TRUE);

        $this->session->set_userdata([
            'admin_id'        => $user->id,
            'admin_username'  => $user->username,
            'admin_logged_in' => TRUE,
        ]);

        $this->admin_model->update_last_login($user->id);

        redirect('dashboard');
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth');
    }
}
