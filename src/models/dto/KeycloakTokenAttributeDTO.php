<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\dto;

use Symfony\Component\Serializer\Annotation\SerializedName;

final class KeycloakTokenAttributeDTO
{
    /**
     * @SerializedName("access_token")
     */
    private string $accessToken;

    /**
     * @SerializedName("refresh_token")
     */
    private string $refreshToken;

    /**
     * @SerializedName("id_token")
     */
    private string $idToken;

    public function __construct(
        string $accessToken,
        string $refreshToken,
        string $idToken
    ) {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->idToken = $idToken;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getIdToken(): string
    {
        return $this->idToken;
    }
}