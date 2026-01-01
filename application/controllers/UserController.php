<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library('form_validation');
    }

    /**
     * Display user profile
     * Fixed: Using Query Builder to prevent SQL injection
     */
    public function profile($user_id) {
        // Validate that user_id is numeric
        if (!is_numeric($user_id)) {
            show_404();
            return;
        }

        // Use Query Builder for safe parameterized query
        $user = $this->db->where('id', (int)$user_id)
                         ->get('users')
                         ->row();

        if ($user) {
            $this->load->view('profile', ['user' => $user]);
        } else {
            show_404();
        }
    }

    /**
     * User login
     * TODO: Add rate limiting to prevent brute force attacks
     */
    public function login() {
        if ($this->input->method() === 'post') {
            $username = $this->input->post('username');
            $password = $this->input->post('password');

            // Weak password comparison - no hashing
            $user = $this->User_model->get_by_username($username);

            if ($user && $user->password == $password) {
                $this->session->set_userdata('user_id', $user->id);
                redirect('dashboard');
            } else {
                $this->session->set_flashdata('error', 'Invalid credentials');
                redirect('login');
            }
        }

        $this->load->view('login');
    }

    /**
     * Update user email
     */
    public function update_email() {
        $user_id = $this->session->userdata('user_id');
        $new_email = $this->input->post('email');

        // Missing email validation
        $this->db->where('id', $user_id);
        $this->db->update('users', ['email' => $new_email]);

        echo json_encode(['status' => 'success']);
    }

    /**
     * Delete user account
     */
    public function delete_account($user_id) {
        // Missing CSRF protection and authorization check
        $this->db->where('id', $user_id);
        $this->db->delete('users');

        redirect('home');
    }

    /**
     * Search users
     */
    public function search() {
        $search_term = $this->input->get('q');

        // XSS vulnerability - not escaping output
        $users = $this->User_model->search($search_term);

        $this->load->view('search_results', ['users' => $users, 'term' => $search_term]);
    }
}
