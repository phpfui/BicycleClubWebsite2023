<?php

declare(strict_types=1);

namespace Laminas\Validator;

use JsonException;
use Laminas\Translator\TranslatorInterface;

use function gettype;
use function is_float;
use function is_int;
use function is_numeric;
use function is_string;
use function json_decode;
use function str_starts_with;

use const JSON_ERROR_DEPTH;
use const JSON_THROW_ON_ERROR;

/**
 * @psalm-type OptionsArgument = array{
 *     allow?: int-mask-of<self::ALLOW_*>,
 *     maxDepth?: positive-int,
 *     messages?: array<string, string>,
 *     translator?: TranslatorInterface|null,
 *     translatorTextDomain?: string|null,
 *     translatorEnabled?: bool,
 *     valueObscured?: bool,
 * }
 */
final class IsJsonString extends AbstractValidator
{
    public const ERROR_NOT_STRING         = 'errorNotString';
    public const ERROR_TYPE_NOT_ALLOWED   = 'errorTypeNotAllowed';
    public const ERROR_MAX_DEPTH_EXCEEDED = 'errorMaxDepthExceeded';
    public const ERROR_INVALID_JSON       = 'errorInvalidJson';

    public const ALLOW_INT    = 0b0000001;
    public const ALLOW_FLOAT  = 0b0000010;
    public const ALLOW_BOOL   = 0b0000100;
    public const ALLOW_ARRAY  = 0b0001000;
    public const ALLOW_OBJECT = 0b0010000;
    public const ALLOW_ALL    = 0b0011111;

    /** @var array<string, string> */
    protected array $messageTemplates = [
        self::ERROR_NOT_STRING         => 'Expected a string but %type% was received',
        self::ERROR_TYPE_NOT_ALLOWED   => 'Received a JSON %type% but this type is not acceptable',
        self::ERROR_MAX_DEPTH_EXCEEDED => 'The decoded JSON payload exceeds the allowed depth of %maxDepth%',
        self::ERROR_INVALID_JSON       => 'An invalid JSON payload was received',
    ];

    /** @var array<string, string|array<string, string>> */
    protected array $messageVariables = [
        'type'     => 'type',
        'maxDepth' => 'maxDepth',
    ];

    protected ?string $type = null;
    /** @var int-mask-of<self::ALLOW_*> */
    private readonly int $allow;
    /** @var positive-int */
    protected readonly int $maxDepth;

    /** @param OptionsArgument $options */
    public function __construct(array $options = [])
    {
        $this->allow    = $options['allow'] ?? self::ALLOW_ALL;
        $this->maxDepth = $options['maxDepth'] ?? 512;

        unset($options['allow'], $options['maxDepth']);

        parent::__construct($options);
    }

    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            $this->error(self::ERROR_NOT_STRING);
            $this->type = gettype($value);

            return false;
        }

        if (is_numeric($value)) {
            /** @psalm-var mixed $value */
            $value = json_decode($value);

            if (is_int($value) && ! $this->isAllowed(self::ALLOW_INT)) {
                $this->error(self::ERROR_TYPE_NOT_ALLOWED);
                $this->type = 'int';

                return false;
            }

            if (is_float($value) && ! $this->isAllowed(self::ALLOW_FLOAT)) {
                $this->error(self::ERROR_TYPE_NOT_ALLOWED);
                $this->type = 'float';

                return false;
            }

            return true;
        }

        if ($value === 'true' || $value === 'false') {
            if (! $this->isAllowed(self::ALLOW_BOOL)) {
                $this->error(self::ERROR_TYPE_NOT_ALLOWED);
                $this->type = 'boolean';

                return false;
            }

            return true;
        }

        if (str_starts_with($value, '[') && ! $this->isAllowed(self::ALLOW_ARRAY)) {
            $this->error(self::ERROR_TYPE_NOT_ALLOWED);
            $this->type = 'array';

            return false;
        }

        if (str_starts_with($value, '{') && ! $this->isAllowed(self::ALLOW_OBJECT)) {
            $this->error(self::ERROR_TYPE_NOT_ALLOWED);
            $this->type = 'object';

            return false;
        }

        try {
            /** @psalm-suppress UnusedFunctionCall */
            json_decode($value, true, $this->maxDepth, JSON_THROW_ON_ERROR);

            return true;
        } catch (JsonException $e) {
            if ($e->getCode() === JSON_ERROR_DEPTH) {
                $this->error(self::ERROR_MAX_DEPTH_EXCEEDED);

                return false;
            }

            $this->error(self::ERROR_INVALID_JSON);

            return false;
        }
    }

    /** @param self::ALLOW_* $flag */
    private function isAllowed(int $flag): bool
    {
        return ($this->allow & $flag) === $flag;
    }
}
