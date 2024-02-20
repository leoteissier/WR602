<?php

namespace App\Tests\Service;

use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PdfServiceTest extends TestCase
{
    private PdfService $pdfService;

    public string $pdfDirectory;

    public function setUp(): void
    {
        $this->pdfDirectory = __DIR__ . '/../../public/pdf';
        $this->pdfService = new PdfService($this->createMock(HttpClientInterface::class),
        $this->createMock(ValidatorInterface::class),
        'http://gotenberg',
        $this->pdfDirectory,
        $this->createMock(EntityManagerInterface::class),
        $this->createMock(Security::class));
    }

    public function testGeneratePdfCreatesFile()
    {
        $data = [
            'url' => 'https://leoteissier.fr/',
            'pdfName' => 'mon_portfolio'
        ];

        dump($this->pdfService);

        // Créer le répertoire où les PDF sont sauvegardés
        if (!is_dir($this->pdfDirectory)) {
            mkdir($this->pdfDirectory, 0777, true);
        }

        // Obtenir le chemin du répertoire où les PDF sont sauvegardés
        $pdfDirectory = $this->pdfDirectory;
        $initialFiles = scandir($pdfDirectory);

        // Exécuter la fonctionnalité de génération de PDF
        $generatedPdfFileName = $this->pdfService->generatePdf($data);

        $finalFiles = scandir($pdfDirectory);
        $newFiles = array_diff($finalFiles, $initialFiles);

        // Vérifier qu'un nouveau fichier a été créé
        $this->assertCount(1, $newFiles, "Un nouveau fichier PDF devrait être créé.");

        // Construire le chemin complet du fichier PDF généré
        $generatedPdfFilePath = $this->pdfDirectory . '/' . $generatedPdfFileName;

        // Vérifier que le fichier généré existe
        $this->assertFileExists($generatedPdfFilePath);

        // Supprimer le fichier généré après le test
        unlink($generatedPdfFilePath);
    }
}
