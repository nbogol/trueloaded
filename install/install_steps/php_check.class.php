<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

class php_check extends install_generic {

    public static $before = 'licence';

    public static function before() {
        return self::$before;
    }

    private function getCheckParams() {
        return array(
            'php' => array(
                'required' => VERSION_PHP_RQ . '+',
                'installed' => phpversion(),
                'recommended' => VERSION_PHP_REC,
                'passfail' => (version_compare(PHP_VERSION, VERSION_PHP_RQ, '>=')) ? true : false,
                'adviced_fail' => (version_compare(PHP_VERSION, VERSION_PHP_REC, '<=')) ? true : false
            ),
            'mysql' => array(
                'required' => $this->lang['yes'],
                'installed' => (extension_loaded('mysqli') || extension_loaded('pdo_mysql')) ? $this->lang['yes'] : $this->lang['no'],
                'passfail' => (extension_loaded('mysqli') || extension_loaded('pdo_mysql')) ? true : false
            ),
            'zLib' => array(
                'required' => $this->lang['yes'],
                'installed' => (extension_loaded('zlib')) ? $this->lang['yes'] : $this->lang['no'],
                'passfail' => (extension_loaded('zlib')) ? true : false
            ),
            'memory' => array(
                'required' => REQ_PHP_MEMORY,
                'installed' => (intval(ini_get('memory_limit')) == -1) ? "Unlimited" : ini_get('memory_limit'),
                'passfail' => $this->check_php_limit(REQ_PHP_MEMORY),
                'adviced_fail' => ($this->check_php_limit(REQ_PHP_MEMORY_REC) ? false : true),
                'recommended' => REQ_PHP_MEMORY_REC,
            ),
            'curl' => array(
                'required' => $this->lang['yes'],
                'installed' => (function_exists('curl_version')) ? $this->lang['yes'] : $this->lang['no'],
                'passfail' => true,
                'adviced_fail' => (!function_exists('curl_version')) ? true : false,
                'ignore' => true
            ),
            'fopen' => array(
                'required' => $this->lang['yes'],
                'installed' => (function_exists('fopen')) ? $this->lang['yes'] : $this->lang['no'],
                'passfail' => (function_exists('fopen')) ? true : false
            ),
            'hash' => array(
                'required' => $this->lang['yes'],
                'installed' => (function_exists('hash')) ? $this->lang['yes'] : $this->lang['no'],
                'passfail' => (function_exists('hash')) ? true : false
            ),
            'xml' => array(
                'required' => $this->lang['yes'],
                'installed' => (function_exists('simplexml_load_string')) ? $this->lang['yes'] : $this->lang['no'],
                'passfail' => (function_exists('simplexml_load_string')) ? true : false
            ),
            'autoload' => array(
                'required' => $this->lang['yes'],
                'installed' => (function_exists('spl_autoload_register')) ? $this->lang['yes'] : $this->lang['no'],
                'passfail' => (function_exists('spl_autoload_register')) ? true : false
            ),
            'json' => array(
                'required' => $this->lang['yes'],
                'installed' => (extension_loaded('json')) ? $this->lang['yes'] : $this->lang['no'],
                'passfail' => (extension_loaded('json')) ? true : false
            ),
            'gd' => array(
                'required' => $this->lang['yes'],
                'installed' => (extension_loaded('gd') && function_exists('gd_info')) ? $this->lang['yes'] : $this->lang['no'],
                'passfail' => (extension_loaded('gd') && function_exists('gd_info')) ? true : false
            ),
            'pathinfo' => array(
                'required' => $this->lang['yes'],
                'installed' => $this->lang['yes'],
                'passfail' => true
            ),
        );
    }

    private function check_php_limit($needed) {
        $installed = ini_get('memory_limit');
        if (intval($installed) == -1)
            return true;
        return ($this->convert_hr_to_bytes($installed) >= $this->convert_hr_to_bytes($needed)) ? true : false;
    }

    function convert_hr_to_bytes($size) {
        ( $bytes = (float) $size ) && ( $last = strtolower(substr($size, -1)) ) && ( $pos = strpos(' kmg', $last, 1) ) && $bytes *= pow(1024, $pos)
        ;
        return round($bytes);
    }

    private function do_match_req() {
        $allmatched_req = true;
        foreach ($this->getCheckParams() as $fname => $fdata) {
            $allmatched_req = ($fdata['passfail'] || $fdata['ignore']) ? $allmatched_req : false;
        }
        return $allmatched_req;
    }

    private function do_match_opt() {
        $allmatched_opt = false;
        foreach ($this->getCheckParams() as $fname => $fdata) {
            $allmatched_opt = (isset($fdata['adviced_fail']) && $fdata['adviced_fail']) ? true : $allmatched_opt;
        }
        return $allmatched_opt;
    }

    public function get_output() {
        $content = '';
        $phpcheckdata = $this->getCheckParams();
        $output_array = array();
        if (preg_match("/Apache\/(.*)\(Win(.*)\)/", $_SERVER['SERVER_SOFTWARE'], $output_array)) {
            $content .='<div class="infobox infobox-large infobox-red clearfix">
			<i class="fa fa-exclamation-triangle fa-4x pull-left"></i> <strong>' . $this->lang['windows_apache_hint'] . '</strong>
		</div>';
        }
        if (!$this->do_match_req()) {
            $content .='<div class="infobox infobox-large infobox-red clearfix">
			<i class="fa fa-exclamation-triangle fa-4x pull-left"></i> <strong>' . $this->lang['phpcheck_failed'] . '</strong>
		</div>';
        } else {
            if ($this->do_match_opt()) {
                $content .='<div class="infobox infobox-large infobox-orange clearfix">
			<i class="fa fa-exclamation-triangle fa-4x pull-left"></i> <strong>' . $this->lang['do_match_opt_failed'] . '</strong>
		</div>';
            }
        }
        $content .= '<br/>
		<table class="colorswitch tableWrap" style="border-collapse: collapse;">
                    <thead>
                    <tr>
                        <th width="54%">' . $this->lang['table_pcheck_name'] . '</th>
                        <th width="13%">' . $this->lang['table_pcheck_installed'] . '</th>
                        <th width="13%">' . $this->lang['table_pcheck_rec'] . '</th>
                        <th width="13%">' . $this->lang['table_pcheck_required'] . '</th>
                        <th width="6%"></th>
                    </tr>
                    </thead>
                    <tbody>';
        foreach ($phpcheckdata as $fname => $fdata) {
            if (isset($fdata['adviced_fail']) && $fdata['passfail']) {
                $passfail_color = ($fdata['adviced_fail']) ? 'neutral' : (($fdata['passfail']) ? 'positive' : 'negative');
                $passfail_icon = ($fdata['adviced_fail']) ? 'fa-exclamation-triangle' : (($fdata['passfail']) ? 'fa-check' : 'fa-times-circle');
            } else {
                $passfail_color = ($fdata['passfail']) ? 'positive' : 'negative';
                $passfail_icon = (($fdata['passfail']) ? 'fa-check' : 'fa-times-circle');
            }
            $content .= '<tr>
                            <td>' . (($this->lang['module_' . $fname]) ? $this->lang['module_' . $fname] : $fname) . '</td>
                            <td class="' . $passfail_color . '">' . $fdata['installed'] . '</td>
                            <td class="positive">' . ((isset($fdata['recommended'])) ? $fdata['recommended'] : $fdata['required']) . '</td>
                            <td class="positive">' . $fdata['required'] . '</td>
                            <td><i class="fa ' . $passfail_icon . ' fa-2x ' . $passfail_color . '"></i></td>
			</tr>';
        }
        $content .='</tbody>
                    </table>';
        if ($this->do_match_req()) {
            $this->log('install_success', $this->lang['phpcheck_success']);
        } else {
            $this->log('install_error', $this->lang['phpcheck_failed']);
        }
        $content .= '<script>$(document).ready(function(){
			$.get( "index.php/pathinfotest", function( data ) {
				if($.trim(data) != "/pathinfotest"){
					pathinfotest_failed();
				}
			}).fail(function() {
			    pathinfotest_failed();
			});
			function pathinfotest_failed(){
				var myFirstColumn = $(".colorswitch tr:last td:nth-child(2)");
				var myLastColum = $(".colorswitch tr:last td:nth-child(5)");
				myFirstColumn.html("' . $this->lang['no'] . '");
				myFirstColumn.removeClass("positive");
				myFirstColumn.addClass("negative");
				myLastColum.html("<i class=\"fa fa-times-circle fa-2x negative\"></i>");
				$(".buttonbar button[name=\"next\"]").hide();
			}
		})</script>';
        return $content;
    }

    public function get_filled_output() {
        return $this->get_output();
    }

    public function parse_input() {
        return $this->do_match_req();
    }

}
