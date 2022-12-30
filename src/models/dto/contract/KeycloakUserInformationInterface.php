<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\dto\contract;

interface KeycloakUserInformationInterface
{
    public function getUuid(): string;
}