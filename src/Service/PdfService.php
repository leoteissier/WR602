<?php

namespace App\Service;

use App\Entity\Pdf;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
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
    private EntityManagerInterface $entityManager;
    private Security $security;


    public function __construct(HttpClientInterface $client, ValidatorInterface $validator, string $gotenbergUrl, string $pdfDirectory, EntityManagerInterface $entityManager, Security $security)
    {
        $this->client = $client;
        $this->validator = $validator;
        $this->gotenbergUrl = $gotenbergUrl;
        $this->pdfDirectory = $pdfDirectory;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function generatePdf(array $data): string
    {
        $url = $data['url'] ?? null;
        $name = $data['pdfName'] ?? null;

        if ($url && $this->isValidUrl($url)) {
            return $this->generatePdfGeneric(['url' => $url, 'pdfName' => $name], '/forms/chromium/convert/url');
        } else {
            throw new \InvalidArgumentException('Invalid URL.');
        }
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

        // Get the current user
        $user = $this->security->getUser();

        // Create and persist a new Pdf entity
        $pdf = new Pdf();
        $pdf->setFilename($pdfFileName);
        $pdf->setName($data['pdfName']);
        $pdf->setUser($user);
        $this->entityManager->persist($pdf);
        $this->entityManager->flush();

        return $pdfFileName;
    }

    /**
     * @param string|null $url
     * @return bool
     */
    private function isValidUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $violations = $this->validator->validate($url, new Url([
            'protocols' => ['http', 'https'],
        ]));

        if(count($violations) > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function getPdfLimitRemaining(): int
    {
        $user = $this->security->getUser();

        $subscription = $user->getSubscription();
        if (!$subscription) {
            throw new \LogicException('User does not have a subscription.');
        }

        $pdfLimit = $subscription->getPdfLimit();

        if ($pdfLimit === -1) {
            return PHP_INT_MAX;
        }

        $qb = $this->entityManager->createQueryBuilder();
        $currentMonthPdfs = $qb->select('count(pdf.id)')
            ->from(Pdf::class, 'pdf')
            ->where('pdf.user = :userId')
            ->andWhere('pdf.createdAt >= :startOfMonth')
            ->andWhere('pdf.createdAt < :startOfNextMonth')
            ->setParameter('userId', $user->getId())
            ->setParameter('startOfMonth', new \DateTime('first day of this month 00:00:00'))
            ->setParameter('startOfNextMonth', new \DateTime('first day of next month 00:00:00'))
            ->getQuery()
            ->getSingleScalarResult();

        return max(0, $pdfLimit - $currentMonthPdfs);
    }

    public function getTotalPDFsAllowed(): int
    {
        $user = $this->security->getUser();

        if (!$user || !$user->getSubscription()) {
            throw new \LogicException('The user must have an active subscription to access this feature.');
        }

        // Supposons que l'entité Subscription contient une propriété `pdfLimit`
        return $user->getSubscription()->getPdfLimit();
    }
}