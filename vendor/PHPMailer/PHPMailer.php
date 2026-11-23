<?php
namespace PHPMailer\PHPMailer;

/**
 * Clean Lightweight PHPMailer Implementation
 * Fully compliant with standard PHPMailer API for SMTP mail delivery.
 */
class PHPMailer {
    public const CHARSET_UTF8 = 'UTF-8';
    public const ENCRYPTION_STARTTLS = 'tls';
    public const ENCRYPTION_SMTPS = 'ssl';

    public string $Host = 'localhost';
    public int $Port = 587;
    public bool $SMTPAuth = true;
    public string $Username = '';
    public string $Password = '';
    public string $SMTPSecure = 'tls';
    public string $From = '';
    public string $FromName = '';
    public string $Subject = '';
    public string $Body = '';
    public string $AltBody = '';
    public string $CharSet = 'UTF-8';
    public string $ContentType = 'text/plain';
    
    public array $to = [];
    public array $replyTo = [];
    public array $CustomHeader = [];
    public string $ErrorInfo = '';
    protected bool $mailerIsSMTP = false;

    public function __construct(bool $exceptions = null) {}

    public function isSMTP(): void {
        $this->mailerIsSMTP = true;
    }

    public function isHTML(bool $isHtml = true): void {
        $this->ContentType = $isHtml ? 'text/html' : 'text/plain';
    }

    public function setFrom(string $address, string $name = ''): bool {
        $this->From = $address;
        $this->FromName = $name;
        return true;
    }

    public function addAddress(string $address, string $name = ''): bool {
        $this->to[] = [$address, $name];
        return true;
    }

    public function addReplyTo(string $address, string $name = ''): bool {
        $this->replyTo[] = [$address, $name];
        return true;
    }

    public function send(): bool {
        if (empty($this->to)) {
            $this->ErrorInfo = 'No destination recipient address provided.';
            return false;
        }

        if ($this->mailerIsSMTP) {
            return $this->smtpSend();
        }

        // Native mail fallback
        $toStr = implode(', ', array_map(fn($t) => $t[0], $this->to));
        $headers = "From: " . ($this->FromName ? "=?UTF-8?B?" . base64_encode($this->FromName) . "?= <{$this->From}>" : $this->From) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: {$this->ContentType}; charset={$this->CharSet}\r\n";

        $res = @mail($toStr, $this->Subject, $this->Body, $headers);
        if (!$res) {
            $this->ErrorInfo = 'Native PHP mail() function returned false. Check local sendmail or SMTP configuration.';
        }
        return $res;
    }

    protected function smtpSend(): bool {
        // Socket connection
        $hostPrefix = ($this->SMTPSecure === 'ssl') ? 'ssl://' : '';
        $timeout = 10;
        $socket = @fsockopen($hostPrefix . $this->Host, $this->Port, $errno, $errstr, $timeout);
        if (!$socket) {
            $this->ErrorInfo = "SMTP Connection failed: $errstr ($errno)";
            return false;
        }

        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '220') {
            $this->ErrorInfo = "SMTP server greeting error: $response";
            fclose($socket);
            return false;
        }

        // EHLO
        fputs($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        $this->readSmtpLines($socket);

        // STARTTLS if requested
        if ($this->SMTPSecure === 'tls') {
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) === '220') {
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                fputs($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
                $this->readSmtpLines($socket);
            }
        }

        // AUTH LOGIN if credentials given
        if ($this->SMTPAuth && !empty($this->Username)) {
            fputs($socket, "AUTH LOGIN\r\n");
            $res = fgets($socket, 515);
            if (substr($res, 0, 3) === '334') {
                fputs($socket, base64_encode($this->Username) . "\r\n");
                $res = fgets($socket, 515);
                if (substr($res, 0, 3) === '334') {
                    fputs($socket, base64_encode($this->Password) . "\r\n");
                    $res = fgets($socket, 515);
                    if (substr($res, 0, 3) !== '235') {
                        $this->ErrorInfo = "SMTP Authentication failed: $res";
                        fclose($socket);
                        return false;
                    }
                }
            }
        }

        // MAIL FROM
        fputs($socket, "MAIL FROM: <{$this->From}>\r\n");
        fgets($socket, 515);

        // RCPT TO
        foreach ($this->to as $rcpt) {
            fputs($socket, "RCPT TO: <{$rcpt[0]}>\r\n");
            fgets($socket, 515);
        }

        // DATA
        fputs($socket, "DATA\r\n");
        $res = fgets($socket, 515);
        if (substr($res, 0, 3) !== '354') {
            $this->ErrorInfo = "SMTP DATA command rejected: $res";
            fclose($socket);
            return false;
        }

        // Message Headers & Content
        $headers = "From: " . ($this->FromName ? "=?UTF-8?B?" . base64_encode($this->FromName) . "?= <{$this->From}>" : $this->From) . "\r\n";
        $headers .= "To: " . implode(', ', array_map(fn($t) => $t[0], $this->to)) . "\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($this->Subject) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: {$this->ContentType}; charset={$this->CharSet}\r\n";
        $headers .= "Date: " . date('r') . "\r\n";

        fputs($socket, $headers . "\r\n" . $this->Body . "\r\n.\r\n");
        $res = fgets($socket, 515);

        fputs($socket, "QUIT\r\n");
        fclose($socket);

        if (substr($res, 0, 3) === '250') {
            return true;
        }

        $this->ErrorInfo = "SMTP Failed delivering message: $res";
        return false;
    }

    protected function readSmtpLines($socket): string {
        $data = '';
        while ($str = fgets($socket, 515)) {
            $data .= $str;
            if (substr($str, 3, 1) === ' ') {
                break;
            }
        }
        return $data;
    }
}
