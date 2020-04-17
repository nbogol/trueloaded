<?php

/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\helpers;

class Download {

    /**
     * Returns a random name, 16 to 20 characters long
     * There are more than 10^28 combinations
     * The directory is "hidden", i.e. starts with '.'
     * @return string
     */
    public static function random_name() {
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        $dirname = '.';
        $length = floor(tep_rand(16, 20));
        for ($i = 1; $i <= $length; $i++) {
            $q = floor(tep_rand(1, 26));
            $dirname .= $letters[$q];
        }
        return $dirname;
    }

    /**
     * Unlinks all subdirectories and files in $dir
     * Works only on one subdir level, will not recurse
     * @param type $dir
     */
    public static function unlink_temp_dir($dir) {
        $h1 = opendir($dir);
        while ($subdir = readdir($h1)) {
            // Ignore non directories
            if (!is_dir($dir . $subdir))
                continue;
            // Ignore . and .. and CVS
            if ($subdir == '.' || $subdir == '..' || $subdir == 'CVS')
                continue;
            // Loop and unlink files in subdirectory
            $h2 = opendir($dir . $subdir);
            while ($file = readdir($h2)) {
                if ($file == '.' || $file == '..')
                    continue;
                @unlink($dir . $subdir . '/' . $file);
            }
            closedir($h2);
            @rmdir($dir . $subdir);
        }
        closedir($h1);
    }

}
