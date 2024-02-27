<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;

class ProfilController extends AbstractController
{
    private Security $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    public function index(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $this->security->getUser();
        $errors = [
            'firstname' => '',
            'lastname' => '',
            'email' => '',
        ];

        $subscription = $user->getSubscription();
        $freeSubscription = $entityManager->getRepository(Subscription::class)->findFreeSubscription();
        $subscriptionName = $subscription ? $subscription->getName() : 'Aucun';

        if ($request->isMethod('POST')) {
            $firstname = $request->request->get('firstname');
            $lastname = $request->request->get('lastname');
            $email = $request->request->get('email');

            $isFormValid = true;

            if (!preg_match("/^[a-zA-ZÀ-ÿ '-]*$/", $firstname)) {
                $errors['firstname'] = 'Le prénom ne doit contenir que des lettres et des tirets.';
                $isFormValid = false;
            }

            if (!preg_match("/^[a-zA-ZÀ-ÿ '-]*$/", $lastname)) {
                $errors['lastname'] = 'Le nom ne doit contenir que des lettres et des tirets.';
                $isFormValid = false;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'adresse email n\'est pas valide.';
                $isFormValid = false;
            }

            if ($isFormValid) {
                $user->setFirstname($firstname);
                $user->setLastname($lastname);
                $user->setEmail($email);

                $entityManager->flush();
                $this->addFlash('success', 'Profil mis à jour avec succès.');

                return $this->redirectToRoute('app_profil');
            }
        }

        // La logique pour afficher le profil reste la même
        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'subscriptionName' => $subscriptionName,
            'freeSubscriptionId' => $freeSubscription ? $freeSubscription->getId() : null,
            'errors' => $errors,
        ]);
    }

    public function passwordEdit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $errors = [
            'current_password' => '',
            'new_password' => '',
            'new_password_confirmation' => '',
        ];

        // S'assure que la méthode de la requête est POST
        if ($request->isMethod('POST')) {
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $newPasswordConfirmation = $request->request->get('new_password_confirmation');

            // Vérifie si les champs sont remplis
            if (null === $currentPassword || null === $newPassword || null === $newPasswordConfirmation) {
                $errors[] = 'Tous les champs doivent être remplis.';
            } else {
                // Vérifie si le mot de passe actuel est correct
                if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $errors['current_password'] = 'Le mot de passe actuel est incorrect.';
                }

                // Vérifie si le nouveau mot de passe à la bonne forme
                if (strlen($newPassword) < 8 && !preg_match("#[0-9]+#", $newPassword) && !preg_match("#[a-z]+#", $newPassword) && !preg_match("#[A-Z]+#", $newPassword) && !preg_match("#\W+#", $newPassword)){
                    $errors['new_password'] = 'Le mot de passe doit contenir au moins 8 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.';
                }

                // Vérifie si le nouveau mot de passe est différent de l'ancien
                if($newPassword === $currentPassword){
                    $errors['new_password'] = 'Le nouveau mot de passe doit être différent de l\'ancien.';
                }

                // Vérifie si les nouveaux mots de passe correspondent
                if ($newPassword !== $newPasswordConfirmation) {
                    $errors['new_password_confirmation'] = 'Les nouveaux mots de passe ne correspondent pas.';
                }
            }

            if (empty($errors)) {
                // Hache le nouveau mot de passe et le définit sur l'utilisateur
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));

                try {
                    $entityManager->flush();
                    return $this->redirectToRoute('app_password_edit_confirmation');
                } catch (\Exception $e) {
                    $errors[] = 'Erreur lors de la mise à jour du mot de passe.';
                }
            }
        }

        return $this->render('profil/password_edit.html.twig', [
            'user' => $user,
            'errors' => $errors,
        ]);
    }

    public function passwordEditConfirmation(): Response
    {
        return $this->render('profil/confirmation_password_edit.html.twig');
    }
}
