<?php
namespace Routes;

use Library\Http\Request;

class FrontendRoutes 
{
    /**
     * @var Object $request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function getViewsDir()
    {
        return '../app/frontend/views/';
    }
    
    /**
     * The list routes
     * @return array
     */
    public function getRoutes() : array
    {

        return [

            // Routes for Review
            'GET/reviews'          => ['controller' => 'frontend-index', 'action' => 'index'],
            'GET/api/v1/reviews'   => ['controller' => 'frontend-review', 'action' => 'getReviews', 'ajax' => $this->request->isAjax()],
            'POST/api/v1/reviews'  => ['controller' => 'frontend-review', 'action' => 'addReviewByFormData', 'ajax' => $this->request->isAjax()],
            'PUT/api/v1/likes'     => ['controller' => 'frontend-review', 'action' => 'addLike', 'ajax' => $this->request->isAjax()],
            'GET/api/v1/analytics' => ['controller' => 'frontend-analytics', 'action' => 'getAnalytics', 'ajax' => $this->request->isAjax()],
        ];
    }
}
