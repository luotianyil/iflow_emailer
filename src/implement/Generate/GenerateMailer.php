<?php

namespace iflow\EMailer\implement\Generate;

use iflow\EMailer\implement\Config;
use iflow\EMailer\implement\Exception\MailerException;
use iflow\EMailer\implement\Message\Message;
use iflow\EMailer\implement\Socket\Client;
use iflow\EMailer\Mailer;

class GenerateMailer {

    public function __construct(
       protected Mailer $mailer,
       protected Config $config,
       protected object|string $client = ''
    ) {}


    /**
     * 初始化连接
     * @return $this
     * @throws MailerException
     */
    protected function connection(): GenerateMailer {
        $this->client = $this->getClient();
        $this->client -> set($this -> config -> getOptions());

        if (!$this->client -> connect($this -> config -> getHost(), $this -> config -> getPort(), $this -> config -> getTimeOut())) {
            throw new MailerException('smtpServer connection error: '. $this->client -> errMsg . 'code: '. $this->client -> errCode);
        }

        return $this;
    }

    /**
     * @param string $host
     * @return $this
     * @throws MailerException
     */
    protected function hello(string $host): GenerateMailer {
        try {
            $this->send("EHLO $host", 250);
        } catch (MailerException $exception) {
            $this->send("HELO $host", 250);
        }
        while (true) {
            $receive = $this->getRecv();
            if (substr($receive, 3, 1) !== '-') break;
        }
        return $this;
    }

    /**
     * 登录远程邮件服务
     * @return $this
     * @throws MailerException
     */
    protected function login(): GenerateMailer {
        $this->send('AUTH LOGIN', 334);
        $this->send(base64_encode($this->config -> getUserName()), 334);
        $this->send(base64_encode($this->config -> getPassWord()), 235);
        return $this;
    }


    /**
     * 发送邮件
     * @param Message $message
     * @return bool
     * @throws MailerException
     */
    protected function toMail(Message $message): bool {
        $this->send("MAIL FROM:<{$this -> config -> getFrom()}>", 250);

        foreach ($this->mailer -> getTo() as $to) $this->send('RCPT TO:<'.$to.'>', 250);
        foreach ($message -> getRcpt() as $to) $this->send($to, [ 250, 251 ]);

        $this->send('DATA', 354);
        $this->send($this -> setMailBody($message));
        $this->send('.', 250);
        return $this->close();
    }

    /**
     * 设置邮件主体内容
     * @param Message $message
     * @return string
     */
    protected function setMailBody(Message $message): string {
        $header = $message -> getHeader();
        $header .= "MIME-Version: ". $this->config -> mimeVersion(). "\r\n";
        $header .= "X-Mailer: By (PHP/" . phpversion() . ")\r\n";
        $header .= "From: {$this -> config -> getFromName()}<{$this -> config -> getFrom()}>\r\n";
        $header .= $message -> getSubject();
        foreach ($this->mailer -> getTo() as $key) $header .= "To:<$key>\r\n";

        $header .= implode("\r\n", $message -> getCc());
        $header .= implode("\r\n", $message -> getBcc());
        $header .= implode("\r\n", $message -> getReplyTo());

        $header = trim($header, "\r\n") . "\r\n";
        return $header . $message -> getBody();
    }

    /**
     * 向邮件服务器发送信息
     * @param string $command
     * @param int|array $code
     * @return mixed
     * @throws MailerException
     */
    protected function send(string $command = '', int|array $code = -1): mixed {
        $send = $this->client -> send(trim($command, "\r\n") . "\r\n");
        if (!$send) throw new MailerException("邮件发送失败 SendStatus: ". $send);
        return $code > 0 ? $this->recvCode($code) : $send;
    }

    /**
     * 获取响应内容
     * @return mixed
     */
    protected function getRecv() {
        return $this->client -> recv($this -> config -> getTimeOut());
    }

    /**
     * 验证响应CODE
     * @param int|array $code
     * @return array
     * @throws MailerException
     */
    protected function recvCode(int|array $code): array {
        $recv = $this->getRecv();
        $code = is_int($code) ? [ $code ] : $code;
        if ($recv) {
            $recvCode = false;
            foreach ($code as $val) if ($recvCode = str_contains($recv, $val)) break;
            $recv = explode(' ', $recv);
            return $recvCode ? $recv : throw new MailerException("smtpServer code vaild fail, code need ". implode(',', $code));
        }
        return [];
    }

    /**
     * 获取客户端链接句柄
     * @return object
     */
    public function getClient(): object {
        if (is_object($this->client)) return $this->client;

        if ($this->client !== '' && class_exists($this->client)) return $this->client = new $this -> client($this->config -> getSsl());

        if (extension_loaded('swoole')) {
            $this->client = new \Swoole\Coroutine\Client($this->config -> getSsl() ? SWOOLE_SSL | SWOOLE_TCP : SWOOLE_TCP);
        }

        return $this->client = new Client($this->config -> getSsl());
    }

    /**
     * 关闭链接
     * @return bool
     */
    protected function close() {
        if ($this->client -> isConnected()) {
            $this->client -> send("QUIT");
            $this->client -> close();
        }
        return true;
    }

    /**
     * 发送邮件
     * @param Message $message
     * @return bool
     * @throws MailerException
     */
    public function push(Message $message): bool {
        $this->connection();
        $recv = $this->recvCode(220);
        return $this -> hello($recv[1]) -> login() -> toMail($message);
    }

    public function rest() {
        return $this->send('REST', 250);
    }
}