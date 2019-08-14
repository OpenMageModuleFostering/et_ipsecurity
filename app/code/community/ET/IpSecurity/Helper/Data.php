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
 * Class ET_IpSecurity_Helper_Data
 */
class ET_IpSecurity_Helper_Data extends Mage_Core_Helper_Abstract
{
    const MODULE_NAME = 'etipsecurity';
    const MODULE_LOG_FOLDER = 'etipsecurity';

    const MESSAGE_TOKEN_NOT_CREATED = 'Token not Created';
    const MESSAGE_TOKEN_NOT_UPDATED = 'Token not Created';


    /**
     * @param $cookieName
     * @return mixed
     */
    public function getCookie($cookieName)
    {
        return Mage::getModel('core/cookie')->get($cookieName);
    }

    /**
     * set cookie token
     *
     * @param string $cookieName
     * @param string $cookieValue
     */
    public function setCookieToken($cookieName, $cookieValue)
    {
        $cookieTime = Mage::getModel('etipsecurity/system_config_source_cookie_expire')->getCookieExpiredTime();
        $this->setCookie($cookieName, $cookieValue, $cookieTime);
    }


    /**
     * set Cookie Value
     *
     * @param string $cookieName
     * @param string $cookieValue
     * @param string $cookiePeriod
     */
    public function setCookie($cookieName, $cookieValue, $cookiePeriod)
    {
        /** @var Mage_Core_Model_Cookie $cookieModel */
        $cookieModel = Mage::getModel('core/cookie');

        //$period = $cookieModel->getLifetime()
        $path = $cookieModel->getPath();
        $domain = $cookieModel->getDomain();
        $secure = $cookieModel->isSecure();
        $httpOnly = $cookieModel->getHttponly();

        $cookieModel->set($cookieName, $cookieValue, $cookiePeriod, $path, $domain, $secure, $httpOnly);
    }


    /**
     * check is Enabled 'Security Token'
     *
     * @return bool
     */
    public function isEnabledIpSecurityToken()
    {
        return (bool)Mage::getStoreConfig(self::MODULE_NAME . '/ipsecuritytoken/enabled');
    }


    /**
     * return count of days
     *
     * @return int
     */
    public function getTokenExpireTime()
    {
        return (int)Mage::getStoreConfig(self::MODULE_NAME . '/ipsecuritytoken/token_expire');
    }


    /**
     * return time (hour)
     *
     * @return int
     */
    public function getCookieExpireTime()
    {
        return (int)Mage::getStoreConfig(self::MODULE_NAME . '/ipsecuritytoken/cookie_expire');
    }

    /**
     * @return string
     */
    public function getTokenName()
    {
        return (string)Mage::getStoreConfig(self::MODULE_NAME . '/ipsecuritytoken/param_name');
    }

    /**
     * @return string
     */
    public function getTokenValue()
    {
        return (string)Mage::getStoreConfig(self::MODULE_NAME . '/ipsecuritytoken/token');
    }

    /**
     * remove token link
     */
    public function resetTokenLinks()
    {
        $this->saveConfigValue('ipsecuritytoken/token', '');
        $this->saveConfigValue('ipsecuritytoken/token_link', '');
        $this->saveConfigValue('ipsecuritytoken/token_link_admin', '');
    }


    /**
     * set Url to admin page with token
     *
     * @param $tokenName
     */
    public function setToken($tokenName)
    {
        $adminUrl = Mage::getUrl('adminhtml');
        $frontUrl = Mage::getUrl();

        $token = '?' . $tokenName . '=';
        $token .= $this->_setToken();

        $adminUrl .= $token;
        $frontUrl .= $token;

        $this->saveConfigValue('ipsecuritytoken/token_link_admin', $adminUrl);
        $this->saveConfigValue('ipsecuritytoken/token_link', $frontUrl);

    }

    /**
     * get Url for access to FrontEnd
     *
     * @return string
     */
    public function getFrontTokenUrl()
    {
        return (string)Mage::getStoreConfig(self::MODULE_NAME . '/ipsecuritytoken/token_link');
    }

    /**
     * get Url for access to FrontEnd
     *
     * @return string
     */
    public function getAdminTokenUrl()
    {
        return (string)Mage::getStoreConfig(self::MODULE_NAME . '/ipsecuritytoken/token_link_admin');
    }


    /**
     * generate token & save to config
     * @return string
     */
    protected function _setToken()
    {
        $token = md5(Mage::helper('core')->getRandomString($length = 32));
        $this->saveConfigValue('ipsecuritytoken/token', $token);
        return $token;
    }


    /**
     * @param string $configPath
     * @param string $value
     */
    public function saveConfigValue($configPath, $value)
    {
        $coreConfig = new Mage_Core_Model_Config();
        $coreConfig->saveConfig(
            self::MODULE_NAME . '/' . $configPath,
            $value
        );
        Mage::getConfig()->reinit();
    }


    /**
     * get Url to Admin page with token
     *
     * @return string
     */
    public function getToken()
    {
        return (string)Mage::getStoreConfig(self::MODULE_NAME . '/ipsecuritytoken/token');
    }

    /**
     * remove Last Update Token Time
     */
    public function resetLastUpdateTokenTime()
    {
        $this->saveConfigValue('ipsecuritytoken/last_updated_date', '');
    }

    /**
     * set Date Last Update Token
     *
     * @return string
     */
    public function setLastUpdateToken()
    {
        $date = now();
        $this->saveConfigValue('ipsecuritytoken/last_updated_date', $date);
        return $date;
    }

    /**
     * create Comment Message For Grid of Expired Token Time
     *
     * @return string
     */
    public function getTokenExpiredTimeMessage_OLD()
    {
        $msg = '';
        $timeLastUpdateToken = $this->getLastUpdateToken();

        if ($timeLastUpdateToken == '') {
            $msg .= $this->__('Token not created');
        } else {

            /** @var ET_IpSecurity_Model_System_Config_Source_Token_Expire $tokenModel */
            $tokenModel = Mage::getModel('etipsecurity/system_config_source_token_expire');

            if ($tokenModel->isTokenExpired()) {
                $msg = $this->__('Token expired!');
                $msg .= ' ';
            } else {

                $tokenExpiredTimeStamp = $tokenModel->getTokenExpiredTimestamp();
                $differentTime = $tokenExpiredTimeStamp - time();

                $differentTimeInHour = round($differentTime / (60 * 60));

                $differentTimeInDay = (int)($differentTimeInHour / 24);
                $msg .= $this->__('Token expires after:');
                $msg .= ' ';

                if ($differentTimeInDay) {
                    $msg .= $this->__('%s d', $differentTimeInDay);
                    $msg .= ' ';
                }

                $hour = (int)($differentTimeInHour - ($differentTimeInDay * 24));

                if ($hour) {
                    $msg .= $this->__('%s h', $hour);
                    $msg .= ' ';
                }

                if ((!$differentTimeInDay) && (!$hour)) {
                    $minute = round($differentTimeInHour, 2) * 100;
                    $msg .= $this->__('%s m', $minute);
                    $msg .= ' ';
                }
            }
        }

        $msg = trim($msg);

        return $msg;
    }


    /**
     * create Comment Message For Grid of Expired Token Time
     *
     * @return string
     */
    public function getTokenExpiredTimeMessage()
    {
        $msg = '';
        $timeLastUpdateToken = $this->getLastUpdateToken();

        if ($timeLastUpdateToken == '') {
            $msg .= $this->__('Token not created');
        } else {

            /** @var ET_IpSecurity_Model_System_Config_Source_Token_Expire $tokenModel */
            $tokenModel = Mage::getModel('etipsecurity/system_config_source_token_expire');

            if ($tokenModel->isTokenExpired()) {
                $msg = $this->__('Token expired!');
            } else {

                $tokenExpiredTimeStamp = $tokenModel->getTokenExpiredTimestamp();
                $differentTime = $tokenExpiredTimeStamp - time();

                $differentTimeInHour = round($differentTime / (60 * 60));

                if ($differentTimeInHour) {
                    $msg .= $this->__('Token expires after:');
                    $msg .= ' ';
                    $msg .= $this->__('%s (hours)', $differentTimeInHour);
                } else {
                    $msg = $this->__('Token expired!');
                }
            }
        }

        $msg = trim($msg);

        return $msg;
    }


    /**
     * check token last Update && url (not empty)
     *
     * @return bool
     */
    public function isSetTokenLastUpdateAndUrl()
    {
        if (($this->getLastUpdateToken() != '') && ($this->getToken() != '')) {
            $this->log('isSetTokenLastUpdateAndUrl(): true');
            return true;
        } else {
            $this->log('isSetTokenLastUpdateAndUrl(): false');
            return false;
        }
    }


    /**
     * get Date Last Update Token
     *
     * @return string
     */
    public function getLastUpdateToken()
    {
        return (string)Mage::getStoreConfig(self::MODULE_NAME . '/ipsecuritytoken/last_updated_date');
    }


    /**
     * Returns ip method which is selected in admin settings
     *
     * @return mixed
     */
    public function getIpVariable()
    {
        /** @var $model ET_IpSecurity_Model_IpVariable */
        $model = Mage::getModel('etipsecurity/ipVariable');
        $ipsArray = $model->getOptionArray();

        $configVariable = Mage::getStoreConfig(self::MODULE_NAME . '/global_settings/get_ip_method');

        if (!in_array($configVariable, $ipsArray)) {
            $configVariable = 'REMOTE_ADDR';
        }

        return $configVariable;
    }


    /**
     * @param string|array $message
     * @return bool
     */
    public function log($message)
    {
        if ($this->isLogEnabled()) {
            $file = $this->getLogFileName();
            if (is_array($message)) {
                $forLog = array();
                foreach ($message as $answerKey => $answerValue) {
                    $answer = !is_scalar($answerValue) ? print_r($answerValue, true) : $answerValue;
                    $forLog[] = $answerKey . ": " . $answer;
                }
                $forLog[] = '***************************';
                $message = implode("\r\n", $forLog);
            }

            $argumentsCount = func_num_args();
            if ($argumentsCount > 1) {
                $forLog = array($message);
                $forLog[] = "Additional data: ";
                $arguments = func_get_args();
                for ($i = 1; $i < $argumentsCount; $i++) {
                    $forLog[] = !is_scalar($arguments[$i]) ? print_r($arguments[$i], true) : $arguments[$i];
                }
                $message = implode("\r\n", $forLog);
            }

            Mage::log($message, Zend_Log::DEBUG, $file, true);
        }
        return true;
    }

    /**
     * check Enabled Logging
     *
     * @return bool
     */
    public function isLogEnabled()
    {
        return (bool)Mage::getStoreConfig(self::MODULE_NAME . '/general/log_enabled');
    }

    /**
     * return log FileName
     *
     * @return string
     */
    public function getLogFileName()
    {
        Mage::getConfig()->getVarDir('log' . DS . self::MODULE_LOG_FOLDER);
        $fileName = Mage::getStoreConfig(self::MODULE_NAME . '/general/log_file');
        if ($fileName == '') {
            $fileName = self::MODULE_LOG_FOLDER . '.log';
        }
        $filePath = self::MODULE_LOG_FOLDER . DS . date("Ymd-") . $fileName;
        return $filePath;
    }


}