<?php

namespace Marvel\Payments;

use Marvel\Database\Models\Settings;

abstract class Base
{
  public $currency;

  public function __construct()
  {
    $settings = Settings::first();
    $this->currency = $settings->options['currency'];
  }
}
