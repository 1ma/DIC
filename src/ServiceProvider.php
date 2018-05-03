<?php

declare(strict_types=1);

namespace UMA\DIC;

interface ServiceProvider
{
    public function provide(Container $c): void;
}
