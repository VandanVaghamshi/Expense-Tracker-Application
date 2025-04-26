<?php
/**
 * Utility Functions
 * 
 * Common functions used across the application.
 */

/**
 * Send JSON response
 * 
 * @param array $data Response data
 * @param int $status_code HTTP status code
 * @return void
 */
function sendResponse($data, $status_code = 200) {
    // Set HTTP response code
    http_response_code($status_code);
    
    // Set content type to JSON
    header('Content-Type: application/json');
    
    // Output JSON data
    echo json_encode($data);
    exit;
}

/**
 * Validate date format (YYYY-MM-DD)
 * 
 * @param string $date Date string
 * @return boolean True if valid, false otherwise
 */
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Clean input data
 * 
 * @param mixed $data Input data
 * @return mixed Cleaned data
 */
function cleanInput($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = cleanInput($value);
        }
    } else {
        $data = htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

/**
 * Get POST data
 * 
 * @return array Cleaned POST data
 */
function getPostData() {
    return cleanInput($_POST);
}

/**
 * Get GET data
 * 
 * @return array Cleaned GET data
 */
function getGetData() {
    return cleanInput($_GET);
}

/**
 * Get JSON data from request body
 * 
 * @return array Decoded JSON data
 */
function getJsonData() {
    $json = file_get_contents('php://input');
    return json_decode($json, true);
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Check if request is AJAX
 * 
 * @return boolean True if AJAX request, false otherwise
 */
function isAjaxRequest() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}

/**
 * Generate CSV file from array
 * 
 * @param array $data Array of data
 * @param string $filename Filename for download
 * @return void
 */
function generateCSV($data, $filename = 'export.csv') {
    if (empty($data)) {
        return false;
    }
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add headers as first row if data is not empty
    if (!empty($data[0])) {
        fputcsv($output, array_keys($data[0]));
    }
    
    // Add data rows
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    // Close output stream
    fclose($output);
    exit;
}

/**
 * Format currency
 * 
 * @param float $amount Amount to format
 * @param string $currency Currency symbol (default: $)
 * @return string Formatted currency
 */
function formatCurrency($amount, $currency = '$') {
    return $currency . number_format($amount, 2);
}

/**
 * Format date
 * 
 * @param string $date Date string (YYYY-MM-DD)
 * @param string $format Output format (default: M d, Y)
 * @return string Formatted date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Get current page name
 * 
 * @return string Current page name
 */
function getCurrentPage() {
    return basename($_SERVER['PHP_SELF']);
}

/**
 * Check if current page is active
 * 
 * @param string $page Page name to check
 * @return string 'active' if current page, empty string otherwise
 */
function isActivePage($page) {
    return (getCurrentPage() == $page) ? 'active' : '';
}