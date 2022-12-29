<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\serializer;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as MainSerializer;

final class Serializer
{
    public function serialize($data, string $format = 'json', array $context = []): string
    {
        return $this->getSerializer()->serialize($data, $format, $context);
    }

    public function deserialize($data, string $type, string $format = 'json', array $context = [])
    {
        return $this->getSerializer()->deserialize($data, $type, $format, $context);
    }

    private function getSerializer(): MainSerializer
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        return new MainSerializer([
            new ArrayDenormalizer(),
            new DateTimeNormalizer(),
            new DateTimeZoneNormalizer(),
            new ObjectNormalizer(
                $classMetadataFactory,
                $metadataAwareNameConverter,
                PropertyAccess::createPropertyAccessorBuilder()->disableMagicMethods()->getPropertyAccessor(),
                new PropertyInfoExtractor([],
                    [
                        new PhpDocExtractor(),
                        new ReflectionExtractor(),
                    ])
            ),
        ], [
            new JsonEncoder(),
        ]);
    }
}
