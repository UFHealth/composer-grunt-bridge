<?php

/*
 * This file is part of the Composer Grunt bridge package.
 *
 * Copyright (c) 2015 John Bloch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JPB\Composer\GruntBridge;

use Composer\IO\IOInterface;

/**
 * Creates Grunt bridges.
 */
class GruntBridgeFactory
{

    /**
     * Create a new Grunt bridge factory.
     *
     * @return self The newly created factory.
     */
    public static function create(): self
    {
        return new self(
            new GruntVendorFinder(),
            GruntClient::create()
        );
    }

    /**
     * Construct a new Grunt bridge factory.
     *
     * @access private
     *
     * @param GruntVendorFinder $vendorFinder The vendor finder to use.
     * @param GruntClient $client The client to use.
     */
    public function __construct(
        GruntVendorFinder $vendorFinder,
        GruntClient $client
    )
    {
        $this->vendorFinder = $vendorFinder;
        $this->client = $client;
    }

    /**
     * Construct a new Composer Grunt bridge plugin.
     *
     * @param IOInterface $io The i/o interface to use.
     *
     * @return GruntBridge
     */
    public function createBridge(IOInterface $io): GruntBridge
    {
        return new GruntBridge($io, $this->vendorFinder, $this->client);
    }

    private $vendorFinder;
    private $client;
}
