<?php

namespace Marvel\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Marvel\Exceptions\MarvelException;

trait TranslationTrait
{

    /**
     * Get all translations for the model.
     *
     * @return string
     */
    public function getTranslatedLanguagesAttribute()
    {
        if ($this->table === 'coupons') {
            $translatedProducts = $this->where('code', $this->code)->get();
            return $translatedProducts->pluck('language')->toArray();
        }

        $translatedProducts = $this->where('slug', $this->slug)->get();
        return $translatedProducts->pluck('language')->toArray();
    }


    /**
     * getTranslations
     *
     * @return void
     */
    public function getTranslations()
    {
        try {
            $translation =  DB::table('translations')->where('item_id', $this->model->id)->where('item_type', get_class($this))->first();
        } catch (\Throwable $th) {
            throw new MarvelException(NOT_FOUND);
        }

        if ($translation->language_code === DEFAULT_LANGUAGE) {
            return DB::table('translations')->where('translation_item_id', $translation->item_id)->where('item_type', get_class($this))->get();
        }
        return DB::table('translations')->where('translation_item_id', $translation->translation_item_id)->orWhere('item_id', $translation->translation_item_id)->where('item_type', get_class($this))->get();
    }

    /**
     * storeTranslation
     *
     * @param  mixed $translation_item_id
     * @param  mixed $language_code
     * @param  mixed $source_language_code
     * @return void
     */
    public function storeTranslation($translation_item_id, $language_code, $source_language_code = DEFAULT_LANGUAGE)
    {
        $translation =  DB::table('translations')->where('item_id', $this->id)->where('item_type', get_class($this))->first();
        if (!$translation) {
            DB::table('translations')->insert([
                'item_id' => $this->id,
                'item_type' =>  get_class($this),
                'language_code' => $language_code,
                'source_language_code' => $source_language_code,
                'translation_item_id' => $translation_item_id
            ]);
        }
    }

    /**
     * Helper method for generate default translated text for invoice
     *
     * @param array $translatedText
     * @return array
     */
    public function formatInvoiceTranslateText($translatedText = [])
    {
        return [
            'subtotal'      => Arr::has($translatedText, 'subtotal') ? $translatedText['subtotal'] : 'SubTotal',
            'discount'      => Arr::has($translatedText, 'discount') ? $translatedText['discount'] : 'Discount',
            'tax'           => Arr::has($translatedText, 'tax') ? $translatedText['tax'] : 'Tax',
            'delivery_fee'  => Arr::has($translatedText, 'delivery_fee') ? $translatedText['delivery_fee'] : 'Delivery Fee',
            'total'         => Arr::has($translatedText, 'total') ? $translatedText['total'] : 'Total',
            'products'      => Arr::has($translatedText, 'products') ? $translatedText['products'] : 'Products',
            'quantity'      => Arr::has($translatedText, 'quantity') ? $translatedText['quantity'] : 'Qty',
            'invoice_no'    => Arr::has($translatedText, 'invoice_no') ? $translatedText['invoice_no'] : 'Invoice No',
            'date'          => Arr::has($translatedText, 'date') ? $translatedText['date'] : 'Date',
        ];
    }
}
