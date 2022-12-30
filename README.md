ATM Yii2 Keycloak
=====================

Что нужно сделать?

Необходимо добавить код в следующих местах:

Файл `common/config/main-local.php`

```php
        'keycloakService' => [
            'baseUrl' => 'http://localhost:8180',
            'realm' => 'myRealm',
            'clientId' => 'myClientId',
            'clientSecret' => '6HB20p1vIw0tDB3uaaXxkxXs5l0JOgRu',
            'callbackUrl' => '/keycloak/auth/callback',
            'userInformationHandler' => new myUserInformationHandler(),
            'userInformationDTOClass' => myUserInformationDTOClass::class,
        ]
```

**Примечания**
1. Ожидается, что `userInformationHandler` будет реализовывать `atmaliance\yii2_keycloak\models\handler\KeycloakUserInformationHandlerInterface`
2. Ожидается, что `userInformationDTOClass` будет реализовывать `atmaliance\yii2_keycloak\models\dto\contract\KeycloakUserInformationInterface`

Файл `config/main.php`

```php
    'bootstrap' => [
        KeycloakBootstrap::class,
    ]
```

Файл `common/config/params.php`
```php
    'keycloakEnabled' => true,
```