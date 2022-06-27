<?php
$_stopwatch = microtime();
define('E2_VERSION', 3860);
define('E2_RELEASE', '2.10');
define('E2_UA_STRING', 'E2 (v' . E2_VERSION . '; Aegea)');
define('E2_MINIMUM_PHP', '5.6');
define('E2_MINIMUM_MYSQL', 4.1);
define('BUILDER_OBFUSCATE', 1);
define('BUILDER_FLATTEN', 1);
define('E2_NEW_FILES_RIGHTS', 0777);
define('E2_JSON_STYLE', JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
define('E2_RUN_ID', chr(rand(65, 90)));
define('HSC_ENC', 'UTF-8');
define('SECONDS_IN_A_MINUTE', 60);
define('SECONDS_IN_AN_HOUR', 3600);
define('SECONDS_IN_A_DAY', 86400);
define('SECONDS_IN_A_WEEK', 604800);
define('SECONDS_IN_A_MONTH', 2592000);
define('SECONDS_IN_A_YEAR', 31536000);
if (version_compare(PHP_VERSION, E2_MINIMUM_PHP) < 0) {
    die ('PHP version must be ' . E2_MINIMUM_PHP . ' or later, you are running ' . PHP_VERSION);
}
if (!function_exists('getimagesize')) {
    die ('Function getimagesize is not defined, php_gd not installed?');
}
if (!function_exists('mb_internal_encoding')) {
    die ('Function mb_internal_encoding is not defined, php_mbstring not installed?');
}
if (!class_exists('PDO')) {
    die ('Class PDO is not defined installed, PDO not installed?');
}
if (!in_array('mysql', PDO::getAvailableDrivers())) {
    die ('Required PDO driver "mysql" not installed');
}
error_reporting(E_ALL);
setlocale(LC_CTYPE, 'ru_RU.UTF');
mb_internal_encoding('UTF-8');
date_default_timezone_set('GMT');
if (version_compare(PHP_VERSION, '7.0') < 0) {
    error_reporting(E_ALL & ~E_STRICT);
}
if (is_file('superconfig.php')) {
    include 'superconfig.php';
}
$_protocol = (!empty ($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' or $_SERVER['SERVER_PORT'] == 443 or isset ($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' or isset ($_SERVER['HTTP_X_HTTPS']) && ($_SERVER['HTTP_X_HTTPS'])) ? 'https' : 'http';
if (is_file('force-https')) {
    $_protocol = 'https';
}
$c = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], '/index.php'));
list ($v,) = explode(':', $_SERVER['HTTP_HOST']);
$full_blog_url     = $_protocol . '://' . $v . $c;
$_user_folder_name = str_replace('/', '--', $v . $c);
if (substr($_user_folder_name, 0, 4) == 'www.') {
    $_user_folder_name = substr($_user_folder_name, 4);
}
if (is_file('multiuser')) {
    if (!empty ($_superconfig) and array_key_exists('rewrites', $_superconfig) and array_key_exists($_user_folder_name, $_superconfig['rewrites'])) {
        $_user_folder_name = $_superconfig['rewrites'][$_user_folder_name];
    }
    define('USER_FOLDER', 'users/' . $_user_folder_name . '/');
    define('USER_FOLDER_URLPATH', 'user/');
} else {
    define('USER_FOLDER', 'user/');
    define('USER_FOLDER_URLPATH', 'user/');
}
if (!empty ($_superconfig) and array_key_exists('store_files_by_users', $_superconfig) and $_superconfig['store_files_by_users']) {
    define('MEDIA_ROOT_FOLDER', USER_FOLDER . 'files/');
} else {
    define('MEDIA_ROOT_FOLDER', '');
}
if (in_array('mail', explode(',', ini_get('disable_functions')))) {
    define('MAIL_ENABLED', false);
} else {
    define('MAIL_ENABLED', true);
}
define('EXTRAS_FOLDER', USER_FOLDER . 'extras/');
define('BACKUP_FOLDER', USER_FOLDER . 'backup/');
define('CACHES_FOLDER', USER_FOLDER . 'caches/');
define('USER_LIBRARY_FOLDER', USER_FOLDER . 'library/');
define('LOG_FOLDER', USER_FOLDER . 'logs/');
define('LICENSE_FILE', USER_FOLDER . 'license.psa');
define('PICTURES_FOLDER', 'pictures/');
define('THUMBNAILS_FOLDER', 'pictures/thumbs/');
define('AVATARS_FOLDER', 'pictures/avatars/');
define('VIDEO_FOLDER', 'video/');
define('AUDIO_FOLDER', 'audio/');
define('TEMPLATES_FOLDER', 'themes/');
define('SYSTEM_FOLDER', 'system/');
define('SCRIPTS_FOLDER', 'system/js/');
define('SYSTEM_LIBRARY_FOLDER', 'system/library/');
define('SYSTEM_TEMPLATE_FOLDER', 'system/theme/');
define('VIDEO_ICON_IMAGE', 'system/theme/images/video.svg');
define('VIDEO_ICON_WIDTH', 180);
define('VIDEO_ICON_HEIGHT', 120);
define('AUDIO_ICON_IMAGE', 'system/theme/images/audio.svg');
define('AUDIO_ICON_WIDTH', 120);
define('AUDIO_ICON_HEIGHT', 120);
define('LANGUAGES_FOLDER', 'system/languages/');
define('DEFAULTS_FOLDER', 'system/default/');
define('MTMPL_FOLDER', 'system/default/mail/');
define('DEFAULT_TEMPLATE', 'acute');
if (!is_file(DEFAULTS_FOLDER . 'config.php')) die ('System config missing');
include DEFAULTS_FOLDER . 'config.php';
$_default_config = $_config;
if (is_file(USER_FOLDER . 'config.php')) {
    include USER_FOLDER . 'config.php';
    $_config = array_merge($_default_config, $_config);
}
define('E2E_STRANGE_ERROR', 10);
define('E2E_USER_ERROR', 20);
define('E2E_PERMISSIONS_ERROR', 30);
define('E2E_MESSAGE', 100);
define('E2E_DIAGNOSTICS_MESSAGE', 110);
define('DEFAULT_ITEMS_PER_PAGE', 10);
define('MAX_ITEMS_PER_PAGE', 100);
define('FP_NO_ID_OR_NEW', -1);
define('FP_INSERT_ERROR', -10);
define('FP_UPDATE_ERROR', -11);
define('FP_EMPTY_FIELD', -20);
define('FP_TITLE_OR_TEXT_EMPTY', -21);
define('FP_NOT_COMMENTABLE', -30);
define('FP_COMMENT_DOUBLE_POST', -101);
define('FP_COMMENT_TOO_LONG', -102);
define('FP_COMMENT_SPAM_SUSPECT', -103);
define('NOTE_COMMENTABLE_NOW', -1);
define('NOTE_COMMENTABLE_NOW_CONDITIONALLY', -2);
define('THUMB_WIDTH', 180);
define('THUMB_HEIGHT', 120);
define('THUMB_JPG_QUALITY', 50);
define('SCALED_IMAGE_JPG_QUALITY', 80);
define('USERPIC_WIDTH', 80);
define('USERPIC_HEIGHT', 80);
define('USERPIC_JPG_QUALITY', 80);
$_fp_error = false;
if (strstr(__FILE__, 'all.php')) {
    define('BUILT', 0);
} else {
    define('BUILT', 1);
}
function c($b = '')
{
    global $_protocol, $errors, $v, $c;
    @session_start();
    $_SESSION['errors'] = $errors;
    if (substr($b, 0, strlen($_protocol) + 3) != $_protocol . '://') {
        header('Location: ' . $_protocol . '://' . $v . $c . '/' . $b);
    } else {
        header('Location: ' . $b);
    }
    flush();
    die;
}

function v()
{
    $y = $_SERVER['HTTP_REFERER'];
    c($y);
}

function b($n = '')
{
    global $c;
    $m = str_replace('/', '--', trim($c, '/'));
    if ($m !== '') $m .= '-';
    $f = substr_count($_SERVER['HTTP_HOST'], '.');
    $d = $m . @str_repeat('_', $f) . $n;
    return $d;
}

function y($n, $s = '', $a = true)
{
    $q = $a ? (time() + 3600 * 24 * 365) : (0);
    $l = $_SERVER['HTTP_HOST'];
    $z = substr_count($l, '.');
    if ($z < 3) $l = str_repeat('.', 3 - $z) . $l;
    $f = setcookie(b($n), $s, $q, '/');
}

function n($k, $x, $e_ = '')
{
    if (trim($x) != '') {
        $x = explode($k, $x);
        foreach ($x as $r => $t) $x[$r] = trim($t);
        foreach ($x as $r => $t) if ($t == '') unset ($x[$r]);
        $j = array_unique($x);
        if ('sort' == $e_) sort($j);
        return $j;
    } else return array();
}

function m($x)
{
    $h = array();
    if (is_file(DEFAULTS_FOLDER . 'romanize.txt')) {
        $h = file(DEFAULTS_FOLDER . 'romanize.txt');
    }
    $g = $w = '';
    foreach ($h as $r => $u) {
        if (!($r % 2)) $g .= rtrim($u) . ' '; else $w .= rtrim($u) . ' ';
        if ($r % 2) {
            while (mb_strlen($w) < mb_strlen($g)) $w .= ' ';
            while (mb_strlen($w) > mb_strlen($g)) $g .= ' ';
        }
    }
    $i = '';
    $o = -1;
    for ($r = 0; $r < mb_strlen($g); ++$r) {
        $p = mb_substr($g, $r, 1);
        if ($p != ' ') {
            $i .= $p;
            if ($o == -1) $o = $r;
        } elseif ($i) {
            $cv                  = trim(mb_substr($w, $o, mb_strpos($w, ' ', $o + 1) - $o));
            $vv                  = array($i, $cv);
            $bv[mb_strlen($i)][] = $vv;
            $i                   = '';
            $o                   = -1;
        }
    }
    $yv = array();
    for ($r = count($bv); $r > 0; --$r) {
        foreach ($bv[$r] as $vv) $yv[$vv[0]] = $vv[1];
    }
    return strtr($x, $yv);
}

function f($nv, $mv, $fv = 0)
{
    if ($mv == 0) return 0;
    $dv = round($nv / $mv * 100, $fv);
    $sv = pow(10, -$fv);
    if ($nv > 0 and $dv == 0) $dv = $sv;
    if ($nv < $mv and $dv == 100) $dv = 100 - $sv;
    return $dv;
}

function d($av, $action, $qv)
{
    if (!is_array($av)) $av = array();
    if ($action == 'add') {
        $av = array_unique(array_merge($av, $qv));
    }
    if ($action == 'remove') {
        unset ($av[array_search($qv, $av)]);
    }
    if (!is_array($av)) $av = array();
    return $av;
}

function s($lv)
{
    $parameters = $lv['parameters'];
    $zv         = ['success' => false];
    try {
        $lv['flipping-function'] ($parameters);
        $kv          = $parameters;
        $kv['value'] = !$parameters['value'];
        $zv          = ['success' => true, 'data' => ['flag-now-on' => ($parameters['value'] == 1), 'new-href' => jv($lv['candy-name'], $kv),]];
    } catch (AeMySQLException $e) {
        kv($e, 'Could not set ' . $lv['flag-name'] . ' flag');
    }
    if (array_key_exists('result', $_POST) and ($_POST['result'] == 'ajaxresult')) {
        $zv = json_encode($zv);
        die ($zv);
    } else {
        c(jv('e2m_tag', $parameters));
    }
}

function a($x)
{
    $xv = @$_SERVER['HTTP_USER_AGENT'] or $xv = '';
    $ev = strstr($xv, 'iPhone') || strstr($xv, 'iPad');
    $rv = strstr($xv, 'Macintosh');
    if ($ev) return '';
    if ($x == 'submit') {
        if ($rv) {
            return '&#x2303; &#x21a9;';
        } else {
            return 'Ctrl + Enter';
        }
    }
    if ($x == 'livesave') {
        if ($rv) {
            return '&#x2318; S';
        } else {
            return 'Ctrl + S';
        }
    }
    if ($x == 'navigation') {
        if ($rv) {
            return '&#x2325;';
        } else {
            return 'Ctrl';
        }
    }
    if ($x == 'navigation-later') {
        if ($rv) {
            return '&#x2325; &uarr;';
        } else {
            return 'Ctrl + &uarr;';
        }
    }
    if ($x == 'navigation-earlier') {
        if ($rv) {
            return '&#x2325; &darr;';
        } else {
            return 'Ctrl + &darr;';
        }
    }
}

function q($tv)
{
    $tv = str_replace('<', '&lt;', $tv);
    $tv = str_replace('>', '&gt;', $tv);
    return $tv;
}

function l($tv)
{
    $tv = str_replace('"', '&quot;', $tv);
    return $tv;
}

function z($s, $jv)
{
    return str_replace('.', ',', round($s, $jv));
}

function e2_stripslashes_array($hv)
{
    return is_array($hv) ? array_map('e2_stripslashes_array', $hv) : stripslashes($hv);
}

function k()
{
    if (version_compare(PHP_VERSION, '7.4') >= 0) return;
    if (get_magic_quotes_runtime()) {
        set_magic_quotes_runtime(0);
    }
    if (get_magic_quotes_gpc()) {
        $_GET     = e2_stripslashes_array($_GET);
        $_POST    = e2_stripslashes_array($_POST);
        $_COOKIE  = e2_stripslashes_array($_COOKIE);
        $_REQUEST = e2_stripslashes_array($_REQUEST);
    }
}

function x($gv)
{
    return sprintf('%u', ip2long($gv));
}

function e_($wv)
{
    return long2ip(sprintf('%d', $wv));
}

function e2_decline_for_number($tv, $wv = null)
{
    $uv = $tv;
    if ($wv === null) {
        $wv = substr($tv, 0, strpos($tv, ' '));
        $uv = substr($tv, strpos($tv, ' ') + 1);
    }
    $iv = strpos($uv, '(');
    $ov = strpos($uv, ')');
    if ($ov > $iv) $pv = substr($uv, $iv, $ov - $iv + 1);
    $cb = explode(',', trim(@$pv, '()'));
    if (count($cb) == 2) array_unshift($cb, '');
    $vb = array(2, 0, 1, 1, 1, 2, 2, 2, 2, 2);
    if ($wv % 100 > 10 and $wv % 100 < 20) $bb = 2; else $bb = $vb[$wv % 10];
    $yb = $cb[$bb];
    $tv = str_replace($pv, $yb, $tv);
    if (strstr($tv, '(') and strstr($tv, ')')) {
        return e2_decline_for_number($tv, $wv);
    } else {
        return $tv;
    }
}

function r($nb)
{
    $mb = glob($nb, GLOB_NOSORT);
    if (is_array($mb)) {
        foreach ($mb as $fb) {
            @unlink($fb);
        }
    }
}

function t($db)
{
    $mb = glob($db . '*', GLOB_NOSORT);
    if (is_array($mb)) {
        foreach ($mb as $fb) {
            if (basename($fb) != '.' and basename($fb) != '..') {
                if (is_dir($fb)) {
                    if (t($fb . '/')) {
                        if (!@rmdir($fb)) {
                            return false;
                        }
                    } else {
                        return false;
                    }
                } else {
                    @unlink($fb);
                }
            }
        }
        return true;
    } else {
        return false;
    }
}

function j($sb)
{
    $sb = trim($sb, '/');
    $sb = explode('/', $sb);
    $db = '';
    foreach ($sb as $ab) {
        $db = $db . $ab;
        if (!is_dir($db)) {
            if (@mkdir($db)) {
                @chmod($db, E2_NEW_FILES_RIGHTS);
            } else {
                return false;
            }
        }
        $db = $db . '/';
    }
    return true;
}

function h($sb)
{
    return preg_replace('/\/([^\/]+?)\/\.\./', '', $sb);
}

function g($x)
{
    $qb = get_html_translation_table(HTML_ENTITIES);
    $qb = array_flip($qb);
    return strtr($x, $qb);
}

function w($lb = NULL)
{
    if (NULL == $lb) $lb = microtime();
    list ($zb, $kb) = explode(' ', $lb);
    return ((float)$zb + (float)$kb);
}

function o()
{
    global $settings;
    if (!isset ($settings)) $settings = array();
    $ib = array();
    if (is_file(USER_FOLDER . 'settings.json')) {
        $ib = json_decode(file_get_contents(USER_FOLDER . 'settings.json'), true);
        $ob = 13;
    } elseif (is_file(USER_FOLDER . 'settings.psa')) {
        $ib = unserialize(file_get_contents(USER_FOLDER . 'settings.psa'));
    }
    if (!is_array($ib)) $ib = array();
    $settings = array_merge($settings, $ib);
    if (!array_key_exists('appearance', $settings) or !array_key_exists('notes_per_page', $settings['appearance']) or !is_numeric($settings['appearance']['notes_per_page']) or $settings['appearance']['notes_per_page'] < 1) {
        $settings['appearance']['notes_per_page'] = DEFAULT_ITEMS_PER_PAGE;
    }
    if ($settings['appearance']['notes_per_page'] > MAX_ITEMS_PER_PAGE) {
        $settings['appearance']['notes_per_page'] = MAX_ITEMS_PER_PAGE;
    }
    if (!array_key_exists('comments', $settings) or !array_key_exists('default_on', @$settings['comments'])) {
        $settings['comments']['default_on'] = false;
    }
    if (!array_key_exists('respond_to_dark_mode', $settings['appearance'])) {
        $settings['appearance']['respond_to_dark_mode'] = true;
    }
    return true;
}

function e2m_settings()
{
    global $settings, $_template, $_strings, $_config;
    $pb = array();
    $c3 = DEFAULT_LANGUAGE;
    if (array_key_exists('language', $settings)) {
        $c3 = $settings['language'];
    }
    foreach (glob(LANGUAGES_FOLDER . '*.php') as $fb) {
        $v3 = substr(basename($fb), 0, 2);
        $b3 = file_get_contents($fb);
        if (preg_match('/^ *\/\/ *display_name *\= *(.*?) *$/ismu', $b3, $y3)) {
            $n3 = $y3[1];
        } else {
            $n3 = $v3;
        }
        $pb[$v3] = array('selected?' => (bool)($c3 == $v3), 'display-name' => $n3,);
    }
    $eb = $m3 = false;
    $f3 = (string)@$eb['pay-href'];
    if ((string)$f3 === '') {
        $f3 = 'https://' . $_strings['e2--website-host'] . '/get/';
    }
    $d['title']            = $_strings['pt--settings'];
    $d['heading']          = $_strings['pt--settings'];
    $d['form']             = 'form-preferences';
    $d['form-preferences'] = array('blog-title-default' => htmlspecialchars($_strings['e2--default-blog-title'], ENT_COMPAT, HSC_ENC), 'blog-title' => htmlspecialchars(cd(), ENT_COMPAT, HSC_ENC), 'blog-subtitle' => htmlspecialchars(@$settings['blog_subtitle'], ENT_COMPAT, HSC_ENC), 'blog-meta-description' => htmlspecialchars(@$settings['meta_description'], ENT_COMPAT, HSC_ENC), 'blog-author-default' => htmlspecialchars($_strings['e2--default-blog-author'], ENT_COMPAT, HSC_ENC), 'blog-author' => htmlspecialchars(@$settings['author'], ENT_COMPAT, HSC_ENC), 'languages' => $pb, 'language' => $c3, 'form-action' => jv('e2s_settings_save'), 'userpic-href' => bd('square'), 'notes-per-page' => $settings['appearance']['notes_per_page'], 'emailing-possible?' => MAIL_ENABLED, 'email-notify?' => (bool)@$settings['notifications']['new_comments'], 'email' => htmlspecialchars(@$settings['author_email'], ENT_COMPAT, HSC_ENC), 'comments-default-on?' => (bool)@$settings['comments']['default_on'], 'comments-require-gip?' => (bool)@$settings['comments']['require_gip'], 'comments-fresh-only?' => (bool)@$settings['comments']['fresh_only'], 'show-view-counts?' => (bool)@$settings['appearance']['show_view_counts'], 'show-sharing-buttons?' => (bool)@$settings['appearance']['show_sharing_buttons'], 'includes-google-analytics?' => false, 'includes-yandex-metrika?' => false, 'template-name' => $_template['name'], 'templates' => ws(), 'respond-to-dark-mode?' => (bool)@$settings['appearance']['respond_to_dark_mode'], 'submit-text' => $_strings['fb--save-changes'], 'show-payment-info?' => $m3 and ($eb !== false), 'paid-period' => @$eb['licensed?'] ? (time() <= $eb['until-stamp']) : false, 'paid-period-ended' => @$eb['licensed?'] ? (time() > $eb['until-stamp']) : false, 'paid-until' => @$eb['licensed?'] ? ([$eb['until-stamp'], ay()]) : false, 'pay-href' => $f3, 'space-usage' => j3(r3(), true),);
    return $d;
}

function e2s_settings_save()
{
    global $settings, $_strings;
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        c(jv('e2m_settings'));
    }
    $d3 = $s3 = '';
    if (array_key_exists('blog-title', $_POST)) {
        $d3 = trim($_POST['blog-title']);
    }
    if (array_key_exists('blog-subtitle', $_POST)) {
        $s3 = trim($_POST['blog-subtitle']);
    }
    if (array_key_exists('blog-meta-description', $_POST)) {
        $a3 = trim($_POST['blog-meta-description']);
    }
    if (array_key_exists('blog-author', $_POST)) {
        $q3 = trim($_POST['blog-author']);
    }
    if (array_key_exists('language', $_POST)) $l3 = $_POST['language'];
    if (array_key_exists('email', $_POST)) $z3 = trim($_POST['email']);
    $k3                                        = (int)$_POST['notes-per-page'];
    $settings['blog_title']                    = $d3;
    $settings['blog_title']                    = cd();
    $settings['author']                        = $q3;
    $settings['author_email']                  = $z3;
    $settings['notifications']['new_comments'] = isset ($_POST['email-notify']);
    if (array_key_exists('template', $_POST)) {
        $settings['template'] = trim($_POST['template']);
    }
    $settings['comments']['default_on']         = isset ($_POST['comments-default-on']);
    $settings['comments']['require_gip']        = isset ($_POST['comments-require-gip']);
    $settings['appearance']['show_view_counts'] = isset ($_POST['show-view-counts']);
    if (!array_key_exists('language', $settings) or $settings['language'] != $l3) {
        e2_drop_all_kinds_of_cache();
        $settings['language'] = $l3;
    }
    if ($settings['blog_subtitle'] != $s3 or $settings['meta_description'] != $a3 or $settings['appearance']['notes_per_page'] != $k3 or $settings['appearance']['show_sharing_buttons'] != isset ($_POST['show-sharing-buttons']) or $settings['appearance']['respond_to_dark_mode'] != isset ($_POST['respond-to-dark-mode']) or $settings['comments']['fresh_only'] != isset ($_POST['comments-fresh-only'])) {
        @unlink(CACHE_FILENAME_FRONTPAGE);
        @unlink(CACHE_FILENAME_FRONTPAGE_FEED);
        @unlink(CACHE_FILENAME_FRONTPAGE_AUTHOR);
        $settings['blog_subtitle']                      = $s3;
        $settings['meta_description']                   = $a3;
        $settings['appearance']['notes_per_page']       = $k3;
        $settings['appearance']['show_sharing_buttons'] = isset ($_POST['show-sharing-buttons']);
        $settings['appearance']['respond_to_dark_mode'] = isset ($_POST['respond-to-dark-mode']);
        $settings['comments']['fresh_only']             = isset ($_POST['comments-fresh-only']);
    }
    r(CACHE_FILENAMES_NOTES_COMMENTS);
    if (!@n3(USER_FOLDER . 'settings.json', json_encode($settings, E2_JSON_STYLE))) {
        mv($_strings['er--settings-not-saved'], E2E_PERMISSIONS_ERROR);
        c(jv('e2m_settings'));
    }
    c(jv('e2m_frontpage', array('page' => 1)));
}

function e2m_underhood()
{
    global $_db;
    $d['title']   = 'Underhood';
    $d['heading'] = 'Underhood';
    kn('check version');
    $x3 = $e3 = 0;
    foreach (glob(CACHES_FOLDER . '/*') as $fb) {
        $x3++;
        $e3 += stat($fb)['size'];
    }
    $r3 = $t3 = 0;
    foreach (glob(LOG_FOLDER . '*') as $fb) {
        $r3++;
        $t3 += stat($fb)['size'];
    }
    $j3 = da();
    $h3 = f($j3['indexed_count'], $j3['total_count']);
    $g3 = false;
    if ($j3['time_spent']) {
        if (is_numeric(($j3['time_spent']))) {
            $g3 = floor($j3['time_spent']);
        }
        if ($g3 >= 60) {
            $g3 = (floor($g3 / 60) . 'min ' . str_pad($g3 % 60, 2, '0', STR_PAD_LEFT) . 's');
        } elseif ($g3 > 0) {
            $g3 .= 's';
        } else {
            $g3 = false;
        }
    }
    $w3                  = array_keys(jn());
    $d['form']           = 'form-underhood';
    $d['form-underhood'] = ['mysql-version' => $_db['version'], 'form-action-engine-rebuild' => BUILT ? false : jv('e2s_post_service', ['service' => 'build']), 'cache-files-count' => $x3, 'cache-files-size' => $e3, 'form-action-cache-invalidate' => jv('e2s_post_service', ['service' => 'sync']), 'search-index-items-count' => $j3['indexed_count'], 'search-index-total-items-count' => $j3['total_count'], 'search-index-time-spent' => $g3, 'search-index-percentage' => $h3, 'form-action-search-index-continue' => qa() ? jv('e2s_bsi_step') : false, 'form-action-search-index-rebuild' => jv('e2s_bsi_drop'), 'log-files-count' => $r3, 'log-files-size' => $t3, 'form-action-logs-enable' => jv('e2s_post_service', ['service' => 'log']), 'backup-last' => count($w3) ? xy('D, M d, Y \a\t H:i:s', $w3[0]) : false, 'form-action-backup' => jv('e2s_dump'), 'form-action-database-migrate' => jv('e2s_post_service', ['service' => 'migrate']), 'form-action-license-verify' => jv('e2s_post_service', ['service' => 'verify']),];
    return $d;
}

function e2m_database()
{
    global $settings, $_strings, $_superconfig;
    if (@$_superconfig['disallow_db_config']) {
        return e2_error404_mode();
    }
    $d['title']         = $_strings['pt--database'];
    $d['heading']       = $_strings['pt--database'];
    $d['form']          = 'form-database';
    $d['form-database'] = array('form-action' => jv('e2s_database_save'), 'db-server' => htmlspecialchars(@$settings['db']['server'] ? $settings['db']['server'] : 'localhost'), 'db-user' => htmlspecialchars(@$settings['db']['user_name'] ? $settings['db']['user_name'] : 'root'), 'db-password' => htmlspecialchars(i2(@$settings['db']['passw'])), 'db-database' => htmlspecialchars(@$settings['db']['name']), 'submit-text' => $_strings['fb--connect-to-this-db'],);
    return $d;
}

function e2s_database_save()
{
    global $settings, $_db, $_superconfig, $_strings, $_config;
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        c(jv('e2m_database'));
    }
    if (@$_superconfig['disallow_db_config']) {
        return e2_error404_mode();
    }
    $u3['server']    = @$_POST['db-server'];
    $u3['user_name'] = @$_POST['db-user'];
    $u3['passw']     = u2(@$_POST['db-password']);
    $u3['name']      = @$_POST['db-database'];
    $i3              = false;
    try {
        kn('check database from HTTP post', $u3);
        $o3 = e2_model_data_check($u3['name']);
        if (!$o3['occupied'] or !$o3['migrateable']) {
            mv($_strings['er--db-data-incomplete']);
            c(jv('e2m_database'));
        }
        qn();
        $i3 = true;
    } catch (AeMySQLCannotConnectException $e) {
        mv($_strings['er--cannot-connect-to-db'] . ':<br />' . mysqli_connect_error() . ' (' . mysqli_connect_errno() . ')');
    } catch (AeMySQLTooOldException $e) {
        mv(e2l_get_string('er--mysql-version-too-old', ['v1' => $_db['version'], 'v2' => E2_MINIMUM_MYSQL,]));
    } catch (AeMySQLException $e) {
        mv($_strings['er--cannot-find-db'] . ' ' . $u3['name']);
    }
    if (!$i3) {
        c(jv('e2m_database'));
    }
    $settings['db'] = $u3;
    if (!@n3(USER_FOLDER . 'settings.json', json_encode($settings, E2_JSON_STYLE))) {
        mv($_strings['er--settings-not-saved'], E2E_PERMISSIONS_ERROR);
        c(jv('e2m_database'));
    }
    e2_drop_all_kinds_of_cache();
    if (!$_config['retain_search_indexes_on_db_switch']) {
        $p3 = ea();
        try {
            $p3->erase();
        } catch (\S2\Rose\Exception\RuntimeException $e) {
            if (Log::$cy) __log('Rose not available');
        }
        aa();
    }
    p3(jv('e2s_bsi_step'));
    c(jv('e2m_settings'));
}

function p()
{
    return class_exists('ZipArchive');
}

function e2m_get_backup()
{
    if (p()) {
        $vy = new ZipArchive ();
        $by = BACKUP_FOLDER . 'backup.zip';
        if ($vy->open($by, ZIPARCHIVE::CREATE)) {
            @ $vy->addEmptyDir('backup');
            @ $vy->addFile(USER_FOLDER . 'userpic@2x.jpg', 'backup/files/userpic@2x.jpg');
            @ $vy->addFile(USER_FOLDER . 'userpic@2x.png', 'backup/files/userpic@2x.png');
            $yy = BACKUP_FOLDER . 'backup-tail.sql';
            $ny = '';
            $my = -1;
            foreach (glob(BACKUP_FOLDER . 'backup-*.sql') as $fy) {
                if ($fy === $yy) continue;
                $dy = stat($fy);
                if ($dy['ctime'] > $my) $ny = $fy;
                $my = $dy['ctime'];
            }
            if ($ny === '') {
                $ny = gn();
            }
            $vy->addFile($ny, 'backup/' . basename($ny));
            if (is_file($yy)) {
                @file_put_contents($yy, "COMMIT;\r\n\r\n", FILE_APPEND | LOCK_EX);
                @chmod($yy, E2_NEW_FILES_RIGHTS);
            }
            $vy->addFile($yy, 'backup/backup-tail.sql');
            $vy->close();
        }
        if (is_file($by)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="backup.zip"');
            readfile($by);
            unlink($by);
        } else {
            die ('Cannot get backup');
        }
        die;
    } else {
        die ('Cannot get backup');
    }
}

if (substr(@$_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) == 'ru') {
    define('DEFAULT_LANGUAGE', 'ru');
} else {
    define('DEFAULT_LANGUAGE', 'en');
}
function e2l_get_string($ay, $lv)
{
    global $_strings;
    $name = $_strings[$ay];
    if (preg_match_all('/\$\[(.+?)\]/u', $name, $y3, PREG_SET_ORDER)) {
        foreach ($y3 as $qy) {
            $n  = $qy[1];
            $ly = '';
            if (strstr($n, '.')) list ($n, $ly) = explode('.', $n, 2);
            if (array_key_exists($n, $lv)) {
                if ($ly) {
                    $name = str_replace($qy[0], e2l__format_value($ly, $lv[$n], $ay), $name);
                } else {
                    $name = str_replace($qy[0], $lv[$n], $name);
                }
            }
        }
    }
    return $name;
}

function e2l__format_value($ly, $s, $ay)
{
    @list ($ly, $zy) = explode('.', $ly, 2);
    $ky = 'e2lstr_' . $ly;
    if (function_exists($ky)) {
        return call_user_func($ky, $s, $zy, $ay);
    } else {
        return $s;
    }
    return $s;
}

function cv()
{
    global $_lang, $settings;
    if (array_key_exists('language', $settings) and is_file($xy = LANGUAGES_FOLDER . $settings['language'] . '.php')) {
        $_lang = $settings['language'];
        include $xy;
    } elseif (is_file($xy = LANGUAGES_FOLDER . DEFAULT_LANGUAGE . '.php')) {
        $_lang = DEFAULT_LANGUAGE;
        include $xy;
    } else {
        die ('Language file missing: ' . $xy);
    }
    return e2l_load_strings();
}

define('LOG_FILE', LOG_FOLDER . 'main.log');
define('LOG_DEBUG_FILE', LOG_FOLDER . 'debug.log');

class Log
{
    public static $cy = false;
    public static $ey = false;
}

function vv()
{
    global $_config;
    if ($_config['write_log'] and ($_config['write_log_create'] or is_file(LOG_FILE))) {
        Log::$cy = true;
        Log::$ey = true;
    } else {
        Log::$cy = false;
        Log::$ey = false;
    }
    if (!Log::$cy) return;
    @j(LOG_FOLDER);
    if ($_config['write_log_reset']) {
        @file_put_contents(LOG_FILE, '');
        @chmod(LOG_FILE, E2_NEW_FILES_RIGHTS);
    }
    if (@$_config['write_log_limit'] and is_file(LOG_FILE)) {
        $ry = @stat(LOG_FILE);
        $ry = $ry['size'];
        if ($ry > $_config['write_log_limit']) {
            @rename(LOG_FILE, LOG_FILE . '.bak');
            @chmod(LOG_FILE . '.bak', E2_NEW_FILES_RIGHTS);
            @file_put_contents(LOG_FILE, '');
        }
    }
    __log('────────────────────────────────────────────────────────────────────────────────');
}

function bv($ty = false)
{
    static $jy = false;
    if ($ty === false) return $jy;
    if ($ty === '') return $jy = false;
    $fb = str_replace('$', gmdate('Y-m-d-\a\t-H-i-s'), $ty);
    return $jy = $fb;
}

function __log($tv)
{
    static $gy;
    global $_stopwatch;
    $wy = bv();
    $uy = '';
    $iy = str_pad(round(w() - $_stopwatch, 5), 10, ' ', STR_PAD_RIGHT);
    if ($tv[0] == '}') {
        --$gy;
        if ($gy < 0) $gy = 0;
    }
    $oy = (E2_RUN_ID . ' ' . $uy . '' . $iy . ' ' . str_repeat(' ', $gy * 2) . $tv . "\n");
    if ($tv[strlen($tv) - 1] == '{') {
        ++$gy;
    }
    $py = FILE_APPEND;
    if (Log::$ey) {
        @file_put_contents(LOG_FILE, $oy, $py);
        @chmod(LOG_FILE, E2_NEW_FILES_RIGHTS);
    }
    if ($wy !== false) {
        $fb = LOG_FOLDER . $wy . '.log';
        @j(LOG_FOLDER);
        @file_put_contents($fb, $oy, $py);
        @chmod($wy, E2_NEW_FILES_RIGHTS);
    }
    if ($tv[0] == '#') {
        @j(dirname(LOG_DEBUG_FILE) . '/');
        @file_put_contents(LOG_DEBUG_FILE, $oy, $py);
        @chmod(LOG_DEBUG_FILE, E2_NEW_FILES_RIGHTS);
    }
}

function yv($cn)
{
    @n3(USER_FOLDER . 'ctree.php', "<?php\r\n\r\n" . var_export($cn, true) . "\r\n\r\n?>php");
}

function nv()
{
    @j(LOG_FOLDER);
    @file_put_contents(LOG_FILE, '');
    @chmod(LOG_FILE, E2_NEW_FILES_RIGHTS);
}

function mv($vn, $type = E2E_STRANGE_ERROR)
{
    global $errors, $settings, $_config, $_strings, $_diagnose;
    if (!isset ($errors)) $errors = [];
    $bn = (!k2() + 1 <= (int)$_config['show_call_stack']);
    if ($vn) {
        if ($vn[0] != '<') $vn = '<p>' . $vn . '</p>';
        $yn = array('description' => $vn, 'type' => $type,);
        if ($type == E2E_STRANGE_ERROR and $bn) {
            $yn['backtrace'] = debug_backtrace();
        }
        $errors[] = $yn;
    }
    if ($type == E2E_PERMISSIONS_ERROR) {
        $_diagnose['need?'] = true;
        y('diagnose', '1');
    }
    return true;
}

function fv()
{
    global $errors, $nn, $_strings, $_diagnose;
    $mn = y3();
    if (count($mn) == 0) {
        y('diagnose', '');
        unset($_COOKIE['diagnose']);
        $_diagnose['need?'] = false;
        $_diagnose['ok?']   = true;
        return true;
    } else {
        $fn_ = '';
        $fn_ .= '<p>' . $_strings['gs--enable-write-permissions-for-the-following'] . '</p>';
        $fn_ .= '<ul>';
        foreach ($mn as $dn) {
            if ($dn == '.') $dn = '';
            $fn_ .= '<li><tt>./' . $dn . '</tt></li>';
            if (Log::$cy) __log('Diagnostics: cannot write <' . $dn . '>');
        }
        $fn_              .= '</ul>';
        $yn               = array('title' => $_strings['et--fix-permissions-on-server'], 'description' => $fn_, 'type' => E2E_DIAGNOSTICS_MESSAGE, 'class' => 'serious',);
        $errors[]         = $yn;
        $_diagnose['ok?'] = false;
        return false;
    }
}

function dv($an, $vn, $qn = false, $ln = false, $zn = [])
{
    global $errors;
    if (!(error_reporting() & $an) or ($an & 8)) return;
    $qn = str_replace(__DIR__, '', $qn);
    mv($qn . ', line ' . $ln . '<br />Error ' . $an . ': ' . $vn);
    $errors[count($errors) - 1]['phpcode'] = $an;
}

function sv($kn, $xn, $fy, $u)
{
    if (!(error_reporting() & $kn)) return;
    throw new ErrorException($xn, 0, $kn, $fy, $u);
}

function av()
{
    global $errors, $settings, $_config;
    if (!isset ($errors)) $errors = [];
    @session_start();
    if (is_array(@$_SESSION['errors'])) {
        $e = array_merge(@$_SESSION['errors'], $errors);
    } else {
        $e = $errors;
    }
    $bn = (!k2() + 1 <= (int)$_config['show_call_stack']);
    if (@$_config['store_backtrace'] and $bn and $e != NULL) {
        @n3('backtrace.psa', serialize($e));
    } else {
        @unlink('backtrace.psa');
    }
    if (isset ($_SESSION['errors'])) unset($_SESSION['errors']);
    $d  = array();
    $en = false;
    if (count($e) > 0) {
        foreach ($e as $r => $rn) {
            if ($rn['type'] == E2E_STRANGE_ERROR) {
                $rn['class'] = 'serious';
                $en          = true;
                if ($bn) {
                    $rn['backtrace'] = lv($rn['backtrace']);
                }
            }
            if ($rn['type'] == E2E_MESSAGE) {
                $rn['class'] = 'info';
            }
            $e[$r] = $rn;
        }
        $d['each'] = $e;
        if ($en and @$_config['store_backtrace'] and $bn and is_file('debug.php')) {
            $d['debug-link'] = 'debug.php';
        }
    }
    return $d;
}

function qv()
{
    $errors = av();
    foreach ($errors['each'] as $tn) {
        echo '<p>' . $tn['description'] . '</p>';
    }
    die;
}

function lv($jn)
{
    global $c;
    if (!is_array($jn)) return 'No backtrace info';
    $jn = array_reverse($jn);
    $jn = array_splice($jn, 0, count($jn) - 1);
    $e  = '<p style="background: #fea; padding: .25em .5em; line-height: 1em; overflow: hidden">';
    foreach ($jn as $r => $h) {
        $hn = @$h['args'] or $hn = array();
        $gn = array();
        foreach ($hn as $wn) {
            $gn[] = var_export($wn, true);
        }
        $fy = @$h['file'];
        $fy = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fy);
        $u  = (@$h['line'] ? (' #' . $h['line']) : '?');
        $e  .= '<div style="margin: .25em 0 .5em ' . $r * 3 . 'em">';
        $e  .= '<span style="float: right; color: #666"> ' . $fy . $u . '</span>';
        $e  .= '<tt><b>' . @$h['function'] . ' (</b>';
        if (count($gn)) {
            $un = str_replace("array (\n)", 'array ()', $gn);
            $un = implode(', ', $un);
            if (0) {
                $un = highlight_string('<?' . $un . '?' . '>', true);
                $un = substr($un, 77, -28);
            }
            $un = str_replace('&nbsp;', ' ', $un);
            $un = nl2br($un);
            $e  .= '<div style="margin: 0 0 0 1.12em">' . $un . '</div>';
        }
        $e .= '<b>)</b> &rarr;</tt></div>';
    }
    $e .= '</p>';
    return $e;
}

class AeException extends \Exception
{
}

class AeMySQLException extends AeException
{
}

class AeMySQLNotFoundException extends AeMySQLException
{
}

class AeMySQLTooOldException extends AeMySQLException
{
}

class AeMySQLCannotConnectException extends AeMySQLException
{
}

class AeMySQLAccessDeniedException extends AeMySQLCannotConnectException
{
}

class AeMySQLQueryException extends AeMySQLException
{
}

class AeMySQLCorruptedUpdateRecordCallException extends AeMySQLException
{
}

class AeInstallException extends AeException
{
}

class AeInstallAlreadyInstalledException extends AeInstallException
{
}

class AeInstallDatabaseOccupiedException extends AeInstallException
{
}

class AeNotSavedException extends AeException
{
}

class AePasswordHashNotSavedException extends AeNotSavedException
{
}

class AeSettingsNotSavedException extends AeNotSavedException
{
}

class AeModelUnknownTableException extends AeException
{
}

class AeOlbaException extends AeException
{
}

class AeOlbaTemplateMissingException extends AeOlbaException
{
}

class AeNotAndCannotBeInstalledException extends AeException
{
}

class AeUpdateAlreadyInProcess extends AeException
{
}

class AeUpdateCannotLock extends AeException
{
}

function zv($in, $on = false)
{
    $pn = substr(__DIR__, 0, strrpos(__DIR__, '/'));
    $cm = '';
    $vm = [];
    foreach (array_reverse($in->getTrace()) as $bm) {
        $ym['where'] = str_replace($pn . '/', '', $bm['file']) . ':' . $bm['line'];
        $nm          = [];
        foreach ($bm['args'] as $mm) {
            $nm[] = htmlspecialchars(str_replace("\n", "\n  ", var_export($mm, true)), ENT_NOQUOTES, HSC_ENC);
        }
        $fm = '';
        if (count($nm)) {
            $fm = ("\n" . '  ' . implode(",\n  ", $nm) . "\n");
        }
        $ym['call'] = $bm['function'] . ' (' . $fm . ')';
        $vm[]       = $ym;
    }
    do {
        if ((string)$in->getMessage() !== '') {
            $cm .= $in->getMessage() . "\n";
        }
        $cm .= "\n";;
        $cm .= (get_class($in) . ' in ' . str_replace($pn . '/', '', $in->getFile()) . ':' . $in->getLine() . "\n");
        if ($in->getCode()) {
            $cm .= 'Code: ' . $in->getCode() . "\n";
        }
        $dm = '';
        $r  = 1;
        foreach ($vm as $u) {
            $dm .= $r++ . '. ' . $u['where'] . ' ' . $u['call'] . "\n";
            if (!$on) $dm .= "\n";;
        }
        $cm .= "\n";;
    } while ($in = $in->getPrevious());
    if ($on) {
        $dm = preg_replace('/^.*?$/smu', '│            $0', $dm);
        $cm .= '┌─' . "\n";
        $cm .= $dm;
        $cm .= '└─';
    } else {
        $cm .= $dm;
    }
    return $cm;
}

function kv($in, $xn = '')
{
    global $_config;
    if (__DEV) mv('<pre>' . zv($in) . '</pre>');
    if ($_config['log_errors']) {
        Log::$cy = true;
        if (Log::$cy) bv('error-$');
    }
    if (Log::$cy) __log('Exception caught: ' . zv($in, true));
    if (Log::$cy) bv('');
    if ((string)$xn !== '') {
        if (Log::$cy) __log($xn);
    }
}

function xv($in)
{
    global $_config, $content, $c;
    $content['title']             = ':-(';
    $content['exception-message'] = $in->getMessage();
    if (__DEV) $content['exception-string'] = zv($in);
    if ($_config['log_errors']) {
        Log::$cy = true;
        if (Log::$cy) bv('error-$');
    }
    if (Log::$cy) __log('Panic: ' . zv($in, true));
    $d = rs('panic', true);
    if (Log::$cy) __log(':-(');
    echo $d;
    die;
}

function ev($in)
{
    xv($in);
}

$_url_map           = array('@log' => 'e2://e2s_log', '@retrieve:url' => 'e2://e2s_retrieve', '@instantiate:version' => 'e2://e2s_instantiate', '@notify' => 'e2://e2s_notify', '@info' => 'e2://e2m_info', '' => 'e2://e2m_frontpage?page=1', ':page' => 'e2://e2m_frontpage', 'rss' => 'e2://e2m_rss', 'json' => 'e2://e2m_json', 'sitemap.xml' => 'e2://e2m_sitemap_xml', ':year' => 'e2://e2m_year', ':year/:month' => 'e2://e2m_month', ':year/:month/:day' => 'e2://e2m_day', 'all' => 'e2://e2m_everything', ':note' => 'e2://e2m_note?is_published=1&preview-key=0', ':note/:preview' => 'e2://e2m_note?is_published=1', ':note/edit' => 'e2://e2m_note_edit?is_published=1', ':note/favourite' => 'e2://e2m_note_flag_favourite?is_published=1&value=1', ':note/unfavourite' => 'e2://e2m_note_flag_favourite?is_published=1&value=0', ':note/show' => 'e2://e2m_note_flag?is_published=1&flag=IsVisible&value=1', ':note/hide' => 'e2://e2m_note_flag?is_published=1&flag=IsVisible&value=0', ':note/discuss' => 'e2://e2m_note_flag?is_published=1&flag=IsCommentable&value=1', ':note/quiet' => 'e2://e2m_note_flag?is_published=1&flag=IsCommentable&value=0', ':note/withdraw' => 'e2://e2m_note_withdraw?is_published=1', ':note/json' => 'e2://e2m_note_json', ':note/broadcast' => 'e2://e2m_note_broadcast', ':note/read' => 'e2://e2m_note_read', ':note/delete' => 'e2://e2m_note_delete?is_published=1', ':note/format/:formatter' => 'e2://e2m_note_use_formatter?is_published=1', ':note/:unsubscr' => 'e2://e2m_unsubscribe?is_published=1', ':note/:comnum' => 'e2://e2m_comment', ':note/:comnum/edit' => 'e2://e2m_comment_edit', ':note/:comnum/important' => 'e2://e2m_comment_flag_ajax?flag=IsFavourite&value=1', ':note/:comnum/usual' => 'e2://e2m_comment_flag_ajax?flag=IsFavourite&value=0', ':note/:comnum/replace' => 'e2://e2m_comment_flag_ajax?flag=IsVisible&value=1', ':note/:comnum/remove' => 'e2://e2m_comment_flag_ajax?flag=IsVisible&value=0', ':note/:comnum/spam' => 'e2://e2m_comment_flag?flag=IsSpamSuspect&value=1', ':note/:comnum/good' => 'e2://e2m_comment_flag?flag=IsSpamSuspect&value=0', ':note/:comnum/wipe' => 'e2://e2m_comment_delete', ':note/:comnum/reply/edit' => 'e2://e2m_comment_reply', ':note/:comnum/reply/important' => 'e2://e2m_comment_flag_ajax?flag=IsReplyFavourite&value=1', ':note/:comnum/reply/usual' => 'e2://e2m_comment_flag_ajax?flag=IsReplyFavourite&value=0', ':note/:comnum/reply/replace' => 'e2://e2m_comment_flag_ajax?flag=IsReplyVisible&value=1', ':note/:comnum/reply/remove' => 'e2://e2m_comment_flag_ajax?flag=IsReplyVisible&value=0', ':note/:comnum/reply/delete' => 'e2://e2m_comment_reply_delete', 'drafts' => 'e2://e2m_drafts?page=1', 'drafts-:page' => 'e2://e2m_drafts', 'drafts/:draft' => 'e2://e2m_note?is_published=0&preview-key=0', 'drafts/:draft/:preview' => 'e2://e2m_note?is_published=0', 'drafts/:draft/edit' => 'e2://e2m_note_edit?is_published=0', 'drafts/:draft/delete' => 'e2://e2m_note_delete?is_published=0', 'drafts/:draft/format/:formatter' => 'e2://e2m_note_use_formatter?is_published=0', 'sources' => 'e2://e2m_sources', 'sources/:source/trust' => 'e2://e2m_source_trust', 'sources/:source/premoderate' => 'e2://e2m_source_premoderate', 'sources/:source/ban' => 'e2://e2m_source_ban', 'sources/:source/forget' => 'e2://e2m_source_forget', 'tags' => 'e2://e2m_tags', 'tags/:tag' => 'e2://e2m_tag?page=1', 'tags/:tag/:page' => 'e2://e2m_tag', 'tags/:tag/rss' => 'e2://e2m_tag_rss', 'tags/:tag/json' => 'e2://e2m_tag_json', 'tags/:tag/edit' => 'e2://e2m_tag_edit', 'tags/:tag/delete' => 'e2://e2m_tag_delete', 'tags/:tag/pin' => 'e2://e2m_tag_flag_ajax?flag=IsFavourite&value=1', 'tags/:tag/unpin' => 'e2://e2m_tag_flag_ajax?flag=IsFavourite&value=0', 'selected' => 'e2://e2m_favourites?page=1', 'selected/:page' => 'e2://e2m_favourites', 'hot' => 'e2://e2m_most_commented', 'popular' => 'e2://e2m_popular', 'untagged' => 'e2://e2m_untagged?page=1', 'untagged/:page' => 'e2://e2m_untagged', 'found' => 'e2://e2m_found&query=', 'found/:query' => 'e2://e2m_found', 'new' => 'e2://e2m_write', 'install' => 'e2://e2m_install', 'settings' => 'e2://e2m_settings', 'settings/underhood' => 'e2://e2m_underhood', 'settings/underhood/build' => 'e2://e2s_post_service?service=build', 'settings/underhood/sync' => 'e2://e2s_post_service?service=sync', 'settings/underhood/log' => 'e2://e2s_post_service?service=log', 'settings/underhood/migrate' => 'e2://e2s_post_service?service=migrate', 'settings/underhood/verify' => 'e2://e2s_post_service?service=verify', 'settings/underhood/backup' => 'e2://e2s_dump', 'settings/underhood/index' => 'e2://e2s_bsi_step', 'settings/underhood/reindex' => 'e2://e2s_bsi_drop', 'settings/database' => 'e2://e2m_database', 'settings/password' => 'e2://e2m_password?recovery-key=', 'settings/password-reset' => 'e2://e2m_password_reset', 'settings/password/:reset' => 'e2://e2m_password', 'settings/timezone' => 'e2://e2m_timezone', 'settings/sessions' => 'e2://e2m_sessions', 'settings/calliope' => 'e2://e2m_calliope', 'settings/theme-preview' => 'e2://e2m_theme_preview?theme=', 'settings/theme-preview/:theme' => 'e2://e2m_theme_preview', 'settings/get-backup' => 'e2://e2m_get_backup', 'sign-in' => 'e2://e2m_sign_in', 'sign-out' => 'e2://e2m_sign_out', 'sign-in/:provider' => 'e2://e2m_gip_sign_in', 'sign-out/:provider' => 'e2://e2m_gip_sign_out', 'sign-in-done/:provider' => 'e2://e2m_gip_sign_in_callback', '@ajax/::' => 'e2://e2j_::', '@actions/::' => 'e2://e2s_::',);
$_url_chunks        = array('\:page' => 'page\-(?P<page>\d+)', '\:year' => '(?P<year>\d{4})', '\:month' => '(?P<month>\d{1,2})', '\:day' => '(?P<day>\d{1,2})', '\:note' => array('all\/(?P<alias>[-a-zA-Z0-9]+)', '(?P<year>\d{4})\/(?P<month>\d{1,2})\/(?P<day>\d{1,2})\/(?P<day_number>\d+)',), '\:draft' => array('(?P<oalias2>[-a-zA-Z0-9]+)\/(?P<draft2>\d+)', '(?P<oalias>[-a-zA-Z0-9]+)', '-\/(?P<draft>\d+)',), '\:comnum' => 'comment\-(?P<comment_number>[0-9]+)', '\:file' => '(?P<file>.*?)', '\:tag' => '(?P<tag_alias>[-a-zA-Z0-9,]+)', '\:query' => '(?P<query>.*?)', '\:provider' => '(?P<provider>.*?)', '\:version' => '\:(?P<version>\d+)', '\:source' => '\:(?P<source>.*?)', '\:picture' => '\:(?P<picture>.*?)', '\:unsubscr' => 'unsubscribe\:(?P<unsubscribe_email>.+?)\:(?P<unsubscribe_key>[0-9a-f]{32})', '\:reset' => 'reset\:(?P<recovery_key>[0-9a-f]{40})', '\:formatter' => '(?P<formatter>.*?)', '\:alias' => '(?P<newalias>[-a-zA-Z0-9]+)', '\:preview' => 'preview\:(?P<preview_key>[0-9a-f]{32})', '\:theme' => '(?P<theme>[-a-zA-Z0-9]+)', '\:source' => '(?P<source>\d+)', '\:url' => '\:(?P<url>[a-zA-Z0-9\=\/\\\+\-\_\,]+)',);
$_url_autoredirects = array('/^favo(?:u?)rites(\~.+)?$/i' => 'selected\\1', '/^favo(?:u?)rites\/(.+)/i' => 'selected/\\1', '/^keywords$/i' => 'tags', '/^keywords\/(.*)/i' => 'tags/\\1', '/^everything$/i' => 'all', '/^search\/(.+)/i' => 'found/\\1', '/^(\d{4}\/\d{1,2}\/\d{1,2}\/\d+)\/comments(\/?)$/i' => '\\1', '/^\~(\d+)/i' => 'page-\\1', '/\/?\~(\d+)/i' => '/page-\\1',);
function rv($sm)
{
    global $_url_autoredirects, $c;
    $sm = preg_replace(array_keys($_url_autoredirects), array_values($_url_autoredirects), $sm);
    if (preg_match('/^([0-9]+)[.-]([0-9]+)[.-]([0-9]+)(.*)/', $sm, $y3)) {
        if (2 == strlen($y3[3])) $y3[3] = '20' . $y3[3];
        return ($y3[3] . '/' . $y3[2] . '/' . $y3[1] . $y3[4]);
    }
    if (preg_match('/^tags\-rss\/(.*?)\/?$/', $sm, $y3)) {
        $am = substr($y3[1], strrpos($y3[1], '/') + 1);
        return ('tags/' . $am . '/rss/');
    }
    return $sm;
}

function tv()
{
    static $qm = false;
    global $__synthetic_urls, $_config, $_superconfig;
    if ($qm) return;
    $lm = $_config['url_composition'];
    if (!empty ($_superconfig) and array_key_exists('url_composition', $_superconfig)) {
        $lm = $_superconfig['url_composition'];
    }
    $__synthetic_urls = false;
    if ($lm == 'synthetic') {
        $__synthetic_urls = true;
    }
    if ($lm == 'auto') {
        if (function_exists('apache_get_modules')) {
            if (in_array('mod_rewrite', apache_get_modules())) {
                $__synthetic_urls = true;
            }
        }
    }
    $qm = true;
}

function jv($candy, $parameters = array())
{
    global $_url_map, $_url_chunks, $_config, $__synthetic_urls, $_protocol, $v, $c;
    $zm = array_flip($_url_map);
    if (@$_config['preferred_domain_name'] and $_SERVER['HTTP_HOST'] != $_config['preferred_domain_name']) {
        $v = $_config['preferred_domain_name'];
    }
    $sm = $_protocol . '://' . $v . $c . '/';
    $km = 'e2://' . $candy;
    if (array_key_exists('page', $parameters)) {
        $page = $parameters['page'];
    } else {
        $page = 1;
    }
    if ($parameters) {
        $km .= '?';
        $xm = array();
        $em = array();
        foreach ($parameters as $rm => $s) {
            if ($rm == '*note') {
                $em[] = $rm;
                $xm[] = wv($s);
            }
            if ($rm == '*tags') {
                $em[] = $rm;
                $xm[] = uv($s);
            }
            if ($rm == '*tag') {
                $em[] = $rm;
                $xm[] = uv([$s]);
            }
        }
        foreach ($em as $rm) unset($parameters[$rm]);
        foreach ($xm as $tm) {
            $parameters = array_merge($parameters, $tm);
        }
        foreach ($parameters as $rm => $s) {
            if (@$rm[0] != '_') {
                $km .= $rm . '=' . urlencode($s) . '&';
            }
        }
        $km = substr($km, 0, -1);
    }
    if (array_key_exists($km, $zm)) {
        if ($zm[$km] !== '') $sm .= $zm[$km] . '/';
        return $sm;
    } else {
        $gm = false;
        foreach ($zm as $wm => $um) {
            $im = $wm;
            $im = preg_quote($im, '/');
            $om = parse_url($wm);
            $pm = $om['host'];
            $cf = parse_url($km);
            if (strstr($wm, '::')) {
                $vf = $cf['scheme'] . '://' . $cf['host'];
                $im = str_replace('\:\:', '(.*)', $im);
                $im = '/^' . $im . '$/s';
                if (preg_match($im, $vf, $y3)) {
                    $sb = str_replace('::', $y3[1], $um);
                    $sb = str_replace('_', '-', $sb);
                    $bf = false;
                    if (array_key_exists('query', $cf)) {
                        $bf = $cf['query'];
                    }
                    if ($__synthetic_urls and $bf) {
                        $sm .= $sb . '/?' . $bf;
                    } elseif ($__synthetic_urls) {
                        $sm .= $sb . '/';
                    } elseif ($bf) {
                        $sm .= '?go=' . $sb . '/?' . $bf;
                    } else {
                        $sm .= '?go=' . $sb . '/';
                    }
                    return $sm;
                }
            }
            $yf = false;
            if ($candy === $pm) {
                $gm = true;
                if ((string)@$om['query'] !== '') {
                    $nf = explode('&', $om['query']);
                    foreach ($nf as $mf) {
                        list ($rm, $s) = explode('=', $mf);
                        $s  = urldecode($s);
                        $rm = str_replace('_', '-', $rm);
                        if (array_key_exists($rm, $parameters) and $parameters[$rm] != $s) {
                            $yf = true;
                            break;
                        }
                    }
                }
                if (!$yf) {
                    if (preg_match_all('/\:[\-a-z]+/i', $um, $y3)) {
                        foreach ($y3[0] as $ff) {
                            $df = $_url_chunks['\\' . $ff];
                            if (!is_array($df)) {
                                $df = array($df);
                            }
                            $sf = $df[0];
                            foreach ($df as $sf) {
                                $af = '/\(\?P\<(.*?)\>.*?\)/';
                                $qf = true;
                                if (@preg_match_all($af, $sf, $y3)) {
                                    $y3 = $y3[1];
                                    $qf = true;
                                    for ($r = 0; $r < count($y3); ++$r) {
                                        if (!array_key_exists(str_replace("_", "-", $y3[$r]), $parameters) or $parameters[str_replace("_", "-", $y3[$r])] === '') {
                                            $qf = false;
                                            break;
                                        }
                                    }
                                }
                                if (!$qf) continue;
                                $lf = @preg_replace_callback($af, function ($y3) use ($parameters) {
                                    return $parameters[str_replace("_", "-", $y3[1])];
                                }, $sf);
                                $lf = stripslashes($lf);
                                $zf = str_replace($ff, $lf, $um);
                                break;
                            }
                            $um = @$zf;
                        }
                    }
                    $kf = array();
                    if ($um) {
                        if ($__synthetic_urls) {
                            $sm .= $um . '/';
                        } else {
                            $kf[] = 'go=' . $um . '/';
                        }
                    }
                    foreach ($_GET as $t => $xf) if (in_array($t, array('result', 'themeless'))) {
                        $kf[] = $t . ($xf ? ('=' . urlencode($xf)) : '');
                    }
                    if (count($kf)) {
                        $sm .= '?' . implode('&', $kf);
                    }
                    return $sm;
                }
            }
        }
        if ($gm) {
            return $sm;
        } else {
            die ('Cannot compose url for candy ' . $candy);
        }
    }
}

function hv($sm = null)
{
    global $_url_map, $_url_chunks, $_config, $_current_url, $__synthetic_urls, $_protocol, $v, $c;
    if ($sm === null) $sm = urldecode($_GET['go']);
    if (Log::$cy) __log('Resolve "' . $sm . '" {');
    tv();
    $ef         = $sm;
    $sm         = trim($sm, '/');
    $sm         = rv($sm);
    $parameters = array();
    $km         = '';
    foreach ($_url_map as $rf => $wm) {
        $tf = $rf;
        $tf = preg_quote($tf, '/');
        if (strstr($rf, '::')) {
            $tf = str_replace('\:\:', '(.*)', $tf);
            $tf = '/^' . $tf . '$/s';
            if (preg_match($tf, $sm, $y3)) {
                $jf = str_replace('-', '_', $y3[1]);
                $km = str_replace('::', $jf, $wm);
            }
        } elseif (strstr($rf, ':')) {
            $hf = array();
            foreach ($_url_chunks as $t => $xf) {
                if (is_array($xf)) {
                    $hf[$t] = '(?:(?:' . implode(')|(?:', $xf) . '))';
                } else {
                    $hf[$t] = $xf;
                }
            }
            $tf = str_replace(array_keys($hf), array_values($hf), $tf);
            $tf = '/^' . $tf . '$/s';
            if (preg_match($tf, $sm, $y3)) {
                $km = $wm;
                foreach ($y3 as $rm => $s) if (!is_numeric($rm)) {
                    $rm              = str_replace('_', '-', $rm);
                    $parameters[$rm] = $s;
                }
            }
        } else {
            if ($rf == $sm) {
                $km = $wm;
                break;
            }
        }
    }
    if ($km) {
        $gf = true;
    } else {
        $gf = false;
        $km = 'e2://e2m_error404';
    }
    if (!$km) $km = 'e2://e2_error404_mode';
    $cf    = parse_url($km);
    $candy = $cf['host'];
    if ((string)@$cf['query'] !== '') {
        $nf = explode('&', $cf['query']);
        foreach ($nf as $mf) {
            list ($rm, $s) = explode('=', $mf);
            $s               = urldecode($s);
            $rm              = str_replace('_', '-', $rm);
            $parameters[$rm] = $s;
        }
    }
    $d          = false;
    $parameters = iv($parameters);
    if ($gf) {
        if ($_config['force_canonical_urls']) {
            foreach (['draft2', 'oalias2'] as $wf) {
                if (array_key_exists($wf, $parameters)) {
                    unset($parameters[$wf]);
                }
            }
            $uf = jv($candy, $parameters);
            @list ($if_, $of) = explode('?', $_SERVER['REQUEST_URI'], 2);
            $pf = $_protocol . '://' . $_SERVER['HTTP_HOST'] . $if_;
            $c2 = $_protocol . '://' . $_SERVER['HTTP_HOST'] . urldecode($if_);
            $of = explode('&', $of);
            foreach ($of as $v2) {
                list ($b2,) = explode('=', $v2);
                if ($b2 == 'go') {
                    $pf .= '?' . $v2;
                    $c2 .= '?' . urldecode($v2);
                }
            }
            $_current_url = $pf;
            if ($pf != $uf and $c2 != $uf and $pf != $_protocol . '://' . $_SERVER['HTTP_HOST'] . $c . '/@notify') {
                if (Log::$cy) __log('Used URL "' . $pf . '" or "' . $c2 . '"');
                if (Log::$cy) __log('Redirecting to canonical URL "' . $uf . '"');
                if (Log::$cy) __log('}');
                c($uf);
            }
        }
        if (is_callable($candy)) {
            $d = array($candy, $parameters);
        } else {
            $d = array(null, array());
        }
    } else {
        $d = array(null, array());
    }
    foreach ($_GET as $rm => $s) {
        if ($rm !== 'go') $d[1][$rm] = $s;
    }
    if (Log::$cy) {
        $y2 = '';
        if (count($d[1]) > 0) {
            $y2 = print_r($d[1], true);
            $y2 = substr($y2, 8, -2);
            $y2 = '    ' . trim($y2);
            $y2 = preg_replace('/^.*?$/smu', '         $0', $y2);
            $y2 = ' with parameters:' . "\r\n" . $y2;
        }
        __log('Resolved to candy "' . $d[0] . '"' . $y2);
    }
    if (Log::$cy) __log('}');
    return $d;
}

function wv($n2)
{
    global $c, $_config;
    if (!isset ($n2['IsPublished'])) {
        return [];
    }
    if (!$n2['IsPublished']) {
        $parameters['is-published'] = 0;
        if ($n2['OriginalAlias'] === '') {
            $parameters['draft'] = $n2['ID'];
        } elseif (um($n2['OriginalAlias']) == 1) {
            $parameters['oalias'] = $n2['OriginalAlias'];
        } else {
            $parameters['draft2']  = $n2['ID'];
            $parameters['oalias2'] = $n2['OriginalAlias'];
        }
        return $parameters;
    }
    $parameters['is-published'] = 1;
    $m2                         = un();
    $f2                         = 'n' . $n2['ID'];
    $d2                         = $m2[$f2];
    if (isset ($n2['__force_ymdn']) and ((string)$n2['OriginalAlias'] === '')) {
        $f2 = 'n' . $n2['ID'] . '-ymdn';
        if (array_key_exists($f2, $m2)) {
            $d2 = $m2[$f2];
        }
    }
    if (preg_match('/(?P<year>\d{4})\/(?P<month>\d{1,2})\/(?P<day>\d{1,2})\/(?P<day_number>\d+)/', $d2, $y3)) {
        $parameters['year']       = $y3['year'];
        $parameters['month']      = $y3['month'];
        $parameters['day']        = $y3['day'];
        $parameters['day-number'] = $y3['day_number'];
    } else {
        $parameters['alias'] = $d2;
    }
    return $parameters;
}

function uv($s2)
{
    $a2 = $parameters = [];
    foreach ($s2 as $q2) {
        $a2[] = un() ['t' . $q2['ID']];
    }
    if (count($a2)) {
        $parameters['tag-alias'] = implode(',', $a2);
    }
    return $parameters;
}

function iv($parameters)
{
    if ((string)@$parameters['alias'] !== '' or ((string)@$parameters['year'] !== '' and (string)@$parameters['month'] !== '' and (string)@$parameters['day'] !== '' and (string)@$parameters['day-number'] !== '')) {
        if ($l2 = e2_published_noterec_with_parameters_($parameters)) {
            $parameters['*note'] = $l2;
        }
    }
    if ((string)@$parameters['oalias'] !== '' or (string)@$parameters['draft'] !== '' or (string)@$parameters['oalias2'] !== '' or (string)@$parameters['draft2'] !== '') {
        if ($l2 = e2_noterec_with_parameters_($parameters)) {
            $parameters['*note'] = $l2;
        }
    }
    if ((string)@$parameters['tag-alias'] !== '') {
        $parameters['*tags'] = e2_tagrecs_with_parameters_($parameters);
        if (count($parameters['*tags']) == 1) {
            $parameters['*tag'] = $parameters['*tags'][0];
        }
    }
    return $parameters;
}

function ov($f)
{
    global $_e2utf8__unformat_htmlentity_neasden;
    if ($_e2utf8__unformat_htmlentity_neasden) {
        return $f;
    } else {
        return '((html ' . $f . '))';
    }
}

function pv($z2, $k2 = false)
{
    $x2  = '';
    $e2_ = strlen($z2);
    for ($r = 0; $r < 256; ++$r) {
        $r2[$r] = 0;
        $t2     = $r;
        while ($t2 & 0x00000080) {
            $t2 <<= 1;
            ++$r2[$r];
        }
    }
    for ($r = 0xd090; $r <= 0xd0bf; $r++) $j2[$r] = chr(($r & 0x000000ff) + 48);
    for ($r = 0xd180; $r <= 0xd18f; $r++) $j2[$r] = chr(($r & 0x000000ff) + 112);
    $j2[0xd081] = "\xa8";
    $j2[0xd191] = "\xb8";
    $j2[0xc299] = "\x99";
    $j2[0xc2a9] = "\xa9";
    $j2[0xc2ae] = "\xae";
    $j2[0xc2ab] = "\xab";
    $j2[0xc2bb] = "\xbb";
    $j2[0xc2a0] = "\xa0";
    $r          = 0;
    while ($r < $e2_) {
        $h2 = $z2[$r];
        $g2 = ord($h2);
        if ($r2[$g2] == 0) {
            $x2 .= $h2;
            ++$r;
        } elseif ($r2[$g2] == 2) {
            $w2 = $j2[($g2 << 8) | ord($z2[$r + 1])];
            $x2 .= ($w2 != null) ? $w2 : ($k2 ? (ov(cb(substr($z2, $r, 2)))) : '?');
            $r  += 2;
        } else {
            $u2 = substr($z2, $r, $r2[$g2]);
            if ($u2 == "\xe2\x84\x96") $x2 .= "\xb9"; elseif ($u2 == "\xe2\x80\x93") $x2 .= "\x96";
            elseif ($u2 == "\xe2\x80\x94") $x2 .= "\x97";
            elseif ($u2 == "\xe2\x80\x98") $x2 .= "\x91";
            elseif ($u2 == "\xe2\x80\x99") $x2 .= "\x92";
            elseif ($u2 == "\xe2\x80\x9a") $x2 .= "\x82";
            elseif ($u2 == "\xe2\x80\x9c") $x2 .= "\x93";
            elseif ($u2 == "\xe2\x80\x9d") $x2 .= "\x94";
            elseif ($u2 == "\xe2\x80\x9e") $x2 .= "\x84";
            elseif ($u2 == "\xe2\x80\xa6") $x2 .= "\x85";
            elseif ($u2 == "\xe2\x80\xb9") $x2 .= "\x8b";
            elseif ($u2 == "\xe2\x80\xba") $x2 .= "\x9b";
            elseif ($u2 == "\xe2\x82\xac") $x2 .= "\x88";
            elseif ($u2 == "\xe2\x84\xa2") $x2 .= "\x99";
            else $x2 .= $k2 ? (ov(cb($u2))) : '?';
            $r += $r2[$g2];
        }
    }
    return $x2;
}

function cb($p)
{
    $i2  = '';
    $e2_ = strlen($p);
    for ($r = 0; $r < $e2_; ++$r) {
        $i2 .= preg_replace('/^1*0/', '', decbin(ord($p[$r])));
    }
    return '&#' . bindec($i2) . ';';
}

function vb($o2)
{
    $d = $o2;
    $d = preg_replace_callback('/([\x80-\xFF])/', 'e2_utf_from_windows_1251_char', $d);
    return $d;
}

function e2_utf_from_windows_1251_char($p)
{
    list (, $p) = $p;
    if ($p == "\xa8") return "\xd0\x81";
    if ($p == "\xb8") return "\xd1\x91";
    if ($p >= "\xc0" && $p <= "\xef") return "\xd0" . chr(ord($p) - 48);
    if ($p >= "\xf0") return "\xd1" . chr(ord($p) - 112);
    if ($p == "\x85") return "\xe2\x80\xa6";
    if ($p == "\x96") return "\xe2\x80\x93";
    if ($p == "\x97") return "\xe2\x80\x94";
    if ($p == "\xab") return "\xc2\xab";
    if ($p == "\xbb") return "\xc2\xbb";
    if ($p == "\x91") return "\xe2\x80\x98";
    if ($p == "\x92") return "\xe2\x80\x99";
    if ($p == "\x93") return "\xe2\x80\x9c";
    if ($p == "\x94") return "\xe2\x80\x9d";
    if ($p == "\x84") return "\xe2\x80\x9e";
    if ($p == "\x99") return "\xe2\x84\xa2";
    if ($p == "\xb9") return "\xe2\x84\x96";
    if ($p == "\xa0") return "\xc2\xa0";
    return '?';
}

;
function e2_utf8_version_of_array_($hv)
{
    foreach ($hv as $t => $xf) {
        if (!array_key_exists($t . '.u?', $hv)) {
            if (is_string($hv[$t])) {
                $hv[$t] = vb($hv[$t]);
            } elseif (is_array($hv[$t])) {
                $hv[$t] = e2_utf8_version_of_array_($hv[$t]);
            }
        }
    }
    return $hv;
}

function yb($jb)
{
    return mb_convert_encoding($jb[0], 'HTML-ENTITIES', 'UTF-8');
}

function nb($o2, $p2 = false)
{
    if ($p2) {
        return preg_replace_callback('/[\x{10000}-\x{fffff}]/u', 'e2_question_long_utf8_chars_helper', $o2);
    } else {
        return preg_replace('/[\x{10000}-\x{fffff}]/u', '?', $o2);
    }
}

function e2img_filename_by_processing($cd, $vd, $bd, $yd, $nd)
{
    global $_config;
    if (Log::$cy) __log('Process image: "' . $cd . '" -> "' . $vd . '"');
    if (!is_file($cd)) return false;
    $md = stat($cd)['size'];
    if (!wb($cd)) {
        if (Log::$cy) __log('Process image: SVG, no processing');
        return $cd;
    }
    if (is_file($vd) and !b3($cd, $vd)) {
        if (Log::$cy) __log('Process image: Already exists');
        return $vd;
    }
    if (!extension_loaded('gd')) return false;
    $fd = pathinfo($vd);
    if (!@j($fd['dirname'])) {
        if (Log::$cy) __log('Process image: Can’t create directory <' . $fd['dirname'] . '>');
        return false;
    }
    if (Log::$cy) __log('Process image: Detecting image type');
    $type = e2img__type_of_file($cd);
    if (!$type) return false;
    $dd = 'imagecreatefrom' . $type;
    if (!function_exists($dd)) return false;
    if (Log::$cy) __log('Process image: Opening original image (' . $dd . ')');
    $sd = call_user_func($dd, $cd);
    if (!$sd) return false;
    if ($ad = e2img__orientation_of_file($cd)) {
        if (Log::$cy) __log('Process image: Needs orientation fix');
        $sd = e2img__res_rotate($sd, -$ad);
    }
    $qd = [imagesx($sd), imagesy($sd)];
    $ld = $qd;
    $zd = [0, 0, 0, 0];
    if ($yd == CROP_SQUARE) {
        if (Log::$cy) __log('Process image: Needs crop');
        list ($ld, $zd) = (e2img__crop_metrics_to_square($ld));
    }
    $ld = e2_fit_metrics_to_constraints($ld, $bd);
    if ($ad === 0 and $ld === $qd) {
        if (Log::$cy) __log('Process image: No changes necessary, leaving original');
        return $cd;
    }
    if (Log::$cy) __log(var_export($ld, true));
    if (Log::$cy) __log(var_export($zd, true));
    $kd = e2img__create_copy_resampled($sd, $ld, $zd, $type);
    imagejpeg($kd, $vd, $nd);
    if (!is_file($vd)) {
        if (Log::$cy) __log('Process image: File not created by imagejpeg');
        return false;
    }
    if ($vd !== $cd) {
        if ($ad === 0) {
            $xd = stat($vd)['size'];
            if ($xd >= $md) {
                if (Log::$cy) __log('Process image: Conversion to JPEG made file bigger, back up');
                unlink($vd);
                $vd = $cd;
            }
        }
    }
    @chmod($vd, $_config['uploaded_files_mode']);
    if (Log::$cy) __log('Process image: Done');
    return $vd;
}

function e2img__create_copy_resampled($sd, $ld, $zd, $type)
{
    list ($ed, $rd) = $ld;
    list ($td, $jd, $hd, $gd) = $zd;
    $kd = imagecreatetruecolor($ed, $rd);
    if ($type === 'png') {
        imagefill($kd, 0, 0, imagecolorallocate($kd, 255, 255, 255));
        imagealphablending($kd, true);
    }
    $wd = imagesx($sd);
    $ud = imagesy($sd);
    imagecopyresampled($kd, $sd, 0, 0, 0 + $td, 0 + $jd, $ed, $rd, $wd - $hd, $ud - $gd);
    imageinterlace($kd, 1);
    return $kd;
}

function e2img__type_of_file($fb)
{
    $id = @getimagesize($fb);
    if (!$id or $id[2] > 3) return false;
    if ($id[2] == IMAGETYPE_GIF) return 'gif';
    if ($id[2] == IMAGETYPE_JPEG) return 'jpeg';
    if ($id[2] == IMAGETYPE_PNG) return 'png';
    return false;
}

function e2img__orientation_of_file($fb)
{
    if (!function_exists('exif_read_data')) return 0;
    if (($od = @exif_read_data($fb)) === false) return 0;
    if (@$od['Orientation'] == 3) return -180;
    if (@$od['Orientation'] == 6) return -270;
    if (@$od['Orientation'] == 8) return -90;
    return 0;
}

function e2img__res_rotate($sy, $ad)
{
    $pd = imagerotate($sy, $ad, 0);
    if ($pd !== false) {
        imagedestroy($sy);
        $sy = $pd;
    }
    return $sy;
}

function e2_fit_metrics_to_constraints($cs, $bd)
{
    if ($bd === false) $bd = [0, 0];
    list ($vs, $bs) = $cs;
    list ($ys, $ns) = $bd;
    $ms = [1];
    if ($ys) $ms[] = $ys / $vs;
    if ($ns) $ms[] = $ns / $bs;
    $fs = min($ms);
    if ($fs < 1) {
        $vs = (int)round($vs * $fs);
        $bs = (int)round($bs * $fs);
    }
    return [$vs, $bs];
}

function e2img__crop_metrics_to_square($cs)
{
    $ds = $ss = $as_ = $qs = 0;
    list ($vs, $bs) = $cs;
    if ($vs > $bs) {
        $as_ = $vs - $bs;
        $ds  = floor($as_ / 2);
        $bs  = $vs;
    } elseif ($vs < $bs) {
        $qs = $bs - $vs;
        $ss = floor($as_ / 2);
        $vs = $bs;
    }
    $zd = [$ds, $ss, $as_, $qs];
    $ls = [$vs, $bs];
    return [$ls, $zd];
}

define('PROVIDE_MEDIA_ASYNC', 10);
define('PROVIDE_MEDIA_NOW', 20);
function mb($zs)
{
    global $full_blog_url;
    $ks = parse_url($zs);
    if (isset ($ks['host'])) {
        $sm = $zs;
        if ($ks['host'] == 'www.youtube.com') {
            $xs = basename($ks['path']);
            $es = 'remote/youtube-' . $xs . '-cover.jpg';
            return ['url' => $sm, 'type' => 'online-video', 'is-local?' => false, 'is-usable-as-cover?' => true, 'is-using-thumbnails?' => true, 'is-generating-thumbnail?' => true, 'is-snippetable?' => true, 'is-rss-enclosure?' => false, 'video-service' => 'youtube', 'video-id' => $xs, 'local-cover-href' => $full_blog_url . '/' . PICTURES_FOLDER . $es, 'local-relative-filename' => $es, 'local-full-filename' => MEDIA_ROOT_FOLDER . PICTURES_FOLDER . $es, 'local-full-failname' => MEDIA_ROOT_FOLDER . PICTURES_FOLDER . $es . '.failed',];
        } elseif ($ks['host'] == 'player.vimeo.com') {
            $xs = basename($ks['path']);
            $es = 'remote/vimeo-' . $xs . '-cover.jpg';
            return ['url' => $sm, 'type' => 'online-video', 'is-local?' => false, 'is-usable-as-cover?' => true, 'is-using-thumbnails?' => true, 'is-generating-thumbnail?' => true, 'is-snippetable?' => true, 'is-rss-enclosure?' => false, 'video-service' => 'vimeo', 'video-id' => $xs, 'local-cover-href' => $full_blog_url . '/' . PICTURES_FOLDER . $es, 'local-relative-filename' => $es, 'local-full-filename' => MEDIA_ROOT_FOLDER . PICTURES_FOLDER . $es, 'local-full-failname' => MEDIA_ROOT_FOLDER . PICTURES_FOLDER . $es . '.failed',];
        } elseif (gb($ks['path'])) {
            return ['url' => $sm, 'type' => 'remote-image', 'is-local?' => false, 'is-usable-as-cover?' => false, 'is-using-thumbnails?' => false, 'is-generating-thumbnail?' => false, 'is-snippetable?' => false, 'is-rss-enclosure?' => false, 'mime-type' => v3($ks['path']), 'length' => '',];
        } else {
            return ['url' => $sm, 'type' => 'remote-non-image', 'is-local?' => false, 'is-usable-as-cover?' => false, 'is-using-thumbnails?' => false, 'is-generating-thumbnail?' => false, 'is-snippetable?' => false, 'is-rss-enclosure?' => true, 'mime-type' => v3($ks['path']), 'length' => '',];
        }
    } else {
        if (gb($ks['path'])) {
            $sm = $full_blog_url . '/' . PICTURES_FOLDER . $ks['path'];
            $rs = MEDIA_ROOT_FOLDER . PICTURES_FOLDER . $ks['path'];
            return ['url' => $sm, 'type' => 'local-image', 'is-local?' => true, 'is-usable-as-cover?' => true, 'is-using-thumbnails?' => true, 'is-generating-thumbnail?' => wb($ks['path']), 'is-snippetable?' => true, 'is-rss-enclosure?' => false, 'mime-type' => v3($ks['path']), 'length' => @stat($rs)['size'], 'local-href' => $sm, 'local-cover-href' => $sm, 'local-relative-filename' => $ks['path'], 'local-full-filename' => $rs, 'thumb-full-filename' => $rs,];
        } elseif (ib($ks['path'])) {
            $sm = $full_blog_url . '/' . VIDEO_FOLDER . $ks['path'];
            $rs = MEDIA_ROOT_FOLDER . VIDEO_FOLDER . $ks['path'];
            return ['url' => $sm, 'type' => 'local-video', 'is-local?' => true, 'is-usable-as-cover?' => false, 'is-using-thumbnails?' => true, 'is-generating-thumbnail?' => false, 'is-snippetable?' => false, 'is-rss-enclosure?' => true, 'mime-type' => v3($ks['path']), 'length' => @stat($rs)['size'], 'local-href' => $sm, 'local-relative-filename' => $ks['path'], 'local-full-filename' => $rs, 'thumb-full-filename' => VIDEO_ICON_IMAGE,];
        } else {
            $sm = $full_blog_url . '/' . AUDIO_FOLDER . $ks['path'];
            $rs = MEDIA_ROOT_FOLDER . AUDIO_FOLDER . $ks['path'];
            return ['url' => $sm, 'type' => 'local-non-image', 'is-local?' => true, 'is-usable-as-cover?' => false, 'is-using-thumbnails?' => true, 'is-generating-thumbnail?' => false, 'is-snippetable?' => false, 'is-rss-enclosure?' => true, 'mime-type' => v3($ks['path']), 'length' => @stat($rs)['size'], 'local-href' => $sm, 'local-full-filename' => $rs, 'thumb-full-filename' => AUDIO_ICON_IMAGE,];
        }
    }
}

function fb($ts)
{
    $js = [];
    foreach ($ts as $zs) {
        $hs = mb($zs);
        if ($hs['is-local?']) $js[] = $zs;
    }
    return $js;
}

function db($ts)
{
    $js = [];
    foreach ($ts as $zs) {
        $hs = mb($zs);
        if ($hs['is-snippetable?']) $js[] = $zs;
    }
    return $js;
}

function sb($gs, $ws)
{
    if (!is_array($gs)) $gs = [];
    if (!is_array($ws)) $ws = [];
    $ts = array_merge($ws, $gs);
    $ts = array_reverse($ts);
    $ts = array_unique($ts);
    $ts = array_reverse($ts);
    return $ts;
}

function ab($ts)
{
    global $full_blog_url;
    if (!is_array($ts) or !count($ts)) return [];
    rb($ts);
    $us = [];
    foreach ($ts as $zs) {
        if (!empty ($is[$zs])) continue;
        $hs = mb($zs);
        if (!$hs['is-usable-as-cover?']) continue;
        if (!is_file($hs['local-full-filename'])) continue;
        $size = m3($hs['local-full-filename']);
        list ($vs, $bs, $os, $ps) = $size;
        $us[]    = ['src' => $hs['local-cover-href'], 'width' => $vs, 'height' => $bs, 'horizontality' => $os, 'verticality' => $ps,];
        $is[$zs] = true;
    }
    return $us;
}

function qb($ts)
{
    global $full_blog_url, $_strings;
    if (!is_array($ts) or !count($ts)) return [];
    rb($ts);
    $ca = [];
    foreach ($ts as $zs) {
        if (!empty ($is[$zs])) continue;
        $hs = mb($zs);
        if (!$hs['is-using-thumbnails?']) continue;
        if (!is_file($hs['local-full-filename'])) continue;
        $va = ['is-available?' => true, 'src' => '', 'width' => '', 'height' => '', 'original-filename' => '', 'original-filesize' => '',];
        if (!$hs['is-local?'] or is_file($hs['local-full-filename'])) {
            if ($hs['is-generating-thumbnail?']) {
                $ba = tb($hs);
            } else {
                $ba = $hs['thumb-full-filename'];
            }
        }
        if (empty ($ba)) {
            $va['is-available?'] = false;
            $ba                  = jb($hs['local-relative-filename']);
        }
        $va['src'] = x3($ba);
        if ($va['is-available?']) {
            $size = m3($ba);
            list ($vs, $bs) = $size;
        } else {
            $vs = $bs = '';
        }
        if (!$vs) $vs = THUMB_WIDTH / 2;
        if (!$bs) $bs = THUMB_HEIGHT / 2;
        list ($vs, $bs) = e2_fit_metrics_to_constraints([$vs, $bs], [THUMB_WIDTH / 2, THUMB_HEIGHT / 2]);
        $va['width']  = $vs;
        $va['height'] = $bs;
        if ($hs['is-local?']) {
            $va['original-filename'] = $zs;
            if (is_file($hs['local-full-filename'])) {
                $ya                      = stat($hs['local-full-filename'])[7];
                $ya                      = round($ya / 1024) . ' ' . $_strings['gs--kb'];
                $va['original-filesize'] = $ya;
            }
        }
        $ca[]    = $va;
        $is[$zs] = true;
    }
    return $ca;
}

function lb($na)
{
    foreach (['maxresdefault', 'hqdefault', 'mqdefault', 'sddefault', 'default'] as $fb) {
        $sm = 'http://img.youtube.com/vi/' . $na . '/' . $fb . '.jpg';
        if (Log::$cy) __log('Trying ' . $sm . '...');
        $ma = @file_get_contents($sm);
        if ($ma !== false) return $ma;
    }
    return false;
}

function zb($fa)
{
    $da = @unserialize(file_get_contents('http://vimeo.com/api/v2/video/' . $fa . '.php'));
    if (!empty ($da[0]['thumbnail_large'])) {
        return @file_get_contents($da[0]['thumbnail_large']);
    }
    return false;
}

function kb($hs, $sa)
{
    if (is_file($hs['local-full-filename'])) {
        if (Log::$cy) __log('Already exists: ' . $hs['local-full-filename']);
    } elseif (is_file($hs['local-full-failname'])) {
        if (Log::$cy) __log('Already tried and failed: ' . $hs['local-full-filename']);
    } else {
        if (Log::$cy) __log('Resource ' . $hs['url'] . ' is missing a cover, retrieving');
        if ($sa == PROVIDE_MEDIA_ASYNC) {
            p3(jv('e2s_retrieve', ['url' => strtr(base64_encode($hs['url']), '+/', '-_'),]));
        }
        if ($sa == PROVIDE_MEDIA_NOW) {
            if (Log::$cy) __log('Downloading "' . $hs['video-service'] . '" cover as ' . $hs['local-full-filename'] . '...');
            if ($hs['video-service'] == 'youtube') {
                $ma = lb($hs['video-id']);
            }
            if ($hs['video-service'] == 'vimeo') {
                $ma = zb($hs['video-id']);
            }
            if ($ma !== false) {
                n3($hs['local-full-filename'], $ma);
            } else {
                n3($hs['local-full-failname'], '');
            }
        }
    }
}

function xb($zs, $sa)
{
    $hs = mb($zs);
    if (Log::$cy) __log('Resource ' . $zs . ' is of type ' . $hs['type']);
    if ($hs['type'] == 'local-image') {
        tb($hs);
    }
    if ($hs['type'] == 'online-video') {
        kb($hs, $sa);
        if ($sa == PROVIDE_MEDIA_NOW and is_file($hs['local-full-filename'])) {
            tb($hs);
        }
    }
    if ($hs['type'] == 'remote-image') {
    }
}

function eb($ts)
{
    foreach ($ts as $zs) {
        $hs = mb($zs);
        if (empty ($hs['local-full-failname'])) continue;
        if (is_file($hs['local-full-failname'])) {
            if (Log::$cy) __log('Deleting ' . $hs['local-full-failname'] . ' to try again');
            unlink($hs['local-full-failname']);
        }
    }
}

function rb($ts)
{
    if (!is_array($ts)) return;
    if (Log::$cy) __log('Asynchronously provide data for resnames {');
    foreach ($ts as $zs) {
        xb($zs, PROVIDE_MEDIA_ASYNC);
    }
    if (Log::$cy) __log('}');
}

function tb($hs)
{
    if (!$hs['is-generating-thumbnail?']) return false;
    return e2img_filename_by_processing($hs['local-full-filename'], jb($hs['local-relative-filename']), [THUMB_WIDTH, THUMB_HEIGHT], CROP_NONE, THUMB_JPG_QUALITY);
}

function jb($aa)
{
    return pb(MEDIA_ROOT_FOLDER . THUMBNAILS_FOLDER . $aa, 'thumb@2x');
}

function gb($zs)
{
    $la = pathinfo($zs);
    $za = @$la['extension'];
    return (in_array(strtolower($za), ['jpg', 'jpeg', 'gif', 'png', 'svg']));
}

function wb($zs)
{
    $la = pathinfo($zs);
    $za = @$la['extension'];
    return (in_array(strtolower($za), ['jpg', 'jpeg', 'gif', 'png']));
}

function ub($zs)
{
    $la = pathinfo($zs);
    $za = @$la['extension'];
    return (in_array(strtolower($za), ['svg']));
}

function ib($zs)
{
    $la = pathinfo($zs);
    $za = @$la['extension'];
    return (in_array(strtolower($za), ['mp4', 'mov']));
}

function ob($zs)
{
    $la = pathinfo($zs);
    $za = @$la['extension'];
    return (in_array(strtolower($za), ['mp3']));
}

function pb($zs, $ka)
{
    if (!empty ($ka)) {
        $xa = explode('/', $zs);
        $ea = array_pop($xa);
        $ra = explode('.', $ea);
        if (count($ra) < 2) $ra[] = '';
        $za   = array_pop($ra);
        $ra[] = $ka;
        if ($za) $ra[] = $za;
        $ea   = implode('.', $ra);
        $xa[] = $ea;
        $zs   = implode('/', $xa);
    }
    return $zs;
}

function c3($ta, $ea)
{
    if (!is_file($ta . $ea)) return $ea;
    $ja = strrpos($ea, '.');
    $ha = substr($ea, 0, $ja);
    $za = substr($ea, $ja);
    $r  = 0;
    while (is_file($ta . $ha . '-' . (++$r) . $za)) ;
    $ea = $ha . '-' . $r . $za;
    return $ea;
}

function v3($zs)
{
    $la = pathinfo($zs);
    $za = @$la['extension'];
    if ($za == 'png') return 'image/png';
    if ($za == 'gif') return 'image/gif';
    if ($za == 'jpg' or $za == 'jpeg') return 'image/jpeg';
    if ($za == 'mp3') return 'audio/mpeg';
    if ($za == 'svg') return 'image/svg+xml';
    if ($za == 'mp4') return 'video/mp4';
    if ($za == 'mov') return 'video/quicktime';
}

function b3($ga, $wa)
{
    return strcasecmp($ga, $wa) === 0;
}

$_folders_written = ['.', USER_FOLDER, CACHES_FOLDER, BACKUP_FOLDER, LOG_FOLDER, MEDIA_ROOT_FOLDER . PICTURES_FOLDER, MEDIA_ROOT_FOLDER . THUMBNAILS_FOLDER, MEDIA_ROOT_FOLDER . PICTURES_FOLDER . 'remote/', MEDIA_ROOT_FOLDER . THUMBNAILS_FOLDER . 'remote/', MEDIA_ROOT_FOLDER . VIDEO_FOLDER, MEDIA_ROOT_FOLDER . AUDIO_FOLDER, MEDIA_ROOT_FOLDER . AVATARS_FOLDER,];
$_files_written   = [USER_FOLDER . 'password-hash.psa', USER_FOLDER . 'password-wait.psa', USER_FOLDER . 'last-comment.psa', USER_FOLDER . 'new-uploads.psa', USER_FOLDER . 'settings.json', USER_FOLDER . 'indexing.psa', USER_FOLDER . 'auth.psa', USER_FOLDER . 'scheduled.psa',];
define('CROP_NONE', 0);
define('CROP_SQUARE', 1);
function y3()
{
    global $_folders_written, $_files_written;
    clearstatcache();
    $ua = [];
    foreach ($_folders_written as $dn) {
        if (is_dir($dn) and !is_writable($dn)) {
            $ua[] = $dn;
        }
    }
    foreach ($_files_written as $dn) {
        if (is_file($dn) and !is_writable($dn)) {
            $ua[] = $dn;
        }
    }
    return $ua;
}

function n3($fy, $x)
{
    @j(dirname($fy));
    if (!@file_put_contents($fy, $x, LOCK_EX)) {
        return false;
    }
    @chmod($fy, E2_NEW_FILES_RIGHTS);
    return true;
}

function m3($fb)
{
    $vs = $bs = 0;
    if (wb($fb)) {
        list ($vs, $bs) = getimagesize($fb);
    } elseif (ib($fb)) {
        try {
            require_once SYSTEM_LIBRARY_FOLDER . 'getid3/getid3.php';
            $id = new getid3 ();
            $id = $id->analyze($fb);
            $vs = $id['video']['resolution_x'];
            $bs = $id['video']['resolution_y'];
        } catch (\Exception $e) {
        }
    } elseif (ub($fb)) {
        if (function_exists('simplexml_load_string')) {
            $ia = simplexml_load_string(file_get_contents($fb));
            if ($ia) {
                $oa = $ia->attributes();
                list ($vs, $bs) = [(string)$oa->width, (string)$oa->height];
            }
        }
    }
    if (substr($fb, strrpos($fb, '.') - 3, 3) == '@2x') {
        $vs = (int)floor($vs / 2);
        $bs = (int)floor($bs / 2);
    }
    $os = round(($bs > 0) ? ($vs / $bs) : 1, 2);
    $ps = round(($vs > 0) ? ($bs / $vs) : 1, 2);
    return [$vs, $bs, $os, $ps];
}

function e2s_retrieve($parameters)
{
    $sm = base64_decode(strtr($parameters['url'], '-_', '+/'));
    if (Log::$cy) __log('Retrieve: ' . $sm);
    xb($sm, PROVIDE_MEDIA_NOW);
    die;
}

function f3($pa, $c1, $gs)
{
    $v1 = [];
    if (is_array($gs)) {
        $v1 = fb($gs);
    }
    $b1 = @unserialize($c1['Uploads']) or $b1 = [];
    $y1 = array_diff($v1, $b1);
    if (count($y1) > 0) {
        z3($pa, $c1['ID'], 'add', $y1);
    }
    return $y1;
}

function d3($ts)
{
    $ua = [];
    foreach (ab($ts) as $n1) {
        $ua[] = $n1['src'];
    }
    return $ua;
}

function s3($name, $m1)
{
    $name = m($name);
    if (preg_match('//u', $name)) $name = pv($name, false);
    if ($m1 == 'image') {
        $ta = MEDIA_ROOT_FOLDER . PICTURES_FOLDER;
    } elseif ($m1 == 'video') {
        $ta = MEDIA_ROOT_FOLDER . VIDEO_FOLDER;
    } elseif ($m1 == 'audio') {
        $ta = MEDIA_ROOT_FOLDER . AUDIO_FOLDER;
    } else {
        return false;
    }
    $f1 = '';
    for ($r = 0; $r < strlen($name); $r++) {
        if ($name[$r] == '?') {
            $f1 .= '';
        } elseif ($name[$r] == ' ') {
            $f1 .= '-';
        } elseif (ord($name[$r]) <= 127) {
            $f1 .= $name[$r];
        }
    }
    if ($f1 == '') $f1 = $m1;
    if ($f1[0] == '.') $f1 = $m1 . $f1;
    return $f1;
}

function a3($d1)
{
    global $_config;
    if (Log::$cy) __log('Count references for upload <' . $d1 . '>');
    if (is_file(USER_FOLDER . 'new-uploads.psa')) {
        $s1 = @unserialize(file_get_contents(USER_FOLDER . 'new-uploads.psa'));
    }
    $a1 = '%' . str_replace('%', '#%', $d1) . '%';
    xn("SELECT `ID`, `Text`, `FormatterID`, `Uploads` " . "FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND (`Text` LIKE '" . $a1 . "' ESCAPE '#' " . "OR `Uploads` LIKE '" . $a1 . "' ESCAPE '#')", 'get notes where uploads may be referenced');
    $q1 = en();
    $l1 = @unserialize($q1[0]['Uploads']);
    if (!is_array($l1)) {
        foreach ($q1 as $n2) {
            $z1 = u3($n2['FormatterID'], @$n2['Text'], 'full-rss');
            $l1 = f3('note', $n2, $z1['meta']['resources-detected']);
        }
    }
    xn("SELECT `ID`, `Description`, `Uploads` " . "FROM `" . $_config['db_table_prefix'] . "Keywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND (`Description` LIKE '" . $a1 . "' ESCAPE '#' " . "OR `Uploads` LIKE '" . $a1 . "' ESCAPE '#')", 'get tags where uploads may be referenced');
    $q1 = en();
    $k1 = @unserialize($q1[0]['Uploads']);
    if (!is_array($k1)) {
        foreach ($q1 as $q2) {
            $z1 = i3(@$q2['Description'], 'full-rss');
            $k1 = f3('tag', $q2, $z1['meta']['resources-detected']);
        }
    }
    if (!is_array($s1)) $s1 = [];
    if (!is_array($l1)) $l1 = [];
    if (!is_array($k1)) $k1 = [];
    $x1 = array_merge($s1, $l1, $k1);
    if (Log::$cy) __log('References found in relevant entries: ' . var_export($x1, true));
    if (in_array($d1, $x1)) {
        if (Log::$cy) __log('Still referenced, do not delete file');
        return true;
    }
    return false;
}

function q3($e1, $xs)
{
    global $_config;
    if ($e1 == 'note' and $xs == 'new') {
        if (is_file(USER_FOLDER . 'new-uploads.psa')) {
            $x1 = @unserialize(file_get_contents(USER_FOLDER . 'new-uploads.psa'));
        }
    } elseif ($e1 == 'note') {
        xn("SELECT `Uploads` FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`=" . $xs);
        $q1 = en();
        $x1 = @unserialize($q1[0]['Uploads']);
    } elseif ($e1 == 'tag') {
        xn("SELECT `Uploads` FROM `" . $_config['db_table_prefix'] . "Keywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`=" . $xs);
        $q1 = en();
        $x1 = @unserialize($q1[0]['Uploads']);
    }
    if (!is_array($x1)) $x1 = array();
    return $x1;
}

function l3($e1, $xs, $x1)
{
    global $_config;
    if ($e1 == 'note' and $xs == 'new') {
        if (!@n3(USER_FOLDER . 'new-uploads.psa', serialize($x1))) {
            mv('ERROR', E2E_PERMISSIONS_ERROR);
        }
    } elseif ($e1 == 'note') {
        xn("UPDATE `" . $_config['db_table_prefix'] . "Notes` " . "SET `Uploads`='" . serialize($x1) . "' " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`=" . $xs);
    } elseif ($e1 == 'tag') {
        xn("UPDATE `" . $_config['db_table_prefix'] . "Keywords` " . "SET `Uploads`='" . serialize($x1) . "' " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`=" . $xs);
    } else {
        return false;
    }
    if (!is_array($x1)) $x1 = array();
    return $x1;
}

function z3($e1, $xs, $action, $qv)
{
    global $_config;
    $x1 = array();
    if (Log::$cy) __log('Register upload: <' . $e1 . ', ' . $xs . ', ' . $action . ', ' . $qv . '>');
    $x1 = q3($e1, $xs);
    $x1 = d($x1, $action, $qv);
    l3($e1, $xs, $x1);
}

function k3($r1, $t1, $gs)
{
    $b1 = @unserialize($t1['Uploads']) or $b1 = array();
    $j1 = fb($gs);
    $x1 = d($b1, 'add', $j1);
    $x1 = serialize($x1);
    if ($x1 != $t1['Uploads']) {
        $t1['Uploads'] = $x1;
        nn($r1, $t1);
    }
}

function e2j_file_upload($parameters = array())
{
    global $_config, $full_blog_url, $_strings;
    @j(MEDIA_ROOT_FOLDER . PICTURES_FOLDER);
    @chmod(MEDIA_ROOT_FOLDER . PICTURES_FOLDER, $_config['uploaded_files_mode']);
    @j(MEDIA_ROOT_FOLDER . VIDEO_FOLDER);
    @chmod(MEDIA_ROOT_FOLDER . VIDEO_FOLDER, $_config['uploaded_files_mode']);
    @j(MEDIA_ROOT_FOLDER . AUDIO_FOLDER);
    @chmod(MEDIA_ROOT_FOLDER . AUDIO_FOLDER, $_config['uploaded_files_mode']);
    $zv = ['success' => false];
    if (count($_FILES) > 0) {
        foreach ($_FILES as $fy) {
            if (!$fy['error']) {
                if (Log::$cy) __log('Ajax file upload: <' . $fy['name'] . '>');
                $zv['data']['file-kind'] = 'image';
                $ta                      = MEDIA_ROOT_FOLDER . PICTURES_FOLDER;
                if (ib($fy['name'])) {
                    $zv['data']['file-kind'] = 'video';
                    $ta                      = MEDIA_ROOT_FOLDER . VIDEO_FOLDER;
                } elseif (ob($fy['name'])) {
                    $zv['data']['file-kind'] = 'audio';
                    $ta                      = MEDIA_ROOT_FOLDER . AUDIO_FOLDER;
                }
                $h1                      = (array_key_exists('overwrite', $_GET) and is_file($ta . $fy['name']));
                $g1                      = false;
                $zv['data']['overwrite'] = (int)$h1;
                if (Log::$cy) __log('Ajax file upload: Overwrite is resolved to <' . (int)$h1 . '>');
                $f1 = s3($fy['name'], $zv['data']['file-kind']);
                if (Log::$cy) __log('Ajax file upload: Safe name is <' . $f1 . '>');
                if (is_file($ta . $f1)) {
                    if (file_get_contents($ta . $f1) == file_get_contents($fy['tmp_name'])) {
                        if (Log::$cy) __log('Ajax file upload: Existing file is the same');
                        $g1 = true;
                    } elseif (!$h1) {
                        $f1 = c3($ta, $f1);
                    }
                }
                if (!$g1) {
                    move_uploaded_file($fy['tmp_name'], $ta . $f1);
                    @chmod($ta . $f1, $_config['uploaded_files_mode']);
                }
                if (Log::$cy) __log('Ajax file upload: File kind is <' . $zv['data']['file-kind'] . '>');
                if ($zv['data']['file-kind'] == 'image') {
                    $za = pathinfo($f1, PATHINFO_EXTENSION);
                    if (b3($za, 'jpg')) {
                        $w1 = $f1;
                    } else {
                        $w1 = $f1 . '.jpg';
                        $w1 = c3(MEDIA_ROOT_FOLDER . PICTURES_FOLDER, $w1);
                    }
                    $u1 = MEDIA_ROOT_FOLDER . PICTURES_FOLDER . $f1;
                    $i1 = MEDIA_ROOT_FOLDER . PICTURES_FOLDER . $w1;
                    if (Log::$cy) __log('Ajax file upload: Process uploaded image <' . $u1 . '>' . ' to possibly <' . $i1 . '>');
                    $i1 = e2img_filename_by_processing($u1, $i1, [$_config['fit_uploaded_images'], $_config['fit_uploaded_images'],], CROP_NONE, SCALED_IMAGE_JPG_QUALITY);
                    $ya = $fy['size'];
                    if (!b3($i1, $u1)) {
                        @unlink($u1);
                        $f1 = $w1;
                        $ya = stat($i1)['size'];
                    }
                    if ($h1) {
                        @unlink(jb($f1));
                    }
                    if ($o1 = e2img_filename_by_processing($u1, jb($f1), [THUMB_WIDTH, THUMB_HEIGHT], CROP_NONE, THUMB_JPG_QUALITY)) {
                        if (Log::$cy) __log('Ajax file upload: thumbnail, done as ' . $o1);
                        list ($vs, $bs) = m3($o1);
                        if (Log::$cy) __log('Ajax file upload: image size ' . $vs . '×' . $bs);
                        if (!$vs) $vs = THUMB_WIDTH / 2;
                        if (!$bs) $bs = THUMB_HEIGHT / 2;
                        list ($vs, $bs) = e2_fit_metrics_to_constraints([$vs, $bs], [THUMB_WIDTH / 2, THUMB_HEIGHT / 2]);
                        $zv['success']          = true;
                        $zv['data']['new-name'] = $f1;
                        $zv['data']['filesize'] = round($ya / 1024) . ' ' . $_strings['gs--kb'];
                        $zv['data']['thumb']    = x3($o1);
                        $zv['data']['width']    = $vs;
                        $zv['data']['height']   = $bs;
                        z3($parameters['entity'], $parameters['entity-id'], 'add', array($f1));
                    } else {
                        if (Log::$cy) __log('Ajax file upload: couldn’t create thumbnail');
                        @unlink($ta . $f1);
                        $zv['error']['message'] = _S('er--cannot-create-thumbnail');
                    }
                }
                if ($zv['data']['file-kind'] == 'video') {
                    if (Log::$cy) __log('Ajax file upload: video, done');
                    $zv['success']          = true;
                    $zv['data']['new-name'] = $f1;
                    $zv['data']['filesize'] = round($fy['size'] / 1024) . ' ' . $_strings['gs--kb'];
                    $zv['data']['thumb']    = VIDEO_ICON_IMAGE;
                    $zv['data']['width']    = VIDEO_ICON_WIDTH / 2;
                    $zv['data']['height']   = VIDEO_ICON_HEIGHT / 2;
                    z3($parameters['entity'], $parameters['entity-id'], 'add', array($f1));
                }
                if ($zv['data']['file-kind'] == 'audio') {
                    if (Log::$cy) __log('Ajax file upload: audio, done');
                    $zv['success']          = true;
                    $zv['data']['new-name'] = $f1;
                    $zv['data']['filesize'] = round($fy['size'] / 1024) . ' ' . $_strings['gs--kb'];
                    $zv['data']['thumb']    = AUDIO_ICON_IMAGE;
                    $zv['data']['width']    = AUDIO_ICON_WIDTH / 2;
                    $zv['data']['height']   = AUDIO_ICON_HEIGHT / 2;
                    z3($parameters['entity'], $parameters['entity-id'], 'add', array($f1));
                }
            } elseif (4 != $fy['error']) {
                if ($fy['error'] == 1) {
                    $zv['error']['message'] = 'too-big';
                } elseif ($fy['error'] == 2) {
                    $zv['error']['message'] = 'too-big';
                } elseif ($fy['error'] == 3) {
                    $zv['error']['message'] = 'partial';
                } else {
                    $zv['error'] = $fy['error'];
                }
            }
        }
    } else {
        if (Log::$cy) __log('Ajax file upload error: no files');
        $zv['error']['message'] = 'no-files';
    }
    $zv = json_encode($zv);
    die ($zv);
}

function x3($p1)
{
    global $full_blog_url;
    $e2_ = strlen(MEDIA_ROOT_FOLDER);
    if ($e2_ and substr($p1, 0, $e2_) == MEDIA_ROOT_FOLDER) {
        return substr($p1, $e2_);
    } else {
        return $full_blog_url . '/' . $p1;
    }
}

function e3()
{
    @unlink(USER_FOLDER . 'userpic@2x.png');
    @unlink(USER_FOLDER . 'userpic@2x.jpg');
    @unlink(USER_FOLDER . 'userpic-large@2x.jpg');
    @unlink(USER_FOLDER . 'userpic-square@2x.jpg');
}

function e2j_userpic_remove()
{
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        c(jv('e2m_settings'));
    }
    e3();
    $zv = json_encode(['success' => true]);
    die ($zv);
}

function e2j_userpic_upload()
{
    global $_config;
    $zv = ['success' => false];
    if (count($_FILES) != 1) {
        if (Log::$cy) __log('Ajax userpic upload error: no or too many files');
        $zv['error']['message'] = 'No or too many files';
        $zv                     = json_encode($zv);
        die ($zv);
    }
    $fy = array_pop($_FILES);
    if (!$fy['error']) {
        if (Log::$cy) __log('Ajax userpic upload: <' . $fy['name'] . '>');
        $cq = pathinfo($fy['name']);
        $za = strtolower($cq['extension']);
        if ($za != 'png') $za = 'jpg';
        $fb = 'userpic.original.' . $za;
        move_uploaded_file($fy['tmp_name'], USER_FOLDER . $fb);
        @chmod(USER_FOLDER . $fb, $_config['uploaded_files_mode']);
        e3();
        copy(USER_FOLDER . $fb, USER_FOLDER . 'userpic-large@2x.jpg');
        $vq = e2img_filename_by_processing(USER_FOLDER . 'userpic-large@2x.jpg', USER_FOLDER . 'userpic-large@2x.jpg', [$_config['max_image_width'], $_config['max_image_width']], CROP_NONE, USERPIC_JPG_QUALITY);
        copy(USER_FOLDER . $fb, USER_FOLDER . 'userpic-square@2x.jpg');
        $bq = e2img_filename_by_processing(USER_FOLDER . 'userpic-square@2x.jpg', USER_FOLDER . 'userpic-square@2x.jpg', [$_config['max_image_width'], $_config['max_image_width']], CROP_SQUARE, USERPIC_JPG_QUALITY);
        $yq = e2img_filename_by_processing(USER_FOLDER . $fb, USER_FOLDER . 'userpic@2x.jpg', [USERPIC_WIDTH, USERPIC_HEIGHT], CROP_SQUARE, USERPIC_JPG_QUALITY);
        if ($bq) {
            $nq = str_replace(USER_FOLDER, USER_FOLDER_URLPATH, $bq);
            $zv = ['success' => true, 'data' => ['new-image-src' => $nq,]];
        } else {
            $zv['error']['message'] = _S('er--supported-only-png-jpg-gif');
        }
    } elseif (4 != $fy['error']) {
        if ($fy['error'] == 1) {
            $zv['error']['message'] = 'File too big';
        } elseif ($fy['error'] == 2) {
            $zv['error']['message'] = 'File too big';
        } elseif ($fy['error'] == 3) {
            $zv['error']['message'] = 'File upload is partial';
        } else {
            $zv['error']['message'] = 'File upload error ' . $fy['error'];
        }
    }
    $zv = json_encode($zv);
    die ($zv);
}

function e2j_file_remove($parameters)
{
    if (!array_key_exists('file', $_POST)) {
        $zv = ['success' => false];
        $zv = json_encode($zv);
        die ($zv);
    }
    $fy = $_POST['file'];
    $zv = ['success' => true];
    $zv = json_encode($zv);
    z3($parameters['entity'], $parameters['entity-id'], 'remove', $fy);
    if (!a3($fy)) {
        if (ob($fy)) {
            if (Log::$cy) __log('Not referenced, deleting ' . MEDIA_ROOT_FOLDER . AUDIO_FOLDER . $fy);
            @unlink(MEDIA_ROOT_FOLDER . AUDIO_FOLDER . $fy);
        } elseif (ib($fy)) {
            if (Log::$cy) __log('Not referenced, deleting ' . MEDIA_ROOT_FOLDER . VIDEO_FOLDER . $fy);
            @unlink(MEDIA_ROOT_FOLDER . VIDEO_FOLDER . $fy);
        } else {
            $yq = pb($fy, 'thumb@2x');
            $mq = @unlink(MEDIA_ROOT_FOLDER . PICTURES_FOLDER . $fy);
            $fq = @unlink(MEDIA_ROOT_FOLDER . THUMBNAILS_FOLDER . $yq);
        }
    }
    die ($zv);
}

function r3()
{
    global $_config;
    if (!$_config['files_total_size_limit']) return false;
    $dq = 0;
    foreach (glob(MEDIA_ROOT_FOLDER . PICTURES_FOLDER . '/*') as $fy) {
        $f  = stat($fy);
        $dq += $f['size'];
    }
    foreach (glob(MEDIA_ROOT_FOLDER . VIDEO_FOLDER . '/*') as $fy) {
        $f  = stat($fy);
        $dq += $f['size'];
    }
    foreach (glob(MEDIA_ROOT_FOLDER . AUDIO_FOLDER . '/*') as $fy) {
        $f  = stat($fy);
        $dq += $f['size'];
    }
    $sq = $_config['files_total_size_limit'];
    $aq = f($dq, $sq);
    return array($dq, $sq, $aq);
}

function t3($qq)
{
    $lq = true;
    if (list ($dq, $sq, $aq) = $qq) {
        $lq = ($sq - $dq) > 0;
    }
    return $lq;
}

function j3($qq, $zq = false)
{
    $kq = '';
    if (list ($dq, $sq, $aq) = $qq) {
        $qq = array('used' => round($dq / 1024 / 1024), 'total' => round($sq / 1024 / 1024), 'percent' => $aq);
        if ($zq or ($sq - $dq) < 1024 * 1024 * 10) {
            if ($dq < $sq) {
                $kq = e2l_get_string('gs--used', $qq);
            } else {
                $kq = e2l_get_string('gs--used-all', $qq);
            }
        }
    }
    return $kq;
}

function e2_error404_mode()
{
    global $_config, $_strings;
    if ($_config['try_redirect_to_all']) {
        $xq = 'all/' . urldecode($_GET['go']);
        hv($xq);
    }
    header('HTTP/1.1 404 Not found');
    $eq['class']   = '404';
    $eq['heading'] = $_strings['pt--page-not-found'];
    $eq['title']   = $_strings['pt--page-not-found'];
    return $eq;
}

function e2s_post_service($parameters)
{
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        c(jv('e2m_underhood'));
    }
    if ($parameters['service'] === 'build') {
        e2_build();
        mv('Engine core built', E2E_MESSAGE);
    }
    if ($parameters['service'] === 'sync') {
        e2_drop_all_kinds_of_cache();
        mv('Caches invalidated', E2E_MESSAGE);
    }
    if ($parameters['service'] === 'log') {
        nv();
        mv('Logs enabled', E2E_MESSAGE);
    }
    if ($parameters['service'] === 'backup') {
    }
    if ($parameters['service'] === 'migrate') {
        qn();
        mv('Database structure up to date', E2E_MESSAGE);
    }
    c(jv('e2m_underhood'));
}

function h3($tv)
{
    include_once 'neasden/neasden.php';
    $Nn               = new Neasden;
    $Nn->profile_name = 'kavychki';
    return $Nn->format($tv);
}

function g3($ly, $tv, $rq)
{
    include_once 'neasden/neasden.php';
    if ($tv === '') return array();
    if ($ly == 'calliope') {
        preg_match_all('/\(\(([^ ]*)( |\)\))/', $tv, $y3);
        return $y3[1];
    } elseif ($ly == 'neasden') {
        $Nn               = new Neasden;
        $Nn->profile_name = $rq;
        $Nn->format($tv);
        return $Nn->resources_detected;
    } else {
        return array();
    }
}

function w3()
{
    return '<div class="foot" style="color: var(--errorColor); font-style: italic">This text was created with a very old version of Aegea that used a formatter called Calliope. It is no longer included with Aegea&nbsp;2.10.</div><div class="foot" style="color: var(--errorColor); font-style: italic">Edit this note to switch it and its comments to the current formatter, Neasden. If anything breaks, edit again to fix.</div><div class="foot" style="color: var(--errorColor); font-style: italic">To temporarily install Calliope, get the directory <tt>/system/calliope/</tt> from Aegea&nbsp;2.9 and copy it to your <tt>/user/calliope/</tt>. This will not work with Aegea&nbsp;2.11. See release notes for Aegea&nbsp;2.10 for details.</div>';
}

function u3($ly, $tv, $rq)
{
    include_once 'neasden/neasden.php';
    if (Log::$cy) __log('Format: format with formatter "' . $ly . '" in context "' . $rq . '"');
    if ($ly == 'calliope') {
        $tv   = pv($tv);
        $tv   = o3($tv, $rq);
        $meta = array();
        $tv   = vb($tv);
        $tv   = '<div class="e2-text-calliope-formatted">' . h3($tv) . '</div>';
    } elseif ($ly == 'neasden') {
        $Nn               = new Neasden;
        $Nn->profile_name = $rq;
        $tv               = $Nn->format($tv);
        $meta             = array('links-required' => $Nn->links_required, 'resources-detected' => $Nn->resources_detected);
    }
    return array('text-final' => $tv, 'meta' => $meta,);
}

function i3($tv, $rq)
{
    global $_config;
    return u3($_config['default_formatter'], $tv, $rq);
}

function o3($tv, $rq)
{
    global $_config, $settings, $full_blog_url, $_template;
    @ (list ($rq, $tq) = explode('|', $rq));
    if (!is_file(USER_FOLDER . 'calliope/WikiFormatter.php')) {
        return w3();
    }
    require_once USER_FOLDER . 'calliope/WikiFormatter.php';
    if ('full' == $rq) $jq = WF_FULL_MODE; elseif ('full-rss' == $rq) $jq = WF_FULL_MODE;
    elseif ('simple' == $rq) $jq = WF_SIMPLE_MODE;
    elseif ('simple-rss' == $rq) $jq = WF_SIMPLE_MODE;
    else return $tv;
    $hq           = new WikiFormatter ();
    $hq->replace  = array('/' => 'i', '+' => 'small', '-' => 's', '*' => 'b', '^' => 'sup', 'v' => 'sub', '#' => 'tt', '!' => 'blockquote',);
    $hq->settings = array('hrefSize' => 100, 'localImgDir' => $full_blog_url . '/' . PICTURES_FOLDER, 'maxImgWidth' => $_template['max_image_width'], 'mode' => $jq, 'enableShrinkLongHref' => 1, 'enableHr' => 0, 'enableBr' => 1, 'enableHeaders' => 1, 'headersStartWith' => 1, 'enableTables' => 1, 'simpleTableCSSClass' => 'e2-text-table', 'enableAutoAcronymEngine' => 0, 'enableAcronym' => 0, 'acronymBase' => '', 'enableList' => 1, 'mailSafe' => "<a href=\"\" onmouseover=\"this.href='mailto:'+{email}\">{icon}<script language=\"JavaScript\">document.write({name});</script></a>", 'ljUserTag' => '<a href="http://livejournal.com/users/{name}/">{name}</a>', 'fullVersionURL' => $tq, 'enableTagIcons' => 0, 'outerUrlInNewWindow' => 0, 'lineBreak' => "\n", 'extLinkHrefPrefix' => '',);
    $tv           = $hq->Wiki2HTML($tv);
    return $tv;
}

function p3($sm, $gq = false)
{
    if (Log::$cy) __log('Spawn: Curl ' . $sm . ' using ' . ($gq ? 'post' : 'get') . '...');
    if (function_exists('curl_init')) {
        $wq = curl_init();
        $uq = !ini_get('open_basedir');
        $uq = ($uq and !$gq);
        curl_setopt_array($wq, array(CURLOPT_URL => $sm, CURLOPT_POST => $gq, CURLOPT_POSTREDIR => false, CURLOPT_POSTFIELDS => '', CURLOPT_CONNECTTIMEOUT => 300, CURLOPT_TIMEOUT => 1, CURLOPT_MAXREDIRS => 1, CURLOPT_COOKIE => r2(), CURLOPT_SSL_VERIFYPEER => false, CURLOPT_FOLLOWLOCATION => $uq, CURLOPT_RETURNTRANSFER => true, CURLOPT_AUTOREFERER => true, CURLOPT_USERAGENT => E2_UA_STRING,));
        $content = curl_exec($wq);
        $iq      = curl_errno($wq);
        $oq      = curl_error($wq);
        $pq      = curl_getinfo($wq);
        curl_close($wq);
        if (Log::$cy) __log('Spawn: Curl returns: [' . print_r($pq, true) . '] [' . $content . '], (errno=' . $iq . ', errstr=' . $oq . ')...');
    } else {
        if (Log::$cy) __log('Spawn: Curl functions are not available');
    }
}

function cy($cl)
{
    global $_config;
    if (@$_config['broadcast_url'] and !$cl['IsExternal']) {
        if ($_config['log_broadcast']) {
            Log::$cy = true;
            if (Log::$cy) bv('broadcast');
        }
        if (Log::$cy) __log('Broadcast-async note: ' . $cl['Title']);
        $sm = jv('e2m_note_broadcast', array('*note' => $cl));
        if (Log::$cy) __log('Broadcast will spawn url: ' . $sm);
        p3($sm);
    }
}

function vy($vl)
{
    global $_config;
    if (!$vl) return false;
    $sm = $_config['broadcast_url'];
    $sm .= '?src=' . urlencode($vl);
    if ($_config['log_broadcast']) {
        Log::$cy = true;
        if (Log::$cy) bv('broadcast');
    }
    if (Log::$cy) __log('Broadcast: Curl ' . $sm . '...');
    if (function_exists('curl_init')) {
        $wq = curl_init();
        $uq = !ini_get('open_basedir');
        curl_setopt_array($wq, array(CURLOPT_URL => $sm, CURLOPT_CONNECTTIMEOUT => 300, CURLOPT_TIMEOUT => 1, CURLOPT_MAXREDIRS => 1, CURLOPT_COOKIE => r2(), CURLOPT_SSL_VERIFYPEER => false, CURLOPT_FOLLOWLOCATION => $uq, CURLOPT_RETURNTRANSFER => true, CURLOPT_AUTOREFERER => true, CURLOPT_USERAGENT => E2_UA_STRING,));
        $content = curl_exec($wq);
        $iq      = curl_errno($wq);
        $oq      = curl_error($wq);
        $pq      = curl_getinfo($wq);
        curl_close($wq);
        if (Log::$cy) __log('Broadcast: Curl returns: [' . print_r($pq, true) . '] [' . $content . '], (errno=' . $iq . ', errstr=' . $oq . ')...');
        if ($iq === 0) return true;
    } else {
        if (Log::$cy) __log('Spawn: Curl functions are not available');
    }
    return false;
}

function by($cl)
{
    if (!$cl) return false;
    $vl = jv('e2m_note_json', array('*note' => $cl));
    return vy($vl);
}

function e2m_note_broadcast($parameters = array())
{
    global $_config;
    if (@$_config['broadcast_url']) {
        if (array_key_exists('*note', $parameters)) {
            $vl = jv('e2m_note_json', array('*note' => $parameters['*note']));
        } elseif (array_key_exists('alias', $parameters)) {
            $vl = jv('e2m_note_json', array('alias' => $parameters['alias']));
        }
        if (vy($vl)) {
            die ('Broadcasted.');
        } else {
            die ('Could not broadcast.');
        }
    } else {
        return e2_error404_mode();
    }
}

function e2m_timezone()
{
    global $_strings, $settings;
    $bl = array('form-action' => jv('e2s_select_timezone'), 'submit-text' => $_strings['fb--select'], 'timezone-selector' => fy($settings['timezone']['offset'], 1), 'dst?' => $settings['timezone']['is_dst'],);
    return array('title' => $_strings['pt--default-timezone'], 'heading' => $_strings['pt--default-timezone'], 'form' => 'form-timezone', 'form-timezone' => $bl,);
}

function yy()
{
    global $_strings;
    $yl = array(-720 => '', -660 => '', -600 => '', -540 => '', -480 => $_strings['tt--zone-pt'], -420 => $_strings['tt--zone-mt'], -360 => $_strings['tt--zone-ct'], -300 => $_strings['tt--zone-et'], -240 => '', -210 => '', -180 => '', -120 => '', -60 => '', 0 => $_strings['tt--zone-gmt'], 60 => $_strings['tt--zone-cet'], 120 => $_strings['tt--zone-eet'], 180 => '', 210 => '', 240 => $_strings['tt--zone-msk'], 270 => '', 300 => '', 330 => '', 345 => '', 360 => $_strings['tt--zone-ekt'], 390 => '', 420 => '', 480 => '', 540 => '', 570 => '', 600 => '', 660 => '', 720 => '', 780 => '', 840 => '',);
    return $yl;
}

function ny($nl)
{
    $yl = yy();
    return @$yl[(int)$nl / SECONDS_IN_A_MINUTE];
}

function my($nl)
{
    $ml = '+';
    if ($nl < 0) $ml = '&ndash;';
    $fl = str_pad((int)(abs($nl) / 3600), 2, '0', STR_PAD_LEFT);
    $dl = str_pad(abs($nl) / 60 % 60, 2, '0', STR_PAD_LEFT);
    return 'GMT' . $ml . $fl . ':' . $dl;
}

function fy($sl, $al = '')
{
    global $_strings;
    $yl = yy();
    $d  = '';
    if (!$al) $al = count($yl);
    $d .= '<select class="e2-select" name="offset" size="' . $al . '">';
    foreach ($yl as $nl => $ql) {
        $ll = '';
        if ($nl * SECONDS_IN_A_MINUTE == $sl) $ll = ' selected="selected"';
        $d  .= '<option' . $ll . ' value="' . $nl . '">';
        $ml = '';
        if ($nl < 0) $ml = '−';
        if ($nl > 0) $ml = '+';
        $fl = (int)(abs($nl * SECONDS_IN_A_MINUTE) / 3600);
        $dl = (int)(abs($nl * SECONDS_IN_A_MINUTE) / 60 % 60);
        if ($fl) {
            $d .= ($ml . ' ' . $fl . ' ' . $_strings['gs--timezone-offset-hours'] . ' ' . ($dl ? ($dl . ' ' . $_strings['gs--timezone-offset-minutes']) : ''));
            if ($ql) {
                $d .= ' (' . $ql . ')';
            }
        } else {
            $d .= $ql;
        }
        $d .= '</option>';
    }
    $d .= '</select>';
    return $d;
}

function e2s_select_timezone()
{
    global $settings, $_strings;
    if (@$_POST['offset'] >= -720 and @$_POST['offset'] <= 780) {
        $settings['timezone']['offset'] = @$_POST['offset'] * SECONDS_IN_A_MINUTE;
        $settings['timezone']['is_dst'] = isset ($_POST['is_dst']);
    }
    if (!@n3(USER_FOLDER . 'settings.json', json_encode($settings, E2_JSON_STYLE))) {
        mv($_strings['er--settings-not-saved'], E2E_PERMISSIONS_ERROR);
        c(jv('e2m_timezone'));
    }
    c(jv('e2m_settings'));
}

function dy($l2)
{
    return array('offset' => (int)$l2['Offset'], 'is_dst' => (bool)$l2['IsDST'],);
}

function sy()
{
    return array('offset' => 0, 'is_dst' => false,);
}

function ay()
{
    global $settings;
    if (array_key_exists('timezone', $settings)) {
        return $settings['timezone'];
    } else {
        return sy();
    }
}

function qy($zl, $kl)
{
    if (@$zl['is_dst']) {
        $xl = (int)date('I', $kl);
        $el = date('Z', $kl) - $xl * SECONDS_IN_AN_HOUR;
        $rl = $zl['offset'];
        $tl = $rl - $el;
        $jl = date('I', $kl + $tl);
        return $jl;
    } else {
        return 0;
    }
}

function ly($zl, $kl)
{
    global $settings;
    if ($zl and is_array($zl)) {
        return ($zl['offset'] + qy($zl, $kl) * SECONDS_IN_AN_HOUR);
    }
}

function zy($kl)
{
    return ly(ay(), $kl);
}

function ky($hl, $qv, $zl)
{
    return gmdate($hl, $qv + ly($zl, $qv));
}

function xy($hl, $qv)
{
    return ky($hl, $qv, ay());
}

function ey($hb, $jb = false, $tb = false)
{
    if (is_numeric($tb)) {
        $gl = gmmktime(0, 0, 0, $jb, $tb, $hb);
        $wl = gmmktime(0, 0, 0, $jb, $tb + 1, $hb) - 1;
    } elseif (is_numeric($jb)) {
        $gl = gmmktime(0, 0, 0, $jb, 1, $hb);
        $wl = gmmktime(0, 0, 0, $jb + 1, 1, $hb) - 1;
    } else {
        $gl = gmmktime(0, 0, 0, 1, 1, $hb);
        $wl = gmmktime(0, 0, 0, 1, 1, $hb + 1) - 1;
    }
    return array($gl, $wl);
}

function ry($zl, $hb, $jb = false, $tb = false)
{
    list ($gl, $wl) = ey($hb, $jb, $tb);
    $gl -= ly($zl, $gl);
    $wl -= ly($zl, $wl);
    return array($gl, $wl);
}

function ty($hb, $jb = false, $tb = false)
{
    return ry(ay(), $hb, $jb, $tb);
}

function jy($hb, $jb = false, $tb = false)
{
    $ul = 13;
    $il = -12;
    $ul += 1;
    $il -= 1;
    list ($gl, $wl) = ey($hb, $jb, $tb);
    $gl -= $ul * 3600;
    $wl -= $il * 3600;
    return array($gl, $wl);
}

function hy($nl)
{
    if ((int)@$nl > 0) return (string)'+' . abs(@$nl); elseif ((int)@$nl < 0) return (string)'-' . abs(@$nl);
    else return '';
}

function gy($kl, $ol = '')
{
    $pl = zy($kl);
    $ml = ($pl >= 0) ? '+' : '-';
    $pl = abs($pl);
    $c4 = $pl % 60;
    $pl -= $c4;
    $jb = $pl % 3600 / 60;
    $pl -= $jb * 60;
    $v4 = $pl / 3600;
    if ($v4 < 10) $v4 = '0' . $v4;
    if ($jb < 10) $jb = '0' . $jb;
    return $ml . $v4 . $ol . $jb;
}

function wy($b4)
{
    global $settings;
    if (is_numeric($b4)) {
        $q1['offset'] = SECONDS_IN_A_MINUTE * $b4;
        $q1['is_dst'] = false;
        $y4           = SECONDS_IN_A_MINUTE * $b4 - SECONDS_IN_AN_HOUR;
        $n4           = array('offset' => $y4, 'is_dst' => true);
        $n4           = (int)ly($n4, time());
        if ($q1['offset'] == $n4) {
            $q1['offset'] = $y4;
            $q1['is_dst'] = true;
        }
    } else {
        if (array_key_exists('timezone', $settings)) {
            $q1 = $settings['timezone'];
        } else {
            $q1['offset'] = 0;
            $q1['is_dst'] = false;
        }
    }
    return $q1;
}

function uy($m4)
{
    $v4 = xy('H', $m4);
    if ($v4 <= 4) return 4; elseif ($v4 <= 10) return 1;
    elseif ($v4 <= 16) return 2;
    elseif ($v4 <= 22) return 3;
    else return 4;
}

function iy($f4, $d4 = null)
{
    global $_strings;
    if ($d4 === null) $d4 = ay();
    $s4 = ky('d.m.Y', $rb, $d4);
    $a4 = ky('d.m.Y', $f4, $d4);
    $q4 = SECONDS_IN_A_MINUTE;
    $l4 = SECONDS_IN_AN_HOUR;
    $rb = time();
    $z4 = uy($rb);
    $k4 = uy($f4);
    $kb = $rb - $f4;
    if ($kb < 0) return $_strings['tt--from-the-future'];
    if ($kb >= 0 and $kb < 54) return $_strings['tt--just-now'];
    if ($kb >= 54 and $kb < 108) return $_strings['tt--one-minute-ago'];
    $x4 = $kb + 12;
    $e4 = floor($x4 / $q4);
    if ($kb >= 108 and $kb < 54 * $q4) return e2l_get_string('tt--minutes-ago', array('minutes' => $e4));
    if ($kb >= 54 * $q4 and $kb < 108 * $q4) return $_strings['tt--one-hour-ago'];
    $x4 = $kb + 12 * $q4;
    $r4 = floor($x4 / $l4);
    if ($kb >= 108 * $q4 and $kb < 4 * $l4) return e2l_get_string('tt--hours-ago', array('hours' => $r4));
    $t4 = ky('G:i', $f4, $d4);
    if ($kb >= 4 * $l4 and $z4 > $k4 and $s4 == $a4) {
        return $_strings['tt--today'];
    }
    if ((($rb - $f4) <= 7884000)) {
        return e2l_get_string('tt--date', array('day' => ky('j', $f4, $d4), 'month' => ky('m', $f4, $d4),));
    }
    return ky('Y', $f4, $d4);
}

function oy($f4, $d4 = null)
{
    global $_strings;
    $kb = time() - $f4;
    if ($kb < 0) return $_strings['tt--from-the-future'];
    if ($kb == 0) return $_strings['tt--now'];
    $j4 = array(array(1, 'tt--seconds-short'), array(SECONDS_IN_A_MINUTE, 'tt--minutes-short'), array(SECONDS_IN_AN_HOUR, 'tt--hours-short'), array(SECONDS_IN_A_DAY, 'tt--days-short'), array(SECONDS_IN_A_MONTH, 'tt--months-short'), array(SECONDS_IN_A_YEAR, 'tt--years-short'), array(SECONDS_IN_A_YEAR + SECONDS_IN_A_MONTH, ''),);
    for ($r = 0; $r < count($j4); ++$r) {
        if ($kb >= $j4[$r][0] and $kb < $j4[$r + 1][0]) {
            return e2l_get_string($j4[$r][1], array('value' => floor($kb / $j4[$r][0])));
        }
    }
    if ($d4 === null) $d4 = ay();
    return ky('Y', $f4, $d4);
}

$_model_contractions       = ['key' => "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY", 'pkey' => "INT UNSIGNED DEFAULT '0' NOT NULL", 'pkey1' => "INT UNSIGNED DEFAULT '1' NOT NULL", 'int' => "INT DEFAULT '0' NOT NULL", 'uint' => "INT UNSIGNED DEFAULT '0' NOT NULL", 'time' => "INT UNSIGNED DEFAULT '0' NOT NULL", '0' => "TINYINT(1) DEFAULT '0' NOT NULL", '1' => "TINYINT(1) DEFAULT '1' NOT NULL", 'v1' => "VARCHAR(1) DEFAULT '' NOT NULL", 'v8' => "VARCHAR(8) DEFAULT '' NOT NULL", 'v15' => "VARCHAR(15) DEFAULT '' NOT NULL", 'v32' => "VARCHAR(32) DEFAULT '' NOT NULL", 'v39' => "VARCHAR(39) DEFAULT '' NOT NULL", 'v64' => "VARCHAR(64) DEFAULT '' NOT NULL", 'fid' => "VARCHAR(32) DEFAULT '" . $_config['default_formatter'] . "' NOT NULL", 'v255' => "VARCHAR(255) DEFAULT '' NOT NULL", 'text' => "MEDIUMTEXT",];
$_model                    = ['Actions' => [['ID', 'key'], ['SubsetID', 'pkey1'], ['EntityID', 'pkey'], ['Stamp', 'time'], ['ReadCount', 'int'],], 'Aliases' => [['ID', 'key'], ['SubsetID', 'pkey1'], ['EntityType', 'v1'], ['EntityID', 'pkey'], ['Alias', 'v64'], ['Stamp', 'time'],], 'Comments' => [['ID', 'key'], ['SubsetID', 'pkey1'], ['NoteID', 'pkey'], ['AuthorName', 'v255'], ['AuthorEmail', 'v255'], ['Text', 'text'], ['Reply', 'text'], ['IsVisible', '1'], ['IsFavourite', '0'], ['IsReplyVisible', '0'], ['IsReplyFavourite', '0'], ['IsAnswerAware', '1'], ['IsSubscriber', '0'], ['IsSpamSuspect', '0'], ['IsNew', '0'], ['Stamp', 'time'], ['LastModified', 'time'], ['ReplyStamp', 'time'], ['ReplyLastModified', 'time'], ['IP', 'v39'], ['IsGIPUsed', '0'], ['GIP', 'v15'], ['GIPAuthorID', 'v64'],], 'GIPsSessions' => [['ID', 'key'], ['SubsetID', 'pkey1'], ['GIP', 'v15'], ['GIPAuthorID', 'v64'], ['AuthorName', 'v255'], ['AuthorEmail', 'v255'], ['AuthorProfileLink', 'v255'], ['SessionToken', 'v255'], ['Stamp', 'time'],], 'Keywords' => [['ID', 'key'], ['SubsetID', 'pkey1'], ['Keyword', 'v255'], ['OriginalAlias', 'v64'], ['PageTitle', 'v255'], ['Description', 'text'], ['Summary', 'text'], ['Uploads', 'text'], ['IsVisible', '1'], ['IsFavourite', '0'],], 'Notes' => [['ID', 'key'], ['SubsetID', 'pkey1'], ['Title', 'v255'], ['Text', 'text'], ['Summary', 'text'], ['FormatterID', 'fid'], ['OriginalAlias', 'v64'], ['Uploads', 'text'], ['IsPublished', '0'], ['IsCommentable', '0'], ['IsVisible', '1'], ['IsFavourite', '0'], ['Stamp', 'time'], ['LastModified', 'time'], ['Offset', 'int'], ['IsDST', '0'], ['IsIndexed', '0'], ['IsExternal', '0'], ['ReadCount', 'uint'], ['SourceID', 'pkey'], ['SourceNoteID', 'pkey'], ['SourceNoteURL', 'v255'], ['SourceNoteJSONURL', 'v255'], ['SourceNoteData', 'text'],], 'NotesKeywords' => [['ID', 'key'], ['SubsetID', 'pkey1'], ['NoteID', 'pkey'], ['KeywordID', 'pkey'],], 'Sources' => [['ID', 'key'], ['SubsetID', 'pkey1'], ['TrueID', 'pkey'], ['Title', 'v255'], ['AuthorName', 'v255'], ['URL', 'v255'], ['PictureURL', 'v255'], ['IsWhiteListed', '0'], ['IsTrusted', '0'],],];
$_model_indexes_create_sql = ['index' => 'INDEX', 'unique' => 'UNIQUE INDEX', 'fulltext' => 'FULLTEXT',];
$_model_indexes_check_sql  = ['index' => 'KEY', 'unique' => 'UNIQUE KEY', 'fulltext' => 'FULLTEXT KEY',];
$_model_indexes            = ['Actions' => [['unique', ['EntityID', 'Stamp']], ['index', ['SubsetID']],], 'Aliases' => [['index', ['SubsetID']], ['index', ['Alias']], ['index', ['EntityID']],], 'Comments' => [['index', ['SubsetID']], ['index', ['NoteID']],], 'GIPsSessions' => [['unique', ['SubsetID', 'GIP', 'GIPAuthorID']], ['index', ['SubsetID']],], 'Keywords' => [['index', ['SubsetID']],], 'Notes' => [['fulltext', ['Title', 'Text']], ['index', ['SubsetID']], ['index', ['Stamp']], ['index', ['SourceID']], ['index', ['SourceNoteID']],], 'NotesKeywords' => [['index', ['SubsetID']], ['index', ['NoteID']],], 'Sources' => [['index', ['SubsetID']],],];
$_model_minimal_table_list = ['Comments', 'Keywords', 'Notes', 'NotesKeywords',];
function e2_model_data_check($h4)
{
    global $_db, $_model, $_model_minimal_table_list, $_config;
    $g4  = false;
    $w4  = array();
    $sql = 'SHOW TABLES FROM `' . mysqli_real_escape_string($_db['link'], $h4) . '`';
    $q1  = mysqli_query($_db['link'], $sql);
    if ($q1) {
        while ($u4 = mysqli_fetch_row($q1)) {
            foreach (array_keys($_model) as $i4) {
                if (strcasecmp($u4[0], $_config['db_table_prefix'] . $i4) === 0) {
                    $g4   = true;
                    $w4[] = $i4;
                }
            }
        }
    }
    $o4 = true;
    foreach (array_keys($_model) as $i4) {
        if (!in_array($i4, $w4)) {
            $o4 = false;
        }
    }
    $p4 = true;
    foreach ($_model_minimal_table_list as $i4) {
        if (!in_array($i4, $w4)) {
            $p4 = false;
        }
    }
    return array('occupied' => $g4, 'complete' => $o4, 'migrateable' => $p4,);
}

function py($u3)
{
    global $cz;
    list ($vz, $bz) = wn($u3['server']);
    if ((string)$bz === '') $bz = null;
    $yz = mysqli_init();
    $yz->options(MYSQLI_OPT_CONNECT_TIMEOUT, E2_MYSQL_CONNECT_TIMEOUT);
    $nz = @mysqli_real_connect($yz, 'p:' . $vz, $u3['user_name'], $u3['passw'], '', $bz);
    if (!$nz) return [];
    $mz = [];
    $fz = ['information_schema', 'performance_schema', 'sys', 'mysql'];
    @$cz++;
    $bf = 'SHOW DATABASES';
    if (Log::$cy) __log('DB [' . $cz . ']: ' . $bf);
    $q1 = mysqli_query($yz, $bf);
    while ($u4 = mysqli_fetch_row($q1)) {
        if (mysqli_select_db($yz, $u4[0]) and !in_array($u4[0], $fz)) {
            $mz[] = $u4[0];
        }
    }
    return $mz;
}

function cn($dz)
{
    global $_config;
    xn("SHOW TABLES LIKE '" . $_config['db_table_prefix'] . $dz . "'");
    $sy = en();
    return count($sy) > 0;
}

function vn($dz, $sz = null)
{
    global $_config;
    if ($sz === null) {
        $sz = $_config['db_table_prefix'];
    }
    xn("SHOW TABLE STATUS LIKE '" . $sz . $dz . "'");
    $q1 = en();
    return $q1 ? $q1[0] : [];
}

function bn($dz)
{
    global $_model, $_model_contractions, $_model_indexes, $_model_indexes_create_sql, $_config, $_db;
    if (!array_key_exists($dz, $_model)) throw new AeModelUnknownTableException ();
    if (cn($dz)) return;
    $sz = $_config['db_table_prefix'];
    $az = [];
    foreach ($_model[$dz] as $qz) {
        list ($name, $type) = $qz;
        $az[] = "`" . $name . "` " . $_model_contractions[$type];
    }
    if (is_array(@$_model_indexes[$dz])) {
        foreach ($_model_indexes[$dz] as $lz) {
            list ($type, $zz) = $lz;
            $kz   = implode('', $zz);
            $xz   = $_model_indexes_create_sql[$type] . ' `' . $kz . '` (`' . implode('`, `', $zz) . '`)';
            $az[] = $xz;
        }
    }
    $sql = ("CREATE TABLE `" . $sz . $dz . "` " . "(" . implode(", ", $az) . ") " . "ENGINE=InnoDB DEFAULT CHARSET=" . $_db['charset']);
    xn($sql);
}

function yn($dz, $t1, $ez = 'INSERT', $rz = '')
{
    global $_config, $_db;
    $tz['SubsetID'] = $_config['db_table_subset'];
    foreach ($t1 as $t => $xf) {
        $tz[$t] = "'" . rn($xf) . "'";
    }
    $jz = "`" . implode("`, `", array_keys($tz)) . "`";
    $hz = implode(", ", array_values($tz));
    xn($ez . " INTO `" . $_config['db_table_prefix'] . $dz . "` " . "(" . $jz . ") VALUES (" . $hz . ")" . ($rz ? (' ' . $rz) : ''));
    $t1['ID'] = mysqli_insert_id($_db['link']);
    return $t1;
}

function nn($dz, $t1, $gz = false, $wz = false)
{
    global $_config;
    if (Log::$cy) __log('Model: update record in table ' . $dz . ' {');
    $uz = array();
    foreach (e2model__soft_fields_for_table_($dz) as $iz) {
        if (array_key_exists($iz, $t1)) {
            $uz[] = '`' . $iz . '`' . "='" . rn($t1[$iz]) . "'";
        }
    }
    $oz = array();
    if (is_array($gz)) {
        foreach (e2model__soft_fields_for_table_($dz) as $iz) {
            if (array_key_exists($iz, $gz)) {
                $oz[] = '`' . $iz . '`' . "='" . rn($gz[$iz]) . "'";
            }
        }
    }
    if (count($oz)) {
        $b = implode(" AND ", $oz);
    } else {
        if (!array_key_exists('ID', $t1) or !is_numeric($t1['ID'])) {
            if (Log::$cy) __log('Error: e2_update_record must be called with an ID field in $record when updating single row');
            return false;
        }
        $b = "`ID`=" . $t1['ID'];
    }
    if (count($uz) > 0) {
        $pz = $wz ? 'LOW_PRIORITY ' : '';
        xn("UPDATE " . $pz . "`" . $_config['db_table_prefix'] . $dz . "` " . "SET " . implode(', ', $uz) . " " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND (" . $b . ")");
    }
    if (Log::$cy) __log('}');
    return true;
}

function e2model__soft_fields_for_table_($dz)
{
    global $_model;
    $d = array();
    if (array_key_exists($dz, $_model)) {
        foreach ($_model[$dz] as $iz) {
            if (!in_array($iz[1], array('key'))) {
                $d[] = $iz[0];
            }
        }
    }
    return $d;
}

function e2m_install()
{
    global $_strings, $_superconfig, $_files_written, $_diagnose;
    if (fn_() !== null) c();
    qs(DEFAULT_TEMPLATE);
    $d = array();
    if ($_superconfig['disallow_installer']) {
        die ('Installer disabled by superconfig');
    }
    if (Log::$cy) __log('Installer: not installed, present user with form');
    $ck              = true;
    $vk['server']    = @$_COOKIE[b('install_db_server')];
    $vk['user_name'] = @$_COOKIE[b('install_db_user_name')];
    $vk['passw']     = i2(@$_COOKIE[b('install_db_passw')]);
    $vk['name']      = @$_COOKIE[b('install_db_name')];
    if (!@isset ($_diagnose['ok?'])) fv();
    if (!$_diagnose['ok?']) {
        if (Log::$cy) __log('Installer: problems, tell user');
        $ck = false;
    }
    $d = ['title' => $_strings['pt--install'], 'heading' => $_strings['pt--install'], 'form-install' => ['form-action' => jv('e2s_install'), 'form-check-db-config-action' => jv('e2j_check_db_config'), 'form-list-databases-action' => jv('e2j_list_databases'), 'installation-possible?' => $ck, 'submit-text' => $_strings['fb--begin'], 'retry-href' => jv('e2m_install'), 'retry-text' => $_strings['fb--retry'], 'db-server' => htmlspecialchars(@$vk['server'] ? $vk['server'] : 'localhost'), 'db-user' => htmlspecialchars(@$vk['user_name'] ? $vk['user_name'] : 'root'), 'db-password' => '', 'db-database' => htmlspecialchars(@$vk['name']),]];
    return $d;
}

function fn_()
{
    static $bk = null;
    if ($bk === null) {
        $bk = @unserialize(@file_get_contents(USER_FOLDER . 'instance.psa')) or $bk = null;
    }
    return $bk;
}

function dn($yk)
{
    static $bk = null;
    $bk            = fn_();
    $bk['version'] = $yk;
    if (n3(USER_FOLDER . '/instance.psa', serialize($bk))) {
        return $bk;
    } else {
        die ('Cannot instantiate v' . $yk . ': probably permission denied');
    }
}

function e2s_instantiate($parameters)
{
    global $_strings;
    if (fn_() !== null) {
        die ('Remove the file "' . USER_FOLDER . 'instance.psa" first');
    } else {
        if (is_numeric($parameters['version'])) {
            if (dn($parameters['version'])) {
                mv($_strings['gs--instantiated-version'] . ' v' . $parameters['version'], E2E_MESSAGE);
                c(jv('e2m_frontpage', array('page' => 1)));
            }
        }
    }
    die ('Could not create instance of engine');
}

function e2_install($lv)
{
    global $_folders_written, $_model, $_strings, $_config, $settings;
    if (fn_() !== null) {
        throw new AeInstallAlreadyInstalledException ('Instance already created');
    }
    if ($_config['log_installs']) {
        Log::$cy = true;
        if (Log::$cy) bv('install-$');
    }
    if (Log::$cy) __log('Installer: force directories');
    foreach ($_folders_written as $nk) {
        @j($nk);
    }
    if (Log::$cy) __log('Installer: write password hash');
    if (!@n3(USER_FOLDER . 'password-hash.psa', serialize(sha1($lv['password'])))) {
        throw new AePasswordHashNotSavedException;
    }
    if (array_key_exists('plain_password', $lv['db_params'])) {
        $lv['db_params']['passw'] = u2($lv['db_params']['plain_password']);
        unset ($lv['db_params']['plain_password']);
    }
    $settings['db']       = $lv['db_params'];
    $settings['template'] = DEFAULT_TEMPLATE;
    $settings['language'] = DEFAULT_LANGUAGE;
    kn('check database during installation', $lv['db_params']);
    $o3 = e2_model_data_check($lv['db_params']['name']);
    $mk = false;
    if ($o3['occupied']) {
        if ($o3['migrateable'] and $lv['allow_migration']) {
            $mk = true;
            if (Log::$cy) __log('Installer: data exists and migrateable');
        } else {
            if (Log::$cy) __log('Installer: incomplete data in the database');
            throw new AeInstallDatabaseOccupiedException ('Database already has some data');
        }
    }
    if ($mk) {
        if (Log::$cy) __log('Installer: no need to create tables, will migrate');
        try {
            qn();
        } catch (AeMySQLException $e) {
            kv($e, 'Could not migrate');
            mv($_strings['er--double-check-db-params']);
        }
    } else {
        if (Log::$cy) __log('Installer: create tables');
        foreach (array_keys($_model) as $dz) {
            bn($dz);
        }
    }
    if (Log::$cy) __log('Installer: write settings');
    if (!@n3(USER_FOLDER . 'settings.json', json_encode($settings, E2_JSON_STYLE))) {
        throw new AeSettingsNotSavedException;
    }
    e2_drop_all_kinds_of_cache();
    if (Log::$cy) __log('Installer: search index');
    $p3 = ea();
    try {
        $p3->erase();
    } catch (\S2\Rose\Exception\RuntimeException $e) {
        if (Log::$cy) __log('Installer: Rose not available');
    }
    aa();
    gn();
    if (Log::$cy) __log('Installer: instantiate');
    dn(E2_VERSION);
    if (Log::$cy) __log('Installer: complete');
}

function sn()
{
    $u3['server'] = $u3['user_name'] = $u3['passw'] = $u3['name'] = '';
    if (array_key_exists('db-server', $_POST)) $u3['server'] = $_POST['db-server'];
    if (array_key_exists('db-user', $_POST)) $u3['user_name'] = $_POST['db-user'];
    if (array_key_exists('db-password', $_POST)) $u3['passw'] = $_POST['db-password'];
    if (array_key_exists('db-database', $_POST)) $u3['name'] = $_POST['db-database'];
    return $u3;
}

function e2s_install()
{
    global $_strings, $_config, $_db;
    if (fn_() !== null) c();
    $u3 = sn();
    foreach ($u3 as $t => $xf) {
        y('install_db_' . $t, $xf);
    }
    if (!array_key_exists('password', $_POST) or trim($_POST['password']) == '') {
        mv($_strings['er--no-password-entered'], E2E_USER_ERROR);
        c(jv('e2m_install'));
    }
    $fk = trim($_POST['password']);
    @session_start();
    $i3 = false;
    try {
        e2_install(['allow_migration' => true, 'password' => $fk, 'db_params' => $u3,]);
        $i3 = true;
    } catch (AeMySQLCannotConnectException $e) {
        mv($_strings['er--cannot-connect-to-db'] . ':<br />' . mysqli_connect_error() . ' (' . mysqli_connect_errno() . ')');
    } catch (AeMySQLTooOldException $e) {
        mv(e2l_get_string('er--mysql-version-too-old', ['v1' => $_db['version'], 'v2' => E2_MINIMUM_MYSQL,]));
    } catch (AeMySQLException $e) {
        mv($_strings['er--cannot-find-db'] . ' ' . $u3['name']);
    } catch (AeInstallDatabaseOccupiedException $e) {
        mv($_strings['er--db-data-incomplete-install']);
    } catch (AeNotSavedException $e) {
        mv($_strings['er--settings-not-saved'], E2E_PERMISSIONS_ERROR);
    } catch (AeInstallException $e) {
    }
    if (!$i3) c(jv('e2m_install'));
    $dk['sessions'] = [['stamp' => time(), 'remote_ip' => q2(), 'key_hash' => z2(true), 'ua' => $_SERVER['HTTP_USER_AGENT'],]];
    if (!e2_($dk)) {
        mv($_strings['er--cannot-write-auth-data'], E2E_PERMISSIONS_ERROR);
    }
    p3(jv('e2s_bsi_step', array()));
    c();
}

function an()
{
    global $v, $c, $_superconfig, $_config;
    $bk = fn_();
    if (fn_() !== null) {
        if (E2_VERSION < $bk ['version']) {
            if (@$_config['dev_ignore_version_mismatch']) return;
            if (Log::$cy) __log('Installer: cannot downdate');
            header('HTTP/1.1 503 Service Unavailable');
            die ('E2 does not support automatic downgrade.');
        } elseif (E2_VERSION > $bk ['version']) {
            if (Log::$cy) __log('Installer: need to update');
            header('Location: http://' . $v . $c . '/perform_update/');
            header('Location: ' . jv('e2s_update_perform'));
            die;
        } else {
            return;
        }
    }
    if (Log::$cy) __log('Installer: not installed {');
    if ((strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') === 0)) {
        if (Log::$cy) __log('Installer: running on Apache');
        $sk = DEFAULTS_FOLDER . 'default.htaccess';
        $ak = false;
        if (!is_file($sk)) {
            echo 'File not found: ' . $sk . '. Please use the full E2 installation package.';
            die;
        }
        if (is_file('.htaccess')) {
            if (Log::$cy) __log('Installer: there is a .htaccess file in the installation directory');
            $qk = file_get_contents($sk);
            $lk = file_get_contents('.htaccess');
            if ($lk != $qk) {
                $ak = true;
                $zk = $kk = '.htaccess.old';
                $xk = 1;
                while (is_file($kk)) {
                    $kk = $zk . '.' . $xk++;
                }
                if (Log::$cy) __log('Installer: existing .htaccess wrong, backing up as <' . $kk . '>');
                if (!@rename('.htaccess', $kk)) {
                    if (Log::$cy) __log('Installer: fuck');
                    echo 'Looks like you are using Apache and have put an incorrect ".htaccess" file in the installation directory. Additionally, the installer was not able to back up your existing ".htaccess" file in order to replace it with the correct one. Please use the full E2 installation package and grant write access on the installation target directory, all the files and subdirectories.';
                    die;
                }
            }
        } else {
            $ak = true;
        }
        if ($ak) {
            if (Log::$cy) __log('Installer: writing a correct .htaccess file');
            if (!@copy($sk, '.htaccess')) {
                if (Log::$cy) __log('Installer: fuck');
                echo 'The installer was not able to create a correct ".htaccess" file. Please grant write access on the installation target directory.';
                die;
            }
            @chmod('.htaccess', E2_NEW_FILES_RIGHTS);
        }
    }
    if ($_superconfig['disallow_installer']) {
        if (Log::$cy) __log('Installer: disallowed in superconfig');
        xv(new AeNotAndCannotBeInstalledException);
    } else {
        $ek = jv('e2m_install');
        if (Log::$cy) __log('Installer: will need to install, going to ' . $ek);
        if (Log::$cy) __log('}');
        c($ek);
    }
}

function e2j_check_db_config()
{
    global $_db, $_strings;
    $u3 = sn();
    $zv = ['success' => true, 'data' => ['message' => '', 'db-responding' => false, 'db-connected' => false, 'db-found' => false, 'db-compatible' => false, 'db-occupied' => false, 'db-migrateable' => false,]];
    try {
        $u3['passw'] = u2($u3['passw']);
        kn('connect to check DB config (try 1)', $u3);
    } catch (AeMySQLAccessDeniedException $e) {
        $zv['data']['db-responding'] = true;
        $zv                          = json_encode($zv);
        die ($zv);
    } catch (AeMySQLCannotConnectException $e) {
        $zv['data']['message'] = 'no-connect';
        $zv                    = json_encode($zv);
        die ($zv);
    } catch (AeMySQLTooOldException $e) {
        $zv['data']['db-responding'] = true;
        $zv['data']['db-connected']  = true;
        $zv['data']['message']       = e2l_get_string('er--mysql-version-too-old', ['v1' => $_db['version'], 'v2' => E2_MINIMUM_MYSQL,]);
        $zv                          = json_encode($zv);
        die ($zv);
    } catch (AeMySQLNotFoundException $e) {
        $zv['data']['db-responding'] = true;
        $zv['data']['db-connected']  = true;
        if ($u3['name']) {
            $zv = json_encode($zv);
            die ($zv);
        } else {
            $mz = py($u3);
            if (count($mz) > 0) {
                $zv['data']['db-found'] = true;
                $u3['name']             = $mz[0];
            } else {
                $zv = json_encode($zv);
                die ($zv);
            }
        }
    }
    $zv['data']['db-responding'] = true;
    $zv['data']['db-connected']  = true;
    $zv['data']['db-found']      = true;
    $zv['data']['db-compatible'] = true;
    try {
        kn('connect to check DB config (try 2)', $u3);
    } catch (AeMySQLException $e) {
        $zv = json_encode($zv);
        die ($zv);
    }
    $zv['data']['db-good'] = true;
    $o3                    = e2_model_data_check($u3['name']);
    if ($o3['occupied']) {
        if ($o3['migrateable']) {
            $zv['data']['message'] = $_strings['gs--data-exists'];
        } else {
            $zv['data']['db-good'] = false;
            $zv['data']['message'] = $_strings['er--db-data-incomplete-install'];
        }
    }
    $zv = json_encode($zv);
    die ($zv);
}

function e2j_list_databases()
{
    $u3 = sn();
    $mz = py($u3);
    $zv = ['success' => true, 'data' => ['databases-list' => $mz,]];
    $zv = json_encode($zv);
    die ($zv);
}

function qn()
{
    global $_db, $_config, $_model, $_model_indexes, $_model_indexes_create_sql, $_model_indexes_check_sql;
    $sz = $_config['db_table_prefix'];
    xn('SET sql_quote_show_create=1');
    ln($sz, ($_db['charset'] === 'utf8mb4') ? 'utf8mb4' : 'utf8');
    if (Log::$cy) __log('Get existing table information {');
    $rk = false;
    foreach (array_keys($_model) as $dz) {
        bn($dz);
        try {
            xn("SHOW CREATE TABLE `" . $sz . $dz . "`");
            $tk[$dz] = en();
            $tk[$dz] = $tk[$dz][0]['Create Table'];
        } catch (AeMySQLException $e) {
            kv($e);
            die ('Database table "' . $prefix . $dz . '" not accessible during migration. Check your database availability');
        }
        xn("SHOW INDEX FROM `" . $sz . $dz . "`");
        $jk = en();
        $hk = [];
        $gk = [];
        foreach ($jk as $lz) {
            $lz = $lz['Key_name'];
            if (preg_match('/\_[0-9]+$/', $lz) or ($dz === 'Actions' and $lz === 'EntityID') or ($dz === 'GIPsSessions' and $lz === 'GIP') or ($dz === 'GIPsSessions' and $lz === 'SubsetID') or ($dz === 'Notes' and $lz === 'Title')) {
                $hk[] = $lz;
                $gk[] = 'DROP INDEX `' . $lz . '`';
            }
            if ($dz === 'Actions' and $lz === 'EntityID') {
                $rk = true;
            }
            if ($dz === 'Actions' and $lz === 'EntityIDStamp') {
                $rk = true;
            }
        }
        if (count($gk)) {
            $gk = implode(', ', array_unique($gk));
            $hk = implode(', ', array_unique($hk));
            if (Log::$cy) __log('Drop erroneous index "' . $hk . '" on "' . $sz . $dz . '"');
            xn("ALTER TABLE `" . $sz . $dz . "` " . $gk);
        }
        if (!strstr($tk[$dz], 'InnoDB')) {
            xn("ALTER TABLE `" . $sz . $dz . "` " . "ENGINE = InnoDB");
        }
        if (!strstr($tk[$dz], '`SubsetID`')) {
            xn("ALTER TABLE `" . $sz . $dz . "` " . "ADD `SubsetID` INT UNSIGNED DEFAULT '0' NOT NULL AFTER `ID`");
        }
        if ($dz === 'Actions' and strstr($tk['Actions'], '`ReadCount`') and !$rk) {
            zn($sz);
        }
        if ($_config['db_table_subset'] > 0) {
            xn("UPDATE `" . $sz . $dz . "` " . "SET `SubsetID` = " . $_config['db_table_subset'] . " " . "WHERE `SubsetID` = 0");
        } else {
            die ('db_table_subset must be greater than 0');
        }
    }
    if (Log::$cy) __log('}');
    if (!strstr($tk['Actions'], '`ReadCount`')) {
        xn("ALTER TABLE `" . $sz . "Actions` " . "ADD `ReadCount` INT DEFAULT '0' NOT NULL");
    }
    if (strstr($tk['Actions'], '`HitCount`')) {
        xn("ALTER TABLE `" . $sz . "Actions` " . "DROP `HitCount`");
        xn("DELETE FROM `" . $sz . "Actions` " . "WHERE `ReadCount` = 0");
    }
    if (!strstr($tk['Aliases'], '`EntityType`')) {
        xn("ALTER TABLE `" . $sz . "Aliases` " . "ADD `EntityType` VARCHAR( 1 ) DEFAULT '' NOT NULL AFTER `ID`");
    }
    xn("UPDATE `" . $sz . "Aliases` " . "SET `EntityType` = 'n' " . "WHERE `EntityType` = ''");
    xn("DELETE FROM `" . $_config['db_table_prefix'] . "Aliases` " . "WHERE `ID` IN (" . "SELECT `ID` FROM (" . "SELECT a.`ID` FROM `" . $_config['db_table_prefix'] . "Aliases` a " . "LEFT OUTER JOIN `" . $_config['db_table_prefix'] . "Keywords` e " . "ON a.`EntityID` = e.`ID` " . "WHERE a.`EntityType` = 't' " . "AND e.`ID` IS NULL" . ") AS temp" . ")", 'clean up “leaked” tag aliases');
    if (!stristr($tk['Comments'], '`Text` MEDIUMTEXT')) {
        xn("ALTER TABLE `" . $sz . "Comments` " . "CHANGE `Text` `Text` MEDIUMTEXT");
    }
    if (!stristr($tk['Comments'], '`Reply` MEDIUMTEXT')) {
        xn("ALTER TABLE `" . $sz . "Comments` " . "CHANGE `Reply` `Reply` MEDIUMTEXT");
    }
    if (!stristr($tk['Comments'], '`IP` VARCHAR(39)')) {
        xn("ALTER TABLE `" . $sz . "Comments` " . "CHANGE `IP` `IP` VARCHAR(39)  DEFAULT '' NOT NULL");
    }
    if (!strstr($tk['Comments'], '`IsGIPUsed`')) {
        xn("ALTER TABLE `" . $sz . "Comments` " . "ADD `IsGIPUsed` TINYINT(1) DEFAULT '0' NOT NULL AFTER `IP`");
    }
    if (!strstr($tk['Comments'], '`GIP`')) {
        xn("ALTER TABLE `" . $sz . "Comments` " . "ADD `GIP` VARCHAR(15) DEFAULT '' NOT NULL AFTER `IsGIPUsed`");
    }
    if (!strstr($tk['Comments'], '`GIPAuthorID`')) {
        xn("ALTER TABLE `" . $sz . "Comments` " . "ADD `GIPAuthorID` VARCHAR(64) DEFAULT '' NOT NULL AFTER `GIP`");
    }
    if (strstr($tk['Comments'], '`SocialType`')) {
        xn("ALTER TABLE `" . $sz . "Comments` " . "DROP `SocialType`");
    }
    if (strstr($tk['Comments'], '`SocialID`')) {
        xn("ALTER TABLE `" . $sz . "Comments` " . "DROP `SocialID`");
    }
    if (!strstr($tk['GIPsSessions'], '`AuthorEmail`')) {
        xn("ALTER TABLE `" . $sz . "GIPsSessions` " . "ADD `AuthorEmail` VARCHAR(255) DEFAULT '' NOT NULL AFTER `AuthorName`");
    }
    if (!strstr($tk['GIPsSessions'], '`AuthorProfileLink`')) {
        xn("ALTER TABLE `" . $sz . "GIPsSessions` " . "ADD `AuthorProfileLink` VARCHAR(255) DEFAULT '' NOT NULL AFTER `AuthorEmail`");
    }
    if (strstr($tk['Keywords'], '`ParentKeywordID`')) {
        xn("ALTER TABLE `" . $sz . "Keywords` " . "DROP `ParentKeywordID`");
    }
    if (!strstr($tk['Keywords'], '`OriginalAlias`')) {
        xn("ALTER TABLE `" . $sz . "Keywords` " . "CHANGE `URLName` `OriginalAlias` VARCHAR( 64 ) DEFAULT '' NOT NULL AFTER `Keyword`");
    }
    if (!strstr($tk['Keywords'], '`PageTitle`')) {
        xn("ALTER TABLE `" . $sz . "Keywords` " . "ADD `PageTitle` VARCHAR(255) DEFAULT '' NOT NULL AFTER `OriginalAlias`");
    }
    if (!stristr($tk['Keywords'], '`Description` MEDIUMTEXT')) {
        xn("ALTER TABLE `" . $sz . "Keywords` " . "CHANGE `Description` `Description` MEDIUMTEXT");
    }
    if (!strstr($tk['Keywords'], '`Summary`')) {
        xn("ALTER TABLE `" . $sz . "Keywords` " . "ADD `Summary` MEDIUMTEXT AFTER `Description`");
    }
    if (!strstr($tk['Keywords'], '`Uploads`')) {
        xn("ALTER TABLE `" . $sz . "Keywords` " . "ADD `Uploads` MEDIUMTEXT AFTER `Summary`");
    }
    if (!stristr($tk['Keywords'], '`Uploads` MEDIUMTEXT')) {
        xn("ALTER TABLE `" . $sz . "Keywords` " . "CHANGE `Uploads` `Uploads` MEDIUMTEXT");
    }
    if (!strstr($tk['Keywords'], '`IsVisible`')) {
        xn("ALTER TABLE `" . $sz . "Keywords` " . "ADD `IsVisible` TINYINT(1) DEFAULT '1' NOT NULL AFTER `Uploads`");
    }
    xn("UPDATE `" . $sz . "Keywords` SET `Summary` = '' WHERE `Summary` IS NULL");
    xn("UPDATE `" . $sz . "Keywords` SET `Uploads` = '' WHERE `Uploads` IS NULL");
    if (!strstr($tk['Notes'], '`FormatterID`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "ADD `FormatterID` VARCHAR( 32 ) DEFAULT '" . $_config['default_formatter'] . "' NOT NULL AFTER `Text`");
    }
    if (!strstr($tk['Notes'], "DEFAULT 'calliope'")) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "CHANGE `FormatterID` `FormatterID` VARCHAR( 32 ) DEFAULT '" . $_config['default_formatter'] . "' NOT NULL");
    }
    if (!strstr($tk['Notes'], '`OriginalAlias`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "CHANGE `URLName` `OriginalAlias` VARCHAR( 64 ) DEFAULT '' NOT NULL AFTER `FormatterID`");
    }
    if (strstr($tk['Notes'], '`IP`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "DROP `IP`");
    }
    if (!stristr($tk['Notes'], '`Text` MEDIUMTEXT')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "CHANGE `Text` `Text` MEDIUMTEXT");
    }
    if (!strstr($tk['Notes'], '`Summary`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "ADD `Summary` MEDIUMTEXT AFTER `Text`");
    }
    if (!strstr($tk['Notes'], '`IsIndexed`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "ADD `IsIndexed` TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER `IsDST`");
    }
    if (!strstr($tk['Notes'], '`Uploads`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "ADD `Uploads` MEDIUMTEXT AFTER `OriginalAlias`");
    }
    if (!stristr($tk['Notes'], '`Uploads` MEDIUMTEXT')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "CHANGE `Uploads` `Uploads` MEDIUMTEXT");
    }
    if (!strstr($tk['Notes'], '`IsExternal`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "ADD `IsExternal` TINYINT(1) DEFAULT '0' NOT NULL AFTER `IsIndexed`");
    }
    if (!strstr($tk['Notes'], '`SourceID`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "ADD `SourceID` INT UNSIGNED DEFAULT '0' NOT NULL AFTER `IsExternal`");
    }
    if (!strstr($tk['Notes'], '`SourceNoteID`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "ADD `SourceNoteID` INT UNSIGNED DEFAULT '0' NOT NULL AFTER `SourceID`");
    }
    if (!strstr($tk['Notes'], '`SourceNoteURL`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "ADD `SourceNoteURL` VARCHAR(255) DEFAULT '' NOT NULL AFTER `SourceNoteID`");
    }
    if (!strstr($tk['Notes'], '`SourceNoteData`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "ADD `SourceNoteData` MEDIUMTEXT AFTER `SourceNoteURL`");
    }
    if (!strstr($tk['Notes'], '`SourceNoteJSONURL`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "ADD `SourceNoteJSONURL` VARCHAR(255) DEFAULT '' NOT NULL AFTER `SourceNoteData`");
    }
    if (strstr($tk['Notes'], '`SourceMainImageURL`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "DROP `SourceMainImageURL`");
    }
    if (strstr($tk['Notes'], '`IsIssue`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "DROP `IsIssue`");
    }
    if (!strstr($tk['Notes'], '`ReadCount`')) {
        xn("ALTER TABLE `" . $sz . "Notes` " . "ADD `ReadCount` INT UNSIGNED DEFAULT '0' NOT NULL AFTER `IsExternal`");
        xn("UPDATE `" . $sz . "Notes` n JOIN (" . "SELECT `EntityID`, SUM(`ReadCount`) `AggregateReadCount` " . "FROM  `" . $sz . "Actions` " . "GROUP BY `EntityID`" . ") a ON n.`ID` = a.`EntityID` " . "SET `ReadCount` = `AggregateReadCount`");
    }
    xn("UPDATE `" . $sz . "Notes` SET `Summary` = '' WHERE `Summary` IS NULL");
    xn("UPDATE `" . $sz . "Notes` SET `Uploads` = '' WHERE `Uploads` IS NULL");
    xn("UPDATE `" . $sz . "Notes` SET `SourceNoteData` = '' WHERE `SourceNoteData` IS NULL");
    if (!strstr($tk['Sources'], '`TrueID`')) {
        xn("ALTER TABLE `" . $sz . "Sources` " . "ADD `TrueID` INT UNSIGNED DEFAULT '0' NOT NULL AFTER `ID`");
        xn("UPDATE `" . $sz . "Sources` " . "SET `TrueID` = `ID`");
    }
    if (Log::$cy) __log('Ensure indexes {');
    if (strstr($tk['Notes'], '`Title` (`Title`(191))')) {
        if (Log::$cy) __log('Drop erroneous index on "' . $sz . 'Notes.Title"');
        xn("ALTER TABLE `" . $sz . "Notes` " . "DROP INDEX `Title`");
    }
    foreach (array_keys($_model) as $dz) {
        foreach ($_model_indexes[$dz] as $lz) {
            list ($type, $zz) = $lz;
            $kz = implode('', $zz);
            $wk = $_model_indexes_check_sql[$type] . ' `' . $kz . '` (`' . implode('`,`', $zz) . '`)';
            $xz = $_model_indexes_create_sql[$type] . ' `' . $kz . '` (`' . implode('`, `', $zz) . '`)';
            if (!strstr($tk[$dz], $wk)) {
                if (Log::$cy) __log('Table "' . $sz . $dz . '" is missing "' . $_model_indexes_check_sql[$type] . '" on columns "' . implode('", "', $zz) . '"');
                if ($type !== 'fulltext' or $_db['innodb-fulltext?']) {
                    xn("ALTER TABLE `" . $sz . $dz . "` " . "ADD " . $xz);
                } else {
                    if (Log::$cy) __log('MySQL version does not support fulltext search, skipping creation of this index');
                }
            }
        }
    }
    if (Log::$cy) __log('}');
    return true;
}

function ln($sz, $uk)
{
    global $_model, $_db;
    if (!in_array($uk, ['utf8', 'utf8mb4'])) return;
    if (Log::$cy) __log('Ensure encoding ' . $uk . ' on all tables {');
    $ik = xa();
    foreach ($ik as $t => $xf) {
        $ik[$t] = SEARCH_EXTRA_PREFIX . $xf;
    }
    $ok = array_merge(array_keys($_model), array_values($ik));
    foreach ($ok as $i4) {
        if (Log::$cy) __log('Migrate: Check table ' . $i4);
        $pk = vn($i4, $sz);
        if (!$pk) continue;
        $c7 = $pk['Collation'];
        if ($uk === 'utf8' and ($c7 == 'utf8_general_ci')) continue;
        if ($uk === 'utf8mb4' and stripos($c7, 'utf8mb4') === 0) continue;
        if (Log::$cy) __log('Migrate: Drop indexes of table ' . $i4);
        xn("SHOW INDEX FROM `" . $sz . $i4 . "` " . "WHERE `Key_name` <> 'PRIMARY' " . "AND `Seq_in_index` = 1", 'show indexes from table ' . $i4);
        $jk = en();
        foreach ($jk as $lz) {
            xn("ALTER TABLE `" . $sz . $i4 . "` " . "DROP INDEX `" . $lz['Key_name'] . "`", 'drop index ' . $lz['Key_name']);
        }
        if (Log::$cy) __log('Migrate: Convert table ' . $i4 . ' to ' . $uk);
        xn("ALTER TABLE `" . $sz . $i4 . "` " . "CONVERT TO CHARACTER SET " . $uk . (($uk === 'utf8') ? " COLLATE utf8_general_ci" : ""), 'convert table to character set ' . $uk);
    }
    if (Log::$cy) __log('}');
}

function zn($sz)
{
    if (Log::$cy) __log('Table "' . $sz . 'Actions" is missing necessary UNIQUE index, must rearrange {');
    xn("DROP TABLE IF EXISTS `" . $sz . "Actions_Fixed`", 'remove temporary Actions_Fixed table if exists');
    xn("CREATE TABLE `" . $sz . "Actions_Fixed` " . "LIKE `" . $sz . "Actions`", 'create new temporary Actions_Fixed table');
    xn("ALTER TABLE `" . $sz . "Actions_Fixed` " . "ADD UNIQUE INDEX(`EntityID`, `Stamp`)", 'add UNIQUE index to the temporary Actions_Fixed table');
    xn("INSERT INTO `" . $sz . "Actions_Fixed` (`SubsetID`, `EntityID`, `Stamp`, `ReadCount`) " . "SELECT `SubsetID`, `EntityID`, `Stamp`, `AggregateReadCount` FROM (" . "SELECT `SubsetID`, `EntityID`, `Stamp`, SUM(`ReadCount`) `AggregateReadCount` " . "FROM `" . $sz . "Actions` " . "GROUP BY `EntityID`, `Stamp`" . ") `" . $sz . "Actions_Fixed_AliasRequiredForNoReason`", 'rearrange Actions records from existing problematic Actions table to the new temporary Actions_Fixed table');
    xn("RENAME TABLE `" . $sz . "Actions` TO `" . $sz . "Actions_Corrupt`", 'rename Actions to Actions_Corrupt');
    xn("RENAME TABLE `" . $sz . "Actions_Fixed` TO `" . $sz . "Actions`", 'rename Actions_Fixed to Actions');
    xn("DROP TABLE `" . $sz . "Actions_Corrupt`", 'remove Actions_Corrupt table');
    if (Log::$cy) __log('}');
}

function e2s_update_perform()
{
    global $settings, $_config, $_diagnose, $_strings;
    if (1) {
        $v7 = ini_get('max_execution_time') + 5;
        $b7 = @unserialize(file_get_contents(USER_FOLDER . 'updating.psa'));
        if (!is_array($b7)) $b7 = [];
        if (isset ($b7['lock']) and $b7['lock'] >= time() - $v7) {
            throw new AeUpdateAlreadyInProcess ('Update already in process');
        }
        $b7['lock'] = time();
        if (!@n3(USER_FOLDER . 'updating.psa', serialize($b7))) {
            throw new AeUpdateCannotLock ('Update can’t get a new lock');
        }
    }
    $bk = fn_();
    $g  = max((int)($bk['version']), 2285);
    if ($bk['version'] == E2_VERSION) v();
    if ($_config['log_updates']) {
        Log::$cy = true;
        if (Log::$cy) bv('update-$');
    }
    if ($_config['backup_before_update']) {
        if (Log::$cy) __log('Backup before update {');
        if (!gn()) {
            @unlink(USER_FOLDER . 'updating.psa');
            die ('Could not make backup before update. Try again?');
        }
        if (Log::$cy) __log('}');
    }
    if (Log::$cy) __log('Update from v' . $g . ' to v' . E2_VERSION . ' {');
    if ($g < 2587) {
        r('caches/*');
        @rmdir('caches');
    }
    if ($g < 2691) {
        $settings = e2_utf8_version_of_array_($settings);
        if (!@n3(USER_FOLDER . 'settings.json', json_encode($settings, E2_JSON_STYLE))) {
            @unlink(USER_FOLDER . 'updating.psa');
            mv($_strings['er--cannot-save-data'], E2E_PERMISSIONS_ERROR);
            qv();
        }
    }
    if ($g < 2921) {
        $settings['template'] = 'plain';
    }
    if ($g < 3354) {
        @rename('pictures/userpics/', AVATARS_FOLDER);
        @unlink(USER_FOLDER . 'password-reset.txt');
    }
    if ($g < 3496) {
        $settings['appearance']['respond_to_dark_mode'] = true;
    }
    @unlink(USER_FOLDER . 'last-update.psa');
    @unlink(CACHES_FOLDER . 'index.xml');
    @j(CACHES_FOLDER);
    @j(BACKUP_FOLDER);
    @j(MEDIA_ROOT_FOLDER . PICTURES_FOLDER . 'remote/');
    @j(MEDIA_ROOT_FOLDER . THUMBNAILS_FOLDER . 'remote/');
    @j(MEDIA_ROOT_FOLDER . VIDEO_FOLDER);
    if (@$settings['template'] == '') $settings['template'] = DEFAULT_TEMPLATE;
    if (isset ($settings['appearance']['hot_frontpage'])) {
        unset($settings['appearance']['hot_frontpage']);
    }
    if (!isset ($settings['blog_subtitle'])) {
        if (isset ($settings['description'])) {
            $settings['blog_subtitle'] = $settings['description'];
            unset($settings['description']);
        }
    }
    if (!isset ($settings['blog_title'])) {
        if (isset ($settings['site_title'])) {
            $settings['blog_title'] = $settings['site_title'];
            unset($settings['site_title']);
        }
    }
    if (!isset ($settings['author_email'])) {
        if (isset ($settings['user']['email'])) {
            $settings['author_email'] = $settings['user']['email'];
            unset($settings['user']);
        }
    }
    if (isset ($settings['db']['table_prefix'])) {
        if ($settings['db']['table_prefix'] != @$_config['db_table_prefix']) {
            @unlink(USER_FOLDER . 'updating.psa');
            die ('You’ve been using a database with a table prefix “' . $settings['db']['table_prefix'] . '”. Now this should be set in the configuration. Please add the following line to the file user/config.php:<br /><br />$_config[\'db_table_prefix\'] = \'' . $settings['db']['table_prefix'] . '\';<br /><br />Then refresh this page.');
        } else {
            unset($settings['db']['table_prefix']);
        }
    }
    if (isset ($settings['comments']['on'])) {
        if (!$settings['comments']['on']) {
            try {
                xn("UPDATE LOW_PRIORITY `" . $_config['db_table_prefix'] . "Notes` " . "SET `IsCommentable`=0 " . "WHERE `SubsetID`=" . $_config['db_table_subset']);
            } catch (AeMySQLException $e) {
            }
        }
        $settings['comments']['default_on'] = (bool)$settings['comments']['on'];
        unset($settings['comments']['on']);
    }
    if (isset ($settings['v3223_rss_permalinks_before_stamp'])) {
        unset($settings['v3223_rss_permalinks_before_stamp']);
    }
    if (is_file(USER_FOLDER . 'settings.json') and is_file(USER_FOLDER . 'settings.psa')) {
        @unlink(USER_FOLDER . 'settings.psa');
    }
    if (!@n3(USER_FOLDER . 'settings.json', json_encode($settings, E2_JSON_STYLE))) {
        mv($_strings['er--cannot-save-data'], E2E_PERMISSIONS_ERROR);
    }
    e2_drop_all_kinds_of_cache();
    qn();
    if ($g < 3601) {
        ja();
        $p3 = ea();
        try {
            $p3->erase();
        } catch (\S2\Rose\Exception\RuntimeException $e) {
            if (Log::$cy) __log('Rose not available');
        }
        aa();
    }
    $_diagnose['need?'] = true;
    y('diagnose', '1');
    $bk = dn(E2_VERSION);
    if (Log::$cy) __log('}');
    @unlink(USER_FOLDER . 'updating.psa');
    if (k2()) {
        mv(e2l_get_string('gs--updated-successfully', array('from' => 'v' . $g, 'to' => 'v' . $bk['version'],)), E2E_MESSAGE);
    }
    c();
}

define('E2_MYSQL_CONNECT_TIMEOUT', 5);
function kn($y7 = '', $u3 = null)
{
    static $n7 = false;
    global $settings, $_db, $cz, $_config;
    if ($n7) return;
    if (getenv('E2_DB_SERVER')) $u3['server'] = getenv('E2_DB_SERVER');
    if (getenv('E2_DB_USER_NAME')) $u3['user_name'] = getenv('E2_DB_USER_NAME');
    if (getenv('E2_DB_PASSW')) $u3['passw'] = u2(getenv('E2_DB_PASSW'));
    if (getenv('E2_DB_NAME')) $u3['name'] = getenv('E2_DB_NAME');
    if ($u3 === null) $u3 = $settings['db'];
    if ($_config['dev_chaos'] and !rand(0, (1 / $_config['dev_chaos']) - 1)) {
        throw new AeMySQLCannotConnectException ('Could not ' . $y7 . "\n\nChaos in e2_mysql_ensure");
    }
    list ($vz, $bz) = wn($u3['server']);
    if ((string)$bz === '') $bz = null;
    $yz = mysqli_init();
    $yz->options(MYSQLI_OPT_CONNECT_TIMEOUT, E2_MYSQL_CONNECT_TIMEOUT);
    $nz = @mysqli_real_connect($yz, 'p:' . $vz, $u3['user_name'], i2($u3['passw']), '', $bz);
    if (!$nz) {
        $iq = mysqli_connect_errno();
        if ($iq == 1045) {
            $nz = @mysqli_real_connect($yz, 'p:' . $vz, $u3['user_name'], $u3['passw'], '', $bz);
            if ($nz) {
                $u3['passw']    = u2($u3['passw']);
                $settings['db'] = $u3;
                if (Log::$cy) __log('Storing encrypted password');
                @n3(USER_FOLDER . 'settings.json', json_encode($settings, E2_JSON_STYLE));
            } else {
                throw new AeMySQLAccessDeniedException ('Could not ' . $y7);
            }
        } else {
            throw new AeMySQLCannotConnectException ('Could not ' . $y7);
        }
    }
    $_db['version'] = mysqli_get_server_info($yz);
    if (version_compare($_db['version'], E2_MINIMUM_MYSQL, '<')) {
        throw new AeMySQLTooOldException ('Could not ' . $y7);
    }
    if (!@mysqli_select_db($yz, $u3['name'])) {
        throw new AeMySQLNotFoundException ('Could not ' . $y7);
    }
    $_db['link']             = $yz;
    $_db['charset']          = version_compare($_db['version'], '5.5.3', '>=') ? 'utf8mb4' : 'utf8';
    $_db['innodb-fulltext?'] = (bool)version_compare($_db['version'], '5.6', '>=');
    $bf                      = 'SET NAMES ' . $_db['charset'];
    mysqli_query($yz, $bf);
    @$cz++;
    if (Log::$cy) __log('DB [' . $cz . ']: ' . $bf);
    $n7 = true;
}

function xn($bf, $y7 = 'run some query')
{
    global $cz, $_db, $_strings, $_config;
    kn($y7);
    if ($_config['dev_chaos'] and !rand(0, (1 / $_config['dev_chaos']) - 1)) {
        throw new AeMySQLQueryException ('Could not ' . $y7 . "\n\nChaos in e2_mysql_query");
    }
    @$cz++;
    if (Log::$cy) if ($y7) __log('Will ' . $y7);
    if (Log::$cy) __log('DB [' . $cz . ']: ' . $bf);
    $_db['result'] = @mysqli_query($_db['link'], $bf);
    if ($_db['result']) {
        if ($_config['backup_tail']) {
            if (stripos($bf, "SELECT") !== 0 and stripos($bf, "SHOW") !== 0) {
                $fb = BACKUP_FOLDER . 'backup-tail.sql';
                @file_put_contents($fb, $bf . ";\r\n\r\n", FILE_APPEND | LOCK_EX);
                @chmod($fb, E2_NEW_FILES_RIGHTS);
            }
        }
    } else {
        throw new AeMySQLQueryException ('Could not ' . $y7 . "\n\nMySQL says:\n" . mysqli_error($_db['link']));
    }
}

function en($type = MYSQLI_ASSOC)
{
    global $_db;
    $d = array();
    while ($nv = @mysqli_fetch_array($_db['result'], $type)) {
        foreach ($nv as $r => $m7) {
            if (is_string($m7)) {
                $nv[$r] = $m7;
            }
        }
        $d[] = $nv;
    }
    return $d;
}

function rn($x)
{
    global $_db;
    kn('escape string');
    return mysqli_real_escape_string($_db['link'], $x);
}

function tn()
{
    global $_config;
    $ua = array_keys(jn());
    if (Log::$cy) __log('Backup: Found ' . count($ua) . ' backups');
    if (count($ua)) {
        $f7 = time() - $ua[0];
        $d7 = ($f7 >= $_config['backup_rebase_interval']);
        if (Log::$cy) __log('Backup: ' . $f7 . ' seconds since last backup');
    } else {
        $d7 = true;
    }
    if ($d7) {
        if (Log::$cy) __log('Backup: Will rebuild backup');
        p3(jv('e2s_dump', []), true);
    }
}

function jn()
{
    $ua = [];
    foreach (glob(BACKUP_FOLDER . '*.sql') as $fy) {
        if (preg_match('/^backup\-(\d+)\-(\d+)\-(\d+)\-at\-(\d+)\-(\d+)\-(\d+)\.sql$/is', basename($fy), $y3)) {
            list (, $hb, $jb, $tb, $v4, $r, $c4) = $y3;
            $m4      = gmmktime($v4, $r, $c4, $jb, $tb, $hb);
            $ua[$m4] = $fy;
        }
    }
    krsort($ua);
    return $ua;
}

function hn()
{
    $ua = jn();
    if (count($ua) > 3) {
        $a7 = -1;
        $q7 = array(SECONDS_IN_A_MINUTE, SECONDS_IN_AN_HOUR, SECONDS_IN_A_DAY, -1);
        $r  = 0;
        foreach ($ua as $m4 => $fy) {
            if ($a7 == -1) {
                $a7 = $m4;
            } elseif ($q7[$r] == -1) {
                @unlink($fy);
            } else {
                if ($a7 - $m4 < $q7[$r]) {
                    @unlink($fy);
                } else {
                    $r++;
                    $a7 = $m4;
                }
            }
        }
    } else {
    }
    return;
}

function gn()
{
    global $_model, $_db, $_config;
    try {
        kn('make backup');
        if ($_db['link']) {
            $l7 = [];
            foreach (array_keys($_model) as $dz) {
                $l7[] = $_config['db_table_prefix'] . $dz;
            }
            $t4 = time();
            $fb = BACKUP_FOLDER . 'backup-' . gmdate('Y-m-d-\a\t-H-i-s', $t4) . '.sql';
            e2_backup($_db['link'], $l7, $fb);
            @unlink(BACKUP_FOLDER . 'backup-tail.sql');
            hn();
            return $fb;
        }
    } catch (AeMySQLException $e) {
        kv($e, 'Could not do backup');
        return false;
    }
}

function wn($z7)
{
    $k7 = parse_url($z7);
    $vz = @$k7['host'];
    $bz = @$k7['port'];
    if ((string)$vz === '') {
        $vz = $z7;
        $bz = '';
    }
    return [$vz, $bz];
}

function e2s_dump()
{
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        c(jv('e2m_underhood'));
    }
    if (gn()) mv('Backed up', E2E_MESSAGE);
    c(jv('e2m_underhood'));
}

define('ALIAS_MAX_LENGTH', 64);
function un($x7 = false)
{
    global $_config;
    static $m2 = null;
    if ($x7) {
        if (Log::$cy) __log('Reset aliasmap');
        @unlink(CACHE_FILENAME_ALIASMAP);
        $m2 = null;
        return;
    }
    if (is_array($m2)) return $m2;
    if (CACHE_ALIASMAP and is_file(CACHE_FILENAME_ALIASMAP)) {
        $m2 = @unserialize(file_get_contents(CACHE_FILENAME_ALIASMAP));
    }
    if (is_array($m2)) return $m2;
    if (Log::$cy) __log('Build aliasmap {');
    $m2 = [];
    xn("SELECT `EntityType`, `EntityID`, `Alias` " . "FROM `" . $_config['db_table_prefix'] . "Aliases` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `Stamp` IN (" . "SELECT MAX(`Stamp`) `MaxStamp` " . "FROM `" . $_config['db_table_prefix'] . "Aliases` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "GROUP BY `EntityType`, `EntityID`" . ")", 'get all active aliases');
    foreach (en() as $e7) {
        $f2      = $e7['EntityType'] . $e7['EntityID'];
        $m2[$f2] = $e7['Alias'];
    }
    xn("SELECT `ID`, `Stamp`, `Offset`, `IsDST`, `OriginalAlias` " . "FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished` = 1 " . "ORDER BY `Stamp`", 'get all notes to cache y/d/m/n urls');
    $r7 = 0;
    $t7 = false;
    foreach (en() as $n2) {
        $f2 = 'n' . $n2['ID'];
        $j7 = ky('Y/m/d', $n2['Stamp'], dy($n2));
        if ($j7 !== $t7) $r7 = 0;
        $r7++;
        $h7 = $j7 . '/' . $r7;
        if (empty ($m2[$f2])) {
            $m2[$f2] = $h7;
        } else {
            if ((string)$n2['OriginalAlias'] === '') {
                $m2[$f2 . '-ymdn'] = $h7;
            }
        }
        $t7 = $j7;
    }
    xn("SELECT `ID`, `OriginalAlias` " . "FROM `" . $_config['db_table_prefix'] . "Keywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'], 'get original active aliases for tags');
    foreach (en() as $q2) {
        $f2 = 't' . $q2['ID'];
        if (empty ($m2[$f2])) {
            $m2[$f2] = $q2['OriginalAlias'];
        }
    }
    if (CACHE_ALIASMAP) n3(CACHE_FILENAME_ALIASMAP, serialize($m2));
    if (Log::$cy) __log('}');
    return $m2;
}

function e2ali__alias_from_title_($g7)
{
    global $_config;
    $w7 = $g7;
    if (array_key_exists('autoreplace_for_aliases', $_config)) {
        $w7 = strtr($w7, $_config['autoreplace_for_aliases']);
    }
    $w7 = m($w7);
    $w7 = str_replace('\'', '', $w7);
    $w7 = str_replace('’', '', $w7);
    $w7 = str_replace(chr(146), '', $w7);
    $u7 = '';
    for ($r = 0; $r < strlen($w7); ++$r) {
        if ((ord($w7[$r]) >= 48 and ord($w7[$r]) <= 57) or (ord($w7[$r]) >= 65 and ord($w7[$r]) <= 90) or (ord($w7[$r]) >= 97 and ord($w7[$r]) <= 122) or 0) {
            $u7 .= $w7[$r];
        } else {
            $u7 .= '-';
        }
    }
    $u7 = preg_replace('/\-+/', '-', $u7);
    $u7 = trim($u7, '-');
    $u7 = strtolower($u7);
    if ($u7 == '-') $u7 = '';
    $u7 = substr($u7, 0, ALIAS_MAX_LENGTH);
    return $u7;
}

function on($i7)
{
    global $_config;
    if ((string)$i7 === '') return false;
    xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Aliases` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `Alias` = '" . $i7 . "' " . "ORDER BY `Stamp` LIMIT 1", 'get alias record for alias "' . $i7 . '"');
    $q1 = en();
    if (count($q1) == 1) {
        return $q1[0];
    } else {
        return false;
    }
}

function pn($i7)
{
    if ((string)$i7 === '') return false;
    if (Log::$cy) __log('Get entity type and id from alias "' . $i7 . '"');
    $o7 = @array_flip(un());
    $f2 = (string)@$o7[$i7];
    if (strlen($f2) > 0 and ($f2[0] == 'n' or $f2[0] == 't')) {
        $vv = ['type' => $f2[0], 'id' => (int)substr($f2, 1)];
        return $vv;
    }
    $e7 = on($i7);
    if (!$e7) return false;
    $vv = ['type' => $e7['EntityType'], 'id' => (int)$e7['EntityID'],];
    if (Log::$cy) __log('Got entity type "' . $vv['type'] . '" and id "' . $vv['id'] . '"');
    return $vv;
}

function cm($py, $pa, $p7, $g7, $cx = 1)
{
    if (Log::$cy) __log('Aliases: "' . $py . '" available alias for source "' . $g7 . '"');
    if ($py == 'set' and (!$pa or !$p7)) return false;
    $u7 = e2ali__alias_from_title_($g7);
    if ($g7 !== '' and $u7 === '') {
        $u7 = (string)$cx;
    } elseif ($cx > 1) {
        $vx = '-' . $cx;
        $u7 = substr($u7, 0, ALIAS_MAX_LENGTH - strlen($vx)) . $vx;
    }
    if ($e7 = on($u7)) {
        $bx = $e7['EntityType'];
        $yx = $e7['EntityID'];
        if ((($p7 and $yx == $p7) and ($pa and $bx == $pa)) or $u7 != un() [$bx . $yx]) {
            if ($py == 'find') {
                return $u7;
            }
            if ($py == 'set') {
                if (Log::$cy) __log('Aliases: update alias timestamp');
                nn('Aliases', array('ID' => $e7['ID'], 'EntityType' => $pa, 'EntityID' => $p7, 'Alias' => $u7, 'Stamp' => time(),));
                un(true);
                return $u7;
            }
        } else {
            return cm($py, $pa, $p7, $g7, $cx + 1);
        }
    } else {
        if ($pa and $p7 and $u7 == '') {
            if (preg_match('/(?P<year>\d{4})\/(?P<month>\d{1,2})\/(?P<day>\d{1,2})\/(?P<day_number>\d+)/', un() [$pa . $p7])) {
                if (Log::$cy) __log('Aliases: d/m/y/n was already used for this entity');
                return '';
            }
        }
        if (Log::$cy) __log('Aliases: it’s an empty alias, and it was not being used for this entity');
        if ($pa == 't' and $nx = af($u7)) {
            if ($nx['ID'] != $p7) {
                return cm($py, $pa, $p7, $g7, $cx + 1);
            }
        }
        if ($py == 'find') {
            return $u7;
        }
        if ($py == 'set') {
            yn('Aliases', array('EntityType' => $pa, 'EntityID' => $p7, 'Alias' => $u7, 'Stamp' => time(),));
            un(true);
            return $u7;
        }
    }
    return '';
}

class AeLayoutDiversityManager
{
    private static $layoutsUseIndexes  = [];
    private static $layoutsUseIndex    = 1;
    private static $layoutsUseMaxIndex = 1;

    public static function addLayoutsUsed($mx)
    {
        self::$layoutsUseIndexes[$mx] = self::$layoutsUseIndex;
        self::$layoutsUseIndex++;
        self::$layoutsUseMaxIndex++;
    }

    public static function hasLayoutBeenUsed($mx)
    {
        if (isset (self::$layoutsUseIndexes[$mx])) return true;
    }

    public static function getLayoutsUseRecency($mx)
    {
        if (isset (self::$layoutsUseIndexes[$mx])) {
            return self::$layoutsUseIndexes[$mx] - self::$layoutsUseMaxIndex;
        }
    }
}

class AeNoteReadCountsProvider
{
    private static $dataByNoteID = [];
    private static $hasRun       = false;
    private static $sql          = null;

    public static function setSQLRequestTemplateToMapIDsToReadCounts($sql)
    {
        self::$sql = $sql;
    }

    public static function requestDeferredReadCountForNoteID($noteID)
    {
        self::$dataByNoteID[$noteID] = true;
    }

    public static function getReadCountForNoteID($noteID)
    {
        if (self::$sql === null) return false;
        if (!self::$hasRun) self:: run();
        if (empty (self::$dataByNoteID[$noteID])) return false;
        return self::$dataByNoteID[$noteID];
    }

    private static function run()
    {
        self::$hasRun = true;
        $fx           = [];
        foreach (self::$dataByNoteID as $dx => $lv) {
            $fx[] = "(`ID` = " . $dx . ")";
        }
        if (!count($fx)) return;
        $fx = implode(' OR ', $fx);
        try {
            xn(self::$sql . " AND (" . $fx . ")", 'get all requested read counts for notes');
            $ib = en();
            foreach ($ib as $vv) {
                self::$dataByNoteID[$vv['ID']] = $vv['ReadCount'];
            }
        } catch (AeMySQLException $e) {
            kv($e);
            if (Log::$cy) __log('Could not get requested read counts for notes');
        }
    }
}

class AePageableNotesView
{
    private $candy;
    private $parameters;
    private $pageExists           = false;
    private $isCached             = false;
    private $hasRun               = false;
    private $sql                  = null;
    private $sql_count            = null;
    private $highlightedTags      = null;
    private $cacheFilename        = null;
    private $prevPageTitle        = null;
    private $nextPageTitle        = null;
    private $totalNotes           = null;
    private $totalPages           = null;
    private $notesCTree           = null;
    private $pagesCTree           = null;
    private $wantPaging           = false;
    private $wantNewCommentsCount = false;
    private $wantReadHrefs        = false;
    private $wantPreviewHrefs     = false;
    private $wantControls         = false;
    private $wantHiddenTags       = false;
    private $wantRelatedNotes     = false;
    private $useLocalHrefs        = false;
    private $page                 = 1;
    private $limit                = 10;

    public function __construct($candy, $parameters)
    {
        $this->candy      = $candy;
        $this->parameters = $parameters;
        if (empty ($parameters['page'])) {
            $this->page = 1;
        } else {
            $this->page = (int)$parameters['page'];
        }
    }

    public function setSQLCountRequest($sql_count)
    {
        if (strpos($sql_count, "SELECT COUNT(*) Total FROM ") !== 0) {
            die ('AePageableNotesView: Count request must start with "SELECT COUNT(*) Total FROM "');
        }
        $this->sql_count = $sql_count;
    }

    public function setLimitlessSQLRequest($sql)
    {
        if (strstr($sql, "LIMIT")) {
            die ('AePageableNotesView: Request must not contain "LIMIT"');
        }
        $this->sql = $sql;
        if ($this->sql_count === null) {
            if (strpos($sql, "SELECT * ") === 0) {
                $this->sql_count = "SELECT COUNT(*) Total " . substr($sql, 9);
            } else {
                die ('AePageableNotesView: setSQLCountRequest () must be used');
            }
        }
    }

    public function setPortionSize($limit)
    {
        $this->limit = abs((int)$limit);
    }

    public function setNextPrevPageTitles($nextPageTitle, $prevPageTitle)
    {
        $this->nextPageTitle = $nextPageTitle;
        $this->prevPageTitle = $prevPageTitle;
    }

    public function setHighlightedTags($highlightedTags)
    {
        $this->highlightedTags = $highlightedTags;
    }

    public function setCacheFilename($cacheFilename)
    {
        $this->isCached      = true;
        $this->cacheFilename = $cacheFilename;
    }

    public function setWantPaging($wantPaging)
    {
        $this->wantPaging = $wantPaging;
    }

    public function setWantNewCommentsCount($wantNewCommentsCount)
    {
        $this->wantNewCommentsCount = $wantNewCommentsCount;
    }

    public function setWantReadHrefs($wantReadHrefs)
    {
        $this->wantReadHrefs = $wantReadHrefs;
    }

    public function setWantPreviewHrefs($wantPreviewHrefs)
    {
        $this->wantPreviewHrefs = $wantPreviewHrefs;
    }

    public function setWantControls($wantControls)
    {
        $this->wantControls = $wantControls;
    }

    public function setWantHiddenTags($wantHiddenTags)
    {
        $this->wantHiddenTags = $wantHiddenTags;
    }

    public function setWantRelatedNotes($wantRelatedNotes)
    {
        $this->wantRelatedNotes = $wantRelatedNotes;
    }

    public function setUseLocalHrefs($useLocalHrefs)
    {
        $this->useLocalHrefs = $useLocalHrefs;
    }

    public function isExistingPage()
    {
        if (!$this->hasRun) $this->run();
        return $this->pageExists;
    }

    public function isFirstPage()
    {
        return $this->page === 1;
    }

    public function isFirstPageOfEmptyView()
    {
        if (!$this->hasRun) $this->run();
        return $this->page === 1 and $this->totalPages === 0;
    }

    public function getTotalNotes()
    {
        if (!$this->hasRun) $this->run();
        return $this->totalNotes;
    }

    public function getTotalPages()
    {
        if (!$this->hasRun) $this->run();
        return $this->totalPages;
    }

    public function getNotesCTree()
    {
        if (!$this->hasRun) $this->run();
        return $this->notesCTree;
    }

    public function getPagesCTree()
    {
        if (!$this->hasRun) $this->run();
        return $this->pagesCTree;
    }

    private function prepareCacheableData()
    {
        $this->totalNotes = 0;
        if ($this->limit) {
            $nl        = ($this->page - 1) * $this->limit;
            $this->sql .= ' LIMIT ' . $nl . ', ' . $this->limit;
        }
        xn($this->sql_count, 'count total notes of "' . $this->candy . '" list');
        $sx               = en();
        $this->totalNotes = $sx ? (int)$sx[0]['Total'] : 0;
        xn($this->sql, 'get limited full notes "' . $this->candy . '" list');
        $q1 = en();
        $ax = [];
        foreach ($q1 as $t => $cl) {
            $ax[] = $cl['ID'];
        }
        $this->notesCTree = [];
        foreach ($q1 as $t => $cl) {
            $noteView = new AeNoteView ($cl);
            $noteView->setWantNewCommentsCount($this->wantNewCommentsCount);
            $noteView->setWantReadHref($this->wantReadHrefs);
            $noteView->setWantPreviewHref($this->wantPreviewHrefs);
            $noteView->setWantControls($this->wantControls);
            $noteView->setWantHiddenTags($this->wantHiddenTags);
            $noteView->setWantCommentsLink(true);
            $noteView->setWantRelatedNotes($this->wantRelatedNotes);
            $noteView->setFilterOutRelatedNoteIDs($ax);
            $noteView->setHighlightedTags($this->highlightedTags);
            $noteView->setUseLocalHref($this->useLocalHrefs);
            $this->notesCTree[] = $noteView->getNoteCTree();
        }
        $this->pagesCTree = [];
        if ($this->limit and $this->totalPages = (int)ceil($this->totalNotes / $this->limit)) {
            $this->pagesCTree['timeline?'] = true;
            $this->pagesCTree['count']     = (int)$this->totalPages;
            $this->pagesCTree['this']      = (int)$this->page;
            if ($this->wantPaging) {
                $this->pagesCTree['earlier-title'] = $this->nextPageTitle;
                $this->pagesCTree['later-title']   = $this->prevPageTitle;
                $qx                                = $this->parameters;
                if ($this->page < $this->totalPages) {
                    $qx['page']                       = $this->page + 1;
                    $this->pagesCTree['earlier-href'] = jv($this->candy, $qx);
                }
                if ($this->page > 1) {
                    $qx['page']                     = $this->page - 1;
                    $this->pagesCTree['later-href'] = jv($this->candy, $qx);
                }
            }
        }
    }

    private function run()
    {
        $this->hasRun = true;
        if ($this->isCached and is_file($this->cacheFilename)) {
            $lx               = @unserialize(file_get_contents($this->cacheFilename));
            $this->totalNotes = @$lx['notes_total'];
            $this->notesCTree = @$lx['notes_ctree'];
            $this->pagesCTree = @$lx['pages_ctree'];
            $this->totalPages = @$this->pagesCTree['count'];
        }
        if (is_int($this->totalNotes) and is_array($this->notesCTree) and is_array($this->pagesCTree) and is_int($this->totalPages)) {
            if (Log::$cy) __log('Retrieved cached CTree');
        } else {
            $this->prepareCacheableData();
            if ($this->isCached) {
                n3($this->cacheFilename, serialize(['notes_total' => $this->totalNotes, 'notes_ctree' => $this->notesCTree, 'pages_ctree' => $this->pagesCTree,]));
            }
        }
        foreach ($this->notesCTree as $zx) {
            AeNoteReadCountsProvider:: requestDeferredReadCountForNoteID($zx['id']);
            if (empty ($zx['related']['each'])) continue;
            foreach ($zx['related']['each'] as $kx) {
                AeNoteReadCountsProvider:: requestDeferredReadCountForNoteID($kx['id']);
            }
        }
        $this->pageExists = ($this->totalPages > 0 and $this->page >= 1 and $this->page <= $this->totalPages);
    }
}

class AeArbitraryNotesCollectionView
{
    private $name                 = '';
    private $isCached             = false;
    private $hasRun               = false;
    private $sql                  = null;
    private $currentURL           = null;
    private $cacheFilename        = null;
    private $cacheExpiresFilename = null;
    private $cacheable            = [];
    private $viewExpiration       = null;
    private $notesCTree           = null;
    private $filterOutIDs         = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setSQLRequest($sql)
    {
        $this->sql = $sql;
    }

    public function setCurrentURL($currentURL)
    {
        $this->currentURL = $currentURL;
    }

    public function setCacheFilename($cacheFilename)
    {
        $this->isCached      = true;
        $this->cacheFilename = $cacheFilename;
    }

    public function setCacheExpiresFilename($cacheExpiresFilename)
    {
        $this->cacheExpiresFilename = $cacheExpiresFilename;
    }

    public function setViewExpiration($viewExpiration)
    {
        $this->viewExpiration = $viewExpiration;
    }

    public function setFilterOutIDs($filterOutIDs)
    {
        $this->filterOutIDs = $filterOutIDs;
    }

    public function orderNotesCTreeByVerticality()
    {
        if (!$this->hasRun) $this->run();
        usort($this->notesCTree, function ($xx, $ex) {
            if (empty ($xx['images'][0]['verticality'])) $rx = 0; else $rx = $xx['images'][0]['verticality'];
            if (empty ($ex['images'][0]['verticality'])) $tx = 0; else $tx = $ex['images'][0]['verticality'];
            return (int)round(($tx - $rx) * 10000);
        });
    }

    public function getNotesCTree()
    {
        if (!$this->hasRun) $this->run();
        return $this->notesCTree;
    }

    private function prepareCacheableData()
    {
        $jx = ['notes-records' => function () {
            $hx = [];
            try {
                xn($this->sql, 'get "' . $this->name . '" list');
                foreach (en() as $cl) {
                    if (rm($cl) === 'public') {
                        $hx[] = $cl;
                    }
                }
            } catch (AeMySQLException $e) {
                kv($e);
                if (Log::$cy) __log('Could not get list from database');
            }
            return $hx;
        },];
        if ($this->isCached and is_file($this->cacheFilename)) {
            $this->cacheable = @unserialize(file_get_contents($this->cacheFilename)) or $this->cacheable = [];
        }
        $gx = true;
        if (!empty ($this->cacheExpiresFilename)) {
            if ($this->isCached and is_file($this->cacheExpiresFilename)) {
                $t4 = time();
                $wx = (int)@file_get_contents($this->cacheExpiresFilename);
                if (Log::$cy) __log('List expires ' . date('r', $wx) . ', now ' . date('r', $t4));
                $gx = ($t4 < $wx);
            }
        }
        $ux = false;
        foreach ($jx as $iz => $ix) {
            if (!array_key_exists($iz, $this->cacheable) or !$gx) {
                if (Log::$cy) __log('Build cache: "' . $iz . '"');
                $this->cacheable[$iz] = $ix ();
                $ux                   = true;
            } else {
                if (Log::$cy) __log('Retrieved from cache: "' . $iz . '"');
            }
        }
        if ($this->isCached and $ux) {
            n3($this->cacheFilename, serialize($this->cacheable));
            if ($this->cacheExpiresFilename) {
                @n3($this->cacheExpiresFilename, time() + $this->viewExpiration);
            }
        }
    }

    private function run()
    {
        $this->hasRun = true;
        if (Log::$cy) __log('AeArbitraryNotesCollectionView "' . $this->name . '" run {');
        if (Log::$cy) __log('Cacheable data {');
        $this->prepareCacheableData();
        if (Log::$cy) __log('}');
        if (Log::$cy) __log('Uncacheable data {');
        $this->notesCTree = [];
        foreach ($this->cacheable['notes-records'] as $cl) {
            if (in_array($cl['ID'], $this->filterOutIDs)) continue;
            $noteView           = new AeNoteView ($cl);
            $l2                 = $noteView->getNoteCTree();
            $l2['current?']     = ($l2['href'] == $this->currentURL);
            $this->notesCTree[] = $l2;
            AeNoteReadCountsProvider:: requestDeferredReadCountForNoteID($cl['ID']);
        }
        if (Log::$cy) __log('}');
        if (Log::$cy) __log('}');
    }
}

class AeNoteView
{
    private $noteRecord              = [];
    private $isCached                = false;
    private $hasRun                  = false;
    private $cacheFilename           = null;
    private $noteCTree               = null;
    private $highlightedTags         = null;
    private $cacheable               = [];
    private $OGImages                = [];
    private $wantRichText            = false;
    private $wantCommentsLink        = false;
    private $wantNewCommentsCount    = false;
    private $wantReadHref            = false;
    private $wantPreviewHref         = false;
    private $wantControls            = false;
    private $wantHiddenTags          = false;
    private $wantSharingButtons      = false;
    private $wantRelatedNotes        = false;
    private $filterOutRelatedNoteIDs = [];
    private $useLocalHref            = false;

    public function __construct($noteRecord)
    {
        $this->noteRecord = $noteRecord;
        if (CACHE_NOTES) {
            $this->isCached      = true;
            $this->cacheFilename = e2_note_cache_filename_with_id_($noteRecord['ID']);
        }
    }

    public function setHighlightedTags($highlightedTags)
    {
        $this->highlightedTags = $highlightedTags;
    }

    public function setWantRichText($wantRichText)
    {
        $this->wantRichText = $wantRichText;
    }

    public function setWantCommentsLink($wantCommentsLink)
    {
        $this->wantCommentsLink = $wantCommentsLink;
    }

    public function setWantNewCommentsCount($wantNewCommentsCount)
    {
        $this->wantNewCommentsCount = $wantNewCommentsCount;
    }

    public function setWantReadHref($wantReadHref)
    {
        $this->wantReadHref = $wantReadHref;
    }

    public function setWantPreviewHref($wantPreviewHref)
    {
        $this->wantPreviewHref = $wantPreviewHref;
    }

    public function setWantControls($wantControls)
    {
        $this->wantControls = $wantControls;
    }

    public function setWantHiddenTags($wantHiddenTags)
    {
        $this->wantHiddenTags = $wantHiddenTags;
    }

    public function setWantSharingButtons($wantSharingButtons)
    {
        $this->wantSharingButtons = $wantSharingButtons;
    }

    public function setWantRelatedNotes($wantRelatedNotes)
    {
        $this->wantRelatedNotes = $wantRelatedNotes;
    }

    public function setFilterOutRelatedNoteIDs($filterOutRelatedNoteIDs)
    {
        $this->filterOutRelatedNoteIDs = $filterOutRelatedNoteIDs;
    }

    public function setUseLocalHref($useLocalHref)
    {
        $this->useLocalHref = $useLocalHref;
    }

    public function getNoteCTree()
    {
        if (!$this->hasRun) $this->run();
        return $this->noteCTree;
    }

    private function prepareCacheableData()
    {
        $jx = ['title'        => function () {
            return h3(htmlspecialchars($this->noteRecord['Title'], ENT_NOQUOTES, HSC_ENC));
        }, 'text-format-info' => function () {
            return u3($this->noteRecord['FormatterID'], $this->noteRecord['Text'], 'full');
        }, 'summary'          => function () {
            if ((string)$this->noteRecord['Summary'] !== '') {
                return h3(htmlspecialchars($this->noteRecord['Summary'], ENT_NOQUOTES, HSC_ENC));
            } else {
                return em($this->cacheable['text-format-info']['text-final']);
            }
        }, 'comments-count'   => function () {
            if (!$this->noteRecord['IsPublished']) {
                return false;
            } else {
                return pf($this->noteRecord['ID']);
            }
        }, 'tags-data'        => function () {
            $s2                         = bf($this->noteRecord['ID']);
            $ox['ctree']                = [];
            $ox['all-resnames-uploads'] = [];
            foreach ($s2 as $r => $q2) {
                $ox['ctree'][]              = ['visible?' => (bool)$q2['IsVisible'], 'name' => htmlspecialchars($q2['Keyword'], ENT_NOQUOTES, HSC_ENC), 'href' => jv('e2m_tag', array('*tag' => $q2)),];
                $ox['all-resnames-uploads'] = array_merge($ox['all-resnames-uploads'], q3('tag', $q2['ID']));
            }
            $ox['all-resnames-uploads'] = array_unique($ox['all-resnames-uploads']);
            return $ox;
        },];
        if ($this->isCached and is_file($this->cacheFilename)) {
            $this->cacheable = @unserialize(file_get_contents($this->cacheFilename)) or $this->cacheable = [];
        }
        $ux = false;
        foreach ($jx as $iz => $ix) {
            if (!array_key_exists($iz, $this->cacheable)) {
                if (Log::$cy) __log('Build cache: "' . $iz . '"');
                $this->cacheable[$iz] = $ix ();
                $ux                   = true;
            } else {
                if (Log::$cy) __log('Retrieved from cache: "' . $iz . '"');
            }
        }
        if ($this->isCached and $ux) {
            n3($this->cacheFilename, serialize($this->cacheable));
        }
    }

    private function run()
    {
        $this->hasRun = true;
        if (Log::$cy) __log('AeNoteView run {');
        if (Log::$cy) __log('Cacheable data {');
        $this->prepareCacheableData();
        if (Log::$cy) __log('}');
        if (Log::$cy) __log('Uncacheable data {');
        $px = false;
        if ($this->noteRecord['IsPublished']) {
            if ((string)$this->noteRecord['OriginalAlias'] !== '') {
                $px = jv('e2m_note', ['alias' => $this->noteRecord['OriginalAlias']]);
            } else {
                $ce                 = $this->noteRecord;
                $ce['__force_ymdn'] = true;
                $px                 = jv('e2m_note', ['*note' => $ce]);
            }
        }
        $zl = dy($this->noteRecord);
        $ve = [(int)$this->noteRecord['LastModified'], $zl];
        $t4 = ($this->noteRecord['IsPublished'] ? [(int)$this->noteRecord['Stamp'], $zl] : $ve);
        $be = rm($this->noteRecord);
        $gs = @$this->cacheable['text-format-info']['meta']['resources-detected'];
        if (!is_array($gs)) $gs = [];
        if (count($gs)) {
            rb($gs);
        }
        $ye = db($gs);
        $ws = @unserialize($this->noteRecord['Uploads']) or $ws = [];
        $ne = array_merge(sb($gs, $ws), $this->cacheable['tags-data']['all-resnames-uploads']);
        $me = d3($ne);
        $fe = db($ne);
        $de = $this->noteRecord['SourceNoteData'];
        $de = @json_decode($de, true);
        $se = @$de['og_images'][0] or $se = '';
        if ($this->noteRecord['IsExternal']) {
            $ae = ad($this->noteRecord);
        } else {
            $ae = [];
        }
        $qe = false;
        $le = $this->cacheable['tags-data']['ctree'];
        foreach ($le as $t => $xf) {
            if ($this->highlightedTags !== null) {
                $le[$t]['current?'] = in_array($le[$t]['name'], $this->highlightedTags);
            }
            if (!$this->wantHiddenTags and !$le[$t]['visible?']) {
                unset ($le[$t]);
            }
        }
        if ($this->wantSharingButtons and $be === 'public') {
            $ze = vm($me);
        } else {
            $ze = false;
        }
        if ($this->wantNewCommentsCount) {
            $ke = of($this->noteRecord['ID']);
        } else {
            $ke = false;
        }
        $this->noteCTree = ['id' => (int)$this->noteRecord['ID'], 'title' => (string)$this->cacheable['title'], 'href' => jv('e2m_note', ['*note' => $this->noteRecord]), 'href-original' => $px, 'time' => $t4, 'last-modified' => $ve, 'text' => (string)$this->cacheable['text-format-info']['text-final'], 'format-info' => $this->cacheable['text-format-info']['meta'], 'summary' => (string)$this->cacheable['summary'], 'snippet-text' => (string)$this->cacheable['summary'], 'draft?' => $be === 'draft', 'scheduled?' => $be === 'scheduled', 'public?' => $be === 'public', 'hidden?' => $be === 'hidden', 'current?' => false, 'favourite?' => (bool)($this->noteRecord['IsFavourite'] and $be !== 'draft'), 'images' => ab($ye), 'thumbs' => qb($ye), 'source-main-image-url' => (string)$se, 'og-images' => $me, 'og-images-thumbs' => qb($fe), 'tags' => $le, 'sharing-buttons' => $ze, 'related' => $qe, 'read-href' => ($this->wantReadHref and $this->noteRecord['IsPublished']) ? jv('e2m_note_read', ['*note' => $this->noteRecord]) : false, 'preview-href' => ($this->wantPreviewHref and ($be !== 'public')) ? jv('e2m_note', ['*note' => $this->noteRecord, 'preview-key' => jm($this->noteRecord)]) : false, 'comments-count' => $this->cacheable['comments-count'], 'comments-count-text' => is_int($this->cacheable['comments-count']) ? e2l_get_string('gs--n-comments', ['number' => $this->cacheable['comments-count']]) : '', 'new-comments-count' => $ke, 'new-comments-count-text' => is_int($ke) ? e2l_get_string('gs--comments-n-new', ['number' => $ke]) : '', 'comments-link?' => (bool)($this->wantCommentsLink and $this->noteRecord['IsPublished'] and (n2($this->noteRecord) or ($this->cacheable['comments-count'] > 0))),];
        if ($this->noteRecord['IsExternal']) {
            $this->noteCTree                  = array_merge($this->noteCTree, $ae);
            $this->noteCTree['href-original'] = $this->noteCTree['href-external'];
            if (!$this->useLocalHref) {
                $this->noteCTree['href'] = $this->noteCTree['href-external'];
            }
        }
        if ($this->wantControls) {
            $this->noteCTree['edit-href'] = jv('e2m_note_edit', array('*note' => $this->noteRecord));
            if ($this->noteRecord['IsPublished'] and !$this->noteRecord['IsVisible']) {
                $this->noteCTree['show-href'] = jv('e2m_note_flag', ['*note' => $this->noteRecord, 'flag' => 'IsVisible', 'value' => 1]);
            }
            if ($this->noteRecord['IsPublished']) {
                $this->noteCTree['favourite-toggle-href'] = jv('e2m_note_flag_favourite', ['*note' => $this->noteRecord, 'value' => !$this->noteRecord['IsFavourite']]);
            }
        }
        $this->noteCTree['href-comments'] = $this->noteCTree['href'] . '#comments';
        if (Log::$cy) __log('}');
        AeNoteReadCountsProvider:: requestDeferredReadCountForNoteID($this->noteRecord['ID']);
        if (Log::$cy) __log('}');
    }
}

function vm($me)
{
    global $_config;
    $xe = $_config['share_to'];
    $ee = '|twitter|facebook|vkontakte|telegram|linkedin|whatsapp|';
    if (@$_config['share_to_twitter_via']) {
        $lv['twitter']['via'] = $_config['share_to_twitter_via'];
    }
    if (count($me) > 0) {
        $re                       = $me[0];
        $ee                       .= 'pinterest|';
        $lv['pinterest']['media'] = $re;
    }
    $te = [];
    foreach (explode(',', $xe) as $je) {
        $je = trim($je);
        if (strstr($ee, '|' . $je . '|')) {
            $te[$je]['share?'] = true;
            if (is_array(@$lv[$je]) and count($lv[$je])) {
                $te[$je]['data'] = $lv[$je];
            }
        }
    }
    return $te;
}

function e2m_note($parameters = [])
{
    global $settings, $_config, $_strings;
    if (Log::$cy) __log('Note {');
    $n2 = $parameters['*note'];
    if ($n2 == false) return e2_error404_mode();
    $he = jm($n2);
    $ge = rm($n2);
    $we = k2();
    $ue = ($parameters['preview-key'] == $he);
    if (!empty ($parameters['preview-key']) and !$ue) return e2_error404_mode();
    if (!$we and !$ue and $ge !== 'public') return e2_error404_mode();
    if (!empty ($parameters['preview-key']) and $ge === 'public') {
        unset($parameters['preview-key']);
        $ie = jv('e2m_note', $parameters);
        c($ie);
    }
    $ie       = jv('e2m_note', $parameters);
    $noteView = new AeNoteView ($n2);
    $noteView->setWantReadHref($_config['count_reads']);
    $noteView->setWantControls($we and !@$_config['read_only']);
    $noteView->setWantHiddenTags($we);
    if ($ge === 'draft' or $ge === 'scheduled') {
        if (!$ue) {
            $oe = ['.note-id' => $n2['ID'], 'form-action' => jv('e2s_note_publish'), 'submit-text' => $_strings['fb--publish-note'], 'can-schedule?' => false, 'can-publish?' => !@$_config['read_only'],];
        }
    }
    $pe = '';
    $c6 = [];
    $v6 = [];
    if ($ge === 'public') {
        $noteView->setWantNewCommentsCount($we);
        $noteView->setWantSharingButtons($settings['appearance']['show_sharing_buttons']);
        $noteView->setWantRelatedNotes(true);
    }
    if ($ge === 'public' or $ge === 'hidden') {
        if (Log::$cy) __log('Navigation {');
        $b6 = fm($n2, 'prev');
        $y6 = fm($n2, 'next');
        if ($b6) {
            $v6['prev-href']  = jv('e2m_note', array('*note' => $b6));
            $v6['prev-title'] = h3(htmlspecialchars($b6['Title'], ENT_NOQUOTES, HSC_ENC));
        }
        if ($y6) {
            $v6['next-href']  = jv('e2m_note', array('*note' => $y6));
            $v6['next-title'] = h3(htmlspecialchars($y6['Title'], ENT_NOQUOTES, HSC_ENC));
        }
        $v6['title']      = $_strings['nm--posts'];
        $v6['timeline?']  = false;
        $v6['this-title'] = h3(htmlspecialchars($n2['Title'], ENT_NOQUOTES, HSC_ENC));
        if (Log::$cy) __log('}');
        if (Log::$cy) __log('Comments {');
        if ($we) {
            $n6 = e2_note_cache_filename_with_id_($n2['ID'] . '-comments-author');
        } else {
            $n6 = e2_note_cache_filename_with_id_($n2['ID'] . '-comments');
        }
        $m6 = null;
        if (CACHE_NOTES_COMMENTS and is_file($n6)) {
            $m6 = @unserialize(file_get_contents($n6));
        }
        if (is_array($m6)) {
            if (Log::$cy) __log('retrieve cached ctree');
            $pe = $m6;
        } else {
            if (Log::$cy) __log('assemble ctree...');
            $f6 = b2($n2['ID']);
            $d6 = array();
            $s6 = true;
            foreach ($f6 as $t => $a6) {
                if ($a6['IsVisible']) {
                    $q6 = wf($n2, $a6, $t + 1);
                    if ($q6['new?'] and $s6) {
                        $q6['first-new?'] = true;
                        $s6               = false;
                    }
                    $d6[] = $q6;
                }
            }
            $pe = $d6;
            if (CACHE_NOTES_COMMENTS) n3($n6, serialize($pe));
        }
        if (!@$_config['read_only'] and n2($n2)) {
            $l6                    = if_($n2);
            $l6['.comment-number'] = count($pe) + 1;
        }
        if (Log::$cy) __log('} // Comments');
    }
    $zx = $noteView->getNoteCTree();
    if ($we and n2($n2, NOTE_COMMENTABLE_NOW_CONDITIONALLY)) {
        if ($n2['IsCommentable']) {
            $c6['href'] = jv('e2m_note_flag', array('*note' => $n2, 'flag' => 'IsCommentable', 'value' => 0,));
            $c6['text'] = $_strings['bt--close-comments-to-post'];
        } else {
            $c6['href'] = jv('e2m_note_flag', array('*note' => $n2, 'flag' => 'IsCommentable', 'value' => 1,));
            $c6['text'] = $_strings['bt--open-comments-to-post'];
        }
    }
    if ($we and $zx['new-comments-count'] > 0) {
        if (Log::$cy) __log('mark comments as not new');
        e2_drop_caches_for_note_($n2['ID'], true);
        nn('Comments', array('IsNew' => 0), array('NoteID' => $n2['ID']));
    }
    if ($we and ($n2['FormatterID'] == 'calliope')) {
        if (is_file(USER_FOLDER . 'calliope/WikiFormatter.php')) {
            $zx['text'] = w3() . $zx['text'];
        }
    }
    $d = ['title' => $n2['Title'], 'notes' => ['only' => $zx], 'pages' => $v6, 'summary' => $zx['summary'],];
    if ($pe) $d['comments']['each'] = $pe;
    if ($c6) $d['comments']['toggle'] = $c6;
    $d['comments']['count']          = $zx['comments-count'];
    $d['comments']['count-text']     = $zx['comments-count-text'];
    $d['comments']['new-count']      = $zx['new-comments-count'];
    $d['comments']['new-count-text'] = $zx['new-comments-count-text'];
    $d['comments']['display-form?']  = n2($n2);
    if (!empty ($l6)) {
        $d['form']         = 'form-comment';
        $d['form-comment'] = $l6;
    }
    if (!empty ($oe)) {
        $d['form']              = 'form-note-publish';
        $d['form-note-publish'] = $oe;
    }
    if (Log::$cy) __log('} // Note');
    return $d;
}

function e2m_note_read($parameters = array())
{
    global $_config;
    if (!$_config['count_reads']) {
        die ('Read counting disabled');
    }
    $n2 = $parameters['*note'];
    if ($n2 == false) return e2_error404_mode();
    if (Log::$cy) __log('Note read {');
    xn("UPDATE LOW_PRIORITY `" . $_config['db_table_prefix'] . "Notes` " . "SET `ReadCount` = `ReadCount` + 1 " . "WHERE `ID` = " . $n2['ID']);
    $z6 = time();
    $z6 = $z6 - ($z6 % SECONDS_IN_AN_HOUR);
    yn('Actions', ['EntityID' => $n2['ID'], 'Stamp' => $z6, 'ReadCount' => 1,], 'INSERT LOW_PRIORITY', 'ON DUPLICATE KEY UPDATE `ReadCount` = `ReadCount` + 1');
    xn("DELETE LOW_PRIORITY FROM `" . $_config['db_table_prefix'] . "Actions` " . "WHERE (`Stamp` < " . (time() - (SECONDS_IN_A_MONTH)) . ")");
    if (Log::$cy) __log('}');
    c(jv('e2m_note', $parameters));
}

function e2m_note_withdraw($parameters = array())
{
    global $_strings;
    $cl = $parameters['*note'];
    if (!$cl) return e2_error404_mode();
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        c(jv('e2m_note', array('*note' => $cl)));
    }
    $k6                  = jv('e2m_note_broadcast', array('*note' => $cl));
    $cl['IsPublished']   = 0;
    $cl['IsCommentable'] = 0;
    $cl['IsVisible']     = 1;
    $cl['Stamp']         = time();
    $cl['IP']            = q2();
    if ($parameters['alias']) {
        $cl['OriginalAlias'] = $parameters['alias'];
    } else {
        $cl['OriginalAlias'] = cm('find', 'n', $cl['ID'], $cl['Title']);
    }
    e2_drop_caches_for_note_($cl['ID'], null);
    nn('Notes', $cl);
    za($cl['ID']);
    p3($k6);
    cm('set', 'n', $cl['ID'], '');
    c(jv('e2m_note', ['*note' => $cl]));
}

function e2m_note_delete($parameters = array())
{
    global $_strings;
    $cl = $parameters['*note'];
    if (!$cl) return e2_error404_mode();
    $ge = rm($cl);
    $x6 = !$cl['IsPublished'];
    if ($x6) {
        $e6 = e2l_get_string('gs--draft-will-be-deleted', array('draft' => htmlspecialchars($cl['Title'], ENT_NOQUOTES, HSC_ENC),));
    } else {
        $e6 = e2l_get_string('gs--post-will-be-deleted', array('post' => htmlspecialchars($cl['Title'], ENT_NOQUOTES, HSC_ENC),));
    }
    $r6 = $x6 ? $_strings['pt--draft-deletion'] : $_strings['pt--post-deletion'];
    $t6 = array('.note-id' => $cl['ID'], '.is-draft' => (int)$x6, 'note-title' => htmlspecialchars($cl['Title'], ENT_COMPAT, HSC_ENC), 'caution-text' => $e6, 'form-action' => jv('e2s_note_delete'), 'submit-text' => $_strings['fb--delete'], 'draft?' => (int)$x6,);
    if ($ge === 'public') {
        $t6['hide-href'] = jv('e2m_note_flag', ['*note' => $parameters['*note'], 'flag' => 'IsVisible', 'value' => 0]);
    }
    if ($cl['IsPublished']) {
        $t6['withdraw-href'] = jv('e2m_note_withdraw', $parameters);
    }
    $d = array('title' => $r6 . ': ' . htmlspecialchars($cl['Title'], ENT_NOQUOTES, HSC_ENC), 'heading' => $r6, 'form' => 'form-note-delete', 'form-note-delete' => $t6,);
    return $d;
}

function e2m_note_flag_favourite($parameters)
{
    global $_config;
    $parameters['flag'] = 'IsFavourite';
    s(['flag-name' => 'favourite', 'candy-name' => 'e2m_note_flag_favourite', 'parameters' => $parameters, 'flipping-function' => function () use ($parameters) {
        bm($parameters);
    },]);
}

function e2m_note_flag($parameters)
{
    bm($parameters);
    c(jv('e2m_note', $parameters));
}

function bm($parameters)
{
    $dx = $parameters['*note']['ID'];
    if (!is_numeric($dx)) {
        return e2_error404_mode();
    }
    e2_drop_caches_for_note_($dx, $parameters['*note']['IsPublished']);
    if ($parameters['flag'] == 'IsVisible') {
        ds();
    }
    nn('Notes', array('ID' => $dx, $parameters['flag'] => (int)($parameters['value'] == 1),));
    try {
        cy(mm($dx));
    } catch (AeMySQLException $e) {
        kv($e, 'Could not broadcast note flag change');
    }
    return true;
}

function e2m_note_use_formatter($parameters)
{
    $dx = $parameters['*note']['ID'];
    if (!is_numeric($dx)) {
        return e2_error404_mode();
    }
    e2_drop_caches_for_note_($dx, $parameters['*note']['IsPublished']);
    if (in_array($parameters['formatter'], array('calliope', 'raw', 'neasden'))) {
        nn('Notes', array('ID' => $dx, 'FormatterID' => $parameters['formatter'],));
        echo 'formatter set to ' . $parameters['formatter'];
    } else {
        echo 'unknown formatter';
    }
    die;
}

function ym($j6, $parameters = array())
{
    global $full_blog_url, $_strings, $_config;
    $r6 = $_strings['pt--new-post'];
    $h6 = $_strings['pt--new-post'];
    $dx = 'new';
    $g6 = $_config['default_formatter'];
    if ($j6 == 'write') {
        $m4 = time();
        $w6 = time();
        $zl = ay();
        $ge = 'draft';
        $i7 = $u6 = '';
    }
    if ($j6 == 'edit') {
        $cl = $parameters['*note'];
        if (!$cl) return e2_error404_mode();
        $m4 = min($cl['Stamp'], time());
        $w6 = (int)$cl['LastModified'];
        $zl = dy($cl);
        $ge = rm($cl);
        if ($cl['IsPublished']) {
            $h6 = $_strings['pt--edit-post'];
            $u6 = '';
            $i7 = $parameters['alias'];
        } else {
            $h6 = $_strings['pt--edit-draft'];
            $u6 = cm('find', 'n', $cl['ID'], $cl['Title']);
            if (@$cl['OriginalAlias']) {
                $i7 = $cl['OriginalAlias'];
            } else {
                $i7 = $u6;
            }
        }
        $dx = $cl['ID'];
        $g6 = $cl['FormatterID'];
        $r6 = $cl['Title'];
    }
    $i6 = df();
    $o6 = array();
    if ($i6 !== null) foreach ($i6 as $p6) {
        $o6[] = $p6['tag'];
    }
    $cr = array();
    if ($j6 == 'edit' and count($o6)) {
        $i6 = bf($cl['ID']);
        foreach ($i6 as $p6) {
            $cr[] = htmlspecialchars($p6['Keyword'], ENT_NOQUOTES, HSC_ENC);
        }
    }
    $vr = array();
    foreach ($o6 as $p6) {
        $br['name']      = $p6;
        $br['selected?'] = in_array($p6, $cr);
        $vr[]            = $br;
    }
    $yr = '';
    $cr = implode(', ', $cr);
    if ($cr) $yr = $cr;
    if ($j6 == 'write') {
        $nr = $_strings['fb--save-and-preview'];
    }
    if ($j6 == 'edit') {
        if (array_key_exists('draft', $parameters)) {
            $nr = $_strings['fb--save-and-preview'];
        } else {
            $nr = $_strings['fb--save-changes'];
        }
    }
    $gs = [];
    if ($j6 == 'edit') {
        $gs = g3($cl['FormatterID'], $cl['Text'], 'full');
    }
    $ws = @unserialize($cl['Uploads']) or $ws = [];
    $mr = qb(fb(sb($gs, $ws)));
    if ($j6 == 'edit') {
        k3('Notes', $cl, $gs);
    }
    $qq             = r3();
    $lq             = t3($qq);
    $d['title']     = $r6;
    $d['heading']   = $h6;
    $d['form']      = 'form-note';
    $d['uploads']   = ['enabled?' => $lq, 'each' => $mr, 'default-name' => htmlspecialchars($i7, ENT_COMPAT, HSC_ENC), 'upload-action' => jv('e2j_file_upload'), 'remove-action' => jv('e2j_file_remove'),];
    $d['form-note'] = ['.note-id' => $dx, '.formatter-id' => $g6, '.last-modified-stamp' => $w6, '.published?' => (bool)@$cl['IsPublished'], '.old-tags-hash' => md5($yr), '.action' => $j6, 'form-action' => jv('e2s_note_process'), 'form-note-livesave-action' => jv('e2j_note_livesave'), 'create:edit?' => (bool)($j6 == 'write'), 'title' => htmlspecialchars(@$cl['Title'], ENT_COMPAT, HSC_ENC), 'tags' => $yr, 'tags-info' => $vr, 'text' => htmlspecialchars(@$cl['Text'], ENT_NOQUOTES, HSC_ENC), 'all-tags' => $o6, 'stamp-formatted' => ky('d.m.Y H:i:s', $m4, $zl), 'time' => @$cl['IsPublished'] ? [(int)$m4, $zl] : false, 'draft?' => $ge === 'draft', 'uploads-enabled?' => $lq, 'summary' => (string)@$cl['Summary'], 'alias-autogenerated' => htmlspecialchars($u6, ENT_COMPAT, HSC_ENC), 'alias' => htmlspecialchars($i7, ENT_COMPAT, HSC_ENC), 'submit-text' => $nr, 'space-usage' => j3($qq),];
    if ($j6 == 'edit') {
        $d['related-delete-href'] = jv('e2m_note_delete', array('*note' => $cl));
    }
    return $d;
}

function e2m_note_edit($parameters = array())
{
    return ym('edit', $parameters);
}

function e2m_write()
{
    return ym('write');
}

function e2s_note_process()
{
    global $_fp_error, $_strings;
    $dx = km();
    if (!$dx) {
        if ($_fp_error == FP_TITLE_OR_TEXT_EMPTY) {
            mv($_strings['er--post-must-have-title-and-text'], E2E_USER_ERROR);
        } elseif ($_fp_error == FP_NO_ID_OR_NEW) {
        } else {
            mv($_strings['er--error-occurred']);
        }
        c(jv('e2m_write'));
    }
    try {
        $n2 = mm($dx);
        c(jv('e2m_note', ['*note' => $n2]));
    } catch (AeMySQLException $e) {
        kv($e, 'Could not get note by ID');
        c();
    }
    die;
}

function e2s_note_publish()
{
    global $_strings, $_config, $settings;
    $dx = false;
    if (array_key_exists('note-id', $_POST)) {
        $dx                  = $_POST['note-id'];
        $fr                  = false;
        $cl                  = mm($dx);
        $dr                  = $cl['OriginalAlias'];
        $sr                  = $cl['Stamp'];
        $ar                  = !$cl['IsExternal'];
        $cl['ID']            = $dx;
        $cl['IsVisible']     = 1;
        $cl['IsPublished']   = 1;
        $cl['IsCommentable'] = (int)$settings['comments']['default_on'];
        $cl['IsFavourite']   = 0;
        if (array_key_exists('browser-offset', $_POST)) {
            $zl = wy(@$_POST['browser-offset']);
        } else {
            $zl = ay();
        }
        if ($fr and $m4 = zm($fr, $zl)) {
            $cl['Stamp'] = $m4;
        } elseif ($ar) {
            $cl['Stamp'] = time();
        } else {
            $cl['Stamp'] = $sr;
        }
        if (la($cl)) {
            $cl['IsIndexed'] = '1';
        }
        if ($zl) {
            $cl['Offset'] = (int)$zl['offset'];
            $cl['IsDST']  = (int)$zl['is_dst'];
        }
        e2_drop_caches_for_note_($dx, null);
        nn('Notes', $cl);
        $i7 = '';
        if ($dr or $dr === '0') {
            $i7                  = cm('set', 'n', $dx, $dr);
            $cl['OriginalAlias'] = $i7;
        }
        if ($i7 != $dr) {
            nn('Notes', $cl);
        }
        $ge = rm($cl);
        if ($ge === 'public') {
            cy($cl);
        }
        c(jv('e2m_note', array('*note' => $cl)));
    }
    c();
}

function nm($dx, $qr = -1)
{
    global $_config;
    $lr = true;
    if ($qr) {
        $lr = false;
    }
    if ($qr === -1) {
        $lr = null;
    }
    e2_drop_caches_for_note_($dx, $lr);
    xn("DELETE FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID` = '" . ((int)$dx) . "'", 'delete note by ID');
    za($dx);
    xn("DELETE FROM `" . $_config['db_table_prefix'] . "Aliases` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `EntityType` = 'n' " . "AND `EntityID`=" . ((int)$dx), 'delete aliases after deleting note');
    xn("DELETE FROM `" . $_config['db_table_prefix'] . "NotesKeywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `NoteID`=" . ((int)$dx), 'delete tag bindings after deleting note');
}

function e2s_note_delete()
{
    global $_strings, $_config;
    $dx = $_POST['note-id'];
    $qr = (bool)$_POST['is-draft'];
    $cl = mm($dx);
    $k6 = jv('e2m_note_broadcast', array('*note' => $cl));
    nm($dx, $qr);
    p3($k6);
    if ($qr) {
        c(jv('e2m_drafts', ['page' => 1]));
    } else {
        c();
    }
    die;
}

function e2j_note_livesave()
{
    die (km('ajaxresult'));
}

function mm($xs)
{
    global $_config;
    xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID` = '" . $xs . "'");
    $ib = en();
    if (count($ib) > 0) {
        return $ib[0];
    } else {
        return false;
    }
}

function fm($n2, $zr, $kr = 1)
{
    global $_strings, $_config;
    $xr = ($zr == 'next') ? '>' : '<';
    $er = ($zr == 'next') ? '' : 'DESC ';
    try {
        xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=" . $kr . " " . "AND (" . "`Stamp` " . $xr . " '" . $n2['Stamp'] . "' " . "OR (`Stamp` = '" . $n2['Stamp'] . "' AND `ID` " . $xr . $n2['ID'] . ")" . ") " . tm(k2()) . "ORDER BY `Stamp` " . $er . ", `ID` " . $er . "LIMIT 1", 'get ' . $zr . ' note');
        $ib = en();
        if (count($ib) > 0) return $ib[0]; else return false;
    } catch (AeMySQLException $e) {
        kv($e, 'Could not get ' . $zr . ' note');
        return null;
    }
}

function dm($rr)
{
    global $_config;
    if (Log::$cy) __log('Lastmodifieds for Local Copier');
    if (CACHE_LASTMODIFIEDS and is_file(CACHE_FILENAME_LASTMODIFIEDS)) {
        $tr = @unserialize(file_get_contents(CACHE_FILENAME_LASTMODIFIEDS));
        if ($tr['ids_csv'] == $rr) {
            if (Log::$cy) __log('Returned from cache');
            return $tr['lastmodifieds_json'];
        }
    }
    $b  = '`ID`=' . str_replace(',', ' OR `ID`=', $rr);
    $jr = array();
    xn("SELECT `ID`, `LastModified` " . "FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND (" . $b . ")", 'get lastmodifieds for Local Copier');
    if (Log::$cy) __log('Requested from DB');
    $q1 = en();
    foreach ($q1 as $t => $xf) {
        $jr[(int)$xf['ID']] = (int)$xf['LastModified'];
    }
    $hr = json_encode($jr);
    if ($hr == '[]') $hr = '{}';
    $tr = array('ids_csv' => $rr, 'lastmodifieds_json' => $hr,);
    if (CACHE_LASTMODIFIEDS) {
        n3(CACHE_FILENAME_LASTMODIFIEDS, serialize($tr));
    }
    return $hr;
}

function sm($hb, $jb, $tb = false)
{
    global $_config;
    list ($wr, $ur) = jy($hb, $jb, $tb);
    xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished` AND (`Stamp` BETWEEN " . $wr . " AND " . $ur . ") " . "ORDER BY Stamp", 'get all notes for the date ' . $tb . '.' . $jb . '.' . $hb);
    $d = [];
    foreach (en() as $ir) {
        if (is_numeric($tb)) {
            $or_ = ((int)$hb) . '/' . ((int)$jb) . '/' . ((int)$tb) == ky('Y/n/j', $ir['Stamp'], dy($ir));
        } elseif (is_numeric($jb)) {
            $or_ = ((int)$hb) . '/' . ((int)$jb) == ky('Y/n', $ir['Stamp'], dy($ir));
        } else {
            $or_ = ((int)$hb) == ky('Y', $ir['Stamp'], dy($ir));
        }
        if ($or_) $d[] = $ir;
    }
    return $d;
}

function e2_published_noterec_with_parameters_($parameters = array())
{
    $n2 = e2_noterec_with_parameters_($parameters);
    if ($n2['IsPublished']) return $n2;
}

function e2_noterec_with_parameters_($parameters = array())
{
    global $_config;
    $n2 = false;
    $pr = false;
    if ((string)@$parameters['oalias'] !== '') $pr = $parameters['oalias'];
    if ((string)$pr !== '') {
        xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `OriginalAlias` = '" . $pr . "' " . "AND `IsPublished` = 0", 'get note record by original alias');
        $n2 = en();
        if (count($n2) === 1) {
            $n2 = @$n2[0];
            if ($n2) return $n2;
        }
    }
    $ct = false;
    if (@$parameters['draft'] !== '') $ct = @$parameters['draft'];
    if (@$parameters['draft2'] !== '') $ct = @$parameters['draft2'];
    if ($ct) {
        $n2 = mm($ct);
        return $n2;
    }
    if ((string)$pr !== '') {
        $parameters['alias'] = $pr;
    }
    if ((string)@$parameters['alias'] !== '') {
        if ($vt = pn(@$parameters['alias'])) {
            if ($vt['type'] == 'n') {
                $n2 = mm($vt['id']);
                if ($n2['IsPublished']) return $n2;
            }
        }
    }
    $bt = sm($parameters['year'], $parameters['month'], $parameters['day']);
    if (@$bt[$parameters['day-number'] - 1]) {
        return $bt[$parameters['day-number'] - 1];
    }
}

function lm($r6, $tv, $zl, $yt)
{
    global $_config;
    ss();
    @unlink(CACHE_FILENAME_DRAFTS);
    @unlink(CACHE_FILENAME_DRAFTS_ALIAS_USE_COUNTS);
    $n2 = array('Title' => $r6, 'Text' => $tv, 'FormatterID' => $_config['default_formatter'], 'OriginalAlias' => cm('find', '', '', $r6), 'Uploads' => $yt, 'Stamp' => (int)time(), 'LastModified' => (int)time(),);
    if ($zl and is_array($zl)) {
        $n2['Offset'] = (int)$zl['offset'];
        $n2['IsDST']  = (int)$zl['is_dst'];
    }
    $n2 = yn('Notes', $n2);
    return $n2['ID'];
}

function zm($nt, $zl)
{
    $mt = '/^ *(\d{1,2})\.(\d{1,2})\.(\d{2}|\d{4}) +(\d{1,2})\:(\d{1,2})\:(\d{1,2}) *$/';
    if (preg_match($mt, $nt, $jb)) {
        $m4 = gmmktime($jb[4], $jb[5], $jb[6], $jb[2], $jb[1], $jb[3]);
        $m4 -= ly($zl, $m4);
        return $m4;
    } else {
        return false;
    }
}

function km($ft = '')
{
    global $_fp_error, $_config, $_e2utf8__unformat_htmlentity_neasden, $_db;
    if (Log::$cy) __log('Process note form');
    try {
        $_fp_error = false;
        $dx        = $r6 = $dt = $tv = $st = '';
        if (array_key_exists('note-id', $_POST)) $dx = $_POST['note-id'];
        if (array_key_exists('title', $_POST)) $r6 = trim($_POST['title']);
        if (array_key_exists('tags', $_POST)) $dt = $_POST['tags'];
        if (array_key_exists('text', $_POST)) $tv = trim($_POST['text'], "\r\n");
        if (array_key_exists('summary', $_POST)) $at = trim($_POST['summary'], "\r\n");
        if (array_key_exists('old-tags-hash', $_POST)) $st = $_POST['old-tags-hash'];
        if (is_array($dt)) $dt = implode(', ', $dt);
        $dt = trim($dt);
        if ($dx == 'new') {
            $_e2utf8__unformat_htmlentity_neasden = ($_config['default_formatter'] == 'neasden');
        } else {
            $_e2utf8__unformat_htmlentity_neasden = ($_POST['formatter-id'] == 'neasden');
        }
        $qt = vn('Notes');
        if (stripos($qt['Collation'], 'utf8mb4') !== 0) {
            $r6 = nb($r6);
            $dt = nb($dt);
            $tv = nb($tv, true);
        }
        $lt = $tv;
        $lt = str_replace("\n", '\n' . "\n", $lt);
        $lt = str_replace("\r", '\r' . "\r", $lt);
        $zt = n(',', $dt, 'sort');
        $dt = implode(', ', $zt);
        $kt = md5($dt);
        if (array_key_exists('browser-offset', $_POST)) {
            $zl = wy(@$_POST['browser-offset']);
        } else {
            $zl = ay();
        }
        $xt = @$_POST['old-stamp'];
        $fr = @$_POST['stamp'];
        $i7 = @$_POST['alias'];
        if ($dx != 'new') {
            $et = mm($dx);
        } else {
            $et = array();
        }
        if ($dx) {
            if ((string)$r6 !== '' and (string)$tv !== '') {
                if ($dx == 'new') {
                    $yt = '';
                    if (is_file(USER_FOLDER . 'new-uploads.psa')) {
                        $yt = @file_get_contents(USER_FOLDER . 'new-uploads.psa');
                    }
                    try {
                        $dx = lm($r6, $tv, $zl, $yt);
                        @unlink(USER_FOLDER . 'new-uploads.psa');
                        $rt = array('*note' => mm($dx),);
                        $tt = ['success' => true, 'data' => ['status' => 'created', 'id' => $dx, 'note-url' => jv('e2m_note', $rt), 'note-edit-url' => jv('e2m_note_edit', $rt)]];
                        $q1 = (int)$dx;
                    } catch (AeMySQLException $e) {
                        kv($e, 'Could not insert note');
                        $tt = ['success' => false, 'error' => ['message' => 'Cannot create record']];
                        $q1 = false;
                    }
                } else {
                    e2_drop_caches_for_note_($dx, $et['IsPublished']);
                    $jt                 = $et;
                    $jt['ID']           = $dx;
                    $jt['Title']        = $r6;
                    $jt['Summary']      = $at;
                    $jt['Text']         = $tv;
                    $jt['FormatterID']  = $et['FormatterID'];
                    $jt['LastModified'] = time();
                    $jt['IsIndexed']    = '0';
                    if ($jt['FormatterID'] === 'calliope') {
                        $jt['FormatterID'] = $_config['default_formatter'];
                    }
                    if ($xt != $fr) {
                        if ($m4 = zm($fr, $zl)) {
                            $jt['Stamp'] = min($m4, time());
                        }
                    }
                    $u7 = $i7;
                    if ((string)$i7 !== '') {
                        $ht = $i7;
                    } elseif (!$et['IsPublished']) {
                        $ht = $r6;
                    } else {
                        $ht = '';
                    }
                    if ($et['IsPublished']) {
                        $u7 = cm('set', 'n', $dx, $ht);
                        $rt = array('*note' => $jt, 'alias' => $u7,);
                    } else {
                        $gt                  = true;
                        $u7                  = cm('find', 'n', $dx, $ht);
                        $jt['OriginalAlias'] = $u7;
                        $rt                  = array('*note' => $jt, 'alias' => $u7,);
                    }
                    $gs = g3($jt['FormatterID'], $jt['Text'], 'full');
                    if (count($gs) > 0) {
                        eb($gs);
                        rb($gs);
                    }
                    try {
                        nn('Notes', $jt);
                        if ($jt['IsPublished']) {
                            if (la($jt)) {
                                $jt['IsIndexed'] = '1';
                                nn('Notes', $jt);
                            }
                            if ($xt != $fr) {
                                un(true);
                            }
                            cy($jt);
                        }
                        $tt = ['success' => true, 'data' => ['status' => 'saved', 'new-alias' => $u7, 'note-url' => jv('e2m_note', $rt), 'note-edit-url' => jv('e2m_note_edit', $rt)]];
                        $q1 = (int)$dx;
                    } catch (AeMySQLException $e) {
                        kv($e, 'Could not update record');
                        $tt = ['success' => false, 'error' => ['message' => 'Cannot update record (' . mysqli_error($_db['link']) . ')']];
                        $q1 = false;
                    }
                }
                if ($kt != $st) {
                    yf(array('NoteID' => $dx));
                    foreach ($zt as $am) {
                        $wt = sf($am);
                        if (!$wt) {
                            $wt['ID'] = mf($am);
                        }
                        xn("INSERT INTO `" . $_config['db_table_prefix'] . "NotesKeywords` " . "(`SubsetID`, `NoteID`, `KeywordID`) " . "VALUES (" . ((int)$_config['db_table_subset']) . ", " . ((int)$dx) . ", " . ((int)$wt['ID']) . ")", 'add new tag bindings');
                    }
                }
                if ($ft != 'ajaxresult' and $q1 and $_POST['instant-publish'] == 'yes') {
                    $_POST['note-id'] = $dx;
                    e2s_note_publish();
                }
            } else {
                $tt        = ['success' => false, 'error' => ['message' => 'Title or text is empty']];
                $_fp_error = FP_TITLE_OR_TEXT_EMPTY;
                $q1        = false;
            }
        } else {
            $tt        = ['success' => false, 'error' => ['message' => 'No note id/new specified']];
            $_fp_error = FP_NO_ID_OR_NEW;
            $q1        = false;
        }
        if ($_config['backup_automatically']) {
            tn();
        }
    } catch (AeMySQLException $e) {
        kv($e);
        $tt = ['success' => false, 'error' => ['message' => 'Database error']];
        $q1 = false;
    }
    if ($ft == 'ajaxresult') return json_encode($tt); else return $q1;
}

function xm($ut, $it)
{
    global $_config;
    if (!($ut and $it) and !k2()) {
        if (Log::$cy) __log('Error: e2_notes_count_generic called for invisible items unsecurely');
        return null;
    }
    if (!is_bool($ut) or !is_bool($it)) {
        if (Log::$cy) __log('Error: e2_notes_count_generic called with non-bool params');
        return null;
    }
    if (!$ut and !$it) {
        if (Log::$cy) __log('Error: e2_notes_count_generic called with nonsensical parameters');
        return null;
    }
    $ot = (CACHES_FOLDER . 'notes-count-p' . (int)$ut . ($ut ? ('v' . (int)$it) : '') . '.txt');
    $q1 = false;
    if (CACHE_NOTES_COUNTS and is_file($ot)) {
        $q1 = @file_get_contents($ot);
    }
    if (is_numeric($q1) and $q1 > 0) {
        return $q1;
    } else {
        $q1 = null;
        try {
            xn("SELECT COUNT(*) As NotesCount FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=" . (int)$ut . " " . ($ut ? ("AND `IsVisible`=" . (int)$it) : ""), 'count notes with flags p' . (int)$ut . ($ut ? ('v' . (int)$it) : ''));
            $q1 = en();
            $q1 = $q1[0]['NotesCount'];
            if (CACHE_NOTES_COUNTS) n3($ot, $q1);
        } catch (AeMySQLException $e) {
            kv($e);
            if (Log::$cy) __log('Could not count notes');
        }
        return $q1;
    }
}

function em($tv)
{
    $at = $tv;
    $at = preg_match('/^(\<\/div\>)?\<p( class\=\"lead\")?\>(.*)\<\/p\>$/mu', $at, $y3);
    $at = @$y3[3];
    if (!$at) $at = $tv;
    if (mb_strlen($at) <= 50) $at = $tv;
    $at = str_replace(array('<p>', '<blockquote>', '<ul>', '<ol>', '<br />',), "\n", $at);
    $at = trim(strip_tags($at));
    if (mb_strlen($at) > 50) {
        $pt = mb_strpos($at, "\n", 50);
    } else {
        $pt = mb_strrpos($at, "\n");
    }
    if ($pt !== false) {
        $at = mb_substr($at, 0, $pt);
        $at = trim($at, ' :.()' . "\n");
    }
    if (preg_match('/^(.{100,}?)(?:[:.!?()]|' . "\n" . ')/su', $at, $y3)) {
        $at = trim($y3[0], ' :.()' . "\n");
    }
    if (preg_match('/^(.{150,}?)[:.!?(),]/su', $at, $y3)) {
        $at = trim($y3[0], ' :.(),' . "\n");
    }
    if (preg_match('/^(.{200,}?)[:.!?(), ]/su', $at, $y3)) {
        $at = trim($y3[0], ' :.()' . "\n");
    }
    $at = preg_replace('/[ \n\r\t]+/su', ' ', $at);
    if (mb_substr($at, -1) === '.') $at = mb_substr($at, 0, -1);
    if (mb_substr($at, -1) === ':') $at = mb_substr($at, 0, -1);
    if (mb_substr($at, -1) === '!') $at = mb_substr($at, 0, -1);
    if (@in_array($at[mb_strlen($at) - 1], array(',', ' '))) {
        $at = trim($at, ', ') . '...';
    }
    if (mb_strlen($at) > 250) {
        $at = trim(mb_substr($at, 0, 250)) . '...';
    }
    return $at;
}

function rm($n2)
{
    $c5 = false;
    if ($n2['IsPublished']) {
        if ($c5) {
            return 'scheduled';
        } else {
            if ($n2['IsVisible']) {
                return 'public';
            } else {
                return 'hidden';
            }
        }
    } else {
        return 'draft';
    }
}

function tm($we = false)
{
    if ($we) {
        return '';
    } else {
        return 'AND (n.`IsVisible` = 1 AND n.`Stamp` <= ' . time() . ') ';
    }
}

function jm($n2)
{
    return '';
}

function e2m_drafts($parameters)
{
    global $_strings, $_config;
    $we         = k2();
    $draftsView = new AePageableNotesView ('e2m_drafts', $parameters);
    $draftsView->setPortionSize((int)$_config['drafts_per_page']);
    $draftsView->setNextPrevPageTitles($_strings['gs--earlier'], $_strings['gs--later']);
    $draftsView->setWantPaging(true);
    $draftsView->setUseLocalHrefs(true);
    if ($draftsView->isFirstPage() and CACHE_DRAFTS) {
        $draftsView->setCacheFilename(CACHE_FILENAME_DRAFTS);
    }
    $draftsView->setLimitlessSQLRequest("SELECT * " . "FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=0 " . "ORDER BY `LastModified` DESC");
    $s5 = $draftsView->getNotesCTree();
    if (count($s5)) {
        if (Log::$cy) __log('Thumbnails {');
        foreach ($s5 as $t => $xf) {
            $s5[$t]['thumbs'] = qb(@$xf['format-info']['resources-detected']);
        }
        if (Log::$cy) __log('}');
    }
    $r6 = $_strings['pt--drafts'];
    if ($parameters['page'] > 1) {
        $r6 .= ' (' . $_strings['gs--page'] . ' ' . $parameters['page'] . ')';
    }
    $d = ['title' => $r6, 'heading' => $_strings['pt--drafts'], 'notes' => $s5, 'pages' => $draftsView->getPagesCTree(),];
    if ($draftsView->isFirstPageOfEmptyView()) {
        $d['nothing'] = $_strings['gs--no-drafts'];
    } elseif (!$draftsView->isExistingPage()) {
        return e2_error404_mode();
    }
    return $d;
}

function um($a5)
{
    global $_config;
    if (Log::$cy) __log('Drafts: find duplicate OriginalAliases...');
    if (CACHE_DRAFTS_ALIAS_USE_COUNTS and is_file(CACHE_FILENAME_DRAFTS_ALIAS_USE_COUNTS)) {
        $q5 = @unserialize(file_get_contents(CACHE_FILENAME_DRAFTS_ALIAS_USE_COUNTS));
    }
    if (CACHE_DRAFTS_ALIAS_USE_COUNTS and @is_array($q5)) {
        if (Log::$cy) __log('Drafts: retrieve cached');
    } else {
        if (Log::$cy) __log('Drafts: assemle cacheable...');
        $q5 = array();
        xn("SELECT `OriginalAlias` FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=0 " . "ORDER BY `ID`", 'get original aliases of drafts to calculate use counts');
        $q1 = en();
        $l5 = array();
        foreach ($q1 as $t => $n2) {
            @$q5[$n2['OriginalAlias']]++;
        }
        if (CACHE_DRAFTS_ALIAS_USE_COUNTS) {
            n3(CACHE_FILENAME_DRAFTS_ALIAS_USE_COUNTS, serialize($q5));
        }
    }
    return $q5[$a5];
}

function im()
{
    global $_strings, $_user_folder_name;
    $z5 = 'https://' . $_strings['e2--website-host'] . '/';
    $k5 = '(' . $_strings['e2--release'] . ' ' . E2_RELEASE . ', v' . E2_VERSION . ')';
    return ['built?' => BUILT, 'installed?' => (fn_() !== null), 'version' => 'v' . E2_VERSION, 'version-description' => $_strings['e2--vname-aegea'] . ' ' . $k5, 'user-folder-name' => $_user_folder_name, 'cookie-prefix' => b(), 'href' => $z5, 'about' => ('<span title="E2 ' . $k5 . '">' . $_strings['e2--powered-by'] . ' ' . '<a href="' . $z5 . '" class="nu"><u>' . $_strings['e2--vname-aegea'] . '</u> ' . '<span class="e2-svgi">' . is('aegea') . '</span></a></span>'),];
}

function om($candy, $x5, $e5, $r5, $v6)
{
    global $full_blog_url, $content, $_config, $_candies_indexable, $_candies_indexable_conditionally, $_template, $_newsfeeds, $_current_url;
    $meta['base-href']    = $full_blog_url . '/';
    $meta['current-href'] = $_current_url;
    $meta['stylesheets']  = ps();
    $meta['scripts']      = ca();
    $meta['newsfeeds']    = $_newsfeeds;
    $meta['favicon-type'] = 'image/x-icon';
    $meta['favicon-href'] = 'favicon.ico';
    if ($t5 = bd()) {
        $meta['favicon-type']          = v3($t5);
        $meta['favicon-href']          = $t5;
        $meta['apple-touch-icon-href'] = bd('square');
    }
    $meta['navigation-links'] = [['rel' => 'index', 'href' => jv('e2m_frontpage', ['page' => 1]), 'id' => 'link-index',]];
    if (!empty ($v6)) {
        foreach (['prev', 'next', 'earlier', 'later'] as $j5) {
            if (array_key_exists($j5 . '-href', $v6)) {
                $xr = $j5;
                if ($j5 == 'earlier') $xr = 'prev';
                if ($j5 == 'later') $xr = 'next';
                $tq = $v6[$j5 . '-href'];
                if ($tq === 'javascript:;') continue;
                $meta['navigation-links'][] = ['rel' => $xr, 'href' => $tq, 'id' => 'link-' . $j5,];
            }
        }
    }
    $h5 = 'noindex, follow';
    if (@$_config['index_follow_everything']) {
        $h5 = 'index, follow';
    }
    if (in_array($candy, $_candies_indexable)) {
        $meta['robots'] = 'index, follow';
    }
    if (in_array($candy, $_candies_indexable_conditionally)) {
        $meta['robots'] = $h5;
    }
    $meta['viewport'] = $_template['meta_viewport'];
    if (is_file(MEDIA_ROOT_FOLDER . 'manifest.json')) {
        $meta['manifest-href'] = $full_blog_url . '/manifest.json';
    }
    $meta['og-images'] = [];
    if (is_array($x5['only']['og-images'])) {
        $meta['og-images']    = $x5['only']['og-images'];
        $meta['twitter-card'] = 'summary_large_image';
    }
    if (is_array(@$e5['og-images'])) {
        $meta['og-images']    = $e5['og-images'];
        $meta['twitter-card'] = 'summary_large_image';
    }
    if (!count($meta['og-images'])) {
        $meta['og-images']    = array($r5['userpic-large-href']);
        $meta['twitter-card'] = 'summary';
    }
    return $meta;
}

function pm()
{
    global $_superconfig, $_config;
    $g5 = ['new-note-href' => jv('e2m_write'), 'drafts-href' => jv('e2m_drafts', ['page' => 1]), 'drafts-count' => (int)xm(false, true), 'settings-href' => jv('e2m_settings'), 'theme-preview-href' => jv('e2m_theme_preview', array('theme' => '')), 'password-href' => jv('e2m_password', array('recovery-key' => '')), 'database-href' => jv('e2m_database'), 'timezone-href' => jv('e2m_timezone'), 'sessions-href' => jv('e2m_sessions'), 'sign-out-href' => jv('e2m_sign_out'),];
    if (p()) {
        $g5['get-backup-href'] = jv('e2m_get_backup');
    }
    if (@$_config['read_only']) {
        unset ($g5['new-note-href']);
        unset ($g5['settings-href']);
        unset ($g5['timezone-href']);
    }
    if (@$_superconfig['disallow_themes_preview']) {
        unset ($g5['theme-preview-href']);
    }
    if (@$_superconfig['disallow_db_config']) {
        unset ($g5['database-href']);
    }
    list ($ke, $w5, $u5) = v2();
    if ($ke) {
        $g5['new-comments-count'] = $ke;
        $g5['new-comments-href']  = $u5;
    }
    return $g5;
}

function e2m_tags()
{
    global $_strings;
    $d['title']   = $_strings['pt--tags'];
    $d['heading'] = $_strings['pt--tags'];
    $d['tags']    = ff([]);
    $i5           = df(true);
    if ($i5 === null) {
        $d['unavailable?'] = true;
    } else {
        $d['tags']['each'] = $i5;
        if (count($i5) == 0) {
            $d['nothing'] = $_strings['gs--no-tags'];
        }
    }
    return $d;
}

function e2m_tag($parameters = [])
{
    global $settings, $_config, $_current_tags, $_strings;
    if (Log::$cy) __log('Tag {');
    $we           = k2();
    $tagNotesView = new AePageableNotesView ('e2m_tag', $parameters);
    $tagNotesView->setPortionSize($settings['appearance']['notes_per_page']);
    $tagNotesView->setNextPrevPageTitles($_strings['gs--earlier'], $_strings['gs--later']);
    $tagNotesView->setWantPaging(true);
    $tagNotesView->setWantNewCommentsCount($we);
    $tagNotesView->setWantReadHrefs($_config['count_reads']);
    $tagNotesView->setWantControls($we and !@$_config['read_only']);
    $tagNotesView->setWantHiddenTags($we);
    $o5 = [];
    if (array_key_exists('*tags', $parameters)) {
        foreach ($parameters['*tags'] as $q2) {
            if ($we or $q2['IsVisible']) {
                $o5[] = $q2;
            }
        }
    }
    if (!@$o5[0]) return e2_error404_mode();
    $p5            = count($o5);
    $p6            = $o5[0];
    $cj            = $parameters['tag-alias'];
    $_current_tags = [];
    foreach ($o5 as $p6) $_current_tags[] = $p6['Keyword'];
    $tagNotesView->setHighlightedTags($_current_tags);
    if (CACHE_TAG and $tagNotesView->isFirstPage() and $p5 === 1) {
        if ($we) {
            $tagNotesView->setCacheFilename(e2_cache_filename_with_id_($p6['ID'], CACHE_FILENAMES_TAG_AUTHOR));
        } else {
            $tagNotesView->setCacheFilename(e2_cache_filename_with_id_($p6['ID'], CACHE_FILENAMES_TAG));
        }
    }
    foreach ($o5 as $p6) if ($p6) $vj[] = "nk.`KeywordID`=" . $p6['ID'];
    $bj = ("FROM `" . $_config['db_table_prefix'] . "Notes` n " . "JOIN `" . $_config['db_table_prefix'] . "NotesKeywords` nk " . "ON nk.`NoteID` = n.`ID` " . "WHERE n.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND nk.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND (" . implode(" OR ", $vj) . ") " . "AND IsPublished=1 " . tm($we) . "GROUP BY n.`ID` " . "HAVING COUNT(*)>=" . $p5);
    $tagNotesView->setSQLCountRequest("SELECT COUNT(*) Total FROM (SELECT 1 " . $bj . ") _");
    $tagNotesView->setLimitlessSQLRequest("SELECT n.*, COUNT(*) " . $bj . " " . "ORDER BY n.`Stamp` DESC");
    $yj                = nf($p6['ID'], 5);
    $nj                = '';
    $e5['description'] = '';
    $e5['summary']     = '';
    $e5['visible?']    = (bool)$p6['IsVisible'];
    if ($p5 == 1) {
        if ($we) {
            $e5['edit-href'] = jv('e2m_tag_edit', array('tag-alias' => $cj));
        }
        if ((string)$p6['Description'] !== '') {
            $z1                            = i3($p6['Description'], 'full');
            $vn                            = $z1['text-final'];
            $e5['description']             = $vn;
            $e5['description-format-info'] = $z1['meta'];
            va(@$z1['meta']['links-required']);
        }
        if ((string)$p6['Summary'] !== '') {
            $e5['summary'] = h3(htmlspecialchars($p6['Summary'], ENT_NOQUOTES, HSC_ENC));
        } elseif ((string)$e5['description'] !== '') {
            $e5['summary'] = em($e5['description']);
        };
        $mj = jv('e2m_tag_rss', array('tag-alias' => $cj));
        $fj = jv('e2m_tag_json', array('tag-alias' => $cj));
        zd('rss', cd() . ': ' . $p6['Keyword'], $mj);
        zd('json', cd() . ': ' . $p6['Keyword'], $fj);
        $e5['og-images'] = d3(sb(@$e5['description-format-info']['resources-detected'], q3('tag', $p6['ID'])));
        $e5['name']      = htmlspecialchars($p6['Keyword'], ENT_COMPAT, HSC_ENC);
        $e5['related']   = $yj;
        $nj              = htmlspecialchars($p6['PageTitle'], ENT_COMPAT, HSC_ENC);
    }
    $dj                     = $tagNotesView->getTotalNotes();
    $e5['notes-count']      = $dj;
    $e5['notes-count-text'] = e2l_get_string('pt--n-posts', array('number' => $dj));
    $sj                     = $e5['notes-count-text'] . ' ' . $_strings['gs--tagged'];
    $aj                     = [];
    foreach ($o5 as $xf) {
        $aj[] = htmlspecialchars($xf['Keyword'], ENT_COMPAT, HSC_ENC);
    }
    $aj = implode(', ', $aj);
    if ((string)$nj !== '') {
        $r6 = $nj;
        $h6 = $nj;
    } else {
        $r6 = cd() . ': ' . $sj . ' ' . $aj;
        if (count($o5) > 1) {
            $h6 = $_strings['pt--tags'] . ': ' . $aj;
        } else {
            $h6 = $_strings['pt--tag'] . ': ' . $aj;
        }
    }
    if ($parameters['page'] > 1) {
        $r6 .= ' (' . $_strings['gs--page'] . ' ' . $parameters['page'] . ')';
    }
    $le = ff($parameters);
    $d  = ['title' => $r6, 'heading' => htmlspecialchars_decode($h6, ENT_COMPAT), 'notes' => $tagNotesView->getNotesCTree(), 'pages' => $tagNotesView->getPagesCTree(), 'tags' => $le,];
    if (!$tagNotesView->isExistingPage() and !$tagNotesView->isFirstPageOfEmptyView()) {
        return e2_error404_mode();
    }
    if ($tagNotesView->isFirstPageOfEmptyView() and !$we) {
        return e2_error404_mode();
    }
    if ($tagNotesView->isFirstPageOfEmptyView()) {
        $d['nothing'] = $_strings['gs--no-such-notes'];
    }
    if ((string)$e5['summary'] !== '') {
        $d['summary'] = $e5['summary'];
    }
    if (count($o5) == 1) {
        $d['tag'] = $e5;
        if (k2()) {
            $d['related-edit-href']  = $e5['edit-href'];
            $d['related-edit-title'] = $_strings['tt--edit-tag'];
        }
    }
    if (Log::$cy) __log('} // Tag');
    return $d;
}

function e2m_tag_edit($parameters = array())
{
    global $_strings;
    if (array_key_exists('*tag', $parameters)) {
        $p6 = $parameters['*tag'];
    }
    if (!$p6) return e2_error404_mode();
    $gs = g3('neasden', $p6['Description'], 'full');
    $ws = @unserialize($p6['Uploads']) or $ws = [];
    $mr = qb(fb(sb($gs, $ws)));
    k3('Keywords', $p6, $gs);
    $qq                          = r3();
    $qj                          = ['enabled?' => t3($qq), 'each' => $mr, 'default-name' => htmlspecialchars($parameters['tag-alias'], ENT_COMPAT, HSC_ENC), 'upload-action' => jv('e2j_file_upload'), 'remove-action' => jv('e2j_file_remove'),];
    $lj                          = ['.tag-id' => $p6['ID'], '.formatter-id' => 'neasden', 'form-action' => jv('e2s_tag_edit'), 'submit-text' => $_strings['fb--save-changes'], 'tag' => htmlspecialchars($p6['Keyword'], ENT_COMPAT, HSC_ENC), 'page-title' => htmlspecialchars($p6['PageTitle'], ENT_COMPAT, HSC_ENC), 'page-title-placeholder' => htmlspecialchars($p6['Keyword'], ENT_COMPAT, HSC_ENC), 'alias' => htmlspecialchars($parameters['tag-alias'], ENT_COMPAT, HSC_ENC), 'description' => htmlspecialchars($p6['Description'], ENT_COMPAT, HSC_ENC), 'summary' => (string)$p6['Summary'], 'favourite?' => (bool)$p6['IsFavourite'], 'space-usage' => j3($qq),];
    $lj['.cache-sensitive-hash'] = md5($lj['tag'] . serialize($qj) . $lj['alias']);
    $d                           = array('body-uploads-enabled?' => t3($qq), 'title' => $_strings['pt--tag-edit'] . ': ' . $p6['Keyword'], 'heading' => $_strings['pt--tag-edit'], 'form' => 'form-tag', 'form-tag' => $lj, 'uploads' => $qj, 'related-delete-href' => jv('e2m_tag_delete', array('*tag' => $p6)),);
    return $d;
}

function e2m_tag_flag_ajax($parameters)
{
    s(['flag-name' => 'tag', 'candy-name' => 'e2m_tag_flag_ajax', 'parameters' => $parameters, 'flipping-function' => function () use ($parameters) {
        cf($parameters);
    },]);
}

function cf($parameters)
{
    if (array_key_exists('*tag', $parameters)) {
        $p6 = $parameters['*tag'];
    }
    if (!$p6) return e2_error404_mode();
    e2_drop_caches_for_tag_($p6['ID']);
    nn('Keywords', array('ID' => $p6['ID'], $parameters['flag'] => (int)($parameters['value'] == 1),));
    return true;
}

function e2m_tag_delete($parameters = array())
{
    global $_strings;
    if (array_key_exists('*tag', $parameters)) {
        $p6 = $parameters['*tag'];
    }
    if (!$p6) return e2_error404_mode();
    $zj = array('.tag-id' => $p6['ID'], 'caution-text' => e2l_get_string('gs--tag-will-be-deleted-notes-remain', array('tag' => htmlspecialchars($p6['Keyword'], ENT_COMPAT, HSC_ENC))), 'tag' => htmlspecialchars($p6['Keyword'], ENT_COMPAT, HSC_ENC), 'form-action' => jv('e2s_tag_delete'), 'submit-text' => $_strings['fb--delete'],);
    $d  = array('title' => $_strings['pt--tag-delete'] . ': ' . $p6['Keyword'], 'heading' => $_strings['pt--tag-delete'], 'form' => 'form-tag-delete', 'form-tag-delete' => $zj,);
    return $d;
}

function e2m_untagged($parameters = [])
{
    global $settings, $_strings, $_config;
    $we           = k2();
    $untaggedView = new AePageableNotesView ('e2m_untagged', $parameters);
    $untaggedView->setPortionSize($settings['appearance']['notes_per_page']);
    $untaggedView->setNextPrevPageTitles($_strings['gs--earlier'], $_strings['gs--later']);
    $untaggedView->setWantPaging(true);
    $untaggedView->setWantNewCommentsCount($we);
    $untaggedView->setWantReadHrefs($_config['count_reads']);
    $untaggedView->setWantControls($we and !@$_config['read_only']);
    $untaggedView->setWantHiddenTags($we);
    $bj = ("FROM `" . $_config['db_table_prefix'] . "Notes` n " . "LEFT OUTER JOIN `" . $_config['db_table_prefix'] . "NotesKeywords` nk " . "ON nk.`NoteID` = n.`ID` " . "WHERE n.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND n.`IsPublished`=1 " . "AND nk.`SubsetID` IS NULL " . tm($we));
    $untaggedView->setSQLCountRequest("SELECT COUNT(*) Total " . $bj);
    $untaggedView->setLimitlessSQLRequest("SELECT n.* " . $bj . " ORDER BY n.`Stamp` DESC");
    $r6 = $_strings['pt--posts-without-tags'];
    if ($parameters['page'] > 1) {
        $r6 .= ' (' . $_strings['gs--page'] . ' ' . $parameters['page'] . ')';
    }
    $d = ['title' => $r6, 'heading' => $_strings['pt--posts-without-tags'], 'notes' => $untaggedView->getNotesCTree(), 'pages' => $untaggedView->getPagesCTree(),];
    if ($untaggedView->isFirstPageOfEmptyView()) {
        $d['nothing'] = $_strings['gs--no-posts-without-tags'];
    } elseif (!$untaggedView->isExistingPage()) {
        return e2_error404_mode();
    }
    return $d;
}

function e2s_tag_edit()
{
    global $_strings, $_config;
    $kj = $am = $vn = $ht = '';
    if (array_key_exists('tag-id', $_POST)) $kj = $_POST['tag-id'];
    if (array_key_exists('tag', $_POST)) $am = $_POST['tag'];
    if (array_key_exists('page-title', $_POST)) $nj = trim($_POST['page-title'], "\r\n");
    if (array_key_exists('description', $_POST)) $vn = trim($_POST['description'], "\r\n");
    if (array_key_exists('summary', $_POST)) $at = trim($_POST['summary'], "\r\n");
    if (array_key_exists('urlname', $_POST)) $ht = trim($_POST['urlname'], "\r\n");
    if (array_key_exists('cache-sensitive-hash', $_POST)) {
        $xj = $_POST['cache-sensitive-hash'];
        $ej = md5($am . $ht);
    }
    $qt = vn('Notes');
    if (stripos($qt['Collation'], 'utf8mb4') !== 0) {
        $am = nb($am);
        $nj = nb($nj);
        $vn = nb($vn, true);
    }
    xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Keywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID` = " . ((int)$kj) . "", 'get tag record to update');
    $rj = en();
    if (count($rj) != 1) die;
    $q2 = $rj[0];
    xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Keywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `Keyword` = '" . rn($am) . "' " . "AND (`ID` != " . ((int)$kj) . ")", 'make sure new tag name does not conflict with existing ones');
    $rj = en();
    if (count($rj) == 0) {
        if ($ej != $xj) {
            ds();
        }
        e2_drop_caches_for_tag_($kj);
        $q2['ID']          = ((int)$kj);
        $q2['Keyword']     = $am;
        $q2['PageTitle']   = $nj;
        $q2['Description'] = $vn;
        $q2['Summary']     = $at;
        $gs                = g3('neasden', $q2['Description'], 'full');
        if (count($gs) > 0) {
            eb($gs);
            rb($gs);
        }
        nn('Keywords', $q2);
        $u7 = cm('set', 't', $q2['ID'], $ht);
        c(jv('e2m_tag', array('tag-alias' => $u7)));
    } else {
        mv($_strings['er--cannot-rename-tag'], E2E_USER_ERROR);
        v();
    }
    die;
}

function e2s_tag_delete()
{
    global $_strings, $_config;
    $kj = ((int)$_POST['tag-id']);
    ds();
    e2_drop_caches_for_tag_($kj);
    xn("DELETE FROM `" . $_config['db_table_prefix'] . "Keywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`=" . $kj, 'delete note by ID');
    xn("DELETE FROM `" . $_config['db_table_prefix'] . "Aliases` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `EntityType` = 't' " . "AND `EntityID` = " . ((int)$kj), 'delete aliases after deleting note');
    xn("DELETE FROM `" . $_config['db_table_prefix'] . "NotesKeywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `KeywordID`=" . $kj, 'delete tag bindings after deleting tag');
    c(jv('e2m_tags'));
}

function vf($tj)
{
    global $_current_tags, $_config;
    $jj = null;
    if (CACHE_FAVTAGS and is_file(CACHE_FILENAME_FAVTAGS)) {
        $jj = @unserialize(file_get_contents(CACHE_FILENAME_FAVTAGS));
    }
    if (!is_array($jj)) {
        try {
            xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Keywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsFavourite`=1 ORDER BY `Keyword`", 'get favorite tags for tags menu');
            $hj = en();
            $jj = array();
            foreach ($hj as $q2) {
                $gj['tag']      = htmlspecialchars($q2['Keyword'], ENT_NOQUOTES, HSC_ENC);
                $gj['href']     = jv('e2m_tag', array('*tag' => $q2));
                $gj['visible?'] = (bool)$q2['IsVisible'];
                $jj[]           = $gj;
            }
            if (CACHE_FAVTAGS) n3(CACHE_FILENAME_FAVTAGS, serialize($jj));
        } catch (AeMySQLException $e) {
            kv($e);
            if (Log::$cy) __log('Count not get tags menu from database');
        }
    }
    if (!is_array($jj)) return null;
    $wj = false;
    if (!empty ($_current_tags)) {
        foreach ($jj as $t => $xf) {
            $jj[$t]['current?'] = in_array($jj[$t]['tag'], $_current_tags);
            if ($jj[$t]['current?']) {
                $wj          = true;
                $uj          = $tj;
                $uj['flag']  = 'IsFavourite';
                $uj['value'] = 0;
                if (k2()) {
                    $jj[$t]['pinnable?']          = true;
                    $jj[$t]['pinned?']            = true;
                    $jj[$t]['pinned-toggle-href'] = (jv('e2m_tag_flag_ajax', $uj));
                }
            }
        }
    }
    if (k2()) {
        if (!$wj and array_key_exists('*tag', $tj)) {
            $ij          = $tj;
            $ij['flag']  = 'IsFavourite';
            $ij['value'] = 1;
            $oj          = ['tag' => htmlspecialchars($tj['*tag']['Keyword'], ENT_NOQUOTES, HSC_ENC), 'href' => jv('e2m_tag', $tj), 'visible?' => (bool)$tj['*tag']['IsVisible'], 'current?' => true, 'pinnable?' => true, 'pinned?' => false, 'pinned-toggle-href' => jv('e2m_tag_flag_ajax', $ij),];
            $jj[]        = $oj;
        }
    }
    return $jj;
}

function bf($dx)
{
    global $_config;
    $s2 = array();
    xn("SELECT k.* " . "FROM `" . $_config['db_table_prefix'] . "Keywords` k, " . "`" . $_config['db_table_prefix'] . "NotesKeywords` nk " . "WHERE k.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND nk.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND nk.`NoteID`=" . ((int)$dx) . " " . "AND k.`ID`=nk.`KeywordID` " . "ORDER BY `Keyword`", 'get tag records for note by id');
    $s2 = en();
    return $s2;
}

function yf($pj)
{
    global $_config;
    $ch = array();
    foreach (array('ID', 'NoteID', 'KeywordID',) as $iz) if (array_key_exists($iz, $pj)) {
        $uz[] = '`' . $iz . '`' . "='" . rn($pj[$iz]) . "'";
        if ($iz == 'ID') $vh = 'tagbinging-id';
        if ($iz == 'NoteID') $vh = 'tagbinging-note-id';
        if ($iz == 'KeywordID') $vh = 'tagbinging-tag-id';
        $ch[$vh] = $pj[$iz];
    }
    $bf = ("DELETE FROM `" . $_config['db_table_prefix'] . "NotesKeywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND " . implode(' AND ', $uz));
    xn($bf);
}

function nf($kj, $bh)
{
    global $_config;
    xn("SELECT `ID`, `Keyword`, `OriginalAlias` " . "FROM `" . $_config['db_table_prefix'] . "Keywords` k " . "WHERE k.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsVisible` = 1 " . "AND k.`ID` IN (" . "SELECT `KeywordID` FROM (" . "SELECT COUNT(`NoteID`) NotesCount, `KeywordID` " . "FROM `" . $_config['db_table_prefix'] . "NotesKeywords` nk " . "WHERE nk.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND nk.`NoteID` IN (" . "SELECT nk2.`NoteID` " . "FROM `" . $_config['db_table_prefix'] . "NotesKeywords` nk2 " . "WHERE nk2.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND nk2.`KeywordID`=" . $kj . ") " . "GROUP BY nk.`KeywordID` " . "HAVING NotesCount > 1 " . "ORDER BY NotesCount DESC " . "LIMIT 1, " . $bh . ") k_ids" . ")", 'find related tags');
    $yj = [];
    foreach (en() as $q2) {
        if ($q2['ID'] === $kj) continue;
        $yj[] = ['name' => htmlspecialchars($q2['Keyword'], ENT_NOQUOTES, HSC_ENC), 'href' => jv('e2m_tag', array('*tag' => $q2)), 'visible?' => true,];
    }
    return $yj;
}

function mf($am)
{
    @unlink(CACHE_FILENAME_TAGS);
    @unlink(CACHE_FILENAME_TAGS_FULL);
    @unlink(CACHE_FILENAME_TAGS_AUTHOR);
    @unlink(CACHE_FILENAME_TAGS_AUTHOR_FULL);
    un(true);
    $q2 = array('Keyword' => $am, 'OriginalAlias' => cm('find', '', '', $am), 'Description' => '',);
    $q2 = yn('Keywords', $q2);
    $yh = cm('set', 't', $q2['ID'], $am);
    if ($yh != $q2['OriginalAlias']) {
        $q2['OriginalAlias'] = $yh;
        nn('Keywords', $q2);
    }
    return $q2['ID'];
}

function ff($parameters)
{
    if (($nh = df()) === null) return [];
    $le['each'] = $nh;
    if (count($le['each']) > 0) {
        $le['href'] = jv('e2m_tags');
    }
    if (($mh = vf($parameters)) !== null) {
        $le['menu-each'] = $mh;
    }
    return $le;
}

function df($fh = false)
{
    global $_config;
    $we = k2();
    $ot = CACHE_FILENAME_TAGS;
    if ($we) $ot = CACHE_FILENAME_TAGS_AUTHOR;
    if ($fh) {
        $ot = CACHE_FILENAME_TAGS_FULL;
        if ($we) $ot = CACHE_FILENAME_TAGS_AUTHOR_FULL;
    }
    $dh = null;
    if (CACHE_TAGS and is_file($ot)) {
        $dh = @unserialize(file_get_contents($ot));
    }
    if (!is_array($dh)) {
        try {
            xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Keywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "ORDER BY `Keyword`", 'get all tags');
            $sh = array();
            foreach (en() as $q2) {
                $am['id']          = (int)$q2['ID'];
                $am['tag']         = htmlspecialchars($q2['Keyword'], ENT_NOQUOTES, HSC_ENC);
                $am['favourite?']  = (bool)$q2['IsFavourite'];
                $am['visible?']    = (bool)$q2['IsVisible'];
                $am['notes-count'] = 0;
                $am['last-used']   = 0;
                $am['freshness']   = 0;
                $am['weight']      = 0;
                if ($fh) {
                    $am['href'] = jv('e2m_tag', array('*tag' => $q2));
                }
                $sh[$q2['ID']] = $am;
            }
            xn("SELECT nk.KeywordID, COUNT(DISTINCT n.ID) as Count, max(n.Stamp) as LastUsed " . "FROM `" . $_config['db_table_prefix'] . "NotesKeywords` nk, " . "`" . $_config['db_table_prefix'] . "Notes` n " . "WHERE nk.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND n.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND n.`IsPublished` = 1 " . tm($we) . "AND nk.`NoteID` = n.`ID` " . "GROUP BY nk.KeywordID", 'get tags ordering info');
            $ah = 0;
            $qh = 0;
            $lh = 0;
            foreach (en() as $zh) {
                $gj                =& $sh[$zh['KeywordID']];
                $gj['notes-count'] = $zh['Count'];
                if (@$gj['last-used'] < $zh['LastUsed']) {
                    $gj['last-used'] = $zh['LastUsed'];
                    $kh              = (time() - $gj['last-used']) / SECONDS_IN_A_YEAR;
                    $gj['freshness'] = pow(1 / 2, $kh);
                }
                $ah = max($ah, $gj['notes-count']);
                $qh = max($qh, $gj['freshness']);
                $lh = max($lh, $gj['notes-count'] * $gj['freshness']);
            }
            $dh = array();
            foreach ($sh as $r => $xf) {
                if (!$we and $xf['notes-count'] == 0) continue;
                $xh      = mb_strtolower($xf['tag']);
                $dh[$xh] = $xf;
                if ($qh != 0) {
                    $dh[$xh]['freshness'] = $xf['freshness'] / $qh;
                } else {
                    $dh[$xh]['freshness'] = 0;
                }
                if ($lh != 0) {
                    $dh[$xh]['weight'] = ($xf['freshness'] * $xf['notes-count'] / $lh);
                } else {
                    $dh[$xh]['weight'] = 0;
                }
                if ($dh[$xh]['favourite?']) $dh[$xh]['weight'] = 1;
            }
            if (CACHE_TAGS) n3($ot, serialize($dh));
        } catch (AeMySQLException $e) {
            kv($e);
            if (Log::$cy) __log('Could not get tags from database');
        }
    }
    return $dh;
}

function sf($p6)
{
    global $_config;
    xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Keywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `Keyword`='" . rn($p6) . "'", 'get tag by name');
    $t = en();
    if (isset ($t[0])) {
        return $t[0];
    } else {
        return null;
    }
}

function af($eh)
{
    global $_config;
    xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Keywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `OriginalAlias`='" . rn($eh) . "'", 'get tag by legacy urlname name');
    $ib = en();
    if (isset ($ib[0])) {
        return $ib[0];
    } else {
        return null;
    }
}

function qf($xs)
{
    global $_config;
    xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Keywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`='" . ((int)$xs) . "'", 'get tag by id');
    $ib = en();
    if (isset ($ib[0])) {
        return $ib[0];
    } else {
        return null;
    }
}

function e2_tagrecs_with_parameters_($parameters)
{
    $rh = array();
    if (@$parameters['tag-alias'] or $parameters['tag-alias'] === '0') {
        $rh = explode(',', $parameters['tag-alias']);
    }
    $s2 = array();
    foreach ($rh as $eh) if ($eh or $eh === '0') {
        if ($vt = pn(@$eh) and ($vt['type'] == 't') and ($q2 = qf($vt['id']))) {
            $s2[] = $q2;
        } else {
            if ($th = af($eh)) {
                $s2[] = $th;
            }
        }
    }
    return $s2;
}

function zf()
{
    global $full_blog_url;
    static $jh;
    $rm = w2();
    if (empty ($jh)) {
        $jh = md5($full_blog_url . 'email' . $rm);
    }
    return $jh;
}

function kf()
{
    global $full_blog_url;
    static $hh;
    $rm = w2();
    if (empty ($hh)) {
        $hh = md5($full_blog_url . 'nospam' . $rm . date('n-Y'));
    }
    return $hh;
}

function xf()
{
    global $full_blog_url;
    static $gh;
    $rm = w2();
    if (empty($gh)) {
        $gh = md5($full_blog_url . 'nospam' . $rm . date('n-Y', strtotime('-1 month')));
    }
    return $gh;
}

function ef($dx)
{
    global $full_blog_url;
    $rm = w2();
    return b('comment_' . md5($full_blog_url . 'nospam_cookie' . $rm . $dx));
}

function rf()
{
    global $full_blog_url;
    $wh = $_SERVER['HTTP_USER_AGENT'];
    $rm = w2();
    return md5($full_blog_url . 'nospam_cookie' . $rm . $wh);
}

function tf()
{
    if (array_key_exists('email', $_POST) and $_POST['email'] !== '') return true;
    $hh = kf();
    $gh = xf();
    if (!array_key_exists($hh, $_POST) and !array_key_exists($gh, $_POST)) return true;
    if ((array_key_exists($hh, $_POST) and $_POST[$hh] !== '') or (array_key_exists($gh, $_POST) and $_POST[$gh] !== '')) return true;
    if (!array_key_exists('comment', $_POST) or (strlen($_POST['comment']) > 6)) return true;
    return false;
}

function e2_cookie_data_is_spam_suspicios_for_note_id_($dx)
{
    if (!array_key_exists(ef($dx), $_COOKIE) or ($_COOKIE[ef($dx)] !== rf())) return true;
    return false;
}

function e2m_comment($parameters = array())
{
    c(jv('e2m_note', $parameters));
}

function e2m_comment_edit($parameters = array())
{
    return hf('edit', $parameters);
}

function hf($j6, $parameters = array())
{
    global $_config, $_strings, $full_blog_url;
    $r6 = $h6 = $_strings['pt--new-comment'];
    $uh = 'new';
    if ($j6 == 'edit') {
        $a6 = e2_commentrec_with_parameters_($parameters);
        $nr = $_strings['fb--save-changes'];
        $n2 = $a6['noterec'];
        $r6 = $h6 = $_strings['pt--edit-comment'];
        $ih = wf($n2, $a6, $parameters['comment-number']);
        if (!$a6) {
            return e2_error404_mode();
        }
        $oh = array('.note-id' => $a6['NoteID'], '.comment-id' => $a6['ID'], '.comment-number' => $parameters['comment-number'], '.already-subscribed?' => false, '.gip' => $a6['GIP'], 'create:edit?' => false, 'form-action' => jv('e2s_comment_process'), 'submit-text' => $nr, 'show-subscribe?' => true, 'subscribe?' => (bool)$a6['IsSubscriber'], 'name' => htmlspecialchars($a6['AuthorName'], ENT_COMPAT, HSC_ENC), 'email' => htmlspecialchars($a6['AuthorEmail'], ENT_COMPAT, HSC_ENC), 'text' => htmlspecialchars($a6['Text'], ENT_COMPAT, HSC_ENC), 'email-field-name' => zf(),);
        if ('' != trim($a6['IP'])) {
            $oh['ip'] = $a6['IP'];
        }
    }
    $d = array('title' => $r6, 'heading' => $h6, 'form' => 'form-comment', 'form-comment' => $oh,);
    if (!empty ($ih)) {
        $d['comments'] = array('each' => array('only' => $ih));
    }
    return $d;
}

function e2m_comment_reply($parameters = array())
{
    global $_strings;
    $a6 = e2_commentrec_with_parameters_($parameters);
    if (!$a6) {
        return e2_error404_mode();
    }
    $n2              = $a6['noterec'];
    $ih              = wf($n2, $a6, $parameters['comment-number']);
    $ih['replying?'] = (bool)true;
    $ph              = ($a6['Reply'] == '' or !$a6['IsReplyVisible']);
    $r6              = $ph ? $_strings['pt--reply-to-comment'] : $_strings['pt--edit-reply-to-comment'];
    $c8              = array('.note-id' => $n2['ID'], '.comment-id' => $a6['ID'], '.reply-action' => $ph ? 'new' : 'edit', 'form-action' => jv('e2s_comment_edit_reply'), 'submit-text' => $ph ? $_strings['fb--publish'] : $_strings['fb--save-changes'], 'create:edit?' => (bool)($ph), 'reply-text' => htmlspecialchars($a6['Reply'], ENT_COMPAT, HSC_ENC), 'emailing-possible?' => MAIL_ENABLED, 'mail-back?' => (bool)($ph),);
    return array('title' => $r6, 'heading' => $r6, 'comments' => array('each' => array('only' => $ih)), 'form' => 'form-comment-reply', 'form-comment-reply' => $c8,);
}

function e2m_comment_delete($parameters = array())
{
    global $_config;
    $a6 = e2_commentrec_with_parameters_($parameters);
    $dx = $a6['NoteID'];
    if (!$a6) {
        return e2_error404_mode();
    }
    e2_drop_caches_for_note_($dx, true);
    @unlink(USER_FOLDER . '/last-comment.psa');
    xn("DELETE FROM `" . $_config['db_table_prefix'] . "Comments` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID` = '" . ((int)$a6['ID']) . "'");
    v();
}

function e2m_comment_reply_delete($parameters = array())
{
    global $_strings, $settings, $_config;
    $a6 = e2_commentrec_with_parameters_($parameters);
    if (!$a6) {
        return e2_error404_mode();
    }
    xn("UPDATE `" . $_config['db_table_prefix'] . "Comments` SET " . "`Reply`='', " . "`IsReplyFavourite`='0' " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`=" . ((int)$a6['ID']));
    v();
}

function e2m_unsubscribe($parameters)
{
    global $_strings, $_config;
    $v8 = "ORDER BY `ID` DESC";
    $b8 = false;
    $n2 = $parameters['*note'];
    $dx = $n2['ID'];
    $z3 = $parameters['unsubscribe-email'];
    $y8 = $parameters['unsubscribe-key'];
    $z3 = str_replace(' ', '+', $z3);
    if ($dx) {
        xn("SELECT `ID`, `Stamp` FROM `" . $_config['db_table_prefix'] . "Comments` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `NoteID`=" . $dx . " " . "AND `IsSubscriber`=1 " . "AND `AuthorEmail`='" . $z3 . "' " . $v8, 'get subscriber’s comments ids');
        $q1 = en();
        if (count($q1) < 1) {
            $d['unsubscribe']['error-message'] = $_strings['gs--you-are-not-subscribed'];
        } else {
            $q6 = @$q1[0];
            $n8 = md5($q6['ID'] . $q6['Stamp'] . 'x');
            if ($y8 == $n8) {
                xn("UPDATE `" . $_config['db_table_prefix'] . "Comments` " . "SET `IsSubscriber`=0 " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `NoteID`=" . $dx . " " . "AND `AuthorEmail` = '" . rn($z3) . "'", 'unsubscribe');
                $b8                             = true;
                $d['unsubscribe']['note-title'] = h3(htmlspecialchars($n2['Title'], ENT_COMPAT, HSC_ENC));
                $d['unsubscribe']['note-href']  = jv('e2m_note', array('*note' => $n2));
            }
            if (!$b8) {
                $d['unsubscribe']['error-message'] = $_strings['gs--unsubscription-didnt-work'];
            }
        }
    } else {
        $d['unsubscribe']['error-message'] = $_strings['gs--post-not-found'];
    }
    if ($b8) {
        $r6 = $_strings['pt--unsubscription-done'];
    } else {
        $r6 = $_strings['pt--unsubscription-failed'];
    }
    $d['unsubscribe']['success?'] = $b8;
    $d['title']                   = $r6;
    $d['heading']                 = $r6;
    return $d;
}

function e2m_comment_flag($parameters)
{
    gf($parameters);
    c(jv('e2m_note', $parameters));
}

function e2m_comment_flag_ajax($parameters)
{
    s(['flag-name' => 'comment', 'candy-name' => 'e2m_comment_flag_ajax', 'parameters' => $parameters, 'flipping-function' => function () use ($parameters) {
        gf($parameters);
    },]);
}

function gf($parameters)
{
    $a6 = e2_commentrec_with_parameters_($parameters);
    $dx = $a6['NoteID'];
    if ($a6) {
        nn('Comments', array('ID' => $a6['ID'], $parameters['flag'] => (int)($parameters['value'] == 1),));
        e2_drop_caches_for_note_($dx, true);
    }
}

function e2s_comment_process()
{
    global $_strings, $_fp_error;
    list ($dx, $uh, $m8) = f2();
    if (Log::$cy) __log('Comments: processed, noteid <' . $dx . '>, commentid <' . $uh . '>');
    if (!$uh) {
        $f8 = '';
        if ($_fp_error == FP_NOT_COMMENTABLE) {
            mv($_strings['er--post-not-commentable'], E2E_USER_ERROR);
        } elseif ($_fp_error == FP_EMPTY_FIELD) {
            mv($_strings['er--name-email-text-required'], E2E_USER_ERROR);
        } elseif ($_fp_error == FP_COMMENT_TOO_LONG) {
            $d8 = $_strings['gs--comment-too-long'];
            $f8 = $_strings['gs--comment-too-long-description'];
        } elseif ($_fp_error == FP_COMMENT_DOUBLE_POST) {
            $d8 = $_strings['gs--comment-double-post'];
            $f8 = $_strings['gs--comment-double-post-description'];
        } elseif ($_fp_error == FP_COMMENT_SPAM_SUSPECT) {
            $d8 = $_strings['gs--comment-spam-suspect'];
            $f8 = $_strings['gs--comment-spam-suspect-description'];
        } elseif ($_fp_error == FP_NO_ID_OR_NEW) {
            mv($_strings['er--error-occurred'] . ' (FP_NO_ID_OR_NEW)');
        } else {
            mv($_strings['er--error-occurred'] . ' (FP ' . $_fp_error . ')');
        }
        if ($f8) {
            $d['title']                   = $d8;
            $d['heading']                 = $d8;
            $d['form']                    = 'form-unaccepted-comment';
            $d['form-unaccepted-comment'] = array('reason' => $f8, 'text' => @htmlspecialchars($m8['text'], ENT_COMPAT, HSC_ENC),);
            return $d;
        }
    }
    if ($dx) {
        c(jv('e2m_note', array('*note' => mm($dx))));
    } else {
        c();
    }
    die;
}

function e2s_comment_edit_reply()
{
    global $_strings, $v, $_config;
    $s8 = @$_POST['text'];
    if (trim($s8) == '') $s8 = '';
    $dx = @$_POST['note-id'];
    $n2 = mm($dx);
    $uh = @$_POST['comment-id'];
    $a6 = uf($uh);
    $a8 = isset ($_POST['mail-back']);
    $q8 = time();
    if (@$_POST['reply-action'] == 'new') {
        $l8 = time();
    }
    @unlink(e2_note_cache_filename_with_id_($dx . '-comments'));
    @unlink(e2_note_cache_filename_with_id_($dx . '-comments-author'));
    if ($a6) {
        xn("UPDATE `" . $_config['db_table_prefix'] . "Comments` SET " . "`Reply`='" . rn($s8) . "', " . (isset ($l8) ? ("`ReplyStamp`='" . $l8 . "', ") : ("")) . "`ReplyLastModified`='" . $q8 . "', " . "`IsReplyVisible`='1' " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`=" . ((int)$uh), 'update comment reply');
        $ie = jv('e2m_note', array('*note' => $n2));
        if ($a8 and $s8 != '') {
            $gj['comment-time']    = array($a6['Stamp'], ay());
            $gj['commenter']       = $a6['AuthorName'];
            $gj['commenter-email'] = $a6['AuthorEmail'];
            $gj['comment-text']    = $a6['Text'];
            $gj['note-title']      = h3($n2['Title']);
            $gj['reply-time']      = array(time(), ay());
            $gj['blog-author']     = vd();
            $gj['note-href']       = $ie;
            $gj['comment-href']    = $ie;
            $gj['reply-text']      = $s8;
            if (1) {
                $z8 = f1('comment-reply', $gj);
                $k8 = e2l_get_string('em--comment-reply', $gj);
                $x8 = $a6['AuthorEmail'];
                $e8 = 'From: ' . d1();
                s1($x8, $k8, $z8, $e8);
            }
            if (1) {
                unset ($gj['commenter-email']);
                $e8 = 'From: ' . d1();
                foreach (y2($n2, $a6['AuthorEmail']) as $r8) {
                    $t8                     = $r8['AuthorEmail'];
                    $j8                     = md5($r8['ID'] . $r8['Stamp'] . 'x');
                    $gj['unsubscribe-href'] = jv('e2m_unsubscribe', array('*note' => $n2, 'unsubscribe-email' => $t8, 'unsubscribe-key' => $j8,));
                    $x8                     = $t8;
                    $z8                     = f1('comment-reply-to-public', $gj);
                    $k8                     = e2l_get_string('em--comment-reply-to-public-subject', $gj);
                    s1($x8, $k8, $z8, $e8);
                }
            }
        }
        c($ie);
    } else {
        v();
    }
    die;
}

function wf($n2, $q6, $wv)
{
    global $_config, $full_blog_url;
    if (Log::$cy) __log('Package comment ' . $q6['ID'] . '...');
    if ($n2 === null) {
        $n2 = mm($q6['NoteID']);
    }
    $gj['number']       = $wv;
    $h8                 = !empty ($q6['IsGIPUsed']);
    $gj['gip-used?']    = $h8;
    $gj['gip']          = $gj['gip-used?'] ? $q6['GIP'] : '';
    $gj['name']         = htmlspecialchars($q6['AuthorName'], ENT_NOQUOTES, HSC_ENC);
    $gj['userpic-set?'] = false;
    if ($h8) {
        $g8 = AVATARS_FOLDER . $q6['GIP'] . '-' . $q6['GIPAuthorID'] . '.jpg';
        if (is_file(MEDIA_ROOT_FOLDER . $g8)) {
            $gj['userpic-set?'] = true;
            $gj['userpic-href'] = $full_blog_url . '/' . $g8;
        }
    }
    $gj['name-href'] = '';
    if ($h8 and $w8 = e2_get_user_profile_url($q6['GIP'], $q6['GIPAuthorID'], $q6['AuthorProfileLink'])) {
        $gj['name-href'] = $w8;
    }
    if (k2()) {
        $gj['email'] = htmlspecialchars($q6['AuthorEmail'], ENT_NOQUOTES, HSC_ENC);
        if ('' != trim($q6['IP'])) {
            $gj['ip'] = $q6['IP'];
        }
    }
    $gj['author-name']      = vd();
    $gj['important?']       = (bool)$q6['IsFavourite'];
    $gj['reply-visible?']   = (bool)($q6['IsVisible'] && $q6['IsReplyVisible']);
    $gj['reply-important?'] = (bool)$q6['IsReplyFavourite'];
    $gj['spam-suspect?']    = (bool)$q6['IsSpamSuspect'];
    $u8                     = array((int)$q6['Stamp'], dy($n2));
    $gj['time']             = $u8;
    $gj['last-modified']    = $u8;
    if ($q6['LastModified']) $gj['last-modified'] = array((int)$q6['LastModified'], dy($n2));
    if ($q6['ReplyStamp']) $gj['reply-time'] = array((int)$q6['ReplyStamp'], dy($n2));
    if ($q6['ReplyLastModified']) $gj['reply-last-modified'] = array((int)$q6['ReplyLastModified'], dy($n2));
    if (k2()) {
        $gj['subscriber?'] = (bool)$q6['IsSubscriber'];
        $gj['new?']        = (bool)$q6['IsNew'];
        $gj['first-new?']  = false;
        if (!@$_config['read_only']) {
            if ($q6['IsFavourite']) {
                $gj['important-toggle-href'] = jv('e2m_comment_flag_ajax', array('*note' => $n2, 'comment-number' => $wv, 'flag' => 'IsFavourite', 'value' => 0));
            } else {
                $gj['important-toggle-href'] = jv('e2m_comment_flag_ajax', array('*note' => $n2, 'comment-number' => $wv, 'flag' => 'IsFavourite', 'value' => 1));
            }
            if ($q6['IsReplyFavourite']) {
                $gj['reply-important-toggle-href'] = jv('e2m_comment_flag_ajax', array('*note' => $n2, 'comment-number' => $wv, 'flag' => 'IsReplyFavourite', 'value' => 0));
            } else {
                $gj['reply-important-toggle-href'] = jv('e2m_comment_flag_ajax', array('*note' => $n2, 'comment-number' => $wv, 'flag' => 'IsReplyFavourite', 'value' => 1));
            }
            $gj['edit-href']                 = jv('e2m_comment_edit', array('*note' => $n2, 'comment-number' => $wv));
            $gj['removed-toggle-href']       = jv('e2m_comment_flag_ajax', array('*note' => $n2, 'comment-number' => $wv, 'flag' => 'IsVisible', 'value' => !$q6['IsVisible']));
            $gj['removed-reply-toggle-href'] = jv('e2m_comment_flag_ajax', array('*note' => $n2, 'comment-number' => $wv, 'flag' => 'IsReplyVisible', 'value' => !$q6['IsVisible']));
            $gj['removed-href']              = jv('e2m_comment_flag_ajax', array('*note' => $n2, 'comment-number' => $wv, 'flag' => 'IsVisible', 'value' => 0));
            $gj['removed-reply-href']        = jv('e2m_comment_flag_ajax', array('*note' => $n2, 'comment-number' => $wv, 'flag' => 'IsReplyVisible', 'value' => 0));
            $gj['replaced-href']             = jv('e2m_comment_flag_ajax', array('*note' => $n2, 'comment-number' => $wv, 'flag' => 'IsVisible', 'value' => 1));
            $gj['replaced-reply-href']       = jv('e2m_comment_flag_ajax', array('*note' => $n2, 'comment-number' => $wv, 'flag' => 'IsReplyVisible', 'value' => 1));
            $i8                              = jv('e2m_comment_reply', array('*note' => $n2, 'comment-number' => $wv));
            if ($q6['Reply'] == '' or !$q6['IsReplyVisible']) {
                $gj['reply-href'] = $i8;
            } else {
                $gj['edit-reply-href'] = $i8;
            }
        }
    }
    if (mb_strlen($q6['Text']) > $_config['max_comment_length']) {
        $q6['Text'] = mb_substr($q6['Text'], 0, $_config['max_comment_length']);
    }
    $o8              = $n2['FormatterID'] === 'raw' ? 'neasden' : $n2['FormatterID'];
    $z1              = u3($o8, $q6['Text'], 'simple');
    $gj['text']      = $z1['text-final'];
    $gj['reply']     = '';
    $gj['replying?'] = (bool)false;
    $gj['replied?']  = (bool)((trim($q6['Reply']) != '') && ($q6['IsReplyVisible']));
    if ((string)$q6['Reply'] !== '') {
        $z1          = u3($n2['FormatterID'], $q6['Reply'], 'full');
        $gj['reply'] = $z1['text-final'];
    }
    if (Log::$cy) __log('Comments: done');
    return $gj;
}

function uf($xs)
{
    global $_config;
    xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Comments` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID` = '" . $xs . "'");
    $ib = en();
    if (count($ib) > 0) {
        return $ib[0];
    } else {
        return false;
    }
}

function if_($cl)
{
    global $_strings, $settings;
    $p8 = @$_COOKIE[b('commenter_name')];
    $cg = @$_COOKIE[b('commenter_email')];
    $vg = @$_COOKIE[b('commenter_ph')];
    $bg = false;
    if ($cg and $vg) {
        foreach (y2($cl) as $r8) {
            $n8 = md5($r8['ID'] . $r8['Stamp'] . 'x');
            if ($r8['AuthorEmail'] == $cg and $vg == $n8) {
                $bg = true;
                break;
            }
        }
    }
    $nr = $_strings['fb--submit'];
    $hh = kf();
    $oh = array('.note-id' => $cl['ID'], '.comment-id' => 'new', '.already-subscribed?' => (bool)$bg, 'cookie-name' => ef($cl['ID']), 'cookie-value' => rf(), 'email-field-name' => zf(), 'nospam-field-name-part-1' => substr($hh, 0, 4), 'nospam-field-name-part-2' => substr($hh, 4), 'create:edit?' => true, 'form-action' => jv('e2s_comment_process'), 'submit-text' => $nr, 'show-subscribe?' => (bool)!$bg, 'emailing-possible?' => MAIL_ENABLED, 'subscribe?' => (bool)$bg, 'subscription-status' => $bg ? $_strings['gs--you-are-already-subscribed'] : '', 'name' => htmlspecialchars($p8, ENT_COMPAT, HSC_ENC), 'email' => htmlspecialchars($cg, ENT_COMPAT, HSC_ENC), 'text' => '', 'email-comments-enabled?' => empty ($settings['comments']['require_gip']), 'gips' => array(),);
    $yg = false;
    $ng = '';
    foreach (e2_list_gips() as $mg) {
        if (!is_file(SYSTEM_FOLDER . 'gips/' . $mg . '.json')) {
            continue;
        }
        $fg              = e2_is_logged_in($mg);
        $oh['gips'][$mg] = (e2_get_gip_auth_url($mg));
        if ($fg) {
            $yg         = true;
            $dg         = e2_get_gip_session($mg);
            $ng         = $dg['GIP'];
            $oh['name'] = htmlspecialchars($dg['AuthorName'], ENT_COMPAT, HSC_ENC);
        }
    }
    if (!$oh['email-comments-enabled?'] and !count($oh['gips'])) {
        return false;
    }
    $oh['email-comments-only?'] = (count($oh['gips']) === 0);
    $oh['logged-in?']           = $yg;
    $oh['logged-in-gip']        = $ng;
    $oh['logout-url']           = $yg ? jv('e2m_gip_sign_out', array('provider' => E2GIP::get_logout_key())) : '';
    return $oh;
}

function of($dx)
{
    return c2($dx, '`IsNew` = 1');
}

function pf($dx)
{
    return c2($dx, '`IsVisible` = 1');
}

function c2($dx, $b)
{
    global $_config;
    if (!is_numeric($dx)) return 0;
    $sg = 0;
    xn("SELECT count(*) " . "FROM `" . $_config['db_table_prefix'] . "Comments` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `NoteID`=" . $dx . " " . "AND (" . $b . ")", 'count comments');
    $q1 = en();
    $q1 = (int)$q1[0]['count(*)'];
    $sg = $q1;
    return (int)$sg;
}

function v2()
{
    global $_config;
    if (Log::$cy) __log('Count new comments');
    $ag = 0;
    $qg = '';
    $tq = '';
    try {
        xn("SELECT `NoteID`, `Text` FROM `" . $_config['db_table_prefix'] . "Comments` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsNew`=1 ORDER BY `Stamp` DESC", 'count new comments for author menu');
        $q1 = en();
        $ag = count($q1);
        while ($ag-- > 0) {
            if ($n2 = mm($q1[$ag]['NoteID'])) {
                $tq = jv('e2m_note', ['*note' => $n2]);
                break;
            }
        }
        $ag++;
    } catch (AeMySQLException $e) {
        kv($e);
        if (Log::$cy) __log('Could not count new comments or provide link to the latest one');
    }
    return array((int)$ag, $qg, $tq);
}

function b2($dx)
{
    global $_config;
    if (Log::$cy) __log('Comments: getting comments for note ' . $dx);
    xn("SELECT c.*, g.`AuthorProfileLink` " . "FROM `" . $_config['db_table_prefix'] . "Comments` c " . "LEFT JOIN `" . $_config['db_table_prefix'] . "GIPsSessions` g " . "ON c.`SubsetID`=g.`SubsetID` " . "AND c.`GIP`=g.`GIP` " . "AND c.`GIPAuthorID`=g.`GIPAuthorID` " . "WHERE c.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND `NoteID`=" . @$dx . " " . "ORDER BY `Stamp`", 'get comments including deleted');
    $q1 = en();
    return $q1;
}

function y2($n2, $lg = '')
{
    global $_config;
    $v8 = "ORDER BY `ID` DESC";
    $d  = $zg = [];
    xn("SELECT DISTINCT `ID`, `Text`, `IsSubscriber`, `IsVisible`, " . "`AuthorName`, `AuthorEmail`, `Stamp` " . "FROM `" . $_config['db_table_prefix'] . "Comments` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `NoteID`=" . @$n2['ID'] . " " . "AND `IsSubscriber`=1 " . "AND `IsVisible`=1 " . "AND `AuthorEmail`!='" . rn($lg) . "' " . $v8, 'get subscribers by note');
    $q1 = en();
    foreach ($q1 as $r8) {
        if (!in_array($r8['AuthorEmail'], $zg)) {
            $d[] = $r8;
        }
        $zg[] = $r8['AuthorEmail'];
    }
    return $d;
}

function n2($n2, $or_ = NOTE_COMMENTABLE_NOW)
{
    global $settings, $_config;
    $kg = true;
    if (@$settings['comments']['fresh_only']) if (isset ($_config['comment_freshness_days'])) if ($n2['Stamp'] < time() - $_config['comment_freshness_days'] * SECONDS_IN_A_DAY) $kg = false;
    $xg = $n2['IsCommentable'];
    if ($or_ == NOTE_COMMENTABLE_NOW_CONDITIONALLY) {
        $xg = true;
    }
    return (rm($n2) === 'public' and $kg and $xg);
}

function e2_commentrec_with_parameters_($parameters = array())
{
    $n2 = $parameters['*note'];
    $d6 = b2($n2['ID']);
    $a6 = @$d6[$parameters['comment-number'] - 1];
    if ($a6) {
        $a6['noterec'] = $n2;
        return $a6;
    }
}

function f2()
{
    global $settings, $_config, $_fp_error;
    $_fp_error = false;
    $jh        = zf();
    $dx        = $uh = $name = $z3 = $tv = '';
    if (array_key_exists('note-id', $_POST)) $dx = trim(@$_POST['note-id']);
    if (array_key_exists('comment-id', $_POST)) $uh = trim(@$_POST['comment-id']);
    if (array_key_exists('comment-number', $_POST)) $wv = trim(@$_POST['comment-number']);
    if (array_key_exists('name', $_POST)) $name = trim(@$_POST['name']);
    if (array_key_exists($jh, $_POST)) $z3 = trim(@$_POST[$jh]);
    if (array_key_exists('text', $_POST)) $tv = trim(@$_POST['text']);
    $eg = vn('Comments');
    if (stripos($eg['Collation'], 'utf8mb4') !== 0) {
        $name = nb($name);
        $tv   = nb($tv);
    }
    if ($uh == 'new') {
        $rg = e2_get_logged_gip_name();
        if ($rg) {
            $dg   = e2_get_gip_session($rg);
            $name = trim($dg['AuthorName']);
            $z3   = '';
            $tg   = $dg['GIPAuthorID'];
        }
    } else {
        if (array_key_exists('gip', $_POST)) $rg = trim(@$_POST['gip']);
    }
    $jg         = ((array_key_exists('already-subscribed', $_POST) and $_POST['already-subscribed']) or (array_key_exists('subscribe', $_POST) and $_POST['subscribe']));
    $hg         = time();
    $m8['text'] = $tv;
    if ($uh == 'new' and !$rg) {
        y('commenter_name', $name);
        y('commenter_email', $z3);
    }
    $gg = ($uh == 'new' and (tf() or e2_cookie_data_is_spam_suspicios_for_note_id_($dx)));
    $wg = 1;
    $q1 = false;
    if (!is_numeric($dx)) {
        $_fp_error = FP_NO_ID_OR_NEW;
    } elseif (!is_numeric($uh) and !($uh == 'new')) {
        $_fp_error = FP_NO_ID_OR_NEW;
    } else {
        if ($tv == '' or (!$rg and ($name == '' or $z3 == ''))) {
            $_fp_error = FP_EMPTY_FIELD;
        }
        if ($uh == 'new') {
            $ug = @unserialize(file_get_contents(USER_FOLDER . '/last-comment.psa'));
            if (md5($name . $z3 . $tv) == $ug['md5']) {
                $_fp_error = FP_COMMENT_DOUBLE_POST;
            }
            if (isset ($_config['max_comment_length']) and strlen(@$_POST['text']) > ($_config['max_comment_length'])) {
                $_fp_error = FP_COMMENT_TOO_LONG;
            }
            $n2 = mm($dx);
            if ($uh == 'new' and !n2($n2)) {
                $_fp_error = FP_NOT_COMMENTABLE;
            }
            if ($gg) {
                $_fp_error = FP_COMMENT_SPAM_SUSPECT;
            }
        }
    }
    if (!$_fp_error) {
        e2_drop_caches_for_note_($dx, true);
        if ($uh == 'new') {
            $a6 = array('NoteID' => (int)$dx, 'AuthorName' => $name, 'AuthorEmail' => $z3, 'Text' => $tv, 'Reply' => '', 'IsVisible' => 1, 'IsAnswerAware' => 1, 'IsSubscriber' => (int)$jg, 'IsSpamSuspect' => (int)$gg, 'IsNew' => (int)$wg, 'Stamp' => (int)time(), 'LastModified' => (int)time(), 'IP' => rn(q2()), 'IsGIPUsed' => intval(!empty ($rg) && !empty ($tg)), 'GIP' => !empty ($rg) ? rn($rg) : '', 'GIPAuthorID' => !empty ($tg) ? rn($tg) : '',);
            $a6 = yn('Comments', $a6);
            $uh = $a6['ID'];
            $ug = array('id' => $uh, 'md5' => md5($name . $z3 . $tv),);
            @n3(USER_FOLDER . 'last-comment.psa', serialize($ug));
            $q1 = (int)$uh;
            $ig = md5($a6['ID'] . $a6['Stamp'] . 'x');
            y('commenter_ph', $ig);
            $n2                          = mm($dx);
            $ie                          = jv('e2m_note', array('*note' => $n2));
            $gj['comment-time']          = array($hg, ay());
            $gj['commenter']             = $name;
            $gj['commenter-email']       = $z3;
            $gj['comment-text']          = $tv;
            $gj['note-title']            = $n2['Title'];
            $gj['note-href']             = $ie;
            $gj['comment-href']          = $ie;
            $gj['comments-disable-href'] = jv('e2m_note_flag', array('*note' => $n2, 'flag' => 'IsCommentable', 'value' => 0));
            $gj['reply-href']            = jv('e2m_comment_reply', array('*note' => $n2, 'comment-number' => $wv));
            if (isset ($settings['author_email']) and @$settings['notifications']['new_comments']) {
                $z8 = f1('comment-new-to-author', $gj);
                $k8 = e2l_get_string('em--comment-new-to-author-subject', $gj);
                $x8 = $settings['author_email'];
                $e8 = 'From: ' . d1() . "\r\n" . 'Reply-to: ' . $name . ' <' . $z3 . ">";
                s1($x8, $k8, $z8, $e8);
            }
            if (!$gg) {
                unset ($gj['commenter-email']);
                $e8 = 'From: ' . d1();
                foreach (y2($n2, $z3) as $r8) {
                    $t8                     = $r8['AuthorEmail'];
                    $j8                     = md5($r8['ID'] . $r8['Stamp'] . 'x');
                    $gj['unsubscribe-href'] = jv('e2m_unsubscribe', array('*note' => $n2, 'unsubscribe-email' => $t8, 'unsubscribe-key' => $j8));
                    $x8                     = $t8;
                    $z8                     = f1('comment-new-to-public', $gj);
                    $k8                     = e2l_get_string('em--comment-new-to-public-subject', $gj);
                    s1($x8, $k8, $z8, $e8);
                }
            }
        } else {
            $og = array('ID' => $uh, 'Text' => $tv, 'IsSubscriber' => ((int)$jg), 'LastModified' => time(),);
            if (!empty ($name)) $og['AuthorName'] = $name;
            if (!empty ($z3)) $og['AuthorEmail'] = $z3;
            nn('Comments', $og);
            $q1 = (int)$uh;
        }
    }
    return array((int)$dx, $q1, $m8);
}

function e2m_most_commented($parameters = [])
{
    global $settings, $_strings, $_config;
    $we                = k2();
    $mostCommentedView = new AePageableNotesView ('e2m_most_commented', $parameters);
    $mostCommentedView->setPortionSize($settings['appearance']['notes_per_page']);
    $mostCommentedView->setNextPrevPageTitles($_strings['gs--earlier'], $_strings['gs--later']);
    $mostCommentedView->setWantNewCommentsCount($we);
    $mostCommentedView->setWantReadHrefs($_config['count_reads']);
    $mostCommentedView->setWantControls($we and !@$_config['read_only']);
    $mostCommentedView->setWantHiddenTags($we);
    $pg = $_config['hot_period'];
    $cw = time() - d2($_config['hot_period']);
    $mostCommentedView->setLimitlessSQLRequest("SELECT * " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=1 " . tm($we) . "AND `ID` IN ( " . "SELECT `NoteID` FROM ( " . "SELECT `NoteID`, COUNT(*) `CommentsCount` " . "FROM `" . $_config['db_table_prefix'] . "Comments` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsVisible` = 1 " . "AND `Stamp` > " . $cw . " " . "GROUP BY `NoteID` " . "ORDER BY `CommentsCount` DESC " . ") As MostCommentedNotesIDs " . ")");
    $d = ['title' => e2l_get_string('pt--most-commented', ['period' => $pg]), 'heading' => e2l_get_string('pt--most-commented', ['period' => $pg]), 'notes' => $mostCommentedView->getNotesCTree(), 'pages' => $mostCommentedView->getPagesCTree(),];
    if ($mostCommentedView->isFirstPageOfEmptyView()) {
        $d['nothing'] = $_strings['gs--no-such-notes'];
    } elseif (!$mostCommentedView->isExistingPage()) {
        return e2_error404_mode();
    }
    return $d;
}

function e2m_favourites($parameters = [])
{
    global $settings, $_config, $_strings;
    $we             = k2();
    $favouritesView = new AePageableNotesView ('e2m_favourites', $parameters);
    $favouritesView->setPortionSize($settings['appearance']['notes_per_page']);
    $favouritesView->setNextPrevPageTitles($_strings['gs--earlier'], $_strings['gs--later']);
    $favouritesView->setWantPaging(true);
    $favouritesView->setWantNewCommentsCount($we);
    $favouritesView->setWantReadHrefs($_config['count_reads']);
    $favouritesView->setWantControls($we and !@$_config['read_only']);
    $favouritesView->setWantHiddenTags($we);
    $favouritesView->setLimitlessSQLRequest("SELECT * " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=1 " . "AND `IsFavourite`=1 " . tm($we) . "ORDER BY `Stamp` DESC");
    $r6 = $_strings['pt--favourites'];
    if ($parameters['page'] > 1) {
        $r6 .= ' (' . $_strings['gs--page'] . ' ' . $parameters['page'] . ')';
    }
    $d = ['title' => $r6, 'heading' => $_strings['pt--favourites'], 'notes' => $favouritesView->getNotesCTree(), 'pages' => $favouritesView->getPagesCTree(),];
    if ($favouritesView->isFirstPageOfEmptyView()) {
        $d['nothing'] = $_strings['gs--no-favourites'];
    } elseif (!$favouritesView->isExistingPage()) {
        return e2_error404_mode();
    }
    return $d;
}

function d2($pg)
{
    if ('year' == $pg) return SECONDS_IN_A_YEAR; elseif ('month' == $pg) return SECONDS_IN_A_MONTH;
    elseif ('week' == $pg) return SECONDS_IN_A_DAY * 7;
    elseif ('day' == $pg) return SECONDS_IN_A_DAY;
    else return PHP_INT_MAX;
}

function e2m_popular($parameters = [])
{
    global $settings, $_config, $_strings;
    $we          = k2();
    $popularView = new AePageableNotesView ('e2m_popular', $parameters);
    $popularView->setPortionSize($settings['appearance']['notes_per_page']);
    $popularView->setNextPrevPageTitles($_strings['gs--earlier'], $_strings['gs--later']);
    $popularView->setWantNewCommentsCount($we);
    $popularView->setWantReadHrefs($_config['count_reads']);
    $popularView->setWantControls($we and !@$_config['read_only']);
    $popularView->setWantHiddenTags($we);
    $pg = $_config['popular_period'];
    if ($pg === 'ever') {
        $popularView->setLimitlessSQLRequest("SELECT * " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished` = 1 " . tm($we) . "ORDER BY `ReadCount` DESC");
    } else {
        $cw = time() - d2($_config['popular_period']);
        $bj = ("FROM `" . $_config['db_table_prefix'] . "Actions` a, " . "`" . $_config['db_table_prefix'] . "Notes` n " . "WHERE a.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND n.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND a.`Stamp` > " . $cw . " " . "AND n.`IsPublished` = 1 " . tm($we) . "AND a.`EntityID` = n.`ID` " . "GROUP BY a.`EntityID`");
        $popularView->setSQLCountRequest("SELECT COUNT(*) Total FROM (SELECT 1 " . $bj . ") _");
        $popularView->setLimitlessSQLRequest("SELECT n.*, a.`EntityID`, SUM(a.`ReadCount`) `AggregateReadCount` " . $bj . " " . "ORDER BY `AggregateReadCount` DESC");
    }
    $d = ['title' => e2l_get_string('pt--most-read', ['period' => $pg]), 'heading' => e2l_get_string('pt--most-read', ['period' => $pg]), 'notes' => $popularView->getNotesCTree(), 'pages' => $popularView->getPagesCTree(),];
    if ($popularView->isFirstPageOfEmptyView()) {
        $d['nothing'] = $_strings['gs--no-such-notes'];
    } elseif (!$popularView->isExistingPage()) {
        return e2_error404_mode();
    }
    return $d;
}

function s2($kj = false, $ax = [])
{
    global $_config, $_current_url;
    $vw                          = $bw = '';
    $mostReadNotesCollectionView = new AeArbitraryNotesCollectionView ('most read or most read by tag');
    $mostReadNotesCollectionView->setCurrentURL($_current_url);
    $mostReadNotesCollectionView->setFilterOutIDs($ax);
    $cw = time() - d2($_config['popular_period']);
    $mostReadNotesCollectionView->setSQLRequest("SELECT n.*, a.`EntityID`, SUM(a.`ReadCount`) `AggregateReadCount` " . "FROM `" . $_config['db_table_prefix'] . "Actions` a, " . "`" . $_config['db_table_prefix'] . "Notes` n " . $vw . "WHERE a.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND n.`SubsetID`=" . $_config['db_table_subset'] . " " . $bw . "AND a.`Stamp` > " . $cw . " " . "AND n.`IsPublished` = 1 " . "AND n.`IsFavourite` = 1 " . tm(k2()) . "AND a.`EntityID` = n.`ID` " . "GROUP BY a.`EntityID` " . "ORDER BY `IsFavourite` DESC, `AggregateReadCount` DESC " . "LIMIT 10");
    if ($kj === false) {
        if (CACHE_POPULAR) {
            $mostReadNotesCollectionView->setViewExpiration(SECONDS_IN_A_DAY);
            $mostReadNotesCollectionView->setCacheFilename(CACHE_FILENAME_POPULAR);
            $mostReadNotesCollectionView->setCacheExpiresFilename(CACHE_FILENAME_POPULAR_EXPIRES);
        }
    } else {
        if (CACHE_POPULAR_WITH_TAG) {
            $mostReadNotesCollectionView->setViewExpiration(SECONDS_IN_A_DAY);
            $mostReadNotesCollectionView->setCacheFilename(e2_cache_filename_with_id_($kj, CACHE_FILENAMES_POPULAR_WITH_TAG));
            $mostReadNotesCollectionView->setCacheExpiresFilename(e2_cache_filename_with_id_($kj, CACHE_FILENAMES_POPULAR_WITH_TAG_EXPIRES));
        }
    }
    return $mostReadNotesCollectionView->getNotesCTree();
}

function a2($kj = false, $ax = [])
{
    global $_strings;
    $yw         = ['title' => $_strings['nm--most-read'],];
    $yw['each'] = s2($kj, $ax);
    if ($kj) {
        $yw['seed'] = $kj;
    }
    if (count($yw['each']) < 7) {
        return [];
    }
    return $yw;
}

function e2m_password_reset()
{
    global $_strings, $_superconfig, $settings;
    if (!is_file(USER_FOLDER . 'password-reset.psa')) {
        $rm = sha1(rand());
        $sm = jv('e2m_password', array('recovery-key' => $rm));
        @n3(USER_FOLDER . 'password-reset.psa', $sm);
    }
    $d['title']                     = $_strings['pt--password-reset'];
    $d['heading']                   = $_strings['pt--password-reset'];
    $nw                             = (bool)($x8 = $settings['author_email']);
    $d['form']                      = 'form-password-reset-email';
    $d['form-password-reset-email'] = array('form-action' => jv('e2s_password_reset_email'), 'show-controls?' => $nw, 'submit-text' => $_strings['fb--send-link-by-email'],);
    if (!@$_superconfig['user_has_no_access_to_files']) {
        $d['form-password-reset-email']['reset-info'] = $_strings['gs--password-reset-link-saved'];
    } elseif (!$nw) {
        mv($_strings['er--cannot-reset-password']);
    }
    return $d;
}

function e2s_password_reset_email()
{
    global $_strings, $settings;
    if ($_SERVER['REQUEST_METHOD'] != 'POST') c();
    if (array_key_exists('email', $_POST)) $z3 = trim($_POST['email']);
    if (!$z3) {
        mv($_strings['er--cannot-send-link-email-empty']);
        c(jv('e2m_password_reset'));
    }
    $mw = @file_get_contents(USER_FOLDER . 'password-reset.psa');
    if (strlen($mw) == 0) {
        mv($_strings['er--error-occurred']);
        c(jv('e2m_password_reset'));
    }
    if ($x8 = $settings['author_email']) {
        if ($z3 == $x8) {
            $z8 = f1('password-reset', array('reset-href' => $mw));
            $k8 = $_strings['em--password-reset-subject'];
            $e8 = 'From: ' . d1();
            s1($x8, $k8, $z8, $e8);
        }
        mv($_strings['gs--password-reset-link-sent-maybe'], E2E_MESSAGE);
        c(jv('e2m_password_reset'));
    }
    die;
}

function e2m_password($parameters)
{
    global $settings, $_strings;
    $fw = false;
    $rm = '';
    if (array_key_exists('recovery-key', $parameters)) {
        $rm = $parameters['recovery-key'];
        $sm = jv('e2m_password', array('recovery-key' => $rm));
        $mw = @file_get_contents(USER_FOLDER . 'password-reset.psa');
        if (strlen($mw) > 0) {
            $fw = ($sm == $mw);
        }
    }
    if (k2() or $fw) {
        $d['title']   = $_strings['pt--password'];
        $d['heading'] = $_strings['pt--password-for-blog'];
        if ($fw) {
            $d['title']   = $_strings['pt--password-reset'];
            $d['heading'] = $_strings['pt--password-reset'];
        }
        $d['form']          = 'form-password';
        $d['form-password'] = array('form-action' => jv('e2s_password_save'), '.recovery-key' => $rm, 'recovering?' => $fw, 'submit-text' => $_strings['fb--change'],);
        return $d;
    } else {
        c();
    }
}

function e2m_sessions()
{
    global $settings, $_strings;
    $dk           = x2();
    $d['title']   = $_strings['pt--sessions'];
    $d['heading'] = $_strings['pt--sessions'];
    $dw           = array();
    $rm           = $_COOKIE[b('key')];
    foreach ($dk['sessions'] as $t => $xf) {
        $dw[] = array('current?' => sha1($rm) === $xf['key_hash'], 'opened' => array((int)$xf['stamp'], sy()), 'ip-address' => $xf['remote_ip'], 'source' => ($xf['remote_ip'] == '127.0.0.1') ? $_strings['gs--locally'] : $xf['remote_ip'], 'title' => j2($xf['ua']), 'user-agent' => $xf['ua'] ? $xf['ua'] : $_strings['gs--unknown'],);
    }
    $dw                    = array_reverse($dw);
    $d['sessions']['each'] = $dw;
    if (count($dw) > 1) {
        $d['form']          = 'form-sessions';
        $d['form-sessions'] = array('form-action' => jv('e2s_drop_other_sessions'), 'submit-text' => $_strings['fb--end-all-sessions-but-this'],);
    }
    return $d;
}

function e2m_sign_in()
{
    if (k2()) {
        c(jv('e2m_frontpage', array('page' => 1)));
    } else {
        return array();
    }
}

function e2m_sign_out()
{
    global $_strings;
    $dk = x2();
    $sw = -1;
    if (array_key_exists('sessions', $dk) and is_array($dk['sessions'])) {
        foreach ($dk['sessions'] as $t => $xf) {
            $rm = $_COOKIE[b('key')];
            if (sha1($rm) === $xf['key_hash']) {
                $sw = $t;
                break;
            }
        }
    }
    if ($sw > -1) unset ($dk['sessions'][$sw]);
    if (!e2_($dk)) {
        mv($_strings['er--cannot-write-auth-data'], E2E_PERMISSIONS_ERROR);
    }
    y('key', '');
    c();
}

function e2s_password_save()
{
    global $settings, $_strings;
    $fw = false;
    $aw = trim($_POST['old-password']);
    if ($rm = trim($_POST['recovery-key'])) {
        $sm = jv('e2m_password', array('recovery-key' => $rm));
        $mw = @file_get_contents(USER_FOLDER . 'password-reset.psa');
        if (strlen($mw) > 0) {
            $fw = ($sm == $mw);
        }
    }
    if (l2($aw) or $fw) {
        $fk = trim($_POST['new-password']);
        if ($fk != '') {
            if (@n3(USER_FOLDER . '/password-hash.psa', serialize(sha1($fk)))) {
                @unlink(USER_FOLDER . 'password-reset.psa');
                mv($_strings['gs--password-changed'], E2E_MESSAGE);
                c();
            } else {
                mv($_strings['er--could-not-change-password'], E2E_PERMISSIONS_ERROR);
                c(jv('e2m_password', array('recovery-key' => '')));
            }
        } else {
            mv($_strings['er--no-password-entered'], E2E_USER_ERROR);
            c(jv('e2m_password', array('recovery-key' => '')));
        }
    } else {
        mv($_strings['er--wrong-password'], E2E_USER_ERROR);
        c(jv('e2m_password', array('recovery-key' => '')));
    }
    die;
}

function q2()
{
    $gv = $_SERVER['REMOTE_ADDR'];
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
        $gv = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
    }
    return $gv;
}

function e2s_sign_in()
{
    global $_strings;
    $dk = x2();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $qw = @$_POST['password'];
        $lw = @$_POST['is_public_pc'];
    } else {
        $qw = @$_GET['password'];
        $lw = false;
    }
    if (l2($qw)) {
        @unlink(USER_FOLDER . 'password-reset.psa');
        $zw               = array('stamp' => time(), 'remote_ip' => q2(), 'key_hash' => z2($lw), 'ua' => $_SERVER['HTTP_USER_AGENT'],);
        $dk['sessions'][] = $zw;
    } elseif (strlen(trim($qw)) > 0) {
        g2();
        mv($_strings['er--wrong-password'], E2E_USER_ERROR);
    }
    if (!e2_($dk)) {
        mv($_strings['er--cannot-write-auth-data'], E2E_PERMISSIONS_ERROR);
        c();
    }
    v();
}

function e2s_drop_other_sessions()
{
    global $_strings;
    $dk = x2();
    foreach ($dk['sessions'] as $t => $xf) {
        $rm = $_COOKIE[b('key')];
        if (sha1($rm) === $xf['key_hash']) {
            $zw = $xf;
            break;
        }
    }
    $dk['sessions'] = array($zw);
    if (!e2_($dk)) {
        mv($_strings['er--cannot-write-auth-data'], E2E_PERMISSIONS_ERROR);
    }
    v();
    die;
}

function l2($qw)
{
    $kw = @unserialize(file_get_contents(USER_FOLDER . '/password-hash.psa'));
    return (sha1($qw) === $kw and trim($qw) != '');
}

function z2($xw = false)
{
    global $settings;
    $rm = h2();
    $ew = sha1($rm);
    y('key', $rm, !$xw);
    return $ew;
}

function k2()
{
    global $nn, $settings, $_auth_sessions;
    if (isset ($nn)) return $nn;
    $nn = false;
    if (isset ($_COOKIE[b('key')])) {
        $rm = $_COOKIE[b('key')];
        $dk = x2();
        $rw = array();
        if (array_key_exists('sessions', $dk) and is_array($dk['sessions'])) {
            foreach ($dk['sessions'] as $zw) {
                $rw[] = $zw['key_hash'];
            }
            $_auth_sessions['count'] = count($dk['sessions']);
        }
        if (1) {
            $nn = (bool)in_array(sha1($rm), $rw, true);
        }
        if (!$nn) {
            y('key', '');
        }
    }
    return $nn;
}

function x2()
{
    if (is_file(USER_FOLDER . 'auth.psa')) {
        $dk = unserialize(@file_get_contents(USER_FOLDER . 'auth.psa'));
        if ($dk) return $dk;
    }
    return array();
}

function e2_($dk)
{
    return n3(USER_FOLDER . 'auth.psa', serialize($dk));
}

function r2()
{
    if ($rm = @$_COOKIE[b('key')]) {
        return b('key') . '=' . $rm . "";
    }
}

function t2()
{
    if ($rm = @$_COOKIE[b('key')]) {
        return 'Cookie: ' . b('key') . '=' . $rm . "\r\n";
    }
    return "\r\n";
}

function j2($xv)
{
    global $_strings;
    if (strstr($xv, 'iPhone')) return $_strings['gs--ua-iphone'];
    if (strstr($xv, 'iPad')) return $_strings['gs--ua-ipad'];
    if (strstr($xv, 'Opera')) $d = $_strings['gs--ua-opera'];
    if (strstr($xv, 'Firefox')) $d = $_strings['gs--ua-firefox'];
    if (strstr($xv, 'Chrome')) $d = $_strings['gs--ua-chrome'];
    if (strstr($xv, 'Safari') and !strstr($xv, 'Chrome')) $d = $_strings['gs--ua-safari'];
    if (!$d) $d = $_strings['gs--ua-unknown'];
    if (strstr($xv, 'Macintosh')) {
        if ($d) $d .= ' ' . $_strings['gs--ua-for-mac'];
    }
    return $d;
}

function e2j_check_password()
{
    $kw = @unserialize(file_get_contents(USER_FOLDER . '/password-hash.psa'));
    $qw = '';
    if (array_key_exists('password', $_POST)) $qw = $_POST['password'];
    g2();
    $zv = ['success' => true, 'data' => ['password-correct' => trim($qw) !== '' and sha1($qw) === $kw],];
    $zv = json_encode($zv);
    die ($zv);
}

function h2()
{
    $tw = '';
    $jw = '0123456789abcdef';
    for ($r = 0; $r < 40; $r++) $tw .= $jw[mt_rand(0, 15)];
    $tw .= time();
    $tw = sha1($tw);
    return $tw;
}

function g2()
{
    if (is_file(USER_FOLDER . 'password-wait.psa')) {
        $hw = unserialize(file_get_contents(USER_FOLDER . '/password-wait.psa'));
        if ($hw['delay'] < 5) {
            $hw['delay']++;
        }
        if (time() - $hw['time'] > SECONDS_IN_A_MINUTE) {
            $hw['delay'] = 0;
        }
        $hw['time'] = time();
    } else {
        $hw = array('time' => time(), 'delay' => 5,);
    }
    n3(USER_FOLDER . 'password-wait.psa', serialize($hw));
    sleep($hw['delay']);
}

function w2()
{
    static $gw;
    if (empty($gw)) $gw = md5('seсret');
    return $gw;
}

function u2($x)
{
    $rm = w2();
    $ww = strlen($rm);
    $uw = strlen($x);
    $d  = '';
    for ($r = 0; $r < $uw + rand(16, 64); ++$r) {
        if ($r > $uw) {
            $iw = rand(0, 127);
        } elseif ($r == $uw) {
            $iw = 0;
        } else {
            $iw = ord($x[$r]);
        }
        $ow = chr(($iw + ord($rm[$r % $ww])) % 256);
        $d  .= $ow;
    }
    $d = base64_encode($d);
    return $d;
}

function i2($x)
{
    $rm = w2();
    $ww = strlen($rm);
    $x  = base64_decode($x);
    $uw = strlen($x);
    $d  = '';
    for ($r = 0; $r < $uw; ++$r) {
        $pw = (ord($x[$r]) + 256 - ord($rm[$r % $ww])) % 256;
        if ($pw === 0) break;
        $d .= chr($pw);
    }
    return $d;
}

function o2()
{
    global $settings;
    if (k2()) {
        return null;
    } else {
        return ['form-action' => jv('e2s_sign_in'), 'form-check-password-action' => jv('e2j_check_password'), 'login-name' => @$settings['author'], 'public-pc?' => false, 'reset-href' => jv('e2m_password_reset'),];
    }
}

$_candies_installer                = array('e2s_build', 'e2m_info', 'e2m_install', 'e2j_check_db_config', 'e2j_list_databases', 'e2s_instantiate', 'e2s_install', 'e2s_update_perform',);
$_candies_public                   = array('e2m_info', 'e2m_frontpage', 'e2m_rss', 'e2m_json', 'e2m_note', 'e2m_note_json', 'e2m_note_read', 'e2m_tags', 'e2m_tag', 'e2m_untagged', 'e2m_tag_rss', 'e2m_tag_json', 'e2m_popular', 'e2m_favourites', 'e2m_most_commented', 'e2m_found', 'e2m_comments', 'e2m_everything', 'e2m_sitemap_xml', 'e2m_year', 'e2m_month', 'e2m_day', 'e2m_unsubscribe', 'e2m_theme_preview', 'e2m_password_reset', 'e2s_password_reset_email', 'e2m_password', 'e2s_password_save', 'e2s_sign_in', 'e2m_sign_out', 'e2m_gip_sign_in', 'e2m_gip_sign_in_callback', 'e2m_gip_sign_out', 'e2s_comment_process', 'e2s_search', 'e2s_bsi_step', 'e2j_check_password', 'e2s_retrieve', 'e2s_notify', 'e2s_dump',);
$_candies_to_disallow_in_read_only = array('e2m_write', 'e2m_note_edit', 'e2s_note_process', 'e2s_note_publish', 'e2s_note_delete', 'e2m_note_flag_favourite', 'e2m_note_flag', 'e2m_comment_edit', 'e2m_comment_delete', 'e2m_comment_reply', 'e2m_comment_reply_delete', 'e2m_comment_flag', 'e2m_comment_flag_ajax', 'e2m_unsubscribe', 'e2s_comment_process', 'e2m_settings', 'e2m_timezone',);
$_candies_public                   = array_merge($_candies_public, $_candies_installer);
$_candies_indexable                = array('e2m_note',);
$_candies_indexable_conditionally  = array('e2m_frontpage', 'e2m_tag', 'e2m_favourites', 'e2m_most_commented', 'e2m_found', 'e2m_tags', 'e2m_everything',);
$_candies_ajax                     = array('e2j_check_db_config', 'e2j_list_databases', 'e2j_check_password', 'e2j_userpic_upload', 'e2j_userpic_remove', 'e2j_file_upload', 'e2j_file_remove', 'e2j_note_livesave', 'e2m_note_flag_favourite', 'e2m_comment_flag_ajax', 'e2m_tag_flag_ajax',);
function p2()
{
    global $settings, $_lang, $_config, $_strings, $c;
    if (Log::$cy) __log('Blog information');
    $cu['author'] = htmlspecialchars(vd(), ENT_NOQUOTES, HSC_ENC);
    if (array_key_exists('blog_subtitle', $settings)) {
        $z1                         = i3($settings['blog_subtitle'], 'full');
        $s3                         = $z1['text-final'];
        $cu['subtitle']             = $s3;
        $cu['subtitle-format-info'] = $z1['meta'];
        va(@$z1['meta']['links-required']);
    }
    $cu['title']               = htmlspecialchars(cd(), ENT_NOQUOTES, HSC_ENC);
    $cu['userpic-set?']        = false;
    $cu['userpic-changeable?'] = k2();
    if ($cu['userpic-href'] = bd()) {
        $cu['userpic-set?']            = true;
        $cu['userpic-large-href']      = bd('large');
        $cu['userpic-square-href']     = bd('square');
        $cu['userpic-changeable-href'] = $cu['userpic-href'];
    } else {
        unset ($cu['userpic-href']);
    }
    if (k2()) {
        $cu['userpic-upload-action'] = jv('e2j_userpic_upload');
        $cu['userpic-remove-action'] = jv('e2j_userpic_remove');
    }
    $cu['href']                   = jv('e2m_frontpage', array('page' => 1));
    $cu['rss-href']               = jv('e2m_rss');
    $cu['jsonfeed-href']          = jv('e2m_json');
    $cu['language']               = $_lang;
    $cu['show-subscribe-button?'] = false;
    $rb                           = array(time(), ay());
    $vu                           = xy('Y', $rb[0]);
    $cu['now']                    = $rb;
    $bu                           = $vu;
    $yu                           = bs('start');
    if (array_key_exists('stamp', $yu)) {
        $bu               = xy('Y', $yu['stamp']);
        $cu['start-time'] = array((int)$yu['stamp'], $yu['timezone']);
    }
    $nu = false;
    $mu = xm(true, true);
    if ($mu !== null) {
        if (k2()) {
            $fu = xm(true, false);
            if ($fu !== null) {
                $nu = ($mu + $fu == 0);
            }
        } else {
            $nu = ($mu == 0);
        }
    }
    $cu['notes-count'] = (int)$mu;
    $cu['virgin?']     = $nu;
    $du                = $_config['years_range_separator'] ? $_config['years_range_separator'] : $_strings['gs--range-separator'];
    $cu['years-range'] = $bu . (($bu == $vu) ? '' : ($du . $vu));
    if ($c) {
        $cu['parent-site-href'] = substr($c, (int)strpos('/', $c));
    }
    return $cu;
}

function cd()
{
    global $settings, $_strings;
    if (array_key_exists('blog_title', $settings) and trim($settings['blog_title']) != '') {
        return trim($settings['blog_title']);
    } else {
        return $_strings['e2--default-blog-title'];
    }
}

function vd()
{
    global $settings, $_strings;
    if (array_key_exists('author', $settings) and trim($settings['author']) != '') {
        return trim($settings['author']);
    } else {
        return $_strings['e2--default-blog-author'];
    }
}

function bd($size = '')
{
    global $full_blog_url;
    $su = false;
    if (is_file(USER_FOLDER . 'userpic@2x.jpg')) {
        $fb = USER_FOLDER . 'userpic@2x.jpg';
        $su = USER_FOLDER_URLPATH . 'userpic@2x.jpg';
    } elseif (is_file(USER_FOLDER . 'userpic@2x.png')) {
        $fb = USER_FOLDER . 'userpic@2x.png';
        $su = USER_FOLDER_URLPATH . 'userpic@2x.png';
    }
    if ($size == 'large' and is_file(USER_FOLDER . 'userpic-large@2x.jpg')) {
        $fb = USER_FOLDER . 'userpic-large@2x.jpg';
        $su = USER_FOLDER_URLPATH . 'userpic-large@2x.jpg';
    } elseif ($size == 'square' and is_file(USER_FOLDER . 'userpic-square@2x.jpg')) {
        $fb = USER_FOLDER . 'userpic-square@2x.jpg';
        $su = USER_FOLDER_URLPATH . 'userpic-square@2x.jpg';
    }
    if ($su === false) return false;
    $ry = stat($fb);
    if ($ry['mtime']) $su .= '?' . $ry['mtime'];
    $su = $full_blog_url . '/' . $su;
    return $su;
}

function yd()
{
    global $_config, $_stopwatch, $cz;
    $au = round(w() - $_stopwatch, 3);
    return ['show?' => ($_config['display_stat'] > (int)!k2()), 'generation-time' => str_replace('.', ',', $au), 'peak-memory-mb' => str_replace('.', ',', round((memory_get_peak_usage() / 1024 / 1024) * 100) / 100), 'db-query-count' => (int)@$cz,];
}

function e2m_info()
{
    global $settings, $_config, $v, $c, $_template;
    $hv = array('E2_VERSION' => E2_VERSION, 'E2_RELEASE' => E2_RELEASE, 'E2_UA_STRING' => E2_UA_STRING, '---', 'PHP_VERSION' => PHP_VERSION, '---', 'installed' => (fn_() !== null), 'server_name' => $v, 'folder_on_server' => $c, '---', 'default formatter' => $_config['default_formatter'], '---', 'theme' => $settings['template'], '---', 'Olba name' => $_template['name'], 'Olba max_image_width' => $_template['max_image_width'], 'Olba stack' => $_template['stack'], '---', 'Neasden' => substr(md5(file_get_contents('system/neasden/neasden.php')), 0, 4), '---',);
    echo '<pre>';
    foreach ($hv as $t => $xf) {
        if ($xf == '---') {
            echo "\n";
            continue;
        }
        echo str_pad($t, 24);
        echo '\'';
        print_r($xf);
        echo '\'';
        echo "\n";
    }
    echo '</pre>';
    die;
}

function e2s_notify()
{
    global $_config;
    if ($_config['holborn']) {
        $qu = @$_GET['src'];
        if ($qu == '') {
            if (Log::$cy) __log('Holborn: No src URL');
            die;
        }
        $lu = file_get_contents($qu);
        $lu = dd($lu);
        $zu = json_decode($lu, true);
        if (!$zu) {
            if (Log::$cy) __log('Holborn: No meaningful info from ' . $qu . ' (' . json_last_error() . ')');
            if ($ku = md($qu)) {
                if (Log::$cy) __log('Holborn: Delete note with ID ' . $ku['ID']);
                nm($ku['ID']);
            }
            die;
        }
        nd($zu, $qu);
    }
    die;
}

function e2m_sources($parameters)
{
    global $_config;
    $xu = $_GET['ord'];
    if (!$xu) $xu = 'ID';
    $xu = "`" . rn($xu) . "`";
    xn("SELECT *, REPLACE(REPLACE(REPLACE(`URL`, 'http://', ''), 'https://', ''), 'www.', '') AS _URLX " . "FROM `" . $_config['db_table_prefix'] . "Sources` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "ORDER BY " . $xu);
    $q1 = en();
    foreach ($q1 as $bm) {
        $eu = $bm['ID'];
        if ($bm['ID'] != $bm['TrueID']) $eu .= '<br />' . $bm['TrueID'];
        $g7 = array('id' => $eu, 'userpic-href' => $bm['PictureURL'], 'href' => $bm['URL'], 'href-display' => str_replace('/', '/<wbr>', $bm['URL']), 'href-filtered' => str_replace('/', '/<wbr>', $bm['_URLX']), 'title' => $bm['Title'], 'author' => $bm['AuthorName'], 'true?' => $bm['ID'] == $bm['TrueID'], 'whitelisted?' => (bool)$bm['IsWhiteListed'], 'trusted?' => (bool)$bm['IsTrusted'],);
        if (!$bm['IsTrusted']) {
            $g7['trust-url'] = jv('e2m_source_trust', array('source' => $bm['ID']));
        }
        if ($bm['IsTrusted']) {
            $g7['premoderate-url'] = jv('e2m_source_premoderate', array('source' => $bm['ID']));
        }
        $g7['ban-url']    = jv('e2m_source_ban', array('source' => $bm['ID']));
        $g7['forget-url'] = jv('e2m_source_forget', array('source' => $bm['ID']));
        $ru[]             = $g7;
    }
    $d = array('title' => 'Sources', 'heading' => 'Sources',);
    if (count($ru)) {
        $d['sources'] = $ru;
    } else {
        $d['nothing'] = 'No sources';
    }
    return $d;
}

function e2m_source_trust($parameters)
{
    global $_config;
    $tu = $parameters['source'];
    xn("UPDATE  " . $_config['db_table_prefix'] . "Sources " . "SET `IsWhitelisted`=1, `IsTrusted`=1 " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`=" . $tu, 'trust source');
    xn("UPDATE  " . $_config['db_table_prefix'] . "Notes " . "SET `IsPublished`=1 " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `SourceID`=" . $tu, 'publish all notes from the just trusted source');
    aa();
    ds();
    @unlink(CACHE_FILENAME_DRAFTS);
    @unlink(CACHE_FILENAME_DRAFTS_ALIAS_USE_COUNTS);
    c();
}

function e2m_source_premoderate($parameters)
{
    global $_config;
    $tu = $parameters['source'];
    xn("UPDATE  " . $_config['db_table_prefix'] . "Sources " . "SET `IsTrusted`=0 " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`=" . $tu, 'distrust source, set to premoderation');
    ds();
    c();
}

function e2m_source_ban($parameters)
{
    global $_config;
    $tu = $parameters['source'];
    xn("UPDATE  " . $_config['db_table_prefix'] . "Sources " . "SET `IsWhiteListed`=0, `IsTrusted`=0 " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`=" . $tu, 'ban source');
    xn("DELETE FROM  " . $_config['db_table_prefix'] . "Notes " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `SourceID`=" . $tu, 'delete all notes from the just banned source');
    ds();
    @unlink(CACHE_FILENAME_DRAFTS);
    @unlink(CACHE_FILENAME_DRAFTS_ALIAS_USE_COUNTS);
    c();
}

function e2m_source_forget($parameters)
{
    global $_config;
    $tu = $parameters['source'];
    xn("DELETE FROM  " . $_config['db_table_prefix'] . "Sources " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`=" . $tu, 'forget source');
    xn("DELETE FROM  " . $_config['db_table_prefix'] . "Notes " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `SourceID`=" . $tu, 'delete all notes from the just forgotten source');
    ds();
    @unlink(CACHE_FILENAME_DRAFTS);
    @unlink(CACHE_FILENAME_DRAFTS_ALIAS_USE_COUNTS);
    c();
}

function nd($ju, $qu)
{
    global $_config;
    $hu = sd(array('author' => $ju['author']['name'], 'title' => $ju['title'], 'href' => $ju['author']['url'], 'userpic-href' => $ju['author']['avatar'],));
    if (!$hu['IsWhiteListed']) return;
    if (preg_match('/\+(\d\d)\:(\d\d)/', $ju['items'][0]['date_published'], $y3)) {
        $nl = $y3[1] * SECONDS_IN_AN_HOUR + $y3[2] * SECONDS_IN_A_MINUTE;
    }
    $gu = @$ju['items'][0]['_e2_data'] or $gu = array();
    $gu = json_encode($gu);
    $wu = $hu['IsTrusted'];
    $n2 = array('Title' => $ju['items'][0]['title'], 'Text' => $ju['items'][0]['content_html'], 'FormatterID' => 'raw', 'OriginalAlias' => '', 'Uploads' => '', 'Stamp' => strtotime($ju['items'][0]['date_published']), 'Offset' => (int)$nl, 'IsDST' => 0, 'LastModified' => strtotime($ju['items'][0]['date_modified']), 'IsCommentable' => 0, 'IsPublished' => $wu, 'IsExternal' => 1, 'SourceID' => $hu['ID'], 'SourceNoteID' => $ju['items'][0]['id'], 'SourceNoteURL' => $ju['items'][0]['url'], 'SourceNoteJSONURL' => $qu, 'SourceNoteData' => $gu,);
    $dx = $ju['items'][0]['id'];
    if ($ku = fd($hu['ID'], $dx)) {
        $n2['ID'] = $ku['ID'];
        nn('Notes', $n2);
    } else {
        $n2 = yn('Notes', $n2);
    }
    if ($wu) {
        if (la($n2)) {
            $n2['IsIndexed'] = '1';
            nn('Notes', $n2);
        }
    }
    e2_drop_caches_for_note_($n2['ID'], $wu);
    if ($_config['backup_automatically']) {
        tn();
    }
}

function md($qu)
{
    global $_config;
    xn("SELECT `ID` FROM " . $_config['db_table_prefix'] . "Notes " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `SourceNoteJSONURL`='" . rn($qu) . "' " . "LIMIT 1", 'get note ID by source JSON URL');
    $q1 = en();
    return $q1[0];
}

function fd($tu, $uu)
{
    global $_config;
    xn("SELECT `ID` FROM " . $_config['db_table_prefix'] . "Notes " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `SourceID`= '" . $tu . "' " . "AND `SourceNoteID`= '" . $uu . "' " . "LIMIT 1", 'get note ID by source ID and source note ID');
    $q1 = en();
    return $q1[0];
}

function dd($lu)
{
    for ($r = 0; $r <= 31; ++$r) {
        $lu = str_replace(chr($r), '', $lu);
    }
    $lu = str_replace(chr(127), '', $lu);
    if (0 === strpos(bin2hex($lu), 'efbbbf')) {
        $lu = substr($lu, 3);
    }
    return $lu;
}

function sd($iu)
{
    global $_config;
    $ou = false;
    xn("SELECT * FROM " . $_config['db_table_prefix'] . "Sources " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `URL`= '" . $iu['href'] . "' " . "LIMIT 1", 'get source record by the URL from blog info');
    $q1 = en();
    if (count($q1)) {
        $ou = $q1[0];
        if ($ou['ID'] != $ou['TrueID']) {
            xn("SELECT * FROM " . $_config['db_table_prefix'] . "Sources " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID`= '" . $ou['TrueID'] . "' " . "LIMIT 1", 'get true source record by using the TrueID of just found record');
            $q1 = en();
            if (count($q1)) {
                $ou = $q1[0];
            }
        }
    }
    $hu = array('Title' => $iu['title'], 'AuthorName' => $iu['author'], 'PictureURL' => $iu['userpic-href'],);
    if ($ou !== false) {
        if ($ou['Title'] !== $iu['title'] or $ou['AuthorName'] !== $iu['author'] or $ou['PictureURL'] !== $iu['userpic-href']) {
            $hu['ID'] = $ou['ID'];
            nn('Sources', $hu);
        }
        return $ou;
    } else {
        $hu['URL']           = $iu['href'];
        $hu['IsWhiteListed'] = 1;
        $hu['IsTrusted']     = 0;
        $hu                  = yn('Sources', $hu);
        $hu['TrueID']        = $hu['ID'];
        nn('Sources', $hu);
        return $hu;
    }
}

function ad($n2)
{
    global $_config;
    $gj = array();
    if (@$n2['IsExternal']) {
        if (array_key_exists('SourceID', $n2)) {
            xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Sources` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `ID` = '" . $n2['SourceID'] . "'", 'get source by id');
            $ib                        = en();
            $gj['source']              = $ib[0]['Title'];
            $gj['source-id']           = (int)$n2['SourceID'];
            $gj['source-true-id']      = (int)$ib[0]['TrueID'];
            $gj['source-whitelisted?'] = (bool)$ib[0]['IsWhiteListed'];
            $gj['source-trusted?']     = (bool)$ib[0]['IsTrusted'];
            if (!$ib[0]['IsTrusted']) {
                $gj['source-trust-url'] = jv('e2m_source_trust', array('source' => $n2['SourceID']));
            }
            if ($ib[0]['IsTrusted']) {
                $gj['source-premoderate-url'] = jv('e2m_source_premoderate', array('source' => $n2['SourceID']));
            }
            $gj['source-ban-url']    = jv('e2m_source_ban', array('source' => $n2['SourceID']));
            $gj['source-forget-url'] = jv('e2m_source_forget', array('source' => $n2['SourceID']));
            $gj['author']            = $ib[0]['AuthorName'];
            $gj['author-href']       = $ib[0]['URL'];
            $gj['userpic-href']      = $ib[0]['PictureURL'];
        }
        if (array_key_exists('SourceNoteURL', $n2) and @$n2['SourceNoteURL'] != '') {
            $gj['href-external'] = $n2['SourceNoteURL'];
        }
    }
    return $gj;
}

function e2m_frontpage($parameters = [])
{
    global $settings, $_strings, $_config;
    if (Log::$cy) __log('Frontpage {');
    $we            = k2();
    $frontpageView = new AePageableNotesView ('e2m_frontpage', $parameters);
    $frontpageView->setPortionSize($settings['appearance']['notes_per_page']);
    $frontpageView->setNextPrevPageTitles($_strings['gs--earlier'], $_strings['gs--later']);
    $frontpageView->setWantPaging(true);
    $frontpageView->setWantNewCommentsCount($we);
    $frontpageView->setWantReadHrefs($_config['count_reads']);
    $frontpageView->setWantControls($we and !@$_config['read_only']);
    $frontpageView->setWantHiddenTags($we);
    $frontpageView->setWantRelatedNotes(true);
    if (CACHE_FRONTPAGE and $frontpageView->isFirstPage()) {
        if ($we) {
            $frontpageView->setCacheFilename(CACHE_FILENAME_FRONTPAGE_AUTHOR);
        } else {
            $frontpageView->setCacheFilename(CACHE_FILENAME_FRONTPAGE);
        }
    }
    $frontpageView->setLimitlessSQLRequest("SELECT * " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=1 " . tm($we) . "ORDER BY `Stamp` DESC");
    $r6 = cd();
    if ($parameters['page'] > 1) {
        $r6 .= ' (' . $_strings['gs--page'] . ' ' . $parameters['page'] . ')';
    }
    $d = ['title' => $r6, 'heading' => '', 'notes' => $frontpageView->getNotesCTree(), 'pages' => $frontpageView->getPagesCTree(), 'frontpage?' => $frontpageView->isFirstPage(),];
    if (!$frontpageView->isExistingPage() and !$frontpageView->isFirstPageOfEmptyView()) {
        return e2_error404_mode();
    }
    if (Log::$cy) __log('} // Frontpage');
    return $d;
}

function e2m_json($parameters = array())
{
    list ($pu, $hg) = ed();
    $lu = json_encode($pu, E2_JSON_STYLE);
    hd($lu, $hg, 'json');
}

function e2m_rss($parameters = array())
{
    list ($pu, $hg) = ed();
    $c0 = e2feeds__rss_using_jsonfeed_array_($pu);
    hd($c0, $hg, 'rss');
}

function e2m_tag_json($parameters = array())
{
    if (array_key_exists('*tag', $parameters)) {
        $q2 = $parameters['*tag'];
    } else {
        return e2_error404_mode();
    }
    list ($pu, $hg) = rd($q2);
    $lu = json_encode($pu, E2_JSON_STYLE);
    hd($lu, $hg, 'json');
}

function e2m_tag_rss($parameters = array())
{
    if (array_key_exists('*tag', $parameters)) {
        $q2 = $parameters['*tag'];
    } else {
        return e2_error404_mode();
    }
    list ($pu, $hg) = rd($q2);
    $c0 = e2feeds__rss_using_jsonfeed_array_($pu);
    hd($c0, $hg, 'rss');
}

function e2m_note_json($parameters = array())
{
    global $settings, $_current_url;
    $n2 = $parameters['*note'];
    if ($n2 == false) return e2_error404_mode();
    $we = k2();
    if (!(rm($n2) === 'public' or ($we and $n2['IsPublished']))) return e2_error404_mode();
    $hg                     = $n2['Stamp'];
    $v0                     = e2_jsonfeed_item_array_from_noterec_($n2);
    $b0                     = array($v0);
    $pu                     = e2_jsonfeed_array_stub_from_jsonfeed_item_arrays_($b0);
    $pu['title']            = cd();
    $pu['_rss_description'] = xd();
    $pu['home_page_url']    = jv('e2m_frontpage', array('page' => 1));
    $pu['feed_url']         = $_current_url;
    hd(json_encode($pu, E2_JSON_STYLE), $hg, 'json');
}

function e2_jsonfeed_array_stub_from_jsonfeed_item_arrays_($b0)
{
    global $_lang, $_config, $settings;
    $d = ['version' => 'https://jsonfeed.org/version/1', 'title' => null, '_rss_description' => null, '_rss_language' => $_lang, '_itunes_email' => '', '_itunes_categories_xml' => '', '_itunes_image' => '', '_itunes_explicit' => '', 'home_page_url' => null, 'feed_url' => null, 'icon' => bd(), 'author' => array('name' => vd(), 'url' => jv('e2m_frontpage', array('page' => 1)), 'avatar' => bd(),), 'items' => $b0, '_e2_version' => E2_VERSION, '_e2_ua_string' => E2_UA_STRING,];
    return $d;
}

function e2_jsonfeed_item_array_from_noterec_($n2)
{
    global $settings;
    $sm = jv('e2m_note', array('*note' => $n2));
    $y0 = (xy('Y-m-d\TH:i:s', $n2['Stamp']) . gy($n2['Stamp'], ':'));
    $n0 = (xy('Y-m-d\TH:i:s', $n2['LastModified']) . gy($n2['LastModified'], ':'));
    $m0 = (xy('D, d M Y H:i:s ', $n2['Stamp']) . gy($n2['Stamp']));
    $z1 = u3($n2['FormatterID'], @$n2['Text'], 'full-rss');
    $me = d3(sb($z1['meta']['resources-detected'], q3('note', $n2['ID'])));
    $eq = array('id' => (string)$n2['ID'], 'url' => $sm, 'title' => h3($n2['Title']), 'content_html' => $z1['text-final'], 'date_published' => $y0, 'date_modified' => $n0,);
    if ($n2['IsExternal']) {
        $d0           = ad($n2);
        $eq['url']    = $d0['href-external'];
        $eq['author'] = array('name' => $d0['author'], 'url' => $d0['author-href'], 'avatar' => $d0['userpic-href'],);
    }
    if (count($me) > 0) {
        $eq['image'] = $me[0];
    }
    $eq['_date_published_rfc2822'] = $m0;
    $eq['_rss_guid_is_permalink']  = 'false';
    $eq['_rss_guid']               = (string)$n2['ID'];
    $eq['_e2_data']                = array('is_favourite' => (bool)$n2['IsFavourite'], 'links_required' => $z1['meta']['links-required'], 'og_images' => $me,);
    return $eq;
}

function zd($s0, $r6, $tq)
{
    global $_newsfeeds;
    if (!isset ($_newsfeeds)) $_newsfeeds = [];
    $a0 = '';
    if ($s0 == 'rss') $a0 = 'application/rss+xml';
    if ($s0 == 'json') $a0 = 'application/json';
    $_newsfeeds[] = ['type' => $a0, 'title' => htmlspecialchars($r6, ENT_NOQUOTES, HSC_ENC), 'href' => $tq];
}

function kd()
{
    global $_config;
    xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=1 " . tm() . "ORDER BY `Stamp` DESC " . "LIMIT " . $_config['rss_items'], 'get recent public noterecs for RSS or JSONFeed');
    return en();
}

function xd()
{
    global $settings;
    if (!empty ($settings['meta_description'])) {
        $q0 = strip_tags(h3(htmlspecialchars($settings['meta_description'], ENT_NOQUOTES, HSC_ENC)));
    } elseif (!empty ($settings['blog_subtitle'])) {
        $z1 = i3($settings['blog_subtitle'], 'full');
        $q0 = $z1['text-final'];
        $q0 = em($q0);
    } else {
        $q0 = cd();
    }
    return $q0;
}

function ed()
{
    global $settings, $_current_url;
    $hg = 0;
    $b0 = array();
    $pu = array();
    $ot = CACHE_FILENAME_FRONTPAGE_FEED;
    if (CACHE_FRONTPAGE_FEED and is_file($ot)) {
        if (Log::$cy) __log('Feed array (RSS, JSON): cached');
        $pu = @unserialize(file_get_contents($ot));
        $hg = filemtime($ot);
    } else {
        if (Log::$cy) __log('Feed array (RSS, JSON): not cached, will need to build');
        $bt = kd();
        foreach ($bt as $n2) {
            $b0[] = e2_jsonfeed_item_array_from_noterec_($n2);
            $hg   = max($hg, $n2['Stamp']);
        }
        $pu                     = e2_jsonfeed_array_stub_from_jsonfeed_item_arrays_($b0);
        $pu['title']            = cd();
        $pu['_rss_description'] = xd();
        $pu['home_page_url']    = jv('e2m_frontpage', array('page' => 1));
        $pu['feed_url']         = $_current_url;
        if (CACHE_FRONTPAGE_FEED) n3($ot, serialize($pu));
    }
    return array($pu, $hg);
}

function rd($q2)
{
    global $_config, $_strings, $_current_url;
    $hg = 0;
    $b0 = array();
    xn("SELECT n.* " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "INNER JOIN `" . $_config['db_table_prefix'] . "NotesKeywords` nk " . "ON nk.`NoteID` = n.`ID` " . "WHERE n.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND nk.`SubsetID`=" . $_config['db_table_subset'] . " " . "AND (nk.`KeywordID` = " . $q2['ID'] . ") " . "AND n.`IsPublished` = 1 " . tm(k2()) . "ORDER BY n.`Stamp` DESC " . "LIMIT " . $_config['rss_items'], 'get tag noterecs for RSS or JSONFeed');
    $bt = en();
    foreach ($bt as $n2) {
        $b0[] = e2_jsonfeed_item_array_from_noterec_($n2);
        $hg   = max($hg, $n2['Stamp']);
    }
    if ((string)$q2['Summary'] !== '') {
        $q0 = strip_tags(h3(htmlspecialchars($q2['Summary'], ENT_NOQUOTES, HSC_ENC)));
    } else if ((string)$q2['Description'] !== '') {
        $z1 = i3($q2['Description'], 'full');
        $q0 = $z1['text-final'];
        $q0 = em($q0);
    } else {
        $q0 = xd();
    }
    $nj = htmlspecialchars($q2['PageTitle'], ENT_COMPAT, HSC_ENC);
    if ((string)$nj !== '') {
        $r6 = $nj;
    } else {
        $r6 = (cd() . ': ' . $_strings['gs--posts-tagged'] . ' ' . htmlspecialchars($q2['Keyword'], ENT_COMPAT, HSC_ENC));
    }
    $pu                     = e2_jsonfeed_array_stub_from_jsonfeed_item_arrays_($b0);
    $pu['title']            = $r6;
    $pu['_rss_description'] = $q0;
    $pu['home_page_url']    = jv('e2m_tag', array('*tag' => $q2));
    $pu['feed_url']         = $_current_url;
    return array($pu, $hg);
}

function e2feeds__rss_using_jsonfeed_array_($content)
{
    $l0 = USER_FOLDER . 'rss/rss.tmpl.php';
    if (!is_file($l0)) {
        $l0 = DEFAULTS_FOLDER . 'rss/rss.tmpl.php';
    }
    if (is_file($l0)) {
        ob_start();
        include $l0;
        $c0 = ob_get_contents();
        ob_end_clean();
    }
    return $c0;
}

function jd($c0)
{
    $c0 = str_replace("\x0", '', $c0);
    for ($r = 0; $r < strlen($c0); ++$r) {
        if (ord($c0[$r]) < 32 and !in_array(ord($c0[$r]), array(10, 13))) {
            $c0[$r] = '';
        }
    }
    return $c0;
}

function hd($z0, $hg, $s0)
{
    global $_config;
    $k0 = gmdate('r', $hg);
    $x0 = md5($hg);
    if ($s0 == 'rss') {
        if (@$_config['dev_xml_as_text']) {
            header('Content-Type: text/plain');
        } else {
            header('Content-Type: application/xml; charset=utf-8');
        }
    } elseif ($s0 == 'json') {
        header('Content-Type: application/json');
    } else {
        header('Content-Type: text/plain');
    }
    header('Last-modified: ' . $k0);
    header('Etag: ' . $x0);
    header('Cache-Control: public');
    header('Expires: ' . date('r', $hg + SECONDS_IN_A_DAY));
    $e0 = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false;
    $r0 = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : false;
    if (!$e0 && !$r0 or $r0 && $r0 != $x0 or $e0 && $e0 != $k0) {
        if ($s0 == 'rss') {
            $z0 = jd($z0);
        }
        ini_set('zlib.output_compression', 0);
        echo $z0;
        ini_set('zlib.output_compression', 1);
    } else {
        header('HTTP/1.1 304 Not Modified');
    }
    die;
}

function e2m_year($parameters = array())
{
    global $_strings, $_config;
    $t0 = $parameters['year'];
    $j0 = e2l_get_string('pt--nth-year', array('year' => $t0));
    if (!ud($t0)) {
        return e2_error404_mode();
    }
    $h0 = gmmktime(0, 0, 0, 1, 1, $t0 - 1);
    $g0 = gmmktime(0, 0, 0, 1, 1, $t0 + 1);
    list ($w0, $u0) = e2__fruitful_neighbours_with_ymd_($t0);
    $i0 = 'e2m_year';
    if ($w0) {
        $o0['prev-href']  = jv($i0, e2__parameters_with_timestamp_($w0));
        $o0['prev-jump?'] = (bool)(gmdate('Y', $h0) != gmdate('Y', $w0));
        $o0['prev-title'] = gmdate('Y', $w0);
    }
    if ($u0) {
        $o0['next-href']  = jv($i0, e2__parameters_with_timestamp_($u0));
        $o0['next-jump?'] = (bool)(gmdate('Y', $g0) != gmdate('Y', $u0));
        $o0['next-title'] = gmdate('Y', $u0);
    }
    $o0['timeline?']  = false;
    $o0['this']       = $t0;
    $o0['this-title'] = $t0;
    $p0               = bs('start');
    $c9               = bs('end');
    if ($t0 == ky('Y', $p0['stamp'], $p0['timezone'])) {
        $v9 = ky('m', $p0['stamp'], $p0['timezone']);
    } else {
        $v9 = 1;
    }
    if ($t0 == xy('Y', time())) {
        $b9 = xy('m', time());
    } else {
        $b9 = 12;
    }
    $y9 = cs($t0);
    for ($n9 = 1; $n9 <= 12; ++$n9) {
        $m9      = gmmktime(0, 0, 0, $n9, 1, $t0);
        $f9[$n9] = array('number' => $n9, 'start-time' => array($m9, sy()), 'href' => gmdate('Y/m/', $m9), 'real?' => $n9 >= $v9 and $n9 <= $b9, 'fruitful?' => @in_array(gmdate('n', $m9), $y9),);
    }
    list ($wr, $ur) = jy($t0);
    $d = ['title' => $j0, 'heading' => $j0, 'pages' => $o0, 'year' => (int)$t0, 'year-months' => $f9,];
    xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished` = 1 " . tm(k2()) . "AND `Stamp` BETWEEN " . $wr . " " . "AND " . $ur . " " . "ORDER BY `Stamp`", 'get all notes for the year');
    $q1 = en();
    $l5 = pd(true, $q1, $t0);
    if (count($l5)) {
        $d['notes-list'] = $l5;
    } else {
        $d['nothing'] = $_strings['gs--no-such-notes'];
    }
    return $d;
}

function e2m_month($parameters = array())
{
    global $_strings, $_config;
    $t0 = $parameters['year'];
    $n9 = $parameters['month'];
    $j0 = e2l_get_string('pt--nth-month-of-nth-year', array('year' => $t0, 'month' => $n9));
    if (!ud($t0, $n9)) {
        return e2_error404_mode();
    }
    $h0 = gmmktime(0, 0, 0, $n9 - 1, 1, $t0);
    $g0 = gmmktime(0, 0, 0, $n9 + 1, 1, $t0);
    list ($w0, $u0) = e2__fruitful_neighbours_with_ymd_($t0, $n9);
    $i0 = 'e2m_month';
    if ($w0) {
        $o0['prev-href']  = jv($i0, e2__parameters_with_timestamp_($w0));
        $o0['prev-jump?'] = (bool)(gmdate('Y/m', $h0) != gmdate('Y/m', $w0));
        $o0['prev-title'] = e2l_get_string('gs--nth-month-of-nth-year', array('year' => gmdate('Y', $w0), 'month' => gmdate('n', $w0)));
    }
    if ($u0) {
        $o0['next-href']  = jv($i0, e2__parameters_with_timestamp_($u0));
        $o0['next-jump?'] = (bool)(gmdate('Y/m', $g0) != gmdate('Y/m', $u0));
        $o0['next-title'] = e2l_get_string('gs--nth-month-of-nth-year', array('year' => gmdate('Y', $u0), 'month' => gmdate('n', $u0)));
    }
    $o0['timeline?']  = false;
    $o0['this-title'] = $j0;
    list ($wr, $ur) = jy($t0, $n9);
    $d = ['title' => $j0, 'heading' => $j0, 'pages' => $o0, 'year' => (int)$t0, 'month' => (int)$n9, 'month-days' => e2_pack_month_days_with_ymd_($t0, $n9, false),];
    xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished` = 1 " . tm(k2()) . "AND `Stamp` BETWEEN " . $wr . " " . "AND " . $ur . " " . "ORDER BY `Stamp`", 'get all notes for the month');
    $q1 = en();
    $l5 = pd(true, $q1, $t0, $n9);
    if (count($l5)) {
        $d['notes-list'] = $l5;
    } else {
        $d['nothing'] = $_strings['gs--no-such-notes'];
    }
    return $d;
}

function e2m_day($parameters = array())
{
    global $_strings, $_config;
    $t0 = (int)$parameters['year'];
    $n9 = (int)$parameters['month'];
    $s9 = (int)$parameters['day'];
    if (!(ud($t0, $n9, $s9))) {
        return e2_error404_mode();
    }
    $j0 = e2l_get_string('pt--nth-day-of-nth-month-of-nth-year', array('year' => $t0, 'month' => $n9, 'day' => $s9));
    $h0 = gmmktime(0, 0, 0, $n9, $s9 - 1, $t0);
    $g0 = gmmktime(0, 0, 0, $n9, $s9 + 1, $t0);
    list ($w0, $u0) = e2__fruitful_neighbours_with_ymd_($t0, $n9, $s9);
    $i0 = 'e2m_day';
    if ($w0) {
        $o0['prev-href']  = jv($i0, e2__parameters_with_timestamp_($w0));
        $o0['prev-jump?'] = (bool)(gmdate('Y/m/d', $h0) != gmdate('Y/m/d', $w0));
        $o0['prev-title'] = e2l_get_string('gs--nth-day-of-nth-month-of-nth-year', array('year' => gmdate('Y', $w0), 'month' => gmdate('n', $w0), 'day' => gmdate('j', $w0),));
    }
    if ($u0) {
        $o0['next-href']  = jv($i0, e2__parameters_with_timestamp_($u0));
        $o0['next-jump?'] = (bool)(gmdate('Y/m/d', $g0) != gmdate('Y/m/d', $u0));
        $o0['next-title'] = e2l_get_string('gs--nth-day-of-nth-month-of-nth-year', array('year' => gmdate('Y', $u0), 'month' => gmdate('n', $u0), 'day' => gmdate('j', $u0),));
    }
    $o0['timeline?']  = false;
    $o0['this-title'] = $j0;
    $d                = ['title' => $j0, 'heading' => $j0, 'pages' => $o0, 'month-days' => e2_pack_month_days_with_ymd_($t0, $n9, $s9),];
    $q1               = sm($t0, $n9, $s9);
    $q1               = array_reverse($q1);
    $we               = k2();
    $l5               = [];
    foreach ($q1 as $t => $n2) {
        if (!(rm($n2) === 'public' or ($we and $n2['IsPublished']))) continue;
        $noteView = new AeNoteView ($n2);
        $noteView->setWantNewCommentsCount($we);
        $noteView->setWantReadHref($_config['count_reads']);
        $noteView->setWantControls($we and !@$_config['read_only']);
        $noteView->setWantHiddenTags($we);
        $noteView->setWantCommentsLink(true);
        $l5[] = $noteView->getNoteCTree();
    }
    if (count($l5)) {
        $d['notes'] = $l5;
    } else {
        $d['nothing'] = $_strings['gs--no-such-notes'];
    }
    return $d;
}

function gd($a9)
{
    global $_config;
    $l5 = null;
    if (CACHE_FULLLIST and is_file(CACHE_FILENAME_FULLLIST)) {
        $l5 = @unserialize(file_get_contents(CACHE_FILENAME_FULLLIST));
        if (Log::$cy) __log('Retrieving full notes list from cache...');
    }
    if (!is_array($l5)) {
        if (Log::$cy) __log('Retrieving full notes list from database...');
        xn("SELECT `ID`, `Title`, `Stamp`, `LastModified`, `Offset`, `IsDST`, " . "`IsFavourite`, `IsPublished`, `IsVisible`, `SourceNoteURL`, `OriginalAlias` " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished` = 1 " . tm() . "ORDER BY `Stamp`", 'get full notes list');
        $q1 = en();
        $l5 = pd($a9, $q1);
        if ($a9) {
            if (CACHE_FULLLIST) n3(CACHE_FILENAME_FULLLIST, serialize($l5));
        }
    }
    return $l5;
}

function e2m_everything($parameters = array())
{
    global $_strings;
    $l5 = gd(true);
    $q9 = count($l5);
    $j0 = e2l_get_string('pt--n-posts', array('number' => $q9));
    $d  = ['title' => $j0, 'heading' => $j0,];
    if (count($l5)) {
        $d['notes-list'] = $l5;
    } else {
        $d['nothing'] = $_strings['gs--no-notes'];
    }
    return $d;
}

function e2m_sitemap_xml($parameters = array())
{
    global $_config;
    $l5 = gd(false);
    if (@$_config['dev_xml_as_text']) {
        header('Content-Type: text/plain');
    } else {
        header('Content-type: application/xml; charset=utf-8');
    }
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\r\n";
    if (count($l5)) {
        $hg = @$l5[0]['last-modified'];
        echo '<url>' . "\r\n";
        echo '<loc>' . jv('e2m_frontpage', array('page' => 1)) . '</loc>' . "\r\n";
        echo '<lastmod>';
        echo gmdate('Y-m-d\TH:i:s\Z', $hg[0]);
        echo '</lastmod>' . "\r\n";
        echo '<changefreq>hourly</changefreq>';
        echo '</url>' . "\r\n";
        foreach ($l5 as $l2) {
            echo '<url>' . "\r\n";
            echo '<loc>';
            echo $l2['href'];
            echo '</loc>' . "\r\n";
            echo '<lastmod>';
            echo gmdate('Y-m-d\TH:i:s\Z', $l2['last-modified'][0]);
            echo '</lastmod>' . "\r\n";
            echo '</url>' . "\r\n";
        }
    }
    echo '</urlset>' . "\r\n";
    die;
}

function e2_pack_month_days_with_ymd_($t0, $n9, $s9)
{
    $l9 = ky('t', gmmktime(0, 0, 0, $n9, 1, $t0), sy());
    $p0 = bs('start');
    $c9 = bs('end');
    if ($t0 . '/' . $n9 == ky('Y/n', $p0['stamp'], $p0['timezone'])) {
        $z9 = ky('d', $p0['stamp'], $p0['timezone']);
    } else {
        $z9 = 1;
    }
    if ($t0 . '/' . $n9 == xy('Y/n', time())) {
        $k9 = xy('d', time());
    } else {
        $k9 = $l9;
    }
    $x9 = vs($t0, $n9);
    for ($r = 1; $r <= $l9; ++$r) {
        $m9     = gmmktime(0, 0, 0, $n9, $r, $t0);
        $e9[$r] = array('number' => $r, 'start-time' => array($m9, sy()), 'href' => gmdate('Y/m/d/', $m9), 'this?' => (bool)($r == $s9), 'real?' => $r >= $z9 and $r <= $k9, 'fruitful?' => @in_array(gmdate('d', $m9), $x9),);
    }
    return $e9;
}

function ud($t0, $n9 = false, $s9 = false)
{
    $p0 = bs('start');
    if ($p0 === false) {
        return false;
    }
    $r9 = ky('Y', $p0['stamp'], $p0['timezone']);
    $t9 = xy('Y', time());
    if ($n9 === false) {
        return (bool)($t0 >= $r9 and $t0 <= $t9);
    } else {
        $j9 = ky('n', $p0['stamp'], $p0['timezone']);
        $h9 = xy('n', time());
        if ($s9 === false) {
            return (bool)($n9 >= 1 and $n9 <= 12 and (($t0 > $r9 and $t0 < $t9) or ($t0 == $r9 and $n9 >= $j9) or ($t0 == $t9 and $n9 <= $h9)));
        } else {
            $g9 = ky('j', $p0['stamp'], $p0['timezone']);
            $w9 = xy('j', time());
            if (1) {
                return (bool)(checkdate($n9, $s9, $t0) and (($t0 > $r9 and $t0 < $t9) or ($t0 == $r9 and $n9 > $j9) or ($t0 == $r9 and $n9 == $j9 and $s9 >= $g9) or ($t0 == $t9 and $n9 < $h9) or ($t0 == $t9 and $n9 == $h9 and $s9 <= $w9)));
            }
        }
    }
}

function e2__fruitful_neighbours_with_ymd_($hb, $jb = false, $tb = false)
{
    global $_db, $_config;
    list ($u9, $i9) = jy($hb, $jb, $tb);
    $o9 = SECONDS_IN_A_DAY;
    if ($tb === false) $o9 = SECONDS_IN_A_MONTH;
    if ($jb === false) $o9 = SECONDS_IN_A_YEAR;
    $p9 = $ci = null;
    xn("SELECT `Stamp`, `Offset`, `IsDST` " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=1 " . "AND `Stamp` < '" . ($i9 - $o9) . "' " . tm(k2()) . "ORDER BY Stamp DESC", 'get previous fruitful neighbour with ymd');
    while ($u = mysqli_fetch_array($_db['result'], MYSQLI_ASSOC)) {
        list ($t0, $n9, $s9) = explode('/', ky('Y/n/j', $u['Stamp'], dy($u)));
        $vi = $hb * 10000 + ($jb ? ($jb * 100) : 0) + ($tb ? $tb : 0);
        $bi = $t0 * 10000 + ($jb ? ($n9 * 100) : 0) + ($tb ? $s9 : 0);
        if ($bi < $vi) {
            $p9 = gmmktime(0, 0, 0, $n9, $s9, $t0);
            break;
        }
    }
    xn("SELECT `Stamp`, `Offset`, `IsDST` " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=1 " . "AND `Stamp` > '" . ($u9 + $o9) . "' " . tm(k2()) . "ORDER BY Stamp", 'get next fruitful neighbour with ymd');
    while ($u = mysqli_fetch_array($_db['result'], MYSQLI_ASSOC)) {
        list ($t0, $n9, $s9) = explode('/', ky('Y/n/j', $u['Stamp'], dy($u)));
        $vi = $hb * 10000 + ($jb ? ($jb * 100) : 0) + ($tb ? $tb : 0);
        $bi = $t0 * 10000 + ($jb ? ($n9 * 100) : 0) + ($tb ? $s9 : 0);
        if ($bi > $vi) {
            $ci = gmmktime(0, 0, 0, $n9, $s9, $t0);
            break;
        }
    }
    return [$p9, $ci];
}

function e2__parameters_with_timestamp_($m4)
{
    list ($parameters['year'], $parameters['month'], $parameters['day']) = explode('/', gmdate('Y/m/d', $m4));
    return $parameters;
}

function pd($a9, $bt, $t0 = false, $n9 = false)
{
    $l5 = [];
    $yi = [];
    foreach ($bt as $t => $n2) {
        $l2['href']          = jv('e2m_note', array('*note' => $n2));
        $l2['time']          = array((int)min($n2['Stamp'], time()), dy($n2));
        $l2['last-modified'] = array((int)min($n2['LastModified'], time()), dy($n2));
        $l2['favourite?']    = (bool)($n2['IsFavourite'] && $n2['IsPublished']);
        $ge                  = rm($n2);
        $l2['draft?']        = $ge === 'draft';
        $l2['scheduled?']    = $ge === 'scheduled';
        $l2['public?']       = $ge === 'public';
        $l2['hidden?']       = $ge === 'hidden';
        if (array_key_exists('SourceNoteURL', $n2) and @$n2['SourceNoteURL'] != '') {
            $l2['href']          = $n2['SourceNoteURL'];
            $l2['href-original'] = $n2['SourceNoteURL'];
        }
        if (($t0 and $n9 and (((int)$t0) . '/' . ((int)$n9) == ky('Y/n', $n2['Stamp'], dy($n2)))) or ($t0 and !$n9 and ((int)$t0 == ky('Y', $n2['Stamp'], dy($n2)))) or (!$t0 and !$n9)) {
            $l5[] = $l2;
            $yi[] = str_replace("\n", ' ', $n2['Title']);
        }
    }
    if (Log::$cy) __log('Will do typography');
    if ($a9) {
        $ni = implode("\n", $yi);
        $ni = h3(htmlspecialchars($ni, ENT_NOQUOTES, HSC_ENC));
        $yi = explode("\n", $ni);
    }
    foreach ($l5 as $t => $xf) {
        $l5[$t]['title'] = $yi[$t];
    }
    $l5 = array_reverse($l5);
    return $l5;
}

function cs($hb)
{
    global $_config;
    list ($mi, $fi) = jy($hb);
    xn("SELECT `Stamp`, `Offset`, `IsDST` " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=1 " . "AND `Stamp` BETWEEN '" . $mi . "' AND '" . $fi . "' " . tm(k2()), 'get all notes for the year ' . $hb . ' to list months with notes');
    $q1 = en();
    $di = array();
    foreach ($q1 as $ir) {
        if (((int)$hb) == ky('Y', $ir['Stamp'], dy($ir))) {
            $di[] = (int)ky('n', $ir['Stamp'], dy($ir));
        }
    }
    $di = @array_unique($di);
    sort($di);
    return $di;
}

function vs($hb, $jb)
{
    global $_config;
    list ($si, $ai) = jy($hb, $jb);
    xn("SELECT `Stamp`, `Offset`, `IsDST` " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=1 " . "AND `Stamp` BETWEEN '" . $si . "' AND '" . $ai . "' " . tm(k2()), 'get all notes for the month ' . $jb . ' of the year ' . $hb . ' to list days with notes');
    $q1 = en();
    $qi = array();
    foreach ($q1 as $ir) {
        if (((int)$hb) . '/' . ((int)$jb) == ky('Y/n', $ir['Stamp'], dy($ir))) {
            $qi[] = (int)ky('j', $ir['Stamp'], dy($ir));
        }
    }
    $qi = @array_unique($qi);
    sort($qi);
    return $qi;
}

function bs($li)
{
    global $_config;
    $zi = 'p1';
    if (!k2()) {
        $zi = 'p1v1';
    }
    $ot = CACHES_FOLDER . $li . '-stamp-' . $zi . '.e2time.psa';
    if (CACHE_EDGE_TIMEINFO and is_file($ot)) {
        $d = @unserialize(file_get_contents($ot));
    }
    if (is_array(@$d)) {
        return $d;
    } else {
        $d = array('stamp' => time(), 'timezone' => ay(),);
        if ($li == 'start') {
            xn("SELECT `Stamp`, `Offset`, `IsDST` " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=1 " . tm(k2()) . "ORDER BY `Stamp` LIMIT 1", 'get blog start timestamp');
        } elseif ($li == 'end') {
            xn("SELECT `Stamp`, `Offset`, `IsDST` " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=1 " . tm(k2()) . "ORDER BY `Stamp` DESC LIMIT 1", 'get blog latest note timestamp');
        }
        $q1 = en();
        if (count($q1)) {
            $d = array('stamp' => $q1[0]['Stamp'], 'timezone' => dy($q1[0]),);
            if (CACHE_EDGE_TIMEINFO) n3($ot, serialize($d));
            return $d;
        }
        return $d;
    }
}

function e2m_calliope($parameters = [])
{
    global $_config, $_strings;
    xn("SELECT `ID`, `Title`, `Stamp`, `LastModified`, `Offset`, `IsDST`, " . "`IsFavourite`, `IsPublished`, `IsVisible`, `SourceNoteURL`, `OriginalAlias` " . "FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished` = '1' " . "AND `FormatterID` = 'calliope' " . "AND (0 " . "OR `Text` LIKE '%!!%' " . "OR `Text` LIKE '%\%\%%' " . "OR `Text` LIKE '%##%' " . "OR `Text` LIKE '%++%' " . "OR `Text` LIKE '%((html%' " . "OR `Text` LIKE '%((img%' " . "OR `Text` LIKE '%((link%' " . "OR `Text` LIKE '%((%.jpg%' " . "OR `Text` LIKE '%((%.jpeg%' " . "OR `Text` LIKE '%((%.gif%' " . "OR `Text` LIKE '%((%.png%' " . "OR `Text` LIKE '%[[html%' " . "OR `Text` LIKE '%[[img%' " . "OR `Text` LIKE '%[[link%' " . "OR `Text` LIKE '%[[%.jpg%' " . "OR `Text` LIKE '%[[%.jpeg%' " . "OR `Text` LIKE '%[[%.gif%' " . "OR `Text` LIKE '%[[%.png%' " . ")" . "ORDER BY `Stamp`", 'get calliope notes list');
    $q1 = en();
    $l5 = pd(true, $q1);
    $q9 = count($l5);
    $d  = ['title' => 'Calliope', 'heading' => 'Legacy posts',];
    if ($q9 > 0) {
        $d['heading'] = 'Edit and re-save these ' . $q9 . ' legacy post(s). Their content will not be displayed in the next version of Aegea';
    }
    if (count($l5)) {
        $d['notes-list'] = $l5;
    } else {
        $d['nothing'] = $_strings['gs--no-notes'];
    }
    return $d;
}

define('CACHE', true);
define('CACHE_ALIASMAP', CACHE and true);
define('CACHE_NOTES', CACHE and true);
define('CACHE_NOTES_RELATED', CACHE and true);
define('CACHE_NOTES_COMMENTS', CACHE and true);
define('CACHE_POPULAR', CACHE and true);
define('CACHE_POPULAR_WITH_TAG', CACHE and true);
define('CACHE_TAGS', CACHE and true);
define('CACHE_FAVTAGS', CACHE and true);
define('CACHE_NOTES_COUNTS', CACHE and true);
define('CACHE_EDGE_TIMEINFO', CACHE and true);
define('CACHE_FRONTPAGE', CACHE and true);
define('CACHE_FRONTPAGE_FEED', CACHE and true);
define('CACHE_TAG', CACHE and true);
define('CACHE_FULLLIST', CACHE and true);
define('CACHE_DRAFTS', CACHE and true);
define('CACHE_DRAFTS_ALIAS_USE_COUNTS', CACHE and true);
define('CACHE_LASTMODIFIEDS', CACHE and true);
define('CACHE_FILENAME_ALIASMAP', CACHES_FOLDER . 'aliasmap.psa');
define('CACHE_FILENAMES_NOTES', CACHES_FOLDER . 'note-*.psa');
define('CACHE_FILENAMES_NOTES_RELATED', CACHES_FOLDER . 'note-*-related.psa');
define('CACHE_FILENAMES_NOTES_COMMENTS', CACHES_FOLDER . 'note-*-comments.ctree.psa');
define('CACHE_FILENAMES_NOTES_COMMENTS_AUTHOR', CACHES_FOLDER . 'note-*-comments-author.ctree.psa');
define('CACHE_FILENAMES_NOTES_COUNTS', CACHES_FOLDER . 'notes-count-*.txt');
define('CACHE_FILENAMES_EDGE_TIMEINFO', CACHES_FOLDER . '*.e2time.psa');
define('CACHE_FILENAME_POPULAR', CACHES_FOLDER . 'popular.ctree.psa');
define('CACHE_FILENAME_POPULAR_EXPIRES', CACHES_FOLDER . 'popular-expires.txt');
define('CACHE_FILENAMES_POPULAR_WITH_TAG', CACHES_FOLDER . 'popular-*.ctree.psa');
define('CACHE_FILENAMES_POPULAR_WITH_TAG_EXPIRES', CACHES_FOLDER . 'popular-*-expires.txt');
define('CACHE_FILENAME_TAGS', CACHES_FOLDER . 'tags.ctree.psa');
define('CACHE_FILENAME_TAGS_FULL', CACHES_FOLDER . 'tags-full.ctree.psa');
define('CACHE_FILENAME_TAGS_AUTHOR', CACHES_FOLDER . 'tags-author.ctree.psa');
define('CACHE_FILENAME_TAGS_AUTHOR_FULL', CACHES_FOLDER . 'tags-author-full.ctree.psa');
define('CACHE_FILENAME_FAVTAGS', CACHES_FOLDER . 'favtags.ctree.psa');
define('CACHE_FILENAME_FRONTPAGE', CACHES_FOLDER . 'frontpage.ctree.psa');
define('CACHE_FILENAME_FRONTPAGE_AUTHOR', CACHES_FOLDER . 'frontpage-author.ctree.psa');
define('CACHE_FILENAME_FRONTPAGE_FEED', CACHES_FOLDER . 'frontpage-feed.psa');
define('CACHE_FILENAMES_TAG', CACHES_FOLDER . 'tag-*.ctree.psa');
define('CACHE_FILENAMES_TAG_AUTHOR', CACHES_FOLDER . 'tag-*-author.ctree.psa');
define('CACHE_FILENAME_FULLLIST', CACHES_FOLDER . 'notes-list.ctree.psa');
define('CACHE_FILENAME_DRAFTS', CACHES_FOLDER . 'drafts.psa');
define('CACHE_FILENAME_DRAFTS_ALIAS_USE_COUNTS', CACHES_FOLDER . 'drafts-auc.psa');
define('CACHE_FILENAME_LASTMODIFIEDS', CACHES_FOLDER . 'last-modifieds-by-id.psa');
function e2_cache_filename_with_id_($xs, $zi)
{
    return str_replace('*', $xs, $zi);
}

function e2_note_cache_filename_with_id_($xs)
{
    return e2_cache_filename_with_id_($xs, CACHE_FILENAMES_NOTES);
}

function e2_drop_caches_for_note_($dx, $lr)
{
    if (is_numeric($dx)) {
        if (Log::$cy) __log('Caches: Drop caches for note id ' . $dx);
        @unlink(e2_note_cache_filename_with_id_($dx));
        @unlink(e2_note_cache_filename_with_id_($dx . '-comments'));
        @unlink(e2_note_cache_filename_with_id_($dx . '-comments-author'));
    } else {
        r(CACHE_FILENAMES_NOTES);
        r(CACHE_FILENAMES_NOTES_COMMENTS);
        r(CACHE_FILENAMES_NOTES_COMMENTS_AUTHOR);
    }
    ss();
    if ($lr !== false) {
        as_();
        r(CACHE_FILENAMES_NOTES_RELATED);
        r(CACHE_FILENAMES_POPULAR_WITH_TAG);
        r(CACHE_FILENAMES_TAG);
        r(CACHE_FILENAMES_TAG_AUTHOR);
        @unlink(CACHE_FILENAME_POPULAR);
        @unlink(CACHE_FILENAME_FRONTPAGE);
        @unlink(CACHE_FILENAME_FRONTPAGE_AUTHOR);
        @unlink(CACHE_FILENAME_FRONTPAGE_FEED);
        @unlink(CACHE_FILENAME_FULLLIST);
        @unlink(CACHE_FILENAME_TAGS);
        @unlink(CACHE_FILENAME_TAGS_FULL);
        @unlink(CACHE_FILENAME_TAGS_AUTHOR);
        @unlink(CACHE_FILENAME_TAGS_AUTHOR_FULL);
    }
    if ($lr !== true) {
        @unlink(CACHE_FILENAME_DRAFTS);
        @unlink(CACHE_FILENAME_DRAFTS_ALIAS_USE_COUNTS);
    }
    @unlink(CACHE_FILENAME_LASTMODIFIEDS);
}

function e2_drop_caches_for_tag_($kj)
{
    if (is_numeric($kj)) {
        @unlink(e2_cache_filename_with_id_($kj, CACHE_FILENAMES_TAG));
        @unlink(e2_cache_filename_with_id_($kj, CACHE_FILENAMES_TAG_AUTHOR));
    } else {
        r(CACHE_FILENAMES_TAG);
        r(CACHE_FILENAMES_TAG_AUTHOR);
    }
    @unlink(CACHE_FILENAME_FAVTAGS);
    @unlink(CACHE_FILENAME_TAGS);
    @unlink(CACHE_FILENAME_TAGS_FULL);
    @unlink(CACHE_FILENAME_TAGS_AUTHOR);
    @unlink(CACHE_FILENAME_TAGS_AUTHOR_FULL);
}

function ds()
{
    if (Log::$cy) __log('Caches: Drop notes caches');
    e2_drop_caches_for_note_(null, null);
}

function ss()
{
    if (Log::$cy) __log('Caches: Drop notes counts cache');
    r(CACHE_FILENAMES_NOTES_COUNTS);
}

function as_()
{
    if (Log::$cy) __log('Caches: Drop egde time info cache');
    r(CACHE_FILENAMES_EDGE_TIMEINFO);
}

function e2_drop_all_kinds_of_cache()
{
    if (Log::$cy) __log('Caches: Drop all kinds of caches');
    r(CACHES_FOLDER . '*');
    return true;
}

define('OLBA_SPECIAL_CHAR', "\x1");
define('OLBA_SPECIAL_SEQUENCE_LENGTH', 6);
function qs($ki = null)
{
    global $_template, $_config, $settings;
    if ($ki === null) $ki = @$settings['template'];
    $xi = null;
    $ei = null;
    $ri = null;
    $ti = null;
    $ji = array();
    $hi = $ki;
    if ($hi !== null) {
        while (1) {
            if (Log::$cy) __log('Prepare theme "' . $hi . '"');
            $gi = TEMPLATES_FOLDER . $hi . '/';
            if (!is_dir($gi) or !is_file($gi . '/theme-info.php')) {
                if (Log::$cy) __log('Theme "' . $hi . '" not found, using default theme "' . DEFAULT_TEMPLATE . '"');
                $hi = DEFAULT_TEMPLATE;
                $gi = TEMPLATES_FOLDER . $hi . '/';
            }
            array_push($ji, $gi);
            $wi      = include $gi . '/theme-info.php';
            $ui[$gi] = $wi;
            if (array_key_exists('max_image_width', $wi)) {
                if ($xi === null) {
                    $xi = $wi['max_image_width'];
                }
            }
            if (array_key_exists('meta_viewport', $wi)) {
                if ($ei === null) {
                    $ei = $wi['meta_viewport'];
                }
            }
            if (array_key_exists('supports_dark_mode', $wi)) {
                if ($ri === null) {
                    $ri = $wi['supports_dark_mode'];
                }
            }
            if (array_key_exists('use_likely_light', $wi)) {
                if ($ti === null) {
                    $ti = $wi['use_likely_light'];
                }
            }
            if (array_key_exists('based_on', $wi)) {
                $hi = $wi['based_on'];
            } else {
                break;
            }
        }
    }
    if ($xi === null) {
        $xi = $_config['max_image_width'];
    }
    if ($ei === null) $ei = '';
    if ($ri === null) $ri = false;
    if ($ti === null) $ti = false;
    $gi = SYSTEM_TEMPLATE_FOLDER;
    array_push($ji, $gi);
    $ui[$gi]                         = [];
    $_template['name']               = $ki;
    $_template['max_image_width']    = $xi;
    $_template['meta_viewport']      = $ei;
    $_template['supports_dark_mode'] = $ri;
    $_template['use_likely_light']   = $ti;
    $_template['stack']              = $ji;
    $_template['infos']              = $ui;
}

;
function ls($ii)
{
    global $content;
    if (!isset ($_olba_includes)) $_olba_includes = 0;
    ++$_olba_includes;
    if (Log::$cy) __log('Eat "' . $ii . '"');
    ob_start();
    include $ii;
    return ob_get_clean();
}

function zs($lz)
{
    return (OLBA_SPECIAL_CHAR . str_pad($lz, OLBA_SPECIAL_SEQUENCE_LENGTH, '0', STR_PAD_LEFT) . OLBA_SPECIAL_CHAR);
}

function ks($name)
{
    static $lz = 0;
    ba($name, '_olba_placeholders');
    return zs($lz++);
}

function xs($oi)
{
    global $_olba_placeholders;
    foreach ($_olba_placeholders as $lz => $s) {
        $pi = zs($lz);
        $co = strpos($oi, $pi);
        $vo = rs($s, true);
        if ($co !== false) {
            $oi = substr_replace($oi, $vo, $co, strlen($pi));
        } else {
            break;
        }
    }
    return $oi;
}

function es($bo)
{
    if (is_dir(EXTRAS_FOLDER)) {
        $yo = EXTRAS_FOLDER . $bo . '.tmpl.php';
        if (is_file($yo)) {
            return ls($yo);
        }
    }
    return '';
}

function rs($bo)
{
    global $_template, $_olba_includes;
    $yo = 'templates/' . $bo . '.tmpl.php';
    if ($ii = e2o__usable_file_with_basename_($yo)) {
        return ls($ii);
    } else {
        ob_end_clean();
        throw new AeOlbaTemplateMissingException ('Missing: ' . $yo);
    }
}

function ts()
{
    global $_config;
    if (@$_config['raw_template_data'] or @$_config['raw_template_data_with_param'] and array_key_exists('raw', $_GET)) {
        $no = 'raw';
    } else {
        $no = 'main';
    }
    return rs($no, true);
}

function js($mo)
{
    ba($mo . '.css', '_olba_used_stylesheets');
}

function hs($fo)
{
    ba($fo . '.js', '_olba_used_scripts');
}

function gs($do_)
{
    foreach (array(SYSTEM_LIBRARY_FOLDER, USER_LIBRARY_FOLDER) as $so) {
        foreach (glob($so . $do_ . '/*') as $fy) {
            $za = pathinfo($fy, PATHINFO_EXTENSION);
            if ($za == 'js') {
                ba($fy, '_olba_used_scripts');
            }
            if ($za == 'css') {
                ba($fy, '_olba_used_stylesheets');
            }
        }
    }
}

function ws()
{
    global $_template, $_config, $settings;
    if ($ao = @opendir(TEMPLATES_FOLDER)) {
        while (false !== ($qo = readdir($ao))) {
            if (is_dir(TEMPLATES_FOLDER . $qo) and $qo != '.' and $qo != '..') {
                if (is_file(TEMPLATES_FOLDER . $qo . '/theme-info.php')) {
                    $lo[$qo] = TEMPLATES_FOLDER . $qo . '/';
                }
            }
        }
        closedir($ao);
    }
    $ua = array();
    $zo = 1000;
    foreach ($lo as $name => $ta) {
        $wi = include $ta . 'theme-info.php';
        $n3 = @$wi['display_name'];
        if (!$n3) continue;
        if (is_array($n3)) {
            if (array_key_exists($settings['language'], $n3)) {
                $n3 = $n3[$settings['language']];
            } else {
                $n3 = array_shift($n3);
            }
        }
        $lz = @$wi['index'] or $lz = $zo++;
        $ko = @$wi['colors'];
        if (!$ko) $ko = array('background' => 'transparent', 'headings' => 'rgba(128,128,128,.2)', 'text' => 'rgba(128,128,128,.2)', 'link' => 'rgba(128,128,128,.2)',);
        $xo = (bool)($name == $_template['name']);
        if ($xo) {
            $eo = jv('e2m_theme_preview', array('theme' => ''));
        } else {
            $eo = jv('e2m_theme_preview', array('theme' => $name));
        }
        $ua[$lz] = array('name' => $name, 'display-name' => $n3, 'colors' => $ko, 'current?' => $xo, 'preview-url' => $eo, 'supports-dark-mode?' => $wi['supports_dark_mode'],);
    }
    ksort($ua);
    return $ua;
}

function us($fb)
{
    return e2o__usable_file_with_basename_('images/' . $fb);
}

function is($ro)
{
    $to = e2o__usable_file_with_basename_('images/' . $ro . '.svg');
    if (is_file($to)) {
        return file_get_contents($to);
    }
    return '';
}

function os($mo)
{
    global $_template;
    $ea = 'styles/' . $mo . '.css';
    $jo = array();
    foreach ($_template['stack'] as $gi) {
        if (is_file($fb = $gi . $ea)) {
            $jo[] = $fb;
        }
        if (array_key_exists('reset_styles', $_template['infos'][$gi]) and in_array($mo, $_template['infos'][$gi]['reset_styles'])) {
            break;
        }
    }
    $jo = array_reverse($jo);
}

function ps()
{
    global $_olba_used_stylesheets, $_template;
    if (!isset ($_olba_used_stylesheets)) return;
    $_olba_used_stylesheets = array_unique($_olba_used_stylesheets);
    $ho                     = array();
    foreach ($_olba_used_stylesheets as $mo) {
        if (is_file($mo)) {
            $ho[] = $mo;
            continue;
        }
        if (is_file($fb = USER_FOLDER . 'js/' . $mo)) {
            $ho[] = $fb;
        }
        $ea = 'styles/' . $mo;
        $jo = array();
        foreach ($_template['stack'] as $gi) {
            if (is_file($fb = $gi . $ea)) {
                $jo[] = $fb;
            }
            if (array_key_exists('reset_styles', $_template['infos'][$gi]) and in_array($mo, $_template['infos'][$gi]['reset_styles'])) {
                break;
            }
        }
        $jo = array_reverse($jo);
        $ho = array_merge($ho, $jo);
    }
    foreach ($ho as $t => $xf) {
        $ry     = stat($xf);
        $ho[$t] .= '?' . $ry['mtime'];
    }
    return $ho;
}

function ca()
{
    global $_olba_used_scripts;
    if (!isset ($_olba_used_scripts)) return;
    $_olba_used_scripts = array_unique($_olba_used_scripts);
    $go                 = array();
    foreach ($_olba_used_scripts as $fo) {
        if (substr($fo, 0, 7) == 'http://' or substr($fo, 0, 8) == 'https://' or substr($fo, 0, 2) == '//') {
            $go[] = $fo;
            continue;
        }
        if (is_file($fo)) {
            $go[] = $fo;
            continue;
        }
        if (is_file($wo = USER_FOLDER . 'js/' . $fo)) {
            $go[] = $wo;
        }
        $ea = 'js/' . $fo;
        if ($wo = e2o__usable_file_with_basename_($ea)) {
            $go[] = $wo;
        }
    }
    foreach ($go as $t => $xf) {
        $ry = stat($xf);
        if ($ry['mtime']) {
            $go[$t] .= '?' . $ry['mtime'];
        }
    }
    return $go;
}

function va($uo)
{
    if (!is_array($uo)) return;
    foreach ($uo as $io) {
        if (substr($io, -3) == '.js') {
            hs(substr($io, 0, -3));
        }
        if (substr($io, -4) == '.css') {
            js(substr($io, 0, -4));
        }
    }
}

function ba($s, $hv)
{
    if (!isset ($GLOBALS[$hv])) {
        $GLOBALS[$hv] = array($s);
    } else {
        $GLOBALS[$hv][] = $s;
    }
}

function e2o__usable_file_with_basename_($ea)
{
    global $_template;
    if (!isset ($_template)) qs();
    foreach ($_template['stack'] as $gi) {
        if (is_file($fb = $gi . $ea)) {
            return $fb;
        }
    }
    return '';
}

function e2m_theme_preview($parameters)
{
    global $_lang, $_strings, $_superconfig, $_template;
    if (@$_superconfig['disallow_themes_preview']) {
        return e2_error404_mode();
    }
    if ($parameters['theme'] == $_template['name']) {
        c(jv('e2m_theme_preview', array('theme' => '')));
    }
    if ($parameters['theme']) {
        qs($parameters['theme']);
    }
    $oo = $_lang;
    if (!is_file($fy = 'system/preview/' . $oo . '.php')) {
        $oo = $_strings['--secondary-language'];
        $fy = 'system/preview/' . $oo . '.php';
    }
    if (!is_file($fy = 'system/preview/' . $oo . '.php')) {
        $fy = 'system/preview/' . DEFAULT_LANGUAGE . '.php';
    }
    $eq = include $fy;
    return $eq;
}

define('SEARCH_EXTRA_PREFIX', 'Rose');
define('SEARCH_LIMIT', 20);
define('SEARCH_SNIPPETS_LIMIT', 20);
define('SEARCH_USE_ROSE', 1);
define('SEARCH_USE_MYSQL', 1);
define('BSI_SELECT_PORTION', 10);
define('BSI_GIVE_UP_TIMEOUT', 10);
define('BSI_UNLOCK_TIMEOUT', 10);

use S2\Rose\Storage\Exception\EmptyIndexException;
use S2\Rose\Storage\Database\PdoStorage;
use S2\Rose\Storage\Database\MysqlRepository;
use S2\Rose\Stemmer\PorterStemmerEnglish;
use S2\Rose\Stemmer\PorterStemmerRussian;
use S2\Rose\Indexer;
use S2\Rose\Entity\Indexable;
use S2\Rose\Entity\Query;
use S2\Rose\Entity\ExternalContent;
use S2\Rose\Finder;
use S2\Rose\SnippetBuilder;

function e2m_found($parameters = array())
{
    global $_db, $_strings, $_config;
    $parameters['query'] = trim($parameters['query']);
    $bf                  = $parameters['query'];
    if (!$bf) {
        return array('title' => $_strings['pt--search-query-empty'], 'heading' => $_strings['pt--search'], 'nothing' => $_strings['gs--search-query-empty'],);
    }
    $fvv = false;
    $yj  = [];
    try {
        if (k2()) {
            $dvv = '';
        } else {
            $dvv = 'AND `IsVisible` = 1 ';
        }
        xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Keywords` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . $dvv . "AND `Keyword`='" . rn($bf) . "'", 'get tags matching the search query');
        $ib = en();
        if (isset ($ib[0]['ID'])) {
            $fvv = ['href' => jv('e2m_tag', array('*tag' => $ib[0])), 'name' => htmlspecialchars($bf, ENT_NOQUOTES, HSC_ENC), 'visible?' => (bool)$ib[0]['IsVisible'],];
            $yj  = nf($ib[0]['ID'], 4);
            array_unshift($yj, $fvv);
        }
    } catch (AeMySQLException $e) {
        kv($e, 'Could not get tags matching the search query');
    }
    $svv = n(' ', $parameters['query']);
    if (SEARCH_USE_ROSE) {
        $avv = new PorterStemmerRussian (new PorterStemmerEnglish ());
        foreach ($svv as $t => $xf) {
            $svv[$t] = $avv->stemWord($svv[$t]);
        }
    }
    $qvv = array();
    $we  = k2();
    if (SEARCH_USE_ROSE) {
        try {
            $p3  = ea();
            $lvv = new Finder ($p3, $avv);
            $lvv->setHighlightTemplate('<mark>%s</mark>');
            $zvv = new Query ($bf);
            $zvv->setInstanceId($_config['db_table_subset']);
            $zvv->setLimit(SEARCH_LIMIT);
            $resultSet = $lvv->find($zvv);
            foreach ($resultSet->getFoundExternalIds() as $kvv) {
                $xvv = $kvv->getId();
                if ($xvv[0] == 'n') {
                    $dx = substr($xvv, 1);
                    $n2 = mm($dx);
                    if (!empty ($_config['search_favourites_boost'])) {
                        if ($n2['IsFavourite']) {
                            $resultSet->setRelevanceRatio($xvv, $_config['search_favourites_boost']);
                        }
                    }
                }
            }
            $snippetBuilder = new SnippetBuilder ($avv);
            $snippetBuilder->setSnippetLineSeparator(' · ');
            $snippetBuilder->attachSnippets($resultSet, static function (array $rvv) use ($we, $_config) {
                $q1 = new ExternalContent ();
                foreach (array_slice($rvv, 0, SEARCH_SNIPPETS_LIMIT) as $kvv) {
                    $xvv = $kvv->getId();
                    if ($xvv[0] == 'n') {
                        $dx = substr($xvv, 1);
                        $n2 = mm($dx);
                        if ($n2) {
                            $noteView = new AeNoteView ($n2);
                            $noteView->setWantReadHref($_config['count_reads']);
                            $noteView->setWantControls($we and !@$_config['read_only']);
                            $noteView->setWantHiddenTags($we);
                            $zx             = $noteView->getNoteCTree();
                            $tvv[$n2['ID']] = $zx;
                            $q1->attach($kvv, $zx['text']);
                        }
                    }
                }
                return $q1;
            });
            foreach ($resultSet->getItems() as $jvv) {
                $hvv = $jvv->getId();
                if ($hvv[0] == 'n') {
                    $dx = substr($hvv, 1);
                    $n2 = mm($dx);
                    if (!(rm($n2) === 'public' or ($we and $n2['IsPublished']))) continue;
                    $n2['_']['_srprovider']     = 'Rose';
                    $n2['_']['_rose_relevance'] = $jvv->getRelevance();
                    $n2['_']['_rose_title']     = $jvv->getHighlightedTitle($avv);
                    $n2['_']['_rose_snippet']   = $jvv->getSnippet();
                    $qvv[]                      = $n2;
                }
            }
            $gvv = false;
            if (@$_config['dev_rose_info']) {
                $gvv = print_r($resultSet->getTrace(), true);
            }
        } catch (EmptyIndexException $e) {
            sa();
        } catch (AeMySQLException $e) {
            kv($e, 'Could not do something with the database while working on Rose search results');
        }
    }
    if (SEARCH_USE_MYSQL) {
        $wvv = rn(preg_quote($bf));
        if ($_db['innodb-fulltext?']) {
            $uvv = "MATCH (`Title`, `Text`) AGAINST ('" . $wvv . "')";
            $ivv = 'MySQL FT';
        } else {
            $uvv = "`Title` LIKE '%" . $wvv . "%' OR `Text` LIKE '%" . $wvv . "%'";
            $ivv = 'MySQL Like';
        }
        $ovv = ("SELECT * FROM `" . $_config['db_table_prefix'] . "Notes` n " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=1 AND (" . $uvv . ") " . tm($we) . "LIMIT " . SEARCH_LIMIT);
        try {
            xn($ovv, 'search using MySQL fulltext search');
            $q1 = en();
            foreach ($q1 as $t => $n2) {
                $n2['_']['_srprovider'] = $ivv;
                $qvv[]                  = $n2;
            }
        } catch (AeMySQLException $e) {
            kv($e, 'Could not search using MySQL fulltext search');
        }
    }
    $pvv = array();
    $l5  = array();
    $r   = 0;
    foreach ($qvv as $n2) {
        if (!in_array($n2['ID'], $pvv)) {
            if (!empty ($tvv[$n2['ID']])) {
                $l2 = $tvv[$n2['ID']];
            } else {
                $noteView = new AeNoteView ($n2);
                $noteView->setWantReadHref($_config['count_reads']);
                $noteView->setWantControls($we and !@$_config['read_only']);
                $l2 = $noteView->getNoteCTree();
            }
            $l2['search-result-provider'] = $n2['_']['_srprovider'];
            if ($n2['_']['_srprovider'] == 'Rose') {
                $l2['search-rose'] = ['relevance' => $n2['_']['_rose_relevance'], 'title' => $n2['_']['_rose_title'], 'snippet' => $n2['_']['_rose_snippet'],];
            }
            if (@$n2['_']['_rose_title']) {
                $l2['title'] = $n2['_']['_rose_title'];
            } else {
                $l2['title'] = ra($l2['title'], $svv);
            }
            $l2['title'] = h3($l2['title']);
            if (!empty ($n2['_']['_rose_snippet'])) {
                $l2['snippet-text'] = $n2['_']['_rose_snippet'];
            } else {
                $tv  = $l2['text'];
                $tv  = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $tv);
                $tv  = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/i', '', $tv);
                $tv  = str_replace(array('<br>', '<br/>', '<br />', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>', '</p>', '</pre>', '</blockquote>', '</li>',), ' ', $tv);
                $tv  = strip_tags($tv);
                $cbv = array();
                $vbv = preg_split('/[\\n\(\)\[\]]|[.:;?!](\s|$)/uis', $tv);
                $bbv = 0;
                $ybv = '';
                foreach ($vbv as $nbv) {
                    $nbv = trim($nbv);
                    if (!$nbv) continue;
                    if (!$ybv) $ybv = $nbv;
                    $mbv = $nbv;
                    $mbv = ra($mbv, $svv);
                    if ($mbv != $nbv) {
                        $cbv[] = ta($mbv);
                        $bbv++;
                        if ($bbv > 3) break;
                    }
                }
                if (count($cbv)) {
                    $l2['snippet-text'] = implode(' · ', $cbv);
                } else {
                    $l2['snippet-text'] = $ybv;
                }
            }
            $l2['has-highlighed-thumbs?'] = false;
            if ($gs = @$l2['format-info']['resources-detected']) {
                $fbv = qb(db($gs));
                foreach ($fbv as $t => $xf) {
                    $fbv[$t]['highlighted?'] = (strstr($xf['original-filename'], $bf) !== false);
                    if ($fbv[$t]['highlighted?']) {
                        $l2['has-highlighted-thumbs?'] = true;
                    }
                }
                $l2['thumbs'] = $fbv;
            }
            $l5[]  = $l2;
            $pvv[] = $n2['ID'];
            $r++;
            if ($r >= SEARCH_LIMIT) break;
        }
    }
    $dj = count($l5);
    if ($dj) {
        $dbv = e2l_get_string('pt--n-posts', array('number' => $dj));
    } else {
        $dbv          = $_strings['pt--no-posts'];
        $d['nothing'] = $_strings['gs--nothing-found'];
    }
    if ($r >= SEARCH_LIMIT) {
        $dbv = $_strings['gs--many-posts'];
    }
    if ($yj) {
        $d['search-related-tags'] = $yj;
    }
    $d['notes']   = $l5;
    $d['pages']   = array();
    $d['title']   = $dbv . ' ' . $_strings['gs--found-for-query'] . ': ' . htmlspecialchars($bf, ENT_NOQUOTES, HSC_ENC);
    $d['heading'] = $bf;
    if (@$gvv) {
        $d['rose-debug-info'] = $gvv;
    }
    return $d;
}

function fa($parameters)
{
    if (Log::$cy) __log('Search form');
    $bf = trim((string)@$parameters['query']);
    return ['form-action' => jv('e2s_search'), 'query' => htmlspecialchars($bf, ENT_COMPAT, HSC_ENC),];
}

function e2s_search()
{
    $bf = @$_POST['query'];
    $bf = str_replace('?', urlencode('?'), $bf);
    $bf = str_replace('/', ' ', $bf);
    $bf = trim($bf);
    $bf = str_replace(' ', '+', $bf);
    c(jv('e2m_found', array('query' => $bf)));
}

function da()
{
    global $_config;
    $sbv = @unserialize(file_get_contents(USER_FOLDER . 'indexing.psa'));
    if (!is_array($sbv)) $sbv = array('spent' => '?');
    $abv = $qbv = '?';
    try {
        xn("SELECT count(*) c FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsPublished`=1 ", 'count total published notes');
        $sy  = en();
        $abv = $sy[0]['c'];
        xn("SELECT count(*) c FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsIndexed`=1 AND `IsPublished`=1 ", 'count indexed published notes');
        $sy  = en();
        $qbv = $sy[0]['c'];
    } catch (AeMySQLException $e) {
        kv($e, 'Could not count some notes');
        return false;
    }
    $lbv = true;
    foreach (xa() as $zbv) {
        if (!cn(SEARCH_EXTRA_PREFIX . $zbv)) {
            $lbv = false;
            break;
        }
    }
    if (!$lbv) {
        $qbv          = 0;
        $sbv['spent'] = false;
    }
    return ['indexed_count' => $qbv, 'total_count' => $abv, 'time_spent' => @$sbv['spent'] ? $sbv['spent'] : false,];
}

function e2s_bsi_step()
{
    global $_db, $_config, $_strings;
    echo '<pre>';
    if ($_config['log_bsi']) {
        Log::$cy = true;
        if (Log::$cy) bv('bsi');
    }
    if (Log::$cy) __log('BSI step');
    if (!qa()) {
        if (Log::$cy) __log('Not indexing');
        die ('Not indexing</pre>');
    }
    $sbv = @unserialize(file_get_contents(USER_FOLDER . 'indexing.psa'));
    if (!is_array($sbv)) $sbv = array('spent' => '?');
    if (!isset ($sbv['lock']) or $sbv['lock'] < time() - (BSI_GIVE_UP_TIMEOUT + BSI_UNLOCK_TIMEOUT)) {
        if (isset ($sbv['lock'])) {
            if (Log::$cy) __log('Indexer: old lock is ' . $sbv['lock']);
            echo 'Old lock is ' . $sbv['lock'] . '<br />';
        } else {
            echo 'No old lock<br />';
        }
        $sbv['lock'] = time();
        if (!@n3(USER_FOLDER . 'indexing.psa', serialize($sbv))) {
            if (Log::$cy) __log('Indexer: can’t get a new lock');
            die ('Can’t get a new lock<br />');
        }
        if (Log::$cy) __log('Indexer: new lock is ' . $sbv['lock']);
        echo 'New lock is ' . $sbv['lock'] . '<br /><br />';
        try {
            $r   = 0;
            $kbv = 0;
            $xbv = w();
            $ebv = false;
            $sn  = false;
            while ($kbv < BSI_GIVE_UP_TIMEOUT) {
                xn("SELECT * FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `IsIndexed`=0 AND `IsPublished`=1 " . "ORDER BY `Stamp` DESC " . "LIMIT " . BSI_SELECT_PORTION, 'get portion of unindexed notes for indexing');
                $sy = en();
                if (count($sy)) {
                    ++$r;
                    if (Log::$cy) __log('Indexer: portion ' . $r);
                    echo 'Portion ' . $r . '<br />';
                    foreach ($sy as $cl) {
                        if (Log::$cy) __log('Indexer: indexing "' . $cl['Title'] . '"');
                        echo 'Indexing: ' . $cl['Title'] . '<br />';
                        if (la($cl)) {
                            $cl['IsIndexed'] = '1';
                            nn('Notes', $cl);
                        } else {
                            $sn = true;
                            break 2;
                        }
                        if ($_config['broadcast_on_indexing']) {
                            by($cl);
                        }
                    }
                    $kbv = round(w() - $xbv, 3);
                    if (Log::$cy) __log('Indexer: step done ' . count($sy) . ', spent ' . $kbv . ' ms so far');
                    echo 'Step done ' . count($sy) . ', spent ' . $kbv . ' ms so far<br /><br />';
                } else {
                    $ebv = true;
                    break;
                }
            }
            if ($ebv) {
                if (Log::$cy) __log('Indexer: indexing complete');
                echo 'Indexing complete<br /><br />';
                @unlink(USER_FOLDER . 'indexing.psa');
            } elseif ($sn) {
                if (Log::$cy) __log('Indexer: indexing failed');
                echo 'Indexing failed<br /><br />';
            } else {
                echo 'Time out<br />';
                unset ($sbv['lock']);
                $sbv['done'] = count($sy);
                if ($sbv['spent'] != '?') $sbv['spent'] += $kbv;
                @n3(USER_FOLDER . 'indexing.psa', serialize($sbv));
            }
        } catch (AeMySQLException $e) {
            kv($e, 'Could not index notes');
            if (Log::$cy) __log('Indexer: DB unaccessible');
            echo 'DB unaccessible<br />';
        }
    } else {
        if (Log::$cy) __log('Indexer: locked');
        echo 'Locked<br />';
    }
    die ('</pre>');
}

function e2s_bsi_drop()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        sa(true);
    }
    c(jv('e2m_underhood'));
}

function sa($rbv = false)
{
    global $_db, $_config;
    try {
        ja();
        if ($rbv) mv('Notes marked for reindexing', E2E_MESSAGE);
        $p3 = ea();
        try {
            $p3->erase();
            if ($rbv) mv('Indexes erased', E2E_MESSAGE);
        } catch (\S2\Rose\Exception\RuntimeException $e) {
            kv($e, 'Rose threw RuntimeException');
        }
        aa();
    } catch (AeMySQLException $e) {
        kv($e, 'Could not mark all notes for reindexing');
    }
}

function aa()
{
    $sbv = array();
    @n3(USER_FOLDER . 'indexing.psa', serialize($sbv));
}

function qa()
{
    return (is_file(USER_FOLDER . 'indexing.psa'));
}

function la($n2)
{
    global $_config;
    if (Log::$cy) __log('Indexer: index noterec');
    static $tbv = null;
    try {
        if ($tbv === null) {
            $avv = new PorterStemmerRussian (new PorterStemmerEnglish ());
            $tbv = new Indexer (ea(), $avv);
        }
        $z1 = u3($n2['FormatterID'], @$n2['Text'], 'full-rss');
        f3('note', $n2, $z1['meta']['resources-detected']);
        $tv  = strip_tags($z1['text-final']);
        $jbv = new Indexable ('n' . $n2['ID'], $n2['Title'], $tv, $_config['db_table_subset']);
        $tbv->index($jbv);
        return true;
    } catch (EmptyIndexException $e) {
        sa();
    } catch (\Exception $e) {
        kv($e, 'Could not index note');
        return false;
    }
}

function za($xs)
{
    global $_config;
    static $tbv = null;
    try {
        if ($tbv === null) {
            $avv = new PorterStemmerRussian (new PorterStemmerEnglish ());
            $tbv = new Indexer (ea(), $avv);
        }
        return $tbv->removeById('n' . $xs, $_config['db_table_subset']);
    } catch (EmptyIndexException $e) {
        sa();
    } catch (\Exception $e) {
        kv($e, 'Could not index note');
        return false;
    }
}

function ka($hbv)
{
    $sz  = 'S2\\Rose\\';
    $gbv = __DIR__ . '/library/rose/';
    $e2_ = strlen($sz);
    if (strncmp($sz, $hbv, $e2_) !== 0) return;
    $wbv = substr($hbv, $e2_);
    $fy  = $gbv . str_replace('\\', '/', $wbv) . '.php';
    if (file_exists($fy)) require $fy;
}

function xa()
{
    return array('TOC' => 'Contents', 'WORD' => 'Word', 'FULLTEXT_INDEX' => 'Fulltext', 'KEYWORD_INDEX' => 'Keyword', 'KEYWORD_MULTIPLE_INDEX' => 'KeywordMultiple',);
}

function ea()
{
    global $_config, $settings;
    static $ubv = null;
    if ($ubv === null and SEARCH_USE_ROSE) {
        if (getenv('E2_DB_SERVER')) $u3['server'] = getenv('E2_DB_SERVER');
        if (getenv('E2_DB_USER_NAME')) $u3['user_name'] = getenv('E2_DB_USER_NAME');
        if (getenv('E2_DB_PASSW')) $u3['passw'] = u2(getenv('E2_DB_PASSW'));
        if (getenv('E2_DB_NAME')) $u3['name'] = getenv('E2_DB_NAME');
        if (empty ($u3)) $u3 = $settings['db'];
        list ($vz, $bz) = wn($u3['server']);
        if ((string)$bz === '') $bz = null;
        $ibv = new \PDO ('mysql:' . 'host=' . $vz . ';' . 'dbname=' . $u3['name'] . ';' . 'port=' . $bz, $u3['user_name'], i2($u3['passw']));
        $yk  = $ibv->getAttribute(\PDO::ATTR_SERVER_VERSION);
        $obv = version_compare($yk, '5.5.3', '>=') ? 'utf8mb4' : 'utf8';
        $ibv->exec('SET NAMES ' . $obv);
        $ibv->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pbv = xa();
        $ubv = new PdoStorage ($ibv, $_config['db_table_prefix'] . SEARCH_EXTRA_PREFIX, array(MysqlRepository::TOC => $pbv['TOC'], MysqlRepository::WORD => $pbv['WORD'], MysqlRepository::FULLTEXT_INDEX => $pbv['FULLTEXT_INDEX'], MysqlRepository::KEYWORD_INDEX => $pbv['KEYWORD_INDEX'], MysqlRepository::KEYWORD_MULTIPLE_INDEX => $pbv['KEYWORD_MULTIPLE_INDEX'],));
    }
    return $ubv;
}

function ra($tv, $svv)
{
    foreach ($svv as $v4) {
        if ($v4 == '-') continue;
        $v4 = preg_quote($v4, '/');
        $v4 = str_replace('е', '[её]', $v4);
        $v4 = str_replace('Е', '[ЕЁ]', $v4);
        $tv = preg_replace('/(?<=^|\W)(' . $v4 . '[\w\p{M}]*)/iu', '<mark>$1</mark>', $tv);
    }
    $tv = str_replace('</mark> <mark>', ' ', $tv);
    $tv = str_replace('</mark> <mark>', ' ', $tv);
    return $tv;
}

function ta($o2)
{
    $c3v = mb_strtoupper(mb_substr($o2, 0, 1));
    return $c3v . mb_substr($o2, 1);
}

function ja()
{
    global $_config;
    xn("UPDATE `" . $_config['db_table_prefix'] . "Notes` " . "SET `IsIndexed`=0 " . "WHERE `SubsetID`=" . $_config['db_table_subset'], 'mark all notes for reindexing');
}

function e2_check_timeout()
{
    static $v3v;
    if (is_null($v3v)) {
        $b3v = ini_get('max_execution_time');
        if ($b3v) {
            $v3v = time() + $b3v - 5;
        } else {
            $v3v = 0;
        }
    }
    return ($v3v == 0) ? true : $v3v >= time();
}

function e2_write_dump_header($fy)
{
    $pq = ('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";' . PHP_EOL . 'SET AUTOCOMMIT=0;' . PHP_EOL . 'START TRANSACTION;' . PHP_EOL . "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;" . PHP_EOL . "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;" . PHP_EOL . "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;" . PHP_EOL . "/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;" . PHP_EOL . "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;" . PHP_EOL . "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=NO_AUTO_VALUE_ON_ZERO */;" . PHP_EOL . "/*!40101 SET NAMES utf8 */;" . PHP_EOL . "/*!50503 SET NAMES utf8mb4 */;" . PHP_EOL . '');
    fwrite($fy, $pq);
    return true;
}

function e2_write_dump_footer($fy)
{
    $y3v = 'COMMIT;' . PHP_EOL;
    $y3v .= "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;" . PHP_EOL . "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;" . PHP_EOL . "/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;" . PHP_EOL . "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;" . PHP_EOL . "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;" . PHP_EOL . "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;" . PHP_EOL;
    fwrite($fy, $y3v);
    return true;
}

function e2_get_table_definition($n3v, $dz)
{
    $m3v = null;
    $q1  = mysqli_query($n3v, "SHOW CREATE TABLE `{$dz}`");
    if ($q1) {
        $f3v = mysqli_fetch_array($q1);
        $m3v = $f3v['Create Table'];
    }
    return $m3v;
}

function e2_write_table_definition($fy, $n3v, $dz)
{
    $d3v = e2_get_table_definition($n3v, $dz);
    if (e2_check_timeout() && $d3v) {
        fwrite($fy, $d3v);
        fwrite($fy, ';');
        fwrite($fy, PHP_EOL . PHP_EOL);
        return true;
    }
    return false;
}

function e2_get_table_data($n3v, $dz, $nl, $limit)
{
    $bf = "SELECT * FROM `{$dz}` LIMIT {$nl}, {$limit}";
    $q1 = mysqli_query($n3v, $bf);
    if (!$q1) {
        return false;
    }
    $s3v = '';
    $a3v = "INSERT INTO `{$dz}` VALUES";
    while ($u4 = mysqli_fetch_row($q1)) {
        $cvv = array();
        foreach ($u4 as $s) {
            $cvv[] = is_null($s) ? "NULL" : "'" . mysqli_real_escape_string($n3v, $s) . "'";
        }
        $s3v .= $a3v . '(' . join(', ', $cvv) . ');' . PHP_EOL;
    }
    return $s3v;
}

function e2_table_disable_keys($dz)
{
    return "ALTER TABLE `{$dz}` DISABLE KEYS;" . PHP_EOL;
}

function e2_table_enable_keys($dz)
{
    return "ALTER TABLE `{$dz}` ENABLE KEYS;" . PHP_EOL;
}

function e2_get_total_records($n3v, $dz)
{
    $t = mysqli_fetch_row(mysqli_query($n3v, "SELECT COUNT(*) FROM `{$dz}`"));
    return $t[0];
}

function e2_backup_select_chuck_size_for_table_($dz)
{
    $limit = 5000;
    if (substr($dz, -7) === 'Actions') $limit = 50000;
    if (substr($dz, -7) === 'Aliases') $limit = 20000;
    if (substr($dz, -7) === 'NotesKeywords') $limit = 50000;
    if (Log::$cy) __log('Backup: chunk size ' . (int)$limit);
    return $limit;
}

function e2_write_table_data($fy, $n3v, $dz)
{
    $dj    = e2_get_total_records($n3v, $dz);
    $nl    = 0;
    $limit = e2_backup_select_chuck_size_for_table_($dz);
    $q1    = true;
    $q3v   = 20000;
    $l3v   = 30;
    if ($dj) {
        $z3v = e2_table_disable_keys($dz);
        fwrite($fy, $z3v);
    }
    $s3v = "INSERT INTO `{$dz}` VALUES";
    $k3v = $dj;
    while ($k3v > 0) {
        $bf  = "SELECT * FROM `{$dz}` ORDER BY `ID` LIMIT {$nl}, {$limit}";
        $q1  = mysqli_query($n3v, $bf);
        $x3v = mysqli_num_rows($q1);
        if (!$q1 || !e2_check_timeout()) {
            $q1 = false;
            break;
        }
        $e3v = array();
        $r3v = 0;
        $t3v = 0;
        while ($u4 = mysqli_fetch_row($q1)) {
            if (!e2_check_timeout()) {
                $q1 = false;
                break;
            }
            $x3v--;
            $zz = array();
            foreach ($u4 as $s) {
                $zz[] = is_null($s) ? "NULL" : "'" . mysqli_real_escape_string($n3v, $s) . "'";
            }
            $lv    = '(' . join(', ', $zz) . ')';
            $r3v   += strlen($lv);
            $e3v[] = $lv;
            $t3v++;
            if (($r3v >= $q3v) || ($t3v >= $l3v) || ($x3v == 0)) {
                $bf = $s3v . join(', ', $e3v) . ';';
                fwrite($fy, $bf);
                fwrite($fy, PHP_EOL);
                $r3v = 0;
                $t3v = 0;
                $e3v = array();
            }
        }
        $nl  += $limit;
        $k3v -= $limit;
    }
    if ($dj) {
        $j3v = e2_table_enable_keys($dz);
        fwrite($fy, $j3v);
    }
    return $q1;
}

function e2_backup($n3v, $l7, $h3v, $cb = array())
{
    $g3v = tmpfile();
    e2_write_dump_header($g3v);
    if (Log::$cy) __log('Backup: wrote header');
    $w3v = true;
    foreach ($l7 as $dz) {
        if (Log::$cy) __log('Backup: table ' . $dz);
        $u3v = e2_write_table_definition($g3v, $n3v, $dz);
        if (Log::$cy) __log('Backup: wrote table definition with result ' . @(int)$u3v);
        $i3v = e2_write_table_data($g3v, $n3v, $dz);
        if (Log::$cy) __log('Backup: wrote table data with result ' . @(int)$i3v);
        $w3v = $u3v && $i3v;
        if ($w3v === false) {
            break;
        }
    }
    if (Log::$cy) __log('Backup: wrote data with running == ' . (int)$w3v);
    if ($w3v) {
        e2_write_dump_footer($g3v);
        fseek($g3v, 0);
        $fy = fopen($h3v, 'w+');
        while ($w3v && ($lv = fread($g3v, 1024))) {
            if (e2_check_timeout()) {
                fwrite($fy, $lv);
            } else {
                $w3v = false;
            }
        }
        fclose($fy);
    }
    fclose($g3v);
    return $w3v;
}

function f1($o3v, $content)
{
    $p3v = MTMPL_FOLDER . $o3v . '.mtmpl.php';
    if (is_file($p3v)) {
        ob_start();
        include $p3v;
        $z8 = ob_get_contents();
        ob_end_clean();
        return trim($z8);
    }
}

function d1()
{
    global $_config, $_superconfig;
    $cyv = $_config['mail_from'];
    if (@$_superconfig['mail_from']) {
        $cyv = $_superconfig['mail_from'];
    }
    if ($cyv[strlen($cyv) - 1] == '@') {
        $cyv .= $_SERVER['HTTP_HOST'];
    }
    return $cyv;
}

function s1($w, $subject, $xn, $vyv = '')
{
    global $_superconfig;
    if (@$_superconfig['mail_debug']) {
        $ta = 'mail-debug';
        $dn = basename(tempnam($ta, 'm-'));
        $tv = ('To:       ' . $w . "\n" . 'Subject:  ' . $subject . "\n" . $vyv . "\n" . "--------------------------------------------------\n" . $xn);
        n3($ta . '/' . $dn, $tv);
        chmod($ta . '/' . $dn, E2_NEW_FILES_RIGHTS);
        rename($ta . '/' . $dn, $ta . '/' . $dn . '.txt');
    }
    $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $vyv     .= "\r\nContent-Type: text/plain; charset=utf-8";
    if (MAIL_ENABLED) {
        mail($w, $subject, $xn, trim($vyv));
    }
}

function _A($tv)
{
    global $_candy, $_protocol, $v, $c, $_current_url;
    if (preg_match('/\<a href\=\"(.*?)\"[^>]*\>(.*?)\<\/a\>/si', $tv, $y3) and ($y3[1] === '' or $y3[1] === $_current_url or $_protocol . '://' . $v . $y3[1] === $_current_url or $_protocol . '://' . $v . $c . '/' . $y3[1] === $_current_url or $_candy == 'e2m_install')) {
        return $y3[2];
    } else {
        return $tv;
    }
}

function _AT($tq)
{
    global $_protocol, $v, $c, $_current_url;
    return ($tq === '' or $tq === $_current_url or $_protocol . '://' . $v . $tq === $_current_url or $_protocol . '://' . $v . $c . '/' . $tq === $_current_url);
}

function _READS($zx)
{
    if (!empty ($zx['read-count'])) return $zx['read-count'];
    return AeNoteReadCountsProvider:: getReadCountForNoteID($zx['id']);
}

function _IMGSRC($fb)
{
    return us($fb);
}

function _SVG($fb)
{
    return is($fb);
}

function _COLOR($xx, $ex, $byv, $yyv = 1)
{
    if (strlen($xx) != 3 and strlen($xx) != 6) return 'f0f';
    if (strlen($ex) != 3 and strlen($ex) != 6) return 'f0f';
    if (strlen($xx) == 3) $xx = $xx[0] . $xx[0] . $xx[1] . $xx[1] . $xx[2] . $xx[2];
    if (strlen($ex) == 3) $ex = $ex[0] . $ex[0] . $ex[1] . $ex[1] . $ex[2] . $ex[2];
    $hz = array($xx[0] . $xx[1], $xx[2] . $xx[3], $xx[4] . $xx[5], $ex[0] . $ex[1], $ex[2] . $ex[3], $ex[4] . $ex[5],);
    foreach ($hz as $t => $xf) {
        $hz[$t] = hexdec($xf);
    }
    $ty  = array($hz[0] + pow($byv, $yyv) * ($hz[3] - $hz[0]), $hz[1] + pow($byv, $yyv) * ($hz[4] - $hz[1]), $hz[2] + pow($byv, $yyv) * ($hz[5] - $hz[2]),);
    $nyv = '';
    foreach ($ty as $t => $xf) {
        $nyv .= str_pad(dechex($xf), 2, '0', STR_PAD_LEFT);
    }
    return $nyv;
}

function _DT($hl, $myv)
{
    if (!$myv) return '';
    list ($m4, $zl) = $myv;
    $d  = $hl;
    $n9 = ky('m', $m4, $zl);
    $d  = str_replace('{zone}', e2__escape_all(my($zl['offset'])), $d);
    $d  = str_replace('{month}', e2__escape_all(e2l_get_string('um--month', array('month' => $n9))), $d);
    $d  = str_replace('{month-short}', e2__escape_all(e2l_get_string('um--month-short', array('month' => $n9))), $d);
    $d  = str_replace('{month-g}', e2__escape_all(e2l_get_string('um--month-g', array('month' => $n9))), $d);
    $d  = ky($d, $m4, $zl);
    return $d;
}

function _AGO($myv)
{
    return oy($myv[0], array('offset' => $myv[1]['offset'], 'is_dst' => $myv[1]['is_dst']));
}

function _NUM($tv)
{
    return e2_decline_for_number($tv);
}

function _CSS($fyv)
{
    return js($fyv);
}

function _CSS_HREF($fyv)
{
    return os($fyv);
}

function _JS($dyv)
{
    return hs($dyv);
}

function _LIB($do_)
{
    return gs($do_);
}

function _T($bo)
{
    echo rs($bo);
}

function _T_DEFER($name)
{
    echo ks($name);
}

function _X($bo)
{
    echo es($bo);
}

function _T_FOR($bo, $syv = null)
{
    global $content;
    if ($syv === null) $syv = $bo;
    if (array_key_exists($syv, $content)) {
        echo rs($bo);
    } else {
        echo '';
    }
}

function _FIT($vp, $bp)
{
}

function _GUIDES($ayv = false)
{
    global $_olba_guides;
    if (is_array($ayv)) $_olba_guides = $ayv;
    if (!is_array($_olba_guides)) return;
    $qyv   = '<div style="position: fixed; width: 100%; height: 100%; z-index: -100">';
    $lyv   = 0;
    $zyv   = $_olba_guides;
    $zyv[] = 100;
    foreach ($zyv as $r => $c7) {
        if ($c7 == 100) break;
        $lyv += $c7;
        $qyv .= '<div style="position: fixed; left: ' . $c7 . '%; width: 0; height: 100%; border-left: 1px #000 dotted; opacity: .2; -webkit-opacity: .2; -moz-opacity: .2"></div>';
        $kyv = 'position: absolute; padding: 2px 3px; top: 0; font-size: 9px; background: #ccc; color: #000; font-family: "Verdana", sans-serif; opacity: .8; -webkit-opacity: .8; -moz-opacity: .8';
        if ($zyv[$r + 1] - $zyv[$r] < 4) {
            $qyv .= '<div style="' . $kyv . '; right: ' . (100 - $c7) . '%; border-bottom-left-radius: .5em; -webkit-border-bottom-left-radius: .5em; -moz-border-bottom-left-radius: .5em;">' . $c7 . '%</div>';
        } else {
            $qyv .= '<div style="' . $kyv . '; left: ' . $c7 . '%; border-bottom-right-radius: .5em; -webkit-border-bottom-right-radius: .5em; -moz-border-bottom-right-radius: .5em;">' . $c7 . '%</div>';
        }
    }
    $qyv               .= '</div>';
    $_olba_current_col = 0;
    return $qyv;
}

function _S($x)
{
    global $_strings;
    return $_strings[$x];
}

function _SHORTCUT($name)
{
    return a($name);
}

function e2__escape_all($x)
{
    $d = '';
    for ($r = 0; $r < mb_strlen($x); ++$r) {
        $d .= '\\' . mb_substr($x, $r, 1);
    }
    return $d;
}

abstract class E2GIP
{
    protected $gip_cookie_name       = 'gip';
    protected $gip_token_cookie_name = 'gip_access_token';
    protected $gip_token             = null;

    abstract public function get_auth_url();

    abstract public static function get_profile_url($xs, $io);

    abstract public function callback();

    const PHP_VERSION_VK_FEATURE = 70100;

    public static function set_session_data($rm, $s)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION[$rm] = $s;
    }

    public static function get_session_data($rm, $xyv = false)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION[$rm])) {
            return null;
        }
        $s = $_SESSION[$rm];
        if ($xyv) {
            unset($_SESSION[$rm]);
        }
        return $s;
    }

    public static function get_gips_order()
    {
        return ['twitter' => 0, 'facebook' => 1, 'vk' => 2, 'telegram' => 3];
    }

    public function get_config($rm)
    {
        $eyv = 'gips/' . $this->type . '.json';
        if (is_file(USER_FOLDER . $eyv)) {
            $lu = @file_get_contents(USER_FOLDER . $eyv);
        } else {
            $lu = @file_get_contents(SYSTEM_FOLDER . $eyv);
        }
        if ($lu !== false) {
            $d = json_decode($lu, true, 512, JSON_BIGINT_AS_STRING)[$rm];
            if ($d) return $d;
        }
        return null;
    }

    public function get_callback_url()
    {
        return jv('e2m_gip_sign_in_callback', array('provider' => $this->type));
    }

    protected function get_proxy_param()
    {
        global $settings;
        $oo = DEFAULT_LANGUAGE;
        if (array_key_exists('language', $settings)) $oo = $settings['language'];
        return '?language=' . $oo . '&type=' . $this->type . '&callback_url=' . urlencode($this->get_callback_url());
    }

    public function get_gip_session_data()
    {
        global $_config;
        $ryv = $this->gip_token ? $this->gip_token : $_COOKIE[b($this->gip_token_cookie_name)];
        xn("SELECT * FROM `" . $_config['db_table_prefix'] . "GIPsSessions` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `GIP` = '" . $this->type . "' " . "AND `SessionToken` = '" . rn($ryv) . "' " . "ORDER BY `ID` DESC LIMIT 1", 'get GIP session data');
        $q1 = en();
        return $q1 ? $q1[0] : array();
    }

    public function is_logged_in()
    {
        if (empty($_COOKIE[b($this->gip_cookie_name)]) || !in_array($_COOKIE[b($this->gip_cookie_name)], e2_list_gips()) || $_COOKIE[b($this->gip_cookie_name)] != $this->type || empty($_COOKIE[b($this->gip_token_cookie_name)])) {
            return false;
        }
        $lv = $this->get_gip_session_data();
        return (bool)$lv;
    }

    protected function save_session($xs, $name, $accessToken, $tyv = '', $userEmail = '', $userLink = '')
    {
        $m4 = time();
        yn('GIPsSessions', ['GIP' => $this->type, 'GIPAuthorID' => $xs, 'AuthorName' => $name, 'AuthorEmail' => $userEmail, 'AuthorProfileLink' => $userLink, 'SessionToken' => $accessToken, 'Stamp' => $m4,], 'INSERT', 'ON DUPLICATE KEY UPDATE ' . '`SessionToken` = "' . rn($accessToken) . '", ' . '`AuthorName` = "' . rn($name) . '", ' . '`Stamp` = "' . $m4 . '"');
        y($this->gip_cookie_name, $this->type);
        y($this->gip_token_cookie_name, $accessToken);
        if (isset($userEmail) && !empty($userEmail)) y('commenter_email', $userEmail);
        $this->gip_token = $accessToken;
    }

    public static function get_logout_key()
    {
        if ($jyv = self::get_session_data('logout_key')) {
            return $jyv;
        }
        $jyv = md5(microtime());
        self::set_session_data('logout_key', $jyv);
        return $jyv;
    }

    public static function is_valid_logout_key($rm)
    {
        $hyv = self::get_session_data('logout_key', true);
        if (empty($hyv) || empty($rm) || $hyv != $rm) {
            return false;
        }
        return true;
    }

    public function logout()
    {
        global $_config;
        y($this->gip_cookie_name);
        y($this->gip_token_cookie_name);
        xn("DELETE FROM `" . $_config['db_table_prefix'] . "GIPsSessions` " . "WHERE `SubsetID`=" . $_config['db_table_subset'] . " " . "AND `GIP` = '" . $this->type . "' " . "AND `SessionToken` = '" . rn($_COOKIE[b($this->gip_token_cookie_name)]) . "'", 'logout');
    }

    public function get_avatar_width()
    {
        return USERPIC_WIDTH;
    }

    public function get_avatar_height()
    {
        return USERPIC_HEIGHT;
    }

    public function save_avatar($xs, $gyv)
    {
        global $_config;
        if (!preg_match('/^https?\:\/\//i', $gyv)) return;
        $gyv = str_replace("\0", '', $gyv);
        @j(MEDIA_ROOT_FOLDER . AVATARS_FOLDER);
        @chmod(MEDIA_ROOT_FOLDER . AVATARS_FOLDER, $_config['uploaded_files_mode']);
        $fb = MEDIA_ROOT_FOLDER . AVATARS_FOLDER . $this->type . '-' . $xs . '.jpg';
        if ($wyv = file_get_contents($gyv)) {
            file_put_contents($fb, $wyv);
        }
        return $fb;
    }
}

function e2m_gip_sign_in($lv)
{
    global $_config, $settings;
    $type = $lv['provider'];
    $bk   = e2_get_gip_instance($type);
    if (!$bk) v();
    header('Location: ' . $bk->get_auth_url());
    die;
}

function e2m_gip_sign_in_callback($lv)
{
    global $_config;
    $type = $lv['provider'];
    $bk   = e2_get_gip_instance($type);
    if (!$bk) {
        die ('Unknown provider');
    }
    $uyv = $bk->callback();
    echo '<script>';
    if ($uyv === true) {
        $dg  = $bk->get_gip_session_data();
        $iyv = ['name' => $dg['AuthorName'], 'gipIcon' => _SVG($type), 'logoutUrl' => jv('e2m_gip_sign_out', array('provider' => E2GIP::get_logout_key())),];
        echo 'window.opener.oauthAuthorized(' . json_encode($iyv) . ');';
    } else {
        echo 'alert (\'' . $uyv . '\');';
    }
    echo 'window.close();</script>';
    die;
}

function e2m_gip_sign_out($lv)
{
    global $_config;
    $jyv = $lv['provider'];
    if (!E2GIP::is_valid_logout_key($jyv)) {
        die('invalid logout key');
    }
    $bk = e2_get_logged_gip();
    if ($bk) {
        $bk->logout();
    }
    v();
}

function e2_list_gips()
{
    static $oyv = null;
    if (!is_null($oyv)) {
        return $oyv;
    }
    $pyv = SYSTEM_FOLDER . 'gips/';
    $cnv = opendir($pyv);
    $oyv = [];
    $vnv = E2GIP::get_gips_order();
    $bnv = count($vnv);
    while (($fy = readdir($cnv)) !== false) {
        if (pathinfo($fy, PATHINFO_EXTENSION) != 'php') continue;
        $ynv = pathinfo($fy, PATHINFO_FILENAME);
        if ($ynv == 'vk') {
            if (PHP_VERSION_ID < E2GIP::PHP_VERSION_VK_FEATURE) continue;
        }
        $rm       = isset($vnv[$ynv]) ? $vnv[$ynv] : ++$bnv;
        $oyv[$rm] = $ynv;
    }
    closedir($cnv);
    ksort($oyv);
    return $oyv;
}

function e2_get_gip_class_name($type)
{
    return "E2GIP" . ucfirst($type);
}

function e2_get_gip_instance($type)
{
    if (!in_array($type, e2_list_gips())) {
        return false;
    }
    $nnv = e2_get_gip_class_name($type);
    $bk  = new $nnv;
    return $bk;
}

function e2_get_gip_auth_url($type)
{
    return jv('e2m_gip_sign_in', array('provider' => $type));
}

function e2_is_logged_in($type = '')
{
    $mnv = !$type ? e2_list_gips() : array($type);
    foreach ($mnv as $type) {
        $bk = e2_get_gip_instance($type);
        if ($bk && $bk->is_logged_in()) {
            return true;
        }
    }
    return false;
}

function e2_get_logged_gip()
{
    foreach (e2_list_gips() as $type) {
        $bk = e2_get_gip_instance($type);
        if ($bk && $bk->is_logged_in()) {
            return $bk;
        }
    }
    return false;
}

function e2_get_logged_gip_name()
{
    foreach (e2_list_gips() as $type) {
        $bk = e2_get_gip_instance($type);
        if ($bk && $bk->is_logged_in()) {
            return $type;
        }
    }
    return false;
}

function e2_get_user_profile_url($type, $xs, $io)
{
    $nnv = e2_get_gip_class_name($type);
    return $nnv::get_profile_url($xs, $io);
}

function e2_get_gip_session($type)
{
    $bk = e2_get_gip_instance($type);
    if (!$bk || !$bk->is_logged_in()) {
        return false;
    }
    return $bk->get_gip_session_data();
}

foreach (e2_list_gips() as $mg) {
    require_once 'system/gips/' . $mg . '.php';
}
define('__DEV', (@$_config['dev_verbose'] > (int)!k2()));
$_stopwatch = w($_stopwatch);
spl_autoload_register('ka');
vv();
o();
$_strings = cv();
if (!BUILT) @include 'builder.php';
function e2()
{
    global $settings, $content, $_candy, $_lang, $_config, $_strings, $_candies_installer, $_candies_public, $_candies_ajax, $_candies_to_disallow_in_read_only, $_template, $_diagnose;
    k();
    set_error_handler('dv');
    set_exception_handler('ev');
    header('X-Powered-By: E2 Aegea v' . E2_VERSION);
    header('Content-type: text/html; charset=UTF-8');
    list ($candy, $parameters) = hv();
    try {
        $content = [];
        $_candy  = $candy;
        if (@$_config['dev_slow_ajax'] and (in_array($candy, $_candies_ajax))) {
            sleep(1 + 2 * (rand() / getrandmax()));
        }
        if (!in_array($candy, $_candies_installer)) {
            an();
        }
        if (@$_config['read_only'] and in_array($candy, $_candies_to_disallow_in_read_only)) {
            $candy = 'e2m_error404';
        }
        $fnv = (bool)k2();
        $dnv = !in_array($candy, $_candies_public);
        if (Log::$cy) __log('User signed in? ' . ($fnv ? 'Yes' : 'No'));
        $_newsfeeds = [];
        zd('rss', cd(), jv('e2m_rss'));
        zd('json', cd(), jv('e2m_json'));
        if (substr($candy, 0, 4) == 'e2m_') {
            qs();
        }
        if (is_callable($candy)) {
            if ($dnv && !$fnv) {
                if (substr($candy, 0, 4) == 'e2s_') {
                    c(jv('e2m_sign_in'));
                } else {
                    $content['title'] = $_strings['pt--sign-in'];
                }
            } else {
                if (Log::$cy) __log('Candy call {');
                $content = call_user_func($candy, $parameters);
                if (Log::$cy) __log('}');
            }
        } else {
            $dnv     = false;
            $content = e2_error404_mode();
        }
    } catch (AeMySQLException $e) {
        if (substr($candy, 0, 4) == 'e2s_') {
            xv($e);
        } else {
            kv($e);
            $parameters              = array();
            $content['unavailable?'] = true;
        }
    }
    if (!is_array($content)) $content = array();
    $content['template']['respond-to-dark-mode?'] = ($_template['supports_dark_mode'] and (bool)@$settings['appearance']['respond_to_dark_mode']);
    $content['template']['use-likely-light?']     = $_template['use_likely_light'];
    if (!array_key_exists('class', $content)) {
        $content['class'] = str_replace('_', '-', str_replace('e2m_', '', $candy));
    }
    if (!array_key_exists('notes', $content)) $content['notes'] = [];
    if (!array_key_exists('drafts', $content)) $content['drafts'] = [];
    if (!array_key_exists('comments', $content)) $content['comments'] = [];
    if (!array_key_exists('notes-list', $content)) $content['notes-list'] = [];
    if (fn_() !== null) {
        if (Log::$cy) __log('Stuff for installed engine {');
        $content['sign-in'] = ['done?' => $fnv, 'required?' => $dnv, 'necessary?' => $dnv && !$fnv, 'href' => jv('e2m_sign_in'), 'prompt' => $_strings['gs--need-password'],];
        $content['hrefs']   = array('everything' => jv('e2m_everything'),);
        if (!array_key_exists('tags', $content)) $content['tags'] = ff($parameters);
        $content['blog']        = p2();
        $content['form-search'] = fa($parameters);
        $content['engine']      = im();
        $content['form-login']  = o2();
        if ($content['form-login'] === null) unset($content['form-login']);
        if (!array_key_exists('summary', $content)) {
            if (!empty ($settings['meta_description'])) {
                $content['summary'] = strip_tags(h3(htmlspecialchars($settings['meta_description'], ENT_NOQUOTES, HSC_ENC)));
            } else {
                $content['summary'] = @trim(strip_tags($content['blog']['subtitle']));
            }
        }
        if (k2()) {
            $content['admin']                = pm();
            $content['last-modifieds-by-id'] = '{}';
            if (@$_COOKIE[b('local_copies')]) {
                $content['last-modifieds-by-id'] = (dm($_COOKIE[b('local_copies')]));
            }
        }
        if (Log::$cy) __log('}');
    }
    $content['title'] = strip_tags(h3(htmlspecialchars($content['title'], ENT_NOQUOTES, HSC_ENC)));
    if (@$content['heading']) {
        $content['heading'] = strip_tags(h3(htmlspecialchars($content['heading'], ENT_NOQUOTES, HSC_ENC)));
    }
    $content['language'] = $_lang;
    if (!@isset ($_diagnose['ok?'])) {
        if (@$_COOKIE[b('diagnose')] or @$_diagnose['need?']) {
            fv();
        }
    }
    if (fn_() !== null) {
        if (@$settings['appearance']['show_view_counts']) {
            AeNoteReadCountsProvider:: setSQLRequestTemplateToMapIDsToReadCounts("SELECT `ID`, `ReadCount` " . "FROM `" . $_config['db_table_prefix'] . "Notes` " . "WHERE `SubsetID`=" . $_config['db_table_subset']);
        }
        foreach ($content['notes'] as $l2) {
            va(@$l2['format-info']['links-required']);
        }
    }
    $content['message'] = av();
    $oi                 = ts();
    $content['meta']    = om($candy, $content['notes'], @$content['tag'], $content['blog'], $content['pages']);
    $content['stat']    = yd();
    $oi                 = xs($oi);
    if (fn_() !== null) {
        $snv = false;
        if (fn_() !== null and qa()) {
            if (is_writable(USER_FOLDER . 'indexing.psa')) {
                $snv = true;
            } else {
                $_diagnose['need?'] = true;
                y('diagnose', '1');
            }
        }
    }
    echo $oi;
    if (fn_() !== null) {
        if ($snv) {
            if (Log::$cy) __log('Spawn BSI step');
            p3(jv('e2s_bsi_step', array()));
        }
    }
    if (@$_config['dev_dump_ctree']) yv($content);
} ?>
