<?php

use atmaliance\yii2_keycloak\KeycloakService;
use sizeg\jwt\Jwt;
use yii\BaseYii;
use yii\web\User;

/**
 * Yii bootstrap file.
 * Used for enhanced IDE code autocompletion.
 */
final class Yii extends BaseYii
{
    /**
     * @var BaseApplication
     */
    public static $app;
}

/**
 * @property User $user
 * @property KeycloakService $keycloakService
 * @property Jwt $keycloakJwt
 */
abstract class BaseApplication extends yii\base\Application
{
}
