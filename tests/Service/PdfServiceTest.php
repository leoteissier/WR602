<?php

namespace App\Tests\Service;

use App\Service\PdfService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class PdfServiceTest extends TestCase
{
    private PdfService $pdfService;
    private string $pdfDirectory = '/public/pdf';

    protected function setUp(): void
    {
        $clientMock = $this->createMock(HttpClientInterface::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);

        // Create a mock user or whatever your application uses for authentication
        $userMock = $this->createMock(\App\Entity\User::class);

        // Mock the EntityManagerInterface
        $entityManagerMock = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);

        // Mock the Security component if required by PdfService
        $securityMock = $this->createMock(\Symfony\Bundle\SecurityBundle\Security::class);

        $this->pdfService = new PdfService(
            $clientMock,
            $validatorMock,
            'http://gotenberg',
            $this->pdfDirectory,
            $entityManagerMock,
            $securityMock
        );

        // Mock the validator to return a ConstraintViolationList (empty for valid cases)
        $validatorMock->method('validate')->willReturn(new ConstraintViolationList());
    }


    public function testUploadPdf()
    {
        $data = [
            'url' => 'http://test.com',
            'pdfName' => 'test.pdf'
        ];
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getContent')->willReturn('test.pdf');
        $clientMock = $this->createMock(HttpClientInterface::class);
        $clientMock->method('request')->willReturn($responseMock);
        $this->pdfService->generatePdf($data);
        $this->assertFileExists('public/pdf/test.pdf');
    }

}
