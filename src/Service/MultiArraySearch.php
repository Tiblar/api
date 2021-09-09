<?php
namespace App\Service;

class MultiArraySearch {
    static function multi_array_search($array, $search) {
        // Create the result array
        $result = array();

        // Iterate over each array element
        foreach ($array as $key => $value)
        {

            // Iterate over each search condition
            foreach ($search as $k => $v)
            {

                // If the array element does not meet the search condition then continue to the next element
                if (!isset($value[$k]) || $value[$k] != $v)
                {
                    continue 2;
                }

            }

            // Add the array element's key to the result array
            $result[] = $key;

        }

        // Return the result array
        return $result;
    }
}