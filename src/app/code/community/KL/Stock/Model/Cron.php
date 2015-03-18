<?php 

class KL_Stock_Model_Cron
{
    public function fire()
    {
        Mage::dispatchEvent('time_to_fix_stock_status_was_reached', array());
    }
}