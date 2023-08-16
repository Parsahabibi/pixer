<?php

namespace Marvel\Traits;

use Marvel\Enums\PaymentStatus;
use Marvel\Enums\PaymentGatewayType;
use Marvel\Enums\OrderStatus as OrderStatusEnum;

trait OrderManagementTrait
{
    use OrderStatusManagerWithPaymentTrait;

    /**
     * changeOrderStatus
     *
     * @param  mixed $order
     * @param  mixed $status
     * @return void
     */
    public function changeOrderStatus($order, $status)
    {
        $prev_order_status = $order->order_status;
        $order->order_status = $status;
        $new_order_status = $order->order_status;

        if ($prev_order_status !== $new_order_status) {
            $payment_gateway_type = isset($order->payment_gateway) ? $order->payment_gateway : PaymentGatewayType::CASH_ON_DELIVERY;
            if ($payment_gateway_type !== PaymentGatewayType::CASH_ON_DELIVERY && $payment_gateway_type !== PaymentGatewayType::CASH) {
                if ($order->payment_status === PaymentStatus::SUCCESS)
                    $this->manageVendorBalance($order, $new_order_status, $prev_order_status);
                $this->orderStatusManagementOnPayment($order, $new_order_status, $order->payment_status);
            } else {
                $this->manageVendorBalance($order, $new_order_status, $prev_order_status);
                $this->orderStatusManagementOnCOD($order, $prev_order_status, $new_order_status);
            }
        }
        $order->save();

        try {
            $children = json_decode($order->children);
        } catch (\Throwable $th) {
            $children = $order->children;
        }
        if (is_array($children) && count($children) && $order->order_status === OrderStatusEnum::CANCELLED) {
            foreach ($order->children as $child_order) {
                $child_order->order_status = $status;
                $child_order->save();
            }
        }
        return $order;
    }
}
