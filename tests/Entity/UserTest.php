<?php

namespace App\Tests\Entity;

use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\Pdf;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetterAndSetter()
    {
        // Création d'une instance de l'entité User
        $user = new User();

        // Définition de données de test
        $email = 'test@test.com';
        $firstname = 'John';
        $lastname = 'Doe';
        $password = 'password';
        $roles = ['ROLE_USER'];
        $subcriptionEndAt = new \DateTime();
        $createdAt = new \DateTimeImmutable();
        $updateAt = new \DateTime();

        $subcriptionId = new Subscription();
        $pdfs = new Pdf();

        // Utilisation des setters
        $user->setEmail($email);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setPassword($password);
        $user->setRoles($roles);
        $user->setSubcriptionEndAt($subcriptionEndAt);
        $user->setCreatedAt($createdAt);
        $user->setUpdateAt($updateAt);

        $user->setSubcriptionId($subcriptionId);
        $user->addPdf($pdfs);

        // Vérification des getters
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($firstname, $user->getFirstname());
        $this->assertEquals($lastname, $user->getLastname());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals($roles, $user->getRoles());
        $this->assertEquals($subcriptionEndAt, $user->getSubcriptionEndAt());
        $this->assertEquals($createdAt, $user->getCreatedAt());
        $this->assertEquals($updateAt, $user->getUpdateAt());

        $this->assertEquals($subcriptionId, $user->getSubcriptionId());
        $this->assertEquals($pdfs, $user->getPdfs()[0]);

    }
}