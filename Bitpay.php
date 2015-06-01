<?php
/*
 * For basic Bitpay functionality
 */
class Bitpay
{
    // To hold configuration value from application.ini file
    private $_configuration;

    // To hold value for bitpay configuration
    private $_options;

    public function __construct()
    {
        $registry = Zend_Registry::getInstance();
        $this->_configuration = $registry->configuration;

        // Load bipay library files
        Zend_Loader::loadFile('bp_options.php', $this->_configuration->includePaths->library . '/bitpay', true);
        Zend_Loader::loadFile('bp_lib.php', $this->_configuration->includePaths->library . '/bitpay', true);
        // Global variable in bitpay to hold configuration values
        global $bpOptions;
        $this->_options = array_merge($bpOptions, $this->_configuration->bitpay->toArray());
        $bpOptions = $this->_options;
    }

    /**
     * Merged the parameter with global bitpay option
     *
     * @param $options array
     * @return array
     */
    public function setDynamicOptions($options)
    {
        global $bpOptions;
        $bpOptions = array_merge($bpOptions, $options);
        $this->_options = $bpOptions;

        return $bpOptions;
    }

    /**
     * Get the information about already created invoice
     *
     * @param string $invoiceId
     * @return JSON mixed
     */
    public function getBitpayInvoiceInfo($invoiceId)
    {
        $invoiceInfo = bpGetInvoice($invoiceId);

        if (empty($invoiceInfo['error'])) {
            return $invoiceInfo;
        } else {
            return false;
        }
    }

    /**
     * Create new invoice on Bipay
     *
     * @param string $invoiceId
     * @param string $price
     * @param string $posData
     * @param array $options
     * @return array|bool
     */
    public function createBitpayInvoice($invoiceId, $price, $posData, $options = array())
    {
        $invoiceInfo = bpCreateInvoice($invoiceId, $price, $posData, $options);

        if (empty($invoiceInfo['error'])) {
            return $invoiceInfo;
        } else {
            return false;
        }
    }

    /**
     * Retrieve notification from Bitpay
     *
     * @return JSON mixed
     */
    public function getNotification()
    {
        $notificationData = bpVerifyNotification();

        return $notificationData;
    }
} 