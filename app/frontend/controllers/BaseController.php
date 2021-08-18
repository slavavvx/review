<?php
namespace Frontend\Controllers;

use Core\Controller;
use \Exception;

	
class BaseController extends Controller
{
    protected function register()
    {
        $storage = $this->getStorage();

        $storage->get('repositoryServiceProvider')->register($storage);
        $storage->get('serviceServiceProvider')->register($storage);
    }

    /**
     * Handle the 404 response
     */
    public function errorNotFoundAction()
    {
        if ($this->request->isAjax()) {

            return $this->response->setStatusCode(404, 'Not Found')->setJsonContent([
                'message' => 'You are lost...',
            ])->send();
        }
        // -- Render the 404 view
        $this->view->setVar('title', 'NotFound');
        $this->view->setVar('style', 'parts/error-styles');
        $this->view->setVar('main', 'error404');
        return $this->response->setStatusCode(404, 'Not Found')->send();
    }

    /**
     * @param string $token
     * @return bool
     * @throws Exception
     */
    public function isBot(string $token) : bool
    {
        /** @var \Services\ReCaptcha\ReCaptchaService $reCaptchaService */
        $reCaptchaService = $this->getStorage()->get('reCaptchaService');
        $secretKey = $this->getStorage()->getConfig('gRecaptcha')['secret_key'];

        $gResponse = $reCaptchaService->getGResponse($token, $secretKey);
        $result = $reCaptchaService->checkAction($gResponse);

        return $result;
    }
}
