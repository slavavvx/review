<?php
namespace Services\Entities;

use Models\Repository\Review as ReviewRepository;
use Models\Repository\Image as ImageRepository;
use Services\DataUpload\FileUpload;
use \Exception;

class Review
{
	/**
	 * @var ReviewRepository
	 */
	private $reviewRepository;

    /**
     * @var ImageRepository
     */
    private $imageRepository;

    /**
     * @var FileUpload
     */
    private $fileUpload;
	
	/**
	 * Review constructor.
	 * @param ReviewRepository $reviewRepository
	 * @param ImageRepository $imageRepository
	 * @param FileUpload $fileUpload
	 */
    public function __construct(

        ReviewRepository $reviewRepository,
        ImageRepository $imageRepository,
        FileUpload $fileUpload
    )
    {
        $this->reviewRepository = $reviewRepository;
        $this->imageRepository = $imageRepository;
        $this->fileUpload = $fileUpload;
    }
	
	/**
	 * @param array $formData
	 * @return bool
	 * @throws Exception
	 */
    public function createNewReview(array $formData) : bool
    {
        if (empty($formData)) {
            throw new Exception('There ara not data to save.');
        }

        $review = $this->addReview($formData);

        if (!$review) {
            throw new Exception('It is not managed to add review!');
        }

        if (!empty($formData['image'])) {
            $path = $this->addUserImageToDir($formData['image']);
            $newImageName = basename($path);

            $reviewId = $this->reviewRepository->getLastInsertId();
            $image = $this->addUserImageToDB($reviewId, $newImageName);

            if (!$image) {
                unlink($path);
                throw new Exception('It is not managed to add image!');
            }
        }

        return true;
    }

    /**
     * @param array $formData
     * @return bool
     */
    private function addReview(array $formData) : bool
    {
        /** @var \Models\Review $review */
        $review = $this->reviewRepository->getNew();
        $result = $review->saveReview($formData);

        return $result;
    }

    /**
     * @param int $reviewId
     * @param string $imageName
     * 
     * @return bool
     */
    private function addUserImageToDB(int $reviewId, string $imageName) : bool
    {
        /** @var \Models\Image $image */
        $image = $this->imageRepository->getNew();
        $result = $image->saveImage($reviewId, $imageName);

        return $result;
    }
	
	/**
	 * @param array $imageData
	 * @return string
	 * @throws Exception
	 */
    private function addUserImageToDir(array $imageData) : string
    {   
        $path = $this->fileUpload->saveFile($imageData);
        return $path;
    }
	
	/**
	 * @param array $params
	 * @return bool
	 * @throws Exception
	 */
    public function addLike(array $params) : bool
    {
       /** @var \Models\Review $review */
        $review = $this->reviewRepository->getNew();
        $result = $review->updateLike($params);

        if(!$result) {
            throw new Exception('It is not managed to update like/dislike!');
        }
        
        return $result;
    }
}
