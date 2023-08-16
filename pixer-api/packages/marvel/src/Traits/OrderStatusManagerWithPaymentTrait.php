<?php

namespace Marvel\Traits;

use Marvel\Database\Models\Balance;
use Marvel\Database\Models\Order;
use Marvel\Enums\OrderStatus;
use Marvel\Enums\PaymentStatus;
use Marvel\Events\OrderCancelled;
use Marvel\Events\PaymentFailed;
use Marvel\Events\PaymentSuccess;

trait OrderStatusManagerWithPaymentTrait
{

    /**
     * manageVendorBalance
     *
     * @param mixed $order
     * @param mixed $order_status
     * @param mixed $payment_status
     * @return void
     */
    public function manageVendorBalance($order, $order_status, $prev_order_status)
    {
        //check if new status is completed then add balance to vendor
        if ($order_status === OrderStatus::COMPLETED) $this->checkIfChildOrder($order, 'add');
        //check if previous status was completed then we need to deduct the amount from vendor balance
        elseif ($prev_order_status === OrderStatus::COMPLETED) $this->checkIfChildOrder($order, 'deduct');
    }

    /**
     * checkIfChildOrder
     *
     * @param  mixed $order
     * @param  mixed $type
     * @return void
     */
    public function checkIfChildOrder($order, $type)
    {
        //check if order is child order
        if ($order->parent_id) {
            $parent_order = Order::find($order->parent_id);
            //check if parent order is mark completed then add vendor balance or continue
            if ($parent_order->order_status === OrderStatus::COMPLETED)
                $this->updateBalanceShop($order, $type);
        } else {
            //this is a parent order and check if a child order is completed then add vendor balance or continue
            $child_orders = $order->children;
            if ($child_orders->count() > 0) {
                foreach ($child_orders as $child_order) {
                    if ($child_order->order_status === OrderStatus::COMPLETED)
                        $this->updateBalanceShop($child_order, $type);
                }
            }
        }
    }


    /**
     * updateBalanceShop
     *
     * @param mixed $order
     * @return void
     */
    protected function updateBalanceShop($order, $action_type = 'add')
    {
        $balance = Balance::where('shop_id', '=', $order->shop_id)->first();
        $adminCommissionRate = $balance->admin_commission_rate;
        $shop_earnings = ($order->total * (100 - $adminCommissionRate)) / 100;
        if ($action_type == 'deduct') $shop_earnings = $shop_earnings * -1;
        $balance->total_earnings = $balance->total_earnings + $shop_earnings;
        $balance->current_balance = $balance->current_balance + $shop_earnings;
        $balance->save();
    }


    /**
     * orderStatusManagementOnPayment
     *
     * @param  mixed $order
     * @param  mixed $order_status
     * @param  mixed $payment_status
     * @return void
     */
    public function orderStatusManagementOnPayment($order, $order_status, $payment_status)
    {
        if ($payment_status === PaymentStatus::PENDING) {
            # code...
            # send notification to user about order is pending.
        } elseif ($payment_status === PaymentStatus::PROCESSING) {
            # code...
            # send notification to user about order is processing.
        } elseif ($payment_status === PaymentStatus::SUCCESS) {

            event(new PaymentSuccess($order));

            switch ($order_status) {
                case 'order-processing':
                    # code...
                    # send notification to site-admin about order is at processing level & payment is success.
                    break;

                case 'order-at-local-facility':
                    # code...
                    # send notification to user about order is at local facility.
                    break;

                case 'order-out-for-delivery':
                    # code...
                    # send notification to user about order is out for delivery.
                    break;

                case 'order-completed':
                    # code...
                    # send notification to user about order is delivered.
                    break;

                case 'order-cancelled':
                    $this->orderStatusManagementOnCancelled($order);
                    event(new OrderCancelled($order));
                    break;
            }
        } elseif ($payment_status === PaymentStatus::AWAITING_FOR_APPROVAL) {
            switch ($order_status) {

                case 'order-pending':
                    # code...
                    # send notification to user about order is pending & payment is waiting for approval.
                    break;
            }
        } elseif ($payment_status === PaymentStatus::FAILED) {

            event(new PaymentFailed($order));

            switch ($order_status) {
                case 'order-failed':
                    # code...
                    # send notification to site-admin about order is failed due to payment failed.
                    # then admin will set order status to cancelled manually on order page.
                    break;

                case 'order-cancelled':
                    $this->orderStatusManagementOnCancelled($order);
                    event(new OrderCancelled($order));
                    break;
            }
        } elseif ($payment_status === PaymentStatus::REVERSAL) {
            # code...
            switch ($order_status) {
                case 'order-cancelled':
                    $this->orderStatusManagementOnCancelled($order);
                    event(new OrderCancelled($order));
                    break;
            }
        }
    }

    /**
     * orderStatusManagementOnCOD
     *
     * @param  mixed $order
     * @param  string $prev_status
     * @param  string $new_status
     * @return void
     */
    public function orderStatusManagementOnCOD($order, $prev_status, $new_status)
    {
        switch ($new_status) {
            case OrderStatus::PENDING:
                # code...
                break;

            case OrderStatus::PROCESSING:
                # code...
                break;

            case OrderStatus::AT_LOCAL_FACILITY:
                # code...
                break;

            case OrderStatus::OUT_FOR_DELIVERY:
                # code...
                break;

            case OrderStatus::COMPLETED:
                # code...
                break;

            case OrderStatus::CANCELLED:
                # code...
                $this->orderStatusManagementOnCancelled($order);
                event(new OrderCancelled($order));
                break;

            case OrderStatus::REFUNDED:
                # code...
                event(new OrderCancelled($order));
                break;

            case OrderStatus::FAILED:
                # code...
                break;
        }
    }

    /**
     * orderAlreadyExists
     *
     * @param  mixed $order
     * @param  string $tracking_number
     * @return void
     */
    public function orderAlreadyExists($tracking_number)
    {
        try {
            $order_exists = false;
            $order_exists = Order::where('tracking_number', '=', $tracking_number)->exists();
            if ($order_exists) {
                return true;
            }
            return $order_exists;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    /**
     * orderStatusManagementOnCancelled
     *
     * @param  mixed $order
     * @return void
     */
    public function orderStatusManagementOnCancelled($order)
    {
        if ($order->parent_id) {
            $parent_order = Order::find($order->parent_id);
        } else {
            $parent_order = $order;
        }

        $tax_amount = $parent_order->sales_tax;
        $delivery_fee = $parent_order->delivery_fee;
        $currently_paid = $parent_order->paid_total;
        $amount = $currently_paid - $tax_amount - $delivery_fee;
        $tax_rate = 0;
        if ($amount > 0) {
            $tax_rate = $tax_amount / $amount;
            //for precision
            $tax_rate = $tax_rate * 1000000;
        }

        // if order is child order
        if ($order->parent_id) {
            $reducedRevenueAmount =  $amount - $order->amount;
            $reducedTaxAmount = $parent_order->sales_tax - ($order->amount * $tax_rate) / 1000000; //for precision

            $parent_order->sales_tax = $reducedTaxAmount;

            $parent_order->paid_total = $reducedRevenueAmount + $reducedTaxAmount + $delivery_fee;
            $parent_order->total = $reducedRevenueAmount + $reducedTaxAmount + $delivery_fee;
            $parent_order->cancelled_amount = $parent_order->cancelled_amount + $order->amount + ($order->amount * $tax_rate) / 1000000;
            $parent_order->save();
            //TODO: give refund to customer if order is pre paid
            if ($parent_order->paid_total == 0) {
                $parent_order->delivery_fee = 0;
                $parent_order->sales_tax = 0;
                $parent_order->save();
            }

            //add cancel amount to the order
            $order->cancelled_amount = $order->total;
            $order->paid_total = 0;
            $order->total = 0;
            $order->save();
        } else {
            $childOrders = $parent_order->children;
            foreach ($childOrders as $childOrder) {
                if ($childOrder->order_status == OrderStatus::CANCELLED) continue;
                $childOrder->cancelled_amount = $childOrder->total;
                $childOrder->paid_total = 0;
                $childOrder->total = 0;
                $childOrder->save();
            }
            $parent_order->cancelled_amount += $parent_order->paid_total;
            $parent_order->sales_tax = 0;
            $parent_order->delivery_fee = 0;
            $parent_order->paid_total = 0;
            $parent_order->total = 0;
            $parent_order->save();
            //TODO: give refund to customer if order is pre paid

        }
    }
}
