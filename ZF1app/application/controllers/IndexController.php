<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        
    }

    public function indexAction()
    {
        $usersTable = new Zend_Db_Table('users');
        $select     = $usersTable->select();
        $select->where("type = 'recipient'");
        $this->view->recipients = $usersTable->fetchAll($select);
        $sensitive = new Model_Sensitive;
    }

    public function saveAction ()
    {
        $data        = trim($this->getRequest()->getParam('data'));
        $name        = trim($this->getRequest()->getParam('name'));
        $recipient   = $this->getRequest()->getParam('recipient');
            
        if (!$recipient) $results = array('result'=>'failure','message'=>'Invalid recipient');
        else if (!$data)  $results = array('result'=>'failure','message'=>'Invalid data');
        else if (!$name) $results = array('result'=>'failure','message'=>'Your name is required');
        else {
            $sensitive = new Model_Sensitive;
            $results   = $sensitive->saveData($data, $name, $recipient);
            if ($results['result']=='success') $results = $sensitive->sendLink();
            
        }
        
        echo json_encode ($results);
        die;
    }

}
