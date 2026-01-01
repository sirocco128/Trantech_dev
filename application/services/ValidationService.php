<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Validation Service - Handles all validation logic
 * Single Responsibility: Validate user input
 */
class ValidationService {

    private $ci;
    private $errors = array();

    public function __construct() {
        $this->ci =& get_instance();
    }

    /**
     * Validate registration data
     * @param array $data User registration data
     * @return bool True if valid, false otherwise
     */
    public function validateRegistration($data) {
        $this->errors = array();

        $this->validateUsername($data['username']);
        $this->validateEmail($data['email']);
        $this->validatePassword($data['password'], $data['confirm_password']);
        $this->validatePhone($data['phone']);
        $this->validateCountry($data['country']);
        $this->validateTerms($data['terms']);

        return empty($this->errors);
    }

    /**
     * Get validation errors
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Validate username
     */
    private function validateUsername($username) {
        if (empty($username)) {
            $this->errors[] = 'Username is required';
            return;
        }

        if (strlen($username) < 3) {
            $this->errors[] = 'Username must be at least 3 characters';
        }

        if (strlen($username) > 50) {
            $this->errors[] = 'Username must be less than 50 characters';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $this->errors[] = 'Username can only contain letters, numbers and underscores';
        }

        if ($this->usernameExists($username)) {
            $this->errors[] = 'Username already exists';
        }
    }

    /**
     * Validate email address
     */
    private function validateEmail($email) {
        if (empty($email)) {
            $this->errors[] = 'Email is required';
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Invalid email format';
            return;
        }

        if ($this->emailExists($email)) {
            $this->errors[] = 'Email already exists';
        }

        if ($this->isDisposableEmail($email)) {
            $this->errors[] = 'Disposable email addresses are not allowed';
        }
    }

    /**
     * Validate password
     */
    private function validatePassword($password, $confirm_password) {
        if (empty($password)) {
            $this->errors[] = 'Password is required';
            return;
        }

        if (strlen($password) < 8) {
            $this->errors[] = 'Password must be at least 8 characters';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $this->errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $this->errors[] = 'Password must contain at least one lowercase letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $this->errors[] = 'Password must contain at least one number';
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $this->errors[] = 'Password must contain at least one special character';
        }

        if ($password !== $confirm_password) {
            $this->errors[] = 'Passwords do not match';
        }
    }

    /**
     * Validate phone number (optional field)
     */
    private function validatePhone($phone) {
        if (!empty($phone) && !preg_match('/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/', $phone)) {
            $this->errors[] = 'Invalid phone number format';
        }
    }

    /**
     * Validate country
     */
    private function validateCountry($country) {
        $valid_countries = array('USA', 'Canada', 'UK', 'Australia', 'Germany', 'France', 'Japan', 'China', 'India');
        if (!empty($country) && !in_array($country, $valid_countries)) {
            $this->errors[] = 'Invalid country selected';
        }
    }

    /**
     * Validate terms acceptance
     */
    private function validateTerms($terms) {
        if ($terms !== '1') {
            $this->errors[] = 'You must accept the terms and conditions';
        }
    }

    /**
     * Check if username exists in database
     */
    private function usernameExists($username) {
        $this->ci->db->where('username', $username);
        $query = $this->ci->db->get('users');
        return $query->num_rows() > 0;
    }

    /**
     * Check if email exists in database
     */
    private function emailExists($email) {
        $this->ci->db->where('email', $email);
        $query = $this->ci->db->get('users');
        return $query->num_rows() > 0;
    }

    /**
     * Check if email is from a disposable domain
     */
    private function isDisposableEmail($email) {
        $disposable_domains = array('tempmail.com', 'guerrillamail.com', '10minutemail.com');
        $email_domain = substr(strrchr($email, "@"), 1);
        return in_array($email_domain, $disposable_domains);
    }
}
