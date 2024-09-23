<?php

/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpUnused */

/** @noinspection PhpPropertyOnlyWrittenInspection */

namespace DefStudio\Telegraph\Support\Testing\Fakes;

use DefStudio\Telegraph\Concerns\FakesRequests;
use DefStudio\Telegraph\ScopedPayloads\TelegraphEditMediaPayload;
use DefStudio\Telegraph\Telegraph;

class TelegraphEditMediaFake extends TelegraphEditMediaPayload
{
    use FakesRequests;

    /**
     * @param array<string, array<mixed>> $replies
     */
    public function __construct(array $replies = [])
    {
        parent::__construct();
        $this->replies = $replies;
    }

    public static function assertSentEditMedia(string $type, string $media): void
    {
        self::assertSentData(Telegraph::ENDPOINT_EDIT_MEDIA, [
            "media" => json_encode([
                'type' => $type,
                'media' => $media,
            ]),
        ], false);
    }
}
