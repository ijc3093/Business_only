<?php
function sendNotificationEmail(string $to, string $subject, string $message): bool
{
    $subject = "[Gospel App] " . $subject;

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Gospel App <no-reply@gospel.local>\r\n";

    $body = "
      <div style='font-family:Arial,sans-serif;'>
        <h3>{$subject}</h3>
        <p>{$message}</p>
      </div>
    ";

    return @mail($to, $subject, $body, $headers);
}
