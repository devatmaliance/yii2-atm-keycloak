<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models;

use atmaliance\yii2_keycloak\exceptions\KeycloakFetcherException;
use atmaliance\yii2_keycloak\exceptions\KeycloakTokenException;
use atmaliance\yii2_keycloak\models\dto\KeycloakTokenAttributeDTO;
use atmaliance\yii2_keycloak\models\serializer\Serializer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Throwable;
use Yii;

final class KeycloakToken
{
    public const SESSION_KEY = '_keycloak_token';
    private const SUCCESS_CODE = 200;

    /**
     * @param KeycloakTokenAttributeDTO $keycloakTokenDTO
     * @return $this
     * set attributes
     */
    public function init(KeycloakTokenAttributeDTO $keycloakTokenDTO): self
    {
        Yii::$app->session->set(self::SESSION_KEY, (new Serializer())->serialize($keycloakTokenDTO));

        return $this;
    }

    /**
     * @return bool
     * check for existence of attributes
     */
    public function isInit(): bool
    {
        return null !== $this->getAttributes();
    }

    /**
     * @return KeycloakTokenAttributeDTO|null
     * get tokens from session
     */
    public function getAttributes(): ?KeycloakTokenAttributeDTO
    {
        try {
            $sessionAttributes = Yii::$app->session->get(self::SESSION_KEY);

            if (empty($sessionAttributes)) {
                return null;
            }

            return (new Serializer())->deserialize($sessionAttributes, KeycloakTokenAttributeDTO::class);
        } catch (Throwable $exception) {
            Yii::error(sprintf('[Keycloak Token Error] %s', $exception->getMessage()));
        }

        return null;
    }

    /**
     * @return void
     * Remove token from session
     */
    public function forget(): void
    {
        Yii::$app->session->remove(self::SESSION_KEY);
    }

    /**
     * @return bool
     * Check token has expired
     */
    public function hasExpired(string $token): bool
    {
        return Yii::$app->keycloakJwt->getParser()->parse($token)->isExpired();
    }

    /**
     * @return $this
     * @throws GuzzleException
     * @throws KeycloakTokenException
     * @throws KeycloakFetcherException
     * Check we need to refresh token and refresh if needed
     */
    public function refreshTokenIfNeeded(): self
    {
        $attributes = $this->getAttributes();

        if (null === $attributes) {
            throw new KeycloakTokenException('attributes is not init');
        }

        if (!$this->hasExpired($attributes->getAccessToken())) {
            return $this;
        }

        return $this->init($this->refreshAccessToken($attributes->getRefreshToken()));
    }

    /**
     * @param string $refreshToken
     * @return KeycloakTokenAttributeDTO
     * @throws GuzzleException
     * @throws KeycloakFetcherException
     * @throws KeycloakTokenException
     * Refresh access token
     */
    public function refreshAccessToken(string $refreshToken): KeycloakTokenAttributeDTO
    {
        $response = (new Client())->request('POST', (new KeycloakFetcher())->getOpenIdValue('token_endpoint'), [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => Yii::$app->keycloakService->getConfiguration()->getClientId(),
                'redirect_uri' => Yii::$app->keycloakService->getConfiguration()->getCallbackUrl(),
                'client_secret' => Yii::$app->keycloakService->getConfiguration()->getClientSecret(),
            ],
        ]);

        if ($response->getStatusCode() !== self::SUCCESS_CODE) {
            throw new KeycloakTokenException(
                sprintf(
                    'Failed to refresh token. Expected status %s but got: %s. Details: %s',
                    self::SUCCESS_CODE,
                    $response->getStatusCode(),
                    $response->getBody()->getContents()
                )
            );
        }

        return (new Serializer())->deserialize($response->getBody()->getContents(), KeycloakTokenAttributeDTO::class);
    }
}