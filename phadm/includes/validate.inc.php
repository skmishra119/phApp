<?php
//require_once 'crypt/crypt.class.php';

class validation{
   /*
    * @errors array
    */
    public $errors = array();
    /*
    * @the validation rules array
    */
    private $validation_rules = array();
    /*
     * @the sanitized values array
     */
    public $sanitized = array();
    
    /*
     * @the source 
     */
    private $source = array();
    /*
     *
     * @the constructor, duh!
     *
     */
    public function __construct()
    {
    	
    }
    /*
     *
     * @add the source
     *
     * @paccess public
     *
     * @param array $source
     *
     */
    public function addSource($source, $trim=false)
    {
        $this->source = $source;
    }
    /*
     *
     * @run the validation rules
     *
     * @access public
     *
     */
    public function run()
    {
    	
    	//var_dump($this->source);
    	if(!isset($this->source['csrftoken']) || $this->source['csrftoken']!=$_SESSION['token_id'])
    	{
    		unset($_SESSION['token_id']);
    		unset($_SESSION['UID']);
    		header("Location: ".DROOT.XROOT."error/csrf-error/".$this->encrypt('true'));
    		exit;
    		//$this->errors['csrftoken']='Unauthorized access.';
    		//return $this->errors;
    	}
    	
    	/*** set the vars ***/
        foreach( new ArrayIterator($this->validation_rules) as $var=>$opt)
        {
        	//var_dump($opt);
            if($opt['required'] == true)
            {
                $this->is_set($var, $opt['msg']);
            }

            /*** Trim whitespace from beginning and end of variable ***/
            if( array_key_exists('trim', $opt) && $opt['trim'] == true )
            {
                $this->source[$var] = trim($this->source[$var]);
            }

            switch($opt['type'])
            {
                case 'email':
                	$this->validateEmail($var, $opt['msg'], $opt['required']);
                    if(!array_key_exists($var, $this->errors))
                    {
                        $this->sanitizeEmail($var);
                    }
                    break;

                case 'url':
                    $this->validateUrl($var,$opt['msg']);
                    if(!array_key_exists($var, $this->errors))
                    {
                        $this->sanitizeUrl($var);
                    }
                    break;

                case 'numeric':
                    $this->validateNumeric($var, $opt['msg'], $opt['min'], $opt['max'], $opt['options'], $opt['required']);
                    if(!array_key_exists($var, $this->errors))
                    {
                        $this->sanitizeNumeric($var);
                    }
                    break;

                case 'string':
                    $this->validateString($var, $opt['msg'], $opt['min'], $opt['max'], $opt['options'], $opt['required']);
                    if(!array_key_exists($var, $this->errors))
                    {
                        $this->sanitizeString($var);
                    }
                break;

                case 'float':
                    $this->validateFloat($var, $opt['msg'], $opt['options'], $opt['required']);
                    if(!array_key_exists($var, $this->errors))
                    {
                        $this->sanitizeFloat($var);
                    }
                    break;

                case 'ipv4':
                    $this->validateIpv4($var, $opt['msg'], $opt['required']);
                    if(!array_key_exists($var, $this->errors))
                    {
                        $this->sanitizeIpv4($var);
                    }
                    break;

                case 'ipv6':
                    $this->validateIpv6($var, $opt['msg'], $opt['required']);
                    if(!array_key_exists($var, $this->errors))
                    {
                        $this->sanitizeIpv6($var);
                    }
                    break;

                case 'bool':
                    $this->validateBool($var, $opt['msg'], $opt['required']);
                    if(!array_key_exists($var, $this->errors))
                    {
                        $this->sanitized[$var] = (bool) $this->source[$var];
                    }
                    break;
                case 'phone':
                    $this->validatePhone($var, $opt['msg'], $opt['min'], $opt['max'], $opt['required']);
                    if(!array_key_exists($var, $this->errors))
                    {
                        $this->sanitized[$var] = (bool) $this->source[$var];
                    }
                    break;
               	case 'mobile':
                  	$this->validateMobile($var, $opt['msg'], $opt['min'], $opt['max'], $opt['required']);
                   	if(!array_key_exists($var, $this->errors))
                   	{
                   		$this->sanitized[$var] = (bool) $this->source[$var];
                   	}
                   	break;
                case "date":
                	$this->validateDate($var, $opt['msg'], $opt['min'], $opt['max'], $opt['required']);
                    if(!array_key_exists($var, $this->errors))
                    {
                        $this->sanitized[$var] = (bool) $this->source[$var];
                    }
                    break;
                case "datetime":
                   	$this->validateDateTime($var, $opt['msg'], $opt['min'], $opt['max'], $opt['required']);
                   	if(!array_key_exists($var, $this->errors))
                   	{
                   		$this->sanitized[$var] = (bool) $this->source[$var];
                   	}
                   	break;
            }
        }
    }


    /**
     *
     * @add a rule to the validation rules array
     *
     * @access public
     *
     * @param string $varname The variable name
     *
     * @param string $type The type of variable
     *
     * @param bool $required If the field is required
     *
     * @param int $min The minimum length or range
     *
     * @param int $max the maximum length or range
     *
     */
    public function addRule($varname, $type, $msg, $required=false, $min=0, $max=0, $opts, $trim=false)
    {
        $this->validation_rules[$varname] = array('type'=>$type, 'msg'=>$msg, 'required'=>$required, 'min'=>$min, 'max'=>$max, 'options'=>$opts,'trim'=>$trim);
        /*** allow chaining ***/
        return $this;
    }
    /**
     *
     * @add multiple rules to teh validation rules array
     *
     * @access public
     *
     * @param array $rules_array The array of rules to add
     *
     */
    public function AddRules(array $rules_array)
    {
        $this->validation_rules = array_merge($this->validation_rules, $rules_array);
    }

    /**
     *
     * @Check if POST variable is set
     *
     * @access private
     *
     * @param string $var The POST variable to check
     *
     */
    private function is_set($var,$msg)
    {
    	if(!isset($this->source[$var]) || trim($this->source[$var])=='')
        {
            $this->errors[$var] = $msg . ' is not set';
        }
    }
    /**
     *
     * @validate an ipv4 IP address
     *
     * @access private
     *
     * @param string $var The variable name
     *
     * @param bool $required
     *
     */
    private function validateIpv4($var, $msg, $required=false)
    {
        if($required==false && strlen($this->source[$var]) == 0)
        {
            return true;
        }
        if(filter_var($this->source[$var], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === FALSE)
        {
            $this->errors[$var] = $msg . ' is not a valid IPv4';
        }
    }
    /**
     *
     * @validate an ipv6 IP address
     *
     * @access private
     *
     * @param string $var The variable name
     *
     * @param bool $required
     *
     */
    public function validateIpv6($var, $msg, $required=false)
    {
        if($required==false && strlen($this->source[$var]) == 0)
        {
            return true;
        }

        if(filter_var($this->source[$var], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === FALSE)
        {
            $this->errors[$var] = $msg . ' is not a valid IPv6';
        }
    }
    /**
     *
     * @validate a floating point number
     *
     * @access private
     *
     * @param $var The variable name
     *
     * @param bool $required
     */
    private function validateFloat($var, $msg, $options=false, $required=false)
    {
        if($required==false && strlen($this->source[$var]) == 0)
        {
            return true;
        }
        if(is_array($options)){
        	if(!in_array($this->source[$var],$options))
        	{
            	$this->errors[$var] = 'Invalid '.$msg . ' selected';
        	}
        }
        if(filter_var($this->source[$var], FILTER_VALIDATE_FLOAT) === false)
        {
            $this->errors[$var] = $msg . ' is an invalid float';
        }
    }
    /**
     *
     * @validate a string
     *
     * @access private
     *
     * @param string $var The variable name
     *
     * @param int $min the minimum string length
     *
     * @param int $max The maximum string length
     *
     * @param bool $required
     *
     */
    private function validatePhone($var, $msg, $min=0, $max=0, $required=false)
    {
        if($required==false && strlen($this->source[$var]) == 0)
        {
            return true;
        }

        if(isset($this->source[$var]))
        {
            if(strlen($this->source[$var]) < $min)
            {
                $this->errors[$var] = $msg . ' is too short';
            }
            elseif(strlen($this->source[$var]) > $max)
            {
                $this->errors[$var] = $msg . ' is too long';
            }
            elseif(!preg_match("/^([\+][0-9]{1,3}[ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9 \.\-\/,]{1,50})((x|ext|extn)[ ]?[0-9]{1,4})?$/",$this->source[$var]))
            {
                $this->errors[$var] = $msg . ' is invalid';
            }
        }
    }
    
    private function validateMobile($var, $msg, $min=0, $max=0, $required=false)
    {
    	if($required==false && strlen($this->source[$var]) == 0)
    	{
    		return true;
    	}
    
    	if(isset($this->source[$var]))
    	{
    		if(strlen($this->source[$var]) < $min)
    		{
    			$this->errors[$var] = $msg . ' is too short';
    		}
    		elseif(strlen($this->source[$var]) > $max)
    		{
    			$this->errors[$var] = $msg . ' is too long';
    		}
    		elseif(!preg_match("/^([\+]{0,1}[0-9]{1,3}[\-]{0,1})?([\(]{0,1}[0-9]{10}[\)]{0,1})?$/",$this->source[$var]))
    		{
    			$this->errors[$var] = $msg . ' is invalid';
    		}
    	}
    }
/**
     *
     * @validate a string
     *
     * @access private
     *
     * @param string $var The variable name
     *
     * @param int $min the minimum string length
     *
     * @param int $max The maximum string length
     *
     * @param bool $required
     *
     */
    private function validateDate($var, $msg, $min=0, $max=0, $required=false)
    {
        if($required==false && strlen($this->source[$var]) == 0)
        {
            return true;
        }

        if(isset($this->source[$var]))
        {
            if(strlen($this->source[$var]) < $min)
            {
                $this->errors[$var] = $msg . ' is too short';
            }
            elseif(strlen($this->source[$var]) > $max)
            {
                $this->errors[$var] = $msg . ' is too long';
            }
            elseif(!preg_match("/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/",$this->source[$var]))
            {
                $this->errors[$var] = $msg . ' is invalid';
            }
        }
    }
    /**
     *
     * @validate a string
     *
     * @access private
     *
     * @param string $var The variable name
     *
     * @param int $min the minimum string length
     *
     * @param int $max The maximum string length
     *
     * @param bool $required
     *
     */
    private function validateDateTime($var, $msg, $min=0, $max=0, $required=false)
    {
    	if($required==false && strlen($this->source[$var]) == 0)
    	{
    		return true;
    	}
    
    	if(isset($this->source[$var]))
    	{
    		if(strlen($this->source[$var]) < $min)
    		{
    			$this->errors[$var] = $msg . ' is too short';
    		}
    		elseif(strlen($this->source[$var]) > $max)
    		{
    			$this->errors[$var] = $msg . ' is too long';
    		}
    		elseif(!preg_match("/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])[ ][0-9]{1,2}:[0-9]{1,2}:00$/",$this->source[$var]))
    		{
    			$this->errors[$var] = $msg . ' is invalid';
    		}
    	}
    }
    /**
     *
     * @validate a string
     *
     * @access private
     *
     * @param string $var The variable name
     *
     * @param int $min the minimum string length
     *
     * @param int $max The maximum string length
     *
     * @param bool $required
     *
     */
    private function validateString($var, $msg, $min=0, $max=0, $options=false, $required=false)
    {
        if($required==false && strlen($this->source[$var]) == 0)
        {
            return true;
        }
		if(isset($this->source[$var]))
        {
            if(is_array($options)){
				if(in_array($this->source[$var],$options)===false)
        		{
            		$this->errors[$var] = 'Invalid '.$msg . ' selected';
        		}
        	}
        	elseif(strlen($this->source[$var]) < $min)
            {
                $this->errors[$var] = $msg . ' is too short';
            }
            elseif(strlen($this->source[$var]) > $max)
            {
                $this->errors[$var] = $msg . ' is too long';
            }
            elseif(!is_string($this->source[$var]))
            {
                $this->errors[$var] = $msg . ' is invalid';
            }
        }
    }
    /**
     *
     * @validate an number
     *
     * @access private
     *
     * @param string $var the variable name
     *
     * @param int $min The minimum number range
     *
     * @param int $max The maximum number range
     *
     * @param bool $required
     *
     */
    private function validateNumeric($var, $msg, $min=0, $max=0, $options=false, $required=false)
    {
        if($required==false && strlen($this->source[$var]) == 0)
        {
            return true;
        }
        if(is_array($options)){
        	if(!in_array($this->source[$var],$options))
        	{
            	$this->errors[$var] = 'Invalid '.$msg . ' selected';
        	}
        }
        if(filter_var($this->source[$var], FILTER_VALIDATE_INT, array("options" => array("min_range"=>$min, "max_range"=>$max)))===FALSE)
        {
            $this->errors[$var] = $msg . ' is an invalid number';
        }
    }
    /**
     *
     * @validate a url
     *
     * @access private
     *
      * @param string $var The variable name
     *
     * @param bool $required
     *
     */
    private function validateUrl($var, $msg, $required=false)
    {
        if($required==false && strlen($this->source[$var]) == 0)
        {
            return true;
        }
        if(filter_var($this->source[$var], FILTER_VALIDATE_URL) === false)
        {
            $this->errors[$var] = $msg . ' is not a valid URL';
        }
    }
    /**
     *
     * @validate an email address
     *
     * @access private
     *
     * @param string $var The variable name 
     *
     * @param bool $required
     *
     */
    private function validateEmail($var, $msg, $required=false)
    {
    	if($required==false && strlen($this->source[$var]) == 0)
        {
            return true;
        }
        if(filter_var($this->source[$var], FILTER_VALIDATE_EMAIL) === false)
        {
            $this->errors[$var] = $msg . ' is not a valid email address';
        }
    }
    /**
     * @validate a boolean 
     *
     * @access private
     *
     * @param string $var the variable name
     *
     * @param bool $required
     *
     */
    private function validateBool($var, $msg, $required=false)
    {
        if($required==false && strlen($this->source[$var]) == 0)
        {
            return true;
        }
        filter_var($this->source[$var], FILTER_VALIDATE_BOOLEAN);
        {
            $this->errors[$var] = $msg . ' is Invalid';
        }
    }

    ########## SANITIZING METHODS ############
    /**
     *
     * @santize and email
     *
     * @access private
     *
     * @param string $var The variable name
     *
     * @return string
     *
     */
    public function sanitizeEmail($var)
    {
        $email = preg_replace( '((?:\n|\r|\t|%0A|%0D|%08|%09)+)i' , '', $this->source[$var] );
        $this->sanitized[$var] = (string) filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    /**
     *
     * @sanitize a url
     *
     * @access private
     *
     * @param string $var The variable name
     *
     */
    private function sanitizeUrl($var)
    {
        $this->sanitized[$var] = (string) filter_var($this->source[$var],  FILTER_SANITIZE_URL);
    }
    /**
     *
     * @sanitize a numeric value
     *
     * @access private
     *
     * @param string $var The variable name
     *
     */
    private function sanitizeNumeric($var)
    {
        $this->sanitized[$var] = (int) filter_var($this->source[$var], FILTER_SANITIZE_NUMBER_INT);
    }
    /**
     *
     * @sanitize a string
     *
     * @access private
     *
     * @param string $var The variable name
     *
     */
    private function sanitizeString($var)
    {
        $this->sanitized[$var] = (string) filter_var($this->source[$var], FILTER_SANITIZE_STRING);
    }
    
    public function getError($idx,$err){
    	$this->errors[$idx] = $err;
    }
    
    public function showErrors(){
    	return $this->errors;
    }

} /*** end of class ***/