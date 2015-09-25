<?php

namespace Api\Library\Shared\Communicate;

class Email
{
    /**
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $content
     * @param string $htmlContent
     */
    public static function send($from, $to, $subject, $content, $htmlContent = '')
    {
        // Create the Transport
        $transport = \Swift_SmtpTransport::newInstance('localhost', 25);

        // Create the Mailer using your created Transport
        $mailer = \Swift_Mailer::newInstance($transport);

        // Create a message
        $message = \Swift_Message::newInstance($subject);
        $message->setFrom($from);
        $message->setTo($to);
        $message->setBody($content);
        if ($htmlContent) {
            $message->addPart($htmlContent, 'text/html');
        }

        // Send the message
        $mailer->send($message);
    }
}
