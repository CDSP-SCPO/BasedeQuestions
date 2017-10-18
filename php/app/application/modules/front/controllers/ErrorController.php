<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class ErrorController extends Zend_Controller_Action
{

	public function init()
	{
		$this->view->translate = new Demo_Translate(
    		'tmx', 
    		realpath($locale = APPLICATION_PATH . '/../locale/error.xml'), 
    		'fr'
    	);
    	$this->view->layout()->setLayout('error');
	}
	
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                $this->view->error = 404;
                $url = $this->getRequest()->getBaseUrl();
                break;

            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                $this->view->error = 500;

                $url = $this->getRequest()->getHttpHost() . $this->view->url();
				$message = $errors->exception->getMessage();
				$stackTrace = $errors->exception->getTraceAsString();
				$params = $errors->request->getParams();
				$params = var_export($params, true);
				$date = date('Y/m/d H:i:s');
				$log = <<<HEREDOC
Date: $date

Url: $url

Message: $message

Stacktrace : $stackTrace

Params : $params




HEREDOC;
				file_put_contents(EXCEPTIONS_LOG, $log, FILE_APPEND);

			break;
        }

        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
    }

}