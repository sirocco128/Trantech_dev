<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Controller - BEFORE REFACTORING
 * This controller demonstrates an overly complex registration method
 */
class UserController extends CI_Controller {

    /**
     * COMPLEX FUNCTION - User Registration
     *
     * This function is overly complex and violates several principles:
     * - Too many responsibilities (validation, business logic, email, database)
     * - Hard to test due to tight coupling
     * - Difficult to read and understand
     * - Mixes different levels of abstraction
     * - Contains duplicate code and nested conditionals
     */
    public function register() {
        // Get POST data
        $username = $this->input->post('username');
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $confirm_password = $this->input->post('confirm_password');
        $phone = $this->input->post('phone');
        $country = $this->input->post('country');
        $terms = $this->input->post('terms');

        // Initialize error array
        $errors = array();

        // Validate username
        if (empty($username)) {
            $errors[] = 'Username is required';
        } else {
            if (strlen($username) < 3) {
                $errors[] = 'Username must be at least 3 characters';
            }
            if (strlen($username) > 50) {
                $errors[] = 'Username must be less than 50 characters';
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $errors[] = 'Username can only contain letters, numbers and underscores';
            }
            // Check if username already exists
            $this->db->where('username', $username);
            $query = $this->db->get('users');
            if ($query->num_rows() > 0) {
                $errors[] = 'Username already exists';
            }
        }

        // Validate email
        if (empty($email)) {
            $errors[] = 'Email is required';
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            } else {
                // Check if email already exists
                $this->db->where('email', $email);
                $query = $this->db->get('users');
                if ($query->num_rows() > 0) {
                    $errors[] = 'Email already exists';
                }
                // Check if email is from a disposable domain
                $disposable_domains = array('tempmail.com', 'guerrillamail.com', '10minutemail.com');
                $email_domain = substr(strrchr($email, "@"), 1);
                if (in_array($email_domain, $disposable_domains)) {
                    $errors[] = 'Disposable email addresses are not allowed';
                }
            }
        }

        // Validate password
        if (empty($password)) {
            $errors[] = 'Password is required';
        } else {
            if (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters';
            }
            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = 'Password must contain at least one uppercase letter';
            }
            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = 'Password must contain at least one lowercase letter';
            }
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = 'Password must contain at least one number';
            }
            if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
                $errors[] = 'Password must contain at least one special character';
            }
        }

        // Validate password confirmation
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }

        // Validate phone
        if (!empty($phone)) {
            if (!preg_match('/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/', $phone)) {
                $errors[] = 'Invalid phone number format';
            }
        }

        // Validate country
        $valid_countries = array('USA', 'Canada', 'UK', 'Australia', 'Germany', 'France', 'Japan', 'China', 'India');
        if (!empty($country) && !in_array($country, $valid_countries)) {
            $errors[] = 'Invalid country selected';
        }

        // Validate terms acceptance
        if ($terms !== '1') {
            $errors[] = 'You must accept the terms and conditions';
        }

        // If there are errors, return them
        if (!empty($errors)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'errors' => $errors)));
            return;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));

        // Prepare user data
        $user_data = array(
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password,
            'phone' => $phone,
            'country' => $country,
            'verification_token' => $verification_token,
            'is_verified' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        // Begin transaction
        $this->db->trans_start();

        // Insert user
        $this->db->insert('users', $user_data);
        $user_id = $this->db->insert_id();

        // Create default user profile
        $profile_data = array(
            'user_id' => $user_id,
            'bio' => '',
            'avatar' => 'default.png',
            'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('user_profiles', $profile_data);

        // Create default user settings
        $settings_data = array(
            'user_id' => $user_id,
            'email_notifications' => 1,
            'sms_notifications' => 0,
            'theme' => 'light',
            'language' => 'en'
        );
        $this->db->insert('user_settings', $settings_data);

        // Complete transaction
        $this->db->trans_complete();

        // Check if transaction was successful
        if ($this->db->trans_status() === FALSE) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Registration failed. Please try again.')));
            return;
        }

        // Send verification email
        $this->load->library('email');
        $this->email->from('noreply@trantech.com', 'Trantech');
        $this->email->to($email);
        $this->email->subject('Verify your account');

        $verification_link = base_url('user/verify/' . $verification_token);
        $message = "Hello " . $username . ",\n\n";
        $message .= "Thank you for registering with Trantech!\n\n";
        $message .= "Please click the following link to verify your account:\n";
        $message .= $verification_link . "\n\n";
        $message .= "If you did not create this account, please ignore this email.\n\n";
        $message .= "Best regards,\n";
        $message .= "The Trantech Team";

        $this->email->message($message);

        $email_sent = $this->email->send();

        // Log registration
        $log_data = array(
            'user_id' => $user_id,
            'action' => 'user_registered',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('activity_logs', $log_data);

        // Send welcome SMS if phone is provided
        if (!empty($phone)) {
            // This is a simplified SMS sending - in real app would use a service
            $sms_message = "Welcome to Trantech, " . $username . "! Your account has been created successfully.";
            // Simulated SMS sending
            $sms_sent = true; // In reality, call SMS API here

            if ($sms_sent) {
                $sms_log = array(
                    'user_id' => $user_id,
                    'phone' => $phone,
                    'message' => $sms_message,
                    'status' => 'sent',
                    'created_at' => date('Y-m-d H:i:s')
                );
                $this->db->insert('sms_logs', $sms_log);
            }
        }

        // Return success response
        $response = array(
            'success' => true,
            'message' => 'Registration successful! Please check your email to verify your account.',
            'user_id' => $user_id,
            'email_sent' => $email_sent
        );

        $this->output
            ->set_status_header(201)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
