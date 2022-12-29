<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\validator;

use Lcobucci\JWT\Token;

final class KeycloakUserValidator
{
    /**
     * @param Token $idToken
     * @param string $idUser
     * @return bool
     * Validate retrieved user is owner of token
     */
    public function validate(Token $idToken, string $idUser): bool
    {
        return $idToken->getClaim('sub') === $idUser;
    }
}