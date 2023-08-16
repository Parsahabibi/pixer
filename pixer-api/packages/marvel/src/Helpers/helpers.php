<?php

if (!function_exists('gateway_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function gateway_path($path = '')
    {
        return __DIR__ . '/';
    }

    if (!function_exists('globalSlugify')) {

        /**
         * It takes a string, a model,  a key, and a divider, and returns a slugified string with a number
         * appended to it if the slug already exists in the database.
         * 
         * Here's a more detailed explanation:
         * 
         * The function takes three parameters:
         * 
         * - ``: The string to be slugified.
         * - ``: The model to check against. Model must pass as Product::class
         * - ``: The key to check The column name of the slug in the database.
         * - ``: The divider to use between the slug and the number.
         * 
         * The function first slugifies the string and then checks the database to see if the slug
         * already exists. If it doesn't, it returns the slug. If it does, it returns the slug with a
         * number appended to it.
         * 
         * Here's an example of how to use the function:
         * 
         * @param string slugText The text you want to slugify
         * @param string model The model you want to check against.
         * @param string key The column name of the slug in the database.
         * @param string divider The divider to use when appending the slug count to the slug.
         * 
         * @return string slug is being returned.
         */
        function globalSlugify(string $slugText, string $model, string $key = '', string $divider = '-'): string
        {
            try {
                $slug      = preg_replace('~[^\pL\d]+~u', $divider, $slugText);
                $slug = strtolower($slug);
                if ($key) {
                    $slugCount = $model::where($key, $slug)->orWhere($key, 'like',  $slug . '%')->count();
                } else {
                    $slugCount = $model::where('slug', $slug)->orWhere('slug', 'like',  $slug . '%')->count();
                }

                if (empty($slugCount)) {
                    return $slug;
                }

                return $slug . $divider . $slugCount;
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }
}
