<?php

namespace wsydney76\translate;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use wsydney76\translate\models\SettingsModel;
use wsydney76\translate\services\TranslateService;
use yii\base\Event;

/**
 *
 * @property TranslateService $translate
 *
 */
class Translate extends Plugin
{

    public static $plugin;

    /**
     * Initializes the module.
     */
    public function init()
    {
        self::$plugin = $this;

        // Set the controllerNamespace based on whether this is a console or web request
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'wsydney76\\translate\\console\\controllers';
        } else {
            $this->controllerNamespace = 'wsydney76\\translate\\controllers';
        }

        parent::init();

        // Set services
        $this->setComponents([
            'translate' => TranslateService::class
        ]);

        // Set Routes

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            //$event->rules['translate/translate'] = 'translate/element/translate';
            $event->rules['translate/translateentry/<entryId:[\d]+>/<siteFromId:[\d]+>/<siteToId:[\d]+>'] = 'translate/translate/translate-entry';
            // \Craft::dd($event->rules);
        });

        // Create Permissions
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions['Translate Plugin'] = [
                    'accessPlugin-translate' => [
                        'label' => 'Use Translation Tools',
                    ]
                ];
            }
        );

        // Register Edit Screen extensions
        Craft::$app->view->hook('cp.entries.edit.settings', function(&$context) {
            if ($context['entry'] != null) {
                return Craft::$app->view->renderTemplate('translate/translatebutton.twig', ['entry' => $context['entry']]);
            }
        });

    }

   protected function createSettingsModel()
   {
       return new SettingsModel();
   }

}
