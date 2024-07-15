<?php
/**
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */

namespace Maddlen\ZermattForm\FormRules;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

abstract class FormRulesAbstract implements FormRulesActionInterface
{
    public function __construct(
        protected readonly ResultFactory    $resultFactory,
        protected readonly Validate         $validate,
        protected readonly ManagerInterface $messageManager,
        protected readonly UrlInterface     $url
    )
    {
    }

    public function execute()
    {
        if ($this->validate->pass()) {
            $this->messageManager->addSuccessMessage(__('Form is valid.'));
        } else {
            $this->messageManager->addErrorMessage(__('Form is invalid.'));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)
            ->setData(['redirect' => $this->redirectUrl()]);
    }

    abstract public function redirectUrl(): string;

    abstract public function rules(): array;
}
