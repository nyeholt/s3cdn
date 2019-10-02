<?php

/**
 * @author marcus@symbiote.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class S3ContentWriter extends ContentWriter {

	public $bucket = 'bucket';
	
	/**
	 * @var S3Client
	 */
	public $s3Service;

    /**
     * What initial permission should assets be given?
     *
     * @var string
     */
    public $defaultAcl = 'public-read';
    
    /**
     * Should this be prefixed in the remote store?
     *
     * @var string
     */
    public $prefix;
    
    /**
     * Should names be hashed by default?
     *
     * @var boolean
     */
    public $hashedNames = false;
    
    public function nameToId($name) {
        $nameId = '';
        if ($this->hashedNames) {
            $nameId = parent::nameToId($name);
        } else {
            $nameId = strpos($name, 'assets/') === 0 ? substr($name, 7) : $name;
        }
        
        return $this->prefix ? $this->prefix . '/' . $nameId : $nameId;
		
	}
	
	/**
	 * Write content to storage
	 *
	 * @param mixed $content 
	 * @param string $name
	 *				The name that is used to refer to this piece of content, 
	 *				if needed
	 */
	public function write($content = null, $fullname = '', $type = null) {
		
		$reader = $this->getReaderWrapper($content);
		
		$name = basename($fullname);
		
		if (!$this->id) {
			if (!$name) {
				throw new Exception("Cannot write a file without a name");
			}
			$this->setId($this->nameToId($fullname));
		}

		if (class_exists('HTTP') && strlen($name)) {
			$type = HTTP::get_mime_type($name);
		}
		$attrs = array(
			'Bucket' => $this->bucket,
			'Key'    => $this->getId(),
			'Body'   => $reader->read(),
			'ACL'    => $this->defaultAcl,
		);
		
        // protect against writing an odd content type
		if ($type && $type != 'application/unknown' && $type != 'directory') {
			$attrs['ContentType'] = $type;
		}
		
		// set cache control
		$cacheControl = Config::inst()->get('S3ContentWriter', 'cache_control');
		if($cacheControl && is_array($cacheControl)) {
			$cacheControlType = str_replace('/', "_", $type);
			if(isset($cacheControl[$cacheControlType])) {
				$attrs['CacheControl'] = $cacheControl[$cacheControlType];
			} else if(isset($cacheControl["default"])) {
				$attrs['CacheControl'] = $cacheControl["default"];
			}
		}
		
		$result = $this->s3Service->putObject($attrs);
		
		if (!$result) {
			throw new Exception("Failed uploading to S3");
		}

		// print_r($this->getHaylix()->info_container($this->publicContainer));
	}

	public function delete() {
		$result = $this->s3Service->deleteObject(array('Bucket' => $this->bucket, 'Key' => $this->getId()));
		return $result;
	}
}
