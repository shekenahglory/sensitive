<?

class Model_Sensitive 
{
    private $data;
    private $recipient;
    private $sender;
    
    function saveData($data, $name, $recipientID)
    {
        $usersTable = new Zend_Db_Table('users');
        $select     = $usersTable->select();
        $select->where('id = ?', $recipientID);
        $select->where("type = 'recipient'");
        $this->recipient = $usersTable->fetchRow($select);
        if (!$this->recipient) array('result'=>'failure','message'=>'Invalid recipient');
        
        $select = $usersTable->select();
        $select->where('name = ?', $name);
        $this->sender = $usersTable->fetchRow($select);
        if (!$this->sender) {
            $this->sender = $usersTable->createRow();
            $this->sender->name = $name;
            $this->sender->type = 'user';
            try {
                $this->sender->save();
            } catch (exception $e) {
                return array('result'=>'failure','message'=>$e->getMessage());
            }
        }
        
        $dataTable = new Zend_Db_Table ('sensitive_data');
        $row = $dataTable->createRow();
        $row->recipient_id = $recipientID;
        $row->sender_id    = $this->sender->id;
        $row->encoded      = $this->encodeData($data);
        $row->saved        = gmdate(MYSQL_DATE);
        
        try {
            $row->save();
        } catch (exception $e) {
            return array('result'=>'failure','message'=>$e->getMessage());
        }        
        
        $this->data = $row;
        return array('result'=>'success','row'=>$row->toArray());     
    }

    public function sendLink ()
    {
        if (!$this->data)      return array('result'=>'failure','message'=>'Invalid Data');
        if (!$this->recipient) return array('result'=>'failure','message'=>'Invalid Recipient');
        
        $links = new Zend_Db_Table ('links');
        $link  = $link->createRow();
        $link->data_id = $this->data->id;
        $link->user_id = $this->recipient->id;
        $link->token   = $this->generateToken(); 
        $link->created = gmdate(MYSQL_DATE);
        $link->status  = 'unused';
        try {
            $link->save();
        } catch (exception $e) {
            return array('result'=>'failure','message'=>$e->getMessage());
        } 
        
        return $this->sendMail($link->$token); 
    }

    private function sendEmail ($token)
    {
        $validator = new Zend_Validate_EmailAddress();
        if (!$validator->isValid($this->recipient->email)) 
            return array('result'=>'failure','message'=>'Invalid Recipient Email Address');
    
        $url  = SERVER_HOST."/read?token="+$token;
        $body = "";
        $transport = new Zend_Mail_Transport_Smtp(SMTP_HOST, array(
            'auth'     => 'login',
            'username' => SMTP_USERNAME,
            'password' => SMTP_PASSWORD,
            'port'     => SMTP_PORT,
        ));
     
        $mail = new Zend_Mail();
        $mail->setBodyText($body);
        $mail->setFrom(SMTP_FROM_ADDRESS, SMTP_FROM_NAME);
        $mail->addTo($this->recipient->email, $this->recipient->name);
        $mail->setSubject('New Userdata Available');
        try {
            $mail->send($transport);
        } catch (exception $e) {
            return array('result'=>'failure','message'=>$e->getMessage());
        }
           
        return array('result'=>'success');
    }
    
    private function generateToken()
    {
        return sha1(uniqid(mt_rand(), true));
    }
    
    private function encodeData($data)
    {
        $cipher = MCRYPT_RIJNDAEL_128;
        $mode   = MCRYPT_MODE_ECB;
        
        return base64_encode(mcrypt_encrypt($cipher, KEY_256, $data, $mode));
    }
        
    private function decode ()
    {
        $cipher = MCRYPT_RIJNDAEL_128;
        $mode   = MCRYPT_MODE_ECB;
        
        return mcrypt_decrypt($cipher, KEY_256, base64_decode($this->data->encoded), $mode);
    }
    
}