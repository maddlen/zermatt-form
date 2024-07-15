<?php
/**
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */

namespace Maddlen\ZermattForm\FormRules;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Validator\ValidatorInterface;

interface FormRulesActionInterface extends HttpPostActionInterface
{
    /**
     * The validations to run against some
     * fields from the request payload.
     *
     * @return ValidatorInterface[]
     * @see /vendor/magento/framework/Validator
     *
     */
    public function rules(): array;

    public function redirectUrl(): string;
}
