<?php

namespace App\Tests\Service;

use App\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class PdfServiceTest extends KernelTestCase
{
    private $clientMock;
    private $validatorMock;
    private PdfService $pdfService;
    private string $pdfDirectory = '/public/pdf';

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(HttpClientInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->pdfService = new PdfService(
            $this->clientMock,
            $this->validatorMock,
            'http://gotenberg',
            $this->pdfDirectory
        );

        // Mock the validator to return a ConstraintViolationList (empty for valid cases)
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
    }

    public function testGeneratePdfFromUrl(): void
    {
        $url = 'https://www.apple.com/';
        $pdfContent = 'PDF content';

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getContent')->willReturn($pdfContent);
        $this->clientMock->method('request')->willReturn($responseMock);

        $generatedFileName = $this->pdfService->generatePdf(['form' => ['url' => $url]]);

        $this->assertFileExists($this->pdfDirectory . '/' . $generatedFileName);

        // Cleanup
        unlink($this->pdfDirectory . '/' . $generatedFileName);
    }

    public function testGeneratePdfFromHtmlFile(): void
    {
        $originalFileName = 'test.html';
        $temporaryFilePath = sys_get_temp_dir() . '/' . $originalFileName;
        file_put_contents($temporaryFilePath, '<html lang="fr">Test</html>');
        $uploadedFile = new UploadedFile(
            $temporaryFilePath,
            $originalFileName,
            'text/html',
            null,
            true // Test mode
        );

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getContent')->willReturn('PDF content');
        $this->clientMock->method('request')->willReturn($responseMock);

        $generatedFileName = $this->pdfService->generatePdf(['form' => ['htmlFile' => $uploadedFile]]);

        $this->assertFileExists($this->pdfDirectory . '/' . $generatedFileName);

        // Cleanup
        unlink($this->pdfDirectory . '/' . $generatedFileName);
        unlink($temporaryFilePath);
    }
}
