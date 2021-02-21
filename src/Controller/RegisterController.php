<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Uid\Uuid;
use Psr\Log\LoggerInterface;
use App\Validator\RequestValidator;
use App\Service\BucketService;
use App\Service\FileUploadService;
use App\Service\SqsProducer;

/**
* RegisterController
* @package  Register-microservice Api
* @author   Piotr Rybinski
*
* @Route("/api")
*/
class RegisterController extends AbstractController
{
    /** @var LoggerInterface */
    private $logger;
    /** @var RequestValidator */
    private $requestValidator;
    /** @var BucketService */
    private $bucketService;
    /** @var FileUploadService */
    private $fileUploadService;
    /** @var SqsProducer */
    private $sqsProducerService;

    /**
     * RegisterController constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->requestValidator = new RequestValidator($logger);
        $this->bucketService = new BucketService($logger);
        $this->fileUploadService = new FileUploadService($logger);
        $this->sqsProducerService = new SqsProducer($logger);
    }

    /**
     * Register user api
     * @param Request $request
     * @return string
     * @Route("/register", name="register_user", methods={"POST"})
     */
    public function addUser(Request $request): JsonResponse
    {
        /** @ToDo - provide authorization. WSO2 or any other OAuth SaaS, or even simple OAuth local service (Symfony Bundle) */

        $data = $request->request->all();
        $name = $data['Name'];
        $email = $data['Email'];
        $file = $request->files->get('filename');

        $this->validateRequest($file, $name, $email);

        $uploadedFile = $this->fileUploadService->moveFile($file);
        $uuid = Uuid::v4();
        $key_name = $uuid.'/'.$uploadedFile['name'];
        $awsFile = $this->bucketService->uploadMultipartFileToBucket($key_name, $uploadedFile['path'].'/'.$uploadedFile['name']);

        /** @ToDo - Verify if $email exists in centralized system (where api send user request), with Api call to centralized system */
        /** @ToDo - Verify if $email exists in SQS queue system where api send messages  */
        /** @ToDo - If email already exist delete file from AWS and display message to user that email is in use */

        $awsUrl = 'https://'.$_ENV['AWS_BUCKET'].'.s3.'.$_ENV['AWS_REGION'].'amazonaws.com/'.$awsFile['key'];
        $message = $this->createMessage($name, $email, $awsUrl, $_ENV['GAME_CENTER']);

        $result = $this->sqsProducerService->sendMessage($message);
    
        $response = new JsonResponse($result, 200);
        $this->logger->error('Api OK response: '. json_encode($response));
        return $response;
    }

    /**
     * Validate request
     * @param $file, $name, $email
     * @return void
     */
    private function validateRequest(UploadedFile $file, string $name, string $email): void
    {
          $this->requestValidator->validateFile($file);
          $this->requestValidator->validateName($name);
          $this->requestValidator->validateEmail($email);
    }

    /**
     * Create Message
     * @param $file, $name, $email
     * @return string
     */
    private function createMessage($name, $email, $s3File, $gamecenter): string
    {
        $result = [
            'name' => $name,
            'email' => $email,
            'avatar' => $s3File,
            'gamcenter' => $gamecenter,
        ];

        return json_encode($result);
    }
}
