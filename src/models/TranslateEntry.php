<?php

namespace wsydney76\translate\models;

use Craft;
use craft\base\Field;
use craft\base\Model;
use craft\elements\Entry;
use craft\fields\Matrix;
use craft\models\FieldLayoutTab;
use verbb\supertable\fields\SuperTableField;

class TranslateEntry extends Model
{
    public $source;
    public $target;
    public $siteFrom;
    public $siteTo;

    public $items = [];

    public function __construct($entryId, $siteFromId, $siteToId)
    {
        $this->source = Entry::find()->id($entryId)->anyStatus()->drafts(true)->siteId($siteFromId)->one();
        $this->target = Entry::find()->id($entryId)->anyStatus()->drafts(true)->siteId($siteToId)->one();

        $this->siteFrom = Craft::$app->sites->getSiteById($siteFromId);
        $this->siteTo = Craft::$app->sites->getSiteById($siteToId);

        parent::__construct();
    }

    public function init()
    {
        if (!$this->source || !$this->target) {
            return null;
        }

        /** @var FieldLayoutTab $tab */
        foreach ($this->source->fieldLayout->getTabs() as $tab) {
            /** @var Field $field */

            $this->items[] = new TranslateGroup('tab', $tab->name);
            foreach ($tab->fields as $field) {
                $this->_handleField($field);
            }
        }
    }

    protected function _handleField(Field $field, $owner = null)
    {

        if ($field->hasContentColumn()) {
            if (substr($field->getContentColumnType(), 0, 6) == 'string' ||
                substr($field->getContentColumnType(), 0, 4) == 'text') {
                if ($field->translationMethod == 'site' || $field->translationMethod == 'language' || $field->translationMethod == 'siteGroup') {
                    $this->items[] = new TranslateField($this, $field, $owner);
                }
            }
        } elseif ($field instanceof Matrix) {
            $this->items[] = new TranslateGroup('matrix', $field->name);
            $blocks = $this->source->getFieldValue($field->handle)->anyStatus()->all();
            foreach ($blocks as $block) {
                $this->items[] = new TranslateGroup('block', $block->type->name);
                foreach ($block->type->fieldLayout->fields as $blockField) {
                    $this->_handleField($blockField, $block);
                }
            }
        } elseif ($field instanceof \benf\neo\Field) {
            $this->items[] = new TranslateGroup('neo', $field->name);
            $blocks = $this->source->getFieldValue($field->handle)->anyStatus()->all();

            foreach ($blocks as $block) {
                $this->items[] = new TranslateGroup('block', $block->type->name);
                foreach ($block->type->fieldLayout->fields as $blockField) {
                    $this->_handleField($blockField, $block);
                }
            }
        }
        elseif ($field instanceof SuperTableField) {
            $this->items[] = new TranslateGroup('supertable', $field->name);
            $blocks = $this->source->getFieldValue($field->handle)->anyStatus()->all();

            foreach ($blocks as $block) {
                foreach ($block->type->fieldLayout->fields as $blockField) {
                    $this->_handleField($blockField, $block);
                }
            }
        }
    }
}
