<?php

if (!function_exists('convert_obj_from_props')) {

    function convert_obj_from_props($className, $items)
    {
        if (is_object($items)) {
            return new $className($items);
        }

        $objectsContainer = [];

        foreach ($items as $item) {
            $objectsContainer[] = new $className($item);
        }

        return $objectsContainer;
    }
}
