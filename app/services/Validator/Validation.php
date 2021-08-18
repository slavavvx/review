<?php
namespace Services\Validator;


class Validation
{
    /**
     * Checking of username that was inputted in form
     * for matching pattern
     * 
     * @param string $userName
     * @param string $userNamePattern
     * 
     * @return int
     */
    public function isValidUserName(string $userName, string $userNamePattern) : int
    {
        $result = preg_match($userNamePattern, $userName);

        return $result;
    }            

    /**
     * Checking of length string that is received from form
     * 
     * @param string $value
     * @param integer $maxLength
     * 
     * @return bool
     */        
    public function isValidLengthData(string $value, int $maxLength) : bool
    {
        $valueLength = iconv_strlen($value, 'UTF-8');

        if ($valueLength < $maxLength) {
            return true;
        }
        return false;
    }

    /**
     * Checking of data received from select field
     * 
     * @param string $value
     * @param array $allowedItems
     * 
     * @return bool
     */
    public function isValidDataSelection(string $value, array $allowedItems) : bool
    {
        foreach ($allowedItems as $item) {

            if ($item == $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check of allowed file name
     * 
     * @param string $fileName
     * @param string $fileNamePattern
     * 
     * @return int
     */
    public function isAllowedFileName(string $fileName, string $fileNamePattern) : int
    {
        $result = preg_match($fileNamePattern, $fileName);

        return $result;
    }

    /**
     * Is executable extension contained in the filename?
     * 
     * @param string $fileName
     * @param array $blackListExt
     * 
     * @return bool
     */
    public function isExtInFileName(string $fileName, array $blackListExt) : bool
    {
        foreach ($blackListExt as $pattern) {
            $result = preg_match($pattern . 'i', $fileName);

             if ($result) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check of allowed file extension
     * 
     * @param string $fileExtention
     * @param array $allowedExtensions
     * 
     * @return bool
     */
    public function isAllowedFileExtension(string $fileExtention, array $allowedExtensions) : bool
    {
        return in_array(strtolower($fileExtention), $allowedExtensions, true);
    }

    /**
     * Check of allowed file type
     * 
     * @param string $fileType
     * @param array $allowedTypes
     * 
     * @return bool
     */
    public function isAllowedFileType(string $fileType, array $allowedTypes) : bool
    {
         return in_array($fileType, $allowedTypes, true);
    }

    /**
     * Check of allowed file size
     * 
     * @param int $fileSize
     * @param int $allowedSize
     * 
     * @return bool
     */
    public function isAllowedFileSize(int $fileSize, int $allowedSize) : bool
    {
        if ($fileSize < $allowedSize) {
            return true;
        }
        return false;
    }
}
