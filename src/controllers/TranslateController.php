<?php

namespace wsydney76\translate\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use wsydney76\translate\models\TranslateEntry;
use wsydney76\translate\Translate;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TranslateController extends Controller
{
    protected $allowAnonymous = true;

    // http://temp1.local/admin/translate/translateentry/2185/1/2
    public function actionTranslateEntry($entryId, $siteFromId, $siteToId) {


        $entry = new TranslateEntry($entryId, $siteFromId, $siteToId);

        if (!$entry->entryFrom || !$entry->entryTo) {
            throw  new NotFoundHttpException();
        }

        Craft::dd($entry);
    }


    /**
     * @return NotFoundHttpException|Response|null
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionSaveEntry()
    {
        $this->requirePermission('accessPlugin-translate');

        $app = Craft::$app;
        $params = $app->request->getBodyParams();

        $query = Entry::find()->id($params['entryId'])->anyStatus()->siteId($params['siteId']);
        if ($params['isDraft']) {
            $query = $query->drafts(true);
        }

        $entry = $query->one();

        if (!$entry) {
            return new NotFoundHttpException();
        }

        //Craft::dd($params);

        $success = Translate::$plugin->translate->saveTranslationFromRequest($entry);

        if (!$success) {
            $app->session->setError('Could not save entry');
            $app->urlManager->setRouteParams([
                'entry' => $entry
            ]);
            return null;
        }

        $app->session->setNotice('Translations saved');
        return $this->redirectToPostedUrl();
    }



}
