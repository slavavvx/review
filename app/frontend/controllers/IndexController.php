<?php
namespace Frontend\Controllers;

use \Exception;
use \TypeError;

class IndexController extends BaseController
{
	private const COLUMN_NAME_FOR_SORT_DEFAULT = 'date';
    private const DATE_FORMAT = 'd-m-Y';
    private const SORT_DESC = 'DESC';
//    private const SORT_ASC = 'ASC';


	protected function initialize()
    {
        parent::initialize();
        $this->register();

        $recaptchaKey = $this->getStorage()->getConfig('gRecaptcha')['browser_key'];

        $this->view->setVar('title', 'Review');
        $this->view->setVar('style', 'parts/review-styles');
        $this->view->setVar('script', 'parts/review-scripts');
        $this->view->setVar('recaptchaKey', $recaptchaKey);
        $this->view->setVar('main', 'review');
    }

	public function indexAction()
	{	
        /** @var \Models\Repository\Review $reviewRepository */
        $reviewRepository = $this->getStorage()->get('reviewRepository');
        /** @var \Services\Reviews\ReviewsService $reviewsService */
        $reviewsService = $this->getStorage()->get('reviewsService');

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
            $this->setErrorMessage();
            return $this->response->setStatusCode(500, 'Internal Server Error')->send();
        }

        if (empty($totalRows)) {
        	$this->view->setVar('reviews', $reviews);
        	$this->view->setVar('pagination', $pagination);
            return $this->response->setStatusCode(200, 'Ok')->send();
        }

        $limit = $reviewsService::LIMIT_DEFAULT;
        $currentPage = $reviewsService::CURRENT_PAGE_DEFAULT;
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
            $this->setErrorMessage();
            return $this->response->setStatusCode(500, 'Internal Server Error')->send();
        }

        foreach ($reviews as &$review) {
            $review['theme'] = $reviewsService->translateTheme($review['theme']);
            $review['date'] = date(self::DATE_FORMAT, strtotime($review['date']));
        }

        $this->view->setVar('reviews', $reviews);
        $this->view->setVar('pagination', $pagination);

        return $this->response->setStatusCode(200, 'Ok')->send();
	}

    private function setErrorMessage(string $errorMessage = '') : bool
    {
        if (empty($errorMessage)) {
            $errorMessage = 'Error: Reviews are not available currently. Try again later.';
        }

        $this->view->setVar('errorMessage', $errorMessage);
        return true;
    }
}
