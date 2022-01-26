<?php


namespace iflow\EMailer\implement\Message;


class Html extends Message
{
    public function setHtml(string $html = ''): static {
        $this->body .= $html . "\r\n";
        return $this;
    }

    /**
     * @return string
     */
    public function getHeader(): string {
        $header = "Content-Type: text/html; charset={$this -> getCharSet()} \r\n";
        $header .= parent::getHeader();
        return $header;
    }
}