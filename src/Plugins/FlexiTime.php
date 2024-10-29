<?php

declare(strict_types=1);

namespace Yuhzel\Xaseco\Plugins;

/*
 * xaseco flexitime plugin.
 *
 * Flexible time limit for tracks. The time remaining can be changed on the
 * fly, or queried, using the /timeleft chat command.
 * Copyright (c) 2015-2016 Tony Houghton ("realh")
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * Updated by Yuhzel
 */

class FlexiTime
{
    //NOTE - Defaults previously from flexitime.xml
    // @phpstan-ignore-next-line
    private int $adminLevel =  2;
    // @phpstan-ignore-next-line
    private array $admins = ["realh", "supercharn", "plext"];
    // @phpstan-ignore-next-line
    private int $defaultTime = 1440;
    // @phpstan-ignore-next-line
    private bool $customTime = true;
    // @phpstan-ignore-next-line
    private int $authorMult = 0;
    // @phpstan-ignore-next-line
    private int $minTime = 120;
    // @phpstan-ignore-next-line
    private bool $useChat = false;
    // @phpstan-ignore-next-line
    private bool $showPanel = true;
    // @phpstan-ignore-next-line
    private string $clockColor = 'fff';
    // @phpstan-ignore-next-line
    private int $warnTime = 300;
    // @phpstan-ignore-next-line
    private string $warnColor = 'ff4';
    // @phpstan-ignore-next-line
    private int $dangerTime = 60;
    // @phpstan-ignore-next-line
    private string $dangerColor = 'f44';
}
