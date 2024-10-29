<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Core\Gbx;

use Yuhzel\Xaseco\Core\Gbx\IxrError;

/**
 * Trait ErrorHandlingTrait
 *
 * Provides error handling capabilities for classes that use this trait.
 * It allows setting, getting, checking, and displaying errors.
 *
 * @package Yuhzel\Xaseco\Core\Gbx
 * @author Yuhzel
 */
trait ErrorHandlingTrait
{
    private ?IxrError $error = null;

    /**
     * Sets an error instance.
     *
     * @param IxrError $error The error to set.
     *
     * @return void
     */
    public function setError(IxrError $error): void
    {
        $this->error = $error;
    }

    /**
     * Gets the current error instance.
     *
     * @return ?IxrError The current error, or null if no error is set.
     */
    public function getError(): ?IxrError
    {
        return $this->error;
    }


    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function displayError(): string
    {
        if ($this->hasError()) {
            return "Error Code: {$this->error->getCode()}, Message: {$this->error->getMessage()}";
        }
        return 'No error found.';
    }
}
