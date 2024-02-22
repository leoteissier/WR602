<?php

namespace App\Controller;

use App\Form\PdfFormType;
use App\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

        $form = $this->createForm(PdfFormType::class);
        $form->handleRequest($request);

        // Récupérer le nombre de PDFs restants
        $pdfsRemaining = $this->pdfGeneratorService->getPdfLimitRemaining();
        // Récupérer le nombre total de PDFs
        $totalPDFs = $this->pdfGeneratorService->getTotalPDFsAllowed();

        return $this->render('pdf/index.html.twig', [
            'form' => $form->createView(),
            'pdfsRemaining' => $pdfsRemaining,
            'totalPDFs' => $totalPDFs,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function download(Request $request): Response
    {
        $form = $this->createForm(PdfFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                $formData = $form->getData();

                // Vérifier s'il reste des PDFs à générer
                if ($this->pdfGeneratorService->getPdfLimitRemaining() <= 0) {
                    // Rediriger vers une page pour augmenter l'abonnement
                    return $this->redirectToRoute('app_subscription_change');
                }

                $fileName = $this->pdfGeneratorService->generatePdf($formData);

                $filePath = $this->pdfDirectory . '/' . $fileName;
                $this->addFlash('success', 'Le fichier PDF a été généré avec succès.');
                return new BinaryFileResponse($filePath);
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Le fichier PDF n\'a pas pu être généré.');
                return $this->redirectToRoute('app_pdf_generate');
            }
        }

        // Si le formulaire n'est pas soumis ou n'est pas valide, redirigez vers la page du formulaire ou gérez l'erreur différemment
        return $this->redirectToRoute('app_pdf_generate');
    }

}