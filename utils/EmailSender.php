<?php

/**
 * Email Sender Utility
 * Handles email notifications for contact form submissions
 */

class EmailSender
{
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        // Load email configuration
        require_once __DIR__ . '/../config/email.php';

        $this->smtpHost = SMTP_HOST;
        $this->smtpPort = SMTP_PORT;
        $this->smtpUsername = SMTP_USERNAME;
        $this->smtpPassword = SMTP_PASSWORD;
        $this->fromEmail = FROM_EMAIL;
        $this->fromName = FROM_NAME;
    }

    /**
     * Send contact form notification email
     */
    public function sendContactNotification($name, $email, $message, $ipAddress = null)
    {
        $subject = "New Contact Form Submission - {$name}";

        $htmlBody = $this->buildContactEmailHTML($name, $email, $message, $ipAddress);
        $textBody = $this->buildContactEmailText($name, $email, $message, $ipAddress);

        return $this->sendEmail(
            $this->fromEmail, // To (your email)
            $subject,
            $htmlBody,
            $textBody,
            $email, // Reply-to (sender's email)
            $name   // Reply-to name
        );
    }

    /**
     * Send email using SMTP or fallback to mail() function
     */
    private function sendEmail($to, $subject, $htmlBody, $textBody, $replyTo = null, $replyToName = null)
    {
        // Try SMTP first if PHPMailer is available
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return $this->sendEmailSMTP($to, $subject, $htmlBody, $textBody, $replyTo, $replyToName);
        }

        // Fallback to basic mail() function
        return $this->sendEmailBasic($to, $subject, $htmlBody, $textBody, $replyTo, $replyToName);
    }

    /**
     * Send email using SMTP with PHPMailer
     */
    private function sendEmailSMTP($to, $subject, $htmlBody, $textBody, $replyTo = null, $replyToName = null)
    {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = $this->smtpPort;
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);

            if ($replyTo) {
                $mail->addReplyTo($replyTo, $replyToName);
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody;

            $mail->send();
            error_log("SMTP Email sent successfully to: {$to}");
            return true;
        } catch (Exception $e) {
            error_log("SMTP Email failed: " . $e->getMessage());
            // Fallback to basic mail
            return $this->sendEmailBasic($to, $subject, $htmlBody, $textBody, $replyTo, $replyToName);
        }
    }

    /**
     * Send email using basic mail() function
     */
    private function sendEmailBasic($to, $subject, $htmlBody, $textBody, $replyTo = null, $replyToName = null)
    {
        // Set headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . ($replyToName ? $replyToName . ' <' . $replyTo . '>' : $replyTo),
            'X-Mailer: PHP/' . phpversion()
        ];

        $headerString = implode("\r\n", $headers);

        // Send email
        $result = mail($to, $subject, $htmlBody, $headerString);

        if ($result) {
            error_log("Basic Email sent successfully to: {$to}");
            return true;
        } else {
            error_log("Basic Email failed to send to: {$to}");
            return false;
        }
    }

    /**
     * Build HTML email body
     */
    private function buildContactEmailHTML($name, $email, $message, $ipAddress)
    {
        $date = date('Y-m-d H:i:s');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { background: #f8fafc; padding: 20px; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #374151; }
                .value { margin-top: 5px; padding: 10px; background: white; border-left: 4px solid #2563eb; }
                .footer { background: #374151; color: white; padding: 15px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📧 New Contact Form Submission</h1>
                </div>
                <div class='content'>
                    <div class='field'>
                        <div class='label'>👤 Name:</div>
                        <div class='value'>{$name}</div>
                    </div>
                    <div class='field'>
                        <div class='label'>📧 Email:</div>
                        <div class='value'>{$email}</div>
                    </div>
                    <div class='field'>
                        <div class='label'>💬 Message:</div>
                        <div class='value'>{$message}</div>
                    </div>
                    <div class='field'>
                        <div class='label'>🌐 IP Address:</div>
                        <div class='value'>{$ipAddress}</div>
                    </div>
                    <div class='field'>
                        <div class='label'>📅 Date:</div>
                        <div class='value'>{$date}</div>
                    </div>
                </div>
                <div class='footer'>
                    <p>This email was sent from your portfolio contact form.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Build plain text email body
     */
    private function buildContactEmailText($name, $email, $message, $ipAddress)
    {
        $date = date('Y-m-d H:i:s');

        return "
NEW CONTACT FORM SUBMISSION
============================

Name: {$name}
Email: {$email}
Message: {$message}
IP Address: {$ipAddress}
Date: {$date}

This email was sent from your portfolio contact form.
        ";
    }

    /**
     * Send auto-reply to the person who submitted the form
     */
    public function sendAutoReply($name, $email)
    {
        $subject = "Thank you for contacting IMAFORBES";

        $htmlBody = $this->buildAutoReplyHTML($name);
        $textBody = $this->buildAutoReplyText($name);

        return $this->sendEmail($email, $subject, $htmlBody, $textBody);
    }

    /**
     * Build auto-reply HTML
     */
    private function buildAutoReplyHTML($name)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { background: #f8fafc; padding: 20px; }
                .footer { background: #374151; color: white; padding: 15px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>👋 Thank You for Contacting Me!</h1>
                </div>
                <div class='content'>
                    <p>Hi {$name},</p>
                    <p>Thank you for reaching out through my portfolio contact form. I've received your message and will get back to you as soon as possible.</p>
                    <p>I typically respond within 24 hours, so please keep an eye on your inbox.</p>
                    <p>Best regards,<br>Imanol Pérez Arteaga<br>Full Stack Developer</p>
                </div>
                <div class='footer'>
                    <p>This is an automated response from imanol@imaforbes.com</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Build auto-reply text
     */
    private function buildAutoReplyText($name)
    {
        return "
Hi {$name},

Thank you for reaching out through my portfolio contact form. I've received your message and will get back to you as soon as possible.

I typically respond within 24 hours, so please keep an eye on your inbox.

Best regards,
Imanol Pérez Arteaga
Full Stack Developer

This is an automated response from imanol@imaforbes.com
        ";
    }
}
