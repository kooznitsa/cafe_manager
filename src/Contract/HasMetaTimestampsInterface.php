<?php

namespace App\Contract;

interface HasMetaTimestampsInterface
{
    public function setCreatedAt(): void;

    public function setUpdatedAt(): void;
}
