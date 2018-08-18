<?php

/*
 * This file is part of the Behat\YiiExtension
 *
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Behat\YiiXExtension\Context;

use Behat\Behat\Context\Context;

/**
 * Yii aware interface for contexts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface YiiXAwareContext extends Context
{
    /**
     * Sets Yii web application instance.
     *
     * @param object $application Yii application
     */
    function setApplication($application);

    function setConfig($config);

}
