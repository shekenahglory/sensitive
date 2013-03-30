<?php

class ReadController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $token = trim($this->getRequest()->getParam('token'));
        $sensitive = new Model_Sensitive;
        $results   = $sensitive->getDataViaToken($token);
        if (isset($results['message']))  $this->view->message = $results['message'];
        if (isset($results['decoded']))  $this->view->decoded = $results['decoded'];
        if (isset($results['sendDate'])) $this->view->date    = $results['sendDate'];
        if (isset($results['sender']) && $results['sender']) {
            $this->view->sender      = $results['sender']['name'];
            $this->view->senderEmail = $results['sender']['email'];
        }
        
        if (isset($results['recipient']) && $results['recipient']) {
            $this->view->recipient      = $results['recipient']['name'];
            $this->view->recipientEmail = $results['recipient']['email'];
        }
    }
}

