<?php

class rcube_passwdqc_password
{
    // Score constants
    public const SCORE_WEAK = 1;
    public const SCORE_ACCEPTABLE = 2;
    public const SCORE_STRONG = 3;

    /**
     * Rule description.
     *
     * @return array human-readable description of the check rule
     */
    public function strength_rules()
    {
        $rc = rcmail::get_instance();
        $N0 = $rc->config->get('password_passwdqc_n0');
        $N1 = $rc->config->get('password_passwdqc_n1');
        $N2 = $rc->config->get('password_passwdqc_n2');
        $N3 = $rc->config->get('password_passwdqc_n3');
        $N4 = $rc->config->get('password_passwdqc_n4');
        $max = $rc->config->get('password_passwdqc_max');
        $passphrase = $rc->config->get('password_passwdqc_passphrase');
        return [
            strtr($rc->gettext("password.passwdqc_minlength"), array('$N4' => $N4)),
            strtr($rc->gettext("password.passwdqc_maxlength"), array('$max' => $max)),
            strtr($rc->gettext("password.passwdqc_4classes"), array('$N4' => $N4, '$N3' => $N3)),
            strtr($rc->gettext("password.passwdqc_3classes"), array('$N3' => $N3, '$N1' => $N1)),
            strtr($rc->gettext("password.passwdqc_2classes"), array('$N1' => $N1, '$N0' => $N0)),
            strtr($rc->gettext("password.passwdqc_1classes"), array('$N0' => $N0)),
            strtr($rc->gettext("password.passwdqc_passphrase"), array('$passphrase' => $passphrase, '$N2' => $N2)),
            $rc->gettext("password.passwdqc_firstupper_lastdigit"),
        ];
    }

    /**
     * Password strength check.
     * Return values:
     *     1 - if password is too weak.
     *     2 - if password is of acceptable strength
     *     3 - if password is strong enough.
     *
     * @param string $passwd Password
     *
     * @return int password score (1 to 3)
     */
    public function check_strength($passwd)
    {
        $score = $this->evaluate_strength($passwd);
        $message = null;

        if ($score === self::SCORE_WEAK) {
            $rc = rcmail::get_instance();
        } elseif ($score === self::SCORE_ACCEPTABLE) {
            $rc = rcmail::get_instance();
        } elseif ($score === self::SCORE_STRONG) {
            $rc = rcmail::get_instance();
        }

        return $score;
    }

    /**
     * Evaluate password strength based on defined rules.
     *
     * @param string $passwd
     *
     * @return int score, one of the SCORE_* constants (between 1 and 3)
     */
    private function evaluate_strength($passwd)
    {

      $this->_debug("Checking password strength");
        $rc = rcmail::get_instance();
        $N0 = $rc->config->get('password_passwdqc_n0');
        $N1 = $rc->config->get('password_passwdqc_n1');
        $N3 = $rc->config->get('password_passwdqc_n3');
        $N4 = $rc->config->get('password_passwdqc_n4');
        $max = $rc->config->get('password_passwdqc_max');
        $length = strlen($passwd);

        // Verify if the password is too long
        if ($length > $max) {
            return self::SCORE_WEAK;
        }

        // Verify if the password is a passphrase
        if ($this->is_passphrase($passwd)===self::SCORE_STRONG) {
          $this->_debug("The password is a passphrase");
          return self::SCORE_STRONG;
        }

        // Verificar requisitos de complejidad
        if ($length < $N4) {
          $this->_debug('The password is too short');
            return self::SCORE_WEAK;
        }

        $has_upper = preg_match('/[A-Z]/', $passwd);
        $has_lower = preg_match('/[a-z]/', $passwd);
        $has_digit = preg_match('/\d/', $passwd);
        $has_special = preg_match('/[\W_]/', $passwd);

        // Verify class count for each password length
        if ($length >= $N4 && $length <= ($N3-1)) {
            if (!($has_upper && $has_lower && $has_digit && $has_special)) {
                $this->_debug('Password needs at least 4 character classes');
                return self::SCORE_WEAK;
            }
        } elseif ($length >= $N3 && $length <= ($N1-1)) {
            if (($has_upper + $has_lower + $has_digit + $has_special) < 3) {
                $this->_debug('Password needs at least 3 character classes');
                return self::SCORE_WEAK;
            }
        } elseif ($length >= $N1 && $length <= $N0) {
            if (($has_upper + $has_lower + $has_digit + $has_special) < 2) {
                $this->_debug('Password needs at least 2 character classes');
                return self::SCORE_WEAK;
            }
        }

        // Verify that if the password passes the firstupper_lastdigit check
        preg_match_all('/[A-Z]/', $passwd, $matches);
        $num_upper = count($matches[0]);
        preg_match_all('/\d/', $passwd, $matches);
        $num_digit = count($matches[0]);

        if ($num_upper === 1 && ctype_alpha($passwd[0]) && $passwd[0] === strtoupper($passwd[0])) {
            $this->_debug('Password fails the firstupper check');
            return self::SCORE_WEAK;
        }
        if ($num_digit === 1 && is_numeric($passwd[$length - 1])) {
            $this->_debug('Password fails the lastdigit check');
            return self::SCORE_WEAK;
        }

        // Verificar si contiene palabras del diccionario (ejemplo simple)
        if ($this->contains_dictionary_words($passwd)) {
            $this->_debug('Tiene palabras de diccionario');
            return self::SCORE_WEAK; // No se permiten palabras del diccionario
        }

        return self::SCORE_STRONG; // Contraseña fuerte
    }

    /**
     * Checks if a password is a passphrase
     *
     * @param string $passwd
     *
     * @return bool true if password is a passphrase
     */
    private function is_passphrase($passwd)
    {
        $rc = rcmail::get_instance();
        $passphrase = $rc->config->get('password_passwdqc_passphrase');
        $N2 = $rc->config->get('password_passwdqc_n2');

        $length = strlen($passwd);
        $word_count = str_word_count($passwd);

        // Evaluar la fuerza de la passphrase
        if ($length >= $N2 || $word_count >= $passphrase) {
            return self::SCORE_STRONG;
        }

        return self::SCORE_WEAK; // Passphrase fuerte
    }
    
    /**
     * Checks if a password would be weak with dictionary words removed
     *
     * @param string $passwd
     *
     * @return bool true if password is weak because it contains dictionary words
     */
    private function contains_dictionary_words($passwd)
    {
      // A basic dictionary with some simple sequences
      $dictionary = ['password', '123456', 'qwerty', 'letmein']; 
      foreach ($dictionary as $word) {
          if (stripos($passwd, $word) !== false) {
            // If the dictionary word appears in the password, check
            // the strength of the password with the word removed
            // if it is weak, the original password is considered weak
            $this->_debug("$word found in password");
            $newpass = str_replace($word, '', $passwd);
            if ($this->evaluate_strength($newpass) === self::SCORE_WEAK) {
              $this->_debug('Password weak with dictionary word removed');
              return true;
            }
          }
      }
      // Also check in reverse
      $revpass = strrev($passwd);
      foreach ($dictionary as $word) {
          if (stripos($revpass, $word) !== false) {
            // If the dictionary word appears in the password, check
            // the strength of the password with the word removed
            // if it is weak, the original password is considered weak
            $this->_debug("$word found in password");
            $newpass = str_replace($word, '', $revpass);
            if ($this->evaluate_strength($newpass) === self::SCORE_WEAK) {
              $this->_debug('Password weak with dictionary word removed');
              return true;
            }
          }
      }
      return false;
    }
    /**
     * Prints debug info to the log
     */
    protected function _debug($str)
    {
            rcube::write_log('passwdqc', $str);
    }
}

