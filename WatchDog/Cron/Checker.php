<?php
/**
 * Created by PhpStorm.
 * User: Ted
 * Date: 04/27/2021
 */

namespace Nouvolution\WatchDog\Cron;

use Magento\Framework\App\Helper\AbstractHelper;

class Checker extends AbstractHelper {

    protected $_n41;
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_transportBuilder ;
	protected $inlineTranslation ;
    protected $_varFactory;

    public function __construct(\Nouvolution\N41\Helper\Data $n41,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
                                \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\Variable\Model\VariableFactory $varFactory
    ) {
        $this->_n41 = $n41;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
		$this->inlineTranslation = $inlineTranslation;
        $this->_storeManager = $storeManager;
        $this->_varFactory = $varFactory;
    }

    public function execute() {
        $tmp = dirname(__FILE__);
        $codepath = mb_substr($tmp,0,strpos($tmp,"/app/code/"));
        $path = $codepath."/var/n41/";
        $constFileName = "config_log_cron.txt";
        $storecode = 'admin';
        
        if(!is_dir($path)) mkdir($path, 0770, true);
        
        $this->_n41->setLog(__CLASS__."::".__FUNCTION__.">>>>>>>>>> START", 'N41_WATCHDOG_CRON');
        $allStores = $this->_storeManager->getStores(true, false);
        
        foreach($allStores as $store){
            if($store->getCode() != $storecode){
                $storeId = $store->getId();
                $this->_storeManager->setCurrentStore($storeId);

                $file = $storeId . '_' . $constFileName;
                $latestConfigFileName = $path.$file;
                $oldConfig = false;

                $headConfigValue = $this->_scopeConfig
                                    ->getValue('design/head',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);                            
                $footerConfigValue = $this->_scopeConfig
                                    ->getValue('design/footer',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
                $variableCustomConfigValue = $this->_varFactory->create()->getVariablesOptionArray();

                $configJson = json_encode($headConfigValue) . "\n".
                            json_encode($footerConfigValue) . "\n".
                            json_encode($variableCustomConfigValue) . "\n";

                // $this->_n41->setLog($this->_varFactory->create()->getVariablesOptionArray(), 'N41_WATCHDOG_CRON');
                    
                if(file_exists($latestConfigFileName)){
                    $latestConfigFile = fopen($latestConfigFileName, "r");
                    $oldConfig = fread($latestConfigFile, filesize($latestConfigFileName));
                    fclose($latestConfigFile);
                }
                
                $filename = time()."_".$file;
                if($oldConfig === false){
                    $n41log = fopen($path.$filename, "a+") or die("Unable to open file!");
                    fwrite($n41log, $configJson);
                    fclose($n41log);
                    copy($path.$filename, $path.$file); 
                }else{
                    if($oldConfig != $configJson && $oldConfig != null){
                        $n41log = fopen($path.$filename, "a+") or die("Unable to open file!");
                        fwrite($n41log, $configJson);
                        fclose($n41log);
                        copy($path.$filename, $path.$file); // latest file.

                        try {
                            $templateOptions = array(
                                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                                'store' => $storeId
                                            );
                            $templateVars = array(
                                                'storeName' => $this->_storeManager->getStore()->getName(),
                                                'storeURL' => $this->_storeManager->getStore()->getBaseUrl(),
                                            );
                            $to = "webdev@n41.com";
                            $from = [
                                        'name' => 'n41web gmail',
                                        'email'=>"n41web@gmail.com",
                                    ];

                            $this->inlineTranslation->suspend();

                            $transport = $this->_transportBuilder->setTemplateIdentifier('watchdog_form')
                                            ->setTemplateOptions($templateOptions)
                                            ->setTemplateVars($templateVars)
                                            ->setFrom($from)
                                            ->addTo($to)
                                            ->getTransport();
                            $transport->sendMessage();

                            $this->inlineTranslation->resume();

                        } catch (\Exception $e) {
                            $this->_n41->setLog($e->getMessage(), "N41_WATCHDOG_CRON_ERROR");
                        }
                    }
                }
            } // if store_code coditions.
        }
        
        $this->_n41->setLog(__CLASS__."::".__FUNCTION__."<<<<<<<<<<< END", 'N41_WATCHDOG_CRON');

        return $this;
    }
}