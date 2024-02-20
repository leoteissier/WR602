<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Form\PaymentDetailsType;
use App\Form\SubscriptionChangeFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function index(): Response
    {
        $subscriptions = $this->entityManager->getRepository(Subscription::class)->findAll();
        $filteredSubscriptions = array_filter($subscriptions, function ($subscription) {
            return $subscription->getPrice() == 10 || $subscription->getPrice() == 90;
        });

        return $this->render('subscription/index.html.twig', [
            'subscriptionChangeForm' => $this->createForm(SubscriptionChangeFormType::class)->createView(),
            'subscriptions' => $filteredSubscriptions,
        ]);
    }

    public function change(Request $request): Response
    {
        $newSubscriptionId = $request->request->get('selected_subscription_id');
        if ($newSubscriptionId) {
            $newSubscription = $this->entityManager->getRepository(Subscription::class)->find($newSubscriptionId);
            if (!$newSubscription) {
                $this->addFlash('error', 'L\'abonnement demandé n\'existe pas.');
                return $this->redirectToRoute('app_subscription');
            }

            return $this->redirectToRoute('app_subscription_invoice', ['id' => $newSubscriptionId]);
        } else {
            $this->addFlash('error', 'Aucun abonnement sélectionné.');
            return $this->redirectToRoute('app_subscription');
        }
    }

    public function invoice(Request $request, int $id): Response
    {
        $subscription = $this->entityManager->getRepository(Subscription::class)->find($id);
        if (!$subscription) {
            throw $this->createNotFoundException('The subscription does not exist');
        }

        $request->getSession()->set('selected_subscription_id', $id);

        // Récupérer la date de fin d'abonnement si elle est définie
        if ($subscription->getName() == 'Abonnement Mensuel') {
            $endDate = (new \DateTime())->modify('+1 month');
        } else if ($subscription->getName() == 'Abonnement Annuel') {
            $endDate = (new \DateTime())->modify('+1 year');
        } else {
            $endDate = null;
        }

        $subscriptionDetails = [
            'name' => $subscription->getName(),
            'price' => $subscription->getPrice(),
            'endDate' => $endDate ? $endDate->format('d-m-Y') : 'Pas encore confirmé',
        ];

        return $this->render('subscription/invoice.html.twig', [
            'subscription' => $subscriptionDetails,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function paymentDetails(Request $request): Response
    {
        $form = $this->createForm(PaymentDetailsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $user = $this->getUser();
            $newSubscriptionId = $request->getSession()->get('selected_subscription_id', null);

            if ($newSubscriptionId) {
                $newSubscription = $this->entityManager->getRepository(Subscription::class)->find($newSubscriptionId);
                if ($newSubscription) {
                    $user->setSubscription($newSubscription); // Assurez-vous que cette méthode existe et est correcte
                    $duration = $newSubscription->getPrice() == 10 ? '+1 month' : '+1 year';
                    $user->setSubscriptionEndAt(new \DateTime($duration));

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    // Maintenant que tout est terminé, retirez l'ID de la session
                    $request->getSession()->remove('selected_subscription_id');
                    return $this->redirectToRoute('app_subscription_payment_confirmation');
                } else {
                    throw $this->createNotFoundException('The subscription does not exist');
                }
            } else {
                throw $this->createNotFoundException('No subscription selected');
            }
        }

        return $this->render('subscription/payment_details.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function paymentConfirmation(): Response
    {
        return $this->render('subscription/confirmation.html.twig');
    }

    public function unsubscribe(): Response
    {
        $user = $this->security->getUser();
        $subscriptionId = $this->entityManager->getRepository(Subscription::class)->findBy(['name' => 'Abonnement Gratuit']);
        $user->setSubscription($subscriptionId[0]);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->redirectToRoute('app_subscription');
    }
}
