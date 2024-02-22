<?php

namespace App\Scripts;

use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckSubscriptions extends Command
{
    protected static $defaultName = 'app:check-subscriptions';

    private $subscriptionRepository;
    private $userRepository;
    private $entityManager;

    public function __construct(SubscriptionRepository $subscriptionRepository, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Checks user subscriptions and updates them if necessary.');
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->userRepository->findAllWithSubscriptions();

        foreach ($users as $user) {
            // Ignorer les utilisateurs avec un abonnement gratuit (SubscriptionEndAt == null)
            if ($user->getSubscriptionEndAt() === null) {
                continue; // Passer à l'itération suivante
            }

            // Vérifie si l'abonnement de l'utilisateur est expiré
            if ($user->getSubscriptionEndAt() < new \DateTime()) {
                if ($user->isAutoRenewSubscription()) {
                    // Vérifier si l'abonnement de l'utilisateur est mensuel ou annuel
                    $isMonthly = $this->subscriptionRepository->isUserSubscriptionMonthly($user->getId());

                    // Définir l'intervalle pour ajouter à la date de fin de l'abonnement
                    $interval = new \DateInterval($isMonthly ? 'P1M' : 'P1Y');
                    // Obtenir la date de fin d'abonnement actuelle et calculer la nouvelle
                    $currentEndAt = new \DateTime();
                    // Ajouter l'intervalle à la date de fin d'abonnement actuelle
                    $currentEndAt->add($interval);
                    // Mettre à jour la date de fin d'abonnement de l'utilisateur
                    $user->setSubscriptionEndAt($currentEndAt);
                    $output->writeln(sprintf('Subscription renewed for user: %s', $user->getEmail()));
                } else {
                    // Si l'abonnement n'est pas renouvelé automatiquement, attribuer un abonnement gratuit
                    $user->setSubscription($this->subscriptionRepository->findFreeSubscription());
                    $user->setSubscriptionEndAt(null);
                    $output->writeln(sprintf('Free subscription assigned to user: %s', $user->getEmail()));
                }
                $this->entityManager->persist($user);
            }
        }

        $this->entityManager->flush();

        $output->writeln('Subscriptions checked and updated successfully.');

        return Command::SUCCESS;
    }
}
