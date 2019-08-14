<?php
include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/SearchRequest.php';
include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Response/SearchResponse.php';
include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Response/CanSearchResponse.php';
include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Response/RefineResponse.php'; 
include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Response/GetLayoutResponse.php'; 
include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Response/GetAddressResponse.php'; 
include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/HelperMethods.php';
include_once 'QALayouts.php';
include_once 'QASearchOk.php';
include_once 'QASearchResult.php';
include_once 'Picklist.php';
include_once 'Address.php';

/**
 * ProOnDemand parser class. 
 */
class ExperianDataQuality_AddressValidationParser
{
    public function parseDoGetLayouts(QALayouts $qaLayouts)
    {
        if(isset($qaLayouts) === \NULL)
        {
            throw new \Exception("qaLayouts");                
        }

        $getAddressResponse = new GetLayoutResponse();
        $getAddressResponse->Layouts = $qaLayouts->Layout;       
                
        return $getAddressResponse;
    }
    
    public function parseDoCanSearchResponse(QASearchOk $searchOK)
    {
        if(isset($searchOK) === \NULL)
        {
            throw new \Exception("searchOK");                
        }

        $canSearchRequest = new CanSearchResponse();
        $canSearchRequest->CanSearch = $searchOK->IsOk;
        $canSearchRequest->ErrorMessage = $searchOK->ErrorMessage;
        $canSearchRequest->ErrorCode = $searchOK->ErrorCode;
        $canSearchRequest->ErrorDetail = $searchOK->ErrorDetail;

        return $canSearchRequest;
    }

    public function parseDoSearchResponse(QASearchResult $searchResult) 
    {
        if(isset($searchResult) === \NULL)
        {
            throw new \Exception("searchResult");                
        }

        $searchResponse = new SearchResponse();
        $searchResponse->PickListEntries = $searchResult->QAPicklist->PicklistEntry;
        $searchResponse->Prompt = $searchResult->QAPicklist->Prompt; 
        $searchResponse->Total = $searchResult->QAPicklist->Total;
        $searchResponse->MatchType = $searchResult->VerificationFlagsType;

        return $searchResponse;
    }        
           
    public function parseResponse($responseParameters, $value = \NULL) 
    {
        $searchResponse = new SearchResponse();
        $searchResponse->PickListEntries = $responseParameters->QAPicklist->PicklistEntry;
        $searchResponse->Prompt = $responseParameters->QAPicklist->Prompt; 
        $searchResponse->Total = $responseParameters->QAPicklist->Total;
        $searchResponse->MatchType = $responseParameters->VerificationFlagsType;

        return $searchResponse;
    } 

    public function parseDoRefineResponse(Picklist $doRefineResponse) 
    {
        if(isset($doRefineResponse) === \NULL)
        {
            throw new \Exception("doRefineResponse");                
        }

        $refineResponse = new RefineResponse();
        $refineResponse->PickListEntries = $doRefineResponse->QAPicklist->PicklistEntry;
        $refineResponse->Prompt = $doRefineResponse->QAPicklist->Prompt; 
        $refineResponse->Total = $doRefineResponse->QAPicklist->Total;
        $refineResponse->MatchType = isset($doRefineResponse->VerificationFlagsType) ? $doRefineResponse->VerificationFlagsType : '';

        return $refineResponse;
    }

    public function parseDoGetAddressResponse(Address $address, $dataSet) 
    {
        if(isset($address) === \NULL)
        {
            throw new \Exception("address");                
        }

        $getAddressResponse = new GetAddressResponse();
        $getAddressResponse->MatchType = HelperMethods::isNullOrWhiteSpace($address->VerifyLevel) ? 'Verified': $address->VerifyLevel;  
        $getAddressResponse->DPVStatus = $address->QAAddress->DPVStatus;
        $getAddressResponse->AddressLineDictionary = array();
        $getAddressResponse->MissingSubPremise = $address->QAAddress->MissingSubPremise;
        $getAddressResponse->Total = (int) 0;
        
        $countOfAddressLines = count($address->QAAddress->AddressLine);
        for ($index = 0; $index < $countOfAddressLines; $index++)
        {          
            $getAddressResponse->AddressLineDictionary['Line' . $index] = $address->QAAddress->AddressLine[$index]->Line;
            
            if($dataSet !== 'DE' && $dataSet !== 'US' && $dataSet !== 'CA') { continue; }
            
            $label = trim($address->QAAddress->AddressLine[$index]->Label);            
            if(($label === "State" || $label === "State code" || $label === "Province code"))
            {
                $regionCode = $address->QAAddress->AddressLine[$index]->Line;
                $region = $dataSet === 'DE' 
                        ? Mage::getModel('directory/region')->loadByName($regionCode, $dataSet)
                        : Mage::getModel('directory/region')->loadByCode($regionCode, $dataSet);
                
                $getAddressResponse->AddressLineDictionary['Line' . $index] = $region->getId();              
            }            
        }

        return $getAddressResponse;
    }     
}