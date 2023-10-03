<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Send email with SMTP protocolo
 *
 * @param string $to Recipient email
 * @param string $subject
 * @param string $message Mail body, support HTML
 * @param string $fromName
 * @param string[] $bccList Blind-carbon-copy email list
 *
 * @return bool
 * @throws Exception
 */
function send_email($to, $subject, $message, $fromName, $bccList = [])
{
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->setFrom(SMTP_FROM, $fromName);
    if ($bccList && is_array($bccList)) {
        foreach ($bccList as $bccAddress) {
            $mail->addBCC($bccAddress);
        }
    }
    $mail->addReplyTo(SMTP_FROM, $fromName);
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->Body = $message;
    /*$mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );*/
    if (!$mail->send()) {
        //echo 'Mailer Error: ' . $mail->ErrorInfo;
        return false;
    } else {
        //echo 'The email message was sent.';
        return true;
    }
}