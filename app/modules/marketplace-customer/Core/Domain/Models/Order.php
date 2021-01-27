<?php


namespace A7Pro\Marketplace\Customer\Core\Domain\Models;


class Order
{
    const STATUS_ONORDER = 1;
    const STATUS_PREPARING = 2;
    const STATUS_SHIPPING = 3;
    const STATUS_RECEIVED = 4;
    const STATUS_CANCELLED = 5;
    const STATUS_DONE = 6;
    const DEFAULT_AMOUNT_TOTAL = 0;
    const DEFAULT_SHIPPING_TOTAL = 0;

    private OrderId $orderId;
    private string $sellerId;
    private int $status;
    private array $products;
    private array $invoice;
    private array $courier;
    private array $customer;
    private ?string $receiptNo;
    private string $shippingAddress;
    private int $amountTotal;
    private int $shippingTotal;
    private Date $createdAt;
    private Date $updatedAt;
    private Date $expiredAt;
    private ?array $cartIds;

    /**
     * Order constructor.
     * @param OrderId $orderId
     * @param string $sellerId
     * @param int $status
     * @param array $products
     * @param array $invoice
     * @param array $courier
     * @param array $customer
     * @param string|null $receiptNo
     * @param string $shippingAddress
     * @param int $amountTotal
     * @param int $shippingTotal
     * @param Date $createdAt
     * @param Date $updatedAt
     * @param Date $expiredAt
     * @param array|null $cartIds
     */
    public function __construct(OrderId $orderId, string $sellerId, int $status, array $products, array $invoice, array $courier, array $customer, ?string $receiptNo, string $shippingAddress, int $amountTotal, int $shippingTotal, Date $createdAt, Date $updatedAt, Date $expiredAt, ?array $cartIds)
    {
        $this->orderId = $orderId;
        $this->sellerId = $sellerId;
        $this->status = $status;
        $this->products = $products;
        $this->invoice = $invoice;
        $this->courier = $courier;
        $this->customer = $customer;
        $this->receiptNo = $receiptNo;
        $this->shippingAddress = $shippingAddress;
        $this->amountTotal = self::DEFAULT_AMOUNT_TOTAL;
        $this->shippingTotal = self::DEFAULT_SHIPPING_TOTAL;
        $this->createdAt = $createdAt ?: new Date(new \DateTime());
        $this->updatedAt = $updatedAt ?: new Date(new \DateTime());
        $this->expiredAt = $expiredAt;
        $this->cartIds = $cartIds;
    }


    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId->id();
    }

    /**
     * @return string
     */
    public function getSellerId(): string
    {
        return $this->sellerId;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt->toDateTimeString();
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updatedAt->toDateTimeString();
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @return array
     */
    public function getInvoice(): array
    {
        return $this->invoice;
    }

    /**
     * @return array
     */
    public function getCourier(): array
    {
        return $this->courier;
    }

    /**
     * @return array
     */
    public function getCustomer(): array
    {
        return $this->customer;
    }

    /**
     * @return string|null
     */
    public function getReceiptNo(): ?string
    {
        return $this->receiptNo;
    }

    /**
     * @return string
     */
    public function getShippingAddress(): string
    {
        return $this->shippingAddress;
    }

    /**
     * @return int
     */
    public function getAmountTotal(): int
    {
        return $this->amountTotal;
    }

    /**
     * @return int
     */
    public function getShippingTotal(): int
    {
        return $this->shippingTotal;
    }

    /**
     * @return string
     */
    public function getExpiredAt(): string
    {
        return $this->expiredAt->toDateTimeString();
    }

    /**
     * @return string
     */
    static function getStatusText(int $status): string
    {
        switch ($status) {
            case self::STATUS_ONORDER:
                return "On Order";

            case self::STATUS_PREPARING:
                return "Preparing";

            case self::STATUS_SHIPPING:
                return "Shipping";

            case self::STATUS_RECEIVED:
                return "Received";

            case self::STATUS_CANCELLED:
                return "Cancelled";

            case self::STATUS_DONE:
                return "Done";

            default:
                return "Unknown status";
        }
    }

    /**
     * @param array $products
     */
    public function setProducts(array $products): void
    {
        $this->products = $products;
    }

    /**
     * @return void
     */
    public function setCart($cart): void
    {
        $this->cartIds = $cart;
    }

    public function ownedBy($sellerId): bool
    {
        return $this->sellerId == $sellerId;
    }

    public function getOrderNextStatus(string $receiptNo = ""): int
    {
        if ($this->status == self::STATUS_ONORDER)
            return self::STATUS_PREPARING;
        elseif (
            $this->status == self::STATUS_PREPARING && $receiptNo != ""
        )
            return self::STATUS_SHIPPING;
        return $this->status;
    }

}