<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Controller for admin pages.
 * - Redirects unauthenticated users to login
 */
class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Only protect non-login routes
        $current_class = $this->router->fetch_class();
        if ($current_class !== 'auth') {
            if (!$this->session->userdata('admin_logged_in')) {
                redirect('auth');
            }
        }
    }
}
