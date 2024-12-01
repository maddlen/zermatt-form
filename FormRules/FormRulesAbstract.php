<?php
/**
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */

namespace Maddlen\ZermattForm\FormRules;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;

abstract class FormRulesAbstract implements FormRulesActionInterface
{
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly ResultFactory    $resultFactory,
        protected readonly Validate         $validate,
        protected readonly ManagerInterface $messageManager,
        protected readonly UrlInterface     $url
    )
    {
    }

    public function execute()
    {
        $isSuccess = false;
        if ($this->validate->pass()) {
            try {
                if ($this->isSubmitted()) {
                    $this->messageManager->addSuccessMessage($this->getSuccessMessage());
                    $isSuccess = true;
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->request->getParam('must_redirect') ?
            $this->resultFactory->create(ResultFactory::TYPE_RAW)->setHeader(
                'x-zermatt-redirect',
                $this->redirectUrl()
            )
            : $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(
                [
                    'success' => $isSuccess,
                    'redirect' => $this->redirectUrl(),
                    'messages' => array_map((fn($message) => $message->getText()), $this->messageManager->getMessages(true)->getItems())]
            );
    }

    protected function isSubmitted(): bool
    {
        if ($this->request->getParam('must_submit')) {
            return $this->submitForm();
        }
        return false;
    }

    abstract public function submitForm(): bool;

    abstract public function getSuccessMessage(): ?Phrase;

    abstract public function redirectUrl(): string;

    abstract public function rules(): array;
}
