<?php
declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
* RequestValidator - validate request
 * @package  Register-microservice Api* @author   Piotr Rybinski
*/
class RequestValidator
{
    /**
     * Validate file
     * @param $file
     * @return void
     */
    public function validateFile(UploadedFile $file): void
    {
        try {
            $size = $file->getSize();
            $type = $file->getClientMimeType();

            $validator = Validation::createValidator();
            $violations = $validator->validate($file, new File(
                [
                    'maxSize' => '2M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/jpg',
                        'image/png'
                    ],
                    'mimeTypesMessage' => 'File must be an image',
                ]
            ));
            
            if (0 !== count($violations)) {
                foreach ($violations as $violation) {
                    throw new HttpException(400, 'File: '.$violation->getMessage());
                }
            }

        } catch (FileException $exception) {
            throw new HttpException(400, 'File is not valid');
        }
    }

    /**
     * Validate email
     * @param $email
     * @return void
     */
    public function validateEmail(string $email): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($email, [new NotBlank(), new Length(['min' => 6]), new Email()]);
        
        if (0 !== count($violations)) {
            foreach ($violations as $violation) {
                throw new HttpException(400, 'Email: '.$violation->getMessage());
            }
        }
    }

    /**
     * Validate name
     * @param $name
     * @return void
     */
    public function validateName(string $name): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($name, [new NotBlank(), new Length(['min' => 3])]);
        
        if (0 !== count($violations)) {
            foreach ($violations as $violation) {
                throw new HttpException(400, 'Name: '.$violation->getMessage());
            }
        }
    }
}
