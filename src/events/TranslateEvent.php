<?php

namespace wsydney76 \translate\events;

use yii\base\Event;

class TranslateEvent extends Event
{
    public $element;
    public $field;
    public $value = '';
}
