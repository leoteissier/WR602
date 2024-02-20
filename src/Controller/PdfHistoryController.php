<?php

namespace App\Controller;

use App\Entity\Pdf;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

/**
 * @method getDoctrine()
 */
class PdfHistoryController extends AbstractController
{
    private Security $security;
    private string $pdfDirectory;
    private EntityManagerInterface $entityManager;


    public function __construct(Security $security, string $pdfDirectory, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->pdfDirectory = $pdfDirectory;
        $this->entityManager = $entityManager;
    }
    public function index(): Response
    {
        // Get the current user
        $user = $this->security->getUser();

        // Initialisation de la variable $isPremium
        $isPremium = false;

        // Vérifiez si l'utilisateur a un objet Subscription associé et, si oui, accédez à son ID
        if ($user && $user->getSubscriptionId() != null) {
            $subscriptionId = $user->getSubscriptionId()->getId(); // Accédez à l'ID de l'abonnement

            // Vérifiez si l'ID de l'abonnement correspond à un abonnement payant
            $isPremium = in_array($subscriptionId, [2, 3]);
        }

        $pdfRepository = $this->entityManager->getRepository(Pdf::class);

        $pdfs = $pdfRepository->findBy(['userId' => $this->security->getUser()]);

        return $this->render('pdf_history/index.html.twig', [
            'pdfs' => $pdfs,
            'isPremium' => $isPremium,
        ]);
    }

    public function show(int $id): Response
    {
        $pdf = $this->entityManager->getRepository(Pdf::class)->find($id);
        // Check if the PDF exists
        if (!$pdf) {
            throw $this->createNotFoundException('PDF not found');
        }

        // Check if the PDF is owned by the current user
        if (!$this->isPdfOwnedByCurrentUser($pdf)) {
            throw $this->createAccessDeniedException('You are not allowed to access this PDF');
        }

        $pdfFilePath = $this->pdfDirectory . '/' . $pdf->getFilename();

        if (!file_exists($pdfFilePath)) {
            throw $this->createNotFoundException('PDF file not found');
        }

        $content = file_get_contents($pdfFilePath);
        $response = new Response($content);

        // Set appropriate headers for inline display
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="' . $pdf->getFilename() . '"');

        return $response;
    }

    public function download(int $id): Response
    {
        $pdf = $this->entityManager->getRepository(Pdf::class)->find($id);

        if (!$pdf) {
            throw $this->createNotFoundException('PDF not found');
        }

        // Check if the PDF is owned by the current user
        if (!$this->isPdfOwnedByCurrentUser($pdf)) {
            throw $this->createAccessDeniedException('You are not allowed to access this PDF');
        }

        $pdfFilePath = $this->pdfDirectory . '/' . $pdf->getFilename();

        if (!file_exists($pdfFilePath)) {
            throw $this->createNotFoundException('PDF file not found');
        }

        $content = file_get_contents($pdfFilePath);

        // Utiliser le nom spécifié par l'utilisateur pour le téléchargement, en fallback sur le nom de fichier original si non spécifié
        $downloadFileName = $pdf->getName() ? $pdf->getName() . '.pdf' : $pdf->getFilename();

        $response = new Response($content);

        // Set appropriate headers for download
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $downloadFileName . '"');

        return $response;
    }

    public function delete(int $id): Response
    {
        $pdf = $this->entityManager->getRepository(Pdf::class)->find($id);

        if (!$pdf) {
            throw $this->createNotFoundException('PDF not found');
        }

        // Check if the PDF is owned by the current user
        if (!$this->isPdfOwnedByCurrentUser($pdf)) {
            throw $this->createAccessDeniedException('You are not allowed to access this PDF');
        }

        $pdfFilePath = $this->pdfDirectory . '/' . $pdf->getFilename();

        if (file_exists($pdfFilePath)) {
            unlink($pdfFilePath);
        }

        // Use the already injected entityManager instead of getDoctrine()
        $this->entityManager->remove($pdf);
        $this->entityManager->flush();

        $this->addFlash('success', 'PDF supprimé avec succès.');

        return $this->redirectToRoute('app_pdf_history');
    }

    private function isPdfOwnedByCurrentUser(Pdf $pdf): bool
    {
        return $pdf->getUserId() === $this->security->getUser();
    }
}
