<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Activity Logger Service - Handles activity logging
 * Single Responsibility: Log user activities
 */
class ActivityLoggerService {

    private $ci;

    public function __construct() {
        $this->ci =& get_instance();
    }

    /**
     * Log user registration activity
     * @param int $user_id User ID
     */
    public function logRegistration($user_id) {
        $this->logActivity($user_id, 'user_registered');
    }

    /**
     * Log generic activity
     * @param int $user_id User ID
     * @param string $action Action performed
     * @param array $metadata Additional metadata (optional)
     */
    public function logActivity($user_id, $action, $metadata = array()) {
        $log_data = array(
            'user_id' => $user_id,
            'action' => $action,
            'ip_address' => $this->ci->input->ip_address(),
            'user_agent' => $this->ci->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        );

        if (!empty($metadata)) {
            $log_data['metadata'] = json_encode($metadata);
        }

        $this->ci->db->insert('activity_logs', $log_data);
    }
}
