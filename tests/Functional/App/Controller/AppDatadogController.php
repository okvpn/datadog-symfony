<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\Tests\Functional\App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
}
