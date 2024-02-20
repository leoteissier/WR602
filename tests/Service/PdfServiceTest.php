<?php

namespace App\Tests\Service;

use App\Service\PdfService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PdfServiceTest extends TestCase
{
    private PdfService $pdfService;
    private $pdfDirectory;

    protected function setUp(): void
    {
        $clientMock = $this->createMock(HttpClientInterface::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);

        // Create a mock user or whatever your application uses for authentication
        $userMock = $this->createMock(\App\Entity\User::class);

        // Mock the EntityManagerInterface
        $entityManagerMock = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);

        // Mock the Security component to return a user when getUser() is called
        $securityMock = $this->createMock(\Symfony\Bundle\SecurityBundle\Security::class);
        $securityMock->method('getUser')->willReturn($userMock); // Simulate a logged-in user

        $this->pdfService = new PdfService(
            $clientMock,
            $validatorMock,
            'http://gotenberg',
            $this->pdfDirectory = '/public/pdf',
            $entityManagerMock,
            $securityMock
        );

        // Mock the validator to return a ConstraintViolationList (empty for valid cases)
        $validatorMock->method('validate')->willReturn(new ConstraintViolationList());
    }

    public function testGeneratePdfCreatesFile()
    {
        $data = [
            'url' => 'https://leoteissier.fr/',
            'pdfName' => 'mon_portfolio' // Ce nom est pour l'entité et non pour le fichier physique.
        ];

        // Obtenir le chemin du répertoire où les PDF sont sauvegardés
        $pdfDirectory = $this->pdfDirectory;
        dump($pdfDirectory);
        $initialFiles = scandir($pdfDirectory);
        dump($initialFiles);

        // Exécuter la fonctionnalité de génération de PDF
        $generatedPdfFileName = $this->pdfService->generatePdf($data);

        $finalFiles = scandir($pdfDirectory);
        $newFiles = array_diff($finalFiles, $initialFiles);

        // Vérifier qu'un nouveau fichier a été créé
        $this->assertCount(1, $newFiles, "Un nouveau fichier PDF devrait être créé.");

        // Construire le chemin complet du fichier PDF généré
        $generatedPdfFilePath =  '/public/pdf/' . $generatedPdfFileName;

        // Vérifier que le fichier généré existe
        $this->assertFileExists($generatedPdfFilePath);

        // Supprimer le fichier généré
        unlink($generatedPdfFilePath);
    }
}
