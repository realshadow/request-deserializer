<?php

namespace Realshadow\RequestDeserializer\Validation;

/**
 * Extension of Purifier to make it type aware
 *
 * @package Realshadow\RequestDeserializer\Validation
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
class Purifier extends \Mews\Purifier\Purifier
{

    /**
     * @inheritdoc
     */
    public function clean($dirty, $config = null)
    {
        if (is_array($dirty)) {
            $output = array_map(function ($item) use ($config) {
                return $this->clean($item, $config);
            }, $dirty);
        } else {
            # -- the htmlpurifier uses replace instead of merge, so we merge
            $output = is_string($dirty) ? $this->purifier->purify($dirty, $this->getConfig($config)) : $dirty;
        }

        return $output;
    }

}
