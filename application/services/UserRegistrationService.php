<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Registration Service - Handles user registration business logic
 * Single Responsibility: Manage user registration process
 */
class UserRegistrationService {

    private $ci;

    public function __construct() {
        $this->ci =& get_instance();
    }

    /**
     * Register a new user
     * @param array $userData User registration data
     * @return array Result with user_id or error message
     */
    public function registerUser($userData) {
        $user_id = $this->createUserAccount($userData);

        if (!$user_id) {
            return array('success' => false, 'message' => 'Registration failed. Please try again.');
        }

        return array('success' => true, 'user_id' => $user_id);
    }

    /**
     * Create user account with all related records
     * @param array $userData
     * @return int|false User ID or false on failure
     */
    private function createUserAccount($userData) {
        $this->ci->db->trans_start();

        $user_id = $this->insertUser($userData);

        if ($user_id) {
            $this->createUserProfile($user_id);
            $this->createUserSettings($user_id);
        }

        $this->ci->db->trans_complete();

        return $this->ci->db->trans_status() ? $user_id : false;
    }

    /**
     * Insert user record
     * @param array $userData
     * @return int User ID
     */
    private function insertUser($userData) {
        $user_data = array(
            'username' => $userData['username'],
            'email' => $userData['email'],
            'password' => $this->hashPassword($userData['password']),
            'phone' => $userData['phone'],
            'country' => $userData['country'],
            'verification_token' => $this->generateVerificationToken(),
            'is_verified' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $this->ci->db->insert('users', $user_data);
        return $this->ci->db->insert_id();
    }

    /**
     * Create default user profile
     * @param int $user_id
     */
    private function createUserProfile($user_id) {
        $profile_data = array(
            'user_id' => $user_id,
            'bio' => '',
            'avatar' => 'default.png',
            'created_at' => date('Y-m-d H:i:s')
        );
        $this->ci->db->insert('user_profiles', $profile_data);
    }

    /**
     * Create default user settings
     * @param int $user_id
     */
    private function createUserSettings($user_id) {
        $settings_data = array(
            'user_id' => $user_id,
            'email_notifications' => 1,
            'sms_notifications' => 0,
            'theme' => 'light',
            'language' => 'en'
        );
        $this->ci->db->insert('user_settings', $settings_data);
    }

    /**
     * Hash password securely
     * @param string $password
     * @return string
     */
    private function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Generate verification token
     * @return string
     */
    private function generateVerificationToken() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Get user verification token
     * @param int $user_id
     * @return string|null
     */
    public function getVerificationToken($user_id) {
        $this->ci->db->select('verification_token');
        $this->ci->db->where('id', $user_id);
        $query = $this->ci->db->get('users');

        if ($query->num_rows() > 0) {
            return $query->row()->verification_token;
        }

        return null;
    }
}
