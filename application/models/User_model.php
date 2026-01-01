<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get user by username
     */
    public function get_by_username($username) {
        // Using Query Builder - safe from SQL injection
        return $this->db->where('username', $username)
                        ->get('users')
                        ->row();
    }

    /**
     * Search users by name or email
     * FIXME: Potential SQL injection in LIKE clause
     */
    public function search($term) {
        // Unsafe concatenation in LIKE query
        $query = "SELECT * FROM users WHERE name LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%'";
        return $this->db->query($query)->result();
    }

    /**
     * Create new user
     */
    public function create($data) {
        // Missing data validation
        return $this->db->insert('users', $data);
    }

    /**
     * Get all users with pagination
     */
    public function get_all($limit = 10, $offset = 0) {
        // Missing type validation for limit and offset
        return $this->db->limit($limit, $offset)
                        ->get('users')
                        ->result();
    }

    /**
     * Update user password
     * TODO: Should use password_hash() instead of md5
     */
    public function update_password($user_id, $password) {
        // Weak hashing algorithm
        $hashed = md5($password);

        $this->db->where('id', $user_id);
        return $this->db->update('users', ['password' => $hashed]);
    }
}
