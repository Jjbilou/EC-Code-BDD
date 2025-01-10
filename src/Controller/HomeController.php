<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\BookRead;
use App\Form\RegistrationFormType;
use App\Form\AddReadBookForm;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookReadRepository;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    private BookReadRepository $readBookRepository;

    // Inject the repository via the constructor
    public function __construct(BookReadRepository $bookReadRepository, BookRepository $bookRepository)
    {
        $this->bookReadRepository = $bookReadRepository;
        $this->bookRepository = $bookRepository;
    }

    #[Route('/', name: 'app.home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $bookRead = new BookRead();
        $user = $this->getUser();
        $inProgressBooks = $this->bookReadRepository->findByUser($user, false);
        $booksRead = $this->bookReadRepository->findByUser($user, true);
        $allBooks = $this->bookRepository->findAll();

        $form = $this->createForm(AddReadBookForm::class, $bookRead, [
            'books' => $allBooks
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $is_read = $form->get('is_read')->getData();
            $bookRead->setRead($is_read);
            $bookRead->setUser($this->getUser());
            $bookRead->setCreatedAt(new \DateTime());
            $bookRead->setUpdatedAt(new \DateTime());

            $entityManager->persist($bookRead);
            $entityManager->flush();

            return $this->redirectToRoute('app.home');
        } 

        // Render the 'hello.html.twig' template
        return $this->render('pages/home.html.twig', [
            'form' => $form,
            'booksRead' => $booksRead,
            'inProgressBooks' => $inProgressBooks,
            'allBooks' => $allBooks,
            'name'      => 'Accueil', // Pass data to the view
        ]);
    }


    #[Route('/login', name: 'auth.login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app.home');
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route('/logout', name: 'auth.logout')]
    public function logout(): void {}

    #[Route('/register', name: 'auth.register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app.home');
        }
        // Render the 'hello.html.twig' template
        return $this->render('auth/register.html.twig', [
            'form' => $form, // Pass data to the view
        ]);
    }
}
