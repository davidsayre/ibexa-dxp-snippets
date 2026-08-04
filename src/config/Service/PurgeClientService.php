<?php


use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;

class PurgeClientService
{
    protected PurgeClientInterface $purgeClient;

    public function __construct(
        PurgeClientInterface $purgeClient
    ) {
        $this->purgeClient = $purgeClient;
    }

    public function getClient(): ?PurgeClientInterface
    {
        return $this->purgeClient;
    }
}

?>