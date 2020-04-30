<?php
/**
 * 
 * @package	PHP Website Crawler
 * @author	Prithu Ahmed
 */
class Crawler {
	protected $_url;
	protected $_depth;
	protected $_domain;
	protected $_crawled		= array();
	protected $_visited		= array();
	protected $_images 		= array();
	protected $_internal 	= array();
	protected $_external 	= array();
	protected $_words 		= array();
	protected $_titles 		= array();
	protected $_result		= array();

	public function __construct($url, $depth=5)
	{
		$this->_url = rtrim($url,'/'); // remove trailing slash
		$this->_depth = $depth;
		$parseUrl = parse_url($url);
		$this->_domain = $parseUrl['host'];
	}

	/**
     * Load the page from curl to the DOMDocument, validate the document and
     * decement the depth. Store each url's status, load time, links count, image count,
     * text count in an Array.
     * @param String - The url of the page to be loaded
     */
	protected function loadPage($url)
	{
		list($content, $status, $time) = $this->getContent($url);
		$doc = new DOMDocument();
    	$loaded = @$doc->loadHTML($content);
    	if ($loaded !== false) {
	        $this->_depth-=1;
	        $this->crawlLinks($doc,$url);
	        $this->crawlImages($doc,$url);
	        $this->crawlText($doc,$url);
	        $this->crawlTitle($doc,$url);
	        $this->_crawled[] = array('url'=>$url, 'code'=>$status, 'load'=>$time, 'internal'=>count($this->_internal[$url]), 'external'=>count($this->_external[$url]), 'images'=>count($this->_images[$url]), 'words'=>$this->_words[$url], 'title_length'=>array_sum($this->_titles[$url])/count($this->_titles[$url]));
	    }	    
	}

	/**
	 * Loop through all the a tags and store the link href url 
	 * in an array while checking for unique internal and external links.
	 * Call the loadPage function again when an internal url is found,
	 * keep calling the loadPage function on every loop until depth is zero.
     * @param DOMDocument - The DOMDocument of the url     
     */
	protected function crawlLinks($doc,$url)
	{
		foreach ($doc->getElementsByTagName("a") as $aTag) {
	        $href = rtrim($aTag->getAttribute('href'),'/'); // remove trailing slash
	        if (strpos($href,'#') === false) { //ignore anchor urls
		        if (filter_var($href, FILTER_VALIDATE_URL) === false ) {
		            $href = $this->_url.$href;
		        }
		        if($this->_url == $href) continue; // continue to next loop if it's the base url
		        if(in_array($href, $this->_visited)) continue; // continue to next loop if the url was already visited	        	       

		        if (strpos($href, $this->_domain) !== false) {	        	
		            $this->_internal[$url][] = $href;
		            $this->_visited[] = $href;
		            // call loadPage function until number of pages to check reaches zero
		    		if($this->_depth>0)$this->loadPage($href);
		        } else {
		            $this->_external[$url][] = $href;
		            $this->_visited[] = $href;
		        }
	    	}
	    }
	    
	}

	/**
	 * Loop through all the img tags and store the unique
	 * image src url in an array
     * @param DOMDocument - The DOMDocument of the url     
     */
	protected function crawlImages($doc,$url)
	{
		foreach ($doc->getElementsByTagName("img") as $imgTag) {
	        $src = $imgTag->getAttribute('src');
	        if(in_array($src, $this->_visited)) continue;
            $this->_images[$url][] = $src;	
            $this->_visited[] = $src;        
	    }
	}

	/**
	 * Loop through all the text nodes and store the counted
	 * words in an array
     * @param DOMDocument - The DOMDocument of the url     
     */
	protected function crawlText($doc,$url)
	{
		$xpath = new DOMXPath($doc);
		$nodes = $xpath->query('//text()'); // text() will only give the textnodes in the document

		$textNodeContent = '';
		foreach($nodes as $node) {
		    $textNodeContent .= " $node->nodeValue";
		}
		$this->_words[$url] = str_word_count($textNodeContent);
	}

	/**
	 * Loop through all the h1 and h2 tags and store the counted
	 * string length in an array
     * @param DOMDocument - The DOMDocument of the url     
     */
	protected function crawlTitle($doc,$url)
	{
		foreach ($doc->getElementsByTagName("h1") as $hTag) {	  
			if(in_array($hTag->nodeValue, $this->_visited)) continue;
            $this->_titles[$url][] =  strlen($hTag->nodeValue);	        
            $this->_visited[] = $hTag->nodeValue;      
	    }
	    foreach ($doc->getElementsByTagName("h2") as $h2Tag) {	       
            if(in_array($h2Tag->nodeValue, $this->_visited)) continue;
            $this->_titles[$url][] =  strlen($h2Tag->nodeValue);	        
            $this->_visited[] = $h2Tag->nodeValue;              
	    }
	}
	
	/**
     * 
     * @param String - The url from which the html will be loaded
     * @return Array - An array of the page contents, status and loading time
     */
    protected function getContent($url)
    {
    	$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; CrawlBot/1.0.0)');
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_HEADER, true);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 5);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	    curl_setopt($ch, CURLOPT_ENCODING, "");
	    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //required for https urls
	    curl_setopt($ch, CURLOPT_MAXREDIRS, 15);     

		$html = curl_exec($ch);
		$time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return array($html, $status, $time);        
    }

	/**
     * Calling this method will start crawler
     */
	public function run()
    {
        $this->loadPage($this->_url);
    }

	/**
     * 
     * @return Array - an Array of all the pages crawled
     */
    public function getResult()
    {
    	return $this->_crawled;
    }
}


/*$imageCount=0;$internalCount=0;$externalCount=0;$pageLoad=0;$wordCount=0;$titleLength=0;
$url = 'https://agencyanalytics.com';
$pages = 10;
$crawler = new Crawler($url,$pages); // create an object of Crawler class
$crawler->run(); // execute the code
$visited = $crawler->getResult(); // fetch the result into an array
$pageCrawled = count($visited);
foreach($visited as $visit)
{
	$url = $visit['url'];
	$imageCount += $visit['images'];
	$internalCount += $visit['internal'];
	$externalCount += $visit['external'];
	$wordCount += $visit['words'];
	$titleLength += $visit['title_length'];			
	$pageLoad +=$visit['load'];
}
echo "Images: ".$imageCount."<br>Internal Links: ".$internalCount."<br>External Links: ".$externalCount."<br>Word Counts: ".$wordCount."<br>Titles Length:".$titleLength;*/