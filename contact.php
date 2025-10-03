<?php

/**
 * Legacy Contact Form Endpoint
 * Redirects to new API structure for backward compatibility
 */

// Redirect to new API endpoint
header("Location: api/contact.php", true, 301);
exit();
