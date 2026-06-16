<?php

namespace App\Enums;

enum ContentBlockType: string
{
    case HEADING = 'heading';
    case PARAGRAPH = 'paragraph';
    case IMAGE = 'image';
    case QUOTE = 'quote';
    case LIST = 'list';
    case CODE = 'code';
    case FAQ = 'faq';
    case CALLOUT = 'callout';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $type): string => $type->value,
            self::cases(),
        );
    }
}
