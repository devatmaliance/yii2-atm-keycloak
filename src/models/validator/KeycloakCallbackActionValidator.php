<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\validator;

use atmaliance\yii2_keycloak\models\dto\KeycloakCallbackActionDTO;

final class KeycloakCallbackActionValidator
{
    private array $errors = [];

    public function validate(KeycloakCallbackActionDTO $keycloakCallbackActionDTO): bool
    {
        // Check for errors from Keycloak
        if (!empty($keycloakCallbackActionDTO->getError())) {
            $this->errors[] = $keycloakCallbackActionDTO->getErrorDescription() ?? $keycloakCallbackActionDTO->getError();
        }

        // Check given state to mitigate CSRF attack
        if (!((new KeycloakStateValidator())->validate($keycloakCallbackActionDTO->getState()))) {
            $this->errors[] = 'Invalid state';
        }

        return empty($this->getErrors());
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}