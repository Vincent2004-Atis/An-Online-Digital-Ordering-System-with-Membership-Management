<?php
/**
 * notify_helper.php
 * Call createNotification() whenever an order status changes.
 */

function createNotification(mysqli $db, int $userId, int $orderId, string $orderStatus): void {
    $messages = [
        'processing' => [
            'title'   => '📦 Your order is on its way!',
            'message' => "Your Order #$orderId is now being processed and will be delivered to your address soon. Please prepare your payment upon delivery.",
        ],
        'completed' => [
            'title'   => '✅ Order Completed!',
            'message' => "Your Order #$orderId has been completed. Thank you for shopping with Amazing World Marketing Corporation!",
        ],
        'pending' => [
            'title'   => '🕐 Order is Pending',
            'message' => "Your Order #$orderId is pending. We will process it shortly.",
        ],
    ];

    if (!isset($messages[$orderStatus])) return;

    $title   = $messages[$orderStatus]['title'];
    $message = $messages[$orderStatus]['message'];

    $stmt = $db->prepare("
        INSERT INTO notifications (user_id, order_id, title, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('iiss', $userId, $orderId, $title, $message);
    $stmt->execute();
    $stmt->close();
}