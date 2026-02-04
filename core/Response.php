<?php
// core/Response.php

namespace Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = '';

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function json(array $data, int $statusCode = 200): void
    {
        $this->statusCode = $statusCode;
        $this->setHeader('Content-Type', 'application/json');
        
        $this->send(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public function redirect(string $url, int $statusCode = 302): void
    {
        $this->statusCode = $statusCode;
        $this->setHeader('Location', $url);
        $this->send();
        exit;
    }

    public function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    public function download(string $filePath, ?string $fileName = null): void
    {
        if (!file_exists($filePath)) {
            $this->setStatusCode(404);
            $this->send('Arquivo não encontrado');
            return;
        }

        $fileName = $fileName ?? basename($filePath);
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);

        $this->setHeader('Content-Type', $mimeType);
        $this->setHeader('Content-Disposition', "attachment; filename=\"{$fileName}\"");
        $this->setHeader('Content-Length', (string) $fileSize);
        $this->setHeader('Cache-Control', 'private, max-age=0, must-revalidate');

        $this->sendHeaders();
        readfile($filePath);
        exit;
    }

    public function send(string $content = ''): void
    {
        http_response_code($this->statusCode);
        
        $this->sendHeaders();
        
        echo $content ?: $this->body;
    }

    private function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
    }

    public function withCookie(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true
    ): self {
        setcookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
        return $this;
    }

    public function deleteCookie(string $name): self
    {
        return $this->withCookie($name, '', time() - 3600);
    }
}