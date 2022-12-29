<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak;

use atmaliance\yii2_keycloak\models\dto\KeycloakConfigurationDTO;
use atmaliance\yii2_keycloak\models\KeycloakToken;
use atmaliance\yii2_keycloak\models\serializer\Normalizer;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use yii\base\Configurable;

class KeycloakService implements Configurable
{
    private KeycloakConfigurationDTO $configuration;
    private KeycloakToken $token;

    /**
     * @param array $config
     * @throws ExceptionInterface
     */
    public function __construct(array $config = [])
    {
        $this->configuration = (new Normalizer())->denormalize($config, KeycloakConfigurationDTO::class);
        $this->token = new KeycloakToken();
    }

    final public function getConfiguration(): KeycloakConfigurationDTO
    {
        return $this->configuration;
    }

    final public function getToken(): KeycloakToken
    {
        return $this->token;
    }
}