<?php

class rcube_passwdqc_password
{
  // Score constants
  public const SCORE_WEAK = 1;
  public const SCORE_ACCEPTABLE = 2;
  public const SCORE_STRONG = 3;

  private $dict = array (
    "0123456789",
    "`1234567890-=",
    "~!@#$%^&*()_+",
    "abcdefghijklmnopqrstuvwxyz",
    "a1b2c3d4e5f6g7h8i9j0",
    "1a2b3c4d5e6f7g8h9i0j",
    "abc123",
    "qwertyuiop[]\\asdfghjkl;'zxcvbnm,./",
    "qwertyuiop{}|asdfghjkl:\"zxcvbnm<>?",
    "qwertyuiopasdfghjklzxcvbnm",
    "1qaz2wsx3edc4rfv5tgb6yhn7ujm8ik,9ol.0p;/-['=]\\",
    '!qaz@wsx#edc$rfv%tgb^yhn&ujm*ik<(ol>)p:?_{\"+}|',
    "qazwsxedcrfvtgbyhnujmikolp",
    "1q2w3e4r5t6y7u8i9o0p-[=]",
    "q1w2e3r4t5y6u7i8o9p0[-]=\\",
    "1qaz1qaz",
    "1qaz!qaz",
    "1qazzaq1",
    "zaq!1qaz",
    "zaq!2wsx"
  );
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
      $message = $rc->gettext('password.too_weak');
    } elseif ($score === self::SCORE_ACCEPTABLE) {
      $rc = rcmail::get_instance();
      $message = $rc->gettext('password.acceptable_strength');
    } elseif ($score === self::SCORE_STRONG) {
      $rc = rcmail::get_instance();
      $message = $rc->gettext('password.strong_strength');
    }

    return [$score, $message];
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
      $this->_debug("The password is too long");
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
    $revpass = strrev($passwd);
    foreach ($this->dict as $dictword) {
      foreach ($this->nsubstr($dictword) as $word) {
        $this->_debug("Checking $word");
        if ($this->check_dict_word($passwd, $word)) {
          return true;
        }
        if ($this->check_dict_word($revpass, $word)) {
          return true;
        }
      }
    }
    // Also check in reverse
    $this->_debug("Password doesn't contain dict words");
    return false;
  }

  /**
   * Checks if a particular dictionary word makes a password
   * unsafe
   *
   * @param string $pass the password to check
   *
   * @param string $word the word that may make the password weak
   *
   * @return bool true if the password is weak with the word removed
   */
  private function check_dict_word($pass, $word)
  {
    $stripos = stripos($pass, $word);
    if ($stripos !== false) {
      $this->_debug("$word found in password");
      $newpass = substr_replace($pass, '', $stripos, strlen($word));
      $this->_debug($newpass);
      if ($this->evaluate_strength($newpass) === self::SCORE_WEAK) {
        $this->_debug('Password weak with dictionary word removed');
        return true;
      }
    }
  }


  /**
   * Returns all substrings of length n
   *
   * @param string $str
   *
   * @return Array string 
   */
  private function nsubstr($str)
  {
    $match = rcmail::get_instance()->config->get('password_passwdqc_match');
    if (strlen($str) < $match) {
      return [$str];
    }

    $subs = [];
    for ($i = 0; $i < strlen($str)-$match+1; $i++) {
      $subs[] = substr($str, $i, $match);
    }
    return $subs;
  }

  /**
   * Prints debug info to the log
   */
  protected function _debug($str)
  {
    rcube::write_log('passwdqc', $str);
  }
}

