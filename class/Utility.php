<?php declare(strict_types=1);

namespace XoopsModules\Songlist;

use Xmf\Request;
use XoopsModules\Songlist\Form\{
    SelectAlbumForm,
    SelectCategoryForm,
    SelectGenreForm,
    SelectVoiceForm,
    SelectSongForm
};

/**
 * Class Utility
 */
class Utility extends Common\SysUtility
{
    //--------------- Custom module methods -----------------------------
    /**
     * Function responsible for checking if a directory exists, we can also write in and create an index.html file
     *
     * @param string $folder The full path of the directory to check
     */
    public static function createFolder($folder): void
    {
        //        try {
        //            if (!is_dir($folder) && !mkdir($folder) && !is_dir($folder)) {
        //                throw new \RuntimeException(sprintf('Unable to create the %s directory', $folder));
        //            } else {
        //                file_put_contents($folder . '/index.html', '<script>history.go(-1);</script>');
        //            }
        //        }
        //        catch (\Exception $e) {
        //            echo 'Caught exception: ', $e->getMessage(), "\n", '<br>';
        //        }
        try {
            if (!\is_dir($folder)) {
                if (!\is_dir($folder) && !\mkdir($folder) && !\is_dir($folder)) {
                    throw new \RuntimeException(\sprintf('Unable to create the %s directory', $folder));
                }
                file_put_contents($folder . '/index.html', '<script>history.go(-1);</script>');
            }
        } catch (\Throwable $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n", '<br>';
        }
    }


    /**
     * @param $src
     * @param $dst
     */
    public static function recurseCopy($src, $dst): void
    {
        $dir = \opendir($src);
        //    @mkdir($dst);
        while (false !== ($file = \readdir($dir))) {
            if (('.' !== $file) && ('..' !== $file)) {
                if (\is_dir($src . '/' . $file)) {
                    self::recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    \copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        \closedir($dir);
    }

    /**
     * Verifies XOOPS version meets minimum requirements for this module
     * @static
     * @param \XoopsModule|null $module
     *
     * @param null|string       $requiredVer
     * @return bool true if meets requirements, false if not
     */
    public static function checkVerXoops(\XoopsModule $module = null, $requiredVer = null): bool
    {
        $moduleDirName = \basename(\dirname(__DIR__));
        if (null === $module) {
            $module = \XoopsModule::getByDirname($moduleDirName);
        }
        \xoops_loadLanguage('admin', $moduleDirName);
        \xoops_loadLanguage('common', $moduleDirName);

        //check for minimum XOOPS version
        $currentVer = mb_substr(\XOOPS_VERSION, 6); // get the numeric part of string
        if (null === $requiredVer) {
            $requiredVer = '' . $module->getInfo('min_xoops'); //making sure it's a string
        }
        $success = true;

        if (\version_compare($currentVer, $requiredVer, '<')) {
            $success = false;
            $module->setErrors(\sprintf(\_AM_SONGLIST_ERROR_BAD_XOOPS, $requiredVer, $currentVer));
        }

        return $success;
    }

    /**
     * Verifies PHP version meets minimum requirements for this module
     * @static
     * @param \XoopsModule|bool|null $module
     *
     * @return bool true if meets requirements, false if not
     */
    public static function checkVerPhp(\XoopsModule $module = null): bool
    {
        $moduleDirName      = \basename(\dirname(__DIR__, 2));
        $moduleDirNameUpper = \mb_strtoupper($moduleDirName);
        if (null === $module) {
            $module = \XoopsModule::getByDirname($moduleDirName);
        }
        \xoops_loadLanguage('admin', $moduleDirName);
        \xoops_loadLanguage('common', $moduleDirName);

        // check for minimum PHP version
        $success = true;

        $verNum = \PHP_VERSION;
        $reqVer = &$module->getInfo('min_php');

        if (false !== $reqVer && '' !== $reqVer) {
            if (\version_compare($verNum, $reqVer, '<')) {
                $module->setErrors(\sprintf(\constant('CO_' . $moduleDirNameUpper . '_ERROR_BAD_PHP'), $reqVer, $verNum));
                $success = false;
            }
        }

        return $success;
    }

    /**
     * compares current module version with the latest GitHub release
     * @static
     *
     * @return string|array info about the latest module version, if newer
     */
    public static function checkVerModule(\Xmf\Module\Helper $helper, ?string $source = 'github', ?string $default = 'master'): ?array
    {
        $moduleDirName      = \basename(\dirname(__DIR__, 2));
        $moduleDirNameUpper = \mb_strtoupper($moduleDirName);
        $update             = '';
        $repository         = 'XoopsModules25x/' . $moduleDirName;
        //        $repository         = 'XoopsModules25x/publisher'; //for testing only
        $ret             = null;
        $infoReleasesUrl = "https://api.github.com/repos/$repository/releases";
        if ('github' === $source) {
            if (\function_exists('curl_init') && false !== ($curlHandle = \curl_init())) {
                \curl_setopt($curlHandle, \CURLOPT_URL, $infoReleasesUrl);
                \curl_setopt($curlHandle, \CURLOPT_RETURNTRANSFER, true);
                \curl_setopt($curlHandle, \CURLOPT_SSL_VERIFYPEER, true); //TODO: how to avoid an error when 'Peer's Certificate issuer is not recognized'
                \curl_setopt($curlHandle, \CURLOPT_HTTPHEADER, ["User-Agent:Publisher\r\n"]);
                $curlReturn = \curl_exec($curlHandle);
                if (false === $curlReturn) {
                    \trigger_error(\curl_error($curlHandle));
                } elseif (false !== \mb_strpos($curlReturn, 'Not Found')) {
                    \trigger_error('Repository Not Found: ' . $infoReleasesUrl);
                } else {
                    $file              = json_decode($curlReturn, false);
                    $latestVersionLink = \sprintf("https://github.com/$repository/archive/%s.zip", $file ? \reset($file)->tag_name : $default);
                    $latestVersion     = $file[0]->tag_name;
                    $prerelease        = $file[0]->prerelease;
                    if ('master' !== $latestVersionLink) {
                        $update = \constant('CO_' . $moduleDirNameUpper . '_' . 'NEW_VERSION') . $latestVersion;
                    }
                    //"PHP-standardized" version
                    $latestVersion = \mb_strtolower($latestVersion);
                    if (false !== mb_strpos($latestVersion, 'final')) {
                        $latestVersion = \str_replace('_', '', \mb_strtolower($latestVersion));
                        $latestVersion = \str_replace('final', '', \mb_strtolower($latestVersion));
                    }
                    $moduleVersion = ($helper->getModule()->getInfo('version') . '_' . $helper->getModule()->getInfo('module_status'));
                    //"PHP-standardized" version
                    $moduleVersion = \str_replace(' ', '', \mb_strtolower($moduleVersion));
                    //                    $moduleVersion = '1.0'; //for testing only
                    //                    $moduleDirName = 'publisher'; //for testing only
                    if (!$prerelease && \version_compare($moduleVersion, $latestVersion, '<')) {
                        $ret   = [];
                        $ret[] = $update;
                        $ret[] = $latestVersionLink;
                    }
                }
                \curl_close($curlHandle);
            }
        }

        return $ret;
    }

    //=========================================================

    /**
     * @return mixed
     */
    public static function getToken()
    {
        $sql    = 'SELECT md5(rand()/rand()*rand()/rand()*rand()*rand()/rand()*rand()) as `salt`';
        $result = $GLOBALS['xoopsDB']->queryF($sql);
        [$salt] = $GLOBALS['xoopsDB']->fetchRow($result);

        return $salt;
    }

    /**
     * @param $string
     * @return string
     */
    public static function ucword($string): string
    {
        $ret = [];
        foreach (\explode(' ', \mb_strtolower($string)) as $part) {
            $ret[] = \ucfirst($part);
        }

        return \implode(' ', $ret);
    }

    /**
     * @param bool|string $ip
     * @return array
     */
    public static function getIPData($ip = false): array
    {
        $ret = [];
        if (\is_object($GLOBALS['xoopsUser'])) {
            $ret['uid']   = $GLOBALS['xoopsUser']->getVar('uid');
            $ret['uname'] = $GLOBALS['xoopsUser']->getVar('uname');
            $ret['email'] = $GLOBALS['xoopsUser']->getVar('email');
        } else {
            $ret['uid']   = 0;
            $ret['uname'] = ($_REQUEST['uname'] ?? '');
            $ret['email'] = ($_REQUEST['email'] ?? '');
        }
        $ret['agent'] = $_SERVER['HTTP_USER_AGENT'];
        if ($ip) {
            $ret['is_proxied']   = false;
            $ret['network-addy'] = @\gethostbyaddr($ip);
            $ret['long']         = @\ip2long($ip);
            if (isIpv6($ip)) {
                $ret['ip6'] = true;
                $ret['ip4'] = false;
            } else {
                $ret['ip4'] = true;
                $ret['ip6'] = false;
            }
            $ret['ip'] = $ip;
        } elseif (Request::hasVar('HTTP_X_FORWARDED_FOR', 'SERVER')) {
            $ip                  = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $ret['is_proxied']   = true;
            $proxy_ip            = $_SERVER['REMOTE_ADDR'];
            $ret['network-addy'] = @\gethostbyaddr($ip);
            $ret['long']         = @\ip2long($ip);
            if (isIpv6($ip)) {
                $ret['ip6']       = true;
                $ret['proxy-ip6'] = true;
                $ret['ip4']       = false;
                $ret['proxy-ip4'] = false;
            } else {
                $ret['ip4']       = true;
                $ret['proxy-ip4'] = true;
                $ret['ip6']       = false;
                $ret['proxy-ip6'] = false;
            }
            $ret['ip']       = $ip;
            $ret['proxy-ip'] = $proxy_ip;
        } else {
            $ret['is_proxied']   = false;
            $ip                  = $_SERVER['REMOTE_ADDR'];
            $ret['network-addy'] = @\gethostbyaddr($ip);
            $ret['long']         = @\ip2long($ip);
            if (isIpv6($ip)) {
                $ret['ip6'] = true;
                $ret['ip4'] = false;
            } else {
                $ret['ip4'] = true;
                $ret['ip6'] = false;
            }
            $ret['ip'] = $ip;
        }
        $ret['made'] = \time();

        return $ret;
    }

    /**
     * @param string $ip
     * @return bool
     */
    public static function isIpv6($ip = ''): bool
    {
        if ('' == $ip) {
            return false;
        }

        if (mb_substr_count($ip, ':') > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param        $filter
     * @param        $field
     * @param string $sort
     * @param string $op
     * @param string $fct
     * @return bool|\XoopsModules\Songlist\Form\SelectAlbumForm|\XoopsModules\Songlist\Form\SelectArtistForm|\XoopsModules\Songlist\Form\SelectCategoryForm|\XoopsModules\Songlist\Form\SelectGenreForm|\XoopsModules\Songlist\Form\SelectVoiceForm
     */
    public static function getFilterElement($filter, $field, $sort = 'created', $op = '', $fct = '')
    {
        $components = static::getFilterURLComponents($filter, $field, $sort);
        $ele        = false;
        require_once \dirname(__DIR__) . '/include/songlist.object.php';
        switch ($field) {
            case 'gid':
                if ('genre' !== $op) {
                    $ele = new SelectGenreForm('', 'filter_' . $field . '', $components['value'], 1, false);
                    $ele->setExtra(
                        'onchange="window.open(\''
                        . $_SERVER['SCRIPT_NAME']
                        . '?'
                        . $components['extra']
                        . '&filter='
                        . $components['filter']
                        . (!empty($components['filter']) ? '|' : '')
                        . $field
                        . ',\'+this.options[this.selectedIndex].value'
                        . (!empty($components['operator']) ? '+\','
                                                             . $components['operator']
                                                             . '\'' : '')
                        . ',\'_self\')"'
                    );
                }
                break;
            case 'vcid':
                if ('voice' !== $op) {
                    $ele = new SelectVoiceForm('', 'filter_' . $field . '', $components['value'], 1, false);
                    $ele->setExtra(
                        'onchange="window.open(\''
                        . $_SERVER['SCRIPT_NAME']
                        . '?'
                        . $components['extra']
                        . '&filter='
                        . $components['filter']
                        . (!empty($components['filter']) ? '|' : '')
                        . $field
                        . ',\'+this.options[this.selectedIndex].value'
                        . (!empty($components['operator']) ? '+\','
                                                             . $components['operator']
                                                             . '\'' : '')
                        . ',\'_self\')"'
                    );
                }
                break;
            case 'cid':
                if ('category' !== $op) {
                    $ele = new SelectCategoryForm('', 'filter_' . $field . '', $components['value'], 1, false);
                    $ele->setExtra(
                        'onchange="window.open(\''
                        . $_SERVER['SCRIPT_NAME']
                        . '?'
                        . $components['extra']
                        . '&filter='
                        . $components['filter']
                        . (!empty($components['filter']) ? '|' : '')
                        . $field
                        . ',\'+this.options[this.selectedIndex].value'
                        . (!empty($components['operator']) ? '+\','
                                                             . $components['operator']
                                                             . '\'' : '')
                        . ',\'_self\')"'
                    );
                }
                break;
            case 'pid':
                $ele = new SelectCategoryForm('', 'filter_' . $field . '', $components['value'], 1, false);
                $ele->setExtra(
                    'onchange="window.open(\''
                    . $_SERVER['SCRIPT_NAME']
                    . '?'
                    . $components['extra']
                    . '&filter='
                    . $components['filter']
                    . (!empty($components['filter']) ? '|' : '')
                    . $field
                    . ',\'+this.options[this.selectedIndex].value'
                    . (!empty($components['operator']) ? '+\','
                                                         . $components['operator']
                                                         . '\'' : '')
                    . ',\'_self\')"'
                );
                break;
            case 'abid':
                if ('albums' !== $op) {
                    $ele = new SelectAlbumForm('', 'filter_' . $field . '', $components['value'], 1, false);
                    $ele->setExtra(
                        'onchange="window.open(\''
                        . $_SERVER['SCRIPT_NAME']
                        . '?'
                        . $components['extra']
                        . '&filter='
                        . $components['filter']
                        . (!empty($components['filter']) ? '|' : '')
                        . $field
                        . ',\'+this.options[this.selectedIndex].value'
                        . (!empty($components['operator']) ? '+\','
                                                             . $components['operator']
                                                             . '\'' : '')
                        . ',\'_self\')"'
                    );
                }
                break;
            case 'aid':
                if ('artists' !== $op) {
                    $ele = new SelectArtistForm('', 'filter_' . $field . '', $components['value'], 1, false);
                    $ele->setExtra(
                        'onchange="window.open(\''
                        . $_SERVER['SCRIPT_NAME']
                        . '?'
                        . $components['extra']
                        . '&filter='
                        . $components['filter']
                        . (!empty($components['filter']) ? '|' : '')
                        . $field
                        . ',\'+this.options[this.selectedIndex].value'
                        . (!empty($components['operator']) ? '+\','
                                                             . $components['operator']
                                                             . '\'' : '')
                        . ',\'_self\')"'
                    );
                }
                break;
            case 'sid':
                if ('songs' !== $op) {
                    $ele = new SelectSongForm('', 'filter_' . $field . '', $components['value'], 1, false);
                    $ele->setExtra(
                        'onchange="window.open(\''
                        . $_SERVER['SCRIPT_NAME']
                        . '?'
                        . $components['extra']
                        . '&filter='
                        . $components['filter']
                        . (!empty($components['filter']) ? '|' : '')
                        . $field
                        . ',\'+this.options[this.selectedIndex].value'
                        . (!empty($components['operator']) ? '+\','
                                                             . $components['operator']
                                                             . '\'' : '')
                        . ',\'_self\')"'
                    );
                }
                break;
            case 'name':
            case 'title':
            case 'artists':
            case 'albums':
            case 'songs':
            case 'hits':
            case 'rank':
            case 'votes':
            case 'description':
            case 'lyrics':
            case 'songid':
            case 'tags':
                $ele = new \XoopsFormElementTray('');
                $ele->addElement(new \XoopsFormText('', 'filter_' . $field . '', 11, 40, $components['value']));
                $button = new \XoopsFormButton('', 'button_' . $field . '', '[+]');
                $button->setExtra(
                    'onclick="window.open(\''
                    . $_SERVER['SCRIPT_NAME']
                    . '?'
                    . $components['extra']
                    . '&filter='
                    . $components['filter']
                    . (!empty($components['filter']) ? '|' : '')
                    . $field
                    . ',\'+$(\'#filter_'
                    . $field
                    . '\').val()'
                    . (!empty($components['operator']) ? '+\','
                                                         . $components['operator']
                                                         . '\'' : '')
                    . ',\'_self\')"'
                );
                $ele->addElement($button);
                break;
        }

        return $ele;
    }

    /**
     * @param        $filter
     * @param        $field
     * @param string $sort
     * @return array
     */
    public static function getFilterURLComponents($filter, $field, $sort = 'created'): array
    {
        $parts     = \explode('|', $filter);
        $ret       = [];
        $value     = '';
        $ele_value = '';
        $operator  = '';
        foreach ($parts as $part) {
            $var = \explode(',', $part);
            if (\count($var) > 1) {
                if ($var[0] == $field) {
                    $ele_value = $var[1];
                    if (isset($var[2])) {
                        $operator = $var[2];
                    }
                } elseif (1 != $var[0]) {
                    $ret[] = \implode(',', $var);
                }
            }
        }
        $pagenav          = [];
        $pagenav['op']    = $_REQUEST['op'] ?? 'videos';
        $pagenav['fct']   = $_REQUEST['fct'] ?? 'list';
        $pagenav['limit'] = Request::getInt('limit', 30, 'REQUEST');
        $pagenav['start'] = 0;
        $pagenav['order'] = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC';
        $pagenav['sort']  = !empty($_REQUEST['sort']) ? '' . $_REQUEST['sort'] . '' : $sort;
        $retb             = [];
        foreach ($pagenav as $key => $value) {
            $retb[] = "$key=$value";
        }

        return ['value' => $ele_value, 'field' => $field, 'operator' => $operator, 'filter' => \implode('|', $ret), 'extra' => \implode('&', $retb)];
    }

    /**
     * @param $objects
     * @return array
     */
    public static function obj2array($objects): array
    {
        $ret = [];
        foreach ((array)$objects as $key => $value) {
            if (\is_a($value, 'stdClass')) {
                $ret[$key] = static::obj2array($value);
            } elseif (\is_array($value)) {
                $ret[$key] = static::obj2array($value);
            } else {
                $ret[$key] = $value;
            }
        }

        return $ret;
    }

    /**
     * @param $url
     * @return mixed
     */
    public static function shortenUrl($url)
    {
        /** @var \XoopsModuleHandler $moduleHandler */
        $moduleHandler = \xoops_getHandler('module');
        /** @var \XoopsConfigHandler $configHandler */
        $configHandler                   = \xoops_getHandler('config');
        $GLOBALS['songlistModule']       = $moduleHandler->getByDirname('songlist');
        $GLOBALS['songlistModuleConfig'] = $configHandler->getConfigList($GLOBALS['songlistModule']->getVar('mid'));

        if (!empty($GLOBALS['songlistModuleConfig']['bitly_username']) && !empty($GLOBALS['songlistModuleConfig']['bitly_apikey'])) {
            $source_url = $GLOBALS['songlistModuleConfig']['bitly_apiurl'] . '/shorten?login=' . $GLOBALS['songlistModuleConfig']['bitly_username'] . '&apiKey=' . $GLOBALS['songlistModuleConfig']['bitly_apikey'] . '&format=json&longUrl=' . \urlencode($url);
            $cookies    = XOOPS_ROOT_PATH . '/uploads/songlist_' . \md5($GLOBALS['songlistModuleConfig']['bitly_apikey']) . '.cookie';
            if (!$ch = \curl_init($source_url)) {
                return $url;
            }
            \curl_setopt($ch, \CURLOPT_COOKIEJAR, $cookies);
            \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, 1);
            \curl_setopt($ch, \CURLOPT_USERAGENT, $GLOBALS['songlistModuleConfig']['user_agent']);
            \curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, $GLOBALS['songlistModuleConfig']['curl_connect_timeout']);
            \curl_setopt($ch, \CURLOPT_TIMEOUT, $GLOBALS['songlistModuleConfig']['curl_timeout']);
            $data = \curl_exec($ch);
            \curl_close($ch);
            $result                = songlist_object2array(json_decode($data));
            $result['status_code'] = 200;
            if ($result['status_code']) {
                if (!empty($result['data']['url'])) {
                    return $result['data']['url'];
                }

                return $url;
            }

            return $url;
        }

        return $url;
    }

    /**
     * @param        $contents
     * @param int    $get_attributes
     * @param string $priority
     * @return array|void
     */
    public static function xml2array($contents, $get_attributes = 1, $priority = 'tag')
    {
        if (!$contents) {
            return [];
        }

        if (!\function_exists('xml_parser_create')) {
            return [];
        }

        //Get the XML parser of PHP - PHP must have this module for the parser to work
        $parser = \xml_parser_create('');
        \xml_parser_set_option($parser, \XML_OPTION_TARGET_ENCODING, 'UTF-8'); # https://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
        \xml_parser_set_option($parser, \XML_OPTION_CASE_FOLDING, 0);
        \xml_parser_set_option($parser, \XML_OPTION_SKIP_WHITE, 1);
        \xml_parse_into_struct($parser, \trim($contents), $xml_values);
        \xml_parser_free($parser);

        if (!$xml_values) {
            return;
        }//Hmm...

        //Initializations
        $xml_array   = [];
        $parents     = [];
        $opened_tags = [];
        $arr         = [];

        $current = &$xml_array; //Refference

        //Go through the tags.
        $repeated_tag_index = []; //Multiple tags with same name will be turned into an array
        foreach ($xml_values as $data) {
            unset($attributes, $value); //Remove existing values, or there will be trouble

            //This command will extract these variables into the foreach scope
            // tag(string), type(string), level(int), attributes(array).
            \extract($data); //We could use the array by itself, but this cooler.

            $result          = [];
            $attributes_data = [];

            if (isset($value)) {
                if ('tag' === $priority) {
                    $result = $value;
                } else {
                    $result['value'] = $value;
                } //Put the value in a assoc array if we are in the 'Attribute' mode
            }

            //Set the attributes too.
            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) {
                    if ('tag' === $priority) {
                        $attributes_data[$attr] = $val;
                    } else {
                        $result['attr'][$attr] = $val;
                    } //Set all the attributes in a array called 'attr'
                }
            }

            //See tag status and do the needed.
            if ('open' === $type) {//The starting of the tag '<tag>'
                $parent[$level - 1] = &$current;
                if (!\is_array($current) or (!\array_key_exists($tag, $current))) { //Insert New tag
                    $current[$tag] = $result;
                    if ($attributes_data) {
                        $current[$tag . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level] = 1;

                    $current = &$current[$tag];
                } else { //There was another element with the same tag name
                    if (isset($current[$tag][0])) {//If there is a 0th element it is already an array
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {//This section will make the value an array if multiple tags with the same name appear together
                        $current[$tag]                           = [$current[$tag], $result]; //This will combine the existing item and the new item together to make an array
                        $repeated_tag_index[$tag . '_' . $level] = 2;

                        if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current         = &$current[$tag][$last_item_index];
                }
            } elseif ('complete' === $type) { //Tags that ends in 1 line '<tag>'
                //See if the key is already taken.
                if (isset($current[$tag])) { //If taken, put all things inside a list(array)
                    if (isset($current[$tag][0]) and \is_array($current[$tag])) {//If it is already an array...
                        // ...push the new element into that array.
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;

                        if ('tag' === $priority and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    } else { //If it is not an array...
                        $current[$tag]                           = [$current[$tag], $result]; //...Make it an array using using the existing value and the new value
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ('tag' === $priority and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }

                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        //0 and 1 index is already taken
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;
                } else { //New Key
                    $current[$tag]                           = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ('tag' === $priority and $attributes_data) {
                        $current[$tag . '_attr'] = $attributes_data;
                    }
                }
            } elseif ('close' === $type) { //End of tag '</tag>'
                $current = &$parent[$level - 1];
            }
        }

        return $xml_array;
    }

    /**
     * @param $array
     * @param $name
     * @param $standalone
     * @param $beginning
     * @param $nested
     * @return string
     */
    public static function toXml($array, $name, $standalone, $beginning, $nested): string
    {
        $output = '';
        if ($beginning) {
            if ($standalone) {
                \header('content-type:text/xml;charset=' . _CHARSET);
            }
            $output .= '<' . '?' . 'xml version="1.0" encoding="' . _CHARSET . '"' . '?' . '>' . "\n";
            $output .= '<' . $name . '>' . "\n";
            $nested = 0;
        }

        if (\is_array($array)) {
            foreach ($array as $key => $value) {
                ++$nested;
                if (\is_array($value)) {
                    $output .= \str_repeat("\t", (int)$nested) . '<' . (\is_string($key) ? $key : $name . '_' . $key) . '>' . "\n";
                    ++$nested;
                    $output .= static::toXml($value, $name, false, false, $nested);
                    $nested--;
                    $output .= \str_repeat("\t", (int)$nested) . '</' . (\is_string($key) ? $key : $name . '_' . $key) . '>' . "\n";
                } elseif ('' != $value) {
                    ++$nested;
                    $output .= \str_repeat("\t", (int)$nested) . '  <' . (\is_string($key) ? $key : $name . '_' . $key) . '>' . \trim($value) . '</' . (\is_string($key) ? $key : $name . '_' . $key) . '>' . "\n";
                    $nested--;
                }
                $nested--;
            }
        } elseif ('' != $array) {
            ++$nested;
            $output .= \str_repeat("\t", (int)$nested) . \trim($array) . "\n";
            $nested--;
        }

        if ($beginning) {
            $output .= '</' . $name . '>';

            return $output;
        }

        return $output;
    }
}
