<?php
class Evil_Minify_HtmlCompressorPlugin extends Zend_Controller_Plugin_Abstract {
    /**
     * Compress html code.
     *
     * @return void
     */
    public function dispatchLoopShutdown() {
        $compressor = new Evil_Minify_HtmlCompressor();
        $response = $this->getResponse();
        $body = $response->getBody();
        $body = $compressor->filter($body);
        $response->setBody($body);
    }

}