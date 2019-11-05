<?php

namespace wsydney76\translate\models;

use craft\base\Model;

class TranslateGroup extends Model
{
    public $type = '';
    public $caption = '';

    public function __construct($type, $caption)
    {
        $this->type = $type;
        $this->caption = $caption;
        parent::__construct();
    }
}
