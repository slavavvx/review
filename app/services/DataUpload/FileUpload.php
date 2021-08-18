<?php
namespace Services\DataUpload;

use \finfo;
use \Exception;

class FileUpload
{
    const FILE_UPLOAD_ERRORS = [

        UPLOAD_ERR_INI_SIZE   => 'The file exceeds the UPLOAD_MAX_FILESIZE directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The file exceeds the MAX_FILE_SIZE directive in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The file was uploaded only partially.',
        UPLOAD_ERR_NO_FILE    => 'The file was not uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
    ];


    /**
     * @param array $fileData
     *
     * @return array
     *
     * @throws Exception
     */
    public function initUploadedFileData(array $fileData) : array
    {
        $errorMessage = $this->checkFileUpload($fileData);

        if (!empty($errorMessage)) {
            throw new Exception($errorMessage);
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $baseName = basename($fileData['name']);
        $name = $this->findFileName($baseName);

        return [
            'base_name' => $baseName,
            'name'      => $name,
            'extension' => pathinfo($baseName, PATHINFO_EXTENSION),
            'type'      => $fileInfo->file($fileData['tmp_name']),
            'size'      => filesize($fileData['tmp_name']),
            'tmp_name'  => $fileData['tmp_name'],
        ];
    }

    /**
     * Finding filename without extension
     * @param string $baseName
     * @return string
     */
    private function findFileName(string $baseName) : string
    {
        $pos = strrpos($baseName, '.');
        $fileName = substr($baseName, 0, $pos);
        return $fileName;
    }

    /**
     * @param array $fileData
     * @return string
     */
    private function checkFileUpload(array $fileData) : string
    {
        if (empty($fileData['tmp_name']) && empty($fileData['error'])) {
            return 'File data are missed!';
        }

        ['tmp_name' => $tmpName, 'error' => $error] = $fileData;

        if ($error !== UPLOAD_ERR_OK) {
            $errorMessage = self::FILE_UPLOAD_ERRORS[$error];
        } elseif (!is_uploaded_file($tmpName)) {
            $errorMessage = 'File is not uploaded by POST!';
        }

        return $errorMessage ?? '';
    }

    /**
     * @param string $fileName
     * @return array
     */
    public function getImageSize(string $fileName) : array
    {
        return getimagesize($fileName);
    }

    /**
     * @param string $folderToMove
     * @param string $extension
     * @return string
     * @throws Exception
     */
    private function createNewFileName(string $folderToMove, string $extension) : string
    {
        do {
            $newfileName = bin2hex(random_bytes(5)) .  '.' . $extension;
            $path = $folderToMove . $newfileName;
        } while (file_exists($path));

        return $path;
    }

    /**
     * @param array $fileData
     *
     * @return string
     *
     * @throws Exception
     */
    public function saveFile(array $fileData) : string
    {
        if (empty($fileData['folder_to_move']) || empty($fileData['tmp_name']) || empty($fileData['extension'])) {
            throw new Exception('File data are missed!');
        }

        ['tmp_name' => $tmpName, 'folder_to_move' => $folderToMove, 'extension' => $extension] = $fileData;

        $path = $this->createNewFileName($folderToMove, $extension);
        $result = move_uploaded_file($tmpName, $path);

        if ($result) {
            return $path;
        }

        throw new Exception('File was not saved!');
    }
}
