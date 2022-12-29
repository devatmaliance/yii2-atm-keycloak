<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models;

use atmaliance\yii2_keycloak\exceptions\KeycloakTokenException;
use atmaliance\yii2_keycloak\exceptions\KeycloakUserException;
use atmaliance\yii2_keycloak\models\dto\KeycloakUserInformationDTO;
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
     * @return bool
     * authenticate user
     */
    public function authenticate(KeycloakToken $keycloakToken): bool
    {
        try {
            $userInformationDTO = $this->getUserInformationDTO($keycloakToken);

            if (null === $userInformationDTO) {
                throw new KeycloakUserException('empty user information');
            }

            $auth = Yii::$app->user;
            $auth->enableSession = false;

            if (!$auth->login(Yii::$app->keycloakService->getConfiguration()->getUserInformationHandler()->handle($userInformationDTO))) {
                throw new RuntimeException('login error');
            }

            return true;
        } catch (KeycloakUserException $exception) {
        } catch (Throwable $exception) {
            Yii::error(sprintf('[Keycloak User Error] %s', $exception->getMessage()));
        }

        $keycloakToken->forget();

        return false;
    }

    /**
     * @param KeycloakToken $keycloakToken
     * @return KeycloakUserInformationDTO|null
     * get user attributes
     */
    private function getUserInformationDTO(KeycloakToken $keycloakToken): ?KeycloakUserInformationDTO
    {
        try {
            $keycloakToken = $keycloakToken->refreshTokenIfNeeded();
            $attributes = $keycloakToken->getAttributes();

            if (null === $attributes) {
                throw new KeycloakTokenException('attributes is not init');
            }

            $parsedIdToken = Yii::$app->keycloakJwt->getParser()->parse($attributes->getIdToken());

            if (!(new KeycloakIdTokenValidator())->validate($parsedIdToken)) {
                throw new KeycloakTokenException('invalid id token');
            }

            $userInformationDTO = (new KeycloakFetcher())->getUserInfo($attributes->getAccessToken());

            if (!(new KeycloakUserValidator())->validate($parsedIdToken, $userInformationDTO->getUuid())) {
                throw new KeycloakUserException('user is not owner of token');
            }

            return $userInformationDTO;
        } catch (GuzzleException $exception) {
        } catch (Throwable $exception) {
            Yii::error(sprintf('[Keycloak User Error] %s', $exception->getMessage()));
        }

        return null;
    }
}