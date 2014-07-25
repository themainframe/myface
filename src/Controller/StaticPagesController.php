<?php
namespace Controller;

use Veto\HTTP\Response;
use Veto\MVC\AbstractController;

class StaticPagesController extends AbstractController
{
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function home()
    {
        if (isset($_SESSION['user'])) {
            $response = new Response();
            $response->headers->add('Location', '/feed');
            return $response;
        }

        return $this->render(
            'viewport/home.html.twig'
        );
    }
}
