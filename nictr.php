<?php
/**
 * WHMCS SDK NicTR Registrar Modül
 *
 * NicTR web sitesi parse edilerek alan adı yenileme, dns değiştirme
 * NicTR alan adı bilgilerini çekerek WHMCS bilgilerini güncelleme 
 * işlemleri yapılmaktadır. 
 *
 * @license GPLv3
 */
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
use WHMCS\Module\Registrar\NicTR\NicTR;

/**
 * Modülle alakalı metadata verileri tanımlanıyor
 *
 *
 * @return array
 */
function nictr_MetaData()
{
    return array(
        'DisplayName' => 'NicTR Domain Yönetimi',
        'APIVersion' => '1.0',
    );
}
/**
 * Registrar ayarları tanımlanıyor.
 *
 * NiçTR kullanıcı adı ve parolası zorunludur.
 * Alan adı yenileme için kredi kartı bilgileri de tanımlanmalıdır.
 *
 * @return array
 */
function nictr_getConfigArray()
{
    $year = date('Y');
    
    $years = array();
    for ($i = $year; $i <= $year + 10; $i++) {
        $years[$i] = $i;
    }

    return array(
        // Friendly display name for the module
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'NicTR Domain Yönetimi',
        ),

        'INFO_ACCOUNT' => array(
            'FriendlyName' => '<br /><strong>Nic.tr HESAP BILGILERINIZ</strong><br />',
            'Type' => 'label',
            'Default' => '',
            'Description' => '',
        ),
        // a text field type allows for single line text input
        'metu_username' => array(
            'FriendlyName' => 'Kullanıcı Adı',
            'Type' => 'text',
            'Size' => '25',
            'Description' => 'xx-metu seklindeki sorumlu kodunuz',
        ),  

        'password' => array(
            'FriendlyName' => 'Parola',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'nic.tr parolanız',
        ),

        'BLANK_ROW' => array(
            'FriendlyName' => '&nbsp;',
            'Type' => 'label',
            'Default' => '',
            'Description' => '',
        ),

        'INFO_CARD' => array(
            'FriendlyName' => '<br /><strong>KREDI KARTI BILGILERI</strong><br />',
            'Type' => 'label',
            'Default' => '',
            'Description' => '',
        ),

        'cc_name' => array(
            'FriendlyName' => 'Kredi Kartı Üzerindeki Ad',
            'Type' => 'text',
            'Size' => '100',
            'Description' => 'Kredi kartınızın üzerinde görünen ad',
        ),

        'cc_no' => array(
            'FriendlyName' => 'Kart No',
            'Type' => 'text',
            'Size' => '16',
            'Description' => '16 haneli kart numarası',
        ),

        'cc_type' => array(
            'FriendlyName' => 'Kart Türü',
            'Type' => 'dropdown',
            'Options' => array(
                '1' => 'Mastercard',
                '2' => 'VISA',
            ),
            'Description' => '',
        ),
        
        'cc_month' => array(
            'FriendlyName' => 'Ay',
            'Type' => 'dropdown',
            'Options' => array(
                '01' => '01',
                '02' => '02',
                '03' => '03',
                '04' => '04',
                '05' => '05',
                '06' => '06',
                '07' => '07',
                '08' => '08',
                '09' => '09',
                '10' => '10',
                '11' => '11',
                '12' => '12',
            ),
            'Description' => '',
        ),

        'cc_year' => array(
            'FriendlyName' => 'Yıl',
            'Type' => 'dropdown',
            'Options' => $years,
            'Description' => '',
        ),

        'cc_cvv' => array(
            'FriendlyName' => 'CVV',
            'Type' => 'text',
            'Size' => '3',
            'Description' => 'Kartin arkasindaki 3 haneli rakam',
        ),        
    );
}

/**
 * Yeni alan adı kaydı.
 *
 * Bu fonksiyon geliştirilecek.
 *
 * @param array $params common module parameters
 * @return array
 */
function nictr_RegisterDomain($params)
{   
    //TODO: Belge gerektiren ve gerektirmeyen TLD'lere göre kontroller ve işlemler yapılacak.
    return array(
        'error' => ".TR uzantılı alan adları manuel olarak ve resmi belgeler ile kayıt edilmektedir.",
    );
}

/**
 * Kullanıcı adı ve parola dogrulamasi.
 *
 * @param string $username
 * @param string $password
 * @return bool
 */
function validateCredentials( $username, $password ) { 
    if (strlen($username) < 3 || strlen($password) < 3) {
        return false;
    }
    return true;
}

/**
 * Kredi karti dogrulamasi.
 *
 * @param string $cc_name
 * @param double $cc_no
 * @param integer $cc_type
 * @param string $cc_month
 * @param integer $cc_yil
 * @param string $cc_cvv
 * @return bool
 */
function validateCC( $cc_name, $cc_no, $cc_type, $cc_month, $cc_yil, $cc_cvv ) {
    if (strlen($cc_name) < 5) {
        return false;
    }
    if (strlen($cc_no) != 16) {
        return false;
    }

    if (!isset($cc_type)) {
        return false;
    }

    if (!( (int)$cc_month) ) {
        return false;
    }
    
    if (!((int)$cc_yil) ) {
        return false;
    }

    if (strlen($cc_cvv) != 3) {
        return false;
    }

    return true;
}

/**
 * Alan adi yenileme
 *
 *
 * @param array $params common module parameters
 * @return array
 */
function nictr_RenewDomain($params)
{   
    $username   = $params['metu_username'];
    $password   = $params['password'];

    $cc_params['ccOwner']                           = $params['cc_name'];
    $cc_params['pan']                               = $params['cc_no'];
    $cc_params['cv2']                               = $params['cc_cvv'];
    $cc_params['ccIssuerId']                        = $params['cc_type'];    
    $cc_params['Ecom_Payment_Card_ExpDate_Month']   = $params['cc_month'];
    $cc_params['Ecom_Payment_Card_ExpDate_Year']    = $params['cc_year'];

    if ( !validateCC( $cc_params['ccOwner'], $cc_params['pan'], $cc_params['ccIssuerId'], $cc_params['Ecom_Payment_Card_ExpDate_Month'], $cc_params['Ecom_Payment_Card_ExpDate_Year'], $cc_params['cv2'] ) ) {
            return array(
                'error' => 'Yenileme işlemi için geçerli bir kredi kartı tanımlanması gerekiyor.',
            );
    }
    
    if ( !validateCredentials( $username, $password ) ) {
        return array(
            'error' => 'nic.tr kullanıcı adı ve parola tanımlarını yapınız.',
        );
    }
    
    $domain = $params['domainname'];

    $registrationPeriod = (int)$params['regperiod'] > 0 ? $params['regperiod'] : 1;
    //$enableDnsManagement    = (bool) $params['dnsmanagement'];

    try {
        $api = new NicTR($username, $password);
        $api->login();
        $api->renewDomain( $domain, $cc_params, $registrationPeriod);
        return array(
            'success' => true,
        );
    } catch (Exception $e) {
        logModuleCall(
            'nictr',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        return array(
            'error' => $e->getMessage(),
        );
    }
}
/**
 * Nameserverlari cek
 *
 *
 * @param array $params common module parameters
 *
 * @return array
 */
function nictr_GetNameservers($params)
{
    $username   = $params['metu_username'];
    $password   = $params['password'];
    
    if ( !validateCredentials( $username, $password ) ) {
        return array(
            'error' => 'nic.tr kullanıcı adı ve parola tanımlarını yapınız.',
        );
    }

    $domain = $params['domainname'];

    try {
        $api = new NicTR($username, $password);
        $api->login();
        $ns = $api->getDomainDNS( $domain );

        return array(
            'success' => true,
            'ns1' => $ns[0],
            'ns2' => $ns[1],
            'ns3' => $ns[2],
            'ns4' => $ns[3],
            'ns5' => $ns[4],
        );
    } catch (Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}
/**
 * Nameserver guncelle.
 *
 *
 * @param array $params common module parameters
 *
 * @return array
 */
function nictr_SaveNameservers($params)
{   
    $username   = $params['metu_username'];
    $password   = $params['password'];
    
    if ( !validateCredentials( $username, $password ) ) {
        return array(
            'error' => 'nic.tr kullanıcı adı ve parola tanımlarını yapınız.',
        );
    }

    $domain = $params['domainname'];

    try {
        $api = new NicTR($username, $password);
        $api->login();
        $api->changeDomainDNS( $domain, array('ns'=>array(
                                                    $params['ns1'], 
                                                    $params['ns2'], 
                                                    $params['ns3'],
                                                    $params['ns4'],
                                                    $params['ns5'] 
                                                    )
                                            ) );        

        return array(
            'success' => true            
        );
    } catch (Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Alan adi durumunu ve sona erme suresini senkron et
 *
 *
 * @param array $params common module parameters
 *
 * @return array
 */
function nictr_Sync($params)
{
    $username   = $params['metu_username'];
    $password   = $params['password'];
    
    if ( !validateCredentials( $username, $password ) ) {
        return array(
            'error' => 'nic.tr kullanıcı adı ve parola tanımlarını yapınız.',
        );
    }
    
    $domain = $params['domainname'];

    try {
        $api = new NicTR($username, $password);
        $api->login();
        $domain_details = $api->getDomain( $domain );

        return array(
            'expirydate' => date("Y-m-d", strtotime($domain_details['expires_on'])), // Format: YYYY-MM-DD
            'active' => true, // Return true if the domain is active
            'expired' => false, // Return true if the domain has expired
            'transferredAway' => false, // Return true if the domain is transferred out
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'nictr',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Admin arayuzunde manuel olarak 
 * belirli bir alan adina ait bilgileri 
 * cekerek WHMCS veritabanini guncelliyoruz
 *
 * @param array $params common module parameters
 *
 * @return array
 */
function nictr_FetchDomainDetails($params)
{
    $username   = $params['metu_username'];
    $password   = $params['password'];
    
    if ( !validateCredentials( $username, $password ) ) {
        return array(
            'error' => 'nic.tr kullanıcı adı ve parola tanımlarını yapınız.',
        );
    }
    
    $domain = $params['domainname'];

    try {
        $api = new NicTR($username, $password);
        $api->login();

        $domain_details     = $api->getDomain( $domain );
        $domain_nameservers = $api->getDomainDNS( $domain );        
        
        $postData = array(
            'domainid' => $params['domainid'],
            'expirydate' => date("Y-m-d", strtotime($domain_details['expires_on'])),
        );

        $i = 1;
        foreach($domain_nameservers as $ns) {
            $postData['ns'.$i] = $ns;
            $i++;
        }
        
        return localAPI('UpdateClientDomain', $postData);
        
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'nictr',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        return array(
            'error' => $e->getMessage(),
        );
    }    
}

function nictr_AdminCustomButtonArray()
{
    return array(
        "Domain Bilgilerini Getir" => "FetchDomainDetails"
    );
}
/**
 * Client Area Custom Button Array.
 *
 * Allows you to define additional actions your module supports.
 * In this example, we register a Push Domain action which triggers
 * the `nictr_push` function when invoked.
 *
 * @return array
 */
/*function nictr_ClientAreaCustomButtonArray()
{
    return array(
        'Push Domain' => 'push',
    );
}*/
/**
 * Client Area Allowed Functions.
 *
 * Only the functions defined within this function or the Client Area
 * Custom Button Array can be invoked by client level users.
 *
 * @return array
 */
/*function nictr_ClientAreaAllowedFunctions()
{
    return array(
        'Push Domain' => 'push',
    );
}*/
/**
 * Example Custom Module Function: Push
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return array
 */
/*function nictr_push($params)
{
    // user defined configuration values
    $userIdentifier = $params['API Username'];
    $apiKey = $params['API Key'];
    $testMode = $params['Test Mode'];
    $accountMode = $params['Account Mode'];
    $emailPreference = $params['Email Preference'];
    $additionalInfo = $params['Additional Information'];
    // domain parameters
    $sld = $params['sld'];
    $tld = $params['tld'];
    // Perform custom action here...
    return 'Not implemented';
}*/
/**
 * Client Area Output.
 *
 * This function renders output to the domain details interface within
 * the client area. The return should be the HTML to be output.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @return string HTML Output
 */
/*function nictr_ClientArea($params)
{
    $output = '
        <div class="alert alert-info">
            Your custom HTML output goes here...
        </div>
    ';
    return $output;
}*/
