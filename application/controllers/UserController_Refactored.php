<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Controller - AFTER REFACTORING
 *
 * This controller demonstrates clean, maintainable code following SOLID principles:
 * - Single Responsibility: Each class has one clear purpose
 * - Dependency Injection: Services are injected for better testability
 * - Separation of Concerns: Validation, business logic, and notifications are separated
 * - Easier to read, test, and maintain
 */
class UserController_Refactored extends CI_Controller {

    private $validationService;
    private $registrationService;
    private $notificationService;
    private $activityLogger;

    /**
     * Constructor - Initialize services
     */
    public function __construct() {
        parent::__construct();

        // Load service classes
        require_once APPPATH . 'services/ValidationService.php';
        require_once APPPATH . 'services/UserRegistrationService.php';
        require_once APPPATH . 'services/NotificationService.php';
        require_once APPPATH . 'services/ActivityLoggerService.php';

        // Initialize services (dependency injection)
        $this->validationService = new ValidationService();
        $this->registrationService = new UserRegistrationService();
        $this->notificationService = new NotificationService();
        $this->activityLogger = new ActivityLoggerService();
    }

    /**
     * REFACTORED FUNCTION - User Registration
     *
     * This method is now clean and focused:
     * - Clear, single responsibility
     * - Easy to understand and test
     * - Delegates specific tasks to services
     * - Much shorter and more maintainable
     */
    public function register() {
        $registrationData = $this->getRegistrationData();

        // Validate input
        if (!$this->validationService->validateRegistration($registrationData)) {
            return $this->sendValidationError($this->validationService->getErrors());
        }

        // Register user
        $result = $this->registrationService->registerUser($registrationData);

        if (!$result['success']) {
            return $this->sendError($result['message'], 500);
        }

        $user_id = $result['user_id'];

        // Send notifications
        $this->sendUserNotifications($user_id, $registrationData);

        // Log activity
        $this->activityLogger->logRegistration($user_id);

        // Return success response
        $this->sendSuccess($user_id);
    }

    /**
     * Get registration data from POST request
     * @return array
     */
    private function getRegistrationData() {
        return array(
            'username' => $this->input->post('username'),
            'email' => $this->input->post('email'),
            'password' => $this->input->post('password'),
            'confirm_password' => $this->input->post('confirm_password'),
            'phone' => $this->input->post('phone'),
            'country' => $this->input->post('country'),
            'terms' => $this->input->post('terms')
        );
    }

    /**
     * Send notifications to user
     * @param int $user_id
     * @param array $userData
     */
    private function sendUserNotifications($user_id, $userData) {
        $verificationToken = $this->registrationService->getVerificationToken($user_id);

        // Send verification email
        $this->notificationService->sendVerificationEmail(
            $userData['email'],
            $userData['username'],
            $verificationToken
        );

        // Send welcome SMS if phone provided
        $this->notificationService->sendWelcomeSMS(
            $userData['phone'],
            $userData['username'],
            $user_id
        );
    }

    /**
     * Send validation error response
     * @param array $errors
     */
    private function sendValidationError($errors) {
        $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success' => false,
                'errors' => $errors
            )));
    }

    /**
     * Send error response
     * @param string $message
     * @param int $statusCode
     */
    private function sendError($message, $statusCode = 500) {
        $this->output
            ->set_status_header($statusCode)
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success' => false,
                'message' => $message
            )));
    }

    /**
     * Send success response
     * @param int $user_id
     */
    private function sendSuccess($user_id) {
        $this->output
            ->set_status_header(201)
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success' => true,
                'message' => 'Registration successful! Please check your email to verify your account.',
                'user_id' => $user_id
            )));
    }
}
