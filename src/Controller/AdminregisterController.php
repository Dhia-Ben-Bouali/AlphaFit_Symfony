<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminregisterType;
use App\Form\AdminUpdateType;
use App\Form\RegistrationFormType;
use App\Form\UpdateType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class AdminregisterController extends AbstractController
{ private $serializer;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder,SerializerInterface $serializer)
    {
        $this->passwordEncoder = $passwordEncoder;



        $this->serializer = $serializer;
    }


    #[Route('/get-user-details/{id}', name: 'get_user_details')]
    public function getUserDetails(Request $request, $id): JsonResponse
    {
        // You should implement your logic to fetch user details based on the $id parameter
        // Replace this with your actual logic to retrieve user details from the database or any other source
        $userDetails = $this->getDoctrine()->getRepository(User::class)->find($id);

        // Check if user details were found
        if (!$userDetails) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        // Return user details in JSON format
        return new JsonResponse([
            'nom' => $userDetails->getNom(),
            'prenom' => $userDetails->getPrenom(),
            'adresse' => $userDetails->getAdresse(),
            'email' => $userDetails->getEmail(),
            'password'=>$userDetails->getPassword(),
            'salaire'=>$userDetails->getSalaire()
            // Add more fields as needed
        ]);
    }
    #[Route('/registeradmin', name: 'app_registeradmin')]
    public function registeradmin(Request $request): Response
    {
        $user = new User();
        $registerForm = $this->createForm(AdminregisterType::class, $user);
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            // Encodez le mot de passe si vous utilisez l'encoder Symfony
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $registerForm->get('password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_list'); // Remplacez par la route appropriée
        }

        return $this->render('user/list.html.twig', [

            'adminreg' => $registerForm->createView(),
        ]);
    }

    #[Route('/users', name: 'useradd_list')]
    public function AddNewUser(Request $request,   PaginatorInterface $paginator ,UserRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        // Get the search query
        $searchQuery = $request->query->get('q');

        // Get the selected role filter
        $selectedRole = $request->query->get('role');

        // Fetch users based on the search query and selected role
        $users = $userRepository->getUsersByRoleAndSearch($selectedRole, $searchQuery);

        // Paginate the results manually
        $page = $request->query->getInt('page', 1);
        $pageSize = 5;
        $totalUsers = count($users);
        $totalPages = ceil($totalUsers / $pageSize);
        $offset = ($page - 1) * $pageSize;
        $paginatedUsers = array_slice($users, $offset, $pageSize);

        // Create a new instance of the User entity
        $user = new User();

        $form = $this->createForm(AdminregisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
            // Set and encode the password
            if ($form->get('password')->getData()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword($user, $form->get('password')->getData())
                );
            }

// Get the selected roles directly
            $selectedRoles = $form->get('roles')->getData();

// Ensure the selected roles is an array
            if (!is_array($selectedRoles)) {
                // Handle the case where the roles are not an array
                throw new \InvalidArgumentException('Invalid roles data.');
            }

// Set the roles with only the first element
            $user->setRoles([$selectedRoles[0]]);
            $user->setActivated(true);
            // Persist and flush the changes
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirect or do any other actions upon successful update
            $this->addFlash('success', 'User added successfully.');

            return $this->redirectToRoute('useradd_list');
        }


        return $this->render('user/list.html.twig', [
            'users' => $paginatedUsers,
            'user' => $user,
            'adminreg' => $form->createView(),
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'searchQuery' => $searchQuery,
            'selectedRole' => $selectedRole,
        ]);
    }




    #[Route('/user/delete/{id}', name: 'user_delete')]
    public function deleteUser(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            // Handle the case where the user with the given ID is not found
            throw $this->createNotFoundException('User not found');
        }

        // Ensure the user is authenticated or add additional checks if needed
        // (e.g., check if the user has the right to delete users)
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'User deleted successfully.');

        return $this->redirectToRoute('useradd_list');
    }

    #[Route('/admin/update/{id}', name: 'admin_update', methods: ['GET', 'POST'])]
    public function updateAdmin(int $id, Request $request, EntityManagerInterface $entityManager, Security $security, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Manually set user fields from the request
        $user->setNom($request->get('adminreg')['nom']);
        $user->setPrenom($request->get('adminreg')['prenom']);
        $user->setEmail($request->get('adminreg')['email']);
        $user->setAdresse($request->get('adminreg')['adresse']);
        // Check if 'salaire' is set and not empty before updating
        $salaire = $request->get('adminreg')['salaire'];
        if ($salaire !== null && $salaire !== '') {
            $user->setSalaire($salaire);
        }
        // Handle password separately to avoid encoding issues
        $plainPassword = $request->get('adminreg')['password'];
        if ($plainPassword !== null && $plainPassword !== '') {
            $user->setPassword($passwordEncoder->encodePassword($user, $plainPassword));
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'User updated successfully.');

        return $this->redirectToRoute('useradd_list');
    }


    #[Route('/admin/updateself', name: 'updateself')]
    public function updateadminself(Request $request, EntityManagerInterface $entityManager, Security $security, UserPasswordEncoderInterface $passwordEncoder): Response

    {  // Retrieve the authenticated user
        $user = $security->getUser();

        // Ensure the user is authenticated
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User is not authenticated.');
        }

        $form1 = $this->createForm(AdminUpdateType::class, $user);
        $form1->handleRequest($request);

        if ($form1->isSubmitted() && $form1->isValid()) {
            // Optionally, you can check if the password is changed and encode it
            if ($form1->get('password')->getData()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword($user, $form1->get('password')->getData())
                );
            }

            // Persist and flush the changes
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirect or do any other actions upon successful update
            return $this->redirectToRoute('user_update', ['id' => $user->getId()]);
        }

        return $this->render('adminregister/profileback.html.twig', [
            'user' => $user,
            'form' => $form1->createView(),
        ]);
    }

}
