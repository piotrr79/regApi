<?php
declare(strict_types=1);

namespace App\Validator;

/**
 * ResponseSetter - overwriting response to http response format
 * @package  Register-microservice Api
 * @author   Piotr Rybinski
 */
class ResponseSetter
{
    /**
     * Ser response
     * @param $code
     * @param $content
     * @return array
     */
    public function setResponse($code, $content): array
    {
        $response = ['Code' => $code, 'Message' => $content];

        return $response;
    }
}
