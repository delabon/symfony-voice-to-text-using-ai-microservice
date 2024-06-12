<?php

namespace App\Controller;

use App\Exception\ApiServerErrorException;
use App\Exception\ApiServerOverloadedException;
use App\Exception\InvalidApiSecretException;
use App\Exception\RateLimitReachedException;
use App\Service\AudioToFileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints AS Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AudioToTextController extends AbstractController
{
    #[Route('/audio-to-text', name: 'audio_to_text_convert', methods: 'POST')]
    public function convert(
        Request $request,
        AudioToFileService $audioToFileService,
        ValidatorInterface $validator,
    ): JsonResponse
    {
        if (!$this->isCsrfTokenValid('voice_to_text_csrf', $request->getPayload()->get('_token'))) {
            return $this->json([
                'success' => false,
                'error' => 'Invalid CSRF token.'
            ], Response::HTTP_FORBIDDEN);
        }

        /** @var ?UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('audioFile');

        if (null === $uploadedFile) {
            return new JsonResponse([
                'success' => false,
                'error' => 'No file was uploaded'
            ], Response::HTTP_BAD_REQUEST);
        }

        $constraints = new Assert\File([
            'maxSize' => '2M',
            'mimeTypes' => [
                'audio/mpeg',
                'audio/mp3',
                'audio/mp4',
                'audio/x-m4a',
                'audio/wav',
                'audio/x-wav',
                'audio/wave',
                'audio/webm',
            ],
            'mimeTypesMessage' => 'Please upload a valid audio file (flac, mp3, mp4, mpeg, mpga, m4a, ogg, wav, or webm).',
        ]);

        $violations = $validator->validate($uploadedFile, $constraints);

        if (count($violations)) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            return $this->json([
                'success' => false,
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $fileExt = $uploadedFile->getClientOriginalExtension();
        $uploadDir = rtrim($this->getParameter('upload_dir'), '/');
        $filename = uniqid() . '-audio.' . $fileExt;
        $filepath =  $uploadDir . '/' . $filename;

        try {
            $uploadedFile->move($uploadDir, $filename);
            $filePart = DataPart::fromPath($filepath);
            $formData = new FormDataPart([
                'file' => $filePart,
                'model' => 'whisper-1'
            ]);
            $headers = $formData->getPreparedHeaders();
            $headers->addParameterizedHeader('Authorization', 'Bearer ' . $this->getParameter('openai_secret'));
            $text = $audioToFileService->convert($formData, $headers);
            @unlink($filepath);

            return $this->json([
                'success' => true,
                'text' => $text
            ]);
        } catch (ClientExceptionInterface $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch(RedirectionExceptionInterface $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_MULTIPLE_CHOICES);
        } catch (FileException|ApiServerErrorException|ServerExceptionInterface $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (ApiServerOverloadedException|TransportExceptionInterface $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (InvalidApiSecretException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        } catch (RateLimitReachedException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }
    }
}
