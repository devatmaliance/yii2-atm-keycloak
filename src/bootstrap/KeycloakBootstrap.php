<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\bootstrap;

use atmaliance\yii2_keycloak\KeycloakService;
use atmaliance\yii2_keycloak\models\KeycloakAuth;
use Throwable;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;

class KeycloakBootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     * @return void
     */
    public function bootstrap($app): void
    {
        if (Yii::$app->params['keycloakEnabled'] !== true) {
            return;
        }

        if (!$app->user->isGuest) {
            return;
        }

        /* @var KeycloakService $keycloakService */
        $keycloakService = $app->keycloakService ?? null;

        if (null === $keycloakService) {
            return;
        }

        try {
            if (!$keycloakService->getToken()->isInit()) {
                return;
            }

            (new KeycloakAuth())->authenticate($keycloakService->getToken());
        } catch (Throwable $exception) {
            Yii::error(sprintf('[Keycloak Behavior Error] %s', $exception->getMessage()));
            $keycloakService->getToken()->forget();
        }
    }
}