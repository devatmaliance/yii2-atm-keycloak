<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models;

use Exception;
use Yii;
use yii\helpers\Url;

final class KeycloakUrl
{
    /**
     * @return string
     * @throws Exception
     * Return the login URL
     * @link https://openid.net/specs/openid-connect-core-1_0.html#CodeFlowAuth
     */
    public function getLoginUrl(): string
    {
        return $this->buildUrl((new KeycloakFetcher())->getOpenIdValue('authorization_endpoint'), array_filter([
            'scope' => 'openid',
            'response_type' => 'code',
            'client_id' => Yii::$app->keycloakService->getConfiguration()->getClientId(),
            'redirect_uri' => Yii::$app->keycloakService->getConfiguration()->getCallbackUrl(),
            'state' => KeycloakState::getValue(),
        ]));
    }

    /**
     * @return string
     * @throws Exception
     * Return the logout URL
     */
    public function getLogoutUrl(): string
    {
        return $this->buildUrl(
            (new KeycloakFetcher())->getOpenIdValue('end_session_endpoint'),
            array_filter([
                'client_id' => Yii::$app->keycloakService->getConfiguration()->getClientId(),
                'post_logout_redirect_uri' => Url::home(true),
                'id_token_hint' => null !== Yii::$app->keycloakService->getToken()->getAttributes() ? Yii::$app->keycloakService->getToken()->getAttributes()->getIdToken() : null,
            ])
        );
    }

    /**
     * @return string
     * @throws Exception
     * Return the register URL
     */
    public function getRegisterUrl(): string
    {
        return str_replace('/auth?', '/registrations?', $this->getLoginUrl());
    }

    /**
     * @param string $url
     * @param array $params
     * @return string
     * Build a URL with params
     */
    private function buildUrl(string $url, array $params): string
    {
        $lastChar = mb_substr($url, -1);

        if ($lastChar === '/') {
            $url = substr($url,0,-1);
        }

        if (empty($params)) {
            return $url;
        }

        return sprintf('%s?%s', $url, http_build_query($params));
    }
}