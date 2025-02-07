<?php

namespace Maddlen\ZermattForm\FormRules;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

readonly class FormRulesService
{
    private FormRulesActionInterface $action;

    public function __construct(
        protected RequestInterface $request,
        protected ResultFactory    $resultFactory,
        protected Validate         $validate,
        protected ManagerInterface $messageManager,
        protected UrlInterface     $url
    )
    {
    }

    public function execute(FormRulesActionInterface $action)
    {
        $this->action = $action;
        $isSuccess = false;
        if ($this->validate->pass()) {
            try {
                if ($this->isSubmitted()) {
                    $this->messageManager->addSuccessMessage($action->getSuccessMessage());
                    $isSuccess = true;
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->request->getParam('must_redirect') ?
            $this->resultFactory->create(ResultFactory::TYPE_RAW)->setHeader(
                'x-zermatt-redirect',
                $action->redirectUrl()
            )
            : $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(
                [
                    'success' => $isSuccess,
                    'redirect' => $action->redirectUrl(),
                    'messages' => array_map(fn($message) => $message->getText(), $this->messageManager->getMessages(true)->getItems())
                ]
            );
    }

    protected function isSubmitted(): bool
    {
        if ($this->request->getParam('must_submit')) {
            return $this->action->submitForm();
        }
        return false;
    }
}
