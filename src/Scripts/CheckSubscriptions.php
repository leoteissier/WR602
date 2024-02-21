<?php

namespace App\Scripts;

use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->userRepository->findAllWithSubscriptions();

        foreach ($users as $user) {
            if ($user->getSubscriptionEndAt() < new \DateTime()) {
                $user->setSubscription($this->subscriptionRepository->findFreeSubscription());
                $user->setSubscriptionEndAt(null);
                $this->entityManager->persist($user);
            }
        }

        $this->entityManager->flush();

        $output->writeln('Subscriptions checked and updated successfully.');

        return Command::SUCCESS;
    }
}
