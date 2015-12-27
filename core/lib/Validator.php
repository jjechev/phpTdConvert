<?php

class Validator
{

    /**
     * Val. List
     * 
     */
    private static $currentFormData;
    private static $errors = array();

    public static function check($formData, $validatorRules)
    {
        self::$errors = array();

        $formData = self::sanitizeInput($formData);
        self::$currentFormData = $formData;

        foreach ($formData as $formDataKey => $formDataValue)
        {
            if (isset($validatorRules[$formDataKey]))
            {
                $status = true;
                $rules = explode('|', $validatorRules[$formDataKey]);
                foreach ($rules as $rule)
                {
                    $ruleElement = explode(":", $rule);
                    $method = $ruleElement[0];
                    unset($ruleElement[0]);
                    $ruleElement = array_values($ruleElement);
                    $result = self::$method($formDataKey, $formDataValue, $ruleElement);
                    $status = $status && $result;
                }
            }
        }
    }

    /**
     * Filtered input data
     * @param  array $formData 
     * @return array           
     */
    private static function sanitizeInput($formData)
    {
        $sanitizedData = filter_var_array($formData, FILTER_SANITIZE_STRING);
        return $sanitizedData;
    }

    /**
     * The field under validation must be yes, on, 1, or true. This is useful for validating "Terms of Service" acceptance.
     * @param type $key
     * @param type $value
     * @param array $validateData
     */
    private static function accepted($key, $value, array $validateData)
    {
        $acceptedValues = array('1', 'yes', 'on', true, 1);
        if (in_array($value, $acceptedValues, true))
        {
            return true;
        }
        else
        {
            self::$errors[$key]['accepted'] = "Not accepted";
            return false;
        }
    }

    /**
     * Check for minLenght
     * @param  string $value        
     * @param  array  $validateData 
     * @return boolean
     */
    private static function minLenght($key, $value, array $validateData)
    {
        if (strlen($value) > $validateData[0])
            return true;
        else
        {
            self::$errors[$key]['minLenght'] = "This field must be atleas " . $validateData[0] . " chars long!";
            return false;
        }
    }

    /**
     * Check for maxLenght
     * @param  string $value        
     * @param  array  $validateData 
     * @return boolean
     */
    private static function maxLenght($key, $value, array $validateData)
    {
        if (strlen($value) < $validateData[0])
            return true;
        else
        {
            self::$errors[$key]['maxLenght'] = "This field must be less than " . $validateData[0] . " chars long!";
            return false;
        }
    }

    /**
     * Check for numeric
     * Проверява дали даденото input поле е с цифрови стойности
     * @param  string $value        
     * @param  array  $validateData 
     * @return boolean               
     */
    private static function numeric($value, array $validateData)
    {
        $status = is_numeric($value);
        if ($status == false)
        {
            self::$errors[$key]['numeric'] = 'This field must contain only numeric symbols!';
        }
        return $status;
    }

    /**
     * Check for firstUpper
     * @param  string $value
     * @return boolean        
     */
    private static function firstUpper($value)
    {
        $comparison = ucwords(strtolower($value));
        if ($comparison === $value)
            return true;
        else
        {
            self::$errors[$key]['firstUpper'] = 'This field must have: first letter capital, others to be lower case!';
            return false;
        }
    }

    /**
     * Check for email
     * @param  string $value
     * @return boolean        
     */
    private static function email($value)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL))
            return true;
        else
        {
            self::$errors[$key]['email'] = 'This is an email field, it must contain email formatting: example@domain.com';
            return false;
        }
    }

    /**
     * Check for alphaNumeric
     * @param  string $value
     * @return boolean        
     */
    private static function alphaNumeric($value)
    {
        $result = ctype_alnum($value);
        if ($result == false)
        {
            self::$errors[$key]['alphaNumeric'] = 'This field must contain only letters and numbers!';
        }
        return $result;
    }

    /**
     * Check for alpha
     * @param  string $value
     * @return boolean        
     */
    private static function alpha($value)
    {
        $result = ctype_alpha($value);
        if ($result == false)
        {
            self::$errors[$key]['alpha'] = 'This field must contain only alphabetical characters';
        }
        return $result;
    }

    /**
     * Check for password
     * 1. Да има поне една главна буква;
     * 2. Да има поне една малка буква;
     * 3. Да има поне една цифра;
     * 4. Да има поне един специален символ;
     * @param  string $value
     * @return boolean        
     */
    private static function password($value)
    {
        if (!preg_match('/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]$/', $value))
        {
            self::$errors[$key]['password'] = 'The ' . $formDataKey . 'field must contain: Atleas one capital letter, atleast one lowercase letter, atleast one number, and atleast one special symbol';
            return false;
        }
        else
            return true;
    }

    /**
     * Check for same fields
     * 	same:[fieldName]
     * @param  string $value        
     * @param  string $validateData 
     * @return boolean               
     */
    private static function same($value, $validateData)
    {
        if ($value === self::$currentFormData[$validateData])
            return true;
        else
        {
            self::$errors[$key]['same'] = 'The field does not match the ' . self::$currentFormData[$validateData] . ' value';
            return false;
        }
    }

    /**
     * Check for required
     * @param  string $value
     * @return boolean
     */
    private static function required($value)
    {
        if (empty($value))
        {
            return false;
        }
        else
            return true;
    }

    /**
     * Returns the errors in an array
     * @return array
     */
    public static function getErrors()
    {
        return self::$errors;
    }

}
