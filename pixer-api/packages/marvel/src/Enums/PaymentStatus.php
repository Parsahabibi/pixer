<?php


namespace Marvel\Enums;

use BenSampo\Enum\Enum;

/**
 * Class RoleType
 * @package App\Enums
 */
final class PaymentStatus extends Enum
{
    public const PENDING                = 'payment-pending';
    public const PROCESSING             = 'payment-processing';
    public const SUCCESS                = 'payment-success';
    public const FAILED                 = 'payment-failed';
    public const REVERSAL               = 'payment-reversal';
    public const CASH_ON_DELIVERY       = 'payment-cash-on-delivery';
    public const CASH                   = 'payment-cash';
    public const WALLET                 = 'payment-wallet';
    public const AWAITING_FOR_APPROVAL  = 'payment-awaiting-for-approval';
    public const DEFAULT_PAYMENT_STATUS = 'payment-pending';
}
