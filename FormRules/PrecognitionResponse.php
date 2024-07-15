<?php
/**
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */

namespace Maddlen\ZermattForm\FormRules;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class PrecognitionResponse
{
    public function __construct(
        protected ResultFactory              $resultFactory,
        protected readonly ResponseInterface $response,
        protected readonly Validate          $validate
    )
    {
    }


    public function send(): never
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($this->validate->results());
        $result->setHttpResponseCode($this->validate->pass() ? 200 : 422);
        $result->setHeader('Precognition', 'true');
        $result->renderResult($this->response);

        $this->response->sendResponse();
        exit;
    }
}
