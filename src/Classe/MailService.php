<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class MailService
{
    private $api_key = '987aef575ad6eefcab9b80290f51f881';
    private $api_secret = 'c7fba4e65ba2f104d7b821a93d4aef35';

    public function send($to_email, $to_name, $subject, $content) 
    {
        $mj = new Client($this->api_key, $this->api_secret, true,['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "c.redikan@gmail.com",
                        'Name' => "La boutique Test"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 2381456,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
            }
}