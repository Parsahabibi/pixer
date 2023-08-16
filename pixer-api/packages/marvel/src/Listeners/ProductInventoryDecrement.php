<?php

namespace Marvel\Listeners;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Marvel\Database\Models\Product;
use Marvel\Database\Models\Variation;

class ProductInventoryDecrement implements ShouldQueue
{
    protected function updateProductInventory($product)
    {
        try {
            $updatedQuantity = $product->quantity - $product->pivot->order_quantity;
            if ($updatedQuantity > -1) {
                if (TRANSLATION_ENABLED) {
                    $this->updateTranslationsInventory($product, $updatedQuantity);
                } else {
                    Product::find($product->id)->update(['quantity' => $updatedQuantity]);
                }
                if (!empty($product->pivot->variation_option_id)) {
                    $variationOption = Variation::findOrFail($product->pivot->variation_option_id);
                    $variationOption->quantity = $variationOption->quantity - $product->pivot->order_quantity;
                    if (TRANSLATION_ENABLED) {
                        $this->updateVariationTranslationsInventory($variationOption, $variationOption->quantity);
                    } else {
                        $variationOption->update([['quantity' => $variationOption->quantity]]);
                    }
                }
            }
        } catch (Exception $th) {
            //
        }
    }

    public function updateTranslationsInventory($product, $updatedQuantity)
    {
        Product::where('sku', $product->sku)->update(['quantity' => $updatedQuantity]);
    }

    public function updateVariationTranslationsInventory($variationOption, $updatedQuantity)
    {
        Variation::where('sku', $variationOption->sku)->update(['quantity' => $updatedQuantity]);
    }


    public function handle($event)
    {
        $products = $event->order->products;
        foreach ($products as $product) {
            $this->updateProductInventory($product);
        }
    }
}
