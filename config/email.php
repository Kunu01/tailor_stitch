<?php

function format_order_email($client_name, $order_details) {
    $message = "Dear $client_name,\n\n";
    $message .= "Your order is now complete and ready for collection.\n\n";
    $message .= "Order Details:\n";
    $message .= " - Order Type: {$order_details['cloth_type']}\n";
    $message .= " - Material: {$order_details['cloth_material']}\n\n";
    $message .= "Thank you for choosing Tailor Stitch.\n";
    $message .= "\nBest regards,\nTailor Stitch Team";
    
    return $message;
}

function send_email($to, $subject, $message) {
    $headers = "From: no-reply@tailorstitch.com";
    
    $success = mail($to, $subject, $message, $headers);
    
    return [
        'success' => $success,
        'error' => $success ? null : error_get_last()['message']
    ];
}