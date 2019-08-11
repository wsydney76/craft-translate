<?php

namespace wsydney76\translate\services;

use Craft;
use craft\base\Component;
use craft\base\Element;
use craft\elements\Entry;
use craft\elements\MatrixBlock;
use craft\errors\ElementNotFoundException;
use Throwable;
use verbb\supertable\elements\db\SuperTableBlockQuery;
use verbb\supertable\elements\SuperTableBlockElement;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use function array_key_exists;

class TranslateService extends Component
{

    /**
     * @param Entry $entry
     * @return bool
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function saveTranslationFromRequest(Entry $entry)
    {
        $app = Craft::$app;
        $params = $app->request->getBodyParams();

        $entry->title = $params['title'];
        $entry->slug = '';

        $supertable = $app->request->getBodyParam('supertable');
        if ($supertable) {
            $this->_saveBlockElements($entry, $params['siteId'], $supertable, 'supertable');
        }

        $matrix = $app->request->getBodyParam('matrix');
        if ($matrix) {
            $this->_saveBlockElements($entry, $params['siteId'], $matrix, 'matrix');
        }

        $fields = $app->request->getBodyParam('fields');
        if ($fields) {
            foreach ($params['fields'] as $handle => $value) {
                $entry->$handle = $value;
            }
        }

        if (!$entry->isDraft) {
            $entry->scenario = Element::SCENARIO_LIVE;
        }
        return $app->elements->saveElement($entry);
    }

    /**
     * @param Entry $entry
     * @param $siteId
     * @param $params
     * @param $type
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     */
    private function _saveBlockElements(Entry $entry, $siteId, $params, $type)
    {
        $app = Craft::$app;
        foreach ($params as $fields) {
            foreach ($fields as $blockId => $blockParams) {
                if ($type == 'supertable') {
                    /** @var SuperTableBlockQuery $query */
                    $query = SuperTableBlockElement::find();
                    $block = $query->ownerId($entry->id)->id($blockId)->siteId($siteId)->one();
                } elseif ($type == 'matrix') {
                    $block = MatrixBlock::find()->ownerId($entry->id)->id($blockId)->siteId($siteId)->one();
                }

                // \Craft::dd($block);
                if (array_key_exists('fields', $blockParams)) {
                    foreach ($blockParams['fields'] as $handle => $value) {
                        /** @noinspection PhpUndefinedVariableInspection */
                        $block->$handle = $value;
                        $block->scenario = Element::SCENARIO_LIVE;
                        if (!$app->elements->saveElement($block)) {
                            // TODO: Proper error handling
                            $entry->addError($handle, 'Error in block ' . $block->id . ': ' . $block->getErrorSummary(true)[0]);
                        }
                    }
                }
            }
        }
    }
}
