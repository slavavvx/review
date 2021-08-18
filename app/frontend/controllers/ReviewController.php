<?php
namespace Frontend\Controllers;

use \Exception;
use \TypeError;

class ReviewController extends BaseController
{
    private const COLUMN_NAME_FOR_SORT_DEFAULT = 'date';
    private const DATE_FORMAT = 'd-m-Y';
    private const SORT_DESC = 'DESC';
//    private const SORT_ASC = 'ASC';


    protected function initialize()
    {
        parent::initialize();
        $this->register();
    }

    /**
     * If Ajax request
     * Getting reviews from Db and return response in json format
     */
    public function getReviewsAction()
    {   
        /** @var \Models\Repository\Review $reviewRepository */
        $reviewRepository = $this->getStorage()->get('reviewRepository');
        /** @var \Services\Reviews\ReviewsService $reviewsService */
        $reviewsService = $this->getStorage()->get('reviewsService');

        $data = $this->request->get();

        try {
            $preparedData = $reviewsService->prepareData($data);
        } catch (Exception | TypeError $e) {
            $this->error->createErrorLog($e);
            return $this->response->setStatusCode(422, 'Unprocessable Entity')->setJsonContent([
                'message' => 'Error: Invalid data!',
            ])->send();
        }

        ['limit' => $limit, 'currentPage' => $currentPage] = $preparedData;
        $reviews = [];
        $pagination = [
            'totalRows'      => 0,
            'totalPages'     => 0,
            'paginatedPages' => 0,
        ];
	
	    try {
		    $totalRows = $reviewRepository->countRows();
	    } catch (Exception | TypeError $e) {
            $this->error->createErrorLog($e);
            return $this->response->setStatusCode(500, 'Internal Server Error')->setJsonContent()->send();
        }

        if (empty($totalRows)) {

            return $this->response->setStatusCode(200, 'Ok')->setJsonContent([
                'reviews'    => $reviews,
                'pagination' => $pagination,
            ], true)->send();
        }

        $totalPages = ceil($totalRows / $limit);
        $paginatedPages = ($totalPages <= $reviewsService::MAX_PAGINATED_PAGES) ? $totalPages : $reviewsService::MAX_PAGINATED_PAGES;
        $offset = ($currentPage - 1) * $limit;
        $pagination = [
            'totalRows'      => $totalRows,
            'totalPages'     => $totalPages,
            'paginatedPages' => $paginatedPages,
        ];

        try {
            $reviews = $reviewRepository->getAll(
                [
                    'join'           => 'images',
                    'sort_direction' => self::COLUMN_NAME_FOR_SORT_DEFAULT . ' ' . self::SORT_DESC,
                    'offset'         => $offset,
                    'limit'          => $limit,
                ]
            );
        } catch (Exception | TypeError $e) {
            $this->error->createErrorLog($e);
            return $this->response->setStatusCode(500, 'Internal Server Error')->setJsonContent()->send();
        }

        foreach ($reviews as &$review) {
            $review['theme'] = $reviewsService->translateTheme($review['theme']);
            $review['date'] = date(self::DATE_FORMAT, strtotime($review['date']));
        }

        return $this->response->setStatusCode(200, 'Ok')->setJsonContent([
            'reviews'    => $reviews,
            'pagination' => $pagination,
        ], true)->send();
    }

   /**
    * If Ajax request
    * Addition new review
    */
    public function addReviewByFormDataAction()
    {
        $formData = $this->request->post();
        $token = $formData['token'] ?? '';

        try {
            if ($this->isBot($token)) {
                return $this->response->setStatusCode(400, 'Bad Request')->setJsonContent([
                    'message' => 'You failed the Google ReCaptcha test!',
                ])->send();
            }
        } catch (Exception | TypeError $e) {
            $this->error->createErrorLog($e);
            return $this->response->setStatusCode(500, 'Internal Server Error')->setJsonContent()->send();
        }

        /** @var \Services\Forms\ReviewFormService $reviewFormService */
        $reviewFormService = $this->getStorage()->get('reviewFormService');
        $preparedFormData = $reviewFormService->prepareFormData($formData);
        $keyName = 'image';
        $imageData = $this->request->isTransferredFile($keyName);

        if (!empty($imageData)) {

             try {
                $preparedImageData = $reviewFormService->prepareImageData($imageData);
                $preparedFormData[$keyName] = $preparedImageData;
            } catch (Exception | TypeError $e) {
                $this->error->createErrorLog($e);
                return $this->response->setStatusCode(500, 'Internal Server Error')->setJsonContent()->send();
            }
        }

        $validationMessages = $reviewFormService->validateReviewFormData($preparedFormData);

        if (!empty($validationMessages)) {
            
            return $this->response->setStatusCode(422, 'Unprocessable Entity')->setJsonContent([
                'message'    => 'Validation failed!',
                'validation' => $validationMessages,
            ])->send();
        }

        /** @var \Services\Entities\Review $reviewEntity */
        $reviewEntity = $this->getStorage()->get('reviewEntity');

        /** @var \PDO $link */
        $link = $this->getStorage()->get('db')->getConnection();
        $link->beginTransaction();

        try {
            $reviewEntity->createNewReview($preparedFormData);
        } catch (Exception | TypeError $e) {
            $link->rollBack();
            
            $this->error->createErrorLog($e);
            return $this->response->setStatusCode(500, 'Internal Server Error')->setJsonContent()->send();
        }

        $link->commit();

        return $this->response->setStatusCode(201, 'Created')->setJsonContent([
            'message' => 'Review is added successfully!',
        ], true)->send();
    }

    public function addLikeAction()
    {
        $likes = $this->request->put();
        $token = $likes['token'] ?? '';

        try {
            if ($this->isBot($token)) {
                return $this->response->setStatusCode(400, 'Bad Request')->setJsonContent([
                    'message' => 'You failed the Google ReCaptcha test!',
                ])->send();
            }
        } catch (Exception | TypeError $e) {
            $this->error->createErrorLog($e);
            return $this->response->setStatusCode(500, 'Internal Server Error')->setJsonContent()->send();
        }

        if (empty($likes)) {
            
            return $this->response->setStatusCode(400, 'Bad Request')->setJsonContent()->send();
        }

        /** @var \Services\Reviews\ReviewLikeService $likeService */
        $likeService = $this->getStorage()->get('reviewLikeService');

        try {
            $preparedData = $likeService->prepareDataForLike($likes);
        } catch (Exception | TypeError $e) {
            $this->error->createErrorLog($e);
           // $message = $e->getMessage();
            
            return $this->response->setStatusCode(400, 'Bad Request')->setJsonContent()->send();
        }

        /** @var \Services\Entities\Review $reviewEntity */
        $reviewEntity = $this->getStorage()->get('reviewEntity');
        /** @var \Models\Repository\Review $reviewRepository */
        $reviewRepository = $this->getStorage()->get('reviewRepository');

        try {
            $reviewEntity->addLike($preparedData);
            $row = $reviewRepository->getById($preparedData['review_id']);
        } catch (Exception | TypeError $e) {
            $this->error->createErrorLog($e);
            
            return $this->response->setStatusCode(500, 'Internal Server Error')->setJsonContent()->send();
        }

        return $this->response->setStatusCode(200, 'Ok')->setJsonContent([
            'like'        => $row['like'],
            'dislike'     => $row['dislike'],
            'likeMessage' => $preparedData['likeMessage'],
        ], true)->send();
    }
}
