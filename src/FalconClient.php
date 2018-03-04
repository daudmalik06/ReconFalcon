<?php
/**
 * Created by PhpStorm.
 * User: daudm
 * Date: 3/4/2018
 * Time: 5:55 PM
 */

namespace dawood\ReconFalcon;

use Threaded;

class FalconClient extends Threaded
{
    /**
     * @var
     */
    private $url;
    private $finishedWorking;
    private $siteLive;
    /**
     * @var int
     */
    private $timeOut;
    /**
     * @var bool
     */
    private $ignoreSSLErrors;

    /**
     * FalconClient constructor.
     * @param string $url
     * @param int $timeOut
     * @param bool $ignoreSSLErrors
     */
    function __construct(string $url, int $timeOut = 10 , bool $ignoreSSLErrors = true)
    {
        $this->url = $url;
        $this->timeOut = $timeOut;
        $this->ignoreSSLErrors = $ignoreSSLErrors;
    }

    /**
     * @return bool
     */
    public function run()
    {
        $this->siteLive = $this->isSiteAvailable($this->url,$this->timeOut, $this->ignoreSSLErrors);
        $this->finishedWorking =true;
    }

    public function isDone()
    {
        return $this->finishedWorking;
    }

    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function isLive()
    {
        return $this->siteLive;
    }

    /**
     * @param $url
     * @param $timeOut
     * @param $ignoreSSLErrors
     * @return bool
     * @throws \Exception
     */
    public function isSiteAvailable(string $url, int $timeOut = 10, bool $ignoreSSLErrors=true)
    {
        $ch = curl_init(trim($url));
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeOut);
        curl_setopt($ch,CURLOPT_HEADER,true);
        curl_setopt($ch,CURLOPT_NOBODY,true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
        if($ignoreSSLErrors)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response) {
            return true;
        }
        return false;
    }
}