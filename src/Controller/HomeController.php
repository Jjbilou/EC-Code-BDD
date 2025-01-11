<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\BookRead;
use App\Form\RegistrationFormType;
use App\Form\AddReadBookForm;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookReadRepository;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
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
    public function __construct(BookReadRepository $bookReadRepository, BookRepository $bookRepository, CategoryRepository $categoryRepository)
    {
        $this->bookReadRepository = $bookReadRepository;
        $this->bookRepository = $bookRepository;
        $this->categoryRepository = $categoryRepository;
    }

    #[Route('/', name: 'app.home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $bookRead = new BookRead();
        if (!$this->getUser()) {
            return $this->redirectToRoute('auth.login'); // Redirige vers la page de login si non connecté
        }
        $user = $this->getUser();
        $inProgressBooks = $this->bookReadRepository->findByUser($user, false); // Tous les livres en cours de lecture par le user connecté
        $booksRead = $this->bookReadRepository->findByUser($user, true); // Tous les livres lu par le user connecté
        $allBooksRead = $this->bookReadRepository->findAllByUser($user); // Tous les livres du user connecté
        $allBooks = $this->bookRepository->findAll(); // Tous les livres
        $allAllBooksRead = $this->bookReadRepository->findAll(); // Tous les livres lu ou en cours de lecture par qui que ce soit
        $allCategories = $this->categoryRepository->findAll(); // Toutes les catégories

        $form = $this->createForm(AddReadBookForm::class, $bookRead, [
            'books' => $allBooks
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On récupère la data de is_read, du user et de la date et on les set pour pouvoir les envoyé en BDD
            $is_read = $form->get('is_read')->getData();
            $bookRead->setRead($is_read);
            $bookRead->setUser($this->getUser());
            $bookRead->setCreatedAt(new \DateTime());
            $bookRead->setUpdatedAt(new \DateTime());

            // Envoie en BDD
            $entityManager->persist($bookRead);
            $entityManager->flush();

            return $this->redirectToRoute('app.home');
        } 

        // Render the 'hello.html.twig' template
        return $this->render('pages/home.html.twig', [
            'form' => $form,
            'allAllBooksRead' => $allAllBooksRead,
            'allCategories' => $allCategories,
            'allBooksRead' => $allBooksRead,
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
        // Création d'un nouveau user via la form d'inscription
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app.home');
        }
        // Render the 'hello.html.twig' template
        return $this->render('auth/register.html.twig', [
            'form' => $form, // Pass data to the view
        ]);
    }

    #[Route('/explorer', name: 'auth.explorer')]
    public function explorer(Request $request, EntityManagerInterface $entityManager): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('auth.login'); // Redirige vers la page de login si non connecté
        }

        $allAllBooksRead = $this->bookReadRepository->findAll(); // Tous les livres lu ou en cours de lecture par qui que ce soit
        $allBooks = $this->bookRepository->findAll(); // Tous les livres

        return $this->render('pages/explore.html.twig', [
            'allBooks' => $allBooks,
            'allAllBooksRead' => $allAllBooksRead,
        ]);
    }
}
