#!/usr/bin/php
<?php

/**
 * USERCACHELIST is a global variable which holds the array of users in it
 * 
 * Another way to do this is to save it in a file in a directory
 * 
 */
$USERCACHELIST = array();

class Cache{
    
    /**
     * globalList is a global variable which will be assigned with the global variable 
     */
    private $globalList;

    /**
     * Timer is a thread which keep looping every minute and checks the items in 
     * list of users which are expired
     */
    private $timer;

    function __construct($timer){
        
        global $USERCACHELIST;

        // initializing globalList by addressing it to global variable
        $this->globalList =& $USERCACHELIST;

        // Cache should be resopnsible to only run the time as far as expiration 
        // check is considered, therefore we are going to use dependancy injuction 
        // and take the responsability of cache to initiate the timer
        // which are decouples the timer object
        $this->timer = $timer;
        $this->timer->start();
    }

    // add new user, if the user has not entered expirationTime in the 
    // input then by default it will be set to 10 minutes
    public function addNewUser($emailid, $firstName, $lastName, $phoneNumber, 
                               $address, $age, $sex, $expirationTime = 10){

        // add to global list with emailid as the key, as emailid is the primary key
        $this->globalList[$emailid] = 
                        new User($emailid, $firstName, $lastName, $phoneNumber, $address, 
                                $age, $sex, $expirationTime, date("Y/m/d H:i:s"));
    }

    // get user details by array
    public function getUserByArray($emailId){

        // check if user exists
        if(!$this->checkIfUserExists($emailId)){
            return false;
        }

        $user = $this->globalList[$emailId];
        return $user->getUserArray();
    }

    // get user details by object
    public function getUserByObject($emailId){

        // check if user exists
        if(!$this->checkIfUserExists($emailId)){
            return false;
        }

        return $this->globalList[$emailId];

    }

    // check if user exits in the list
    public function checkIfUserExists($emailId){

        // if user does not exists in userlist then 
        // retrun false indicating user not found
        if(!array_key_exists($emailId, $this->globalList)){
            return false;
        }
        return true;

    }

    // delete specific user by emailid
    public function deleteUser($emailId){

        // if user does not exists in userlist
        if(!array_key_exists($emailId, $this->globalList)){
            // return true to indicate no user removed
            return false;
        }

        // remove user
        unset($this->globalList[$emailId]);
        
        // return true to indicate user removed
        return true;

    }

    // delete expired users, user might want to remove all the expired users from the list
    public function deleteExpiredUsers(){

        // use the function from timer to remove all the expired users
        $this->timer->loopOverCacheList();
    }

}

/**
 * This class is helpfull to create an user datatyoe
 */
class User{

    /**
     * 
     * User data explination:
     * Lets consider emailid as primary key 
     * All the others are the attribute of the user which are changable
     * 
     */

    // immutable parameters - these variable cannot be changed as 
    // they changing these will effect the folw
    private $emailid;
    private $expirationTime;
    private $enteredTime;

    // mutable variables - these things can be changed by the user
    public $firstName;
    public $lastName;
    public $phoneNumber;
    public $address;
    public $age;
    public $sex;

    public function __construct($emailid, $firstName, $lastName, $phoneNumber, 
                                $address, $age, $sex, $expirationTime, $enteredTime){
        $this->emailid = $emailid;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phoneNumber = $phoneNumber;
        $this->address = $address;
        $this->age = $age;
        $this->sex = $sex;
        $this->expirationTime = $expirationTime;
        $this->enteredTime = $enteredTime;
    }

    // get user details in array formate
    public function getUserArray(){
        return array(
            'emailid' => $this->emailid,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'phoneNumber' => $this->phoneNumber,
            'address' => $this->address,
            'age' => $this->age,
            'sex' => $this->sex,
            'expirationTime' => $this->expirationTime,
            'enteredTime' => $this->enteredTime
        );
    }

    // get user expiration time
    public function getExpirationTime(){
        return $this->expirationTime;
    }

    // get user enterd the cache time
    public function getEnteredTime(){
        return $this->enteredTime;
    }
    
}

/**
 * This class is responsable to check the expiration of user every minute
 * 
 * Note: Make sure to install pthreads and php with ZTS (Zend Thread Safety) otherwise 
 *       Thread will not work. Another way to do this is to create a cron job which run 
 *       every minute and calls run function in this class. 
 *       In this example I choose to extend the class with thread and run in the Cache class
 */
class Timer extends Thread{

    private $globalList;

    function __construct(){

        global $USERCACHELIST;

        // initializing globalList by addressing it to global variable
        $this->globalList =& $USERCACHELIST;

    }

    function run(){

        while(true){
            
            // check hapens every minute therefore waits for 60 seconds(a minute)
            sleep(60);

            // perfrom a check on expiration time
            $this->loopOverCacheList();

        }
        
    }

    // function to perform check on expireDate on user data
    public function loopOverCacheList(){

        // loop over all the ites in the global list to check if there is an expired item
        // if it is remove the item        
        foreach($this->globalList as $key => $item){

            // if the difference between current time and the time that the user 
            // enterd the cache is more than the expiration time then remove the
            // user from the list
            if($this->isExpired($item)){

                // remove the user from the array using the key
                unset($this->globalList[$key]);

            }

        }

    }

    /**
     * function to check is expired or not
     * 
     * input: User object
     * returns: boolean
     */ 
    private function isExpired($item){

        // return the boolean value by checking if the expiration time is greater then or equal to
        // the difference between current time and user enterd time
        return (
                ( strtotime( date("Y/m/d H:i:s") ) - strtotime( $item->getEnteredTime() ) )
                        >= $item->getExpirationTime()
                );

    }

}

?>