<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Media_model extends CI_Model {

    /**
     * Get all non-deleted media, ordered by created_at desc
     */
    public function get_all($limit = null, $offset = 0)
    {
        $this->db->where('is_deleted', 0);
        $this->db->order_by('created_at', 'DESC');
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get('media');
        return $query->result();
    }

    /**
     * Get total count of non-deleted media
     */
    public function get_total_count()
    {
        $this->db->where('is_deleted', 0);
        return $this->db->count_all_results('media');
    }

    /**
     * Get single media by ID
     */
    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        $this->db->where('is_deleted', 0);
        $query = $this->db->get('media');
        return $query->row();
    }

    /**
     * Insert media record
     * @return int|false Last insert ID or false on failure
     */
    public function insert($data)
    {
        $this->db->insert('media', $data);
        return $this->db->insert_id();
    }

    /**
     * Update media record
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('media', $data);
    }

    /**
     * Soft delete: mark as deleted
     */
    public function soft_delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->update('media', [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Permanently delete record (hard delete)
     */
    public function hard_delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('media');
    }

    /**
     * Get all media including soft-deleted (for admin restore/delete)
     */
    public function get_all_with_deleted($limit = null, $offset = 0)
    {
        $this->db->order_by('is_deleted', 'ASC')->order_by('created_at', 'DESC');
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get('media');
        return $query->result();
    }

    /**
     * Restore a soft-deleted media
     */
    public function restore($id)
    {
        $this->db->where('id', $id);
        return $this->db->update('media', [
            'is_deleted' => 0,
            'deleted_at' => null
        ]);
    }

    /**
     * Check if media file exists on disk
     */
    public function file_exists_on_disk($file_path)
    {
        $full_path = FCPATH . $file_path;
        return file_exists($full_path);
    }
}
