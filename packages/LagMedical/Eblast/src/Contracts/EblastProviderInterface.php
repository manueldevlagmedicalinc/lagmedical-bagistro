<?php

namespace LagMedical\Eblast\Contracts;

interface EblastProviderInterface
{
    public function upsertContact(string $email, int $listId): void;

    public function testConnection(int $listId): array;

    public function findOrCreateFolder(string $name): int;

    public function findOrCreateList(string $name, int $folderId): int;
}
