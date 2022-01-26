<?php

namespace iflow\EMailer\implement;

class Config {

    public function __construct(
        protected array $config = []
    ) {}

    /**
     * 服务器地址
     * @return string
     */
    public function getHost(): string {
        return $this->config['smtpHost'] ?? '';
    }

    /**
     * 服务器端口
     * @return int
     */
    public function getPort(): int {
        return $this->config['smtpPort'] ?? 0;
    }

    /**
     * 是否启用SSL
     * @return bool
     */
    public function getSsl(): bool {
        return $this->config['ssl'] ?? true;
    }

    /**
     * 用户名
     * @return string
     */
    public function getUserName(): string {
        return $this->config['userName'] ?? '';
    }

    /**
     * 密码
     * @return string
     */
    public function getPassWord(): string {
        return $this->config['passWord'] ?? '';
    }

    /**
     * 会话超时时间
     * @return int|float
     */
    public function getTimeOut(): int|float {
        return $this->config['timeOut'] ?? 30;
    }

    /**
     * Socket参数
     * @return array
     */
    public function getOptions(): array {
        return $this->config['options'] ?? [ 'open_eof_check' => true, 'package_eof' => "\r\n", 'package_max_length' => 1024 * 1024 * 2 ];
    }

    /**
     * 发件人
     * @return string
     */
    public function getFrom(): string {
        return $this->config['from'] ?? '';
    }

    /**
     * 发件人名称
     * @return string
     */
    public function getFromName(): string {
        return $this->config['fromName'] ?? '';
    }

    /**
     * 编码格式
     * @return string
     */
    public function getCharSet(): string {
        return $this->config['charSet'] ?? 'utf-8';
    }

    /**
     * MIME VERSION
     * @return string
     */
    public function mimeVersion(): string {
        return $this->config['mimeVersion'] ?? '1.0';
    }


}