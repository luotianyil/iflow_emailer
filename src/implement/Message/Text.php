<?php


namespace iflow\EMailer\implement\Message;


class Text extends Message {

    public function setText(string $content = ""): static {
        $this->body .= $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeader(): string {
        $header = "Content-Type: text/plain; charset={$this -> getCharSet()} \r\n";
        $header .= parent::getHeader();
        return $header;
    }
}