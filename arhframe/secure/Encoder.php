<?php

/**
*
*/
class Encoder
{
    private $encoders;
    private $salt ="";
    public function __construct()
    {
    }
    public function setEncoders($encoders)
    {
        if (!is_array($encoders)) {
            $this->encoders = array($encoders);
        } else {
            $this->encoders = $encoders;
        }
    }

    public function crypt($text)
    {
        if (empty($this->encoders) || in_array('plaintext', $this->encoders)) {
            return $text;
        }
        $encoderAvailable = hash_algos();
        foreach ($this->encoders as $encoder) {
            if (!in_array($encoder, $encoderAvailable)) {
                continue;
            }
            $text = hash($encoder, $text.$this->salt);
        }

        return $text;
    }
	public function getSalt() {
		return $this->salt;
	}
	public function setSalt($salt) {
		$this->salt = $salt;
		return $this;
	}


}