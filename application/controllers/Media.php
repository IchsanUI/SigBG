<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Media extends MY_Controller {

    public $upload_config = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Media_model', 'media_model');

        $this->upload_config = [
            'upload_path'   => './assets/uploads/media/',
            'allowed_types' => 'jpg|jpeg|png|gif|webp|mp4|avi|mov|mkv|webm',
            'max_size'      => 100, // MB
            'encrypt_name'  => TRUE,
            'remove_spaces' => TRUE,
            'overwrite'     => FALSE
        ];
    }

    /**
     * List all media files
     */
    public function index()
    {
        $media = $this->media_model->get_all();
        $data = [
            'page_title'  => 'Manajemen Media',
            'username'    => $this->session->userdata('admin_username'),
            'media_list'  => $media,
            'content_view'=> 'media/list_view',
        ];

        $view_data = ['content_view' => 'media/list_view'];
        foreach ($data as $k => $v) {
            $view_data[$k] = $v;
        }
        $this->load->view('layouts/admin_layout', $view_data);
    }

    /**
     * Handle media upload via AJAX
     */
    public function upload()
    {
        if ($this->request_method() !== 'POST') {
            show_error('Invalid request method.', 405);
        }

        // Validate title
        $title = trim($this->input->post('title'));
        if (empty($title)) {
            $title = pathinfo($_FILES['userfile']['name'], PATHINFO_FILENAME);
        }

        $this->load->library('upload');

        // Configure upload
        $this->upload->initialize($this->upload_config);

        if ( ! $this->upload->do_upload('userfile')) {
            $error = $this->upload->display_errors('', '');
            // If it's an allowed type error, suggest proper types
            if (strpos($error, 'allowed types') !== FALSE) {
                $allowed = 'Images: jpg, jpeg, png, gif, webp<br>
                           Videos: mp4, avi, mov, mkv, webm';
                $this->output
                     ->set_content_type('application/json')
                     ->set_status_header(422)
                     ->set_output(json_encode(['error' => $allowed]));
                return;
            }
            $this->output
                 ->set_content_type('application/json')
                 ->set_status_header(400)
                 ->set_output(json_encode(['error' => $error]));
            return;
        }

        $file_data = $this->upload->data();
        $file_type = $this->detect_file_type($file_data['file_ext']);
        $ext = strtolower(pathinfo($file_data['orig_name'], PATHINFO_EXTENSION));
        $relative_path = 'assets/uploads/media/' . $file_data['file_name'];
        $real_path = FCPATH . $relative_path;

        $duration = null;
        if ($file_type === 'image') {
            // Validate min duration 3 seconds
            $min_dur = intval($this->input->post('min_duration', TRUE));
            $duration = ($min_dur >= 3) ? $min_dur : 5;
        }
        // duration is NULL for videos (auto-detected by player)

        // Detect video duration using ffprobe if available
        if ($file_type === 'video' && function_exists('shell_exec')) {
            $detected = $this->detect_video_duration($real_path);
            if ($detected) {
                $duration = $detected;
            }
        }

        $insert_data = [
            'type'      => $file_type,
            'file_path' => $relative_path,
            'title'     => $title,
            'duration'  => $duration,
            'file_size' => $file_data['file_size'],
            'mime_type' => $file_data['file_type'], // CI stores MIME in file_type
        ];

        $media_id = $this->media_model->insert($insert_data);
        if ( ! $media_id) {
            unlink($real_path);
            $this->output
                 ->set_content_type('application/json')
                 ->set_status_header(500)
                 ->set_output(json_encode(['error' => 'Gagal menyimpan data media ke database.']));
            return;
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode([
                 'success'    => true,
                 'id'         => $media_id,
                 'file_name'  => $file_data['file_name'],
                 'type'       => $file_type,
                 'title'      => $title,
                 'duration'   => $duration,
                 'size'       => $file_data['file_size'],
                 'path'       => base_url($relative_path),
                 'csrf_token' => $this->security->get_csrf_hash(),
             ]));
    }

    /**
     * Edit media metadata (title, duration)
     */
    public function edit($id)
    {
        if ($this->request_method() !== 'POST') {
            show_error('Invalid request method.', 405);
        }

        $media = $this->media_model->get_by_id($id);
        if ( ! $media) {
            $this->output
                 ->set_content_type('application/json')
                 ->set_status_header(404)
                 ->set_output(json_encode(['error' => 'Media tidak ditemukan.']));
            return;
        }

        $title   = trim($this->input->post('title'));
        $duration = $this->input->post('duration');

        $update = [];
        if ($title !== '') $update['title'] = $title;

        if ($media->type === 'image' && $duration !== '') {
            $dur = intval($duration);
            if ($dur >= 3) {
                $update['duration'] = $dur;
            } else {
                $this->output
                     ->set_content_type('application/json')
                     ->set_status_header(422)
                     ->set_output(json_encode(['error' => 'Durasi gambar minimal 3 detik.']));
                return;
            }
        }

        if ($update) {
            $this->media_model->update($id, $update);
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(['success' => true, 'csrf_token' => $this->security->get_csrf_hash()]));
    }

    /**
     * Delete media (soft delete + optionally remove file)
     */
    public function delete($id)
    {
        if ($this->request_method() !== 'POST') {
            show_error('Invalid request method.', 405);
        }

        $media = $this->media_model->get_by_id($id);
        if ( ! $media) {
            $this->output
                 ->set_content_type('application/json')
                 ->set_status_header(404)
                 ->set_output(json_encode(['error' => 'Media tidak ditemukan.']));
            return;
        }

        // Soft delete
        $this->media_model->soft_delete($id);

        // Optionally remove physical file if asked
        $remove_file = $this->input->post('remove_file', TRUE);
        if ($remove_file) {
            $filepath = FCPATH . $media->file_path;
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(['success' => true, 'csrf_token' => $this->security->get_csrf_hash()]));
    }

    /**
     * Serve media files through controller (since uploads dir is deny from all)
     * Usage: media/serve/{id}
     */
    public function serve($id)
    {
        $media = $this->media_model->get_by_id($id);
        if ( ! $media) {
            show_404();
        }

        $filepath = FCPATH . $media->file_path;
        if ( ! file_exists($filepath)) {
            show_404();
        }

        $this->output
             ->set_content_type($media->mime_type)
             ->set_header('Content-Length: ' . filesize($filepath))
             ->set_header('Cache-Control: public, max-age=86400')
             ->set_output(file_get_contents($filepath));
    }

    /**
     * Serve media by filename (for direct URL access)
     */
    public function serve_file($filename)
    {
        $this->db->where('is_deleted', 0);
        $query = $this->db->get_where('media', ['file_path LIKE' => '%/' . $filename]);
        $media = $query->row();

        if ( ! $media) {
            show_404();
        }

        $filepath = FCPATH . $media->file_path;
        if ( ! file_exists($filepath)) {
            show_404();
        }

        $this->output
             ->set_content_type($media->mime_type)
             ->set_header('Content-Length: ' . filesize($filepath))
             ->set_header('Cache-Control: public, max-age=86400')
             ->set_output(file_get_contents($filepath));
    }

    // --------------------------------------------------------------------

    /**
     * Detect if file is image or video from extension
     */
    private function detect_file_type($extension)
    {
        $img_exts = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
        $extension = strtolower($extension);
        return in_array($extension, $img_exts) ? 'image' : 'video';
    }

    /**
     * Detect video duration using ffprobe/ffmpeg
     */
    private function detect_video_duration($filepath)
    {
        // Try ffprobe first
        $cmd = escapeshellarg(APPPATH . '../vendor/bin/ffprobe') . ' -v quiet -print_format json -show_format "' . $filepath . '"';
        $output = shell_exec($cmd);
        if ($output) {
            $data = json_decode($output, TRUE);
            if (isset($data['format']['duration'])) {
                return (int) round(floatval($data['format']['duration']));
            }
        }

        // Fallback: try ffmpeg
        $cmd = 'ffprobe -v quiet -print_format json -show_format "' . $filepath . '"';
        $output = shell_exec($cmd);
        if ($output) {
            $data = json_decode($output, TRUE);
            if (isset($data['format']['duration'])) {
                return (int) round(floatval($data['format']['duration']));
            }
        }

        return null;
    }

    /**
     * Get HTTP method (supports X-HTTP-Method-Override for AJAX)
     */
    private function request_method()
    {
        return strtoupper($this->input->server('REQUEST_METHOD', TRUE));
    }
}
