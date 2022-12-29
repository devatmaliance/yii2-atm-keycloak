<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models;

use Exception;
use Yii;

final class KeycloakState
{
    public const SESSION_KEY = '_keycloak_state';

    /**
     * @return void
     * @throws Exception
     * Save State to Session
     */
    public static function init(): void
    {
        Yii::$app->session->set(self::SESSION_KEY, bin2hex(random_bytes(16)));
    }

    /**
     * @return string|null
     * get state value
     */
    public static function getValue(): ?string
    {
        return Yii::$app->session->get(self::SESSION_KEY);
    }
}