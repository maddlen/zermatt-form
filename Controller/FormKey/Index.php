<?php
/**
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */

namespace Maddlen\ZermattForm\Controller\FormKey;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Store\Model\StoreManagerInterface;

class Index implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public function __construct(
        protected ResultFactory $resultFactory,
        protected FormKey $formKey,
        protected StoreManagerInterface $storeManager
    ) {
    }

    public function execute(): ResultInterface
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(['form_key' => $this->formKey->getFormKey()]);
        return $result;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setContents('Unauthorized');
        $result->setHttpResponseCode(403);
        return new InvalidRequestException($result);
    }

    protected function validateOrigin(RequestInterface $request): bool
    {
        $domain = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST) ?: '';
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        if (strpos($baseUrl, $domain) === false) {
            return false;
        }
        return true;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        // This route requests the form key... so we must leave it opened.
        // But we still want to enforce same origin policy.
        return $this->validateOrigin($request);
    }
}
