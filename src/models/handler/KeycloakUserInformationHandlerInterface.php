<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\handler;

use atmaliance\yii2_keycloak\models\dto\contract\KeycloakUserInformationInterface;
use yii\web\IdentityInterface;

interface KeycloakUserInformationHandlerInterface
{
    public function handle(KeycloakUserInformationInterface $userInformationDTO): IdentityInterface;
}