<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Functional\App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Okvpn\Bundle\DatadogBundle\Tests\Functional\App\Entity\DatadogUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait AppDatadogControllerTrait
{
    public function index()
    {
        return new Response('OK');
    }

    public function exception()
    {
        // Exception
    }

    public function entity()
    {
        $user = (new DatadogUser())
            ->setUsername('foo');

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new JsonResponse(['status' => true]);
    }
}

if (class_exists(AbstractController::class)) {
    class AppDatadogController extends AbstractController
    {
        use AppDatadogControllerTrait;

        public function getDoctrine(): ManagerRegistry
        {
            return $this->container->get('doctrine');
        }
    }
} else {
    class AppDatadogController extends Controller
    {
        use AppDatadogControllerTrait;
    }
}
