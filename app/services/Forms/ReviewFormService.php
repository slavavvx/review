<?php
namespace Services\Forms;

use \Exception;
use Services\Validator\Validation;
use Services\DataUpload\FileUpload;


class ReviewFormService
{
    const NAME_FIELD = 'name';
    const THEME_FIELD = 'theme';
    const TEXT_FIELD = 'text';
    const IMAGE_FIELD = 'image';
    const ERROR = 'error';

    const UPLOAD_FAILED = 'Изображение не было загружено. Произошла ошибка!';
    const INVALID_FILE_NAME = 'Недопустимое имя файла!';

    const EMPTY_FIELD = 'Поле обязательное для заполнения!';
    const INVALID_NAME = 'Недопустимое имя!';
    const INVALID_NAME_LENGTH = 'Имя не может быть больше 40 символов!';
    const INVALID_TEXT_LENGTH = 'Сообщение не может быть больше 650 символов!';
    const INVALID_THEME = 'Недопустимая тема!';
    const MAX_LENGTH_NAME = 40;
    const MAX_LENGTH_TEXT = 650;

    const THEME_FIELD_ALLOWED_ITEMS = ['thanks', 'proposal', 'complaint'];

    const UPLOAD_DIR = '../public/uploaded_images/';
    private const USERNAME_PATTERN = '/^(([A-Za-z]{1,2}`?[A-Za-z]{3,14})|([А-Яа-я]{3,14}))(((\s-\s)|\s|-){1}[A-Za-zА-Яа-я]+){0,2}$/u';
    private const FILENAME_PATTERN = '/^([A-Za-z0-9_\-]{1,30}|[А-Яа-я0-9_\-]{1,30})\.[A-Za-z]{1,5}$/u';
    private const ALLOWED_SIZE = 2 * 1024 * 1024;
    private const ALLOWED_EXTENSIONS = ['png', 'jpeg', 'jpg'];
    private const ALLOWED_TYPE = ['image/png', 'image/jpeg'];

    private const BLACK_LIST_EXTENSIONS = [

        '/\.ph(p([34578s]|\-s)?|t|tml)/',
        '/\.asp/',
        '/\.asa/',
        '/\.ascx/',
        '/\.asax/',
        '/\.cer/',
        '/\.swf/',
        '/\.xap/',
    ];

    const INVALID_FILE_SIZE = 'Размер изображения может быть не более 2 Мб!';
    const INVALID_FILE_EXTENSION = 'Изображение должно иметь формат jpeg, jpg, png!';
    const INVALID_FILE_TYPE = 'Недопустимый тип изображения!';

    /**
     * @var Validation
     */
    private $validation;

    /**
     * @var FileUpload
     */
    private $fileUpload;
	
	
	/**
	 * ReviewFormService constructor.
	 * @param Validation $validation
	 * @param FileUpload $fileUpload
	 */
    public function __construct(

        Validation $validation,
        FileUpload $fileUpload
    )
    {
        $this->validation = $validation;
        $this->fileUpload = $fileUpload;
    }
	
	/**
	 * @param array $formData
	 * @return array
	 */
    public function prepareFormData(array $formData) : array
    {
        return [
            'name'  => $formData['name'] ?? '',
            'theme' => $formData['theme'] ?? '',
            'text'  => $formData['text'] ?? '',
        ];
    }
	
	/**
	 * @param array $imageData
	 * @return array
	 * @throws Exception
	 */
    public function prepareImageData(array $imageData) : array
    {
        $folderToMove = ROOT_PATH . self::UPLOAD_DIR;
        $preparedImageData = $this->fileUpload->initUploadedFileData($imageData);

        $preparedImageData['folder_to_move'] = $folderToMove;

        return $preparedImageData;
    }

    /**
    * Проверяются данные из всех обязательных полей формы
    * и если они не валидны возвращаются сообщения для всех полей
    * 
    * @param array $formData
    * 
    * @return array
    */
    public function validateReviewFormData(array $formData) : array
    {
        $validationMessages = [];

        if (empty($formData[self::NAME_FIELD])) {
            $validationMessages[self::NAME_FIELD] = self::EMPTY_FIELD;
        } elseif (!$this->validation->isValidUserName($formData[self::NAME_FIELD], self::USERNAME_PATTERN)) {
            $validationMessages[self::NAME_FIELD] = self::INVALID_NAME;
        } elseif (!$this->validation->isValidLengthData($formData[self::NAME_FIELD], self::MAX_LENGTH_NAME)) {
            $validationMessages[self::NAME_FIELD] = self::INVALID_NAME_LENGTH;
        }
        
        if (empty($formData[self::THEME_FIELD])) {
            $validationMessages[self::THEME_FIELD] = self::EMPTY_FIELD;
        } elseif (!$this->validation->isValidDataSelection($formData[self::THEME_FIELD], self::THEME_FIELD_ALLOWED_ITEMS)) {
            $validationMessages[self::THEME_FIELD] = self::INVALID_THEME;
        }
        
        if (empty($formData[self::TEXT_FIELD])) {
            $validationMessages[self::TEXT_FIELD] = self::EMPTY_FIELD;
        } elseif (!$this->validation->isValidLengthData($formData[self::TEXT_FIELD], self::MAX_LENGTH_TEXT)) {
            $validationMessages[self::TEXT_FIELD] = self::INVALID_TEXT_LENGTH;
        }

        if (!empty($formData[self::IMAGE_FIELD])) {
            $validationMessages += $this->validateImageData($formData[self::IMAGE_FIELD]);
        }

        return $validationMessages;
    }

    /**
     * @param array $imageData
     * 
     * @return array
     */
    private function validateImageData(array $imageData) : array
    {   
        $validationMessages = [];

        if (!empty($imageData[self::ERROR])) {
            $validationMessages[self::IMAGE_FIELD] = self::UPLOAD_FAILED;
        } elseif (!$this->validation->isAllowedFileSize($imageData['size'], self::ALLOWED_SIZE)) {
            $validationMessages[self::IMAGE_FIELD] = self::INVALID_FILE_SIZE;
        } elseif (!$this->validation->isAllowedFileName($imageData['base_name'], self::FILENAME_PATTERN)) {
            $validationMessages[self::IMAGE_FIELD] = self::INVALID_FILE_NAME;
        } elseif ($this->validation->isExtInFileName($imageData['name'], self::BLACK_LIST_EXTENSIONS)) {
            $validationMessages[self::IMAGE_FIELD] = self::INVALID_FILE_NAME;
        } elseif (!$this->validation->isAllowedFileExtension($imageData['extension'], self::ALLOWED_EXTENSIONS)) {
            $validationMessages[self::IMAGE_FIELD] = self::INVALID_FILE_EXTENSION;
        } elseif (!$this->validation->isAllowedFileType($imageData['type'], self::ALLOWED_TYPE)) {
            $validationMessages[self::IMAGE_FIELD] = self::INVALID_FILE_TYPE;
        }

        return $validationMessages;
    }
}
