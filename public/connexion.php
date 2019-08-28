<?php 

class User
{
   /* Cookie name used for cookie authentication */
   const cookie_name = 'auth_cookie';
   
   /* Cookie session's length in seconds (after that, the user must authenticate again with username and password) */
   const session_time = 604800; // 7 days
   
   /* Account Id (taken from the account_id column of the accounts table) */
   private $account_id;
   
   /* Account username */
   private $account_name;

   private $email;
   
   /* Boolean value set to TRUE if the authentication is successful */
   private $is_authenticated;
   
   /* Optional account expiry date */
   private $expiry_date;

   private $passKey;
   
   /* Cookie session id (session_id column from the sessions table) */
   private $session_id;
   
   /* Timestamp of the last login (stored in "Unix timestamp" format) */
   private $session_start_time;
   
   /* PDO object to use for database operations */
   private $db;
   
   /* Constructor; it takes the $db object as argument, passed by reference */
   public function __construct(&$db)
   {
      $this->account_id = NULL;
      $this->account_name = NULL;
      $this->email = NULL;
      $this->is_authenticated = FALSE;
      $this->expiry_date = NULL;
      $this->session_id = NULL;
      $this->session_start_time = NULL;
      $this->db = $db;
   }

   /* Username and password authentication */
public function login($name, $password)
{
 
   /* Check the strings' length */
   if ((mb_strlen($name) < 3) || (mb_strlen($name) > 24))
   {
      return 5;
   }
   
   if ((mb_strlen($password) < 3) || (mb_strlen($password) > 24))
   {
      return 6;
   }

   try
   {
      /* First we search for the username */
      $sql = 'SELECT * FROM accounts WHERE (account_name = ?) AND (account_enabled = 1) AND ((account_expiry > NOW()) OR (account_expiry < ?))';
      $st = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
      $st->execute(array($name, '2000-01-01'));
      $res = $st->fetch(PDO::FETCH_ASSOC);
      //  var_dump($res);
      /* If the username exists and is enabled, then we check the password */
      if(gettype($res) == "boolean" && $res == FALSE){
         return -5;
      }
      else if (password_verify($password, $res['account_password']))
      {
         /* Log in ok, we retrieve the account data */
         $this->account_id = $res['account_id'];
         $this->account_name = $res['account_name'];
         $this->is_authenticated = TRUE;
         $this->expiry_date = $res['account_expiry'];
         $this->session_start_time = time();
          
         /* Now we create the cookie and send it to the user's browser */
         $this->create_session();
      }
      else {
         return -2;
      }
   }
   catch (PDOException $e)
   {
      /* Exception (SQL error) */
      // echo $e->getMessage();
      return -1;
   }
   
   /* If no exception occurs, return true */ 
   return 0;
}

private function create_session()
{
   try
   {
      /* Create a new cookie */
      $cookie = bin2hex(random_bytes(16));
    
      /* Saves the md5 hash of the new cookie in the database */
      $sql = 'INSERT INTO sessions (session_cookie, session_account_id, session_start) VALUES (?, ?, NOW())';
      $st = $this->db->prepare($sql);
      $st->execute(array(md5($cookie), $this->account_id));
    
      /* Reads the session ID of the new cookie session and stores it in the class parameter */
      $this->session_id = $this->db->lastInsertId();
   }
   catch (PDOException $e)
   {
      /* Exception (SQL error) */
      echo $e->getMessage();
      return FALSE;
   }
 
   /* Finally we actually send the cookie to the user and we save it in the $_COOKIE PHP superglobal */
   setcookie(self::cookie_name, $cookie, time() + self::session_time, '/');
   $_COOKIE[self::cookie_name] = $cookie;

   /* If no exception occurs, return true */ 
   return TRUE;
}

public function cookie_login()
{
   try
   {
      /* First, clean all expired sessions */
      $this->clean_sessions();
      
      if (array_key_exists(self::cookie_name, $_COOKIE))
      {
         /* First we check the cookie's length */
         if (mb_strlen($_COOKIE[self::cookie_name]) < 1) 
         { 
             return TRUE;
         }

         $auth_sql = 'SELECT *, UNIX_TIMESTAMP(session_start) AS session_start_ts FROM sessions, accounts WHERE (session_cookie = ?) AND (session_account_id = account_id) AND (account_enabled = 1) AND ((account_expiry > NOW()) OR (account_expiry < ?))';
         $cookie_md5 = md5($_COOKIE[self::cookie_name]);
         $auth_st = $this->db->prepare($auth_sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
         $auth_st->execute(array($cookie_md5, '2000-01-01'));
         
         if ($res = $auth_st->fetch(PDO::FETCH_ASSOC))
         {
            /* Log in successful */
			$this->account_id = $res['account_id'];
            $this->account_name = $res['account_name'];
            $this->is_authenticated = TRUE;
            $this->expiry_date = $res['account_expiry'];
            $this->session_id = $res['session_id'];
            $this->session_start_time = intval($res['session_start_ts'], 10);
         }
      }
   }
   catch (PDOException $e)
   {
      /* Exception (SQL error) */
      echo $e->getMessage();
      return FALSE;
   }
   
   /* If no exception occurs, return true */ 
   return TRUE;
}

private function clean_sessions()
{
	/* Query to clean expired sessions */
	$query = 'DELETE FROM sessions WHERE (session_start < (NOW() - INTERVAL ? SECOND))';
	
	/* Add the session_time constant as parameter */
	$params = array(self::session_time);
	
	/* Execute the query */
	$st = $this->db->prepare($query);
	$st->execute($params);
}

public function logout($close_all_sessions = FALSE)
{
   /* First we check if a cookie does exist */
   if (mb_strlen($_COOKIE[self::cookie_name]) < 1)
   {
      return TRUE;
   }
   
   try
   {
      /* First, we close the current session */
      $cookie_md5 = md5($_COOKIE[self::cookie_name]);
      $sql = 'DELETE FROM sessions WHERE (session_cookie = ?)';
      $st = $this->db->prepare($sql);
      $st->execute(array($cookie_md5));
       
	  /* Do we need to close other sessions as well? */
      if ($close_all_sessions)
      {
         /* We close all account's sessions */
         $sql = 'DELETE FROM sessions WHERE (session_account_id = ?)';
         $st = $this->db->prepare($sql);
         $st->execute(array($this->account_id));
      }
   }
   catch (PDOException $e)
   {
      /* Exception (SQL error) */
      echo $e->getMessage();
      return FALSE;
   }
   
   /* Delete the cookie from user's browser */
   setcookie(self::cookie_name, '', 0, '/');
   $_COOKIE[self::cookie_name] = NULL;
   
   /* Clear user-related properties */
   $this->account_id = NULL;
   $this->account_name = NULL;
   $this->is_authenticated = FALSE;
   $this->expiry_date = NULL;
   $this->session_id = NULL;
   $this->session_start_time = NULL;
   
   /* If no exception occurs, return true */ 
   return TRUE;
}

public static function add_account($username, $password, $passwordRepeat, $email, &$db, &$passKeyOut)
{
   /* First we check the strings' length */
   if ((mb_strlen($username) < 3) || (mb_strlen($username) > 24))
   {
      return -2;
   }
   
   if ((mb_strlen($password) < 3) || (mb_strlen($password) > 24))
   {
      return -3;
   }
   if($email == NULL){
      return -4;
   }
   else if($password != $passwordRepeat){
      return -5;
   }
   /* Password hash */
   $hash = password_hash($password, PASSWORD_DEFAULT);
    
   $passKey = md5(uniqid(rand()));

   try
   {
      /* Add the new account on the database (it's a good idea to check first if the username already exists) */
      $sql = 'INSERT INTO accounts (account_name, email, account_password, account_enabled, account_expiry, pass_key) VALUES (?, ?, ?, ?, ?, ?)';
      $st = $db->prepare($sql);
      $st->execute(array($username, $email, $hash, '0', '1999-01-01', $passKey));
   }
   catch (PDOException $e)
   {
      /* Exception (SQL error) */
	   // echo $e->getMessage();
      return FALSE;
   }
   
   $passKeyOut = $passKey;
   /* If no exception occurs, return true */
   return 1;
}

public static function delete_account($account_id, &$db)
{
   /* Note: you should only allow "admin" users to run this function 
      and carefully check the $account_id value */
   
   try
   {
      /* First, we close any open session the account may have */
      $sql = 'DELETE FROM sessions WHERE (session_account_id = ?)';
      $st = $db->prepare($sql);
      $st->execute(array($account_id));
       
      /* Now we delete the account record */
      $sql = 'DELETE FROM accounts WHERE (account_id = ?)';
      $st = $db->prepare($sql);
      $st->execute(array($account_id));
   }
   catch (PDOException $e)
   {
      /* Exception (SQL error) */
      echo $e->getMessage();
      return FALSE;
   }
   
   /* If no exception occurs, return true */
   return TRUE;
}

/* Edit an existing user; arguments set to NULL are not changed */
public static function edit_account($account_id, &$db, $username = NULL, $password = NULL, $enabled = NULL, $expiry = NULL, $email, $passwordRepeat)
{  
   /* Note: 
      each argument should be checked and validated before running the update query.
      You should check the strings' length (like in the other functions), the correct 
      format of the expiry date and so on.
   */
   if ((mb_strlen($username) < 3) || (mb_strlen($username) > 24))
   {
      return -2;
   }
   
   if ((mb_strlen($password) < 3) || (mb_strlen($password) > 24))
   {
      return -3;
   }
   if($email == NULL){
      return -4;
   }
   else if($password != $passwordRepeat){
      return -5;
   }
   /* Array of values for the PDO statement */
   $sql_vars = array();
    
   /* Edit query */
   $sql = 'UPDATE accounts SET ';
    
   /* Now we check which fields need to be updated */
   if (!is_null($username))
   {
      $sql .= 'account_name = ?, ';
      $sql_vars[] = $username;
   }
   if (!is_null($email))
   {
      $sql .= 'email = ?, ';
      $sql_vars[] = $email;
   }
    
   if (!is_null($password))
   {
      $sql .= 'account_password = ?, ';
      $sql_vars[] = password_hash($password, PASSWORD_DEFAULT);
   }
   
   if (!is_null($enabled))
   {
      $sql .= 'account_enabled = ?, ';
      $sql_vars[] = strval(intval($enabled, 10));
   }
   
   if (!is_null($expiry))
   {
      $sql .= 'account_expiry = ?, ';
      $sql_vars[] = $expiry;
   }
   
   if (count($sql_vars) == 0)
   {
      /* Nothing to change */
	  return TRUE;
   }
   
   $sql = mb_substr($sql, 0, -2) . ' WHERE (account_id = ?)';
   $sql_vars[] = $account_id;
    
   try
   {
      /* Execute query */
      $st = $db->prepare($sql);
      $st->execute($sql_vars);
   }
   catch (PDOException $e)
   {
      /* Exception (SQL error) */
      // echo $e->getMessage();
      return FALSE;
   }
   
   /* If no exception occurs, return true */
   return -9;
}

   public static function confirm_account(&$passKey, &$db) {
      $query = 'UPDATE accounts SET account_enabled = 1 WHERE (pass_key = ?)';
      $st = $db->prepare($query);
      $st->execute(array($passKey));
   }

   public function Get_is_authenticated(){
      return $this->is_authenticated;
   }

   public function Get_account_id(){
      return $this->account_id;
   }

   public function Get_email(){
      return $this->email;
   }

   public function Get_username(){
      return $this->account_name;
   }

   public function Get_expiry_date(){
      return $this->expiry_date;
   }
}

?>

