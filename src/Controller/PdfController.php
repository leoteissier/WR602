<?php

namespace App\Controller;

use App\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PdfController extends AbstractController
{
    private PdfService $pdfGeneratorService;
    private string $pdfDirectory;

    public function __construct(PdfService $pdfGeneratorService, string $pdfDirectory)
    {
        $this->pdfGeneratorService = $pdfGeneratorService;
        $this->pdfDirectory = $pdfDirectory;
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function index(Request $request): Response
    {
        // Créez un formulaire pour collecter l'URL ou le fichier HTML
        $form = $this->createFormBuilder()
            ->add('url', TextType::class, [
                'required' => false,
                'label' => 'Enter a URL:',
                'constraints' => [
                    new Regex([
                        'pattern' => '/\b(?:https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i',
                        'message' => 'Please enter a valid URL.',
                    ]),
                ],
            ])
            ->add('htmlFile', FileType::class, [
                'required' => false,
                'label' => 'Or upload an HTML file:'
            ])
            ->getForm();

        // Traitez le formulaire
        $form->handleRequest($request);

        // Affichez le formulaire
        return $this->render('pdf/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function download(Request $request): BinaryFileResponse
    {
        // Récupérez les données du formulaire
        $formData = $request->request->all();
        $pdfFileName = $this->pdfGeneratorService->generatePdf($formData);
        $pdfFilePath = $this->pdfDirectory . '/' . $pdfFileName;

        if (!file_exists($pdfFilePath)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas.');
        }

        // Créer une afficher le fichier PDF
        return new BinaryFileResponse($pdfFilePath);
    }
}