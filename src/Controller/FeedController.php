<?php
namespace Controller;

use Veto\HTTP\Request;
use Veto\HTTP\Response;
use Veto\MVC\AbstractController;
use Zend\Db\Adapter\Adapter;

class FeedController extends AbstractController
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

    public function feed()
    {
        if (!isset($_SESSION['user'])) {
            $response = new Response();
            $response->headers->add('Location', '/login');
            return $response;
        }

        $getFeed = $this->adapter->query('
            SELECT status.id, user.id as owner_id, status.content, user.email, MD5(user.email) AS hash FROM status
            INNER JOIN user ON status.user_id = user.id
            ORDER BY status.id DESC LIMIT 20;
        ');
        $feedItems = $getFeed->execute();

        return $this->render(
            'viewport/feed.html.twig', array(
                'feed' => $feedItems,
                'user_id' => $_SESSION['user']['id'],
                'user_email' => $_SESSION['user']['email']
            )
        );
    }

    public function post(Request $request)
    {
        $userId = $request->request->get('user');
        $content = $request->request->get('content');

        // Post as me
        $insertPost = $this->adapter->query('
            INSERT INTO status (user_id, content)
            VALUES (' . $userId . ', \'' . $content . '\');
        ');
        $insertPost->execute();


        $response = new Response();
        $response->headers->add('Location', '/feed');
        return $response;
    }

    public function delete(Request $request, $post)
    {
        $removePost = $this->adapter->query('
            DELETE FROM status
            WHERE status.id = ' . $post . ';
        ');
        $removePost->execute();

        $response = new Response();
        $response->headers->add('Location', '/feed');
        return $response;
    }
}
