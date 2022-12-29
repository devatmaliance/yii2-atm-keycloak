<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\validator;

use atmaliance\yii2_keycloak\exceptions\KeycloakFetcherException;
use atmaliance\yii2_keycloak\models\KeycloakFetcher;
use sizeg\jwt\JwtValidationData;
use Yii;

final class KeycloakJwtValidator extends JwtValidationData
{
    /**
     * @return void
     * @throws KeycloakFetcherException
     */
    public function init(): void
    {
        $this->validationData->setIssuer((new KeycloakFetcher())->getOpenIdValue('issuer'));
        $this->validationData->setAudience(Yii::$app->keycloakService->getConfiguration()->getClientId());

        parent::init();
    }
}