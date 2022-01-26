# iflow_emailer

## 安装

```shell
composer require iflow/emailer
```

## 使用

```php
use iflow\EMailer\implement\Message\Html;
use iflow\EMailer\Mailer;

$config = [
    'default' => 'qqMailer',
    'emails' => [
        'mailer' => [
            'userName' => 'localhost',
            'passWord' => 'pwd',
            'fromName' => 'localhost：',
            'from' => 'root',
            'ssl' => true,
            'smtpHost' => 'smtp.exmail.domain.com',
            'smtpPort' => 465,
            'timeOut' => 1,
            'mimeVersion' => '1.0',
            'options' => [
                'open_eof_check' => true,
                'package_eof' => "\r\n",
                'package_max_length' => 65536
            ]
        ]
    ]
];

$mail = new Mailer($config, 'mailer');

$content = new Html();
$content = $content
    -> setHtml("<h1>测试邮件</h1> <strong>带附件</strong>")
    -> addAttachment('filename', 'filepath', encoded: 'base64')
    -> setSubject("测试邮件");
```