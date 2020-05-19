<?php

namespace wsydney76\translate\models;

use benf\neo\elements\Block;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\Model;
use craft\elements\MatrixBlock;
use verbb\supertable\elements\SuperTableBlockElement;
use wsydney76 \translate\events\TranslateEvent;

class TranslateField extends Model
{

    const EVENT_GETCONTENT = 'getContent';

    public $type = 'field';
    public $entry;
    public $field;
    public $owner;
    public $targetOwner;
    public $caption = '';
    public $ownerField;
    public $isSimpleField = true;
    public $elementType = '';

    public function __construct(TranslateEntry $entry, Field $field, ElementInterface $owner = null)
    {

        $this->entry = $entry;
        $this->field = $field;
        $this->owner = $owner;
        $this->caption = $field->name;

        if ($owner) {
            if ($owner instanceof MatrixBlock) {
                $this->targetOwner = MatrixBlock::find()->id($owner->id)->siteId($entry->siteTo->id)->one();
                $this->elementType = 'matrix';
            }
            if ($owner instanceof Block) {
                $this->targetOwner = Block::find()->id($owner->id)->siteId($entry->siteTo->id)->one();
                $this->elementType = 'neo';
            }
            if ($owner instanceof SuperTableBlockElement) {
                $this->targetOwner = SuperTableBlockElement::find()->id($owner->id)->siteId($entry->siteTo->id)->one();
                $this->elementType = 'supertable';
            }
            $this->isSimpleField = false;
            $this->ownerField = Craft::$app->fields->getFieldById($owner->fieldId);
        }

        parent::__construct();
    }

    public function getSourceFormName()
    {
        return $this->getFormName() . '_from';
    }

    public function getTargetFormName()
    {
        return $this->getFormName() . '_to';
    }

    public function getFormName()
    {
        if ($this->isSimpleField) {
            return "fields[{$this->field->handle}]";
        }

        return "{$this->elementType}[{$this->ownerField->handle}][{$this->owner->id}][fields][{$this->field->handle}]";
    }

    public function getSourceValue()
    {
        if ($this->isSimpleField) {
            return $this->getValue($this->entry->source, $this->field);
        }
        return $this->getValue($this->owner, $this->field);
    }

    public function getTargetValue()
    {
        if ($this->isSimpleField) {
            return $this->getValue($this->entry->target, $this->field);
        }
        return $this->getValue($this->targetOwner, $this->field);
    }

    public function getValue(Element $element, Field $field)
    {
        $value = $element->getFieldValue($field->handle);
        if ($this->hasEventHandlers(self::EVENT_GETCONTENT)) {
            $event = new TranslateEvent([
                'element' => $element,
                'field' => $field,
                'value' => $value
            ]);
            $this->trigger(self::EVENT_GETCONTENT, $event);
            $value = $event->value;
        }

        return $value;
    }

    public function getBlockType()
    {
        if ($this->isSimpleField) {
            return null;
        }

        return [
            'name' => "matrix[{$this->ownerField->handle}][{$this->owner->id}][type]",
            'value' => $this->owner->type->handle
        ];
    }

}
