<?php

namespace Illuminatech\DbSafeDelete\Test;

use Illuminatech\DbSafeDelete\Test\Support\Item;

class SafeDeletesTest extends TestCase
{
    public function testDelete()
    {
        $itemWithoutReference = Item::query()
            ->whereDoesntHave('purchases')
            ->first();

        $itemWithoutReference->delete();

        $this->assertFalse(Item::query()->withoutGlobalScopes()->whereKey($itemWithoutReference->getKey())->exists());

        $itemWithReference = Item::query()
            ->whereHas('purchases')
            ->first();

        $itemWithReference->delete();

        $this->assertTrue(Item::query()->withoutGlobalScopes()->whereKey($itemWithReference->getKey())->exists());
    }
}
