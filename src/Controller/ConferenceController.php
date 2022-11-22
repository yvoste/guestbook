<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    /**
     * @Route("/hello/{name}", name="homepage")
     */
    public function index(Request $request, string $name = ''): Response
    {
        //dump($request);
        return $this->render('conference/index.html.twig', [
            'controller_name' => 'ConferenceController',
            'vari' => $name
        ]);
        /*return new Response(
            <<<EOF
                <html>
                    <body>
                        <img src="images/under-construction.gif" />
                    </body>
                </html>
            EOF
        );*/
    }
}
