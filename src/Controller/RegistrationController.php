<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminregisterType;
use App\Form\RegistrationFormType;
use App\Form\UpdateType;
use App\Repository\UserRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class RegistrationController extends AbstractController
{

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, MailerInterface $mailer,UserRepository $userRepository): Response
    {
        // Create a new User instance
        $user = new User();

        // Create the registration form with the User entity
        $form = $this->createForm(RegistrationFormType::class, $user);

        // Handle the form submission
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            // Check if the email already exists
            $existingUser = $userRepository->findOneBy(['email' => $email]);

            if ($existingUser) {
                // Add a form error for the email field
                $form->get('email')->addError(new FormError('Email already exists.'));

                // Render the registration form with error message
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
            // Get the password and confirmPassword from the form
            $password = $form->get('password')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            // Check if passwords match
            if ($password !== $confirmPassword) {
                // Add a form error for the confirmPassword field
                $form->get('confirmPassword')->addError(new FormError('Passwords do not match.'));

                // Render the registration form with error message
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Encode the plain password and set other user data
            $user->setPassword(
                $passwordEncoder->encodePassword($user, $password)
            );
            $user->setRoles(['ROLE_USER']);
            $user->setActivated(true);

            // Save the user to the database
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // Send a confirmation email
            $email = (new Email())
                ->from('legaltechrec@gmail.com')
                ->to($user->getEmail())
                ->subject('Welcome to AlphaFit')
                ->html($this->renderView('mailer/index.html.twig'));

            $transport = new GmailSmtpTransport('legaltechrec@gmail.com', 'yxzc rivk amcc neif');
            $mailer = new Mailer($transport);
            $mailer->send($email);

            // Redirect to a success page or login
            return $this->redirectToRoute('app_login');
        } else {
            // Display errors for debugging
            dump($form->getErrors(true, false));
        }

        // Render the registration form
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/user/update', name: 'user_update', methods: ['GET', 'POST'])]
    public function updateUser(Request $request, EntityManagerInterface $entityManager, Security $security, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        // Retrieve the authenticated user
        $user = $security->getUser();

        // Ensure the user is authenticated
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User is not authenticated.');
        }

        $form = $this->createForm(UpdateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Optionally, you can check if the password is changed and encode it
            if ($form->get('password')->getData()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword($user, $form->get('password')->getData())
                );
            }

            // Persist and flush the changes
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirect or do any other actions upon successful update
            return $this->redirectToRoute('user_update', ['id' => $user->getId()]);
        }
        $imagePath = $user->getImage();


        $imageName = basename($imagePath);


        return $this->render('user/profile.html.twig', [

            'user' => $user,
            'imageName' => $imageName,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/upload-image', name: 'upload_image', methods: ['POST'])]
    public function uploadImage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $uploadedFile = $request->files->get('profile_picture');

        if ($uploadedFile) {
            $originalFilename = $uploadedFile->getClientOriginalName();

            // Concaténer le chemin complet avec le nom de fichier
            $imagePath = 'C:\Users\ikram\Pictures\\' . $originalFilename;

            // Mettre à jour le chemin de l'image dans la base de données
            $user->setImage($imagePath);
            $entityManager->persist($user);
            $entityManager->flush();

            // Déplacer le fichier téléchargé vers un emplacement spécifique sur le serveur
            try {
                $uploadedFile->move(
                    $this->getParameter('images_directory'), // Répertoire de destination
                    $originalFilename
                );
            } catch (FileException $e) {
                // handle exception if the file cannot be moved
            }
        }

        return $this->redirectToRoute('user_update');
    }

    #[Route('/forgotpass', name: 'forgotpass')]
    public function forgotpass(Security $security): Response
    {
        return $this->render('registration/forgotpassword.html.twig');
    }
    #[Route('/forgotpassadmin', name: 'forgotpassadmin')]
    public function forgotpassadmin(Security $security): Response
    {
        return $this->render('registration/forgetpasswordadmin.html.twig');
    }
    /**
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    #[Route('/forgotpassemail', name: 'forgotpassemail')]
    public function forgotpassemail(Security $security, Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $email = $request->request->get('email');

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user) {
            // Generate a new password
            $newPassword = bin2hex(random_bytes(8)); // Change the length as needed

            // Encode and set the new password for the user
            $encodedPassword = $passwordEncoder->encodePassword($user, $newPassword);
            $user->setPassword($encodedPassword);

            // Save the updated user entity to the database
            $this->getDoctrine()->getManager()->flush();

            // Use Symfony's Mailer component to send the email
            $email = (new Email())
                ->from('legaltechrec@gmail.com')
                ->to($email)
                ->subject('Password Reset')
                ->html($this->renderView('email/welcome.html.twig', ['newPassword' => $newPassword]));

            $transport = new GmailSmtpTransport('legaltechrec@gmail.com', 'yxzc rivk amcc neif');
            $mailer = new Mailer($transport);
            $mailer->send($email);

            // Check user role and redirect accordingly
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                return $this->redirectToRoute('app_adminlogin');
            } else {
                return $this->redirectToRoute('app_login');
            }
        }else {
            // User not found, return a response with an error message
            $errorMessage = 'Email not found. Please check your email address.';
            return $this->render('registration/forgotpassword.html.twig', ['error' => $errorMessage]);
            // Alternatively, you can use JsonResponse:
            // return new JsonResponse(['error' => $errorMessage], Response::HTTP_NOT_FOUND);
        }

        // Handle the case where the user is not found
        // You might want to add a flash message or error handling here


    }




//    #[Route('/user/profile', name: 'user_profile')]
//    public function userProfile(Security $security): Response
//    {
//        // Retrieve the authenticated user
//        $user = $security->getUser();
//
//        // Ensure the user is authenticated
//        if (!$user instanceof User) {
//            throw $this->createAccessDeniedException('User is not authenticated.');
//        }
//
//        return $this->render('user/profile.html.twig', [
//            'user' => $user,
//        ]);
//    }
}
