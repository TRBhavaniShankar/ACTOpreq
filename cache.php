<?php

$list = array();

class Cache{
    
    /* lets consider emailid as primary key */
    
    private $timer;
    function __construct(){
        // this thread keep looping every minute and checks the items in list of users which are expired
        $timer = new Timer();
        $timer->start();
    }

    // add new user
    public function addNewUser($emailid, $firstName, $lastName, $phoneNumber, $address, $age, $sex, $expirationTime = 10){
        $list[$emailid] = array(
            'user' => new User($emailid, $firstName, $lastName, $phoneNumber, $address, $age, $sex),
            'timer' => $expirationTime,
            'enteredTime' => date("Y/m/d H:i:s")
        );
    }

}


class User{
    public $emailid;
    private $firstName;
    private $lastName;
    private $phoneNumber;
    private $address;
    private $age;
    private $sex;
    private $expirationTime = 10;

    public function __construct($emailid, $firstName, $lastName, $phoneNumber, $address, $age, $sex, $expirationTime=10){
        $this->emailid = $emailid;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phoneNumber = $phoneNumber;
        $this->address = $address;
        $this->age = $age;
        $this->sex = $sex;
    }

    
}

class Timer extends Thread{

    function run(){
        while(true){
            
            sleep(60);

            // loop over all the ites in the global list to 
            // check if there is an expired item
            // if it is remove the item
            foreach($item as $list){
                if((strtotime(date("Y/m/d H:i:s"))-strtotime($item['enteredTime'])) >= $item['timer']){
                    // remove the user from the array
                    unset($list[$item]);
                }
            }

        }
    }
}

?>