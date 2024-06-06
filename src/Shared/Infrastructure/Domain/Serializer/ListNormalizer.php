<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer;

use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractList;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ListNormalizer extends AbstractValueObjectNormalizer implements NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private const string KEY_TYPE = 'type';

    private const string KEY_ELEMENT_TYPE = 'element_type';

    private const string KEY_ELEMENTS = 'elements';

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): AbstractList {
        if (isset($data[self::KEY_ELEMENTS])) {
            $elements = array_map(
                fn ($element) => $this->denormalizer->denormalize(
                    data: $element,
                    type: $data[self::KEY_ELEMENT_TYPE],
                    format: $format,
                    context: $context,
                ),
                $data[self::KEY_ELEMENTS],
            );
        } else {
            $elements = [];
        }

        return new $data[self::KEY_TYPE](...$elements);
    }

    /**
     * @param AbstractList $object
     */
    public function normalize(
        mixed $object,
        ?string $format = null,
        array $context = [],
    ): array {
        $elements = $object->toArray();

        $normalized = [
            self::KEY_TYPE => $object::class,
        ];

        if (!empty($elements)) {
            $normalized[self::KEY_ELEMENT_TYPE] = $elements[array_key_first($elements)]::class;
            $normalized[self::KEY_ELEMENTS] = array_map(
                fn ($element) => $this->normalizer->normalize($element, $format, $context),
                $elements,
            );
        }

        return $normalized;
    }

    protected function getSupportedType(): string
    {
        return AbstractList::class;
    }
}
