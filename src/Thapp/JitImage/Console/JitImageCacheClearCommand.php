<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Console;

use Illuminate\Console\Command;
use Thapp\JitImage\Cache\CacheInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


/**
 * Class: JitImageCacheClearCommand
 *
 *
 * @package Thapp\JitImage
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitImageCacheClearCommand extends Command
{
    /**
     * config
     *
     * @var array
     */
    protected $config;

    /**
     * container
     *
     * @var Illuminate\Container\Container
     */
    protected $files;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'jitimage:clearcache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear JitImage cache.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CacheInterface $cache)
    {
        parent::__construct();
        $this->cache = $cache;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->cache->purge();
        $this->info('cache was successfully cleared');
    }
}
