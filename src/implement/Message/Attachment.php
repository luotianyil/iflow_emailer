<?php


namespace iflow\EMailer\implement\Message;


trait Attachment
{
    protected array $attachment = [];
    protected string $boundary = "";
    protected string $charSet = "utf-8";

    public function addAttachment(string $filename = '', string $filePath = '', ?string $mime = null, string $encoded = 'base64'): static {
        $this->attachment[] = [
            'fileName' => $filename !== '' ? $filename : basename($filePath),
            'path' => $filePath,
            'mime' => $mime,
            'encoded' => $encoded
        ];
        return $this;
    }

    protected function attachmentToBody($body): string {
        $this -> boundary = '----='.uniqid('_mailer');
        return $this->setAttachHeader($body);
    }

    protected function setAttachHeader($body): string {
        $body   = base64_encode($body);
        $body   = str_replace("\r\n" . '.', "\r\n" . '..', $body);
        $body   = str_starts_with($body, '.') ? '.' . $body : $body;

        $headers []    =  "Content-Type: multipart/mixed;boundary=\"{$this -> boundary}\"\r\n";
        $headers []    =  '--' . $this -> boundary;
        $headers []    =  'Content-Type: text/html;charset="'.$this->charSet.'"';
        $headers []    =  'Content-Transfer-Encoding: base64'. "\r\n";
        $headers []    =  '';
        $headers []    =  $body . "\r\n";
        foreach ($this->attachment as $file) {
            $this->genAttach($file, $headers);
        }
        $headers[] = "--" . $this -> boundary . "--";
        return str_replace("\r\n" . '.', "\r\n" . '..', trim(implode("\r\n", $headers)));
    }


    /**
     * 格式化附件内容
     * @param $file
     * @param $headers
     * @return void
     */
    protected function genAttach($file, &$headers) {
        if (file_exists($file['path'])) {
            $content = file_get_contents($file['path']);
            $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimetype = !$file['mime'] ? $fileInfo -> buffer($content)  : $file['mime'];

            if ($file['encoded'] === 'base64') $content = chunk_split(base64_encode($content));

            // 初始化文件头
            $headers[] = "--". $this -> boundary;
            $headers[] = 'Content-Type: ' . $mimetype . '; name="' . $file['fileName'] . '"';
            $headers[] = 'Content-disposition: attachment; filename="' . $file['fileName'] . '"';
            $headers[] = 'Content-Transfer-Encoding: '. $file['encoded'] . self::LF;
            $headers[] = '';
            $headers[] = $content . self::LF;
        }
    }

}