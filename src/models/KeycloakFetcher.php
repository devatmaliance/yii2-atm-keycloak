<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models;

use atmaliance\yii2_keycloak\exceptions\KeycloakFetcherException;
use atmaliance\yii2_keycloak\exceptions\KeycloakTokenException;
use atmaliance\yii2_keycloak\exceptions\KeycloakUserException;
use atmaliance\yii2_keycloak\models\dto\KeycloakTokenAttributeDTO;
use atmaliance\yii2_keycloak\models\dto\KeycloakUserInformationDTO;
use atmaliance\yii2_keycloak\models\serializer\Normalizer;
use atmaliance\yii2_keycloak\models\serializer\Serializer;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Lcobucci\JWT\Claim;
use Throwable;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

final class KeycloakFetcher
{
    private Client $httpClient;
    private const SUCCESS_CODE = 200;
    private const CACHE_OPENID_CONFIGURATION_DURATION = 604800;

    public function __construct()
    {
        $this->httpClient = new Client();
    }

    /**
     * @param string $key
     * @return mixed
     * @throws KeycloakFetcherException
     * @throws Exception
     * Return a value from the Open ID Configuration
     */
    public function getOpenIdValue(string $key)
    {
        static $openIdConfiguration;
        $openIdConfiguration = $openIdConfiguration ?: $this->getOpenidConfiguration();

        return ArrayHelper::getValue($openIdConfiguration, $key);
    }

    /**
     * @return array
     * @throws KeycloakFetcherException
     * Retrieve OpenId Endpoints
     */
    public function getOpenidConfiguration(): array
    {
        try {
            $cacheKey = sprintf(
                '%s-%s-%s',
                'keycloak_web_guard_openid',
                Yii::$app->keycloakService->getConfiguration()->getRealm(),
                md5(Yii::$app->keycloakService->getConfiguration()->getBaseUrl())
            );

            if (Yii::$app->keycloakService->getConfiguration()->isNeedToCacheOpenidConfiguration()) {
                $openidConfiguration = Yii::$app->cache->get($cacheKey);

                if (!empty($openidConfiguration)) {
                    return $openidConfiguration;
                }
            }

            $response = $this->httpClient->request(
                'GET',
                sprintf('%s/realms/%s/.well-known/openid-configuration', Yii::$app->keycloakService->getConfiguration()->getBaseUrl(), Yii::$app->keycloakService->getConfiguration()->getRealm())
            );

            if ($response->getStatusCode() !== self::SUCCESS_CODE) {
                throw new KeycloakFetcherException(
                    sprintf(
                        'Expected status %s but got: %s. Details: %s',
                        self::SUCCESS_CODE,
                        $response->getStatusCode(),
                        $response->getBody()->getContents()
                    )
                );
            }

            $openidConfiguration = Json::decode($response->getBody()->getContents());

            if (Yii::$app->keycloakService->getConfiguration()->isNeedToCacheOpenidConfiguration()) {
                Yii::$app->cache->set($cacheKey, $openidConfiguration, self::CACHE_OPENID_CONFIGURATION_DURATION);
            }

            return $openidConfiguration;
        } catch (Throwable $exception) {
            throw new KeycloakFetcherException(sprintf('[Keycloak Fetcher Error] It was not possible to load OpenId configuration: %s', $exception->getMessage()));
        }
    }

    /**
     * @param string $code
     * @return KeycloakTokenAttributeDTO
     * @throws GuzzleException
     * @throws KeycloakTokenException
     * @throws KeycloakFetcherException
     * Get tokens from Code
     */
    public function getTokenInformation(string $code): KeycloakTokenAttributeDTO
    {
        $response = $this->httpClient->request('POST', $this->getOpenIdValue('token_endpoint'), [
            'form_params' => [
                'code' => $code,
                'grant_type' => 'authorization_code',
                'client_id' => Yii::$app->keycloakService->getConfiguration()->getClientId(),
                'redirect_uri' => Yii::$app->keycloakService->getConfiguration()->getCallbackUrl(),
                'client_secret' => Yii::$app->keycloakService->getConfiguration()->getClientSecret(),
            ],
        ]);

        if ($response->getStatusCode() !== self::SUCCESS_CODE) {
            throw new KeycloakTokenException(
                sprintf(
                    'Failed to get token. Expected status %s but got: %s. Details: %s',
                    self::SUCCESS_CODE,
                    $response->getStatusCode(),
                    $response->getBody()->getContents()
                )
            );
        }

        return (new Serializer())->deserialize($response->getBody()->getContents(), KeycloakTokenAttributeDTO::class);
    }

    /**
     * @param string $accessToken
     * @return KeycloakUserInformationDTO
     * @throws GuzzleException
     * @throws KeycloakFetcherException
     * @throws KeycloakUserException|
     * @throws ExceptionInterface
     * Get user attributes
     */
    public function getUserInfo(string $accessToken): KeycloakUserInformationDTO
    {
        if (Yii::$app->keycloakService->getConfiguration()->canExtractUserInformationInAccessToken()) {
            $userInfo = [];

            foreach (Yii::$app->keycloakJwt->getParser()->parse($accessToken)->getClaims() as $claim) {
                /** @var Claim $claim */
                $userInfo[$claim->getName()] = $claim->getValue();
            }

            return (new Normalizer())->denormalize($userInfo, KeycloakUserInformationDTO::class);
        }

        $response = $this->httpClient->request('GET', $this->getOpenIdValue('userinfo_endpoint'), [
            'headers' => [
                'Authorization' => "Bearer $accessToken",
                'Accept' => 'application/json',
            ],
        ]);

        if ($response->getStatusCode() !== self::SUCCESS_CODE) {
            throw new KeycloakUserException(
                sprintf(
                    'Was not able to get userinfo. Expected status %s but got: %s. Details: %s',
                    self::SUCCESS_CODE,
                    $response->getStatusCode(),
                    $response->getBody()->getContents()
                )
            );
        }

        return (new Serializer())->deserialize($response->getBody()->getContents(), KeycloakUserInformationDTO::class);
    }
}