<?php

namespace A7Pro\Marketplace\Customer\Core\Application\Services\Order\OrderReceived;

class OrderReceivedRequest
{
    public ?string $orderId;

    /**
     * OrderReceivedRequest constructor.
     * @param string|null $orderId
     */
    public function __construct(?string $orderId)
    {
        $this->orderId = $orderId;
    }

    public function validate(): array
    {
        $errors = [];

        if (!isset($this->orderId)){
            $errors[] = 'order_id_must_be_specified';
        }

        return $errors;
    }
}