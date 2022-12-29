<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\validator;

use Lcobucci\JWT\Token;
use Yii;

class KeycloakIdTokenValidator
{
    /**
     * @param Token $idToken
     * @return bool
     * Validate id token
     * @link https://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
     */
    public function validate(Token $idToken): bool
    {
        return !$idToken->isExpired() && $idToken->validate(Yii::$app->keycloakJwt->getValidationData());
    }
}