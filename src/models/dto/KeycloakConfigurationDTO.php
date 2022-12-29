<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\dto;

use atmaliance\yii2_keycloak\models\handler\KeycloakUserInformationHandlerInterface;
use yii\helpers\Url;

final class KeycloakConfigurationDTO
{
    private KeycloakUserInformationHandlerInterface $userInformationHandler;
    private string $baseUrl;
    private string $realm;
    private string $callbackUrl;
    private string $clientId;
    private string $clientSecret;
    private bool $isNeedToCacheOpenidConfiguration;
    private bool $canExtractUserInformationInAccessToken;

    public function __construct(
        KeycloakUserInformationHandlerInterface $userInformationHandler,
        string $baseUrl,
        string $realm,
        string $clientId,
        string $clientSecret,
        string $callbackUrl = '/keycloak/auth/callback',
        bool $isNeedToCacheOpenidConfiguration = true,
        bool $canExtractUserInformationInAccessToken = true
    ) {
        $this->userInformationHandler = $userInformationHandler;
        $this->baseUrl = $baseUrl;
        $this->realm = $realm;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->callbackUrl = $callbackUrl;
        $this->isNeedToCacheOpenidConfiguration = $isNeedToCacheOpenidConfiguration;
        $this->canExtractUserInformationInAccessToken = $canExtractUserInformationInAccessToken;
    }

    public function getUserInformationHandler(): KeycloakUserInformationHandlerInterface
    {
        return $this->userInformationHandler;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getRealm(): string
    {
        return $this->realm;
    }

    public function getCallbackUrl(): string
    {
        return Url::to($this->callbackUrl, true);
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function isNeedToCacheOpenidConfiguration(): bool
    {
        return $this->isNeedToCacheOpenidConfiguration;
    }

    public function canExtractUserInformationInAccessToken(): bool
    {
        return $this->canExtractUserInformationInAccessToken;
    }
}