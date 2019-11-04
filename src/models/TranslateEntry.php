<?php

namespace wsydney76\translate\models;

use Craft;
use craft\base\Field;
use craft\base\Model;
use craft\elements\Entry;
use yii\web\NotFoundHttpException;
use function get_class;
use function in_array;

class TranslateEntry extends Model
{
    public $entryFrom;
    public $entryTo;
    public $siteFrom;
    public $siteTo;

    public $fields = [];

    public function __construct($entryId, $siteFromId, $siteToId)
    {
        $this->entryFrom = Entry::find()->id($entryId)->anyStatus()->drafts(true)->siteId($siteFromId)->one();
        $this->entryTo = Entry::find()->id($entryId)->anyStatus()->drafts(true)->siteId($siteToId)->one();

        $this->siteFrom = Craft::$app->sites->getSiteById($siteFromId);
        $this->siteTo = Craft::$app->sites->getSiteById($siteToId);

        parent::__construct();
    }

    public function init()
    {
        if (!$this->entryFrom || !$this->entryTo) {
            return null;
        }

        foreach ($this->entryFrom->fieldLayout->tabs as $tab) {
            /** @var Field $field */
            foreach ($tab->fields as $field) {
                $this->_handleField($field);
                if (get_class($field) == 'craft\\fields\\Matrix') {
                    $this->fields[] = $field;
                }
            }
        }

        Craft::dd($this->fields);
    }

    protected function _handleField($field) {
        if ($field->hasContentColumn()) {
            if ($field->getContentColumnType() == 'string' || $field->getContentColumnType() == 'text') {
                if ($field->translationMethod == 'site' || $field->translationMethod == 'language') {
                    $this->fields[] = $field;
                }
            }
        }
    }
}
