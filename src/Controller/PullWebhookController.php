<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class PullWebhookController extends AbstractController
{
    #[Route('/pull', name: 'app_pull_webhook')]
    public function index(KernelInterface $kernel): JsonResponse
    {
        $projectPath = $kernel->getProjectDir();
        $flagDirectory = $projectPath . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'flags';
        @mkdir($flagDirectory, 0644, true);
        $fileFlag = $flagDirectory . DIRECTORY_SEPARATOR . 'pull';
        @unlink($fileFlag);
        file_put_contents($fileFlag, time());

        return $this->json([
            'message' => 'OK'
        ]);
    }
}
