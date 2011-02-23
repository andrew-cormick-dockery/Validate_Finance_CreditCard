<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 *  Data validation class for New Zealand
 *
 * This class provides methods to validate:
 *  - IRD numbers
 *  - Regional codes
 *  - Telephone number
 *  - Postal code
 *  - Bank AC
 *
 * PHP Versions 4 and 5
 *
 * This source file is subject to the New BSD license, That is bundled
 * with this package in the file LICENSE, and is available through
 * the world-wide-web at
 * http://www.opensource.org/licenses/bsd-license.php
 * If you did not receive a copy of the new BSDlicense and are unable
 * to obtain it through the world-wide-web, please send a note to
 * pajoye@php.net so we can mail you a copy immediately.
 *
 * @category  Validate
 * @package   Validate_NZ
 * @author    Byron Adams <Byron.adams54@gmail.com>
 * @copyright 2006 Byron Adams
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Validate_NZ
 */

/**
 * Data validation class for New Zealand
 *
 * This class provides methods to validate:
 *
 *  - IRD numbers
 *  - Regional codes
 *  - Telephone number
 *  - Postal code
 *  - Bank AC
 *  - Car Registration
 *
 * @category Validate
 * @package  Validate_NZ
 * @author   Byron Adams <Byron.adams54@gmail.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Validate_NZ
 */
class Validate_NZ
{
    /**
     * Validate  New Zealand postal codes
     *
     * @param string $postcode postcode to validate
     * @param bool   $strong   optional; strong checks against a list of
     *                         postcodes
     *
     * @static   array     $postcodes
     * @return   bool      The valid or invalid postal code
     * @link     www.nzpost.co.nz/nzpost/images/addressing.nzpost/pdfs/postcodedirectory_nomaps.pdf
     * @access   public
     */
    function postalCode($postcode, $strong = false, $dataDir = null)
    {
        if (!ctype_digit($postcode)) {
            return false;
        }

        if ($strong) {
            static $postcodes;

            if (!isset($postcodes)) {
                if ($dataDir != null && (is_file($dataDir . '/NZ_postcodes.txt'))) {
                    $file = $dataDir . '/NZ_postcodes.txt';
                } else {
                    $file = '@DATADIR@/Validate_NZ/data/NZ_postcodes.txt';
                }
                $postcodes = array_map('trim', file($file));
            }

            return in_array((string)$postcode, $postcodes, true);
        }

        return preg_match('/^[0-9]{4}$/', $postcode);
    }
    /**
     * Validates a New Zealand IRD Number (ssn equiv)
     *
     * recently the format has changed to having a
     * prefix of 0, this will work with both new and old IRD numbers.
     *
     * @param string $ssn IRD number to validate
     *
     * @access  public
     * @return  bool       The valid or invalid ird number
     *
     * @link www.ird.govt.nz/resources/file/eb70020dbbadb13/rwt-nrwt-spec-2006.pdf
     */
    function ssn($ssn)
    {
        $ssn = str_replace(array("-", " ", "."), "", trim($ssn));
        if (!ctype_digit($ssn)) {
            return false;
        }
        switch (strlen($ssn)) {
        case 8:
            return Validate_NZ::checkIRD($ssn);
            break;
        case 9:
            if ($ssn{0} == "0") {
                return Validate_NZ::checkIRD($ssn);
            }
            break;
        }
        return false;
    }
    /**
     * Validates a New Zealand Regional Code
     *
     * @param string $region regional code to validate
     *
     * @access    public
     * @static    array      $regions
     * @return    bool       The valid or invalid regional code
     * @link      www.google.com/apis/adwords/developer/adwords_api_regions.html
     */
    function region($region)
    {
        static $regions = array("AUK", "BOP", "CAN", "GIS", "HKB", "MBH", "MWT",
                                "NSN", "NTL", "OTA", "STL", "TAS", "TKI", "WGN",
                                "WKO", "WTC");
        return in_array(strtoupper($region), $regions);
    }
    /**
     * Validates a New Zealand phone number
     *
     * This function validates all New Zealand phone numbers
     * checks for landline,0800,0900,0508,021,027 and 025 numbers
     * allows for various combinations with spaces dashes and parentheses.
     *
     * @param string $number          the number to validate
     * @param bool   $requireAreaCode Optional (default: true)
     *
     * @see       http://en.wikipedia.org/wiki/Telephone_numbers_in_New_Zealand
     * @access    public
     * @static    array      $servicePrefix, $mobilePrefix
     * @return    bool       The valid or invalid phone number
     */
    function phoneNumber($number, $requireAreaCode = true)
    {
        static $servicePrefix = array("0800", "0900", "0508");
        static $mobilePrefix = array("021", "025", "027");
        $regexp = '';
        // Remove non-numeric characters that we still accept
        $number = str_replace(array("+", " ", "(", ")", "-",), "", trim($number));
        // Sanity check
        if (!ctype_digit($number)) {
            return false;
        } else {
            $numlength = strlen($number);
            switch ($numlength) {
            case 7:
            case 9:
                if (!$requireAreaCode) {
                    // Is land line w/o area code
                    $regexp = "(^[0-9]{7}$)";
                } else {
                    // Is land line with area code
                    $regexp = "(^0(3|4|6|7|9)[0-9]{7}$)";
                }
                break;
            case 10:
                if (in_array(substr($number, 0, 4), $servicePrefix)) {
                    // Is 0800,0900 or 0508 number
                    $regexp = "(^0(8|9|5)0(0|8)[0-9]{6}$)";
                } elseif (in_array(substr($number, 0, 3), $mobilePrefix)) {
                    //Is Mobile number
                    $regexp = "(^02(1|5|7)[0-9]{3}[0-9]{4}$)";
                }
                break;
            case 11:
                if (substr($number, 0, 4) == '0800') {
                    // Is 0800 with 7 digits?
                    $regexp = "(^0800[0-9]{7}$)";
                }

                if (substr($number, 0, 3) == "640") {
                    // Is land line with country code
                    $regexp = "(^640(3|4|6|7|9)[0-9]{7})";
                }
                break;
            }
        }
        if ($regexp != '') {
            return preg_match($regexp, $number);
        }
        return false;
    }
    /**
     * Validates a New Zealand Bank Account Number
     *
     * This function checks wheather the given value
     * is a valid New Zealand bank account number.
     * allows several formats.
     *
     * @param string $bankcode number to validate
     *
     * @access    public
     * @return    bool       The valid or invalid Bank Account Number
     */
    function bankCode($bankcode)
    {
        $bankcode = str_replace(array("-", " ", "."), '', trim($bankcode));
        return (ctype_digit($bankcode) && strlen($bankcode) == 15);
    }
    /**
     * Validates a New Zealand Vehicle license plates
     *
     * This function checks wheather the given value
     * is a valid New Zealand Vehicle license plate
     *
     * 6 characters for cars or trucks and
     * 5 characters for motorbikes and trailors
     *
     * @param string $reg string to validate
     *
     * @access    public
     * @return    bool       The valid or invalid license plate number
     */
    function carReg($reg)
    {
        $reg = trim($reg);
        return (ctype_alnum($reg) &&
               !ctype_alpha($reg) &&
               !ctype_digit($reg) &&
               in_array(strlen($reg), array("6", "5")));
    }
    /**
     * Return true if the checksum[s] in the specified value is valid as
     * regards the value being a valid IRD number.
     *
     * @param string $ssn Value to perform the validation on
     *
     * @access public
     * @return boolean
     */
    function checkIRD($ssn)
    {
        $ird = (int) str_replace("-", "", $ssn);
        if (strlen("$ird") == 8 ) {
            //should be 8 characters in length: converting to int drops leading zero.
            if ($ird < 10000000) {
                return false;
            }
            $weights = array(2,7,6,5,4,3,2);
            $sird    = "$ird";
            $sum     = 0;
            for ($i = 0; $i < 7; ++$i) {
                $sum += $sird[$i] * $weights[$i];
            }
            $remainder  = ($sum%11);
            $checkdigit = 11 - $remainder;
            if ($sird[7] == $checkdigit) {
                return true;
            }
            if ($checkdigit == 10) {
                $weights = array(4,3,2,5,2,7,6);
                $sum     = 0;
                for ($i = 0; $i < 7; ++$i) {
                    $sum += $sird[$i] * $weights[$i];
                }
                $remainder  = ($sum%11);
                $checkdigit = 11 - $remainder;
                if ($checkdigit == 10) {
                    return false;
                }
                return ($sird[7] == $checkdigit);
            }
        }
        return false;
    }
}
