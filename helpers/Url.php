<?php
namespace asinfotrack\yii2\toolbox\helpers;

/**
 * This helper extends the basic functionality of the Yii2-Url-helper.
 * It provides functionality to retrieve information about the currently
 * requested url, such as TLD, subdomains, etc.
 * 
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class Url extends \yii\helpers\Url
{
	
	/**
	 * @var array internal cache for preparsed request data
	 */
	protected static $RCACHE;
	
	/**
	 * Caches the data for faster acces in subsequent calls
	 */
	protected static function cacheReqData()
	{		
		//fetch relevant vars
		$host = rtrim(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'], '/');
		$hostParts = array_reverse(explode('.', $host));
		static::$RCACHE = [
			'protocol'=>Yii::$app->request->isSecureConnection ? 'https' : 'http',
			'host'=>$host,
			'uri'=>$_SERVER['REQUEST_URI'],
			'queryString'=>Yii::$app->request->queryString,
			'hostParts'=>$hostParts,
			'numParts'=>count($hostParts),
		];
	}
	
	/**
	 * Returns the protocol. In case of a secure connection, this is 'https', otherwise
	 * 'http'. If $widthColonAndSlashes param is true (default), the colon and slashes
	 * will be appended.
	 * 
	 * @param boolean $widthColonAndSlashes if true 'http' -> 'http://'
	 * @return string the protocol (either http or https)
	 */
	public static function getProtocol($widthColonAndSlashes=true)
	{
		if (!isset(self::$RCACHE)) static::cacheReqData();
		
		return $widthColonAndSlashes ? self::$RCACHE['protocol'] . '://' : self::$RCACHE['protocol'];
	}
	
	/**
	 * Returns the TLD part of the requested host name (eg 'com', 'org', etc.). If
	 * there is no tld (eg localhost), null is returned
	 * 
	 * @return string|null tld or null if there is none
	 */
	public static function getTld()
	{
		if (!isset(self::$RCACHE)) static::cacheReqData();
		
		return self::$RCACHE['numParts'] > 1 ? self::$RCACHE['hostParts'][0] : null;
	}
	
	/**
	 * Returns the actual domain or host-name of the current request.
	 * This can either be 'yourpage.com' or 'server23' in case of a
	 * hostname
	 * 
	 * @return string domain or host
	 */
	public static function getDomain()
	{
		if (!isset(self::$RCACHE)) static::cacheReqData();

		//decide if tld needs to be joined
		if (self::$RCACHE['numParts'] > 1) {
			return self::$RCACHE['hostParts'][1] . '.' . static::getTld();
		} else {
			return self::$RCACHE['hostParts'][0];
		}
	}
	
	/**
	 * Powerful method to get the full subdomain or parts of it.
	 * If no index is provided, the full subdomain will be returned:
	 * http://my.super.fast.website.com -> 'my.super.fast'
	 * If an index is provided, that specific part is returned. In the
	 * preceding example that would translate as follows:
	 * 0:	fast
	 * 1:	super
	 * 2:	my
	 * 
	 * Should there be no subdomain or the index is out of range, 
	 * null is returned.
	 * 
	 * @param integer $index optional index to return
	 * @return null|string either full subdomain, a part of it or null
	 */
	public static function getSubdomain($index=null)
	{
		if (!isset(self::$RCACHE)) static::cacheReqData();
		
		//if no more than two parts there is no subdomain
		if (self::$RCACHE['numParts'] < 3) return null;
		
		//check if certain index is requested
		if ($index === null) {
			//join all subdomain parts and return them
			return implode('.', array_reverse(array_slice(self::$RCACHE['hostParts'], 2)));
		} else {
			//check if there is such a part
			if (self::$RCACHE['numParts'] <= $index + 2) return null;
			//return it
			return self::$RCACHE['hostParts'][2 + $index];
		}
	}
	
}