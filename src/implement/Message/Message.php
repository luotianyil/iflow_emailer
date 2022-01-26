<?php


namespace iflow\EMailer\implement\Message;


abstract class Message {

    use Attachment;

    protected array $header = [];
    protected mixed $body = "";
    protected string $Subject = "";

    protected array $Cc = [];
    protected array $Bcc = [];
    protected array $ReplyTo = [];
    protected array $Rcpt = [];

    protected CONST LF = "\r\n";

    public function setHeader(array $header): static {
        $this->header = array_replace_recursive($this->header, $header) ?? [];
        return $this;
    }

    /**
     * @return string
     */
    public function getHeader(): string {
        $header = "";
        foreach ($this->header as $key => $value) {
            $header .= "$key: $value". self::LF;
        }
        $header .= "Content-Transfer-Encoding: quoted-printable". self::LF;
        $header .= 'Date: '. date('r') . self::LF;
        return $header;
    }

    public function setSubject(string $Subject = ""): static {
        $this->Subject = "Subject: ". $Subject . self::LF;
        return $this;
    }

    public function getSubject(): string {
        return $this->Subject;
    }

    public function setCc(string $cc): static {
        $this->Cc[] = "Cc: <" . $cc . "> ";
        $this->Cc = array_unique($this->Cc);
        return $this;
    }

    public function setBcc(string $bcc): static {
        $this->Bcc[] = "Bcc: <" . $bcc . "> ";
        $this->Bcc = array_unique($this->Bcc);
        return $this;
    }

    /**
     * @param string $ReplyTo
     * @return static
     */
    public function setReplyTo(string $ReplyTo): static
    {
        $this->ReplyTo[] = "Reply-To: <" . $ReplyTo . "> ";
        $this->ReplyTo = array_unique($this->ReplyTo);
        return $this;
    }

    /**
     * @param string $Rcpt
     * @param string $dns
     * @return Message
     */
    public function setRcpt(string $Rcpt, string $dns = ''): static {

        if (empty($dns)) {
            $Rcpt = 'RCPT TO:<' .$Rcpt. '>';
        } else {
            $dns = strtoupper($dns);
            $notify = [];
            if (str_contains($dns, 'NEVER')) {
                $notify[] = 'NEVER';
            } else {
                foreach ([ 'SUCCESS', 'FAILURE', 'DELAY' ] as $value) {
                    if (str_contains($dns, $value)) $notify[] = $value;
                }
            }
            $Rcpt = 'RCPT TO:<' .$Rcpt. '> NOTIFY='.implode(',', $notify);
        }

        $this->Rcpt[] = $Rcpt;
        return $this;
    }

    /**
     * @param string $charSet
     * @return Message
     */
    public function setCharSet(string $charSet): static {
        $this->charSet = $charSet;
        return $this;
    }

    /**
     * @return string
     */
    public function getCharSet(): string {
        return $this->charSet;
    }

    public function getCc(): array {
        return $this->Cc;
    }

    /**
     * @return array
     */
    public function getBcc(): array {
        return $this->Bcc;
    }

    /**
     * @return array
     */
    public function getReplyTo(): array {
        return $this->ReplyTo;
    }

    /**
     * @return array
     */
    public function getRcpt(): array {
        return $this->Rcpt;
    }

    public function getBody(): string {
        return empty($this->attachment) ? self::LF . $this->body : $this->attachmentToBody($this->body);
    }
}