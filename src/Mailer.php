<?php

namespace iflow\EMailer;

use iflow\EMailer\implement\Config;
use iflow\EMailer\implement\Generate\GenerateMailer;
use iflow\EMailer\implement\Message\Message;

class Mailer {

    protected Config $config;
    protected GenerateMailer $generateMailer;

    protected array $defaultConfig = [
        'default' => 'mailServer',
        'emails' => [
            'mailServer' => [
                'userName' => 'localhost',
                'passWord' => 'pwd',
                'fromName' => 'localhost：',
                'from' => 'root',
                'ssl' => true,
                'smtpHost' => 'smtp.exmail.domain.com',
                'smtpPort' => 465,
                'timeOut' => 0.5,
                'mimeVersion' => '1.0',
                'options' => [
                    'open_eof_check' => true,
                    'package_eof' => "\r\n",
                    'package_max_length' => 65536
                ]
            ]
        ]
    ];

    /**
     * 接收邮件邮箱地址
     * @var string[]
     */
    protected array $to = [];

    public function __construct(array $config, string $default = 'mailServer') {
        $this->config = new Config($config['emails'][$default ?: $config['default']]);
        $this->generateMailer = new GenerateMailer($this, $this->config);
    }

    /**
     * @param string|array $to
     * @return Mailer
     */
    public function setTo(string|array $to): Mailer {
        if (is_string($to)) $this->to[] = $to;
        else $this->to = array_merge($this -> to, $to);
        $this->to = array_unique($this->to);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getTo(): array {
        return $this->to;
    }

    /**
     * 发送邮件
     * @param Message $message
     * @return bool
     * @throws implement\Exception\MailerException
     */
    public function send(Message $message): bool {
        return $this->generateMailer -> push($message);
    }

}