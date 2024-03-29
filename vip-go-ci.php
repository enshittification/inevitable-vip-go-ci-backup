#!/usr/bin/php
<?php
/**
 * Require requires.php which does the rest.
 *
 * This is done so that the interpreter
 * line above is not printed when vip-go-ci
 * is used as a library.
 *
 * @package Automattic/vip-go-ci
 */

declare(strict_types=1);

require_once __DIR__ . '/requires.php';

