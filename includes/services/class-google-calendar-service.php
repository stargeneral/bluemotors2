<?php
/**
 * Google Calendar Integration Service
 * 
 * Integrates WordPress booking system with Google Calendar
 * 
 * @package BlueMotosSouthampton
 * @since 1.3.0
 */

namespace BlueMotosSouthampton\Services;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__DIR__) . '../vendor/autoload.php';

class GoogleCalendarService {
    
    private $client;
    private $calendar_service;
    private $calendar_id;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_google_client();
    }
    
    /**
     * Initialize Google Client
     */
    private function init_google_client() {
        try {
            // Load service account credentials from JSON file
            $credentials_file = plugin_dir_path(__DIR__) . '../vendor/service-account-credentials.json';
            
            if (!file_exists($credentials_file)) {
                throw new \Exception('Service account credentials file not found at: ' . $credentials_file);
            }
            
            // Read and decode JSON credentials
            $credentials_json = file_get_contents($credentials_file);
            $credentials = json_decode($credentials_json, true);
            
            if (!$credentials || !isset($credentials['client_email']) || !isset($credentials['private_key'])) {
                throw new \Exception('Invalid service account credentials format');
            }
            
            // Read calendar information for calendar ID
            $calendar_info_file = plugin_dir_path(__DIR__) . '../vendor/google-calendar-infor.txt';
            
            if (!file_exists($calendar_info_file)) {
                throw new \Exception('Google Calendar information file not found');
            }
            
            $calendar_info = file_get_contents($calendar_info_file);
            preg_match('/calendar ID: (.+)/', $calendar_info, $calendar_matches);
            
            if (!$calendar_matches) {
                throw new \Exception('Invalid calendar information format');
            }
            
            $this->calendar_id = trim($calendar_matches[1]);
            
            // Create client
            $this->client = new \Google\Client();
            $this->client->setApplicationName('Blue Motors Southampton Booking');
            $this->client->setScopes([\Google\Service\Calendar::CALENDAR]);
            
            // Use the JSON credentials file directly
            $this->client->setAuthConfig($credentials_file);
            
            $this->calendar_service = new \Google\Service\Calendar($this->client);
            
        } catch (\Exception $e) {
            error_log('Google Calendar Service initialization error: ' . $e->getMessage());
            $this->client = null;
            $this->calendar_service = null;
        }
    }
    
    /**
     * Check if Google Calendar is available
     */
    public function is_available() {
        return $this->client !== null && $this->calendar_service !== null;
    }
    
    /**
     * Create event in Google Calendar
     */
    public function create_event($booking_data) {
        if (!$this->is_available()) {
            return new \WP_Error('calendar_unavailable', 'Google Calendar service is not available');
        }
        
        try {
            // Create event object
            $event = new \Google\Service\Calendar\Event([
                'summary' => $this->generate_event_title($booking_data),
                'description' => $this->generate_event_description($booking_data),
                'start' => [
                    'dateTime' => $this->format_datetime($booking_data['booking_date'], $booking_data['booking_time']),
                    'timeZone' => 'Europe/London',
                ],
                'end' => [
                    'dateTime' => $this->calculate_end_time($booking_data['booking_date'], $booking_data['booking_time'], $booking_data['duration'] ?? 60),
                    'timeZone' => 'Europe/London',
                ],
                'attendees' => [
                    ['email' => $booking_data['customer_email'], 'displayName' => $booking_data['customer_name']]
                ],
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'email', 'minutes' => 24 * 60], // 24 hours before
                        ['method' => 'popup', 'minutes' => 60], // 1 hour before
                    ],
                ],
            ]);
            
            // Insert event
            $created_event = $this->calendar_service->events->insert($this->calendar_id, $event);
            
            return [
                'success' => true,
                'event_id' => $created_event->getId(),
                'event_link' => $created_event->getHtmlLink(),
                'event_data' => $created_event
            ];
            
        } catch (\Exception $e) {
            error_log('Google Calendar create event error: ' . $e->getMessage());
            return new \WP_Error('event_creation_failed', 'Failed to create calendar event: ' . $e->getMessage());
        }
    }
    
    /**
     * Update existing event
     */
    public function update_event($event_id, $booking_data) {
        if (!$this->is_available()) {
            return new \WP_Error('calendar_unavailable', 'Google Calendar service is not available');
        }
        
        try {
            // Get existing event
            $event = $this->calendar_service->events->get($this->calendar_id, $event_id);
            
            // Update event details
            $event->setSummary($this->generate_event_title($booking_data));
            $event->setDescription($this->generate_event_description($booking_data));
            
            $event->setStart([
                'dateTime' => $this->format_datetime($booking_data['booking_date'], $booking_data['booking_time']),
                'timeZone' => 'Europe/London',
            ]);
            
            $event->setEnd([
                'dateTime' => $this->calculate_end_time($booking_data['booking_date'], $booking_data['booking_time'], $booking_data['duration'] ?? 60),
                'timeZone' => 'Europe/London',
            ]);
            
            // Update event
            $updated_event = $this->calendar_service->events->update($this->calendar_id, $event_id, $event);
            
            return [
                'success' => true,
                'event_id' => $updated_event->getId(),
                'event_link' => $updated_event->getHtmlLink()
            ];
            
        } catch (\Exception $e) {
            error_log('Google Calendar update event error: ' . $e->getMessage());
            return new \WP_Error('event_update_failed', 'Failed to update calendar event: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete event from Google Calendar
     */
    public function delete_event($event_id) {
        if (!$this->is_available()) {
            return new \WP_Error('calendar_unavailable', 'Google Calendar service is not available');
        }
        
        try {
            $this->calendar_service->events->delete($this->calendar_id, $event_id);
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            error_log('Google Calendar delete event error: ' . $e->getMessage());
            return new \WP_Error('event_deletion_failed', 'Failed to delete calendar event: ' . $e->getMessage());
        }
    }
    
    /**
     * Get busy time slots for a date
     */
    public function get_busy_slots($date) {
        if (!$this->is_available()) {
            return [];
        }
        
        try {
            // Set time range for the entire day
            $start_time = new \DateTime($date . ' 00:00:00', new \DateTimeZone('Europe/London'));
            $end_time = new \DateTime($date . ' 23:59:59', new \DateTimeZone('Europe/London'));
            
            // Get events for the day
            $events = $this->calendar_service->events->listEvents($this->calendar_id, [
                'timeMin' => $start_time->format(\DateTime::RFC3339),
                'timeMax' => $end_time->format(\DateTime::RFC3339),
                'singleEvents' => true,
                'orderBy' => 'startTime'
            ]);
            
            $busy_slots = [];
            foreach ($events->getItems() as $event) {
                if ($event->getStart()->getDateTime()) {
                    $event_start = new \DateTime($event->getStart()->getDateTime());
                    $event_end = new \DateTime($event->getEnd()->getDateTime());
                    
                    $busy_slots[] = [
                        'start' => $event_start->format('H:i'),
                        'end' => $event_end->format('H:i'),
                        'title' => $event->getSummary()
                    ];
                }
            }
            
            return $busy_slots;
            
        } catch (\Exception $e) {
            error_log('Google Calendar get busy slots error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if time slot is available
     */
    public function is_slot_available($date, $time, $duration = 60) {
        if (!$this->is_available()) {
            return true; // Fallback to assuming available if calendar unavailable
        }
        
        try {
            $start_datetime = new \DateTime($date . ' ' . $time, new \DateTimeZone('Europe/London'));
            $end_datetime = clone $start_datetime;
            $end_datetime->add(new \DateInterval('PT' . $duration . 'M'));
            
            // Check for conflicting events
            $events = $this->calendar_service->events->listEvents($this->calendar_id, [
                'timeMin' => $start_datetime->format(\DateTime::RFC3339),
                'timeMax' => $end_datetime->format(\DateTime::RFC3339),
                'singleEvents' => true
            ]);
            
            return count($events->getItems()) === 0;
            
        } catch (\Exception $e) {
            error_log('Google Calendar availability check error: ' . $e->getMessage());
            return true; // Fallback to available
        }
    }
    
    /**
     * Generate event title
     */
    private function generate_event_title($booking_data) {
        $service_name = $booking_data['service_name'] ?? 'Service Appointment';
        $customer_name = $booking_data['customer_name'] ?? 'Customer';
        
        return "{$service_name} - {$customer_name}";
    }
    
    /**
     * Generate event description
     */
    private function generate_event_description($booking_data) {
        $description = "Blue Motors Southampton Appointment\n\n";
        
        if (isset($booking_data['customer_name'])) {
            $description .= "Customer: {$booking_data['customer_name']}\n";
        }
        
        if (isset($booking_data['customer_phone'])) {
            $description .= "Phone: {$booking_data['customer_phone']}\n";
        }
        
        if (isset($booking_data['customer_email'])) {
            $description .= "Email: {$booking_data['customer_email']}\n";
        }
        
        if (isset($booking_data['vehicle_reg'])) {
            $description .= "Vehicle: {$booking_data['vehicle_reg']}";
            
            if (isset($booking_data['vehicle_make']) && isset($booking_data['vehicle_model'])) {
                $description .= " ({$booking_data['vehicle_make']} {$booking_data['vehicle_model']})";
            }
            $description .= "\n";
        }
        
        if (isset($booking_data['service_name'])) {
            $description .= "Service: {$booking_data['service_name']}\n";
        }
        
        if (isset($booking_data['booking_reference'])) {
            $description .= "Reference: {$booking_data['booking_reference']}\n";
        }
        
        if (isset($booking_data['special_requirements'])) {
            $description .= "\nSpecial Requirements:\n{$booking_data['special_requirements']}\n";
        }
        
        return $description;
    }
    
    /**
     * Format datetime for Google Calendar
     */
    private function format_datetime($date, $time) {
        $datetime = new \DateTime($date . ' ' . $time, new \DateTimeZone('Europe/London'));
        return $datetime->format(\DateTime::RFC3339);
    }
    
    /**
     * Calculate end time
     */
    private function calculate_end_time($date, $time, $duration_minutes) {
        $datetime = new \DateTime($date . ' ' . $time, new \DateTimeZone('Europe/London'));
        $datetime->add(new \DateInterval('PT' . $duration_minutes . 'M'));
        return $datetime->format(\DateTime::RFC3339);
    }
    
    /**
     * Get calendar public URL
     */
    public function get_calendar_url() {
        return "https://calendar.google.com/calendar/embed?src=" . urlencode($this->calendar_id) . "&ctz=Europe%2FLondon";
    }
    
    /**
     * Test connection
     */
    public function test_connection() {
        if (!$this->is_available()) {
            return ['success' => false, 'message' => 'Google Calendar service not initialized'];
        }
        
        try {
            $calendar = $this->calendar_service->calendars->get($this->calendar_id);
            
            return [
                'success' => true,
                'calendar_name' => $calendar->getSummary(),
                'calendar_id' => $this->calendar_id,
                'timezone' => $calendar->getTimeZone()
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }
}

// Global access function
function bms_google_calendar() {
    static $instance = null;
    if ($instance === null) {
        $instance = new GoogleCalendarService();
    }
    return $instance;
}