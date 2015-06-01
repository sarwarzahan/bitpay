<?php

/**
 * Process bitpay payment, notification, payment confirmation
 */
class BitpayController extends Zend_Controller_Action
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $this->configuration = $registry->configuration;

    }

    public function indexAction()
    {
        // Redirect user to home page
        $url = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();
        $this->_helper->redirector->gotoUrl($url);
    }

    /*
     * Pay invoices with bitcoins
     */
    public function payInvoiceAction()
    {
        $url = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();

        // Check if pay invoice is enabled
        if (!$this->configuration->bitpay->payinvoice) {
            // Redirect user to home page
            $this->_helper->redirector->gotoUrl($url);
        }

        $invoiceId = $this->getRequest()->id;
        $invoice = new invoice($invoiceId);

        // Check if invoice is in sent status
        if (empty($invoice) || $invoice->status != 1) {
            // Redirect user to home page
            $this->_helper->redirector->gotoUrl($url);
        }

        // Prepare request to make call to bitpay
        $bitpay = new Ukko_Bitpay();
        $options = array();
        $redirectURL = $url . '/bitpay/redirect/id/' . $invoiceId;
        $notificationURL = $url . '/bitpay/notification/id/' . $invoiceId;
        $options['notificationEmail'] = $this->user->email;
        $options['notificationURL'] = $notificationURL;
        $options['redirectURL'] = $redirectURL;
        $bitpay->setDynamicOptions($options);

        // First check if invoice in Bitpay has been created for this invoice id
        $bitpayInvoiceInfo = $bitpay->getBitpayInvoiceInfo($invoiceId);
        if ($bitpayInvoiceInfo == false) {
            // Now create new Bitpay invoice by making request
            $posData = '{"id":' . $invoice->id . ',"total":' . $invoice->total . '}';
            $bitpayInvoiceInfo = $bitpay->createBitpayInvoice($invoiceId, $invoice->total, $posData);
            if ($bitpayInvoiceInfo != false) {
                // Redirect user to Bitpay page
                $this->_helper->redirector->gotoUrl($bitpayInvoiceInfo['url']);
            }
        } else {
            // Bitpay Invoice has been created already, so use the existing info
            // Redirect user to Bitpay page
            $this->_helper->redirector->gotoUrl($bitpayInvoiceInfo['url']);
        }

    }

    /**
     * For custom page
     */
    public function redirectAction()
    {
        $invoiceId = $this->getRequest()->id;
        $this->_helper->redirector->gotoUrl('/invoice/' . $invoiceId);
    }

    /**
     * Retrieve notification from Bitpay
     */
    public function notificationAction()
    {
        // Receive notification JSON
        $bitpay = new Ukko_Bitpay();
        $notificationData = $bitpay->getNotification();

        if (!empty($notificationData['status'])) {
            if ($notificationData['status'] == 'confirmed') {
                $invoiceId = $this->getRequest()->id;
                $invoice = new invoice($invoiceId);
                $invoice->setStatus(2);
            }
        }

        $this->_helper->layout->disableLayout();
        die();
    }
} 