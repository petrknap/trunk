<?php

namespace PetrKnapCz;

use Swift_Mailer;
use Swift_SmtpTransport;

class SwiftMailerFactory
{
    public static function create(string $host, int $port, string $username = null, string $password = null, string $encryption = null)
    {
        $transport = new Swift_SmtpTransport($host, $port);

        if ($username) {
            $transport->setUsername($username);
        }

        if ($password) {
            $transport->setPassword($password);
        }

        if ($encryption) {
            $transport->setEncryption($encryption);
        }

        return new Swift_Mailer($transport);
    }
}
