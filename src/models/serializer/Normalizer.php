<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\models\serializer;

use ArrayObject;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

final class Normalizer
{
    /**
     * @param $data
     * @param string|null $format
     * @param array $context
     * @return array|ArrayObject|bool|float|int|mixed|string|null
     * @throws ExceptionInterface
     */
    public function normalize($data, string $format = null, array $context = [])
    {
        return $this->getSerializer()->normalize($data, $format, $context);
    }

    /**
     * @param $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @return mixed
     * @throws ExceptionInterface
     */
    public function denormalize($data, string $type, string $format = null, array $context = [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true])
    {
        return $this->getSerializer()->denormalize($data, $type, $format, $context);
    }

    private function getSerializer(): Serializer
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        $normalizers = [
            new ArrayDenormalizer(),
            new DateTimeNormalizer(),
            new ObjectNormalizer(
                $classMetadataFactory, $metadataAwareNameConverter, PropertyAccess::createPropertyAccessorBuilder()->disableMagicMethods()->getPropertyAccessor(), new PropertyInfoExtractor(
                    [],
                    [
                        new PhpDocExtractor(),
                        new ReflectionExtractor(),
                    ]
                )
            ),
        ];

        return new Serializer($normalizers);
    }
}
