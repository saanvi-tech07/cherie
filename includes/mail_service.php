<?php
/**
 * CHÉRIE — Email Service Layer using PHPMailer
 * Real SMTP integration with transparent status reporting.
 */

if (!defined('CHERIE_APP')) {
    define('CHERIE_APP', true);
}

require_once ROOT_PATH . '/config/mail.php';
require_once ROOT_PATH . '/vendor/PHPMailer/Exception.php';
require_once ROOT_PATH . '/vendor/PHPMailer/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {

    /**
     * Internal sender helper
     */
    private static function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody): array {
        // Transparent configuration check: Do not pretend emails were sent!
        if (!MAIL_ENABLED || empty(MAIL_USERNAME) || MAIL_USERNAME === 'your-email@gmail.com') {
            return [
                'sent' => false,
                'status' => 'NOT_CONFIGURED',
                'message' => 'Email notification skipped: SMTP credentials are not configured in config/mail.php. (To enable real email sending, set MAIL_ENABLED=true and provide your SMTP Host, Username & App Password).'
            ];
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USERNAME;
            $mail->Password = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION;
            $mail->Port = MAIL_PORT;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = self::wrapEmailTemplate($subject, $htmlBody);

            $result = $mail->send();
            if ($result) {
                return ['sent' => true, 'status' => 'DELIVERED', 'message' => 'Email successfully sent via SMTP.'];
            } else {
                return ['sent' => false, 'status' => 'FAILED', 'message' => 'SMTP Error: ' . $mail->ErrorInfo];
            }
        } catch (\Exception $e) {
            return ['sent' => false, 'status' => 'ERROR', 'message' => 'Mailer Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Send Customer Registration Confirmation Email
     */
    public static function sendRegistrationConfirmation(string $email, string $fullName): array {
        $subject = 'Welcome to CHÉRIE, ' . $fullName . ' ✨';
        $body = "
            <p>Dear {$fullName},</p>
            <p>Welcome to our boutique family! We are thrilled to have you with us.</p>
            <p>At <strong>CHÉRIE</strong>, every piece is curated with love, grace, and timeless elegance.</p>
            <p style='margin: 20px 0;'>
                Use code <span style='background:#F7DCE5; padding:4px 8px; border-radius:4px; font-weight:bold; color:#49383C;'>CHERIE15</span> at checkout to enjoy <strong>15% off</strong> on your first order.
            </p>
            <p><a href='" . BASE_URL . "/shop.php' style='display:inline-block; background:#C58A9C; color:#fff; padding:10px 20px; border-radius:30px; text-decoration:none;'>Explore The Collection</a></p>
        ";
        return self::sendEmail($email, $fullName, $subject, $body);
    }

    /**
     * Send Customer Order Confirmation Email
     */
    public static function sendOrderConfirmation(array $order, array $orderItems): array {
        $subject = 'Order Confirmed: #' . $order['order_number'] . ' — CHÉRIE';
        $itemsHtml = "<table style='width:100%; border-collapse:collapse; margin:15px 0;'>
            <thead>
                <tr style='border-bottom:1px solid #C58A9C; text-align:left;'>
                    <th style='padding:8px 0;'>Item</th>
                    <th style='padding:8px 0;'>Qty</th>
                    <th style='padding:8px 0; text-align:right;'>Price</th>
                </tr>
            </thead>
            <tbody>";
        foreach ($orderItems as $item) {
            $itemsHtml .= "<tr>
                <td style='padding:8px 0; border-bottom:1px solid #f0e6e8;'>" . htmlspecialchars($item['product_name']) . "</td>
                <td style='padding:8px 0; border-bottom:1px solid #f0e6e8;'>" . (int)$item['quantity'] . "</td>
                <td style='padding:8px 0; border-bottom:1px solid #f0e6e8; text-align:right;'>" . formatPrice($item['price'] * $item['quantity']) . "</td>
            </tr>";
        }
        $itemsHtml .= "</tbody></table>";

        $body = "
            <p>Dear " . htmlspecialchars($order['customer_name']) . ",</p>
            <p>Thank you for choosing CHÉRIE! Your order has been placed successfully and is being prepared with tender care.</p>
            <p><strong>Order Number:</strong> #" . htmlspecialchars($order['order_number']) . "<br>
            <strong>Payment Method:</strong> " . strtoupper($order['payment_method']) . " (" . ucfirst($order['payment_status']) . ")<br>
            <strong>Total Amount:</strong> " . formatPrice($order['total_amount']) . "</p>
            {$itemsHtml}
            <p><strong>Delivery Address:</strong><br>
            " . nl2br(htmlspecialchars($order['shipping_address'])) . "<br>
            " . htmlspecialchars($order['city']) . ", " . htmlspecialchars($order['state']) . " - " . htmlspecialchars($order['postal_code']) . "</p>
        ";
        return self::sendEmail($order['customer_email'], $order['customer_name'], $subject, $body);
    }

    /**
     * Send Password Reset Link Email
     */
    public static function sendPasswordReset(string $email, string $resetToken): array {
        $resetUrl = BASE_URL . '/reset-password.php?token=' . urlencode($resetToken) . '&email=' . urlencode($email);
        $subject = 'Reset Your CHÉRIE Password';
        $body = "
            <p>Hello,</p>
            <p>We received a request to reset your password for your CHÉRIE account.</p>
            <p style='margin: 25px 0;'>
                <a href='{$resetUrl}' style='display:inline-block; background:#C58A9C; color:#fff; padding:12px 24px; border-radius:30px; text-decoration:none;'>Reset Password</a>
            </p>
            <p style='font-size:12px; color:#777;'>This link is valid for 1 hour. If you did not request this, please safely ignore this email.</p>
        ";
        return self::sendEmail($email, 'Cherie Customer', $subject, $body);
    }

    /**
     * Notify Admin of New Order
     */
    public static function notifyAdminNewOrder(array $order): array {
        $subject = '[New Order Received] #' . $order['order_number'] . ' — ' . formatPrice($order['total_amount']);
        $body = "
            <p>Hello Admin,</p>
            <p>A new order has just been placed on CHÉRIE!</p>
            <ul>
                <li><strong>Order Number:</strong> #" . htmlspecialchars($order['order_number']) . "</li>
                <li><strong>Customer:</strong> " . htmlspecialchars($order['customer_name']) . " (" . htmlspecialchars($order['customer_email']) . ")</li>
                <li><strong>Amount:</strong> " . formatPrice($order['total_amount']) . "</li>
                <li><strong>Payment:</strong> " . strtoupper($order['payment_method']) . "</li>
            </ul>
            <p><a href='" . ADMIN_URL . "/orders/view.php?id=" . $order['id'] . "'>View Order Details in Admin Panel</a></p>
        ";
        return self::sendEmail(ADMIN_NOTIFICATION_EMAIL, 'Store Admin', $subject, $body);
    }

    /**
     * Brand HTML Email Wrapper
     */
    private static function wrapEmailTemplate(string $title, string $content): string {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>{$title}</title>
        </head>
        <body style='margin:0; padding:20px; background-color:#FFF9F5; font-family:Arial, sans-serif; color:#49383C; line-height:1.6;'>
            <div style='max-width:600px; margin:0 auto; background:#ffffff; border-radius:16px; border:1px solid #F7DCE5; overflow:hidden;'>
                <div style='background:linear-gradient(135deg, #F7DCE5, #EAE3F5); padding:30px; text-align:center;'>
                    <h1 style='margin:0; font-family:Georgia, serif; font-size:32px; letter-spacing:4px; color:#49383C;'>CHÉRIE</h1>
                    <p style='margin:6px 0 0; font-size:12px; color:#C58A9C; letter-spacing:1px;'>A Little Love in Every Detail.</p>
                </div>
                <div style='padding:30px;'>
                    {$content}
                    <hr style='border:none; border-top:1px solid #F7DCE5; margin:30px 0;'>
                    <p style='font-size:12px; color:#99888C; text-align:center; margin:0;'>
                        With love,<br>
                        <strong>CHÉRIE Boutique</strong><br>
                        " . htmlspecialchars(getSetting('site_address', 'Mumbai, India')) . "
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }
}
