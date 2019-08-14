<?php 
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/CanSearchRequest.php');
    require_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/GetLayoutsRequest.php';
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/SearchRequest.php');
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/RefineRequest.php');
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/GetAddressRequest.php');
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/BaseSearchRequest.php');
    
    interface ExperianDataQuality_IService 
    { 
        function doGetLayouts(GetLayoutsRequest $doGetLayoutsRequest);

        function doCanSearch(CanSearchRequest $canSearchRequest);

        function doSearch(BaseSearchRequest $baseRequest,  $searchAddress = "");

        function doRefine(RefineRequest $refineRequest);

        function doGetAddress(GetAddressRequest $getAddressRequest);
    }