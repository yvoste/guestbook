<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ConferenceController extends AbstractController
{

    // public function index(Environment $twig, ConferenceRepository $conferenceRepository): Response
    // {
    //     return new Response($twig->render(
    //         'conference/index.html.twig',
    //         ['conferences' => $conferenceRepository->findAll(),]
    //     ));
    // }

    private $twig;
    private $manager;

    public function __construct(EntityManagerInterface $manager, Environment $twig)
    {
        $this->manager = $manager;
        $this->twig = $twig;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {

        return new Response($this->twig->render(
            'conference/index.html.twig',
            ['conferences' => $this->manager->getRepository(Conference::class)->findAll(),]
        ));
    }


    /**
     * @route("/conference/{id}", name="conference")
     */
    public function show(Request $request, Conference $conference): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->manager->getRepository(Comment::class)->getCommentPaginator($conference, $offset);

        return new Response($this->twig->render(
            'conference/show.html.twig',
            [
                'conference' => $conference,
                'comments' => $paginator,
                'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
                'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            ],
        ));
    }
}
