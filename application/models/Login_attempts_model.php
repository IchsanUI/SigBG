<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login_attempts_model extends CI_Model
{
    const MAX_ATTEMPTS = 5;        // before lockout
    const LOCKOUT_SECONDS = 900;   // 15 minutes

    public function __construct()
    {
        parent::__construct();
    }

    public function record_failed($ip)
    {
        $this->db->insert('login_attempts', ['ip_address' => $ip]);
    }

    public function count_recent_attempts($ip)
    {
        $since = date('Y-m-d H:i:s', time() - self::LOCKOUT_SECONDS);
        $this->db
            ->from('login_attempts')
            ->where('ip_address', $ip)
            ->where('attempted_at >=', $since);

        return $this->db->count_all_results();
    }

    public function is_locked($ip)
    {
        return $this->count_recent_attempts($ip) >= self::MAX_ATTEMPTS;
    }

    public function get_lockout_remaining($ip)
    {
        $this->db
            ->select('attempted_at')
            ->from('login_attempts')
            ->where('ip_address', $ip)
            ->order_by('attempted_at', 'DESC')
            ->limit(1);
        $row = $this->db->get()->row();
        if (!$row) return 0;

        $ts = strtotime($row->attempted_at);
        $unlock = $ts + self::LOCKOUT_SECONDS;
        return max(0, $unlock - time());
    }

    public function clear_attempts($ip)
    {
        $this->db->where('ip_address', $ip)->delete('login_attempts');
    }

    public function cleanup_old_attempts()
    {
        $cutoff = date('Y-m-d H:i:s', time() - self::LOCKOUT_SECONDS);
        $this->db->where('attempted_at <', $cutoff)->delete('login_attempts');
    }
}
