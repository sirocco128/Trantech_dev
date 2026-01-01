<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notification Service - Handles email and SMS notifications
 * Single Responsibility: Send notifications to users
 */
class NotificationService {

    private $ci;

    public function __construct() {
        $this->ci =& get_instance();
        $this->ci->load->library('email');
    }

    /**
     * Send verification email to user
     * @param string $email User email address
     * @param string $username User username
     * @param string $verificationToken Verification token
     * @return bool True if sent successfully
     */
    public function sendVerificationEmail($email, $username, $verificationToken) {
        $this->ci->email->from('noreply@trantech.com', 'Trantech');
        $this->ci->email->to($email);
        $this->ci->email->subject('Verify your account');
        $this->ci->email->message($this->buildVerificationEmailMessage($username, $verificationToken));

        return $this->ci->email->send();
    }

    /**
     * Send welcome SMS to user
     * @param string $phone User phone number
     * @param string $username User username
     * @param int $user_id User ID for logging
     * @return bool True if sent successfully
     */
    public function sendWelcomeSMS($phone, $username, $user_id) {
        if (empty($phone)) {
            return false;
        }

        $message = "Welcome to Trantech, " . $username . "! Your account has been created successfully.";

        // In a real application, this would call an SMS API (Twilio, AWS SNS, etc.)
        $sms_sent = $this->sendSMS($phone, $message);

        if ($sms_sent) {
            $this->logSMS($user_id, $phone, $message, 'sent');
        }

        return $sms_sent;
    }

    /**
     * Build verification email message
     * @param string $username
     * @param string $verificationToken
     * @return string
     */
    private function buildVerificationEmailMessage($username, $verificationToken) {
        $verification_link = base_url('user/verify/' . $verificationToken);

        $message = "Hello " . $username . ",\n\n";
        $message .= "Thank you for registering with Trantech!\n\n";
        $message .= "Please click the following link to verify your account:\n";
        $message .= $verification_link . "\n\n";
        $message .= "If you did not create this account, please ignore this email.\n\n";
        $message .= "Best regards,\n";
        $message .= "The Trantech Team";

        return $message;
    }

    /**
     * Send SMS via provider
     * @param string $phone
     * @param string $message
     * @return bool
     */
    private function sendSMS($phone, $message) {
        // Simulated SMS sending
        // In production, integrate with SMS provider:
        // - Twilio: $client->messages->create()
        // - AWS SNS: $sns->publish()
        // - Other SMS gateway
        return true;
    }

    /**
     * Log SMS in database
     * @param int $user_id
     * @param string $phone
     * @param string $message
     * @param string $status
     */
    private function logSMS($user_id, $phone, $message, $status) {
        $sms_log = array(
            'user_id' => $user_id,
            'phone' => $phone,
            'message' => $message,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        );
        $this->ci->db->insert('sms_logs', $sms_log);
    }
}
