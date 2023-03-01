<?php
/**
 * Crypt class to perform the majority of encryption and decryption functions within sheel.
 *
 * @package      sheel\Crypt
 * @version      1.0.0.0
 * @author       sheel
 */
class crypt
{
	protected $sheel;
	private $securekey, $iv;
	/**
	 * Constructor
	 */
	function __construct($sheel)
	{
		$this->sheel = $sheel;
		$this->securekey = md5($this->sheel->config['key1'] . $this->sheel->config['key2'] . $this->sheel->config['key3']);
		$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
		$this->iv = openssl_random_pseudo_bytes($ivlen);
	}

	/**
	 * Function to process and encrypt a text string with url compatibility
	 *
	 * @param       string        plain text
	 *
	 * @return      string        Returns encrypted text
	 */
	public function encrypt($encrypt = '')
	{
		$encrypt = serialize($encrypt);

		$mac = hash_hmac('sha256', $encrypt, substr(bin2hex($this->securekey), -32));
		$passcrypt = openssl_encrypt($mac, "AES-128-CBC", $this->securekey, $options=OPENSSL_RAW_DATA, $this->iv);
		$encoded = $this->base64_url_encode($passcrypt) . '|' . $this->base64_url_encode($this->iv);
		return $encoded;
	}
	/**
	 * Function to process and decrypt a text string
	 *
	 * @param       string        encrypted text
	 *
	 * @return      string        Returns decrypted text
	 */
	public function decrypt($decrypt = '')
	{
		$decrypt = explode('|', $decrypt . '|');
		$decoded = $this->base64_url_decode($decrypt[0]);
		$iv = $this->base64_url_decode($decrypt[1]);
		if (strlen($iv) !== mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC)) {
			return false;
		}
		$decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->securekey, $decoded, MCRYPT_MODE_CBC, $iv));
		$mac = substr($decrypted, -64);
		$decrypted = substr($decrypted, 0, -64);
		$calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($this->securekey), -32));
		if ($calcmac !== $mac) {
			return false;
		}
		$decrypted = unserialize($decrypted);
		return $decrypted;
	}
	/**
	 * Function safely base64 encode a url
	 *
	 * @param        string       text to encode
	 *
	 * @return	string       HTML representation the buyer spending gauge for public view
	 */
	public function base64_url_encode($input)
	{
		return strtr(base64_encode($input), '+/=', '-_,');
	}
	/**
	 * Function safely base64 decode a url
	 *
	 * @param        string       encoded text to decode
	 *
	 * @return	string       HTML representation the buyer spending gauge for public view
	 */
	public function base64_url_decode($input)
	{
		return base64_decode(strtr($input, '-_,', '+/='));
	}
}
?>