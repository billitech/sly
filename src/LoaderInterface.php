<?php

namespace Billitech\Sly;

interface LoaderInterface {

    /**
     * Load template file
     *
     * @param string $template
     * @return string
     */
    public function load($template);

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param  string $name
     * @return string
     */
    public function getCacheKey($name);

    /**
     * Returns true if the template is still fresh.
     *
     * @param  string $name
     * @param  int    $time
     * @return bool
     */
    public function isFresh($name, $time);

    /**
     * Get the fully qualified location of the view.
     *
     * @param  string  $name
     * @return string
     */
    public function find($name);
}
