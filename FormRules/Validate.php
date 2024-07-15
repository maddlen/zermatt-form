<?php
/**
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */

namespace Maddlen\ZermattForm\FormRules;

use InvalidArgumentException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Validator\ValidatorInterface;

class Validate
{
    private array $rules;

    private array $invalidParams = [];

    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly Validator        $formKeyValidator
    )
    {
    }

    public function request(array $rules = []): void
    {
        $requestParams = $this->request->isAjax() ? json_decode((string) $this->request->getContent(), true, 512, JSON_THROW_ON_ERROR) : $this->request->getParams();
        $this->request->setParams($requestParams);
        $this->invalidParams = [];
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->invalidParams['form_key'] = __('Invalid form key');
        } else {
            $this->rules = $rules;
            $this->validateParams($requestParams, $rules);
        }
    }

    private function validateParams(mixed $requestParams, array $rules): void
    {
        $paramsToValidate = array_filter($requestParams, static fn($v, $param): bool => in_array($param, array_keys($rules)), ARRAY_FILTER_USE_BOTH);
        array_walk($paramsToValidate, fn($value, $param) => $this->validateParam($param, $value));
    }

    private function validateParam(int|string $param, $value): void
    {
        array_walk($this->rules[$param], function ($validatorClass) use ($param, $value): void {
            $validator = $this->getValidator($validatorClass);
            $isPrecognition = $this->request->getHeader('Precognition');
            $isValid = $validator->isValid($value);
            if (
                $isPrecognition && $value && !$isValid // When in Precognition, empty fields (empty $value) are valid...
                || !$isPrecognition && !$isValid // ... whereas, when submitting the form, non-empty validations must pass.
            ) {
                $messages = $validator->getMessages();
                $this->invalidParams[$param] = __(reset($messages))->render();
            }
        });
    }

    private function getValidator($validatorClass): ValidatorInterface
    {
        $validator = new $validatorClass();
        if (!$validator instanceof ValidatorInterface) {
            throw new InvalidArgumentException('Rule class must implement \Laminas\Validator\ValidatorInterface');
        }

        return $validator;
    }

    public function results(): array
    {
        if (!$this->pass()) {
            // Prepare results in the way Laravel Precognition JS expects them.
            return [
                'message' => implode(' ', [
                    reset($this->invalidParams),
                    count($this->invalidParams) > 1 ? __('and %1 more errors', count($this->invalidParams)) : ''
                ]),
                'errors' => $this->invalidParams
            ];
        }

        return [];
    }

    public function pass(): bool
    {
        return $this->invalidParams === [];
    }
}
