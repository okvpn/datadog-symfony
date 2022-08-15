<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Functional\App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Okvpn\Bundle\DatadogBundle\Tests\Functional\App\Entity\DatadogUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AppDatadogController extends AbstractController
{
    public function __construct(protected EntityManagerInterface $em)
    {}

    public function index(): Response
    {
        return new Response('OK');
    }

    public function exception(): void
    {
        // Exception
    }

    public function entity(): JsonResponse
    {
        $user = (new DatadogUser())->setUsername('foo');

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(['status' => true]);
    }
}
