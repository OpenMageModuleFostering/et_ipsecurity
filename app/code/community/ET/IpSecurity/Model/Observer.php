<?php
/**
 * NOTICE OF LICENSE
 *
 * You may not sell, sub-license, rent or lease
 * any portion of the Software or Documentation to anyone.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   ET
 * @package    ET_IpSecurity
 * @copyright  Copyright (c) 2012 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://shop.etwebsolutions.com/etws-license-free-v1/   ETWS Free License (EFL1)
 */

/**
 * Class ET_IpSecurity_Model_Observer
 */
class ET_IpSecurity_Model_Observer
{
    const TOKEN_COOKIE_NAME = 'ipsecurity_token';

    /**
     * Rss with admin authentication
     * @var array
     */
    protected $_requestPathList = array(
        '/rss/order/new',
        '/rss/catalog/notifystock',
        '/rss/catalog/review'
    );

    protected $_redirectPage = null;
    protected $_redirectBlank = null;
    protected $_rawAllowIpData = null;
    protected $_rawBlockIpData = null;
    protected $_rawExceptIpData = null;
    protected $_eventEmail = "";
    protected $_emailTemplate = 0;
    protected $_emailIdentity = null;
    protected $_storeType = null;
    protected $_lastFoundIp = null;
    protected $_isFrontend = false;
    protected $_isDownloader = false;
    protected $_alwaysNotify = false;

    protected $_eventEmailToken = "";
    protected $_alwaysNotifyToken = false;
    protected $_emailTemplateToken = 0;
    protected $_emailTemplateTokenFail;
    protected $_emailIdentityToken = null;

    protected static $_flagCheckToken = 0;

    /**
     * If loading Frontend
     *
     * Event: controller_action_predispatch
     * @param $observer
     */
    public function onLoadingFrontend($observer)
    {
        $this->_readFrontendConfig();
        $this->_readTokenConfig();
        $this->_processIpCheck($observer);
    }

    /**
     * If loading Frontend and router is "rss"
     *
     * Event: controller_front_init_routers
     * @param Varien_Event_Observer $observer
     */
    public function onLoadingRss($observer)
    {
        foreach ($this->_requestPathList as $pattern) {
            if (strpos(Mage::app()->getRequest()->getPathInfo(), $pattern) !== false) {
                /** @var ET_IpSecurity_Helper_Data $helper */
                $helper = Mage::helper('etipsecurity');
                $helper->log('onLoadingRss()');

                $eventName = (string)$observer->getEvent()->getName();
                $helper->log('event Name: ' . $eventName);

                $this->_readAdminConfig();
                $this->_readTokenConfig();
                $this->_processIpCheck($observer);
            }
        }
    }

    /**
     * If loading Admin
     *
     * Event: controller_action_predispatch
     * @param $observer
     */
    public function onLoadingAdmin($observer)
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');
        $helper->log('onLoadingAdmin()');

        $eventName = (string)$observer->getEvent()->getName();
        $helper->log('event Name: ' . $eventName);

        $this->_readAdminConfig();
        $this->_readTokenConfig();
        $this->_processIpCheck($observer);
    }

    /**
     * On failed login to Admin
     *
     * @param $observer
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onAdminLoginFailed($observer)
    {
        // TODO: for http://support.etwebsolutions.com/issues/371
    }

    /**
     * On loading Downloader
     *
     * Event: controller_front_init_routers
     * @param Varien_Event_Observer $observer
     */
    public function onLoadingDownloader($observer)
    {
        //only in downloader exists Maged_Controller class
        if (class_exists("Maged_Controller", false)) {
            $this->_readDownloaderConfig();
            $this->_processIpCheck($observer);
        }
    }

    /**
     * Reading configuration for Frontend
     */
    protected function _readFrontendConfig()
    {
        $this->_redirectPage = $this->trimTrailingSlashes(
            Mage::getStoreConfig('etipsecurity/ipsecurityfront/redirect_page'));
        $this->_redirectBlank = Mage::getStoreConfig('etipsecurity/ipsecurityfront/redirect_blank');
        $this->_rawAllowIpData = Mage::getStoreConfig('etipsecurity/ipsecurityfront/allow');
        $this->_rawBlockIpData = Mage::getStoreConfig('etipsecurity/ipsecurityfront/block');
        $this->_eventEmail = Mage::getStoreConfig('etipsecurity/ipsecurityfront/email_event');
        $this->_emailTemplate = Mage::getStoreConfig('etipsecurity/ipsecurityfront/email_template');
        $this->_emailIdentity = Mage::getStoreConfig('etipsecurity/ipsecurityfront/email_identity');
        $this->_alwaysNotify = Mage::getStoreConfig('etipsecurity/ipsecurityfront/email_always');
        $this->_rawExceptIpData = Mage::getStoreConfig('etipsecurity/ipsecuritymaintetance/except');

        $this->_storeType = Mage::helper("catalog")->__("Frontend");
        $this->_isFrontend = true;
    }


    /**
     * Reading configuration for Admin
     */
    protected function _readAdminConfig()
    {
        $this->_redirectPage = $this->trimTrailingSlashes(
            Mage::getStoreConfig('etipsecurity/ipsecurityadmin/redirect_page'));
        $this->_redirectBlank = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/redirect_blank');
        $this->_rawAllowIpData = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/allow');
        $this->_rawBlockIpData = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/block');
        $this->_eventEmail = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/email_event');
        $this->_emailTemplate = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/email_template');
        $this->_emailIdentity = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/email_identity');
        $this->_alwaysNotify = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/email_always');

        $this->_storeType = Mage::helper("core")->__("Admin");
        $this->_isFrontend = false;
    }

    /**
     * load Token config
     */
    protected function _readTokenConfig()
    {
        $this->_eventEmailToken = Mage::getStoreConfig('etipsecurity/ipsecuritytoken/email_event');
        $this->_alwaysNotifyToken = Mage::getStoreConfig('etipsecurity/ipsecuritytoken/email_always');
        $this->_emailTemplateToken = Mage::getStoreConfig('etipsecurity/ipsecuritytoken/email_template');
        $this->_emailTemplateTokenFail = Mage::getStoreConfig('etipsecurity/ipsecuritytoken/fail_email_template');
        $this->_emailIdentityToken = Mage::getStoreConfig('etipsecurity/ipsecuritytoken/email_identity');
    }


    /**
     * Read configuration for Downloader (used Admin config)
     */
    protected function _readDownloaderConfig()
    {
        $this->_readAdminConfig();
        $this->_storeType = Mage::helper("etipsecurity")->__("Downloader");
        $this->_isDownloader = true;

        // TODO: заглушка. Если страницы для перехода не существует,
        // то поиск ссылки на no-rout вызывет ошибку.
        //$this->_redirectBlank = true;
    }

    /**
     * Get current Scope (frontend, admin, downloader)
     *
     * @return string
     */
    protected function _getScopeName()
    {
        if ($this->_isFrontend) {
            $scope = 'frontend';
        } elseif ($this->_isDownloader) {
            $scope = 'downloader';
        } else {
            $scope = 'admin';
        }

        return $scope;
    }

    /**
     * Checking current ip for rules
     *
     * @param Varien_Event_Observer $observer
     * @return ET_IpSecurity_Model_Observer
     */
    protected function _processIpCheck($observer)
    {
        $currentIp = $this->getCurrentIp();
        //error or IPv6 or localhost
        if (is_null($currentIp) || $currentIp === "127.0.0.1") {
            return $this;
        }

        $allowIps = $this->_ipTextToArray($this->_rawAllowIpData);
        $blockIps = $this->_ipTextToArray($this->_rawBlockIpData);

        $allow = $this->isIpAllowed($currentIp, $allowIps, $blockIps);

        if (!$allow) {
            $allow = $this->_checkSecurityTokenAccess($observer);
        }

        $this->_processAllowDeny($allow, $currentIp);

        return $this;
    }


    /**
     * check Access By Token
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    protected function _checkSecurityTokenAccess(Varien_Event_Observer $observer)
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');
        $helper->log('_checkSecurityTokenAccess()');

        $access = false;

        // if Module Enabled && Not Empty Url and Token
        if (($helper->isEnabledIpSecurityToken()) && ($helper->isSetTokenLastUpdateAndUrl())) {

            $helper->log('IpSecurityToken: Enabled');

            /** @var ET_IpSecurity_Model_System_Config_Source_Token_Expire $tokenModel */
            $tokenModel = Mage::getModel('etipsecurity/system_config_source_token_expire');

            if (!$tokenModel->isTokenExpired()) {
                $helper->log('token not expired');

                $tokenValueConfig = $helper->getTokenValue();

                $access = $this->_checkAccessByCookie($tokenValueConfig);

                if (!$access) {
                    $access = $this->_checkAccessByToken($observer, $tokenValueConfig);
                }

            } else {
                // log token expired
                $helper->log('token expired');
            }
        } else {
            $helper->log('IpSecurityToken: Disabled');
        }

        return $access;
    }

    /**
     * send Token email notification
     *
     * @param bool $success
     * @throws Mage_Core_Exception
     */
    protected function _notifyLoginByToken($fullUrl, $success)
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');
        $helper->log('_notifyLoginByToken()');

        if ($success) {
            $template = $this->_emailTemplateToken;
        } else {
            $template = $this->_emailTemplateTokenFail;
        }

        if (!$this->_eventEmailToken && (!$template)) {
            return;
        }

        $currentIp = $this->getCurrentIp();
        $recipients = explode(",", $this->_eventEmailToken);

        /* @var Mage_Core_Model_Email_Template $emailTemplate */
        $emailTemplate = Mage::getModel('core/email_template')->setDesignConfig(array('area' => 'backend'));

        $coreHelper = Mage::helper('core');

        foreach ($recipients as $recipient) {

            try {
                $emailTemplate
                    ->sendTransactional(
                        $template,
                        $this->_emailIdentityToken,
                        trim($recipient),
                        trim($recipient),
                        array(
                            'ip' => $currentIp,
                            'ip_rule' => Mage::helper('etipsecurity')->__($this->getLastBlockRule()),
                            'date' => $coreHelper->formatDate(null, Mage_Core_Model_Locale::FORMAT_TYPE_FULL, true),
                            'storetype' => $this->_storeType,
                            'url' => $fullUrl,
                            'info' => base64_encode(serialize(array($this->_rawAllowIpData, $this->_rawBlockIpData))),
                        )
                    );
            } catch (Exception $ex) {
                $helper->log($ex);
            }
        }
    }


    /**
     * @param Varien_Event_Observer $observer
     * @param string $tokenValueConfig
     * @return bool
     */
    protected function _checkAccessByToken($observer, $tokenValueConfig)
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');
        $helper->log('_checkAccessByToken()');

        $access = false;

        /** @var Mage_Cms_IndexController $controller */
        $controller = $observer->getControllerAction();
        $eventName = (string)$observer->getEvent()->getName();
        $helper->log('event Name: ' . $eventName);

        if ($controller) {

            $tokenName = $helper->getTokenName();
            $helper->log('token Name: ' . $tokenName);

            $tokenValueRequest = $controller->getRequest()->getParam($tokenName);

            //$fullUrl = $controller->getRequest()->getServer('HTTP_REFERER');
            //$fullUrl = $controller->getRequest()->getServer('SCRIPT_URI');
            $fullUrl = Mage::helper('core/url')->getCurrentUrl();

            $helper->log('token value request: ' . $tokenValueRequest);
            $helper->log('token value config: ' . $tokenValueConfig);

            if ($tokenValueRequest) {

                if ($tokenValueRequest == $tokenValueConfig) {

                    $helper->setCookieToken(self::TOKEN_COOKIE_NAME, $tokenValueConfig);
                    $access = true;

                    if (!self::$_flagCheckToken) {
                        $this->_addTokenLog($fullUrl, 'Successful token use');

                        $this->_notifyLoginByToken($fullUrl, true);

                        // log logOn By token Ok
                        $helper->log('Successful token use: Ok, set cookie Ok');

                        self::$_flagCheckToken = 1;
                    }

                } else {
                    // log not valid token
                    $helper->log('Unsuccessful token use attempt: not valid token');

                    $this->_addTokenLog($fullUrl, 'Unsuccessful token use attempt');

                    if ($this->_alwaysNotifyToken) {
                        $this->_notifyLoginByToken($fullUrl, false);
                    }
                }
            }
        }

        return $access;
    }

    /**
     * add token Log
     *
     * @param string $message
     */
    protected function _addTokenLog($fullUrl, $message)
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');

        /** @var ET_IpSecurity_Model_Iptokenlog $ipTokenLogModel */
        $ipTokenLogModel = Mage::getModel('etipsecurity/iptokenlog');

        $ipTokenLogModel->setData('blocked_ip', $this->getCurrentIp());

        $ipTokenLogModel->setData('last_block_rule',
            //$helper->__($message)
            $message
        );

        $ipTokenLogModel->setData('create_time', now());

        $helper->log('_addTokenLog():');
        $helper->log('url: ' . $fullUrl);

        $ipTokenLogModel->setData('blocked_from', $fullUrl);

        try {
            $ipTokenLogModel->save();
        } catch (Exception $ex) {
            $helper->log('error Add Token Log: ', $ex);
        }
    }


    /**
     * check access By cookie
     * is set & valid return true
     *
     * @param string $tokenValueConfig
     * @return bool
     */
    protected function _checkAccessByCookie($tokenValueConfig)
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');
        $helper->log('_checkAccessByCookie()');
        $access = false;

        $cookieValue = $helper->getCookie(self::TOKEN_COOKIE_NAME);

        // check cookie if OK set new Time Expire
        if ($cookieValue) {
            if ($cookieValue == $tokenValueConfig) {

                $helper->setCookieToken(self::TOKEN_COOKIE_NAME, $cookieValue);
                $access = true;

                // log cookie update
                $helper->log('cookie valid & update, access: true');
            } else {
                // cookie not valid
                $helper->log('cookie not valid, access: false');
            }
        } else {
            $helper->log('cookie not set');
        }

        return $access;
    }


    /**
     * Check IP for allow/deny rules
     *
     * @param $currentIp string
     * @param $allowIps array
     * @param $blockIps array
     * @return bool
     */
    public function isIpAllowed($currentIp, $allowIps, $blockIps)
    {
        $allow = true;

        # look for allowed
        if ($allowIps) {
            # block all except allowed
            $allow = false;

            # are there any allowed ips
            if ($this->isIpInList($currentIp, $allowIps)) {
                $allow = true;
            }
        }

        # look for blocked
        if ($blockIps) {
            # are there any blocked ips
            if ($this->isIpInList($currentIp, $blockIps)) {
                $allow = false;
            }
        }
        return $allow;
    }

    /**
     * Redirect denied users to block page or show maintenance page to visitor
     *
     * @param $allow boolean
     * @param $currentIp string
     */
    protected function _processAllowDeny($allow, $currentIp)
    {
        $currentPage = $this->trimTrailingSlashes(Mage::helper('core/url')->getCurrentUrl());
        // searching for CMS page storeId
        // (block access to admin redirects to admin)
        $pageStoreId = $this->getPageStoreId();
        if ($pageStoreId !== false) {
            $this->_redirectPage = Mage::getUrl(null, array('_direct' => $this->_redirectPage, "_store" => $pageStoreId));
        } else {
            //no active page to redirect - redirecting to no-route
            $this->_redirectPage = Mage::getUrl('no-route', array("_store" => $pageStoreId));
        }
        $scope = $this->_getScopeName();

        if (!strlen($this->_redirectPage) && !$this->_isDownloader) {
            $this->_redirectPage = $this->trimTrailingSlashes(Mage::getUrl('no-route'));
        }

        if ($this->_redirectBlank == 1 && !$allow) {
            header("HTTP/1.1 403 Forbidden");
            header("Status: 403 Forbidden");
            header("Content-type: text/html");
            $needToNotify = $this->saveToLog(array('blocked_from' => $scope, 'blocked_ip' => $currentIp));
            if (($this->_alwaysNotify) || $needToNotify) {
                $this->_send();
            }
            exit("Access denied for IP:<b> " . $currentIp . "</b>");
        }

        if ($this->trimTrailingSlashes($currentPage) != $this->trimTrailingSlashes($this->_redirectPage) && !$allow) {
            header('Location: ' . $this->_redirectPage);
            $needToNotify = $this->saveToLog(array('blocked_from' => $scope, 'blocked_ip' => $currentIp));
            if (($this->_alwaysNotify) || $needToNotify) {
                $this->_send();
            }
            exit();
        }

        $exceptIps = $this->_ipTextToArray($this->_rawExceptIpData);
        $isMaintenanceMode = Mage::getStoreConfig('etipsecurity/ipsecuritymaintetance/enabled');
        if (($isMaintenanceMode) && ($this->_isFrontend)) {
            $doNotLoadSite = true;
            # look for except
            if ($exceptIps) {
                # are there any except ips
                if ($this->isIpInList($currentIp, $exceptIps)) {
                    Mage::app()->getResponse()->appendBody(
                        html_entity_decode(
                            Mage::getStoreConfig('etipsecurity/ipsecuritymaintetance/remindermessage'),
                            ENT_QUOTES,
                            "utf-8"
                        )
                    );
                    $doNotLoadSite = false;
                }
            }

            if ($doNotLoadSite) {
                header('HTTP/1.1 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 7200'); // in seconds
                print html_entity_decode(
                    Mage::getStoreConfig('etipsecurity/ipsecuritymaintetance/message'),
                    ENT_QUOTES,
                    "utf-8"
                );
                exit();
            }

        }
    }


    /**
     * Get store id of target redirect cms page
     *
     * @return int|
     */

    public function getPageStoreId()
    {
        /* @var $cmsPage Mage_Cms_Model_Page */
        $cmsPage = Mage::getModel('cms/page');
        $storeId = Mage::app()->getStore()->getId();

        //if current store is Admin
        if ($storeId == 0) {
            if (isset($_SERVER["SERVER_NAME"])) {
                /** @var Mage_Core_Model_Store $store */
                foreach (Mage::app()->getStores() as $store) {
                    $url = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, false);
                    //domain check
                    if (strpos($url, $_SERVER["SERVER_NAME"]) !== false) {
                        $redirectPage = $this->trimTrailingSlashes(
                            Mage::getStoreConfig('etipsecurity/ipsecurityadmin/redirect_page', $store->getId()));
                        //store have that page
                        if ($cmsPage->checkIdentifier($redirectPage, $store->getId())) {
                            $this->_redirectPage = $redirectPage;
                            return $store->getId();
                        }
                    }
                }
            }
        }
        //check identifier check page on active and specified store
        $pageId = $cmsPage->checkIdentifier($this->_redirectPage, $storeId);
        if ($pageId > 0) {
            //current store id
            return $storeId;
        }
        //no active redirect page for current store
        return false;
    }


    /**
     * Convert IP range as string to array with first and last IP of range
     *
     * @param $ipRange string
     * @return array[first,last]
     */
    protected function _convertIpStringToIpRange($ipRange)
    {
        $ip = explode("|", $ipRange);
        $ip = trim($ip[0]);
        $simpleRange = explode("-", $ip);
        //for xx.xx.xx.xx-yy.yy.yy.yy
        if (count($simpleRange) == 2) {
            $comparableIpRange = array(
                "first" => $this->_convertIpToComparableString($simpleRange[0]),
                "last" => $this->_convertIpToComparableString($simpleRange[1]));
            return $comparableIpRange;
        }
        //for xx.xx.xx.*
        if (strpos($ip, "*") !== false) {
            $fromIp = str_replace("*", "0", $ip);
            $toIp = str_replace("*", "255", $ip);
            $comparableIpRange = array(
                "first" => $this->_convertIpToComparableString($fromIp),
                "last" => $this->_convertIpToComparableString($toIp));
            return $comparableIpRange;
        }
        //for xx.xx.xx.xx/yy
        $maskRange = explode("/", $ip);
        if (count($maskRange) == 2) {
            $maskMoves = 32 - $maskRange[1];
            $mask = (0xFFFFFFFF >> $maskMoves) << $maskMoves;
            $subMask = 0;
            for ($maskDigits = 0; $maskDigits < $maskMoves; $maskDigits++) {
                $subMask = ($subMask << 1) | 1;
            }
            $fromIp = ip2long($maskRange[0]) & $mask;
            $toIp = long2ip($fromIp | $subMask);
            $fromIp = long2ip($fromIp);
            $comparableIpRange = array(
                "first" => $this->_convertIpToComparableString($fromIp),
                "last" => $this->_convertIpToComparableString($toIp));
            return $comparableIpRange;
        }

        $comparableIpRange = array(
            "first" => $this->_convertIpToComparableString($ip),
            "last" => $this->_convertIpToComparableString($ip)
        );

        return $comparableIpRange;

    }

    /**
     * Convert IP address (x.xx.xxx.xx) to easy comparable string (xxx.xxx.xxx.xxx)
     *
     * @param $ip string
     * @return string
     * @throws Exception
     */
    protected function _convertIpToComparableString($ip)
    {
        $partsOfIp = explode(".", trim($ip));
        if (count($partsOfIp) != 4) {
            throw new Exception("Incorrect IP format: " . $ip);
        }
        $comparableIpString = sprintf(
            "%03d%03d%03d%03d",
            $partsOfIp[0],
            $partsOfIp[1],
            $partsOfIp[2],
            $partsOfIp[3]
        );
        return $comparableIpString;

    }

    /**
     * Is ip in list of IP rules
     *
     * @param $searchIp string
     * @param $ipRulesList array
     * @return bool
     */
    public function isIpInList($searchIp, $ipRulesList)
    {
        $searchIpComparable = $this->_convertIpToComparableString($searchIp);
        if (count($ipRulesList) > 0) {
            foreach ($ipRulesList as $ipRule) {
                $ip = explode("|", $ipRule);
                $ip = trim($ip[0]);
                try {
                    $ipRange = $this->_convertIpStringToIpRange($ip);
                    //var_dump($ipRange);
                    if (count($ipRange) == 2) {
                        $ipFrom = $ipRange["first"];
                        $ipTo = $ipRange["last"];
                        if ((strcmp($ipFrom, $searchIpComparable) <= 0) &&
                            (strcmp($searchIpComparable, $ipTo) <= 0)
                        ) {
                            $this->_lastFoundIp = $ipRule;
                            return true;
                        }
                    }
                } catch (Exception $e) {
                    Mage::log($e->getMessage());
                }
                //}
            }
        }
        return false;
    }

    /**
     * Trim trailing slashes, except single "/"
     *
     * @param $str string
     * @return string
     */
    protected function trimTrailingSlashes($str)
    {
        $str = trim($str);
        return $str == '/' ? $str : rtrim($str, '/');
    }

    /**
     * Send to admin information about IP blocking
     */
    protected function _send()
    {
        $sendResult = false;
        if (!$this->_eventEmail) {
            return $sendResult;
        }
        $currentIp = $this->getCurrentIp();
        //$storeId = 0; //admin

        $recipients = explode(",", $this->_eventEmail);

        /* @var Mage_Core_Model_Email_Template $emailTemplate */
        $emailTemplate = Mage::getModel('core/email_template')->setDesignConfig(array('area' => 'backend'));
        $coreHelper = Mage::helper('core');
        $coreUrlHelper = Mage::helper('core/url');
        foreach ($recipients as $recipient) {
            $sendResult = $emailTemplate
                ->sendTransactional(
                    $this->_emailTemplate,
                    $this->_emailIdentity,
                    trim($recipient),
                    trim($recipient),
                    array(
                        'ip' => $currentIp,
                        'ip_rule' => Mage::helper('etipsecurity')->__($this->getLastBlockRule()),
                        'date' => $coreHelper->formatDate(null, Mage_Core_Model_Locale::FORMAT_TYPE_FULL, true),
                        'storetype' => $this->_storeType,
                        'url' => $coreUrlHelper->getCurrentUrl(),
                        'info' => base64_encode(serialize(array($this->_rawAllowIpData, $this->_rawBlockIpData))),
                    )
                );
        }
        return $sendResult;
    }

    /**
     * Return block rule
     *
     * @return string
     */
    public function getLastBlockRule()
    {
        $lastBlockRule = 'Not in allowed list';
        if (!is_null($this->_lastFoundIp)) {
            $lastBlockRule = $this->_lastFoundIp;
        }
        return $lastBlockRule;
    }

    /**
     * Get IP of current client
     *
     * @return string
     */
    public function getCurrentIp()
    {
        /** @var $helper ET_IpSecurity_Helper_Data */
        $helper = Mage::helper('etipsecurity');
        $selectedIpVariable = $helper->getIpVariable();

        if (isset($_SERVER[$selectedIpVariable])) {
            $currentIp = $_SERVER[$selectedIpVariable];
        } elseif (isset($_SERVER["REMOTE_ADDR"])) { //
            //no default IP variable
            $currentIp = $_SERVER["REMOTE_ADDR"];
        } else {
            //unknown IP
            $currentIp = "0.0.0.0";
        }
        return $this->_getCurrentIp($currentIp, $selectedIpVariable);
    }

    /**
     * HTTP_X_FORWARDED_FOR can return comma delimetered list of IP addresses.
     * We need only one IP address to check
     *
     * @param $currentIp
     * @param $selectedIpVariable
     * @return string
     */
    protected function _getCurrentIp($currentIp, $selectedIpVariable)
    {
        switch ($selectedIpVariable) {
            case 'HTTP_X_FORWARDED_FOR':
                $resultArray = explode(',', $currentIp);
                $result = trim($resultArray[0]);
                break;
            default:
                $result = $currentIp;
        }
        //IPv6 127.0.0.1
        if ($result == "::1") {
            $result = "127.0.0.1";
        } elseif (substr_count($result, ':') > 0) {
            //finding ipv4 part
            $ipVFourArray = explode(".", $result);
            //IPv4-compatible IPv6
            if (count($ipVFourArray) == 4) {
                $ipVFourArray[0] = array_pop(explode(":", $ipVFourArray[0]));
                return implode(".", $ipVFourArray);
            }
            //no real ip4 address
            return null;
        }

        return $result;
    }

    /**
     * Convert string with IP to IP array
     *
     * @param $text string
     * @return array
     */
    protected function _ipTextToArray($text)
    {
        $ips = preg_split("/[\n\r]+/", $text);
        foreach ($ips as $ipsk => $ipsv) {
            if (trim($ipsv) == "") {
                unset($ips[$ipsk]);
            }
        }
        return $ips;
    }

    /**
     * Save Blocked IP to log
     *
     * @param array $params
     * @return bool
     */
    protected function saveToLog($params = array())
    {
        $needNotify = true;

        if (!((isset($params['blocked_ip'])) && (strlen(trim($params['blocked_ip'])) > 0))) {
            $params['blocked_ip'] = $this->getCurrentIp();
        }

        if (!((isset($params['blocked_from'])) && (strlen(trim($params['blocked_from'])) > 0))) {
            $params['blocked_from'] = 'undefined';
        }

        $now = now();

        /* @var $logTable ET_IpSecurity_Model_Mysql4_Ipsecuritylog_Collection */
        $logTable = Mage::getModel('etipsecurity/ipsecuritylog')->getCollection();
        $logTable->getSelect()->where('blocked_from=?', $params['blocked_from'])
            ->where('blocked_ip=?', $params['blocked_ip']);

        if (count($logTable) > 0) {
            foreach ($logTable as $row) {
                /* @var $row ET_IpSecurity_Model_Ipsecuritylog */
                $timesBlocked = $row->getData('qty') + 1;
                $row->setData('qty', $timesBlocked);
                $row->setData('last_block_rule', $this->getLastBlockRule());
                $row->setData('update_time', $now);
                $row->save();
                if (($timesBlocked % 10) == 0) {
                    $needNotify = true;
                } else {
                    $needNotify = false;
                }
            }
        } else {
            /** @var ET_IpSecurity_Model_Ipsecuritylog $log */
            $log = Mage::getModel('etipsecurity/ipsecuritylog');

            $log->setData('blocked_from', $params['blocked_from']);
            $log->setData('blocked_ip', $params['blocked_ip']);
            $log->setData('qty', '1');
            $log->setData('last_block_rule', $this->getLastBlockRule());
            $log->setData('create_time', $now);
            $log->setData('update_time', $now);

            $log->save();
            $needNotify = true;
        }

        // if returns true - IP blocked for first time or timesBloked is 10, 20, 30 etc.
        return $needNotify;
    }

}