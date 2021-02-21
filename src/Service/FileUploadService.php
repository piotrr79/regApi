<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Request;

 /**
  * FileUploadService - storing uploaded file on disc in temp folder
* @package  Register-microservice Api
* @author   Piotr Rybinski
  */
class FileUploadService
{

    /** @var Filesystem $fileSystem */
    private $fileSystem;
    /** @var LoggerInterface $logger */
    private $logger;

    /**
     * FilesRetrieveService constructor.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->fileSystem = new Filesystem();
    }

    /**
     * Get file from submitted form, rename and move to temp folder on local disc
     * @param $request
     * @return array
     */
    public function moveFile(UploadedFile $file)
    {
        $name = $file->getClientOriginalName();
        $size = $file->getSize();
        $type = $file->getClientMimeType();

        // set up temp dir to move file
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
        // Remove whitespaces and add timestamp as prefix
        $original_name = preg_replace('/\s+/', '_', $name);
        // Generate sanitized name (remove all url restricted non ASCII character)
        $name = $this->sanitizeFileName($original_name);

        // upload file
        try {
            $file->move($tempDir, $name);
        } catch (FileException $exception) {
            /* $this->logger->info('***** File move() exception: ' . json_encode($exception->getMessage())); */
            throw new FileException();
        }

        return ['path' => $tempDir, 'name' => $name];
    }

    /**
     * sanitizeFileName - remove illegal characters from file name, makes filename url friendly
     * @param $original_name
     * @return string
     */
    private function sanitizeFileName($filename)
    {
        $rawname = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $strip = ["~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â€”", "â€“", ",", "<", ".", ">", "/", "?"];

                   // remove illegal characters
        $clean = trim(str_replace($strip, "", strip_tags($rawname)));

        return $clean.'.'.$extension;
    }
}
