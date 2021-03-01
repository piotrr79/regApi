<?php
declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
// AWS Aws\Sdk
use Aws\S3\S3MultiRegionClient;
use Aws\Exception\AwsException;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * BucketService - Amazon SDK integration for S3 Buckets
 * @package  Register-microservice Api
 * @author   Piotr Rybinski
 */
class BucketService
{

    /** @var LoggerInterface $logger */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set up S3Client
     * @param array $http
     * @return object
     */
    public function getS3Client($http = []): S3MultiRegionClient
    {
        return new S3MultiRegionClient([
          'version'     => 'latest',
          'region'      => $_ENV['AWS_REGION'],
          'http'    => $http,
          'credentials' => [
              'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
              'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
          ],
        ]);
    }

    /**
     * File upload - put object to the bucket
     * @param $file
     * @throws /AwsException
     * @return array
     */
    public function uploadMultipartFileToBucket($file_key, $source): array
    {
        
        // get S3 Client
        $s3client = $this->getS3Client();
        $uploader = new MultipartUploader($s3client, $source, [
            'bucket' => $_ENV['AWS_BUCKET'],
            'key' => $file_key,
            'ACL' => 'public-read',
        ]);

        $this->logger->info('Source: '. json_encode(serialize($source)));
        $this->logger->info('File Key: '. json_encode(serialize($file_key)));

        try {
            $api_response = $uploader->upload();
            $response = ['key' => $file_key, 'api_response' => $api_response];
        } catch (MultipartUploadException $exception) {
            $response = $exception->getMessage();
            throw new HttpException(400, 'File cannot be processed '. json_encode(serialize($response)));
        }

        $this->logger->info('File upload AWS response: '. json_encode(serialize($response)));

        return $response;
    }
}
