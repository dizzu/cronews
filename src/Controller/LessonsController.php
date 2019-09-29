<?php

namespace App\Controller;

use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LessonsController extends Controller
{
    public function indexAction(Connection $connection)
    {
        $lessons = $connection->fetchAll('SELECT * FROM lessons ORDER BY date_created DESC');
        $pageVars['lessons'] = $lessons;
        return $this->render('lessons/list.html.twig', $pageVars);
    }

    public function addAction(Connection $connection)
    {
        if ($_POST) {
            $connection->insert('lessons', $_POST);
            $id = $connection->lastInsertId();
            return $this->render('lessons/success.html.twig', ['id' => $id]);
        }

        return $this->render('lessons/add.html.twig');
    }

    public function successAction()
    {
        return $this->render('lessons/success.html.twig');
    }

    public function lessonAction(Connection $connection, $lesson_id, $step = 1)
    {
        $statement = $connection->executeQuery('SELECT * FROM lessons WHERE id = ?', array($lesson_id));
        $lesson = $statement->fetch();
        $lesson['text'] = nl2br($lesson['text'], false);
        $pageVars['lesson'] = $lesson;
        switch ($step) {
            case 1:
                if (count($_POST)) {
                    $connection->insert('responses', [
                        'lesson_id' => $lesson['id'],
                        'user_id' => 1,
                        'step' => 1,
                        'text' => $_POST['highlighted_text']
                    ]);
                    $pageVars['highlighted_text'] = $_POST['highlighted_text'];
                    return $this->render('lessons/step1_verification.html.twig', $pageVars);
                }
                return $this->render('lessons/step1.html.twig', $pageVars);
            break;

            case 2:
                if (count($_POST)) {
                    $connection->insert('responses', [
                        'lesson_id' => $lesson['id'],
                        'user_id' => 1,
                        'step' => 2,
                        'text' => $_POST['text']
                    ]);
                    $pageVars['text'] = $_POST['text'];
                    $response = new Response(
                    $this->renderView('lessons/step2_verification.html.twig', $pageVars), 200);
                    $response->headers->set('X-XSS-Protection', 0);

                    return $response;
                }
                return $this->render('lessons/step2.html.twig', $pageVars);
            break;

            case '3':
                if (count($_POST)) {
                    $connection->insert('responses', [
                        'lesson_id' => $lesson['id'],
                        'user_id' => 1,
                        'step' => 3,
                        'text' => $_POST['text']
                    ]);
                    $pageVars['text'] = $_POST['text'];
                    $response = new Response(
                    $this->renderView('lessons/step3_verification.html.twig', $pageVars), 200);
                    $response->headers->set('X-XSS-Protection', 0);

                    return $response;
                }
                return $this->render('lessons/step3.html.twig', $pageVars);
            break;
        }
    }

    public function responsesAction(Connection $connection, $lesson_id)
    {
        $statement = $connection->executeQuery('SELECT * FROM lessons WHERE id = ?', array($lesson_id));
        $lesson = $statement->fetch();
        $pageVars['lesson'] = $lesson;

        $statement = $connection->executeQuery('SELECT * FROM responses WHERE lesson_id = ? ORDER BY step ASC, date_created ASC ', array($lesson_id));
        $responses = $statement->fetchAll();

        $users = $connection->fetchAll('SELECT * FROM users');
        foreach ($users as $user) {
            $usersById[$user['id']] = $user;
        }

        $pageVars['responses'] = $responses;
        $pageVars['users'] = $usersById;

        return $this->render('lessons/responses.html.twig', $pageVars);


    }
}