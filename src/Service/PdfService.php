<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PdfService
{
    private HttpClientInterface $client;
    private ValidatorInterface $validator;
    private string $gotenbergUrl;
    private string $pdfDirectory;

    public function __construct(HttpClientInterface $client, ValidatorInterface $validator, string $gotenbergUrl, string $pdfDirectory)
    {
        $this->client = $client;
        $this->validator = $validator;
        $this->gotenbergUrl = $gotenbergUrl;
        $this->pdfDirectory = $pdfDirectory;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function generatePdf(array $data): string
    {
        $formData = $data['form'] ?? [];
        $url = $formData['url'] ?? null;
        $file = $formData['htmlFile'] ?? null;

        if ($this->isValidUrl($url)) {
            return $this->generatePdfGeneric(['url' => $url], '/forms/chromium/convert/url');
        } elseif ($file instanceof UploadedFile) {
            $htmlContent = file_get_contents($file->getPathname());
            return $this->generatePdfGeneric(['htmlContent' => $htmlContent], '/forms/chromium/convert/html');
        }

        throw new \InvalidArgumentException('You must provide a URL or a file.');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function generatePdfGeneric(array $data, string $endpoint): string
    {
        $body = [];
        if (isset($data['url'])) {
            $body['url'] = $data['url'];
        } else if (isset($data['htmlContent'])) {
            $body['files'] = [
                'file' => [
                    'content' => $data['htmlContent'],
                    'filename' => 'document.html',
                ],
            ];
        }

        $response = $this->client->request(
            'POST',
            $this->gotenbergUrl . $endpoint,
            [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
                'body' => $body,
            ]
        );

        $pdfFileName = uniqid('pdf_', true) . '.pdf';
        $pdfFilePath = $this->pdfDirectory . '/' . $pdfFileName;

        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->pdfDirectory)) {
            $filesystem->mkdir($this->pdfDirectory);
        }

        file_put_contents($pdfFilePath, $response->getContent());
        return $pdfFileName;
    }

    private function isValidUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $violations = $this->validator->validate($url, new Url());
        return 0 === count($violations);
    }
}