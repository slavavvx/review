<?php
namespace Frontend\Controllers;

use \Exception;
use \TypeError;

class AnalyticsController extends BaseController
{
    private const TITLE_NO_REVIEWS = 'Нет данных для анализа.';


    protected function initialize()
    {
        parent::initialize();
        $this->register();
    }

    public function getAnalyticsAction()
    {   
        /** @var \Models\Repository\Review $reviewRepository */
        $reviewRepository = $this->getStorage()->get('reviewRepository');
        $data = [];

        try {
            $totalRows = $reviewRepository->countRows();
        } catch (Exception | TypeError $e) {
            $this->error->createErrorLog($e);
            return $this->response->setStatusCode(500, 'Internal Server Error')->setJsonContent()->send();
        }

        if ($totalRows === 0) {

            $analytics = [
                'totalRows'    => $totalRows,
                'title'        => self::TITLE_NO_REVIEWS,
                'description'  => $data,
            ];
            
            return $this->response->setStatusCode(200, 'Ok')->setJsonContent([
                'analytics'  => $analytics,
            ], true)->send();
        }

        /** @var \Services\Reviews\ReviewAnalyticsService $analyticsService */
        $analyticsService = $this->getStorage()->get('reviewAnalyticsService');

        try {
            $data = $reviewRepository->getDataByThemes(['thanks', 'proposal', 'complaint']);
            $analytics = $analyticsService->prapareAnalytics($data, $totalRows);
        } catch (Exception | TypeError $e) {
            $this->error->createErrorLog($e);
            return $this->response->setStatusCode(500, 'Internal Server Error')->setJsonContent()->send();
        }

        return $this->response->setStatusCode(200, 'Ok')->setJsonContent([
            'analytics' => $analytics,
        ], true)->send();
    }
}
