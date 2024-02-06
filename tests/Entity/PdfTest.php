<?php

namespace App\Tests\Entity;

use App\Entity\Pdf;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class PdfTest extends TestCase
{
    public function testGetterAndSetter()
    {
        // Création d'une instance de l'entité Pdf
        $pdf = new Pdf();

        // Définition de données de test
        $createdAt = new \DateTimeImmutable();

        $userId = new User();

        // Utilisation des setters
        $pdf->setCreatedAt($createdAt);
        $pdf->setUserId($userId);

        // Vérification des getters
        $this->assertEquals($createdAt, $pdf->getCreatedAt());
        $this->assertEquals($userId, $pdf->getUserId());
    }
}
