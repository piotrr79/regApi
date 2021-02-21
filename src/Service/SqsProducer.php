<?php
declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
// AWS Aws\Sdk
use Aws\Sqs\SqsClient; 
use Aws\Exception\AwsException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * SqsProducer - Amazon SDK integration for S3 SQS
 * @package  Register-microservice Api
 * @author   Piotr Rybinski
 */
class SqsProducer
{

    /** @var LoggerInterface $logger */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set up Sqs Client
     * @param array $http
     * @return object
     */
    public function getSqsClient()
    {
        return new SqsClient([
            'profile' => 'private',
            'region' => $_ENV['AWS_REGION'],
            'version' => '2012-11-05'
        ]);
    }

    /**
     * Send messafge to SQS queue
     * @param $message
     * @throws /AwsException
     * @return array
     */
    public function sendMessage($message)
    {
        
        $params = [
            'DelaySeconds' => 10,
            'MessageAttributes' => [
                'Content-Type' => [
                    'DataType' => 'String',
                    'StringValue' => 'application/json'
                ],
            ],
            'MessageBody' => $message,
            'QueueUrl' => $_ENV['SQS_QUEUE_URL']
        ];

        try {
            $response = $this->getSqsClient()->sendMessage($params);
        } catch (AwsException $exception) {
            $response = $exception->getMessage();
            throw new HttpException(400, 'Message could not be sent '. json_encode(serialize($response)));
        }

        $this->logger->info('SQS send message response: '. json_encode(serialize($response)));

        return $response;
    }
}
 
 


