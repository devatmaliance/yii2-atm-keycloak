<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models;

use atmaliance\yii2_keycloak\exceptions\KeycloakFetcherException;
use atmaliance\yii2_keycloak\exceptions\KeycloakTokenException;
use atmaliance\yii2_keycloak\exceptions\KeycloakUserException;
use atmaliance\yii2_keycloak\models\dto\contract\KeycloakUserInformationInterface;
use atmaliance\yii2_keycloak\models\validator\KeycloakIdTokenValidator;
use atmaliance\yii2_keycloak\models\validator\KeycloakUserValidator;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use Throwable;
use Yii;

final class KeycloakAuth
{
    /**
     * @param KeycloakToken $keycloakToken
     * @return void
     */
    public function authenticate(KeycloakToken $keycloakToken): void
    {
        try {
            $userInformationDTO = $this->getUserInformationDTO($keycloakToken);
            $user = Yii::$app->keycloakService->getConfiguration()->getUserInformationHandler()->handle($userInformationDTO);

            if (!Yii::$app->user->login($user)) {
                throw new RuntimeException('login error');
            }

            return;
        } catch (Throwable $exception) {
            Yii::error(sprintf('[Keycloak User Error] %s', $exception->getMessage()));
        }

        $keycloakToken->forget();
        throw new RuntimeException('login error');
    }

    /**
     * @param KeycloakToken $keycloakToken
     * @return KeycloakUserInformationInterface
     * @throws KeycloakTokenException
     * @throws KeycloakUserException
     * @throws \ReflectionException
     * @throws KeycloakFetcherException
     */
    private function getUserInformationDTO(KeycloakToken $keycloakToken): KeycloakUserInformationInterface
    {
        $keycloakToken = $keycloakToken->refreshTokenIfNeeded();
        $attributes = $keycloakToken->getAttributes();

        if (!$attributes) {
            throw new KeycloakTokenException('attributes is not init');
        }

        $parsedIdToken = Yii::$app->keycloakJwt->getParser()->parse($attributes->getIdToken());

        if (!(new KeycloakIdTokenValidator())->validate($parsedIdToken)) {
            throw new KeycloakTokenException('invalid id token');
        }

        /* @todo $attributes->getAccessToken() заменить на $attributes->getIdToken() */
        $userInformationDTO = (new KeycloakFetcher())->getUserInfo($attributes->getAccessToken());

        if (!(new KeycloakUserValidator())->validate($parsedIdToken, $userInformationDTO->getUuid())) {
            throw new KeycloakUserException('user is not owner of token');
        }

        return $userInformationDTO;
    }
}