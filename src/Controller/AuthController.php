<?php
namespace Controller;

use Veto\HTTP\Request;
use Veto\HTTP\Response;
use Veto\MVC\AbstractController;
use Zend\Db\Adapter\Adapter;

class AuthController extends AbstractController
{
    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    private $adapter;

    public function __construct(\Twig_Environment $twig, Adapter $adapter)
    {
        $this->twig = $twig;
        $this->adapter = $adapter;
    }

    public function login()
    {
        return $this->render(
            'viewport/login.html.twig'
        );
    }

    public function doLogin(Request $request)
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $getUser = $this->adapter->query(
            'SELECT * FROM user WHERE email = \'' . $email . '\' AND password = \'' . md5($password) . '\' LIMIT 1'
        );

        $result = $getUser->execute();

        if ($result->count() == 1) {
            $_SESSION['user'] = $result->current();
            $response = new Response();
            $response->headers->add('Location', '/');
        } else {
            $response = new Response();
            $response->headers->add('Location', '/login');
        }

        return $response;
    }

    public function signup()
    {
        return $this->render(
            'viewport/signup.html.twig'
        );
    }

    public function doSignup(Request $request)
    {
        $email = $request->request->get('email');
        $password = md5($request->request->get('password'));

        $addUser = $this->adapter->query('
            INSERT INTO user (email, password)
            VALUES (\'' . $email . '\', \'' . $password . '\');
        ');

        $addUser->execute();

        $response = new Response();
        $response->headers->add('Location', '/login');
        return $response;
    }

    public function closeAccount(Request $request, $id)
    {
        $removeUser = $this->adapter->query('
            DELETE FROM user
            WHERE id = ' . $id . ';
        ');

        $removeUser->execute();

        $removePosts = $this->adapter->query('
            DELETE FROM status
            WHERE user_id = ' . $id . ';
        ');

        $removePosts->execute();

        session_destroy();

        $response = new Response();
        $response->headers->add('Location', '/');
        return $response;
    }

    public function logout(Request $request)
    {
        session_destroy();

        $response = new Response();
        $response->headers->add('Location', '/');
        return $response;
    }
}
