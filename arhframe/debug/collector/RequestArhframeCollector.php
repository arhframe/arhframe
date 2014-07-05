<?php
import('arhframe.Router');
import('arhframe.BeanLoader');
class RequestArhframeCollector extends DebugBar\DataCollector\RequestDataCollector
{
    public function collect()
    {
        $array = parent::collect();
        $request = BeanLoader::getInstance()->getBean('arhframe.request');
        $array['$_INFO'] = $this->formatVar($request->getInfoRequest());

        return $array;
    }
}
