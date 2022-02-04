<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelExtensions\Normalizer;

use Fusonic\HttpKernelExtensions\Exception\ConstraintViolationException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * A normalizer for {@see ConstraintViolationException}.
 * It uses the Symfony ConstraintViolationListNormalizer {@see \Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer}
 * and enhances it with an error name and the message template.
 */
final class ConstraintViolationExceptionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(
        private ConstraintViolationListNormalizer $constraintViolationListNormalizer
    ) {
    }

    /**
     * @param ConstraintViolationException $object
     * @param array<mixed>                 $context
     *
     * @return array<string, mixed>
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $constraintViolationList = $object->getConstraintViolationList();
        $normalized = $this->constraintViolationListNormalizer->normalize($constraintViolationList, $format, $context);

        if (!isset($normalized['violations'])) {
            return $normalized;
        }

        foreach ($normalized['violations'] as $index => $normalizedViolation) {
            /** @var ConstraintViolation $violation */
            foreach ($constraintViolationList as $violation) {
                if (isset($normalizedViolation['title']) && $violation->getMessage(
                    ) === $normalizedViolation['title']) {
                    $normalized['violations'][$index]['messageTemplate'] = $violation->getMessageTemplate();

                    $constraint = $violation->getConstraint();
                    $code = $violation->getCode();

                    if ($constraint && $code) {
                        /** @var class-string<\Symfony\Component\Validator\Constraint> $constraintClass */
                        $constraintClass = $constraint::class;
                        $normalized['violations'][$index]['errorName'] = $constraintClass::getErrorName($code);
                    }
                }
            }
        }

        return $normalized;
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof ConstraintViolationException;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return __CLASS__ === ConstraintViolationExceptionNormalizer::class;
    }
}