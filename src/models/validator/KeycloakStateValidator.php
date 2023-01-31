<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\validator;

use atmaliance\yii2_keycloak\models\KeycloakState;

final class KeycloakStateValidator
{
    public function validate(?string $incomingState): bool
    {
        $currentState = KeycloakState::getValue();

        return !empty($currentState) && !empty($incomingState) && $incomingState === $currentState;
    }
}