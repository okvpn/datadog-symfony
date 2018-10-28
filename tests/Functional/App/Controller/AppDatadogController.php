<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Functional\App\Controller;

use Okvpn\Bundle\DatadogBundle\Tests\Functional\App\Entity\DatadogUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AppDatadogController extends Controller
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
