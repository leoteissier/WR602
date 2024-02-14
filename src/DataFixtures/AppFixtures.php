<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Subscription;
use App\Entity\Pdf;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer les abonnements
        $subscriptionsInfo = [
            [
                'title' => 'Abonnement Gratuit',
                'description' => 'Abonnement gratuit avec accès limité à 10 génération de pdf par mois',
                'pdfLimit' => 10,
                'price' => 0.00,
                'media' => 'free.png',
            ],
            [
                'title' => 'Abonnement Standard',
                'description' => 'Abonnement Standard avec accès limité à la génération de pdf',
                'pdfLimit' => 50,
                'price' => 5.00,
                'media' => 'Standard.png',
            ],
            [
                'title' => 'Abonnement Premium',
                'description' => 'Abonnement Premium avec accès limité à la génération de pdf',
                'pdfLimit' => 200,
                'price' => 10.00,
                'media' => 'Premium.png',
            ],
        ];

        $subscriptionEntities = [];

        foreach ($subscriptionsInfo as $info) {
            $subscription = new Subscription();
            $subscription->setTitle($info['title'])
                ->setDescription($info['description'])
                ->setPdfLimit($info['pdfLimit'])
                ->setPrice($info['price'])
                ->setMedia($info['media']);
            $manager->persist($subscription);
            $subscriptionEntities[$info['title']] = $subscription;
        }

        // Créer un utilisateur // Changez l'email, le firstname, le lastname et le mot de passe pour votre propre email
        $user = new User();
        $user->setEmail('')
            ->setFirstname('')
            ->setLastname('')
            ->setPassword(password_hash('', PASSWORD_BCRYPT))
            ->setRoles(['ROLE_USER'])
            ->setSubscriptionId($subscriptionEntities['Abonnement Gratuit'])
            ->setIsVerified(true);
        $manager->persist($user);

        // Créer 5 PDFs pour cet utilisateur
        for ($i = 0; $i < 5; $i++) {
            $pdf = new Pdf();
            $pdf->setUserId($user)
                ->setName('PDF ' . $i);
            $manager->persist($pdf);
        }

        $manager->flush();
    }
}