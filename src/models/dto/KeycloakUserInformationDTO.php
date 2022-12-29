<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\dto;

use Symfony\Component\Serializer\Annotation\SerializedName;

final class KeycloakUserInformationDTO
{
    private string $email;

    /**
     * @SerializedName("sub")
     */
    private string $uuid;

    /**
     * @SerializedName("email_verified")
     */
    private bool $emailVerified;

    /**
     * @SerializedName("name")
     */
    private string $fullName;

    /**
     * @SerializedName("preferred_username")
     */
    private string $username;

    /**
     * @SerializedName("given_name")
     */
    private string $name;

    /**
     * @SerializedName("family_name")
     */
    private string $surname;

    public function __construct(
        string $uuid,
        string $username,
        string $name,
        string $surname,
        string $fullName,
        string $email,
        bool $emailVerified
    ) {
        $this->uuid = $uuid;
        $this->username = $username;
        $this->name = $name;
        $this->surname = $surname;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->emailVerified = $emailVerified;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }
}