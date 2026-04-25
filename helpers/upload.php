<?php

/**
 * File Upload Helper Functions
 * Handles product image uploads and file validation
 */

// Define upload directories
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('PRODUCT_IMG_DIR', UPLOAD_DIR . 'products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

/**
 * Create upload directories if they don't exist
 * @return void
 */
function initializeUploadDirs() {
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    if (!is_dir(PRODUCT_IMG_DIR)) {
        mkdir(PRODUCT_IMG_DIR, 0755, true);
    }
}

/**
 * Validate uploaded file
 * @param array $file $_FILES array element
 * @return array Validation result ['valid' => bool, 'error' => string]
 */
function validateUploadedFile($file) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds php.ini upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        return [
            'valid' => false,
            'error' => $errorMessages[$file['error']] ?? 'Unknown upload error'
        ];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return [
            'valid' => false,
            'error' => 'File size exceeds ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB limit'
        ];
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return [
            'valid' => false,
            'error' => 'File type not allowed. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS)
        ];
    }
    
    // Check MIME type
    $mimeType = mime_content_type($file['tmp_name']);
    if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
        return [
            'valid' => false,
            'error' => 'Invalid file MIME type'
        ];
    }
    
    return ['valid' => true];
}

/**
 * Upload product image
 * @param array $file $_FILES['image'] element
 * @return array Upload result ['success' => bool, 'filename' => string, 'error' => string]
 */
function uploadProductImage($file) {
    // Initialize directories
    initializeUploadDirs();
    
    // Validate file
    $validation = validateUploadedFile($file);
    if (!$validation['valid']) {
        return [
            'success' => false,
            'error' => $validation['error']
        ];
    }
    
    try {
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = PRODUCT_IMG_DIR . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => false,
                'error' => 'Failed to move uploaded file'
            ];
        }
        
        // Return relative path for database storage
        $relativePath = 'products/' . $filename;
        
        return [
            'success' => true,
            'filename' => $relativePath
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Delete product image
 * @param string $filename Image filename/path
 * @return bool True if deleted successfully
 */
function deleteProductImage($filename) {
    if (empty($filename)) {
        return true;
    }
    
    // Ensure we're only deleting from products directory
    $filepath = PRODUCT_IMG_DIR . basename($filename);
    
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    
    return true;
}

/**
 * Get full URL to product image
 * @param string $filename Relative filename from database
 * @return string Full URL to image
 */
function getImageUrl($filename) {
    if (empty($filename)) {
        return null;
    }
    
    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'];
    return $baseUrl . '/ecommerce_backend/uploads/' . $filename;
}
