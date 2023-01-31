<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\dto;

use Symfony\Component\Serializer\Annotation\SerializedName;

final class KeycloakCallbackActionDTO
{
    private ?string $code;
    private ?string $state;
    private ?string $error;

    /**
     * @SerializedName("error_description")
     */
    private ?string $errorDescription;

    public function __construct(
        ?string $code,
        ?string $state,
        ?string $error,
        ?string $errorDescription
    ) {
        $this->code = $code;
        $this->state = $state;
        $this->error = $error;
        $this->errorDescription = $errorDescription;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getErrorDescription(): ?string
    {
        return $this->errorDescription;
    }
}