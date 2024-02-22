<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Subscription;
use App\Entity\Pdf;
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
                'pdfLimit' => 5,
                'price' => 0.00,
                'media' => 'free.png',
            ],
            [
                'title' => 'Abonnement Mensuel',
                'description' => 'Abonnement Premium avec accès illimité à la génération de pdf pendant un mois',
                'pdfLimit' => -1,
                'price' => 10.00,
                'media' => 'Premium.png',
            ],
            [
                'title' => 'Abonnement Annuel',
                'description' => 'Abonnement Premium avec accès illimité à la génération de pdf pendant un an',
                'pdfLimit' => -1,
                'price' => 90,
                'media' => 'Premium.png',
            ],
        ];

        $subscriptionEntities = [];

        foreach ($subscriptionsInfo as $info) {
            $subscription = new Subscription();
            $subscription->setName($info['title'])
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
            ->setPassword(password_hash('', PASSWORD_BCRYPT))
            ->setRoles(['ROLE_USER'])
            ->setSubscription($subscriptionEntities['Abonnement Gratuit'])
            ->setIsVerified(true);
        $manager->persist($user);

//        // Créer 5 PDFs pour cet utilisateur
//        for ($i = 0; $i < 5; $i++) {
//            $pdf = new Pdf();
//            $pdf->setUserId($user)
//                ->setFilename('PDF ' . $i);
//            $manager->persist($pdf);
//        }

        $manager->flush();
    }
}