<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function find_by_username($username)
    {
        return $this->db
            ->get_where('admin_users', ['username' => $username], 1)
            ->row();
    }

    public function verify_password($username, $password)
    {
        $user = $this->find_by_username($username);
        if (!$user) {
            return FALSE;
        }
        if (password_verify($password, $user->password_hash)) {
            return $user;
        }
        return FALSE;
    }

    public function update_last_login($id)
    {
        $this->db
            ->where('id', $id)
            ->update('admin_users', ['updated_at' => date('Y-m-d H:i:s')]);
    }
}
